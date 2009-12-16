<?php
class BPServices
{
    private static $apibase = "http://browserplus.yahoo.com/api/v3";
    private static $serviceKey = "bp.services";
    private static $baseKey = "bp.service.%s.%s.%s";
    
    var $services = null;
    
    private function getServiceKey($name, $platform, $version) {
        return sprintf(self::$baseKey, $name, $platform, $version);
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
    
            apc_store($key, json_encode($services));
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
            $json = fetch(self::$apibase . "/corelet/metadata/{$name}/{$version}/{$platform}");
            if ($json) apc_store($key, $json);
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
        $str .= "The following services are in production.\n\n";
        foreach($latest as $name => $s) {
            $str .= "[{$name}]({$name}.html)\n";
            $str .= ": {$s['doc']}\n\n";
        }
        $str .= "\n";
        return "<div markdown=\"1\" class=\"api\">\n$str\n</div>\n";
    }

    function renderServiceDoc($name, $platform=null, $version=null)
    {
        $str = "# {$name}\n";
        $maxvn = 0;
        $maxvs = "";
        $maxos = "";

        $versions = array();

        foreach($this->services as $s) {
            if ($s['name'] == $name) {
                $vs = $s['versionString'];
                $os = $s['os'];
                $vn = $this->versionNum($vs);
                $versions[] = "{$vn}_{$os}_$vs";
                if ($vn >= $maxvn) {
                    $maxvn = $vn;
                    $maxvs = $vs;
                    $maxos = $os;
                }
            }
        }
        
        rsort($versions);

        if (!$platform) $platform = $maxos;
        if (!$version) $version = $maxvs;

        $s_os = ($platform == "ind" ? "" : "($platform)");
        if ($platform == "ind") $platform = "win32";
        //$str = "# {$name}{$s_os}, v{$version}\n\n";
        $str = "# {$name}, v{$version}\n\n";
        $str .= "Doc coming soon...\n";
        /*
        $str .= "~~~\n";
        $str .= "KEY($name): " . $this->getServiceKey($name, $platform, $version) . "\n";
        $str .= print_r($this->getService($name, $platform, $version), 1);
        $str .= "~~~\n";
        */
        return "<div markdown=\"1\" class=\"api\">\n$str\n</div>\n";
    }
}
