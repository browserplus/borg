<?php

// Class that includes "Twitter" needs to require site.php
class Twitter
{
    private static $userUrl = "http://twitter.com/statuses/user_timeline.json?screen_name=";
    private static $userKey = "twitter.user.";
    private static $searchUrl = "http://search.twitter.com/search.json?q=";
    private static $searchKey = "twitter.search.";    

    // return the "cachekey", which prevents others from hitting our cache files
    function get_cache_secret() {
        return get_secret("cachekey");
    }
    
    // fetch tweets for specific user and store in apc cache under "twitter.user.<user>"
    function fetch_user_tweets($user) {
        $json = fetch(self::$userUrl . urlencode($user));
        if ($json) {
            apc_store(self::$userKey . $user, $json);
            return true;
        } else {
            return false;
        }
    }

    // fetch twitter search results and store in apc cache under "twitter.search.<search>"
    function fetch_search_tweets($search) {
        $json = fetch(self::$searchUrl . urlencode($search));
        if ($json) {
            apc_store(self::$searchKey . $search, $json);
            return true;
        } else {
            return false;
        }
    }

    // hyperlink tweet text, replacing "http://...", "@user", "#search" with hyperlinks.
    function hyperlinkit($s) {
        // http://daringfireball.net/2009/11/liberal_regex_for_matching_urls
        $gruber = "\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))";

        // hyperlink urls
        $s = preg_replace("@$gruber@i", "<a href=\"$1\">$1</a>", $s);

        // replace @users
        $s = preg_replace("/@([-_a-z0-9]+)/", "@<a href=\"http://twitter.com\">$1</a>", $s);

        // replace #search
        $s = preg_replace("/#([-_a-z0-9]+)/", "#<a href=\"http://search.twitter.com/search?q=$1\">$1</a>", $s);

        return $s;
    }

    
    function render_user_widget($user, $rows=5) {
        // note that data is stored in apc via a cron job
        $json = apc_fetch(self::$userKey . $user);
        if (!$json) { return ""; }

        $tweets = json_decode($json);
        $str = "<ul>";

        foreach($tweets as $t) {
            if ($rows-- == 0) { break; }
            $time = prettyDate(strtotime($t->created_at));
            $str .= "<li>" . $this->hyperlinkit($t->text) . " <span class=\"tweet-when\">({$time})<span></li>";
        }
    
        $str .= "</ul>";
        return render_widget("tweet", "Twitter: <a href=\"http://twitter.com/$user\">Feed</a>", $str);
    }

    function render_search_widget($search, $rows=5) {
        // note that data is stored in apc via a cron job
        $json = apc_fetch(self::$searchKey . $search);
        if (!$json) { return ""; }

        $tweets = json_decode($json);
        $str = "<ul>";

        foreach($tweets->results as $t) {
            if ($rows-- == 0) { break; }
            $time = prettyDate(strtotime($t->created_at));

            $str .= "<li><a href=\"http://twitter.com/{$t->from_user}\">{$t->from_user}</a>: " .
                $this->hyperlinkit($t->text) . " <span class=\"tweet-when\">({$time})<span></li>";
        }
    
        $str .= "</ul>";
        return render_widget("tweet", "Twitter: <a href=\"http://search.twitter.com/search?q=$search\">Search</a>", $str);
    }
}

