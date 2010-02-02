<?php
include "../../../php/site.php";
include "../../../php/ws.php";
include "../../../php/bpservices.php";

// return value
$json = null;

// url is "/api/v1/service/<service_name>/<version>/<platform>"
$uri = explode("/", getenv('REQUEST_URI'));

$len = count($uri);
if (count($uri) > 3) {

    $service = $uri[$len-3];
    $version = $uri[$len-2];
    $platform = $uri[$len-1];

    if (strlen($service) > 0 && wsIsValidVersion($version) && wsIsValidPlatform($platform)) {
        $bp = new BPServices();
        $json = $bp->getService($service, $platform, $version);
    }
}

header("Content-Type: application/json");      

if ($json) {
    echo $json;
} else {
    // Error
    $out = array(
        "code" => 400,
        "description" => "Bad Request",
        "detail" => "Malformed Syntax or bad query");

    header("HTTP/1.0 {$out['code']} {$out['description']}");
    echo json_encode($out);
}

?>