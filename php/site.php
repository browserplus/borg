<?php
define("LAYOUT_2C", "site_2c");
define("LAYOUT_3C", "site_3c");
define("SECRETS_FILE", "/home/borg/borg_secrets.json");

$EMOTES = array(
    ":)"   => '<img alt="smile" src="/images/emote/smile.png">',
    ":-)"  => '<img alt="smile" src="/images/emote/smile.png">',
    ":("   => '<img alt="frown" src="/images/emote/frown.png">',
    ":-("  => '<img alt="frown" src="/images/emote/frown.png">',
    ";)"   => '<img alt="wink" src="/images/emote/wink.png">',
    ";-)"  => '<img alt="wink" src="/images/emote/wink.png">',
    ":D"   => '<img alt="happy" src="/images/emote/happy.png">',
    ":-D"  => '<img alt="happy" src="/images/emote/happy.png">',
    ":|"   => '<img alt="stoic" src="/images/emote/stoic.png">',
    ":-|"  => '<img alt="stoic" src="/images/emote/stoic.png">',
    "8)"   => '<img alt="cool" src="/images/emote/cool.png">',
    "8-)"  => '<img alt="cool" src="/images/emote/cool.png">',
    ":P"   => '<img alt="razz" src="/images/emote/razz.png">',
    ":-P"  => '<img alt="razz" src="/images/emote/razz.png">',
    ":O"   => '<img alt="surprised" src="/images/emote/surprised.png">',
    "9.9"  => '<img alt="sarcastic" src="/images/emote/sarcastic.png">',
    //    ":\\"  => '<img alt="annoyed" src="/images/emote/annoyed.png">',
    //    ":-\\" => '<img alt="annoyed" src="/images/emote/annoyed.png">',
    ":x"   => '<img alt="angry" src="/images/emote/angry.png">',
    ":-x"  => '<img alt="angry" src="/images/emote/angry.png">',
    "d:)"  => '<img alt="relaxed" src="/images/emote/relaxed.png">',
    "[:)"  => '<img alt="jamming" src="/images/emote/jamming.png">'
);

$EMO_SEARCH  = array_keys($EMOTES);
$EMO_REPLACE = array_values($EMOTES);

function get_secret($key) {
    static $json = null;
    if (!$json) {
        $json = json_decode(file_get_contents(SECRETS_FILE), true);
    }
    
    return $json[$key];
}

/*
 * Escape html text.
 */
function h($text) {
    return htmlspecialchars($text,ENT_QUOTES);//,'UTF-8');
}

/*
 * Optionally hyperlink an text item.
 */
function l($label, $url, $bool=true) {
    return $bool ? "<a href=\"$url\">$label</a>" : $label;
}

function render2c($title, $tab, $body, $widgets) {
    $data = array(
        "title" => $title,
        "active" => $tab,
        "body" => $body,
        "widgets" => $widgets
    );
    
    render_page($data, LAYOUT_2C);
}

function render3c($title, $tab, $widgets_left, $body, $widgets_right) {
    $data = array(
        "title" => $title,
        "active" => $tab,
        "widgets_left" => $widgets_left,
        "body" => $body,
        "widgets_right" => $widgets_right
    );
    
    render_page($data, LAYOUT_3C);
}
function get_projects() {
    return json_decode(file_get_contents(get_cfg_var("dok_base") . "/projects/projects.json"), 1);
}

function fetch($url) {
    $ch = curl_init($url);
    ob_start();
    curl_exec ($ch);
    curl_close ($ch);
    return ob_get_clean();
}

function render_page($data, $template) {
    extract($data);
    include(get_cfg_var("dok_base") . "/layout/{$template}.php");
}


function prettyDate($ts)
{
    $diff = time() - $ts;
    
    $map = array(
        array(60, 0, "just now"),
        array(120, 0, "1 minute ago"),
        array(3600, 60, "minutes ago"),
        array(7200, 0, "1 hour ago"),
        array(86400, 3600, "hours ago"),
        array(172800, 0, "1 day ago"),
        array(604800, 86400, "days ago"),
        array(1209600, 0, "1 week ago"),
        array(2592000, 604800, "weeks ago"),
        array(5184000, 0, "1 month ago"),
        array(28857600, 2592000, "months ago"),
        array(63072000, 0, "1 year ago"),
        array(0, 31536000, "years ago"));

            
    $cnt = count($map);
    for ($i = 0; $i < $cnt; $i++)
    {
        if ($diff < $map[$i][0] && $map[$i][1] == 0) {
            return $map[$i][2];
        } else if ($diff < $map[$i][0] || $map[$i][0] == 0) {
            return floor($diff/$map[$i][1]) . " " . $map[$i][2];
        }
    }
    
    return "?";
}

/*
 * If linked URL is too long, add some wbrs so it won't make the table too wide.
 */ 
