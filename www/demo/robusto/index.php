<?php 

require("../../../php/vars.php");

function svc($s) {
	echo "<a href=\"/docs/services/{$s}.html\">$s</a>";
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html lang="en">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8">
	<title>Mr. Robusto</title>
	<link rel="stylesheet" type="text/css" href="/demo/robusto/robusto.css" media="screen">
</head>
<body>
<h1>Mr. Robusto: Resumable Uploads</h1>

<p><em>

Parallel uploads, archiving, chunking, compression, md5 - so many options to for
files and uploading with BrowserPlus... And now for those with slow, unreliable
connections, <strong>resumable uploads</strong>.

</em></p>
	
<p>

To start your resumable upload, select a large file and press <em>Start</em>. Hit
<em>Stop</em> or navigate away at anytime to simulate an unreliable connection. When
you come back to the page, reselect the same file. The "chunks" already uploaded
appear green and won't be re-uploaded.  Once all chunks reach the server, the original
file is recreated.

</p>

<p>
	
For more details, visit the <a href="/docs/demos/resumble_uploads.html">resumable uploads</a>
demo page.

</p>

<p>&nbsp;</p>

	<div id="ctrlpanel">
		<form id="frm">
			<div id="actions" class="fs">
				Upload Controls
				<div class="button_container"> 
					<button id="b_file" disabled>Select File</button>
					<button id="b_start" disabled>Start</button>
					<button id="b_stop" disabled>Stop</button>
				</div>
			</div>
		</form>
	
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
	</div>
	
	
	<script src="http://bp.yahooapis.com/<?php echo BROWSERPLUS_JS_VERSION; ?>/browserplus-min.js"></script>
	<script src="http://yui.yahooapis.com/3.0.0/build/yui/yui-min.js"></script>
	<script src="/js/json2.js"></script>
	<script src="/demo/robusto/robusto.js"></script>
</body>
</html>
