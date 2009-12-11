<?php
include("/home/websites/browserplus/php/site.php");
include("/home/websites/browserplus/php/db.php");
include("/home/websites/browserplus/php/irc.php");

$RowsToShow = 50;
$results = null;

$irc = new IRC();
$max = $irc->get_max_id();

if (isset($_GET['id']) && ((int)$_GET['id']) > 0) {
    $results = $irc->get_rows_at($_GET['id'], $RowsToShow);
}

echo $irc->render_mobile($results, $max);
?>