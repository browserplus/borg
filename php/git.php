<?php

class GIT {
	private $db;
	private static $commit_table = "gitcommit";
    private static $git_base = "http://github.com/";

    private static $platformIssueUrl = "http://github.com/api/v2/json/issues/list/browserplus/platform/open";
    private static $platformIssueKey = "git.issues.browserplus.platform";

	function __construct() {
        $this->db = new DB("git");
    }

    // return the "cachekey", which prevents others from hitting our cache files
    function get_cache_secret() {
        return get_secret("cachekey");
    }
    
    private function get_issue_object($issue) {
        return array(
            "title" => $issue['title'],
            "id" => $issue["number"]
        );
    }

    // fetch browserplus platform issues from GIT
    function fetch_platform_issues() {
        // 1. Fetch the json encoded issues from github
        // 2. Iterate thru issues, just keeping data we need (title/number)
        // 3. Sort "temp" by updated_at (stored in key)
        // 4. Cache sorted json string
        $json = fetch(self::$platformIssueUrl);

        if ($json) {
            // Strip \r\n out of json, otherwise json_decode will fail
            $json = str_replace(array(chr(13), chr(10)), array("",""), $json);
            $issues_arr = json_decode($json, 1);
            if ($issues_arr) {
                $data = array();
                foreach($issues_arr as $issues) {
                    foreach($issues as $i) {
                        // temp key so we can sort array based on date
                        $ut = strtotime($i["updated_at"]);
                        $data["T{$ut}"] = $this->get_issue_object($i);
                    }
                }

                // newest issues first
                krsort($data);
                apc_store(self::$platformIssueKey, json_encode(array_values($data)));
                return true;
            }
        }

        return false;
    }



    /*
     * Query irc table for range of values
     */
    function get_rows($num_rows=10) {
        $starting_id = max(0, $max_id - $num_rows);
        $sql = "SELECT * FROM " . self::$commit_table . " ORDER BY tcommit DESC LIMIT $num_rows";
        return $this->db->fetch_all($sql, array($starting_id));
    }

    function get_projects_flattened() {
        $all = get_projects();
        $ret = array();
        foreach($all as $label=>$projects) {
            foreach($projects as $url) {
                // only process urls hosted on github (see gitbase above)
                if (substr($url, 0, strlen($gitbase)) == $gitbase) {
                    // project is "lloyd/bp-imagealter"
                    $np = explode("/", substr($url, strlen(self::$git_base)));
                    $ret[$np[1]] = $url;
                }
            }
        }
        
        return $ret;
    }

    function render_project_widget() {
        $gitbase = "http://github.com/";

        $projects = $this->get_projects_flattened();
        $arr = array();
        foreach($projects as $repository => $url) {
            $arr[] = "<a href=\"$url\">$repository</a>";
        }
        $s = join(", ", $arr);
        return render_widget("git-projects", "GitHub: Projects", $s);
    }
    
    function render_issues_widget($num=5) {
        $json = apc_fetch(self::$platformIssueKey);

        if ($json && ($issues = json_decode($json, 1))) {
            $s = "<ul>";
            foreach($issues as $i) {
                if ($num-- == 0) break;
                $s .= "<li><a href=\"http://github.com/browserplus/platform/issues/#issue/{$i['id']}\">{$i['title']}</a></li>";
            }
            $s .= "</ul>";
            return render_widget("issues", "GitHub: <a href=\"<a href=\"http://github.com/browserplus/platform/issues/\">Issues</a>", $s);
        }
        return "";
    }
    
    function render_issues_mobile($num=10) {
        $json = apc_fetch(self::$platformIssueKey);

        $s = "";
        if ($json && ($issues = json_decode($json, 1))) {
            foreach($issues as $i) {
                if ($num-- == 0) break;
                $s .= "<li><a target=\"_self\" href=\"http://github.com/browserplus/platform/issues/#issue/{$i['id']}\">{$i['title']}</a></li>";
            }
        }
        return $s;
    }
}