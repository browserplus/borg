<?php 
require("../../../php/vars.php");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>JSON Inspector (BrowserPlus&trade;)</title>
<link rel="stylesheet" type="text/css" href="SyntaxHighlighter.css" media="screen" />
<style type="text/css" media="screen">
#url { width: 700px; margin-bottom: 5px;}
body { text-align: left; color:#333;}
#sample_title { padding: 10px 0 10px 0px; font:bold 123.1% verdana;}

.dp-highlighter { overflow-y: hidden; }
.dp-highlighter ol li {
  list-style-image:none;
  list-style-position:outside !important;
  list-style-type:decimal;
}        
</style>
</head>
<body class="yui-skin-sam">
    <img src="/i/bp-logo-sm.png" style="margin:5px 0;">

    <div id="sample_title">JSON Inspector</div>
    <form id="url_input_form" name="f">
        <input type="text" id="url" value="">
        <input id="fetch" type="submit" value="Fetch">
    </form>

    <div id="output_container">
        <pre id="jsonOutput" name="code" class="javascript"></pre> 
    </div>

    <div id="bpinstall" style="display:none"></div>

	<script src="<?php echo BROWSERPLUS_MIN_JS; ?>"></script>
	<script src="/installer/install-min.js"></script> 
    <script src="bpDpSyntaxHighlighter.js"></script>
    <script src="json2-min.js"></script>
    <script src="jsonrequest.js"></script>
</body>
</html>