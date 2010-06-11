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
