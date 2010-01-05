<?php
include "/home/websites/browserplus/php/dok.php";

$request = isset($_GET['__route__']) ? "/" . $_GET['__route__'] : "/";

$conf = array(
    "baseurl" => "/about",      // site url
    "basename" => "About",
    "pages"   => "pages/about", // under getenv("DOK_BASE")
    "vars"    => array("active" => "About")
);

dok($conf, $_GET["__route__"]);
?>
