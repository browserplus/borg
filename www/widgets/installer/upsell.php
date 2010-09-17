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
    <h1>Upsell Installation Example</h1>

    <p>
        If you use BrowserPlus to enhance your web page, but do not require it, you can call a method
        in the Installer that renders an <em>upsell</em> link.  When the user presses the upsell link,
        the Installer dialog will pop up the user will be guided through the installation process.
    </p>
    
    <p><b>Note:</b>
        If you don't see the upsell link, then BrowserPlus is already installed.
    </p>

    <p><span class="hilt">Turn on your sound / Turn off your tunes</span>.  Once installed, BrowserPlus is going to speak to you.</p>

    <p>If it's going to show, the upsell link is displayed below this paragraph.</p>
    
    <p id="install_area" style="background:#cce;padding:4px;display:none;text-align:center;">
        Enhance your experience with BrowserPlus.<br>
        &gt;&gt;&gt; <a id="install_link" href="#"> Install BrowserPlus </a>. &lt;&lt;&lt;
    </p>
    
    <p>See it above?  Nope, then BrowserPlus is already installed.</p>

	<script src="<?php echo BROWSERPLUS_MIN_JS; ?>"></script>	 
	<script src="http://bp.yahooapis.com/toolbox/installer/<?php echo INSTALL_WIDGET_VERSION; ?>/install-min.js"></script>	
    <script>
        function sayit(text) {
            BrowserPlus.TextToSpeech.Say({utterance: text}, function(){});
        }
        
        var initCB = function(res){
            if (res.success) {
                var tts = { service: "TextToSpeech", version: "1", minversion: "1.0.2"};
                BrowserPlus.require({services: [tts]}, function(res) {
                    if (res.success) {
                        sayit("Dude, BrowserPlus is installed!");
                    }
                });
            }
        };

		BrowserPlus.init({}, function (r) {
          if (!r.success && r.error === 'bp.notInstalled') {
            var ia = document.getElementById("install_area");
            ia.style.display = 'inline';    
            var lnk = document.getElementById("install_link");
            lnk.onclick = function () {
              BPTool.Installer.show({}, initCB);
              ia.style.display = 'none';    
              return false;  
            }
          } else {
            initCB(r);
          }
        });
        
    </script>

</body>
</html>
<!-- sp1-bplus-001.yos.sp1.yahoo.com compressed/chunked Mon Mar  8 16:28:22 PST 2010 -->