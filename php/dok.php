<?php
require("markdown.php");

class Dok 
{
    var $conf;
    var $titlemap_keys;
    var $titlemap_vals;

    public static $default_filename = 'index.html';

	function __construct($conf) {
        $this->conf = $conf;
        $dok_base = get_cfg_var("dok_base");
        $this->conf["site"]   = "$dok_base/dok";
        $this->conf["layout"] = "$dok_base/layout";
        $this->conf['pages']  = "$dok_base/" . $this->conf['pages'] ;
        include($this->conf['site'] . '/helper.php' );
        $this->titlemap_keys = array_keys($TitleMap);
        $this->titlemap_vals = array_values($TitleMap);
    }
    
    private function endswith( $str, $sub ) {
        //return ( substr( $str, strlen( $str ) - strlen( $sub ) ) === $sub );
        return (false !== ($i = strrpos($str, $sub)) && $i === strlen($str) - strlen($sub));
    }
    
    private function scandir($dir) {
	    $dirs = array();
	    $files = array();

	    if ($handle = opendir($dir)) {
		    // correct way of iterating thru with readdir
		    while (false !== ($file = readdir($handle))) {
			    $full = $dir . '/' . $file;
			    if (is_dir($full) && $file != '.' && $file != '..') {
				    $f = basename($file);
				    $dirs[$f]  = $f;
			    } else if (is_file($full)) {
				    $f = basename($file);
					$key = preg_replace("/^(\d+_?)?(.+)(\..+)$/", "\\2.html", $f);
				    $files[$key] = $f;
			    }
		    }

            closedir($handle);
	    }
	
	    return array("dirs"=>$dirs, "files"=>$files);
    }

    private function findFile($datadir, $htmlfile) {
        $srcfile = null;
        $files = null;

        if (file_exists($datadir)) {
            $files = $this->scandir($datadir);

            if ($files["files"][$htmlfile]) {
                // 1. File is specfied in URI and found
                $srcfile = $files["files"][$htmlfile];
            } else if (!$htmlfile && $files["files"][self::$default_filename]) {
                // 2. No file specified, use index.html
                $srcfile = $files["files"][self::$default_filename];                
                $htmlfile = self::$default_filename;
            } else if (!$htmlfile && count($files) > 0) {
                // 3. No file specified and "index" file does not exist.
                //    Show "first" file in directory.   The file can be 
                //    named 000_anything.md and the digits+underscore are
                //    stripped out.
				list($htmlfile,$srcfile) = each($files["files"]);
            }
        }

        return array($htmlfile, $srcfile, $files, $datadir);
    }
    
    private function getFileContents($uri) {

        if ($this->endswith($uri, ".html")) {
            $dir = dirname($uri);
            $htmlfile = basename($uri);
        } else {
	        $dir = ($this->endswith($uri, '/') ? $uri : "$uri/");
            $htmlfile = "";
        }
        
        // correct "/" around dir
        $dir = ($dir == "." ? "/" : ($this->endswith($dir, "/") ? $dir : "$dir/"));
        $dir = ($dir[0] != "/" ? "/$dir" : $dir);

        list($htmlfile, $srcfile, $files, $datadir) = $this->findFile($this->conf['pages'] . "$dir", $htmlfile);
        if ($srcfile) {
            $layout = "dok_2c.php";            
        } else {
            list($htmlfile, $srcfile, $files, $datadir) = $this->findFile($this->conf['site'], "file_not_found.html");
            $layout = "dok_1c.php";
        }

        if ($srcfile) {
	        $body = file_get_contents($datadir . "/$srcfile");

	        $fileext = substr($srcfile, strrpos($srcfile, '.')+1);
            $titles = array();
            foreach(array_keys($files["files"]) as $file) {
                $titles[$file] = $this->title_from_file($file);
            }

            $dirs = array();
            foreach(array_keys($files["dirs"]) as $name) {
                $dirs[$name] = $this->conf['baseurl'] . $dir . $name . "/";
            }

            $up_dir = ($dir == "/") ? "/" : $this->conf['baseurl'] . dirname(substr($dir, 0, -1));

	        return array(
                "filename" => $htmlfile,
                "title" => $this->title_from_file($htmlfile),
                "type" => $fileext,
	            "body" => $body,
	            "dir"  => $this->conf['baseurl'] . $dir,
	            "up_dir" => $up_dir,
	            "dirs" => $dirs,
	            "files" => $titles,
	            "layout" => $layout
	        );
        } else {
            return null;
        }
    }

    private function title_from_file($file) {
        $str = preg_replace("/^(\d+_?)?(.+)(\..+)$/", "\\2", $file);
        $str = str_replace($this->titlemap_keys, $this->titlemap_vals, $str);
        $str[0] = strtoupper($str[0]);
        $func = create_function('$s', 'return " " . strtoupper($s[1]);');
        $str = preg_replace_callback('/_([a-z])/i', $func, $str);

        return $str;
    }

    function render($uri) {
        $data = $this->getFileContents($uri);
        if ($data) {
            $this->render_one($data);
        } else {
            echo "Bad Request";
        }
    }

    function render_one($data) {
        if ($data["type"] == "raw") {
            echo $data["body"];
        } else {
            $page = array_merge($data, $this->conf['vars']);
            extract($page);

            if ($type == "md") {
                $body = markdown($body);
            }

            // DBG - set to false
            if (false) { 
                // copy array and remove body so it doesn't take so much room
                $arr = $page;
                $arr["body"] = "... [removed for debug]...";
                $body .= "<hr><h1>Debug</h1><pre>";
                $body .= print_r($arr, 1);
                //$body .= print_r($_SERVER, 1);
                $body .= "</pre>";
            }

            include $this->conf['layout'] . "/" . $layout;
        }            
    }
}

function dok($conf, $uri)
{
    $d = new Dok($conf);
    $d->render($uri);
}
?>