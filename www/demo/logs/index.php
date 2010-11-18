<?php 
require("../../../php/vars.php");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html lang="en">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8">
	<title>Log File Viewer</title>
	<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/combo?3.2.0/build/tabview/assets/skins/sam/tabview.css&3.2.0/build/widget/assets/skins/sam/widget.css&3.2.0/build/cssfonts/fonts-min.css">
	<style type="text/css" media="screen">
	body{margin:10px;}
	h1 { margin-top:0em;font: bold 16pt	 'Courier New', Courier, monospace !important;}
	pre {font-size:93%;}
	.yui3-tabview-panel {overflow:scroll;height:600px;}
	</style>
</head>
<body class="yui3-skin-sam	yui-skin-sam"> 
<h1>BrowserPlus Log Files</h1>

<div id="demo"></div>
	<button id="uploadIt">Upload Log Files</button>
	<script type="text/javascript" src="http://yui.yahooapis.com/3.2.0/build/yui/yui-min.js"></script> 
	<script src="<?php echo BROWSERPLUS_MIN_JS; ?>"></script>

<script type="text/javascript"> 
YUI().use("yui", "tabview", function(Y) {

	var UPLOAD_URL = "http://browserplus.org/misc/upload.php";
	var tabview = new Y.TabView({
		srcNode: '#demo'
	});
	
	function error(s, err) {
		alert("Error in " + s + ": " + err.error + (err.verboseError ? " - " + err.verboseError : ""));
	}

	function uploadFiles() {
		BrowserPlus.LogAccess.get({}, function(logs){
			var i, len, files = {};

			if (!logs.success) return;

			for (i = 0, len = logs.value.length; i < len; i++) {
				files["file"+i] = logs.value[i];
			}
			BrowserPlus.FileTransfer.upload({
				files: files,
				url:   UPLOAD_URL,
				progressCallback: function(p) {
					console.log("upload progress: " + p.filePercent + "%");
				}
			}, function(){});
		});
	}
	
	function getLogFiles() {
		BrowserPlus.LogAccess.get({}, function(logs){
			var i, len, item;
			if (logs.success) {
				// each item is a file handle
				for (i = 0, len = logs.value.length; i < len; i++) {
					item = logs.value[i];

					// cap max read amt to 768KB
					var offset = 0;
					var readAmt = 768 * 1024;

					if (item.size > readAmt) offset = item.size - readAmt;

					BrowserPlus.FileAccess.read({file: item, offset: offset,size: readAmt },
						(function() {
							var file = item;
							var start = offset;
							return function(r) {
								var str, i;
								if (r.success) {
									str = r.value;
									// inform user when log file is chopped
									if (start != 0) {
										str = "... Log file too large, showing most recent " + (readAmt/1024) + "KB ...\n" + 
											str.substr(str.indexOf("\n"));
									}

									tabview.add({label:file.name, content: "<pre>"+str+"</pre>"}, tabview.size());

									// need to render tabview when we get data back for first tab
									if (tabview.size() == 1) tabview.render();
								}
							};
						})());
				} // for 
			} else {
				error("LogAccess", logs);
			}
		});
	}


	BrowserPlus.init(function(init) {
		if (init.success) {
			// lots of different services for all the various upload options
			BrowserPlus.require({
				services: [
					{service: 'FileAccess',	  version: "2", minversion: "2.0.1" },
					{service: 'FileTransfer', version: "1", minversion: "1.1.1"},
					{service: 'LogAccess',	  version: "1", minversion: "1.0.0"}
				]},
				function(require) {
					if (require.success) {
						Y.on("click", function() { uploadFiles(); }, "#uploadIt");
						getLogFiles();
					} else {
						error("REQUIRE", require);
					}
			});
		} else {
			error("INIT", init);
		}
		
	});


});
</script> 

</body>
</html>
