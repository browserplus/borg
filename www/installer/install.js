/*!
 *	bp-install-lib.js - A little javascript library which makes it easy
 *				 to integrate in-page browserplus installation.
 *
 *	Usage:
 *
 *	  installer = BPInstaller({
 *		config: "params"
 *	  });
 *
 *	  installer.start(callback);
 *
 *	(c) Yahoo! Inc 2010 All rights reserved
 */

// handle multiple inclusions of this file
BPInstaller = typeof BPInstaller != "undefined" && BPInstaller ? BPInstaller : function(cfg) {
	// class global data
	var UNIQUE_ID_FRAGMENT = 'bp75717a74cec54ae480567a4a7b07b60d';

	// instance data
	var STATE = "allocated";
	var CANCELED = false; // gets flipped when the client cancels us
	var PAUSED = false; // gets flipped when the client pauses us at a given event
	var $BP = BrowserPlus;
	var clientCallback = null;
	var initArgs = null;
	var javaVersion = null;
	var cleanups = [ ];

	// this whole thing is a big state machine
	var TheMachine = {
		/* entries are <state_name> => [ <emit_event?>, <pausable?>, <state function>] */

		// immediately occurs immediately allocation and argument validation
		start: [false, false, start_StateFunction],

		// after the client calls start(), immediately before we call BrowserPlusInit
		bpCheck: [true, true, bpCheck_StateFunction],

		// immediately before the check for Java on the machine
		javaCheck: [true, true, javaCheck_StateFunction], 

		// after we've confirmed that java is available on the machine but before we begin
		// the installation in earnest
		startJavaInstall: [true, true, startJavaInstall_StateFunction],

		// in the process of downloading, unpacking, and running the BrowserPlus installer to completion
		running: [true, false, null],

		// after we've confirmed that java is not available on the machine but before we begin
		// the installation in earnest
		startFallbackInstall: [true, true, startFallbackInstall_StateFunction],

		// We've caused a download and are now waiting for the user to complete the installation
		// of BrowserPlus.  cancel() may be called in this state and will clean up.
		waitForUserCompletion: [true, false, waitForUserCompletion_StateFunction],

		// The installation procedure has completed either successfully or not, the client callback
		// will be invoked immediately with a proper result code.
		complete: [true, false, complete_StateFunction]
	};

	var debug = function(msg) {
		if (cfg && cfg.debug) {
			cfg.debug(msg);
		}
	}

	var emitEvent = function(type, pausable, extra) {
		if (cfg && cfg.eventHandler) {
			var ev = {
				type: type,
				pausable: pausable,
				pause: pausable ? function() { PAUSED = true; } : null
			};
			if (extra === null) extra = {};
			for (k in extra) {
				if (extra.hasOwnProperty(k)) {
					ev[k] = extra[k];
				}
			}
			cfg.eventHandler(ev, self);
		}
	}

	function raiseError(error, verboseError) {
		if (error === null) { error = "bp.installationError"; }
		if (verboseError === null) { verboseError = "" };
		stateTransition('complete', {
			success: false,
			error: error,
			verboseError: verboseError
		});
	}

	var stateTransition = function (state, extra) {
		debug("Attempt to transition from '"+STATE+"' to '"+state+"'");
		if (!TheMachine[state]) {
			throw "attempt to transition to non-existent state: " + state;
		}
		STATE = state;
		var s = TheMachine[STATE];
		// if this is a state where we emit, then emit
		if (s[0]) { emitEvent(state, s[1], extra); }
		// if we are not paused, then move into the state
		if (!PAUSED && !CANCELED && typeof s[2] === 'function') { s[2](extra); } 
	};

	function getAppletContainer(divId, appletId, jarName, javaClass, params) {
		var t =
			'<applet codebase="' + cfg.pathToJar + '"' +
			' code="'+javaClass+'"' +
			' archive="' + jarName + '"' +
			' id="' + appletId + '"' +
			' width="0" height="0" name="Yahoo! BrowserPlus Installer" mayscript="true">';

		if (!params.codebase_lookup) {
			params["codebase_lookup"] = false;
		}

		for (var param in params) {
			if (params.hasOwnProperty(param)) {
				t += '<param name="'+param+'" value="'+params[param]+'"></param>';
			}
		}
		t += '</applet>';

			var div = document.createElement("div");
		div.id = divId;
		div.style.visibility = "hidden";
		try { div.style.borderStyle = "hidden"; } catch (e) { }
		div.style.width = 0;
		div.style.height = 0;
		div.style.border = 0;
		div.style.position = "absolute";
		div.style.top = 0;
		div.style.left = 0;
		div.innerHTML = t;
		return div;
	}

	// The java checker object, responsibile for silently checking for the existence
	// of java.
	function javaCheck_StateFunction() {
		debug("building java check DOM node"); 
		var divId = UNIQUE_ID_FRAGMENT + "_check_id";
		var appletId = UNIQUE_ID_FRAGMENT + "_check_applet";
		var div = getAppletContainer(divId, appletId, cfg.checkJarName,
			"com.yahoo.browserplus.installer.javatest.class", {});
		debug("appending java check DOM node to DOM"); 
		document.body.appendChild(div);

		// an async break to allow the applet to become ready.
		debug("async break to allow for applet readiness"); 
		setTimeout(function() {
			try {
				javaVersion = document.getElementById(appletId).getJavaVersion();
			} catch (e) {
			}
			
			// remove pollutant from DOM
			try {
				document.body.removeChild(document.getElementById(divId));
			}
			catch(e) {
				try { debug("couldn't remove applet tag: " + e); } catch(e) {}
			}

			// XXX: parse javaVersion to ensure correct handling of older versions.
			if (javaVersion !== null) {
				stateTransition("startJavaInstall");
			} else {
				stateTransition("startFallbackInstall");				
			}
		}, 0);					 
	};

	function startJavaInstall_StateFunction() {
		debug("building java check DOM node"); 
		var divId = UNIQUE_ID_FRAGMENT + "_install_div";
		var appletId = UNIQUE_ID_FRAGMENT + "_install_applet";
		var div = getAppletContainer(divId, appletId, cfg.installJarName,
			"com.yahoo.browserplus.installer.bplusloader.class",
			{
				installerBaseURL: cfg.installURL				
			});
		debug("appending java check DOM node to DOM"); 
		function removeAppletTagFromDOM() {
			// remove pollutant from DOM
			try {
				document.body.removeChild(document.getElementById(divId));
			}
			catch(e) {
				try { debug("couldn't remove applet tag: " + e); } catch(e) {}
			}
		}

		document.body.appendChild(div);

		// an async break to allow the applet to become ready.
		debug("async break to allow for applet readiness"); 
		var lastPercentSent = -1;
		var pollerId = setInterval(function() {
			try {
						var applet = document.getElementById(appletId);
				// Safari relinqueshes control to javascript when
				// displaying the javascript "trust" dialog.  We must
				// delay polling until the user interacts with that
				// dialog
				// XXX: handle "deny"
				if ($BP.clientSystemInfo().browser !== 'Safari' ||
					applet.hasOwnProperty("isActive"))
				{
					var status = String(applet.status().status);
					debug("applet status: " + status);
					if (status === 'starting') {
						// noop
					} if (status === 'error') {
						clearInterval(pollerId);
						debug("java installer encountered an error"); 
						removeAppletTagFromDOM();
						raiseError("bp.installerJavaError",
								   "java installer encountered an error");
					} else if (status === 'complete') {
						clearInterval(pollerId);
						removeAppletTagFromDOM();
						// explicit refresh needed for firefox, but
						// will make other browsers reload the parent
						// page (doh).
						if ($BP.clientSystemInfo().browser === 'Firefox') {
							try {navigator.plugins.refresh(false);} catch(e) {}
						}
						setTimeout(function() {
							$BP.init(initArgs, function(r) {
								stateTransition("complete", r);
							});
						}, 0);
					} else if (status === 'running') {
						// XXX: throttle?  only event when changed?
						var percent = applet.status().percent;
						if (lastPercentSent != percent) {
							lastPercentSent = percent;
							stateTransition(
								'running',
								{
									percent: applet.status().percent,
									localPercent: applet.status().localPercent,
									phase: applet.status().phase
								});
						}
					} else {
						debug("UNEXPECTED STATUS: " + status);
					}
				}
			} catch (e) {
				clearInterval(pollerId);
				removeAppletTagFromDOM();
				debug("that was exceptional: " + e.name + ": " + e.message); 
				raiseError("bp.installerJavascriptError", e.name + ": " + e.message);
			}
		}, 250);
	}

	function startFallbackInstall_StateFunction() {
		// fallback installation involves us attaching an iframe to the DOM
		// which will trigger the normal download of our installer. (as if a
		// user had clicked on a direct link or typed the filename into the
		// location bar).
		//
		// NOTE: continue() should be called in a click event handler, otherwise
		// certain browsers will make it hard for the user to see the download
		// event (considering it to be a "drive by download").

		var csi = BrowserPlus.clientSystemInfo();

		var downloadPath = "";
		if (csi.os === "Mac") {
			downloadPath = cfg.installURL + "osx/";	   
		} else {
			downloadPath = cfg.installURL + "win32/";	 
		}

		var iframeId = UNIQUE_ID_FRAGMENT + "_download_iframe";
		var iframe = document.createElement("iframe");
		iframe.src = downloadPath;
		iframe.style.display = "none";
		iframe.id = iframeId;
		document.body.appendChild(iframe);

		// schedule DOM cleanup
		cleanups.push(function() {
			try {
				document.body.removeChild(document.getElementById(iframeId));
			} catch(e) {
				try { debug("couldn't remove download iframe: " + e); } catch(e) {}
			}
		});

		// transition to waitForUserCompletion
		stateTransition('waitForUserCompletion');
	}

	function waitForUserCompletion_StateFunction() {
		setTimeout(function() {
			try { navigator.plugins.refresh(false); } catch(e) { }
			$BP.init(initArgs, function(r) {
				if (r.success || r.error !== 'bp.notInstalled') {
					stateTransition('complete', r);
				} else {
					waitForUserCompletion_StateFunction();
				}
			});
		}, 700);
	}

	function start_StateFunction()	{
		debug("validating inclusion of browserplus.js"); 
		// if the client didn't include browserplus.js, then we cannot run
		if (typeof BrowserPlus == "undefined" || !BrowserPlus) {
			throw "bp-install-lib.js requires browserplus.js to have been included";
		}
		debug("validated!"); 

		// verify required arguments
		debug("validating required arguments"); 
		{
			var requiredArgs = [
				"pathToJar", "installJarName", "checkJarName", "installURL"
			];
			for (a in requiredArgs) {
				if (requiredArgs.hasOwnProperty(a)) {
					if (!cfg || !cfg[requiredArgs[a]]) {
						throw "BPInstaller missing required '"+
							requiredArgs[a] +"' config parameter";
					}
				}
			}
		}
		debug("validated!"); 
	}


	function complete_StateFunction(r) {
		CANCELED = true;
		clientCallback(r);

		// now run any remaining cleanups.
		while (cleanups.length) {
			try {
				var f = cleanups.pop();
				f();
			} catch(e) {
				try { debug("couldn't run cleanup function: " + e); } catch(e) {}				 
			}
		}
	}

	function bpCheck_StateFunction() {
		// now we'll route through BrowserPlus's init call()
		$BP.init(initArgs, function(r) {
			if (r.success) {
				// no work need be done!
				stateTransition('complete', r);
			} else if (r.error === 'bp.notInstalled') {
				// BrowserPlus is *not* installed!	now it's time to
				// start the upsell dance.
				debug("BrowserPlus not installed, checking for presence of java"); 
				stateTransition("javaCheck");
			} else	{
				debug("error returned from init, aborting installation: " + r.error); 
				stateTransition('complete', r);
			}
		});
	}

	stateTransition("start");

	var self = {
		start: function(args, fn) {
			// allow the client to omit argument specification
			if (fn == null) {
				fn = args;
				args = {};
			}
			clientCallback = fn;
			initArgs = args;
			stateTransition("bpCheck");
		},

		cancel: function() {
			CANCELED = true;			  
		},

		resume: function() {
			debug("client invokes resume when in the '"+STATE+"' state");
			if (PAUSED) {
				PAUSED = false;
				if (TheMachine[STATE] && TheMachine[STATE][2]) {
					debug("resuming from '"+STATE+"'");
					TheMachine[STATE][2]();
				} else {
					debug("no work to be done to continue from this state");
				}
			}
		}
	};

	return self;
};
BPInstallerUI = typeof BPInstallerUI != "undefined" && BPInstallerUI ? BPInstallerUI : function() {

	var strings = {
		// ID prefix for all elements with an ID= attribute
		id: "ybp_wi",
		
		// first title that shows in dialog
		title: 'Yahoo! BrowserPlus',
		java_title: 'Installing Yahoo! BrowserPlus...',
		fallback_title: 'Installing Yahoo! BrowserPlus...',
		done_title: 'Yahoo! BrowserPlus Setup - Complete',

		bd_text: 'To continue using all of the features of this website, you need to update your system ' + 
				'with the BrowserPlus plug-in.<br><br>' +
				'The installation will take less than a minute and you won\'t even need to restart your browser.<br><br>',
		bd_tos: 'I agree to the <a href="#" style="color:{linkcolor}" >terms of service</a> and automatic <a href="#" style="color:{linkcolor}" >feature updates</a>.',
		bd_continue: 'Continue',
		bd_notnow: 'Not Now',
		bd_tosnotchecked: 'In order to continue, you need to accept the terms and conditions.',

		fallback_head: 'Installing is Easy!',
		fallback_text_win: 'During installation, click Run or Allow if prompted by dialog boxes.',
		fallback_text_mac: 'To install Yahoo! BrowserPlus, double-click the setup file in your Downloads folder.',
		fallback_text: '',

		done_head: 'You have successfully installed Yahoo BrowserPlus',
		done_text: 'Yahoo! BrowserPlus updates will automatically be downloaded to provide you with the ' + 
			'latest features and security improvements. To change this, see ' + 
			'<a style="color:{linkcolor}" target="blank" href="http://browserplus.yahoo.com/autoupdate">'	 +
			'http://browserplus.yahoo.com/autoupdate</a>.',
		done_button: 'Close',

		// browserplus logo
		icon:   "http://browserplus.org/i/bp-install-logo.png",
		icon_w: "75",
		icon_h: "75",
		
		// main font
		font:      "arial, sans-serif",
		fontsize: "12px",
		fontcolor: "#333",
		linkcolor: "#00c",

		titlebg:  "#0081c2",  // title bar and border
		titlebg1: "#6ad",    // brighter, top of title bar
		titlebg2: "#35a",    // darker, bottom of title bar
		titlefg:  "white",   // titlebar text
		dialogbg: "#f7fcfe", // dialog background


		// in-line style attributes ... so there's no external CSS requirements
		s_overlay:        'background:#000; opacity: 0.33; filter:alpha(opacity=33); position: fixed; top:0; left:0; height:100%; width:100%; z-index:500;',
		s_dialog:         'text-align:left;background:{dialogbg}; color:{fontcolor}; position:fixed; top:100px; left:200px; width: 500px; border:2px solid {titlebg}; border-top:1px solid {titlebg1};font:{fontsize} {font}; z-index:501;line-height:1.3em;',
		s_image:          'width:{icon_w}px;height:{icon_h}px; background: transparent url({icon}) no-repeat 0 0; margin:0 auto; padding:0 5px;',
		s_titlebar:       'text-align:left;background:{titlebg}; color:{titlefg}; padding: 4px 5px; font: bold 108% {font};',
		s_bd:             'padding:10px 10px 5px 10px; text-align:left; border-top:1px solid {titlebg2};',
		s_dialog_head:    'font:bold 108% {font};color:{fontcolor};margin-bottom:1em;',
		s_buttons:        'border-top: 1px solid #e7ecee; padding-top:5px;',
		s_tos_not:        'color:green;',
		s_progress_outer: 'text-align:center;',
		s_progress:       'position:relative; border:1px solid #69c; width:300px; height:18px; margin:10px auto;',
		s_progress_bar:   'position:absolute; top:0px; left:0px; background:#cdf; width:0; height:18px;',
		s_progress_text:  'position:absolute; top:0px; left:0px; text-align:center; width:100%; font:bold 14px/18px {font}; z-index:511;',
		s_input:          "padding:2px 6px 3px; margin:2px 5px;font-size: 93%; font-family:'lucida grande',arial,sans-serif;",
		s_td1:            'padding-right:10px;',
		s_td:             'padding:0; border:0px;',
		// so the last entry is comma-less
		noop: ''
	},
	
	installer,
	installerCallbackValue,
	isInstalling = false,
	userCallback,
	
	
	// Page "2" of dialog for those without Java - shows "Click Allow or Run" text
	fallbackTmpl = 
		'<table border=0 width="100%" height="auto">' +
			'<tbody><tr>' +
				'<td style="{s_td}" valign="top"><div style="{s_image}"></div></td>' +
				'<td style="{s_td} {s_td1}" valign="top"><h2 style="{s_dialog_head}">{fallback_head}</h2>{fallback_text}<br></td>' +
			'</tr></tbody>' +
		'</table>',


	// Page "2" of dialog for those with Java - shows progress bar
	javaTmpl = 
		'<div style="{s_progress_outer}">' +
			'<div><div style="{s_image}"></div></div>' +
			'<div id="{id}_progress" style="{s_progress}">' +
				'<div id="{id}_progress_bar" style="{s_progress_bar}"></div>' +
				'<div id="{id}_progress_text" style="{s_progress_text}">0%</div>' +
			'</div>' +
		'</div>',
		
	// Page "3" of dialog, All Done!
	doneTmpl =
		'<table border=0 width="100%" height="auto">' +
			'<tbody><tr>' +
				'<td style="{s_td}" valign="top"><div style="{s_image}"></div></td>' +
				'<td style="{s_td}" valign="top"><h2 style="{s_dialog_head}">{done_head}</h2>{done_text}<br><br></td>' +
			'</tr></tbody>' +
			'<tfoot><tr>' +
				'<td align=right colspan=2 style="{s_td} {s_buttons}">' +
					'<button id="{id}_bt3" style="{s_input}">{done_button}</button>' +
				'</td>' +
			'</tr></tfoot>' +
		'</table>',
	

	// Page "1" of dialog
	dialogTmpl = 
		'<div id="{id}_dialog" style="{s_dialog}">' +
			'<div id="{id}_title" style="{s_titlebar}">{title}</div>' +
			'<div id="{id}_bd" style="{s_bd}">' +
				'<table border=0 width="100%" height="auto">' +
					'<tbody>' + 
						'<tr>' +
							'<td style="{s_td}" valign="top"><div style="{s_image}"></div></td>' +
							'<td style="{s_td}">{bd_text}</td>' +
						'</tr>' +
						'<tr>' +
							'<td align="center" colspan="2" style="{s_td}">' +
								'<div><input id="{id}_cb" type="checkbox" style="{s_input}"><label for="{id}_cb">{bd_tos}</label></div>' +
								'<div id="{id}_tos_not" style="display:none; {s_tos_not}"><br>{bd_tosnotchecked}</div><br>' +
							'</td>' +								
						'</tr>' +
					'</tbody>' +
					'<tfoot><tr>'+
						'<td align=right colspan=2 style="{s_td} {s_buttons}">' +
							'<button id="{id}_bt1" style="{s_input}">{bd_continue}</button>' +
							'<button id="{id}_bt2" style="{s_input}">{bd_notnow}</button>' +
						'</td>' +
					'</tr></tfoot>' +
				'</table>' +
			'</div>' +
		'</div>',
		
		overlayTmpl = '<div id="{id}_overlay" style="{s_overlay}"></div>',
			
		Dialog,
		Overlay,

		ua = function() {
			var o={ie:0, opera:0, gecko:0, webkit: 0};
			var ua=navigator.userAgent, m;

			// Modern KHTML browsers should qualify as Safari X-Grade
			if ((/KHTML/).test(ua)) {
				o.webkit=1;
			}

			// Modern WebKit browsers are at least X-Grade
			m=ua.match(/AppleWebKit\/([^\s]*)/);
			if (m&&m[1]) {
				o.webkit=parseFloat(m[1]);
			}

			if (!o.webkit) { // not webkit
				m=ua.match(/Opera[\s\/]([^\s]*)/);
				if (m&&m[1]) {
					o.opera=parseFloat(m[1]);
				} else { // not opera or webkit
					m=ua.match(/MSIE\s([^;]*)/);
					if (m&&m[1]) {
						o.ie=parseFloat(m[1]);
					} else { // not opera, webkit, or ie
						m=ua.match(/Gecko\/([^\s]*)/);
						if (m) {
							o.gecko=1; // Gecko detected, look for revision
							m=ua.match(/rv:([^\s\)]*)/);
							if (m&&m[1]) {
								o.gecko=parseFloat(m[1]);
							}
						}
					}
				}
			}
			return o;
		}();

	function get(el) {
		return (el && el.nodeType) ? el : document.getElementById(el);
	}
	
	function isString(o) {
		return typeof o === 'string';
	}

	function isObject(o) {
		return typeof o === 'object';
	}

	// thank you YUI
	function substitute(s, o) {
		var i, j, k, key, v, meta, saved=[], token, SPACE=' ', LBRACE='{', RBRACE='}';

		for (;;) {
			i = s.lastIndexOf(LBRACE);
			if (i < 0) { break;}
			j = s.indexOf(RBRACE, i);
			if (i + 1 >= j) { break; }

			//Extract key and meta info 
			token = s.substring(i + 1, j);
			key = token;
			meta = null;
			k = key.indexOf(SPACE);
			if (k > -1) {
				meta = key.substring(k + 1);
				key = key.substring(0, k);
			}

			// lookup the value
			v = o[key];
			s = s.substring(0, i) + v + s.substring(j + 1);
		}

		// restore saved {block}s
		for (i=saved.length-1; i>=0; i=i-1) {
			s = s.replace(new RegExp("~-" + i + "-~"), "{"	+ saved[i] + "}", "g");
		}

		return s;
	}


	function addListener(el, type, fn) {
		if (isString(el)) { el = get(el); }

		if (el.addEventListener){
			el.addEventListener(type, fn, false);
		} else if (el.attachEvent) {
			el.attachEvent("on"+type, fn);
		}
	}

	function removeListener(el, type, fn) {
		if (isString(el)) { el = get(el); }

		if (!el) { return; }
		if (el.removeEventListener){
			el.removeEventListener(type, fn, false);
		} else if (el.detachEvent) {
			el.detachEvent("on"+type, fn);
		}
	}
	
	function getViewportSize() {
		var width = window.innerWidth,   // Safari, Operaa
		 	height = window.innerHeight, // Safari, Opera
			mode = document.compatMode;

		if ( (mode || ua.ie) && !ua.opera ) { // IE, Gecko
			if (mode === 'CSS1Compat') {
				// Standards
				height = document.documentElement.clientHeight;
				width  = document.documentElement.clientWidth;
			} else {
				// Quirks
				height = document.body.clientHeight; 
				width  = document.body.clientWidth; 
			}
		}

		return [width, height];
	}

	function resizeCB() {
		// we only need to move the dialog based on scroll position if
		//	 we're using a browser that doesn't support position: fixed, like < IE 7
		var view = getViewportSize();

		var left = window.XMLHttpRequest === null ? document.documentElement.scrollLeft : 0;
		var top = window.XMLHttpRequest === null ? document.documentElement.scrollTop : 0;

		var h = Dialog.offsetHeight;
		var w = Dialog.offsetWidth;

		Dialog.style.left = Math.floor(Math.max((left + (view[0] - w) / 2), 0)) + 'px';
		Dialog.style.top  = Math.max(0, Math.floor(Math.max((top + (view[1] - h) / 2), 0)) - 50) + 'px';
	}

	function goAway() {
		// remove all traces
		removeListener(strings.id + "_bt1", "click", buttonCB);
		removeListener(strings.id + "_bt2", "click", goAway);
		removeListener(strings.id + "_bt3", "click", goAway);

		removeListener(window, "resize", resizeCB);
		Dialog.parentNode.removeChild(Dialog);
		Overlay.parentNode.removeChild(Overlay);			

		if (installerCallbackValue) {
			userCallback(installerCallbackValue);
		}
	}

	function buttonCB(e) {
		var tos = get(strings.id + "_cb");
		if (tos.checked) {
			installer.resume();
			// control back to myEventHandler
		} else {
			get(strings.id + "_tos_not").style.display="block";
		}
	}

	function showOverlay() {
		var e = document.createElement("div");
		e.innerHTML = substitute(overlayTmpl, strings);
		document.body.appendChild(e);
		Overlay = get(strings.id + "_overlay");
		/*
		if (ua.ie) {
			//overlay.style.top = Math.max(document.body.scrollTop,document.documentElement.scrollTop) + 'px';
			//overlay.left = Math.max(document.body.scrollLeft,document.documentElement.scrollLeft) + 'px';
			var size = getViewportSize();
			overlay.style.width = size[0] + 'px';
			overlay.style.height = size[1] + 'px';		
		}
		*/
	}

	function showDialog() {
		var e = document.createElement("div");
		document.body.appendChild(e);
		e.innerHTML = substitute(dialogTmpl, strings);
		Dialog = get(strings.id + "_dialog");
		resizeCB();
		addListener(window, "resize", resizeCB);
		addListener(strings.id + "_bt1", "click", buttonCB);
		addListener(strings.id + "_bt2", "click", goAway);
	}
	
	function myEventHandler(e, ii) {
		var bd, title, percent, type = e.type, pbar, ptext, dialog;

		dialog =  get(strings.id + "_dialog");

		bd = get(strings.id + "_bd");
		title = get(strings.id + "_title");
		percent = 0;

		if (type === "javaCheck") {
			// when isInstalling true, we delay the callback to the function passed in start
			isInstalling = true;
			e.pause();
			showOverlay();
			showDialog();
		} else if (type === "startFallbackInstall") {
			removeListener(strings.id + "_bt1", "click", buttonCB);
			removeListener(strings.id + "_bt2", "click", goAway);
			bd.innerHTML = substitute(fallbackTmpl, strings);
			title.innerHTML = strings.fallback_title;
			// show instructions
		} else if (type === "startJavaInstall") {
			removeListener(strings.id + "_bt1", "click", buttonCB);
			removeListener(strings.id + "_bt2", "click", goAway);

			bd.innerHTML = substitute(javaTmpl, strings);
			title.innerHTML = strings.java_title;
			// show java install progress
		} else if (type === "running") {
			if (e.hasOwnProperty('percent')) {
				pbar = get(strings.id + "_progress_bar");
				ptext = get(strings.id + "_progress_text");
				if (pbar && ptext) {
					percent = ""+parseInt(e.percent, 10) + "%";
					get(strings.id + "_progress_bar").style.width = percent;
					get(strings.id + "_progress_text").innerHTML = percent;
				}
			}
		} else if (type === "complete" && dialog) {
			bd.innerHTML = substitute(doneTmpl, strings);
			title.innerHTML = strings.done_title;
			addListener(strings.id + "_bt3", "click", goAway);
		}
	}
	
	function myStartCallback(e) {
		// Callback the user supplied callback immediately when BrowserPlus is already installed.
		// Otherwise, save the value and callback after the dialog has been dismissed.
		if (isInstalling) {
			installerCallbackValue = e;
		} else {
			userCallback(e);
		}
	}


	return {
		start: function(cfg, fn) {
			var key, strs, 
				defaultCFG = {
					pathToJar: ".",
					installJarName: "bp-installer.jar",
					checkJarName: "bp-java-check.jar",
					installURL: "http://browserplus.yahoo.com/dist/v2/installer/"
				};

			userCallback = fn;

			if (!userCallback) {
				userCallback = cfg;
				cfg = {};
			} 

			cfg = cfg || {};

			if (typeof userCallback !== 'function') { throw "No callback function provided to start()";}
			if (typeof cfg !== 'object') { throw "Config object passed to start() is not an object.";}

			// set defaults if not already set
			for (key in defaultCFG) {
				if (defaultCFG.hasOwnProperty(key)) {
					cfg[key] = cfg[key] || defaultCFG[key];
				}
			}
			
			// allow caller to override any "strings" property
			if (cfg.hasOwnProperty("strings") && isObject(cfg)) {
				strs = cfg.strings;
				for (key in strs) {
					if (strs.hasOwnProperty(key) && isString(strs[key])) {
						strings[key] = strs[key];
					}
				}
			}

			// event handler must point back to our handler
			cfg.eventHandler = myEventHandler;

			installer = BPInstaller(cfg);

			// Platform specific language
			if (BrowserPlus.clientSystemInfo().os === "Mac") {
				strings.fallback_text = strings.fallback_text_mac;
			} else {
				strings.fallback_text = strings.fallback_text_win;				
			}

			installer.start({}, myStartCallback);
		}
	};

}();