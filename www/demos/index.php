<?php
include "../../php/dok.php";

$conf = array(
    "baseurl" => "/demos",      // site url
    "basename" => "Demos",
    "pages"   => "pages/demos", // under getenv("DOK_BASE")
    "vars"    => array("active" => "Demos")
);

$request = isset($_GET['__route__']) ? $_GET['__route__'] : "";
dok($conf, $request);
?>
