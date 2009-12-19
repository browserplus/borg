<?php
class BPServices
{
    private static $apibase = "http://browserplus.yahoo.com/api/v3";
    private static $serviceKey = "bp.services";
    private static $baseKey = "bp.service.%s.%s.%s";
    private static $builtInServices = array("DragAndDrop", "FileBrowse", "InactiveServices", "Log");
    private static $ttl = 1800;
    var $builtinPath = null;
    var $services = null;
    
	function __construct() {
        $this->builtinPath = getenv("DOK_BASE") . "/services/";
    }

    private function getServiceKey($name, $platform, $version) {
        return sprintf(self::$baseKey, $name, $platform, $this->versionNum($version));
    }

    function getAllServices() {
        if ($this->services != null) {
            return $this->services;
        }

        $key = self::$serviceKey;
        $services = apc_fetch($key);

        if ($services == null) {
            $x = fetch(self::$apibase . "/corelets");
            $services = json_decode($x, 1);

            // add some doc to the builtins
            $dnd = "Support drag and drop of files from desktop to web browser.";
            $fb = "Present the user with a native file browser dialog that allows multiple file selection and file filtering.";
            $inact =  "Allows for the exploration of available inactive services, which may be downloaded and activated.";
            $log = "Access plugin logging facilities. The available levels in order of severity are Fatal, Error, Warn, Info, Debug.";

            $builtins = <<< EOS
[
    {"name":"DragAndDrop","versionString":"1.0.1","os":"osx","documentation":"$dnd","CoreletType":"built-in", "CoreletAPIVersion":3,"size":0},
    {"name":"DragAndDrop","versionString":"1.0.1","os":"win32","documentation":"$dnd","CoreletType":"built-in", "CoreletAPIVersion":3,"size":0},
    {"name":"FileBrowse","versionString":"1.0.1","os":"osx","documentation":"$fb","CoreletType":"built-in", "CoreletAPIVersion":3,"size":0},
    {"name":"FileBrowse","versionString":"1.0.1","os":"win32","documentation":"$fb","CoreletType":"built-in", "CoreletAPIVersion":3,"size":0},
    {"name":"InactiveServices","versionString":"1.0.1","os":"osx","documentation":"$inact","CoreletType":"built-in", "CoreletAPIVersion":3,"size":0},
    {"name":"InactiveServices","versionString":"1.0.1","os":"win32","documentation":"$inact","CoreletType":"built-in", "CoreletAPIVersion":3,"size":0},
    {"name":"Log","versionString":"1.0.1","os":"osx","documentation":"$log","CoreletType":"built-in", "CoreletAPIVersion":3,"size":0},
    {"name":"Log","versionString":"1.0.1","os":"win32","documentation":"$log","CoreletType":"built-in", "CoreletAPIVersion":3,"size":0}
]
EOS;

            $builtins = json_decode($builtins, 1);

            foreach($builtins as $b) {
                $services[] = $b;
            }
    
            apc_store($key, json_encode($services), self::$ttl);
        } else {
            $services = json_decode($services, 1);
        }

        $this->services = $services;
        return $services;
    }
    
    function getService($name, $platform, $version) {
        $key = $this->getServiceKey($name, $platform, $version);
        $json = apc_fetch($key);

        if ($json == null) {

            if (in_array($name, self::$builtInServices)) {
                // Built-In Service
                // The api doesn't yet return documentation for built-in services, so
                // we store the equivalent info in a local file 
                $path = $this->builtinPath . $name . "_" . $version . ".json";

                if (file_exists($path)) {
                    $json = file_get_contents($path);                    
                }
            } else {
                // Regular Service, fetch from web services api
                $json = fetch(self::$apibase . "/corelet/metadata/{$name}/{$version}/{$platform}");
            }

            if ($json) apc_store($key, $json, self::$ttl);
        }
        
        return ($json ? json_decode($json, 1) : null);
    }

    
    function versionNum($versionString) {
        $x = explode(".", $versionString);
        return $x[0] * 1000000 + $x[1] * 1000 + $x[2];
    }
    
