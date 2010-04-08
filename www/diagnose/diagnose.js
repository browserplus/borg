function runDiagnostics(cb) 
{
	txt = "";
	logfiles = [ ];

	function allDone(interestingness) {
		cb(interestingness, txt);
	}

	function allComplete() {
		// everythings done!  let's see if there are errors or warnings that are interesting
		var numErrs = 0;
		for (var i=0; i < logfiles.length; i++) {
			numErrs += logfiles[i].errors.length;
			// append the entire logfiles
			txt += "\n\nContents of " + logfiles[i].handle.name + ":\n\n";
			txt += logfiles[i].contents;
		}

		allDone(numErrs ? "completeWithErrors" : "completeNoErrors");
	}


	BrowserPlus.init(function(r) {
		function appendMsg(msg) {
			txt += msg += "\n\n";
		}

		function appendServices(s) {
			var msg = "";
			for (var i=0; i < s.length; i++) {
				msg += (s[i].service ? s[i].service : s[i].name) + " - " + s[i].version + "\n";
			}
			appendMsg(msg);
		}


		function processLogs() {
			// iterate through all log files to find the first one with no content
			for (var i=0; i < logfiles.length; i++) {
				if (logfiles[i].contents === "") {
					// cap max read amt to 128k
					var offset = 0;
					var readAmt = 128 * 1024;
					if (logfiles[i].handle.size > readAmt) {
						offset = logfiles[i].handle.size - readAmt;
					}
					BrowserPlus.FileAccess.read(
						{
							file: logfiles[i].handle,
							offset: offset,
							size: readAmt
						},
						(function() {
							var logFileNum = i;
							return function(r) {
								if (!r.success) {
									appendMsg("error processing logs: " + r.error + ": " + r.verboseError);
									allDone("logReadError");
								} else {
									var arr = r.value.split("\n");
									var errs = [ ];
									for (var i=0; i < arr.length; i++) {
										if (arr[i].indexOf("ERROR") >= 0) {
											errs.push(arr[i]);
										}
									}
									if (errs.length) {
										appendMsg(errs.length + " errors in " + logfiles[logFileNum].handle.name);
										appendMsg(errs.join("\n"));
									} else {
										appendMsg("no errors in " + logfiles[logFileNum].handle.name);
									}
									logfiles[logFileNum].contents = r.value;
									logfiles[logFileNum].errors = errs;
									processLogs();
								}
							}
						})());
					break;
				}
			}
			if (i == logfiles.length) {
				// all done processing logs!
				allComplete();
			}

		}

		function gotLogs(l) {
			if (!l.success) {
				appendMsg("error fetching logs: " + l.error + ": " + l.verboseError);
				allDone("logFetchError");
			} else {
				var msg = "";

				for (var i=0; i < l.value.length; i++) {
					var logFile = l.value[i];
					msg += logFile.name + " " + (logFile.size / 1024.0).toFixed(2) + "k";
					msg += "\n";
					logfiles.push( {
						handle: logFile,
						errors: [ ],
						contents: "" 
					} );
				}
				appendMsg(msg);
				processLogs();
			}
		}

		function gotLogAccess(r) {
			if (r.success) {
				appendMsg("Log Access present: ");
				appendServices(r.value);

				BrowserPlus.LogAccess.get({}, gotLogs);
			} else {
				appendMsg("\n\nCouldn't install LogAccess: " + r.error + ": " + r.verboseError);
				allDone("requireServicesError");
			}
		}

		if (r.success) {
			var pi = BrowserPlus.getPlatformInfo();
			appendMsg("Yahoo! BrowserPlus installed, v" + pi.version + " - " + pi.os + " - " + pi.buildType);
			BrowserPlus.listActiveServices(function(as) {
				appendMsg("Installed Services: ");
				appendServices(as.value);
				BrowserPlus.require({services: [
					{ service: "LogAccess", version: "1" },
					{ service: "FileAccess", version: "2" } ]}, gotLogAccess);
			});
		} else if (r.error == "bp.notInstalled") {
			appendMsg("BrowserPlus isn't installed");
			allDone("notInstalled");
		} else if (r.error == "bp.unsupportedClient") {
			// Uh oh, they're running a browser we don't yet support.  We should
			// probably disable features that require browserplus, and rendering
			// an installation link for BrowserPlus is useless.
			appendMsg("BrowserPlus isn't supported on your machine");
			allDone("notSupported");
		} else {
			// yikes!  Unexpected error!
			appendMsg("BrowserPlus hit an error: " + r.error + ": " + r.verboseError);
			allDone("browserplusError");
		}
	});
}