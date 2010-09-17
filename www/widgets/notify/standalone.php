<?php require("../../../php/vars.php") ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  	<title>Desktop Notification Example</title>
 	<style type="text/css" media="screen">
    h1 {font-size:14pt;}
    h2 {font-size:12pt;margin-top:30pt;color:#369;}
    p {font-size:11pt;}
  	</style>
</head>
<body>
    <h1>Notify Examples</h1>
    <p>Show native notifications on your desktop when BrowserPlus is installed.</p>
    <h2>Quick Test</h2>
    <p>Click on the button below to see the message.</p>
    <input id="b1" type="button" value="Push Me!">

    <form name="f">
    <h2>Customize the message</h2>
    <p>1. Write a message to be displayed:</p>
    <input id="customMessage" type="text" size="80" value="That would be really cool if I could display native notifications from my webpage.">
    <p>2. Select an image:</p>
    <input type="radio" name="img" value="1" checked><img src="http://l.yimg.com/a/i/us/bp/s/1.106/tool/notify-icon1.png" alt="snow">
    <input type="radio" name="img" value="2"><img src="http://l.yimg.com/a/i/us/bp/s/1.106/tool/notify-icon2.png" alt="moon">
    <input type="radio" name="img" value="3"><img src="http://l.yimg.com/a/i/us/bp/s/1.106/tool/notify-icon3.png" alt="sun">
    <input type="radio" name="img" value="4"><img src="http://l.yimg.com/a/i/us/bp/s/1.106/tool/notify-icon4.png" alt="partial sun">
    <p>3. Show the message:</p>
    <input id="b2" type="button" value="Show it!">
    </form>

	<script src="<?php echo BROWSERPLUS_MIN_JS; ?>"></script>	 
	<script src="http://bp.yahooapis.com/toolbox/notify/<?php echo NOTIFY_WIDGET_VERSION; ?>/notify-min.js"></script>	
    <script>
var notify = BPTool.Notify.create();

function getSelectedImage(radio) {
    var i, len = radio.length;
    for (i = 0; i < len; i++) {
        if (radio[i].checked) {
            return radio[i].nextSibling.src;
        }
    }
    
    return "";
}

document.getElementById('b1').onclick=function(e) {
    notify.show("Button 1 Clicked", "Welcome to BrowserPlus.  You just clicked on button 1!", "http://l.yimg.com/a/i/us/we/31/b/32.gif");
};

document.getElementById('b2').onclick=function(e) {
    var msg = document.getElementById("customMessage").value;
    var imgsrc = getSelectedImage(document.forms['f']);
    notify.show("Button 2 Clicked", msg, imgsrc);
};
    </script>
</body>
</html>