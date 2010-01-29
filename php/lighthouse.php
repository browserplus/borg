<?php

// Fetch bugs for all browserplus projects.
// Lighthouse API doc - http://lighthouseapp.com/api
function lighthouse_update_cmp($a, $b) {
    return $a['updated'] < $b['updated'];
}

class Lighthouse
{
    // APC key
    private static $projectKey = "lighthouse.project.ids";
    private static $ticketsKey = "lighthouse.tickets.";
    private static $allTicketsKey = "lighthouse.tickets.all";
    
    private static $projectsUrl = "http://browserplus.lighthouseapp.com/projects.xml?_token=%s";
    private static $ticketsUrl  = "http://browserplus.lighthouseapp.com/projects/%s/tickets.xml?_token=%s";
    private static $token = null;
    var $projectNameMap = array();
    
	function __construct() {
	    $this->token = get_secret("lighthouse");
    }


    // return the "cachekey", which prevents others from hitting our cache files
    function get_cache_secret() {
        return get_secret("cachekey");
    }
    
    // return a list of bugs for all projects
    function get_all_bugs() {
        $key = self::$allTicketsKey;
        $bugs = array();

        if (!($json = apc_fetch($key))) {
            $ids = $this->get_project_ids();

            if (count($ids) > 0) {
                foreach($ids as $id) {
                    $bugs = array_merge($bugs, $this->get_bugs($id));
                }
            }
        
            // newest on top
            usort($bugs, "lighthouse_update_cmp");
        
            apc_store($key, json_encode($bugs));
        }
        
        if ($json) {
            $bugs = json_decode($json, 1);
        }

        return $bugs;
    }

    // return list of bugs for given project id
    function get_bugs($project_id) {

        $key = self::$ticketsKey . $project_id;
        $bugs = array();
        apc_store($key, null);
        if (!($xml = apc_fetch($key))) {
            $url = sprintf(self::$ticketsUrl, $project_id, $this->token);

            $xml = fetch($url);
            apc_store($key, $xml);
        }

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

    function get_project_ids() {
        $ids = array();
        $key = self::$projectKey;
        
        if (!($xml = apc_fetch($key))) {
            $url = sprintf(self::$projectsUrl, $this->token);
            $xml = fetch($url);
            apc_store($key, $xml);
        }

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

    function render_widget($num=5) {
        $json = apc_fetch(self::$allTicketsKey);

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
        $json = apc_fetch(self::$allTicketsKey);

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