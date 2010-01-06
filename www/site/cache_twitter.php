<?php
include("../../php/site.php");
include("../../php/twitter.php");

$t = new Twitter();

// to make sure we're the ones calling
if (isset($_GET["s"]) && $_GET["s"] == $t->get_cache_secret()) {
    echo ($t->fetch_user_tweets("browserplus") ? "cached" : "error caching") . " user feed\n";
    echo ($t->fetch_search_tweets("browserplus") ? "cached" : "error caching") . " search feed\n";
}
