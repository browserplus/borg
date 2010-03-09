<?php require("../../../php/vars.php") ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title>BrowserPlus Upload, Colors and Constraints</title>
<meta name="description" content="File uploading tool that utilizes BrowserPlus to support native drag and drop, file browsing with filters, multiple file upload and progress tracking."><style type="text/css" media="screen">
body {background:black;color:white;font-family:verdana;}
#uploader { width: 600px; height:300px; }
#results {height:200px; width:750px; overflow-y:auto;font-size:8pt;font-family:monospace;background:white;color:black;border:4px solid #69c;}
h3 {margin-top:30px;margin-bottom:5px;}
p {width:650px;line-height:1.4em;font-size:10pt;}
code {background:#369;font-size:0.9em;}
strong {color:#e90;}

</style>
</head>
<body>
<h3>Uploader: Colors and Constraints</h3>

<p>
    <strong>Drag and drop</strong> files from the desktop or select <b>Browse...</b> below to add files to the uploader.
</p>

<p> 
    The uploader in this case is set up to <b>limit</b> the files accepted based on a number of constraints. 
    The constraints limit the uploader to accept just 10 files at a time (<code>maxFiles: 10</code>) with no 
    one file larger than 2,000,000 bytes (<code>maxFileSize: 2000000</code>). Also, the type of file accepted 
    is limited to these mime types: (<code>mimeTypes: ["application/pdf", "image/gif", 
    "image/png", "image/jpeg"]</code>).
</p>


<div id="uploader"></div>

<h3>Uploader Events</h3>

<div id="results"></div>

<script type="text/javascript" src="http://bp.yahooapis.com/<?php echo BROWSERPLUS_JS_VERSION; ?>/browserplus-min.js"></script>	 
<script type="text/javascript" src="http://bp.yahooapis.com/toolbox/uploader/<?php echo UPLOAD_WIDGET_VERSION; ?>/uploader-min.js"></script>	
<script type="text/javascript" src="http://l.yimg.com/a/lib/bp/s/1.106/json2.js"></script>
<script type="text/javascript">
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

        postCB: function(uuid) {
            return { description: "A file description."};
        },
        
        uploadCB: function(res) {
            if (res.success) {
                results.innerHTML = "<div style=\"color:#090\"><b>******** UPLOAD COMPLETE ********</b></div>" + results.innerHTML;
            }
        },
        
        constraintCB: function(res) {
            results.innerHTML = "<div><span style=\"background:#f9e;\">Constraint Violation: " + res.constraint + "</span> file: " + 
                res.name + ", size: " + res.size + ", mimeType: " + res.mimeType + "</div>" + results.innerHTML;
        }
    };
}();

BPTool.Uploader.create("uploader", {
    uploadUrl: "/misc/upload.php", 

    fileCB: my.fileCB,
    postCB: my.postCB,
    uploadCB: my.uploadCB,
    constraintCB: my.constraintCB,

    maxFiles: 10,
    maxFileSize: 2000000,
    mimeTypes: ["application/pdf", "image/png", "image/gif", "image/jpeg"],

    titleColor:"#fff", 
    fileColor:"#eee",
    uploadBackground:"#000", 
    fileBackground: "#456",

    hoverBackground: "#789",
    selectBackground: "#e70",

    progressBackground: "#123",
    progressBarColor:"#e70",

    footerBackground:"#123",
    footerColor:"silver"}).render();
</script>
</body>
</html>