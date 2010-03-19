<?php

// 
// basic inputs (GET - can check for existence of file with these):
//   md5  - hash of file contents
//   name - name of file
//
// file upload inputs (POST - to upload chunks)
//   file   - file chunk
//   chunk  - chunk number
//   chunks - total number of chunks
//
// output:
//   status - new|partial|complete|error
//   value  - string, value of which depends on 'status'
//      new      - empty string
//      partial  - string list of completed chunks "1,2,3,7"
//      complete - url location of file
//      error    - error message string
//
//
// chunk file pattern: chunk_0000_0000.bin

define("DBG_ME", false);
define("DEMO_MODE", true); // in demo mode, just save empty files
define("UPLOAD_BASE" , "/tmp/robusto"); // base dir where files are written
define("UPLOADED_URL", "/demo/robusto/just_a_demo/%s/%s");

// optionally write to debug file
function dbg($str) {
	if (DBG_ME) {
		error_log("${str}\n", 3, "/tmp/dbg.txt");
	}
}

// return assoc array of files 
//     "chunks" is all chunk files in given directory
//     "files" is everything else
function getFilesOnDisk($dir) {
	$chunks = array();
	$files  = array();
	if (is_dir($dir) && $dh = opendir($dir)) {
		while (($file = readdir($dh)) !== false) {
			if (preg_match('/^chunk_\d{4}_\d{4}\.bin$/', $file)) {
				$chunks[] = $file;
			} else if (!is_dir("${dir}/${file}")){
				$files[] = $file;
			}
		}
	}
	
	return array("chunks" => $chunks, "files" => $files);
}

// Output JSON return message and exit
function result($status, $value) {
	$json = json_encode(array("status" => $status, "value" => $value));
	dbg($json);
	echo $json;
	exit;
}

function resultComplete($md5, $filename) {
	$url = sprintf(UPLOADED_URL, $md5, $filename);
	result("complete", $url);
}

function resultNew() {
	result("new", "");
}

// array of chunk file names
function resultPartial($files) {
	$arr = array();
	foreach($files as $f) {
		// chunk file pattern: chunk_0000_0000.bin
		preg_match("/chunk_(\d{4})/", $f, $matches);
		$arr[] = (int)$matches[1];
	}

	result("partial", join(",", $arr));
}

function resultError($msg) {
	result("error", $msg);
}

// output is json, but text/plain makes for better debugging
header('Content-type: text/plain; charset=UTF-8');


// Get Params - all apis need to check if file previously uploaded
$md5  = isset($_GET["md5"])  ? $_GET["md5"]  : "";
$filename = isset($_GET["name"]) ? preg_replace('/[^\w\._-]+/', '', $_GET['name']) : "";

// if the basics are specified, don't go on
if (!preg_match("/^[a-f0-9]{32}$/i", $md5)  || // md5 is hex and 16 bytes
	empty($filename)) 
{
	resultError("Invalid parameters, expecting md5 and name"); // EXITs
}


if (!isset($_FILES['file'])) {

	// ---------------------------------------------------------------
	// Not Uploading - query to see what chunks are uploaded
	// ---------------------------------------------------------------

	$dir = UPLOAD_BASE . "/${md5}";

	$fod = getFilesOnDisk($dir);
	$chunks = $fod["chunks"];
	$files  = $fod["files"];

	if (count($files) == 1) {
		// file already on disk - TODO compare name
		$name = $files[0];
		if (DEMO_MODE || md5("${dir}/${name}") == $md5) {
			// md5 sig matches - great
			resultComplete($md5, $name); // EXITs
		}
	} else if (count($chunks) > 0) {
		resultPartial($chunks);  // EXITs
	}

	// for any error conditions, just ask for file again
	resultNew(); // EXITs

} else {

	// ---------------------------------------------------------------
	// File Uploaded!
	// ---------------------------------------------------------------

	$chunk  = $_POST["chunk"];    // chunk number
	$chunks = $_POST["chunks"];   // number of chunks
	$file   = $_FILES['file'];    // uploaded file

	//
	// Make sure all parameters are valid before entering
	//
	if (is_uploaded_file($file['tmp_name'])    && // file uploaded via HTTP POST
		$file['error'] == 0                    && // no erros
		$file['size'] <= (2*1024*1024)         && // 2MB or less
		preg_match("/^[0-9]{1,4}$/", $chunk)   && // chunk num is reasonable int
		preg_match("/^[0-9]{1,4}$/", $chunks)  && // num chunks is reasonable int
		$chunk < $chunks)                         // chunk num is reasonable
	{
		// there's a dir to work with
		$dir = UPLOAD_BASE . "/${md5}";
		if ((is_dir($dir) && is_writable($dir)) || mkdir($dir, 0777, true)) {

			// name of chunk file is "chunk_000x_000y.bin"
			// where "x" is chunk num and "y" is num chunks
			$chunkName = sprintf("chunk_%04d_%04d.bin", $chunk, $chunks);

			if (DEMO_MODE) {
				// Don't actually save file contents, just write empty file
				$file_saved = (file_put_contents("${dir}/${chunkName}", "") !== false);
			} else {
				$file_saved = move_uploaded_file($file['tmp_name'], "${dir}/${chunkName}");
			}

			if ($file_saved) {
				$fod = getFilesOnDisk($dir);
				$fileChunks = $fod["chunks"];
				$files = $fod["files"];

				// remove suspicious characters in file name
				$filename = preg_replace('/[^\w\._-]+/', '', $filename);
				if (!$filename) { $filename = "file.bin";}

				// if file already on disk, we're done!!!
				if (count($files) > 0 ) {
					// file already there?
					for ($i = 0, $cnt = count($files); $i < $cnt; $i++) {
						if ($filename == $files[$i]) {
							// file is there, we're done!
							resultComplete($md5, $filename);	 // EXITs
						}
					}
				}

				// if we have all the chunks, reassemble file
				if (count($fileChunks) == $chunks) {

					// concat files in order
					sort($fileChunks);

					// destination file gets original name
					$dpath = "${dir}/${filename}";
					$dest = fopen($dpath, "wb");

					// For each chunk, concat it to dest.
					// Note that we don't need to do anything special for demo mode ... we'd
					// just be concatting empty files
					for($i = 0; $i < count($fileChunks); $i++) {
						$path = "${dir}/" . $fileChunks[$i];
						$src = fopen($path, "rb");
						
						while (!feof($src)) { fwrite($dest, fread($src, 8192));	}

						@fclose($src);
					}
					@fclose($dest);

					// delete the chunks ... no longer needed
					for($i = 0; $i < count($fileChunks); $i++) {
						$path = "${dir}/" . $fileChunks[$i];
						@unlink($path); 
					}

					if (DEMO_MODE || md5($dpath) == $md5) {
						// we're done
						resultComplete($md5, $filename); // EXITs
					} else {
						resultError("MD5 signature does not match between client and server"); // EXITs
					}

					// cron job can delete completed uploads

				} else {
					// Not done uploading all the chunks, return list of chunks we have
					resultPartial($fileChunks);
				}

			} else {
				resultError("Could not save file chunk"); // EXITs
			}
		} else {
			resultError("Could not create uploads directory"); // EXITs
		}
	} else {
		resultError("Invalid file upload parameters"); // EXITs
	}
}