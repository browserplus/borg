#!/usr/bin/php
<?php
include("/home/websites/browserplus/php/site.php");
include("/home/websites/browserplus/php/forum.php");

// DOK_BASE - normally set in apache, but not available to scripts
putenv("DOK_BASE=/home/websites/browserplus/data");

$f = new Forum();
$secret = $f->get_cache_secret();

$host = ((get_cfg_var('bp_env') == "local") ? "borg" : "browserplus.org");
fetch("http://$host/site/cache_forums.php?s=$secret");
