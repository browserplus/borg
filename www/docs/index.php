<?php
include "/home/websites/browserplus/php/dok.php";

$request = isset($_GET['__route__']) ? "/" . $_GET['__route__'] : "/";

$conf = array(
    "baseurl" => "/docs",      // site url
    "basename" => "Docs",
    "pages"   => "pages/docs", // under getenv("DOK_BASE")
    "vars"    => array("active" => "Docs")
);

dok($conf, $_GET["__route__"]);
?>
