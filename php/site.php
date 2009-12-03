<?php
define("LAYOUT_2C", "site_2c");

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
        "title" => $tilte,
        "active" => $tab,
        "body" => $body,
        "widgets" => $widgets
    );
    
    render_page($data, LAYOUT_2C);
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

/*
 * If linked URL is too long, break it the label by removing everything after the '?' or
 * add <wbr>.
 */ 
function render_line_link_cb($matches) {
    $max = 50;
    $title = $matches[1];
    if (strlen($matches[1]) > $max) {
        if (($p = strpos($matches[1], "?")) < $max) {
            $title = substr($title, 0, $p) . "?...";
        } else {
            $title = preg_replace("/(.{30})/", "\\1<wbr>", $title);
        }
    }
    return "<a target='_blank' href='{$matches[1]}'>$title</a>";
}

/* 
 * Render a line of text, hyperlinking urls and displaying emoticons.
 */
function render_line($s){
    global $EMO_SEARCH, $EMO_REPLACE;
    $s = str_replace($EMO_SEARCH, $EMO_REPLACE, $s);
    $words = preg_split("/[\s,]+/", $s);
    $out = array();
    foreach($words as $w) {
        if (strlen($w) > 30 && !preg_match("#^((ht|f)tps?://)|(git@github.com)#", $w)) {
            $w = preg_replace("/(.{30})/", "\\1<wbr>", $w);
        }
    
        $out[] = $w;
    }

    $s = implode(" ", $out);

    // link hyperlinks
    $host = "([a-z\d][-a-z\d]*[a-z\d]\.)+[a-z][-a-z\d]*[a-z]";
    $port = "(:\d{1,})?";
    $path = "(\/[^?<>\#\"\s]+)?";
    $query = "(\?[^<>\#\"\s]+)?";
    $s = preg_replace_callback("#((ht|f)tps?:\/\/{$host}{$port}{$path}{$query})#i", "render_line_link_cb", $s);

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
?>