<?php
include("../../php/site.php");
include("../../php/db.php");
include("../../php/irc.php");

define("IRC_MAX_ROWS", 150);

$irc = new IRC();

$search_term = "";
$max_id = $irc->get_max_id();
$blurb = false;
$in_search = false;

if (isset($_GET['search'])) {
    $in_search = true;
    $id = $max_id;
    $search_term = h($_GET['search']);

    // don't need to escape search below since we're using PDO
    $results = $irc->find_rows($_GET['search'], IRC_MAX_ROWS);
    $rcount  = count($results);

    if ($rcount == 0) {
        $blurb = "No IRC results match that query.";
    } else {
        $blurb = "Results that match '" . h($_GET['search']) . "':";
    }

    $rcount  = count($results);
    $nav  = l("Current", "/discuss/");

} else {
    if (isset($_GET['id'])) {
        $id = (int)($_GET['id']);
        $id = (($max_id - $id) < 0) ? $max_id : $id;
    } else {
        $id = $max_id;
    }

    $results = $irc->get_rows($id, IRC_MAX_ROWS);
    $rcount = count($results);

    if ($rcount > 0) {
        $first_id = $results[0]["id"];
        $last_id  = $results[count($results)-1]["id"];
        $next_id  = $last_id + IRC_MAX_ROWS - 1;

        $blurb = "To chat with the BrowserPlus community, fire up your favorite IRC client and ";
        $blurb .= "head on over to <tt>#browserplus</tt> on <tt>freenode</tt>.";

        $nav = l("&laquo; Older", "?id=$first_id", ($first_id > 0)) . " | " . 
            l("Current", "/discuss/", ($id != $max_id)) . " | " .
            l("Newer &raquo;", "?id=$next_id",  ($last_id < $max_id));
    }
}

$cfg = array("show_long_date"=>false, "top_nav"=>$nav, "bot_nav"=>$nav);
if ($in_search) {
    $cfg["url_pat"] = "/discuss/?mid=%s";
    $cfg["url_key"] ="id";
}

$table = render_table($results, "stamp", "who", "utterance", $cfg);
     


$widgets = $irc->render_widget("day") . $irc->render_widget("week") . $irc->render_widget("month");

$body = <<< EOS
<h1>IRC Transcript</h1>
<p>$blurb</p>
<div class="irc-logs">
$table
</div>
EOS;

render2c("IRC Logs", "Discuss", $body, $widgets);
?>