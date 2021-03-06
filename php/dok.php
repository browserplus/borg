<?php
require("markdown.php");
require("vars.php");

function dok_file_sort($a, $b) {
    return strcasecmp($a, $b);
}

interface iFileScanner
{
    public function findFile($datadir, $htmlfile);
    public function getFileContents($path);
}

class Dok implements iFileScanner
{
    private $conf;
    private $varmap_keys;
    private $varmap_vals;
    private $scanner;
    private $titlemap;
    private $linkmap;
    private $pagedata;
    
    public static $default_filename = 'index.html';

	public function __construct($conf) {
        $this->conf = $conf;
        $dok_base = getenv("DOK_BASE");
        $this->conf["site"]   = "$dok_base/dok";
        $this->conf["layout"] = "$dok_base/layout";
        $this->conf['pages']  = "$dok_base/" . $this->conf['pages'] ;
        include($this->conf['site'] . '/helper.php' );

        // Maps certain file names (like cpp_services) to titles (C++ Services) 
        $this->titlemap = $TitleMap;

        // Remembers the contents in *.link files so we can have external links in the 
        // navigation bar
        $this->linkmap = array();
        
        // DokVarMap replaces @{varname} variables in documents
        $keys = array_keys($VarMap);
        
        $this->varmap_keys = array();
        foreach($keys as $k) {
            $this->varmap_keys[] = '@{' . $k . '}';
        }
        
        $this->varmap_vals = array_values($VarMap);
    }
    
    private function endswith( $str, $sub ) {
        //return ( substr( $str, strlen( $str ) - strlen( $sub ) ) === $sub );
        return (false !== ($i = strrpos($str, $sub)) && $i === strlen($str) - strlen($sub));
    }
    
    private function getHierarchy($path) {
        $orig = $path;
        if ($path[0] == "/") $path = substr($path, 1);
        if ($path[strlen($path)-1] == "/") $path = substr($path, 0, -1);
        $arr = explode("/",$path);
        $cur = "";
        $ret = array();

        $ret [$this->conf['baseurl']. '/'] = $this->conf['basename'];

        if ($path != "") {
            foreach($arr as $c) {
                $cur .= "/$c";
                $ret[$this->conf['baseurl'] . "{$cur}/"] = $this->title_from_file($c);
            }
        }
        
        return $ret;
    }

    private function getPage($uri) {

        if ($this->endswith($uri, ".html")) {
            $dir = dirname($uri) . "/";
            $htmlfile = basename($uri);
        } else {
	        $dir = $uri;
            $htmlfile = "";
        }

		if (empty($dir)) { $dir = ".";}
		$dir = ($dir[0] == "." ? "/" : "/${dir}");
        $datadir = $this->conf['pages'] . $dir;

		$dbg =false;
        if ($dbg) echo "<pre>findFile('$datadir', '$htmlfile')\n";
        list($htmlfile, $srcfile, $files) = $this->scanner->findFile($datadir, $htmlfile);

        if ($dbg) {
            echo "datadir=$datadir\n";
            echo "htmlfile=$htmlfile\nsrcfile=$srcfile\n";
            print_r($files);
            echo "</pre>";
        }


        if ($srcfile) {
            $layout = "dok_2c.php";            
        } else {
            $datadir = $this->conf['site'] . "/";
            $layout = "dok_1c.php";
            list($htmlfile, $srcfile, $files) = $this->scanner->findFile($datadir, "file_not_found.html");
        }

        if ($srcfile) {
	        $body = $this->scanner->getFileContents($datadir . "$srcfile");

	        $fileext = substr($srcfile, strrpos($srcfile, '.')+1);
            $titles = array();
            foreach(array_keys($files["files"]) as $file) {
                $titles[$file] = $this->title_from_file($file);
            }

            $hierarchy = $this->getHierarchy($dir);

            $dirs = array();
            foreach(array_keys($files["dirs"]) as $name) {
                $dirs[$this->title_from_file($name)] = $this->conf['baseurl'] . $dir . $name . "/";
            }

            
	        return array(
                "filename" => $htmlfile,
                "parents" => $hierarchy,
                "datadir" => $datadir,
                "title" => $this->title_from_file($htmlfile),
                "linkmap" => $this->linkmap,
                "type" => $fileext,
	            "body" => $body,
	            "dirs" => $dirs,
	            "files" => $titles,
	            "layout" => $layout
	        );
        } else {
            return null;
        }
    }

