<?php
include("/home/websites/browserplus/php/site.php");
include("/home/websites/browserplus/php/twitter.php");

$twitter = new Twitter();
echo ($twitter->fetch_user_tweets("browserplus") ? "cached" : "error caching") . " user feed\n";
echo ($twitter->fetch_user_tweets("browserplus") ? "cached" : "error caching") . " search feed\n";