    function renderServiceHome()
    {
        $latest = array();
        foreach($this->services as $s) {
            $n = $s['name'];
            $vn = $this->versionNum($s['versionString']);
            if ((isset($latest[$n]) && $vn > $latest[$n]['vn']) || !isset($latest[$n])) {
                $latest[$n] = array("vn" => $vn, "doc" => $s['documentation']);
            }
        }

        ksort($latest);
        $str = "# Services\n\n";
        $str .= "The following services are in production at <http://browserplus.yahoo.com/>. ";
        $str .= "With BrowserPlus installed, you can test services with "; 
        $str .= "<a target=\"_blank\" href=\"http://browserplus.yahoo.com/developer/explore/test/\">Service Explorer</a>.\n\n";
        $str .= "### Services Available\n\n";

        foreach($latest as $name => $s) {
            $str .= "[{$name}]({$name}.html)\n";
            $str .= ": {$s['doc']}\n\n";
        }
        $str .= "\n";
        return "<div markdown=\"1\" class=\"api\">\n$str\n</div>\n";
    }

    function renderServiceDoc($name)
    {
        $str = "# {$name}\n";
        $maxvn = 0;
        $maxvs = "";

        $versions = array();
        $validvers = array();

        foreach($this->services as $s) {
            if ($s['name'] == $name) {
                $vs = $s['versionString'];
                $vn = $this->versionNum($vs);
                $key = "{$vn}|$vs";
                if (!isset($versions[$key])) {
                    $versions[$key] = 1;
                    $validvers[$vs] = 1;
                }
                if ($vn >= $maxvn) {
                    $maxvn = $vn;
                    $maxvs = $vs;
                }
            }
        }

        krsort($versions);
        
        if (isset($_GET['v']) && isset($validvers[$_GET['v']])) {
            $version = $_GET['v'];
        } else {
            $version = $maxvs;
        }

        if (false) {
            $str = "# {$name}, v{$version}\n\n";
            $str .= "Doc coming soon...\n";
        } else {
            $str = "# {$name}, v{$version}\n\n";

            // assume services are same for all platforms and fetch the "win32" version
            $service = $this->getService($name, "win32", $version);

            if ($service) {
                $str .= $service['documentation'] . "\n\n";
                
                if ($service['CoreletType'] == "dependent" && isset($service['CoreletRequires'])) {
                    $dn = $service['CoreletRequires']['Name'];
                    $str .= "**NOTE**: {$name} depends on [{$dn}]({$dn}.html).\n\n";
                }
                
                // print all versions
                if (count($versions) > 1) {
                    $links = array();

                    foreach($versions as $v => $ignore) {
                        // array entry is 4000004|4.0.4
                        $arr = explode("|", $v);
                        $v = $arr[1];
                     
                        if ($v == $version) {
                            $links[] = "**{$v}**";
                        } else {
                            $links[] = "[$v]({$name}.html?v={$v})";
                        }
                    }
                    
                    if (count($links) > 0) {
                        $str .= "#### Other Versions\n\n";
                        $str .= join(", ", $links) . "\n\n";
                    }
                }
                

                if (count($service['functions']) > 0) {
                    $funcs = $service['functions'];
                    foreach($funcs as $f) {
                        $str .= "## BrowserPlus.{$name}.{$f['name']}({params}, function{}())\n\n";
                        $str .= $f['documentation'] . "\n\n";

                        $str .= "### Parameters\n\n";
                        
                        if (count($f['parameters']) > 0) {
                            $params = $f['parameters'];
                            foreach($params as $p) {
                                $str .= "{$p['name']}: {$p['type']}";
                                if (!$p['required']) {
                                    $str .= " *(Optional)*";
                                }
                                $str .= "\n";
                                $str .= ": " . $p['documentation'] . "\n\n";
                            }
                        } else {
                            $str .= "*No Parameters*\n\n";
                        }
                    }
                }

                if (false) {
                    $str .= "~~~\n";
                    $str .= "KEY($name): " . $this->getServiceKey($name, "win32", $version) . "\n";
                    $str .= print_r($this->getService($name, "win32", $version), 1);
                    $str .= "~~~\n";
                }

            }
        }

        return "<div markdown=\"1\" class=\"api\">\n$str\n</div>\n";
    }
}