    private function title_from_file($file) {
        static $func;

        $str = preg_replace("/^(\d+_?)?(.+)(\..+)$/", "\\2", $file);

        // create the strtoupper func just once
        if (!$func) $func = create_function('$s', 'return " " . strtoupper($s[1]);');
        
        // optionally map file name to pretty title
        if (isset($this->titlemap[$str])) $str = $this->titlemap[$str];

        // convert file_name to File_Name
        $str[0] = strtoupper($str[0]);
        $str = preg_replace_callback('/_([a-z])/i', $func, $str);

        return $str;
    }

    private function fmt_code_with_line_numbers($code, $extra_tr="") {
        $lines = explode("\n", trim($code));
        $cnt = count($lines);
        $str = implode("\n", $lines);
        $str = htmlentities($str);
        $tbl = "\n<table>\n<tr><td><pre class=\"linenumbers\">";
        if ($cnt < 10) { 
            $fmt = "%d\n";
        } else if ($cnt < 100) {
            $fmt = "%02d\n";
        } else {
            // if we're showing more than 1,000 code of lines at a time, we're doing something wrong
            $fmt = "%03d\n";
        }

        for($i = 1; $i <= $cnt; $i++) {
            $tbl .= sprintf($fmt, $i);
        }
        $tbl .= "</pre></td><td><pre class=\"prettyprint\">$str</pre></td></tr>\n";
        if ($extra_tr) {
            $tbl .= $extra_tr;
        }
        $tbl .= "</table>\n\n";

        return $tbl;
    }

    // replace @{action data} vars
    // actions: 
    //     include - include example source code
    //     service - link to service doc
    private function at_actions($matches) {
        $action = $matches[1];
        if ($action == "include") {
            // include source code
            $dir = $this->pagedata['datadir'];
            $path = $dir . $matches[2];
            if (file_exists($path)) {
                $code = file_get_contents($path);
                // replace @{var} patterns in file (for things like bpver).
                $code = str_replace($this->varmap_keys, $this->varmap_vals, $code);
                $tr = "<tr><td class=\"runex\" colspan=2 align=\"right\"><a href=\"" . 
                    str_replace(".raw", ".html", $matches[2]) . "\">Run Example</a></td></tr>";
                $str = $this->fmt_code_with_line_numbers($code, $tr);

            } else {
                $str = "<pre style=\"color:#900\">ERROR - could not find\n$path</pre>";
            }
        } else if ($action == "service" || $action == "s" || $action == "svc" || $action == "sv") {
            $s = $matches[2];
            $str = "<a href=\"/docs/services/{$s}.html\">$s</a>";
        }
        return $str;
    }

    private function _renderit($data) {
        if ($data["type"] == "raw") {
            // Substitute our homegrown @{varname} syntax.  Values set in data/dok/helper.php
            $body = $data["body"];
            $body = str_replace($this->varmap_keys, $this->varmap_vals, $body);
            echo $body;
        } else {
            $page = array_merge($data, $this->conf['vars']);
            extract($page);

            // Two options at this point, Markdown or HTML
            if ($type == "md") {
                $body = markdown($data['body']);
            }

            // Substitute our homegrown @{varname} syntax.  Values set in data/dok/helper.php
            $body = str_replace($this->varmap_keys, $this->varmap_vals, $body);

            // replace @{insert examples/file_name.html}
            //         @{service DragAndDrop}
            $body = preg_replace_callback('/@\{([a-z]+)\s*(.+?)\}/i', array(&$this, 'at_actions'), $body);

            // Using Google Code Prettyify cause it seems to be the lightest 
            // weight JavaScript prettifier.  Just 2 files.
            //     http://google-code-prettify.googlecode.com/
            //
            // If there is any code to be highlighted in body, include syntax highlighting
            //    0. code is marked with [pre class="prettyprint"]code[/code]
            //    1. body onload=prettyPrint
            //    2. include css file
            //    3. include js  file
            if (strpos($body, '<pre class="prettyprint">') !== false) {
                $onload = "onload=\"prettyPrint()\"";
                $stylesheets = array("/syntaxhighlighter/prettify.css");
                $jslibs = array("/syntaxhighlighter/prettify.js");
            }

            // Print out all the variables sent to the templates.
            if (false) { 
                // copy array and remove body so it doesn't take so much room
                $arr = $page;
                $arr["body"] = "... [removed for debug]...";
                $body .= "<hr><h1>Debug</h1><pre>";
                $body .= print_r($arr, 1);
                //$body .= print_r($_SERVER, 1);
                $body .= "</pre>";
            }

            // Include layout.  All variables in this function are visible to the template.
            unset($data);
            include $this->conf['layout'] . "/" . $layout;
        }            
    }

