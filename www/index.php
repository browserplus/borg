<?php
include("/home/websites/browserplus/php/site.php");
include("/home/websites/browserplus/php/db.php");
include("/home/websites/browserplus/php/irc.php");
include("/home/websites/browserplus/php/git.php");
include("/home/websites/browserplus/php/twitter.php");
include("/home/websites/browserplus/php/forum.php");

$tableRowsToShow = 15;
$twitterRowsToShow = 5;
$forumRowsToShow = 5;

function get_links_widget() {
    $links = array(
        array(
            "link" => "http://browserplus.yahoo.com/install/",
            "title" => "Install BrowserPlus"
        ),

        array(
            "link" => "http://developer.yahoo.net/blog/archives/2009/12/browserplus_source_code_available_now_on_github.html",
            "title" => "BrowserPlus Source Code Available Now on Github"
        )
    );
    
    $str = "<ul>";
    foreach($links as $link => $d) {
        $str .= "<li><a href=\"{$d['link']}\">{$d['title']}</a></li>";
    }

    $str .= "</ul>";
    
    return render_widget("links", "Links Around the Web", $str);
}

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

    return render_widget("blog", "BrowserPlus Blog", $s);
}

// IRC Transcript
$irc = new IRC();
$results = $irc->get_rows($irc->get_max_id(), $tableRowsToShow);

$ircnav = l("#browserplus", "/discuss/");
$irctable = render_table($results, "stamp", "who", "utterance", 
    array("show_long_dates"=>true, "top_nav" => "<strong>IRC: $ircnav</strong>"));

$ircwidgets = $irc->render_widget("day") . $irc->render_widget("week");// . $irc->render_widget("month");


// GIT Projects
$git = new GIT();
$results = $git->get_rows($tableRowsToShow);
$gitnav = "<strong>GitHub: <a href=\"http://www.github.com/browserplus/\">BrowserPlus</a></strong>";
$gittable = render_table($results, "tcommit", "project", "msg", 
    array("show_long_dates"=>true, "top_nav" => $gitnav, "url_key" => "url", "url_pat" => "%s"));

$gitwidgets = $git->render_project_widget();

// Links Widget
$linkswidget = get_links_widget();

// Blog Widget
$blogwidget = get_blog_widget();

// Twitter Widgets
$twitter = new Twitter();
$tw_user_widget   = $twitter->render_user_widget("browserplus", $twitterRowsToShow);
$tw_search_widget = $twitter->render_search_widget("browserplus", $twitterRowsToShow);

$forums = new Forum($forumRowsToShow);
$forum_widget = $forums->render_widget();



$body = <<< EOS
<h1 class="homepage">BrowserPlus Dashboard</h1>
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
    $gitwidgets
EOS;


render3c("BrowserPlus", "Home", $left, $body, $right);
//render2c("BrowserPlus", "Home", $body, $blogwidget . $ircwidgets . $gitwidgets);
?>