<?php
require("../../../php/vars.php");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html lang="en">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8">
	<title>New Mail Message</title>
	<link rel="stylesheet" type="text/css" href="compose.css" media="screen">
</head>
<body>
	<div id="tabs">
		<ul>
			<li><b>What's New</b></li>
			<li><b>Inbox</b> 648 emails</li>
			<li id="selected">New Message</li>
		</ul>
	</div>
	<div class="buttons">
		<button>Send</button>
		<button>Attach</button>
		<button>Save Draft</button>
		<button>Spelling</button>
		<button>Cancel</button>
	</div>
	<form>
		<dl>
			<dt><label>To:</label></dt>
			<dd><input id="mailto" type="text"></dd>
		</dl>
		<dl>
			<dt><label>Cc:</label></dt>
			<dd><input type="text"></dd>
		</dl>
		<dl>
			<dt><label>Subject:</label></dt>
			<dd><input type="text"></dd>
		</dl>
		<dl>
			<textarea></textarea>
		</dl>

		<div class="attach">
			<div class="attachtitle">
				<b>Attach</b>
			</div>
			<div id="attacharea"></div>
		</div>

	</form>
	<script src="http://yui.yahooapis.com/3.1.0/build/yui/yui-min.js"></script>
	<script src="<?php echo BROWSERPLUS_MIN_JS; ?>"></script>
	<script src="attach.js"></script>
</body>
</html>

