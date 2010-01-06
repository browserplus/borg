<?php

// map file names to page titles
$TitleMap = array(
    "cpp_services" => "C++ Services",
    "service_dev" => "Service Authors",
    "web_dev" => "Web Developers",
    "platform" => "Browser Technologists",
    "services" => "Available Services"
);

// replace @{varname} with below
$VarMap = array(
    "bpver" => "2.4.21",
    "rubyver" => "4.2.6"
);

function getFileScanner($uri) {
    if (strpos($uri, "services/") === 0) {
        return new ServicesFileScanner();
    }
    
    return null;
}

class ServicesFileScanner implements iFileScanner 
{
    var $bp = null;
    var $services = null;
    var $homepage = "00_home";
    
	public function __construct() {
        include("../../php/site.php");
        include("../../php/bpservices.php");
        $this->bp = new BPServices();
    }

    private function basename($file) {
        // return file strip stripped of all path and (the last) extension
        $name = basename($file);
        return substr($name, 0, strrpos($name, "."));        
    }

    public function findFile($datadir, $htmlfile) {
        $bn = $this->basename($htmlfile);
        
        $foundIt = false;
        $services = $this->bp->getAllServices();
        foreach($services as $s) {
            $name = $s['name'];
            if ($name == $bn) $foundIt = true;
            $files["${name}.html"] = "${name}.md";
        }

        ksort($files);
        $home = array("home.html" => "00_home.md");
        $files = array_merge($home, $files);

        $files = array("dirs" => array(), "files" => $files);

        if ($foundIt) {
            return array("{$bn}.html", "{$bn}.md", $files);
        } else {
            return array("home.html", $this->homepage . ".md", $files);            
        }
    }
    
    public function getFileContents($path)
    {
        $name = $this->basename($path);

        if ($name == $this->homepage) {
            return $this->bp->renderServiceHome();
        } else {
            return $this->bp->renderServiceDoc($name);
        }
    }
}

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
                echo "<h1>^<a href=\"$url\">$title</a></h1>";
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