<?php
$uploadErrors = array(
    UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
    UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
    UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
    UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
    UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
    UPLOAD_ERR_EXTENSION => 'File upload stopped by extension.',
);

header("Content-type: text/plain");
$output = array();
$postvars = array();
foreach ($_POST as $k => $v) {
    $postvars[$k] = $v;
}

$output["post_variables"] = $postvars;
$output["cookies"] = $_COOKIE;


$fvars = array();

foreach($_FILES as $name => $file) {

    $ec = $_FILES[$name]['error'];

    if($ec !== UPLOAD_ERR_OK)
    {
        $fvars[$name] = array("error" => (isset($uploadErrors[$ec]) ? $uploadErrors[$ec] : "unknown error"));
    } else {
        $fvars[$name] = array(
	        "name" => $_FILES[$name]['name'],
	        "size" => $_FILES[$name]['size'],
            "md5"  => md5(file_get_contents($_FILES[$name]['tmp_name']))
        );
    }
}

$output["files"] = $fvars;

$json = json_encode($output);

//error_log(print_r($_FILES, 1), 3, "/tmp/dbg.txt");
//error_log("$json\n", 3, "/tmp/dbg.txt");

echo $json;
?>
