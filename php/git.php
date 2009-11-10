<?php

class GIT {
	private $db;
	private static $commit_table = "gitcommit";
    private static $git_base = "http://github.com/";

	function __construct() {
        $this->db = new DB("git");
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
        $s = "";
        foreach($projects as $repository => $url) {
            $s .= "<a href=\"$url\">$repository</a>\n";
        }

        return render_widget("git-projects", "Projects", $s);
    }
}