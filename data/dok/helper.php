<?php

// map file names to page titles
$TitleMap = array("c_cpp" => "C/C++");

// replace @{varname} with below
$VarMap = array(
    "bpver" => "2.4.21",
    "rubyver" => "4.2.6"
);


/*
 * Helper Functions for Dok Layout.
 */

/*
 * Rend
 */
function dok_pages_widget($parents, $dirs, $files, $current_file) {
    ob_start();
    if (count($files) > 0 || count($dirs) > 0) {
        echo "<div class=\"widget widget-pages\">\n";
        $cnt = count($parents);

        foreach($parents as $url => $title) {
            if ($cnt-- == 1) {
                echo "<h1>$title</h1>";
            } else {
                echo "<h1>$up<a href=\"$url\">$title</a></h1>";
            }
        }

        echo "<div class=\"widget-content\">\n";
        echo "<ul>\n";
        if (count($files) > 0) {
            foreach($files as $file => $title) {
                if ($file == $current_file) {
                    echo "<li class=\"active\">&raquo; $title</li>\n";
                } else {
                    echo "<li><a href=\"$file\">$title</a></li>\n";
                }
            }
        }

        if (count($dirs) > 0) {
            foreach($dirs as $label => $path) {
                echo "<li><a href=\"$path\">$label/</a></li>";
            }
        }

        echo "</ul>\n";
        echo "</div>\n";
        echo "</div>\n";
    }
    return ob_get_clean();
}

?>