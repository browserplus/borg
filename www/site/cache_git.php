<?php
include("../../php/site.php");
include("../../php/db.php");
include("../../php/git.php");

$g = new GIT();

// to make sure we're the ones calling
if (isset($_GET["s"]) && $_GET["s"] == $g->get_cache_secret()) {
    echo ($g->fetch_platform_issues() ? "cached" : "error caching") . " forums\n";
}
