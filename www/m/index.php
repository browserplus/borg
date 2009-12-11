<?php
include("/home/websites/browserplus/php/site.php");
include("/home/websites/browserplus/php/forum.php");
include("/home/websites/browserplus/php/twitter.php");
include("/home/websites/browserplus/php/db.php");
include("/home/websites/browserplus/php/irc.php");
include("/home/websites/browserplus/php/git.php");

$RowsToShow = 20;
$t = new Twitter();
$f = new Forum();
$irc = new IRC();
$git = new GIT();


$twitterSearchItems = $t->render_search_mobile("browserplus", $RowsToShow);
$twitterUserItems = $t->render_user_mobile("browserplus", $RowsToShow);

$max = $irc->get_max_id();
$results    = $irc->get_rows($max, $RowsToShow);
$ircItems   = $irc->render_mobile($results, $max);
$issueItems = $git->render_issues_mobile($RowsToShow);
$forumItems = $f->render_mobile($RowsToShow);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
         "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title>BrowserPlus</title>
  <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;"/>
  <style type="text/css" media="screen">@import "/iui/dashboardx.css";</style><!-- http://www.phpied.com/cssmin-js/ -->
  <script type="application/x-javascript" src="/iui/iuix.js"></script>
  <style type="text/css" media="screen">
  .items li {font-size:100%;font-weight:normal;}
  .when {color:#999;font-size:10pt;}
  .who {font-weight:bold;color:#369;}
  </style>
</head>

<body>
    <div class="toolbar">
        <h1 id="pageTitle"></h1>
        <a id="backButton" class="button" href="#"></a>
    </div>
    
    <ul id="dashboard" title="BrowserPlus" selected="true">
        <li><a href="#irc">IRC</a></li>
        <li><a href="#tweetSearch">Twitter Search</a></li>
        <li><a href="#tweetUser">Twitter User</a></li>
        <li><a href="#forum">Forums</a></li>
        <li><a href="#issues">Issues</a></li>
    </ul>

    <ul id="irc" title="IRC" class="items">
        <?php echo $ircItems ?>
    </ul>

    <div id="tweetSearch" title="Twitter Search">
        <?php echo $twitterSearchItems ?>
    </div>

    <div id="tweetUser" title="Twitter User">
        <?php echo $twitterUserItems ?>
    </div>

    <ul id="forum" title="Forums" class="items">
        <?php echo $forumItems ?>
    </ul>

    <ul id="issues" title="Issues" class="items">
        <?php echo $issueItems ?>
    </ul>

</body>

</html>
