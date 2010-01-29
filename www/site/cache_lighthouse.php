<?php
include("../../php/site.php");
include("../../php/lighthouse.php");

$bugs = new Lighthouse(1);

// to make sure we're the ones calling
if (isset($_GET["s"]) && $_GET["s"] == $bugs->get_cache_secret()) {
    // fetches bugs
    $bugs->get_all_bugs();
}
