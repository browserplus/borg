<?php
include("/home/websites/browserplus/php/site.php");
include("/home/websites/browserplus/php/db.php");
include("/home/websites/browserplus/php/irc.php");
include("/home/websites/browserplus/php/git.php");


$RowsToShow = 15;


// IRC Transcript
$irc = new IRC();
$results = $irc->get_rows($irc->get_max_id(), $RowsToShow);
$ircnav = l("#browserplus", "/discuss/");
$irctable = render_table($results, "stamp", "who", "utterance", 
    array("show_long_dates"=>true, "top_nav" => $ircnav));

$ircwidgets = $irc->render_widget("day") . $irc->render_widget("week") . $irc->render_widget("month");


// GIT Projects
$git = new GIT();
$results = $git->get_rows($RowsToShow);
$gitnav = "<strong>Latest Project Commits</strong>";
$gittable = render_table($results, "tcommit", "project", "msg", 
    array("show_long_dates"=>true, "top_nav" => $gitnav, "url_key" => "url", "url_pat" => "%s"));

$gitwidgets = $git->render_project_widget();


$body = <<< EOS
<h1>BrowserPlus</h1>
<div class="home-logs">
    <div class="home-irc-log">$irctable</div>
    <div class="home-git-log">$gittable</div>
</div>
EOS;

render2c("BrowserPlus", "Home", $body, $ircwidgets . $gitwidgets);

?>