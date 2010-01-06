<?php
include("../../php/site.php");
include("../../php/db.php");
include("../../php/irc.php");

$RowsToShow = 50;
$results = null;

$irc = new IRC();
$max = $irc->get_max_id();

if (isset($_GET['id']) && ((int)$_GET['id']) > 0) {
    $results = $irc->get_rows_at($_GET['id'], $RowsToShow);
}

echo $irc->render_mobile($results, $max);
?>