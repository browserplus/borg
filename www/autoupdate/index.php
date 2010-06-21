<?php
include "../../php/dok.php";

$conf = array(
    "baseurl" => "/autoupdate",      // site url
    "basename" => "Autoupdate",
    "pages"   => "pages/autoupdate", // under getenv("DOK_BASE")
    "vars"    => array("active" => "Home")
);

$request = isset($_GET['__route__']) ? $_GET['__route__'] : "";
dok($conf, $request);
?>
