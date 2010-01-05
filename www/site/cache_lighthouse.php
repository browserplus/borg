<?php
include("/home/websites/browserplus/php/site.php");
include("/home/websites/browserplus/php/lighthouse.php");

$bugs = new Lighthouse();

// to make sure we're the ones calling
if (isset($_GET["s"]) && $_GET["s"] == $bugs->get_cache_secret()) {
    // fetches bugs
    $bugs->get_all_bugs();
}