function render_line_link_cb($matches) {
    $title = $matches[1];

    // for long urls, add some white space breaks
    if (strlen($title) > 60) {
        $title = preg_replace("/(.{20})/", "\\1<wbr>", $title);
    }
    return "<a target='_blank' href='{$matches[1]}'>$title</a>";
}


/* 
 * Render a line of text, hyperlinking urls and displaying emoticons.
 */
function render_line($s){
    global $EMO_SEARCH, $EMO_REPLACE;

    $s = str_replace($EMO_SEARCH, $EMO_REPLACE, $s);
    $words = preg_split("/[\s]+/", $s);
    $out = array();
    foreach($words as $w) {
        if (strlen($w) > 30 && !preg_match("#^((ht|f)tps?://)|(git@github.com)#", $w)) {
            $w = preg_replace("/(.{30})/", "\\1<wbr>", $w);
        }
    
        $out[] = $w;
    }

    $s = implode(" ", $out);

    // http://daringfireball.net/2009/11/liberal_regex_for_matching_urls
    $gruber = "\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))";
    $s = preg_replace_callback("@$gruber@i", "render_line_link_cb", $s);

    // link git projects
    // git@github.com:lloyd/bp-imagealter.git
    // http://github.com/lloyd/bp-imagealter
    $s = preg_replace("#(git@github.com:([^/]+/)([-_a-z0-9]+)\.git)#i", 
        "<a target='_blank' href='http://github.com/$2$3'>$1</a>", $s);    

    // highlight karma
    $s = preg_replace("/^(\w+--)$/", "<span style=\"color:red;font-style:italic\">$1</span>", $s);
    $s = preg_replace("/^(\w+\+\+)$/", "<span style=\"color:green;font-style:italic;\">$1</span>", $s);

    return $s;
}
    


function render_table($rows, $when_key, $who_key, $what_key, $cfg=array())
{
    $month = array(
        "01" => "Jan", "02" => "Feb", "03" => "Mar", "04" => "Apr", "05" => "May", "06" => "Jun", 
        "07" => "Jul", "08" => "Aug", "09" => "Sep", "10" => "Oct", "11" => "Nov", "12"=> "Dec");

    // table with alternating colors.
    $str = "<div class=\"log\">";
    if ($cfg['top_nav']) { $str .= "<div class=\"log-nav\">" . $cfg["top_nav"] . "</div>\n"; }
    $str .= "<table><tbody>\n";

    $cnt = 0; // even or odd
    $curday = null;
    foreach($rows as $row) {


        if (preg_match("/^[0-9]+$/", $row[$when_key])) {
            // integer already
            $time = $row[$when_key];
        } else {
            $time = strtotime($row[$when_key]);
        }
        $who  = h($row[$who_key]);
        $what = render_line($row[$what_key]);

        if ($cfg['show_long_dates']) {
            $when = date("m/d/y H:i", $time);
        } else {
            $when = date("H:i", $time);
            $day = date("F j, Y", $time);

            if ($day != $curday) { 
                $str .= "<tr><td class=\"log-hdr\" colspan=2>$day</td></tr>";
                $curday = $day;
                $cnt = 0;
            }
        }

        $clz = ($cnt++ % 2 == 0 ? "even" : "odd");
        $str .= "<tr>";
        if ($cfg['url_pat'] && $cfg['url_key']) {
            $t = "<a href=\"" . sprintf($cfg['url_pat'], $row[$cfg['url_key']]) . "\">$when</a>";
        } else {
            $t = $when;
        }
        $str .= "  <td class=\"log-when\"><nobr>$t</nobr></td>";
        $str .= "  <td class=\"$clz\">";
        $str .= "    <span class=\"log-who\">" . $who . ":</span> ";
        $str .= "    <span class=\"irc-what\">$what</span>";
        $str .= "  </td>\n";
        $str .= "</tr>\n";
    }


    $str .= "</tbody></table>\n";
    if ($cfg['bot_nav']) { $str .= "<div class=\"log-nav\">" . $cfg["bot_nav"] . "</div>\n"; }
    return "$str</div>\n";
}


function render_widget($name, $title, $body) {
    return <<< EOS
    <div class="widget widget-$name">
        <h1>$title</h1>
        <div class="widget-content">
            $body
        </div>
    </div>
EOS;
}

function isMobile() {
    $agent = $_SERVER['HTTP_USER_AGENT'];
    if(preg_match("~Mozilla/[^ ]+ \((iPhone|iPod); U; CPU [^;]+ Mac OS X; [^)]+\) AppleWebKit/[^ ]+ \(KHTML, like Gecko\) Version/[^ ]+ Mobile/[^ ]+ Safari/[^ ]+~",$agent,$match)) {
        return true;
    } elseif(stristr($agent,'iphone') or stristr($agent,'ipod')){
        return true;
    } else {
        return false;
    }
}
?>