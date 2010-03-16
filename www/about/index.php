<?php
include "../../php/dok.php";

$conf = array(
    "baseurl" => "/about",      // site url
    "basename" => "About",
    "pages"   => "pages/about", // under getenv("DOK_BASE")
    "vars"    => array("active" => "About")
);

$request = isset($_GET['__route__']) ? $_GET['__route__'] : "";
dok($conf, $request);
?>
