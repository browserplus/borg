<?php require("../../../php/vars.php") ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <title>Installation Example</title>
  <style type="text/css" media="screen">
	body {font-family:arial,sans-serif;}
	h1 {font-size:14pt;}
	.hilt {background:#ff3;padding:2px;font-weight:bold;}
  </style>
</head>
<body>
	<h1>Popup Installation Example</h1>

	<p>
		The Install tool guides the user through a step-by-step BrowserPlus installation process.
		If BrowserPlus is not installed, you should see a dialog guiding you to install BrowserPlus.
	</p>

	<p>
		<b>Note:</b> If you don't see the dialog, then BrowserPlus is already installed.
	</p>

	<p>
		<span class="hilt">Turn on your sound / Turn off your tunes</span>. 
		Once installed, BrowserPlus is going to speak to you.
	</p>

	<script src="<?php echo BROWSERPLUS_MIN_JS; ?>"></script>	 
	<script src="http://bp.yahooapis.com/toolbox/installer/<?php echo INSTALL_WIDGET_VERSION; ?>/install-min.js"></script>	
	<script>
		function sayit(text) {
			BrowserPlus.TextToSpeech.Say({utterance: text}, function(){});
		}

		BPTool.Installer.show({}, function(res){
			if (res.success) {
				var tts = { service: "TextToSpeech", version: "1", minversion: "1.0.2"};
				BrowserPlus.require({services: [tts]}, function(res) {
					if (res.success) {
						sayit("Dude, BrowserPlus is installed!");
					}
				});
			}
		});
	</script>
</body>
</html>