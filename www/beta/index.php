<?php
include "../../php/dok.php";

$request = isset($_GET['__route__']) ? "/" . $_GET['__route__'] : "/";

$conf = array(
    "baseurl"  => "/beta",      // site url
    "basename" => "Beta",
    "pages"    => "pages/beta", // under getenv("DOK_BASE")
    "vars"     => array("active" => "Docs")
);

dok($conf, $_GET["__route__"]);
?>
