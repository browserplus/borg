<?php

define("MAX", 64*1024);

$dir = "/tmp/diagnose";
$logs = (isset($_POST["logs"]) ? $_POST["logs"] : null);

if ($logs && strlen($logs) <= MAX) {
	if ((is_dir($dir) && is_writable($dir)) || mkdir($dir, 0777, true)) {
		$filename = tempnam($dir, "d1_" . date("Ymd_H_i_s") . "_");
		file_put_contents($filename, $logs);
		echo "OK";
		exit;
	} 
}

echo "ERROR";
?>
