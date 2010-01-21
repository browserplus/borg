<?php

// Common functions for web services
function wsIsValidVersion($version) {
    return preg_match('/^\d{1,5}\.\d{1,5}\.\d{1,5}$/', $version);
}
    
function wsIsValidPlatform($platform) {
    return preg_match('/^(win32|osx)$/', $platform);
}

?>