<?php
include "/home/websites/browserplus/php/dok.php";

$request = isset($_GET['__route__']) ? "/" . $_GET['__route__'] : "/";

$conf = array(
    "baseurl" => "/support",      // site url
    "basename" => "Support",
    "pages"   => "pages/support", // under getenv("DOK_BASE")
    "vars"    => array("active" => "Support")
);

dok($conf, $_GET["__route__"]);
?>
