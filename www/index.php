<?php
include("/home/websites/browserplus/php/site.php");
include("/home/websites/browserplus/php/db.php");
include("/home/websites/browserplus/php/irc.php");
include("/home/websites/browserplus/php/git.php");

function get_blog_widget() {
    $atom = file_get_contents("/var/www/blog/atom.xml");
    $xml = simplexml_load_string($atom);
    
//    print "<pre>"; print_r($xml); print "</pre>";
    
//    return "test";
    $s = "<ul>";
    $max = 5;
    foreach($xml->entry as $item) {
        if ($max-- == 0) { break; }
        $link = (string)$item->link;
        $title = (string)$item->title;
        $s .= "<li><a href=\"$link\">$title</a></li>";
    }
    
    $s .= "</ul>";

    return render_widget("blog", "Blog", $s);
}


$RowsToShow = 15;


// IRC Transcript
$irc = new IRC();
$results = $irc->get_rows($irc->get_max_id(), $RowsToShow);

$ircnav = l("#browserplus", "/discuss/");
$irctable = render_table($results, "stamp", "who", "utterance", 
    array("show_long_dates"=>true, "top_nav" => "<strong>IRC: $ircnav</strong>"));

$ircwidgets = $irc->render_widget("day") . $irc->render_widget("week") . $irc->render_widget("month");


// GIT Projects
$git = new GIT();
$results = $git->get_rows($RowsToShow);
$gitnav = "<strong>Latest Project Commits</strong>";
$gittable = render_table($results, "tcommit", "project", "msg", 
    array("show_long_dates"=>true, "top_nav" => $gitnav, "url_key" => "url", "url_pat" => "%s"));

$gitwidgets = $git->render_project_widget();

$blogwidget = get_blog_widget();

$body = <<< EOS
<h1>BrowserPlus Dashboard</h1>

<div class="home-logs">
    <div class="home-irc-log">$irctable</div>
    <div class="home-git-log">$gittable</div>
</div>
EOS;

render2c("BrowserPlus", "Home", $body, $blogwidget . $ircwidgets . $gitwidgets);

?>