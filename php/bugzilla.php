<?php

function bugz_update_cmp($a, $b) {
    return $a['updated'] < $b['updated'];
}

// Fetch latest bug from Bugzilla
class Bugzilla
{
	// APC key
	private static $bugsKey  = "bugzilla.tickets.all";
	private static $bugsFeed = "http://bugs.browserplus.org/buglist.cgi?bug_status=NEW&bug_status=REOPENED&bug_status=RESOLVED&query_format=advanced&title=Bug%20List&ctype=atom&limit=20&&order=Last%20Changed%20DESC";

    // return the "cachekey", which prevents others from hitting our cache files
    function get_cache_secret() {
        return get_secret("cachekey");
    }
    
	// This method should be called by cron scripts cause it takes a bit of time.  Returns
	// a JSON respresentation of the most recent bugs.
	function fetch_bugs() {
		$atom = fetch(self::$bugsFeed);
		if (!$atom) { return; }

		$xml = simplexml_load_string($atom);

		$bugs = array();
		foreach($xml->entry as $item) {
			$title = (string)$item->title;
			$link = (string)$item->link['href'];
			$time = prettyDate(strtotime((string)$item->updated));
	
			$lines = explode("\n", (string)$item->summary);
			$name = null;
			$table = array();
			foreach($lines as $line) {
				if (preg_match("|<td>(.+)</td>|", trim($line), $arr)) {
					if ($name == null) {
						$name = $arr[1];
					} else {
						$table[$name] = $arr[1];
						$name = null;
					}
				}
			}
	
			// above builds this table:
			// Array
			// (
			//	   [Product] => platform
			//	   [Component] => Triage
			//	   [Assignee] => David Grigsby
			//	   [Reporter] => Lloyd Hilaiel
			//	   [Status] => NEW
			//	   [Resolution ] => ---
			//	   [Priority] => P1
			//	   [Severity ] => normal
			//	   [Target Milestone] => 2.8
			//	   [Creation date] => 2010-03-12
			//	   [Last changed date] => 15:42:45
			// )
			
			$bugs[] = array(
			  	"project"	 => $table["Product"],
				"component"  => $table["Component"],
				"assingee"   => $table["Assignee"],
				"reporter"   => $table["Reporter"],
				"status"     => $table["Status"],
				"resolution" => $table["Resolution"],
				"priority"   => $table["Priority"],
				"severity"   => $table["Severity"],
				"milestone"  => $table["Target Milestone"],
				"updated"    => strtotime((string)$item->updated),
				"title"      => $title,
				"url"        => $link
			);
		}

		if (count($bugs) > 0) {
        	// newest on top
        	usort($bugs, "bugz_update_cmp");

			$json = json_encode($bugs);
			// store data for render functions
			apc_store(self::$bugsKey, $json);
			return $json;
		} else {
			return "";
		}
	}

	private function get_cached_data() {
		$json = apc_fetch(self::$bugsKey);
		
		// just in case it's not cached yet
		if (empty($json)) {
			$json = $this->fetch_bugs();
		}

		return $json;
	}

	function render_widget($num=5) {
		$json = $this->get_cached_data();

		if ($json && ($issues = json_decode($json, 1))) {
			$s = "<ul>";
			foreach($issues as $i) {
				if ($num-- == 0) break;
				$s .= "<li><span class=\"lighthouse-state\">" . $i["status"] . "</span>: " . 
					"<a href=\"{$i['url']}\">{$i['title']}</a> " .
					"<span class=\"lighthouse-when\">(" . prettyDate($i['updated']) . ")<span></li>";
			}
			$s .= "</ul>";
			return render_widget("issues", "BrowserPlus: <a href=\"http://bugs.browserplus.org/\">Bugz</a>", $s);
		}
		return "";
	}
	
	function render_mobile($num=10) {
		$json = $this->get_cached_data();

		$s = "";
		if ($json && ($issues = json_decode($json, 1))) {
			foreach($issues as $i) {
				if ($num-- == 0) break;
				$s .=  "<li><a target=\"_self\" href=\"{$i['url']}\"><b>{$i['status']}</b>: {$i['title']} <span class=\"when\">" . prettyDate($i['updated']) . "</span></a></li>";
			}
		}
		return $s;
	}

}