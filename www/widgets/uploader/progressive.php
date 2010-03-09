<?php require("../../../php/vars.php") ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title>BrowserPlus Upload, Progressive Enhancement</title>
<meta name="description" content="File uploading tool that utilizes BrowserPlus to support native drag and drop, file browsing with filters, multiple file upload and progress tracking.">
<style type="text/css" media="screen">
    form {background: #ddd;border:1px solid #ccc;padding:10px;}
    .orig {margin-bottom:10px;padding:10px; border:1px solid #999; background:white;}
    .enhancer {margin-top:20px;}
    h4 {margin:0 0 1em 0;}
</style>
</head>
<body>
    <h3>Uploader: Progressive Enhancement</h3>
    <p>
        This demo starts with a standard, working &lt;input type="file"&gt; file upload tool.
        Press the button below to transform the plain HTML upload field to a rich upload
        widget that supports multiple file selection, folder selection, drag + drop from the 
        desktop and ZIP compression.
    </p>
    <form id="uploadForm" method="post" action="/misc/upload.php">
        <h4>Upload a single file</h4>
        <input class="orig" type="FILE" name="file">
        <br>
        <input type="SUBMIT" value="Upload!">
    </form>

    <div class="enhancer">
        <input type="button" id="b1" value="Enhance Upload Experience">
    </div>
</body>
<script type="text/javascript" src="http://bp.yahooapis.com/<?php echo BROWSERPLUS_JS_VERSION; ?>/browserplus-min.js"></script>	 
<script type="text/javascript" src="http://bp.yahooapis.com/toolbox/uploader/<?php echo UPLOAD_WIDGET_VERSION; ?>/uploader-min.js"></script>	
<script type="text/javascript">

function uploadEnhancer(e) {
    var i, j, div, action, 
        numForms, numItems, 
        item, form, forms = document.getElementsByTagName("form"), 
        uploaderId="uploader";

    // replace the first FORM with a [input type="file"] element
    numForms = forms.length;
    for (i=0; i < numForms; i++) {
        form = forms[i];
        numItems = form.childNodes.length;
        for (j = 0; j < numItems; j++) {
            item = form.childNodes[j];
            
            if (item.tagName == "INPUT" && item.type == "file") {
                action = form.getAttribute("action");
                div = document.createElement("div");
                div.setAttribute("id", uploaderId);
                div.setAttribute("style", "width:600px;height:200px;");
                form.parentNode.insertBefore(div, form);
                form.parentNode.removeChild(form);
                BPTool.Uploader.create(uploaderId, {uploadUrl: action}).render();
                this.style.display="none";

                return false;
            }
        }
    }
}

BrowserPlus.init({},function(res) {
    if (res.success) {
        document.getElementById('b1').onclick = uploadEnhancer;
    } else {
        document.getElementById('b1').onclick = function() {
            alert("Sorry, can't enhance your upload experience without BrowserPlus.");
        };
    }
});

</script>

</html>