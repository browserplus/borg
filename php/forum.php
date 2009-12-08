<?php

class Forum
{
    // APC key
    private static $forumKey = "ydn.forums";
    
    // keep N items per feed
    private static $itemsToKeep = 5;

    // Forum Feeds to fetch (RSS)
    private static $feeds = array(
        "http://developer.yahoo.net/forum/index.php?act=rssout&id=54",
        "http://developer.yahoo.net/forum/index.php?act=rssout&id=55",
        "http://developer.yahoo.net/forum/index.php?act=rssout&id=56",
        "http://developer.yahoo.net/forum/index.php?act=rssout&id=58"
    );
    
    // return the "cachekey", which prevents others from hitting our cache files
    function get_cache_secret() {
        return get_secret("cachekey");
    }
    
    function fetch_forums() {
        // data to be stored in here
        $json = array();
        $good = 0;
        foreach(self::$feeds as $url) {
            $data = fetch($url);
            if ($data) {
                $good++;
                $keep = self::$itemsToKeep;
                $rss = simplexml_load_string($data);
                foreach ($rss->channel->item as $item) {
                if ($keep-- == 0) { break; }
                $time = strtotime($item->pubDate);
                
                $text = strip_tags($item->description);
                if (strlen($text) > 140) {
                    $text = substr($text, 0, 140) . "&hellip;";
                }

                $json["T{$time}"] = array(
                    "time" => $time,
                    "link" => (string)$item->link,
                    "text" => $text, 
                    );
                } 
            }
        }

        if (count($json) > 0) {
            // sort items by key, newest to oldest
            krsort($json);
            apc_store(self::$forumKey, json_encode($json));
            return true;
        } else {
            return false;
        }
    }
    
    function render_widget($rows=5) {
        $json = apc_fetch(self::$forumKey);
        if (!$json) { return ""; }
        $items = json_decode($json, 1);

        $str = "<ul>";
        foreach($items as $item) {
            if ($rows-- == 0) { break; }
            $str .= "<li>" . $item["text"] . 
                " <a href=\"{$item['link']}\"><img src=\"/images/permalink.gif\"></a> " .
                "<span class=\"forum-when\">(" . prettyDate($item["time"]) . "</span>)</li>";
        }
        
        $str .= "</ul>";
        return render_widget("forum", "YDN: <a href=\"http://developer.yahoo.net/forum/index.php?showforum=90\">Forums</a>", $str);
    }
}