BPInstallerUI = typeof BPInstallerUI != "undefined" && BPInstallerUI ? BPInstallerUI : function() {

	var strings = {
		// ID prefix for all elements with an ID= attribute
		id: "ybp_wi",
		
		ahref: 'a target="blank" style="color:{linkcolor}" href',
		
		// first title that shows in dialog
		title: 'Yahoo! BrowserPlus',
		java_title: 'Installing Yahoo! BrowserPlus...',
		fallback_title: 'Installing Yahoo! BrowserPlus...',
		done_title: 'Setup Complete',

		autoupdate_link: "http://browserplus.org/autoupdate",
		eula_link: "http://info.yahoo.com/legal/us/yahoo/browserplus/",

		bd_text: 'To use all the features of this website, you need the latest version of the Yahoo! BrowserPlus plug-in.<br><br>' +
			'Installing BrowserPlus takes less than a minute, and you won\'t even need to restart your browser.<br><br>' ,
		bd_eula: 'I agree to the <{ahref}="{eula_link}">end user license agreement</a> and <{ahref}="{autoupdate_link}">automatic feature updates</a>.',
		bd_continue: 'Continue',
		bd_notnow: 'Not Now',
		bd_eulanotchecked: 'In order to continue, you need to accept the end user license agreement.',

		fallback_head: 'Installing is Easy!',
		fallback_text_win: 'During installation, click Run or Allow if prompted by dialog boxes.',
		fallback_text_mac: 'To install Yahoo! BrowserPlus, double-click the setup file in your Downloads folder.',
		fallback_text: '',

		done_head: 'You have successfully installed Yahoo BrowserPlus.',
		done_text: 'Yahoo! BrowserPlus updates will automatically be downloaded to provide you with the ' + 
			'latest features and security improvements.',
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
		s_eula_not:       'color:green;',
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
								'<div><input id="{id}_cb" type="checkbox" style="{s_input}"><label for="{id}_cb">{bd_eula}</label></div>' +
								'<div id="{id}_eula_not" style="display:none; {s_eula_not}"><br>{bd_eulanotchecked}</div><br>' +
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
		var eula = get(strings.id + "_cb");
		if (eula.checked) {
			installer.resume();
			// control back to myEventHandler
		} else {
			get(strings.id + "_eula_not").style.display="block";
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