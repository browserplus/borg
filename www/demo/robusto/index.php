<?php 

require("../../../php/vars.php");

function svc($s) {
	echo "<a href=\"/docs/services/{$s}.html\">$s</a>";
}

// remember cookie for 2 days
$cookieTime = time()+(2*24*3600);

// check for uuid in cookie first
$cookieName = "robusto";

if (preg_match("|/([0-9a-f]{32})$|", $_SERVER["REQUEST_URI"], $a1)) {
	// get uuid from url and save it in cookie
	$uuid = $a1[1];
	setcookie($cookieName, $uuid, $cookieTime);
} else if (isset($_COOKIE[$cookieName]) && preg_match("|^([0-9a-f]{32})$|", $_COOKIE[$cookieName], $a1)) {
	// cookie already has uuid
	$uuid = $a1[1];
} else {
	// no uuid, create and save it
	$uuid = md5(uniqid(rand(), true));
	setcookie($cookieName, $uuid, $cookieTime);
}

// URL to get back, cookie or not
$url = "http://" . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/index/$uuid";
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html lang="en">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8">
	<title>Mr. Robusto</title>
	<link rel="stylesheet" type="text/css" href="/demo/robusto/robusto.css" media="screen">
</head>
<body>
<script type="text/javascript">
		var RobustoUUID = "<?php echo $uuid; ?>";
</script>
<h1>Mr. Robusto: Resumable Uploads</h1>

	<p><em>

 		Parallel uploads, archiving, chunking, compression, md5 - so many options
        to for files and uploading with BrowserPlus... And now for those with
        slow, unreliable connections, <strong>resumable uploads</strong>.

	</em></p>
	
	<p>

		For those with unreliable connections, uploading is a frustrating, all or
        nothing process. By breaking a file into multiple, smaller chunks and
        adding a little server-side logic, you can easily add resumable uploads to
        your website The key services for resumable uploads are <?php
        svc("FileChecksum") ?> MD5 (fingerprint of file contents) <?php
        svc("FileAccess")?> <em>chunks</em> (breaks large file into small pieces)
        and <?php svc("Uploader")?>.

	</p>
	<p>

		Select a large file and press <em>Start</em> to begin. Press <em>Stop</em>
        or navigate away at anytime (if you navigate away you'll have to reselect
        the same file). The table below indicates the upload status of each of the
        various chunks.

	</p>

	<form id="frm">
		<fieldset id="actions" class="fs">
			<legend>Resumable Upload</legend>
			<div class="button_container"> 
				<button id="b_file" disabled>Select File</button>
				<button id="b_start" disabled>Start</button>
				<button id="b_stop" disabled>Stop</button>
			</div>
			<table class="detail_container">
				<tr>
					<th>File:</th>
					<td id="d_file">&nbsp;</td>
				</tr>
				<tr>
					<th>Size:</th>
					<td id="d_size">&nbsp;</td>
				</tr>
				<tr>
					<th>Chunks:</th>
					<td id="d_chunks">&nbsp;</td>
				</tr>
				<tr>
					<th>Status:</th>
					<td id="d_status">&nbsp;</td>
				</tr>
				<tr>
					<th>Chunks:</th>
					<td><div id="chunks">&nbsp;</div></td>
			</table>
		</fieldset>
	</form>
	
	<h2>Details</h2>

	<p>
		
		The normal server-side upload script has been modified to...
	
	</p>

	<script src="http://bp.yahooapis.com/<?php echo BROWSERPLUS_JS_VERSION; ?>/browserplus-min.js"></script>
	<script src="http://yui.yahooapis.com/3.0.0/build/yui/yui-min.js"></script>
	<script src="/js/json2.js"></script>
	<script src="/demo/robusto/robusto.js"></script>
</body>
</html>
