<?php 
require("../../php/vars.php");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html lang="en">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8">
	<title>Service Explorer: BrowserPlus</title>
	<link rel="stylesheet" type="text/css" href="explorer.css">
</head>
<body class="yui-skin-sam">
	
<div id="explorer">
	<div id="navlists">
		<div id="services">
			<h2>Services</h2>
			<select id="servicelist" size="8"><option>Loading...</option></select>
		</div>

		<div id="versions">
			<h2>Versions</h2>
			<select id="versionlist" size="8"></select>
		</div>

		<div id="functions">
			<h2>Functions</h2>
			<select id="functionlist" size="8"></select>
		</div>
	</div>

	<div id="container">
		<div id="method">
			<h3 id="title">Select the Service, Version and Function Above</h3>
			<form id="frm" name="fparams"><fieldset id="fields"><br></fieldset></form>
		</div>
		<h3>Console</h3>
		<div id="result" class="prettyprint"></div>
	</div>

</div>
<script src="http://yui.yahooapis.com/3.0.0/build/yui/yui-min.js"></script>
<script src="<?php echo BROWSERPLUS_MIN_JS; ?>"></script>
<script src="/installer/install-min.js"></script> 
<script src="/js/json2.js"></script>
<script src="parameters.js"></script>
<script>
YUI().use("event-base", "io-base", "dom-base", "substitute", function(Y) {
	var ServiceOS = (BrowserPlus.clientSystemInfo().os == "Mac" ? "osx" : "win32");
	var ServiceVersions = {};
	var ServiceDetails = {};
	var CurrentService = null;
	var CurrentFunction = null;
	var FileHandles;
	var QueryServiceParam = null;
	var QueryVersionParam = null;
	var QueryFunctionParam = null;
	var HasWritablePaths = false;
	var WritableVersion = "3.0.0";
	var FileAccessVersion = "2.0.1";
	
	// for log(LEVEL, str)
	var FUNC   = "func";
	var RETVAL = "retval";
	var ERROR  = "error";
	var INFO   = "info";
	var TIME   = "time";
	
	var Tmpl = {
		EmptyTitle: 'Select the Service, Version and Function Above</h3>',
		EmptyForm:	'<br>',

		FunctionTitle: 
			'<a target="doc" href="/docs/services/{sname}.html?v={version}#{fname}">BrowserPlus.{sname}.{fname}()</a>',

		FunctionForm: 
			'<ol>' + 
			'	 {fields}' +
			'	 <li><input class="runAction" type="button" value="Run" id="submit"></li>' + 
			'</ol>',

		FunctionParams:
			'<dt class="parameter">' + 
			'	  <span class="varBlock">' + 
			'		  <span class="varName">{opt1}{name}:</span>' + 
			'		  <span class="varType">{type}</span>{opt2}' + 
			'	  </span>' +
			'</dt>' + 
			'<dd>{doc}</dd>'
		};

	
	/* Note that QueryParams is an Object, not a function. */
	var QueryParams = (function() {
		var i, nv, ret = {}, q = window.location.search.substring(1).split("&"), len = q.length;

		for (i = 0; i < len; i++) {
			nv = q[i].split("=");
			ret[nv[0]] = nv[1];
		}
	
		return ret;
	})();
	
	function getTimeStamp() {
		var d = new Date(),
			h = d.getHours(),
			m = d.getMinutes(),
			s = d.getSeconds();
		h = (h < 10 ? "0" + h : h);
		m = (m < 10 ? "0" + m : m);
		s = (s < 10 ? "0" + s : s);
		return ("[" + h + ":" + m + ":" + s + "] ");
	}

	function asciiquo(value) {
		return value.replace(/[^\x20-\x7f]/g, "_").replace(/"/g, "&quot;");
	}

	function isImage(file) {
		var i, len = file.mimeType.length, mime;

		for (i = 0; i < len; i++) {
			mime = file.mimeType[i];
			if (mime === "image/jpeg" || mime === "image/pjpeg" || mime === "image/gif" || mime === "image/png") {
				return true;
			}
		}
		return false;
	}

	function opt(name, sel) {
		return '<option value="' + name + '">' + name + '</option>';
	}

	function setFunctionList(details) {
		var opts=[], funcs = details.functions, selected = -1;
		for(var i = 0, len=funcs.length; i < len; i++) {
			opts.push(opt(funcs[i].name));
			if (funcs[i].name === QueryFunctionParam) { selected = i; }
		}

		Y.one("#functionlist").setContent(opts.join(""));
		Y.one("#functionlist").set("selectedIndex", selected);

		// only preselect function once (on first page load)
		QueryFunctionParam = null;
		if (selected > -1) {
			functionListChangedEvent();
		}
	}

	// util method to convert version strings into sortable strings (1.2.3 into 100020003)
	function versionNum(versionString) {
		var arr = versionString.match(/(\d+)/g);
		// (v added so a string is returned instead of an int)
		return "v"+(parseInt(arr[0],10)*1000000 + parseInt(arr[1]*1000,10) + parseInt(arr[2],10));
	}

	// Called in response to AJAX request (for builtin functions not described by InactiveServices)
	function gotServiceDetails(id, o, args) {
		var obj = JSON.parse(o.responseText);
		ServiceDetails[args.s + args.v] = obj;
		args.cb(obj);
	};

	// Service name is selected from Service List
	function serviceListChangedEvent(e) {
		var opts=[], versions, selectedValue = Y.one("#servicelist").get("value"), selected = -1;
		
		// only set when function is chosen
		CurrentService = null;
		CurrentFunction = null;

		if (selectedValue) {
			// sort version numbers from new to old (versionNum makes 1.2.3 into string like "v100020003")
			versions = ServiceVersions[selectedValue];
			versions.sort(function(a,b){
				return versionNum(b).localeCompare(versionNum(a));
			});

			for (var i = 0, len = versions.length; i < len; i++) {
				if (versions[i] === QueryVersionParam) { selected = i;}
				opts.push(opt(versions[i]));
			}
		}
		
		Y.one("#versionlist").setContent(opts.join(""));
		Y.one("#functionlist").setContent(opt(""));

		// replace function form with empty form
		Y.one("#title").setContent(Tmpl.EmptyTitle);
		Y.one("#fields").setContent(Tmpl.EmptyForm);
		Y.one("#versionlist").set("selectedIndex", selected);			

		// version only preselected first time thru
		QueryVersionParam = null;
		if (selected > -1) {
			versionListChangedEvent();
		}
	}

	// Version string is selected from Versions List
	function versionListChangedEvent(e) {
		// only set when function is chosen
		CurrentService = null;
		CurrentFunction = null;

		var service = Y.one("#servicelist").get("value");
		var version = Y.one("#versionlist").get("value");
	
		// replace function form with empty form
		Y.one("#title").setContent(Tmpl.EmptyTitle);
		Y.one("#fields").setContent(Tmpl.EmptyForm);

		if (service && version) {
			if (ServiceDetails[service+version]) {
				// details already cached from call to Describe below
				setFunctionList(ServiceDetails[service+version]);
			} else {
				// this will take a little time, so show Loading... in this case
				Y.one("#functionlist").setContent(opt("Loading..."));

				BrowserPlus.describeService({service:service, version:version, minversion:version}, function(r1) {
					if (r1.success) {
						// fetching service description locally
						ServiceDetails[r1.value.name+r1.value.versionString] = r1.value;
						setFunctionList(r1.value);
					} else {
						// get description of service from web services
						BrowserPlus.InactiveServices.Describe({service:service, version:version}, function(r2) {
							if (r2.success) {
								ServiceDetails[r2.value.name + r2.value.versionString] = r2.value;
								setFunctionList(r2.value);
							} else {
								// failed getting service description in 2 ways
							}
						});
					}
				});

			}
		}
	}

	// Version string is selected from Versions List
	function functionListChangedEvent(e) {
		var sname = Y.one("#servicelist").get("value");
		var vname = Y.one("#versionlist").get("value");
		var fname = Y.one("#functionlist").get("value");	

		if (!sname || !vname || !fname) { return; }

		// get reference to current service
		var i, len;
		
		CurrentService = ServiceDetails[sname + vname];
		
		// get reference into current function
		for(i = 0, len = CurrentService.functions.length; i < len; i++) {
			if (CurrentService.functions[i].name == fname) {
				CurrentFunction = CurrentService.functions[i];
				break;
			}
		}

		FileHandles = {};
		
		var plen = CurrentFunction.parameters.length, p, value, params="", opt1, opt2, form="", help, enabled, style, key, hint, firstFocus;

		if (plen === 0) {
			params = '<dt class="noparams">No parameters</dt>';
			form = '<p class="noparams formp">No parameters</p>';
		} else {
			for (i = 0; i < plen; i++) {
				p = CurrentFunction.parameters[i];
				if (p.required) {
					opt1 = "<b>"; opt2 = " (req)</b>";
				} else {
					opt1 = "<em>"; opt2 = " (opt)</em>";
				}

				params += Y.Lang.substitute(Tmpl.FunctionParams, { 
					name: p.name, type: p.type, opt1: opt1, opt2: opt2});
				
				key = CurrentService.name + "_" + CurrentFunction.name + "_" + p.name;

				enabled = "";
				style = "";

				help = ExParamHelp[key] || "";
				if (help) { 
					help = '<span class="paramhelp">' + help + "</span>";
				}

				value = QueryParams[p.name] || ExParamValues[key] || "";
				hint  = ExParamHints[key] || "";

				if (p.type === "callback") {
					enabled = "disabled";
					style = 'style="background:#ffc"';
					value = 'callback provided';
				} else if (p.type === "path") {
					enabled = "disabled";
					help = "<input id=\"h_" + p.name + "\" class=\"fileAction\" type=\"button\" value=\"Select File\"> " + help;
					style = 'style="background:#def"';
				} else if (p.type === "writablePath" && HasWritablePaths) {
					enabled = "disabled";
					help = "<input id=\"h_" + p.name + "\" class=\"saveAsAction\" type=\"button\" value=\"Select File\"> " + help;
					style = 'style="background:#def"';
				} else if (hint === "filemap" || hint === "filelist") {
					enabled = "disabled";
					help = "<input id=\"h_" + p.name + "\" class=\"filesAction\" type=\"button\" value=\"Select Files\"> " + help;
					style = 'style="background:#def"';
				}

				if (!firstFocus) { firstFocus = "f_" + p.name; }
				form += '<li><label>' + opt1 + p.name + ":" + p.type + opt2 + "</label>";
				form += '<input ' + enabled + ' type="text" id="f_' + p.name + '" name="f_' + p.name + 
					'" ' + style + ' value="' + value.replace(/"/g, "&quot;") + '" "size="40">' + help;
				form += '</li>';
			}
		}

			'<a target="doc" href="/docs/services/{sname}.html?v={version}#{fname}">BrowserPlus.{sname}.{fname}()</a>',

		Y.one("#title").setContent(Y.Lang.substitute(Tmpl.FunctionTitle, {sname:sname, fname:fname, version: vname}));
		Y.one("#fields").setContent(Y.Lang.substitute(Tmpl.FunctionForm, {fields:form}));

		if (firstFocus) { Y.one("#"+firstFocus).focus();}
	}

	function log(level, str, fmt, extra) {

		if (str === undefined) {
			str = level;
			level = INFO;
		}

		var cnt, r = Y.one("#result");
		if (r) {
			str = str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");

			// from http://snippets.dzone.com/posts/show/5770
			try {
				cnt = ((str.match(/[^\n]*\n[^\n]*/gi).length));
			} catch(e) {
				cnt = 0;
			}

			if (fmt && cnt > 0) {
				str = "VALUE:\n<pre>" + str + "</pre>";
				if (extra) { 
					str += extra;
				}
			}

			r.append('<div><span class="log_' + TIME + '">' + getTimeStamp() + '</span>' + 
				'<span class="log_' + level + '">' + str + "</span></div>");
			r.set("scrollTop", r.get("scrollHeight"));
		}
	}

	function formClickedEvent(e) {
		var node = e.target;

		// Run button pressed or 'Enter' key pressed
		if (node.hasClass("runAction") || node.get("id") == "frm") {
			e.preventDefault();
			log(INFO, "==== Run! ====")
			runAction();
		} else if (node.hasClass("fileAction") || node.hasClass("filesAction")) {
			e.preventDefault();
			log(INFO, "==== Open Select File Dialog ====");
			selectFilesAction(node);
		} else if (node.hasClass("saveAsAction")) {
			e.preventDefault();
			log(INFO, "==== Open Save As Dialog ====");
			saveAsAction(node);
		}

	}

	function testInvokeFunc(name) {
		return function(res) {
			if (res.success) {
				log(INFO, "SUCCESS");

				if (res.value.file && isImage(res.value.file)) {
					BrowserPlus.FileAccess[FileAccessVersion].getURL({ file: res.value.file }, function (r) {
						var str;
						if (r.success) {
							if (res.value.width <= 600) { 
								str = '<img src="' + r.value + '" width="' + res.value.width + '" height="' + res.value.height + '">';
							} else {
								str = "<div class=\"viewit\">The result is an image.  <b><a target=\"_blank\" href=\"" + r.value + "\">View it</a></b> in a new window.</div>";
							}
							log(RETVAL, JSON.stringify(res.value, null, "  "), true, str);
						}
					});


				} else {
					log(RETVAL, JSON.stringify(res.value, null, "  "), true);
				}
			} else {
				log(INFO, "FAILURE");
				log(ERROR, res.error + (res.verboseError ? (" - " + res.verboseError) : ""));
			}
		};
	}

	function testCallbackFunc(name) {
		return function(res) {
			log(INFO, name + " CALLBACK");
			log(RETVAL, JSON.stringify(res, null, "	 "), true);
		};
	}

	function getRequireCallback(serviceName, functionName, version, obj, prettyObj) {
		return function(res) {
			if (res.success) {
				log(FUNC, "BrowserPlus." + serviceName + "." + functionName + "(" + JSON.stringify(prettyObj, null) + ", function(){});");
				BrowserPlus[serviceName][version][functionName](obj, testInvokeFunc(functionName));
			} else {
				log(ERROR, "Error requiring " + serviceName + "." + functionName + "(" + version + ")");
			}
		};
	}

	function progressCallback(status) {
		log(INFO, "Loading " + status.name + " Service - " + status.totalPercentage + "%");
	}


	function selectFilesAction(node) {
		BrowserPlus.FileBrowse.OpenBrowseDialog({}, function(res) {
			var i, handles = [], name, files = res.value.files, len = files.length, fnames=[];
			log(RETVAL, JSON.stringify(files, null, " "), true);
			if (Y.Lang.isArray(files) && len > 0) {
				for (i = 0; i < len; i++) {
					handles.push(files[i]);
					fnames.push(files[i].name);
                    if (node.hasClass("fileAction")) {
                        // limit to first item for fileAction
                        break;
                    }
				}

				name = node.get("id").substring(2);
				FileHandles[name] = handles;
				document.fparams["f_" + name].value = fnames.join(", ");
			}
		});
	}
	
	function displaySaveAs(node) {
		BrowserPlus.FileBrowse[WritableVersion].saveAs({}, function(res) {
			var i, name, file;

			if (res.success) {
				file = res.value;
				log(RETVAL, JSON.stringify(file, null, " "), true);
				name = node.get("id").substring(2);
				FileHandles[name] = [file];
				document.fparams["f_" + name].value = file.name;
			} else {
				log(INFO, res.error + (res.verboseError ? (" - " + res.verboseError) : ""));
			}

		});
	}

	function saveAsAction(node) {
		if (HasWritablePaths && BrowserPlus.FileBrowse[WritableVersion] === undefined) {
			BrowserPlus.require(
				{ services: [{service:"FileBrowse", version: WritableVersion}]}, 
				function(res) {
					if (res.success) {
						displaySaveAs(node);
					} else {
						log(INFO, res.error + (res.verboseError ? (" - " + res.verboseError) : ""));
					}
				}); // require()
		} else {
			displaySaveAs(node);
		}
	}

	function runAction() {
		var i, j, len, params, el, name, type, required, val, obj = {}, prettyObj = {}, error = false, sname, fname, version,
			errorStyle = "2px solid #e33", filemap, fstr=[];

		if (!CurrentService || !CurrentFunction) { return; }
		
		sname  = CurrentService.name;
		version	  = CurrentService.versionString;
		fname  = CurrentFunction.name;
		params = CurrentFunction.parameters;

		for (i = 0, len=params.length; i < len; i++) {

			name	 = params[i].name;
			type	 = params[i].type;
			required = params[i].required;

			el = document.fparams['f_'+name];
			val = Y.Lang.trim(el.value);

			if (val) {
				el.style.border = "";

				switch (type) {
				case "any":
					try {
						obj[name] = JSON.parse(val);
						prettyObj[name] = JSON.parse(val);
					} catch(er1) {
						// send as string
						obj[name] = val;
						prettyObj[name] = '"' + asciiquo(val) + '"';
					}

					break;
				case "list":
					try {
						if (FileHandles[name]) {
							obj[name] = FileHandles[name];
							for (j = 0; j < FileHandles[name].length; j++) {
								fstr.push("<file"+j+">");
							}
							prettyObj[name] = "[" + fstr.join(", ") + "]";
						} else {
							obj[name] = JSON.parse(val);
							prettyObj[name] = JSON.parse(val);
						}
					} catch (er2) {
						el.style.border = errorStyle;
						error = true;
					}
					break;
				case "map":
					try {
						if (FileHandles[name]) {
							filemap = {};
							for (j = 0; j < FileHandles[name].length; j++) {
								filemap["f" + j] = FileHandles[name][j];
								fstr.push("f"+j + ": <file"+j + ">");
							}

							obj[name] = filemap;
							prettyObj[name] = "{" + fstr.join(", ") + "}";
						} else {
							obj[name] = JSON.parse(val);
							prettyObj[name] = JSON.parse(val);
						}
					} catch (er3) {
						el.style.border = errorStyle;
						error = true;
					}
					break;
				case "integer": 
					obj[name] = parseInt(val, 10);
					prettyObj[name] = parseInt(val, 10);
					break;
				case "double": 
					obj[name] = parseFloat(val);
					prettyObj[name] = parseFloat(val);
					break;
				case "boolean":
					obj[name] = /^(1|t|true|y|yes)$/i.test(val);
					prettyObj[name] = /^(1|t|true|y|yes)$/i.test(val);
					break;
				case "path":
					obj[name] = FileHandles[name][0];
					prettyObj[name] = "<file>";
					break;
				case "writablePath":
					obj[name] = FileHandles[name][0];
					prettyObj[name] = "<fileW>";
					break;
				case "callback":
					obj[name] = testCallbackFunc(name);
					prettyObj[name] = "function(r){}";
					break;
				default:
					obj[name] = val;
					prettyObj[name] = val;
					break;
				}
			} else if (required) {
				el.style.border = errorStyle;
				error = true;
			}
		}

		if (error) {
			log(ERROR, "Error - Please set all required values.");
		} else if (BrowserPlus[sname] && BrowserPlus[sname][version]) {
			log(FUNC, "BrowserPlus." + sname + "." + fname + "(" + JSON.stringify(prettyObj, null) + ", function(){});");
			BrowserPlus[sname][version][fname](obj, testInvokeFunc(fname));
		} else {
			log(INFO, "Requiring Service " + sname + " (" + version + ")");
			BrowserPlus.require({ services: [{service:sname, version: version, minversion:version}], progressCallback: progressCallback }, 
				getRequireCallback(sname, fname, version, obj, prettyObj));
		}
	}

	function addVersion(name, version) {
		if (ServiceVersions[name]) {
			ServiceVersions[name].push(version);
		} else {
			ServiceVersions[name] = [version];
		}
	}

	// Action happens once BrowserPlus is initialized
	BPInstallerUI.start({pathToJar: "../installer"}, function(initres) {
		var services = [
			{service: "FileBrowse", version:"2"},
			{service: "InactiveServices", version:"1"},
			{service: "FileAccess", version: FileAccessVersion}
		];

		QueryServiceParam  = QueryParams["s"] || null;
		QueryVersionParam  = QueryParams["v"] || null;
		QueryFunctionParam = QueryParams["f"] || null;

		if (!initres.success) {
			// BrowserPlus did not start
			alert("ERROR - not able to initialize BrowserPlus.	Error is " +
				initres.error + (initres.verboseError ? " - " + initres.verboseError : ""));
			return;
		}

		BrowserPlus.require({services: services}, function(x) {
			if (!x.success) { 
				// Could not require services
				alert("ERROR - could not download services required to run Explorer. Error is " +
					x.error + (x.verboseError ? " - " + x.verboseError : ""));
				return;
			}

			// Get of available services
			BrowserPlus.InactiveServices.All({}, function(all) {
				if (all.success) {
					var s, i, len = all.value.length,allServices = {};

					// 1. Fetch all available services thru web services api
					for(i = 0; i < len; i++) {
						s = all.value[i];
						allServices[s.name + s.version] = true;
						addVersion(s.name, s.version);
					}

					// 2. Add builtin and "developer" services (service installed thru sdk)
					BrowserPlus.listActiveServices(function(r) {
						var sorted = [], services = [], slist=Y.one("#servicelist"), name, version, selected = -1;
						if (r.success) {
							for (i = 0, len = r.value.length; i < len; i++) {  
								name = r.value[i].name;
								version = r.value[i].version;

								// service may already have been added thru step 1 
								if (!allServices[name+version]) {
									if (name === "FileBrowse" && versionNum(version).substr(1) >= 3000000) {
										HasWritablePaths = true;
									}
									addVersion(name, version);
								}
							}
						}

						// create a select list [option] for each service
						for (s in ServiceVersions) {
							if (ServiceVersions.hasOwnProperty(s)) {
								services.push(opt(s));
								sorted.push(s); // a list of service names only, used to preselect an item
							}
						}


						// and sort services
						services.sort();
						sorted.sort();

						// There may be an "s=" parameter in the URL which means preselect a service.
						// Look for index in sorted list.
						for (i = 0; i < sorted.length; i++) {
							if (QueryServiceParam === sorted[i]) { 
								selected = i; 
								break;
							}
						}

						slist.setContent(services.join(""));
						Y.on('change', serviceListChangedEvent, "#servicelist");
						Y.on('change', versionListChangedEvent, "#versionlist");
						Y.on('change', functionListChangedEvent, "#functionlist");
						Y.on("click",  formClickedEvent, '#fields');//' input[type="button"]');
						Y.on("submit", formClickedEvent, '#frm');//' input[type="button"]');
						slist.set("selectedIndex", selected);
						if (selected > -1) {
							serviceListChangedEvent();
						}
					});
				}
			});
		});
	});
});
</script>
<script>

var _gaq = _gaq || [];
_gaq.push(['_setAccount', 'UA-11920567-1']);
_gaq.push(['_trackPageview']);

(function() {
	var ga = document.createElement('script');
	ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	ga.setAttribute('async', 'true');
	document.documentElement.firstChild.appendChild(ga);
})();

</script>
</body>
</html>