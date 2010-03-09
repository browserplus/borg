<?php require("../../../php/vars.php") ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <title>BrowserPlus File Upload, Upload Events</title>
        <meta name="description" content="File uploading tool that utilizes BrowserPlus to support native drag and drop, file browsing with filters, multiple file upload and progress tracking.">

        <style type="text/css" media="screen">
        #uploader { width: 600px; height:280px; }
        h3 {margin-top:30px;margin-bottom:5px;}
        p {width:600px;}
        strong {color:#c00;}
        </style>
    </head>

    <body>
        <h3>Uploader: Default Look and Feel</h3>
        <p>
            <strong>Drag and drop</strong> files from the desktop or select <strong>Browse...</strong> 
            below to add files to the uploader.
        </p>

        <div id="uploader"></div>

		<script type="text/javascript" src="http://bp.yahooapis.com/<?php echo BROWSERPLUS_JS_VERSION; ?>/browserplus-min.js"></script>	 
		<script type="text/javascript" src="http://bp.yahooapis.com/toolbox/uploader/<?php echo UPLOAD_WIDGET_VERSION; ?>/uploader-min.js"></script>	
        <script type="text/javascript">
        BPTool.Uploader.create("uploader", {uploadUrl: "/misc/upload.php"}).render();
        </script>
    </body>
</html>