<?php
class BPServices
{
	private static $apibase = "http://browserplus.yahoo.com/api/v3";
	private static $serviceKey = "bp.services";
	private static $serviceNamesKey = "bp.services.names";
	private static $baseKey = "bp.service.%s.%s.%s";
	private static $builtInServices = array("DragAndDrop", "FileBrowse", "InactiveServices", "Log");
	private static $ttl = 1800;
	var $builtinPath = null;
	
	function __construct() {
		$this->builtinPath = getenv("DOK_BASE") . "/services/";
	}

	private function getServiceKey($name, $platform, $version) {
		return sprintf(self::$baseKey, $name, $platform, $this->versionNum($version));
	}

	function getAllServiceNamesAndVersions($os) {
		$key = self::$serviceNamesKey;
		$json = apc_fetch($key);

		if ($json == null) {
			
			// get (json) services as php object
			$sj = $this->getAllServices();
			if (!$sj) return null;
			$services = json_decode($sj, 1);
			
			$names = array();
			foreach($services as $s) {
				// filter services by OS and store all versions per service(name)
				if ($s['os'] == "ind" || $s['os'] == $os) {
					$n = $s['name'];
					$v = $s['versionString'];
				
					if (isset($names[$n])) {
						$names[$n][] = $v;
					} else {
						$names[$n] = array($v);
					}
				}
			}
		
			ksort($names);
			$json = json_encode($names);

			apc_store($key, $json, self::$ttl);
		}

		return $json;
	}

	function getAllServices() {
		$key = self::$serviceKey;
		$json = apc_fetch($key);

		if ($json == null) {
			$json = fetch(self::$apibase . "/corelets");
			$services = json_decode($json, 1);

			// add some doc to the builtins
			$dnd = "Support drag and drop of files from desktop to web browser.";
			$fb = "Present the user with a native file browser dialog that allows multiple file selection and file filtering.";
			$inact =  "Allows for the exploration of available inactive services, which may be downloaded and activated.";
			$log = "Access plugin logging facilities. The available levels in order of severity are Fatal, Error, Warn, Info, Debug.";

			$builtins = <<< EOS
[
	{"name":"DragAndDrop","versionString":"2.0.0","os":"osx","documentation":"$dnd","CoreletType":"built-in", "CoreletAPIVersion":3,"size":0},
	{"name":"DragAndDrop","versionString":"2.0.0","os":"win32","documentation":"$dnd","CoreletType":"built-in", "CoreletAPIVersion":3,"size":0},
	{"name":"DragAndDrop","versionString":"1.0.1","os":"osx","documentation":"$dnd","CoreletType":"built-in", "CoreletAPIVersion":3,"size":0},
	{"name":"DragAndDrop","versionString":"1.0.1","os":"win32","documentation":"$dnd","CoreletType":"built-in", "CoreletAPIVersion":3,"size":0},
	{"name":"FileBrowse","versionString":"2.0.0","os":"osx","documentation":"$fb","CoreletType":"built-in", "CoreletAPIVersion":3,"size":0},
	{"name":"FileBrowse","versionString":"2.0.0","os":"win32","documentation":"$fb","CoreletType":"built-in", "CoreletAPIVersion":3,"size":0},
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
	
			$json = json_encode($services);
			apc_store($key, $json, self::$ttl);
		}

		return $json;
	}
	
	//
	// Return description of a service as JSON string. 
	//
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

		return $json;
	}
	
	function versionNum($versionString) {
		$x = explode(".", $versionString);
		return $x[0] * 1000000 + $x[1] * 1000 + $x[2];
	}
	
	function renderServiceHome()
	{
		$latest = array();
		
		// get (json) services as php object
		$json = $this->getAllServices();
		if (!$json) return "";
		$services = json_decode($json, 1);
		
		foreach($services as $s) {
			$n = $s['name'];
			$vn = $this->versionNum($s['versionString']);
			if ((isset($latest[$n]) && $vn > $latest[$n]['vn']) || !isset($latest[$n])) {
				$latest[$n] = array("vn" => $vn, "doc" => h($s['documentation']));
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

		// get (json) services as php object
		$json = $this->getAllServices();
		if (!$json) return "";
		$services = json_decode($json, 1);
		
		foreach($services as $s) {
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
			$str = "<a href=\"/explore/?s={$name}&v={$version}\"><img src=\"/images/explore_button.png\" border=\"0\" align=\"right\"></a>\n\n";
			$str .= "# {$name}, v{$version}\n\n";

			// assume services are same for all platforms and fetch the "win32" version
			$sj = $this->getService($name, "win32", $version);
			if ($sj) $service = json_decode($sj, 1);

			if ($service) {
				$str .= h($service['documentation']) . "\n\n";
				
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
						$str .= "<a name=\"{$f['name']}\"></a>\n";
						$str .= "## BrowserPlus.{$name}.{$f['name']}({params}, function{}())\n\n";
						$str .= h($f['documentation']) . "\n\n";
						$str .= "Test: <a href=\"/explore/?s={$name}&f={$f['name']}&v=$version\">{$f['name']}()</a>\n\n";
						$str .= "### Parameters\n\n";

						if (count($f['parameters']) > 0) {
							$params = $f['parameters'];
							foreach($params as $p) {
								$str .= "{$p['name']}: {$p['type']}";
								if (!$p['required']) {
									$str .= " *(Optional)*";
								}

								$str .= "\n";

								if ($name == "ImageAlter" && $f['name'] == "transform" && $p['name'] == "actions") {
									$str .= ": " . $this->fmtImageAlter($p['documentation']) . "\n\n";
								} else {
									$str .= ": " . h($p['documentation']) . "\n\n";
								}
							}
						} else {
							$str .= "*No Parameters*\n\n";
						}
					}
				}
			}
		}

		return "<div markdown=\"1\" class=\"serviceapi api\">\n$str\n</div>\n";
	}
	
	function fmtImageAlter($doc) {
		$marker = "Supported actions include:";
		
		$pos = strpos($doc, $marker);
		if ($pos === FALSE) {
			return "NOPE";
		} 
		
		$pos += strlen($marker);
		$p1 = trim(substr($doc, 0, $pos)) . "  \n";
		
		$p2 = trim(substr($doc, $pos));
		$p2 = str_replace("|", "  \n", $p2);

		$p2 = preg_replace("/([a-z_]+)\s+--/", "**\\1**: ", $p2);

		return $p1 . $p2;
	}

}
