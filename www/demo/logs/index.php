<?php 
require("../../../php/vars.php");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html lang="en">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8">
	<title>Log File Viewer</title>
	<link rel="stylesheet" type="text/css" href="style.css">
	<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/combo?3.2.0/build/tabview/assets/skins/sam/tabview.css&3.2.0/build/widget/assets/skins/sam/widget.css&3.2.0/build/cssfonts/fonts-min.css">
</head>
<body class="yui3-skin-sam  yui-skin-sam"> 
<h1>View BrowserPlus Log Files</h1>

<p>View tabs</p>
<div id="demo"></div>

	<script type="text/javascript" src="http://yui.yahooapis.com/3.2.0/build/yui/yui-min.js"></script> 
	<script src="<?php echo BROWSERPLUS_MIN_JS; ?>"></script>

<script type="text/javascript"> 
YUI().use("yui", "tabview", function(Y) {
	/*
    var tabview = new Y.TabView({
        children: [{
            label: 'foo',
            content: '<p>foo content</p>'
        }, {
            label: 'bar',
            content: '<p>bar content</p>'
        }, {
            label: 'baz',
            content: '<p>baz content</p>'
        }]
    });
 
    tabview.render('#demo');
    tabview.selectChild(2);
	*/
	

	function error(s, err) {
		console.log("Error in " + s + ": " + err.error + (err.verboseError ? " - " + err.verboseError : ""));
	}

	function getLogFiles() {
		console.log("getLogFiles");
		BrowserPlus.LogAccess.get({}, function(logs){
			var i, len, children = [], item, tabview;
			if (logs.success) {
				for (i = 0, len = logs.value.length; i < len; i++) {
					item = logs[i];
					console.log("item:");
					console.log(item);
					children.push({label:item.name, content: "test content: " + i});
				}
				tabview = new Y.TabView({children:children});
			    tabview.render('#demo');
			} else {
				error("LOGACCESS", logs)
			}
		});
	}


	BrowserPlus.init(function(init) {
		if (init.success) {
			console.log("init");
			// lots of different services for all the various upload options
			BrowserPlus.require({
				services: [
					{service: 'FileAccess',   version: "2", minversion: "2.0.1" },
					{service: 'LogAccess',   version: "1", minversion: "1.0.0"}
				]},
				function(require) {
					console.log("require");
					if (require.success) {
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