    // return all files + dirs in the given directory
    private function getDirContents($dir) {
	    $dirs = array();
	    $ftmp = array(); // tmp array to be sorted
        $fmap = array(); // array keeps key->file association
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
                    if (preg_match("/.link$/", $f)) {
                        // just get first word in first line in *.link file
                        $lines = file($full);
                        $words = preg_split("/[\s]+/", $lines[0]);
                        $this->linkmap[$key] = $words[0];
                    }

				    $fmap[$key] = $f;
				    $ftmp[$f] = $key; // to be sorted via actual file name
			    }
		    }

            closedir($handle);
	    }

	    // case-insensitive sort of file names
	    // need custom sort to get case-insensivity
        uksort($ftmp, "dok_file_sort");

        // exchange file/value
        $fsort = array();
        foreach($ftmp as $f => $key) {
            $fsort[$key] = $fmap[$key];
        }
        
	    return array("dirs"=>$dirs, "files"=>$fsort);
    }

    public function render($uri) {
        $this->scanner = getFileScanner($uri);
        if ($this->scanner == null) {
            $this->scanner = $this;
        }

        $data = $this->getPage($uri);
        if ($data) {
            // setting page for preg_callback type functions that need access to the data
            $this->pagedata = $data;
            $this->_renderit($data);
        } else {
            echo "Bad Request";
        }
    }


    //
    // Default implemenation of iFileScanner that works off of file system.
    // The file scanner can be overriden based on the URL.  In helper.php
    // getFileScanner(uri) holds the mapping.
    //

    // returns the file to render in the given directory
    public function findFile($datadir, $htmlfile) {
        $srcfile = null;
        $files = null;

        if (file_exists($datadir)) {
            $files = $this->getDirContents($datadir);

            if (isset($files["files"][$htmlfile])) {
                // 1. File is specfied in URI and found
                $srcfile = $files["files"][$htmlfile];
            } else if (!$htmlfile && isset($files["files"][self::$default_filename])) {
                // 2. No file specified, use index.html
                $srcfile = $files["files"][self::$default_filename];                
                $htmlfile = self::$default_filename;
            } else if (!$htmlfile && count($files) > 0) {
                // 3. No file specified and "index" file does not exist.
                //    Show "first" file in directory.   The file can be 
                //    named 000_anything.md and the digits+underscore are
                //    stripped out.
				list($htmlfile, $srcfile) = each($files["files"]);
            }
        }

        return array($htmlfile, $srcfile, $files);
    }
    
    // returns the contents ("body") of the given path
    public function getFileContents($path) {
        return file_get_contents($path);
    }
}

function dok($conf, $uri)
{
	if (substr($uri, 0, 6) == "demos/") {
		header("Location: /demos/");
		exit;
	}

	// uri doesn't end with either "/" or file extension
	if ($uri && !preg_match('/\.[a-z0-9]+|\/$/', $uri)) {
		// so it must be a directory without trailing slash, redirect with slash
		header("Location: http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REDIRECT_URL'] . '/');
		exit;
	}

    $d = new Dok($conf);
    $d->render($uri);
}
?>