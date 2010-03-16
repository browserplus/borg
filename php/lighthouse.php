<?php

// Fetch bugs for all browserplus projects.
// Lighthouse API doc - http://lighthouseapp.com/api
function lighthouse_update_cmp($a, $b) {
    return $a['updated'] < $b['updated'];
}

class Lighthouse
{
    // APC key
    private static $ticketsKey = "lighthouse.tickets.all";
    private static $projectsUrl = "http://browserplus.lighthouseapp.com/projects.xml?_token=%s";
    private static $ticketsUrl  = "http://browserplus.lighthouseapp.com/projects/%s/tickets.xml?_token=%s";
    private static $token = null;

    var $projectNameMap = array();
    
	function __construct() {
	    self::$token = get_secret("lighthouse");
    }


    // return the "cachekey", which prevents others from hitting our cache files
    function get_cache_secret() {
        return get_secret("cachekey");
    }
    
    // Return a list of bugs for all projects.  This method should only 
    // be called by cron scripts cause it takes a bit of time.  The results
    // are cached and used by the render methods below.
    function get_all_bugs() {
        $bugs = array();

        $ids = $this->get_project_ids();

        if (count($ids) > 0) {
            foreach($ids as $id) {
                $bugs = array_merge($bugs, $this->get_bugs($id));
            }
        }

        // newest on top
        usort($bugs, "lighthouse_update_cmp");

        // store data for render functions
        apc_store(self::$ticketsKey, json_encode($bugs));
        
        if (isset($json)) {
            $bugs = json_decode($json, 1);
        }

        return $bugs;
    }

    // return list of bugs for given project id
    private function get_bugs($project_id) {
        $bugs = array();

        $url = sprintf(self::$ticketsUrl, $project_id, self::$token);
        $xml = fetch($url);

        if ($xml) {
            $tickets = simplexml_load_string($xml);
            foreach ($tickets->ticket as $t) {
                $bugs[] = array(
                    "project" => $this->projectNameMap[(string)$project_id],
                    "project_id" => $project_id,
                    "closed" => (int)$t->closed,
                    "title" => (string)$t->title,
                    "state" => (string)$t->state,
                    "user" => (string)$t->{'user-name'},
                    "updated" => strtotime((string)$t->{'updated-at'}),
                    "created" => strtotime((string)$t->{'created-at'}),
                    "url" => (string)$t->url
                );
            }
        }
        
        return $bugs;
    }

    private function get_project_ids() {
        $ids = array();
        
        $url = sprintf(self::$projectsUrl, self::$token);
        $xml = fetch($url);

        if ($xml) {
            $projects = simplexml_load_string($xml);
            foreach ($projects->project as $p) {
                $id = (int)$p->id;
                $ids[] = $id;
                $this->projectNameMap[$id] = (string)$p->name;
            }
        }

        return $ids;
    }

    private function get_cached_data() {
        $json = apc_fetch(self::$ticketsKey);
        
        // just in case it's not cached yet
        if (!$json) {
            $this->get_all_bugs();
            $json = apc_fetch(self::$ticketsKey);
        }

        return $json;
    }

    function render_widget($num=5) {
        $json = $this->get_cached_data();

        if ($json && ($issues = json_decode($json, 1))) {
            $s = "<ul>";
            foreach($issues as $i) {
                if ($num-- == 0) break;
                $s .= "<li><span class=\"lighthouse-state\">" . $i["state"] . "</span>: " . 
                    "<a href=\"{$i['url']}\">{$i['title']}</a> " .
                    "<span class=\"lighthouse-when\">(" . prettyDate($i['updated']) . ")<span></li>";
            }
            $s .= "</ul>";
            return render_widget("issues", "Lighthouse: <a href=\"http://browserplus.lighthouseapp.com/\">Tickets</a>", $s);
        }
        return "";
    }
    
    function render_mobile($num=10) {
        $json = $this->get_cached_data();

        $s = "";
        if ($json && ($issues = json_decode($json, 1))) {
            foreach($issues as $i) {
                if ($num-- == 0) break;
                $s .=  "<li><a target=\"_self\" href=\"{$i['url']}\"><b>{$i['state']}</b>: {$i['title']} <span class=\"when\">" . prettyDate($i['updated']) . "</span></a></li>";
            }
        }
        return $s;
    }

}