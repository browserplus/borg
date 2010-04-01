<?php require("../../../php/vars.php") ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title>Compress Uploaded Files</title>
<meta name="description" content="File uploading tool that utilizes BrowserPlus to support native drag and drop, file browsing with filters, multiple file upload, file zipping and progress tracking.">

<style type="text/css" media="screen">
#uploader { width: 600px; height:250px; }
#results {height:200px; width:750px; overflow-y:auto;font-size:8pt;font-family:monospace;background:white;color:black;border:4px solid #69c;}
h3 {margin-top:30px;margin-bottom:5px;}
p {width:600px;}
strong {color:#c00;}
em {font-weight:bold;}
</style>

</head>
<body>
<h3>Uploader: Archive and Compress Files</h3>
<p>

Like the Unix command line, BrowserPlus allows you to chain tools together. By setting <strong>archiveFormat</strong> to <em>zip</em>
(or 'zip-uncompressed', 'tar', 'tar-gzip' or 'tar-bzip2'), the <a href="/developer/explore/?s=Archiver">Archiver</a> service is used
to <em>compress</em> all files into a single archive file. For certain file types (text based), this can greatly decrease the size of
the file and increase the upload speed.

</p>
<div id="uploader"></div>

<h3>Uploader Events</h3>
<div id="results"></div>

<script type="text/javascript" src="http://bp.yahooapis.com/<?php echo BROWSERPLUS_JS_VERSION; ?>/browserplus-min.js"></script>	 
<script type="text/javascript" src="http://bp.yahooapis.com/toolbox/uploader/<?php echo UPLOAD_WIDGET_VERSION; ?>/uploader-min.js"></script>	
<script type="text/javascript" src="http://l.yimg.com/a/lib/bp/s/1.106/json2.js"></script><script type="text/javascript">
var my = function() {
    var results = document.getElementById("results");

    return {

        fileCB: function(res) {
            var k, msg;

            if (res.success) {
                msg = "{id: " + res.id + ", action:" + res.action + ", name:" + res.name + ", size:" + 
                    res.size + ", mimeType: " + res.mimeType + ", uuid:" + res.uuid + "}";
                if (res.value) {
                    msg = "<b>--- RESULT(" + res.name + ") ---</b><br>" + 
                        JSON.stringify(res.value).replace(/,/g, ",<br>") + 
                        "<br>==============<br>" + msg;
                }
            } else {
                msg = "<span style=\"color:red\">ERROR(" + res.id + "), upload failed: " + 
                    res.error + " - " + res.verboseError + "</span>";
            }

            results.innerHTML = "<div>" + msg + "</div>" + results.innerHTML;
        },

        uploadCB: function(res) {
            if (res.success) {
                results.innerHTML = "<div style=\"color:#090\"><b>******** UPLOAD COMPLETE ********</b></div>" + results.innerHTML;
            }
        }
    };
}();

BPTool.Uploader.create("uploader", {
    uploadUrl: "/misc/upload.php", 
    fileCB: my.fileCB,
    uploadCB: my.uploadCompleteCB,
    archiveFormat: "zip"
}).render();

</script>
</body>
</html>