<?php
include("../../php/site.php");
include("../../php/forum.php");

$f = new Forum();

// to make sure we're the ones calling
if (isset($_GET["s"]) && $_GET["s"] == $f->get_cache_secret()) {
    echo ($f->fetch_forums() ? "cached" : "error caching") . " forums\n";
}
