<?php
include("../php/site.php");
if (isMobile()) {
    header("Location: /m/");
    exit;
}
include("../php/db.php");
include("../php/irc.php");
include("../php/git.php");
include("../php/twitter.php");
include("../php/forum.php");
include("../php/lighthouse.php");

$tableRowsToShow = 15;
$listItemsToShow = 5; // for twitter, forum, blog, issues

function get_links_widget() {
    $links = array(
	
        array(
            "link" => "/docs/demos/Resumble_uploads.html",
            "title" => "Resumable Uploads Demo"
        ),

		array(
			"link" => "http://github.com/browserplus/platform/raw/2.6.0/docs/ChangeLog.txt",
			"title" => "BrowserPlus 2.6.0 Release Notes"
		),

        array(
            "link" => "http://browserplus.yahoo.com/install/",
            "title" => "Install BrowserPlus 2.6.0"
        ),

        array(
            "link" => "/docs/demos/Uploadr.html",
            "title" => "8 Ways to Speed Your Uploads"
        )
    );
    
    $str = "<ul>";
    foreach($links as $link => $d) {
        $str .= "<li><a href=\"{$d['link']}\">{$d['title']}</a></li>";
    }

    $str .= "</ul>";
    
    return render_widget("links", "* What's New *", $str);
}

function get_blog_widget($max) {
	$feed = "/var/www/blog/atom.xml";
	if (!file_exists($feed)) {
		return "";
	}

   	$atom = file_get_contents($feed);
    $xml = simplexml_load_string($atom);
    $s = "<ul>";

    foreach($xml->entry as $item) {
        if ($max-- == 0) { break; }
        $link = (string)$item->link['href'];
        $title = (string)$item->title;
        $s .= "<li><a href=\"$link\">$title</a></li>";
    }
    
    $s .= "</ul>";

    return render_widget("blog", "BrowserPlus: <a href=\"/blog/\">Blog</a>", $s);
}

// IRC Transcript
$irc = new IRC();
$results = $irc->get_rows($irc->get_max_id(), $tableRowsToShow);

$ircnav = l("#browserplus", "/discuss/");
$irctable = render_table($results, "stamp", "who", "utterance", 
    array("show_long_dates"=>true, "top_nav" => "<strong>IRC: $ircnav</strong>"));

//$ircwidgets = $irc->render_widget("day") . $irc->render_widget("week") . $irc->render_widget("month");
$ircwidgets = $irc->render_widget("week");

// GIT Projects
$git = new GIT();
$results = $git->get_rows($tableRowsToShow);
$gitnav = "<strong>GitHub: <a href=\"http://www.github.com/browserplus/\">BrowserPlus</a></strong>";
$gittable = render_table($results, "tcommit", "project", "msg", 
    array("show_long_dates"=>true, "top_nav" => $gitnav, "url_key" => "url", "url_pat" => "%s"));

$gitwidgets = $git->render_project_widget();

// lighthouse issues
$lighthouse = new Lighthouse();
$issuewidget = $lighthouse->render_widget($listItemsToShow);

// Links Widget
$linkswidget = get_links_widget();

// Blog Widget
$blogwidget = get_blog_widget($listItemsToShow);

// Twitter Widgets
$twitter = new Twitter();
$tw_user_widget   = $twitter->render_user_widget("browserplus", $listItemsToShow);
$tw_search_widget = $twitter->render_search_widget("browserplus", $listItemsToShow);

$forums = new Forum();
$forum_widget = $forums->render_widget($listItemsToShow);



$body = <<< EOS
<div class="home-logs">
    <div class="home-irc-log">$irctable</div>
    <div class="home-git-log">$gittable</div>
</div>
EOS;


// widgets
$left = <<< EOS
    $tw_user_widget 
    $tw_search_widget
    $forum_widget
EOS;

$right =  <<< EOS
    $linkswidget
    $blogwidget
    $ircwidgets
    $issuewidget
    $gitwidgets
EOS;


render3c("BrowserPlus", "Home", $left, $body, $right);
//render2c("BrowserPlus", "Home", $body, $blogwidget . $ircwidgets . $gitwidgets);
?>