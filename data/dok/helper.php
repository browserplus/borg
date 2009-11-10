<?php

// map file names to page titles
$TitleMap = array("c_cpp" => "C/C++");

/*
 * Helper Functions for Dok Layout.
 */

/*
 * Rend
 */
function dok_pages_widget($dirs, $files, $up_dir, $current_file) {
    ob_start();
    if (count($files) > 0 || count($dirs) > 0) {
        echo "<div class=\"widget widget-pages\">\n";
        echo "<h1>Pages (<a href=\"$up_dir\">Up a Level</a>)</h1>\n";
        echo "<div class=\"widget-content\">\n";
        echo "<ul>\n";
        if (count($files) > 0) {
            foreach($files as $file => $title) {
                if ($file == $current_file) {
                    echo "<li class=\"active\">&raquo; $title &laquo;</li>\n";
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