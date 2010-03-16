<?php
include "../../php/dok.php";

$conf = array(
    "baseurl" => "/docs",      // site url
    "basename" => "Docs",
    "pages"   => "pages/docs", // under getenv("DOK_BASE")
    "vars"    => array("active" => "Docs")
);

$request = isset($_GET['__route__']) ? $_GET['__route__'] : "";
dok($conf, $request);
?>
