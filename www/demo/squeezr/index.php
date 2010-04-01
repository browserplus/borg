<?php 
require("../../../php/vars.php");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <title>Bit Squeezr</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/combo?2.8.0r4/build/reset-fonts-grids/reset-fonts-grids.css&2.8.0r4/build/base/base-min.css">
    <link rel="stylesheet" type="text/css" href="bitsqueezr.css" media="screen">
</head>
<body>
    <div id="doc">
        <div id="hd">
            <h1>Bit Squeezr</h1>
        </div>
    
        <div id="bd">
            <p>
                Drag and drop the one or more files and folders below that you wish to combine and compress (using 
                <a href="http://en.wikipedia.org/wiki/Tar_(file_format)">tar</a> and 
                <a href="http://en.wikipedia.org/wiki/LZMA">LZMA</a>).
            </p>

            <div id="seq">
                <div id="dropitem" class="active">
                    <span id="droptext"></span>
                </div>
                <div id="combineitem" class="pending">
                    <span id="combinetext"></span> 
                </div>
                <div id="compressitem" class="pending">
                    <span id="compresstext"></span> 
                </div>
                <div id="downloaditem" class="pending">
                    <span id="downloadtext"></span>
                    <span id="resettext" style="display:none">(<a href="#" id="reset"></a>)</span>
                </div>
            </div>

            <p>
               <a href="http://en.wikipedia.org/wiki/LZMA">LZMA</a> is a compression algorithm 
               that <i>out-performs nearly all others in terms of ultimate file size</i>.  It 
               takes a little longer to run and requires more memory, but given the 
               fast multicore cpu and never fast enough internet connection you're probably using, 
               it's worthwhile.  Happy bit squeezing!
            </p>
        </div>
<form id="super_form" method="post" action="/file/">
    <input type="hidden" id="download" name="download" />
</form>

        <div id="ft">
            <p>
                <a href="http://browserplus.yahoo.com/">A BrowserPlus&trade; thang!</a>
            </p>
        </div>
    </div>
<script src="http://bp.yahooapis.com/<?php echo BROWSERPLUS_JS_VERSION; ?>/browserplus-min.js"></script>
<script src="http://bp.yahooapis.com/toolbox/installer/<?php echo INSTALL_WIDGET_VERSION; ?>/install-min.js"></script> 
<script type="text/javascript" src="bitsqueezr.js"></script>
</body>
</html>