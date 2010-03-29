<?php

class IRC {
	private $db;
	
	private static $table = "chat";
	private static $logpath =  "/home/websites/browserplus/irc_logs/";
	// subtract 8 hours to convert from GST to PST
	//private static $tblcols = "id, from_unixtime(unix_timestamp(stamp)-28800) as stamp, utterance, who";
	private static $tblcols = "id, from_unixtime(unix_timestamp(stamp)) as stamp, utterance, who";
	function __construct() {
		$this->db = new DB("irc");
	}

	/*
	* Query irc db for max id
	*/
	function get_max_id() {
		return $this->db->max_id(self::$table, "id");	 
	}

	/*
	* Query irc table for range of values
	*/
	function get_rows($max_id, $num_rows=10) {
		$starting_id = max(0, $max_id - $num_rows);
		$sql = "SELECT " . self::$tblcols . " FROM " . self::$table . " WHERE id > ? ORDER BY chat.stamp LIMIT $num_rows";
		return $this->db->fetch_all($sql, array($starting_id));
	}

	function get_rows_at($id, $num_rows) {
		$sql = "SELECT " . self::$tblcols . " FROM " . self::$table . " WHERE id > ? ORDER BY chat.stamp LIMIT $num_rows";
		return $this->db->fetch_all($sql, array($id));
	}

	/*
	* Search irc table
	*/
	function find_rows($search, $num_rows) {
		$sql = "SELECT " . self::$tblcols . " FROM " . self::$table . " WHERE MATCH(utterance) AGAINST (?)	ORDER BY stamp DESC LIMIT $num_rows";
		return $this->db->fetch_all($sql, array($search));
	}

	/*
	* Return most popular words in IRC for given timeframe.	 
	* 
	* $alias   - (day | week | month | forever)
	* $url	   - baseurl
	* $howmany -  upper limit on how many words to fetch
	* $minPixelSize
	* $maxPixelSize
	*/
	function get_hot_words($timeframe, $url, $howmany, $minPixelSize=7, $maxPixelSize=35) {
		$fn = self::$logpath . "irc_${timeframe}.csv";
		if (!file_exists($fn)) {
			return "No topics found!";
		}

		$lines = file($fn);

		$words = array();
		$cnt = 0;
		foreach ($lines as $line) {
			$terms = preg_split('/,/', $line);
			$words[$terms[0]] = $terms[1];
			if (++$cnt > $howmany) {
				break;
			}
		}

		uksort($words, "strnatcasecmp");

		// take a return array from "getHotIRCWords" and normalize the
		// "frequency" element to a floating point number between $min and $max
		$min = -1;
		$max = -1;

		// determine min/max
		foreach ($words as $word => $freq) {
			$max = ($max == -1) ? $freq : max($max, 0 + $freq);
			$min = ($min == -1) ? $freq : min($min, 0 + $freq);
		}

		// normalize
		$results = array();
		foreach ($words as $word => $freq) {  
			if ($max - $min != 0) {
				$results[$word] = ((($freq - $min) / (1.0 * $max - $min)) *
					($maxPixelSize - $minPixelSize)) + $minPixelSize;
			} else {
				// default font size
				$results[$word] = 9;
			}
		}

		$str = "";
		foreach ($results as $k => $v) {
			$str .= "<a style=\"font-size:" . round($v) . "px\" href=\"${url}${k}\">$k</a>\n";
		}
	
		return $str;
	}

	function render_widget($period) {
		$body = $this->get_hot_words($period, "/discuss/?search=", 20, 10, 20);
		return render_widget("irc-tags", "IRC Topics: " . ucfirst($period), $body);
	}
	
	function render_mobile($results, $max_id) {
		$str = "";
		if (is_array($results) && count($results) > 0) {
			foreach($results as $row) {
				$when = date("D M j, g:ia", strtotime($row['stamp']));
				$who = h($row['who']);
				$what = render_line($row['utterance']);
				$max_id = $row['id'];
				$str .= "<li><span class=\"who\">$who</span> $what <span class=\"when\">$when</span></li>";
			}
		}

		$str .= "<li><a href=\"mirc.php?id={$max_id}\" target=\"_replace\">Get Latest Conversation</a></li>";
		return $str;
	}
}

?>