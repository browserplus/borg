<?php 
require("../../../php/vars.php");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
    "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <title>Spacebook - Profile Creation Demo</title>


    <!-- Combo-handled YUI CSS files: -->
    <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/combo?2.7.0/build/reset-fonts-grids/reset-fonts-grids.css&2.7.0/build/assets/skins/sam/skin.css">
    <link rel="stylesheet" type="text/css" href="style.css" media="screen">


</head>
<body class="yui-skin-sam">
    <!-- doc:750C, doc2:950C, doc3:100% 10px, doc4:974C -->
    <!-- t1:160L, t2:180L, t3:300L, t4:180R, t5:240R, t6:300R -->
    <div id="doc" class="yui-t1">
        <div id="hd"><img src="img/logo.png"></div>
        <div id="bd">
            <div id="yui-main">
                <div class="yui-b">
                    <!-- primary block -->
                    <h1>Profile Image Editor</h1>
                    <p>
                        This demo utilizes image and upload services to create a powerful profile editor.
                        Users drag photos from the desktop and drop it on their current profile picture.
                        The picture may be transformed with image filters and cropping.  After every 
                        manipulation, the image is automatically uploaded without user intervention and
                        without page refresh.
                    </p>
                    <h2>DragAndDrop</h2>
                    <p>
                        <a href="/developer/explore/?s=DragAndDrop">Drag &amp; Drop</a> is 
                        a service built in to BrowserPlus.  Users drag files from the desktop and drop them on 
                        designated areas of the browser window.
                        In this demo, the <a id="showDropTarget" href="#">designated area</a> is the 
                        profile picture.
                    </p>
                    <h2>FileAccess</h2>
                    <p><a href="/developer/explore/?s=FileAccess">FileAccess</a> gives developers
                        JavaScript access to <em>the contents of</em> 
                        or <em>a url to</em> the file that the user has selected.
                    </p>
                    <h2>ImageAlter</h2>
                    <p>
                        <a href="/developer/explore/?s=ImageAlter">ImageAlter</a>
                        provides JavaScript developers with image filters, scaling and cropping
                        tools.  Users may choose to crop their image or apply filters like grayscale,
                        sepia and more.
                    </p>
                    <h2>Uploader</h2>
                    <p>
                        <a href="/developer/explore/?s=Uploader">Uploader</a> allows one or more
                        files to be uploaded simultaneously.  Here, the image is uploaded automatically
                        when the user first drops a new image, and when the photo is manipulated with 
                        ImageAlter.
                    </p>
                </div>
            </div>
            <div class="yui-b">
                <!-- secondary block -->
                <div id="profile" class="hoverNo">
                    <img id="profileImage" src="img/profile.png" alt="Click to change your profile picture">
                </div>
                <img id="progress" style="visibility:hidden" src="img/3MA_processingbar.gif">
                <p class="small">
                    <span id="status">Loading...</span>
                    <span id="dblclickhelp" class="high"></span>
                    
                </p>
            </div>
        </div>
        <div id="ft">

        </div>
        <div id="dialog" style="visibility:hidden;display:none;">
            <div class="hd">Change Your Profile</div>
            <div class="bd" style="height:350px">
                <div id="actions">
                    <img id="action_crop" src="img/a_crop.png" title="Crop">
                    <img id="action_grayscale" src="img/a_grayscale.png" title="Grayscale Filter">
                    <img id="action_sepia" src="img/a_sepia.png" title="Sepia Filter">
                    <img id="action_solarize" src="img/a_solarize.png" title="Solarize Filter">
                    <img id="action_oil_paint" src="img/a_oilpaint.png" title="Oil Paint Filter">
                    <img id="action_nada" src="img/a_original.png" title="No Filter">
                </div>
                <div id="well">
                    <img id="dialogImage" src="img/profile.png">
                </div>
            </div>
            <div class="ft">
                <input id="cancel" type="button" value="Cancel">
                <input id="ok" type="button" value="OK">
            </div>
        </div>

    </div>
    <!-- Combo-handled YUI JS files: -->
<script src="http://yui.yahooapis.com/combo?2.7.0/build/yahoo-dom-event/yahoo-dom-event.js&2.7.0/build/dragdrop/dragdrop-min.js&2.7.0/build/container/container-min.js&2.7.0/build/element/element-min.js&2.7.0/build/resize/resize-min.js&2.7.0/build/imagecropper/imagecropper-min.js"></script> 
<script src="http://bp.yahooapis.com/<?php echo BROWSERPLUS_JS_VERSION; ?>/browserplus-min.js"></script>
<script src="http://bp.yahooapis.com/toolbox/installer/<?php echo INSTALL_WIDGET_VERSION; ?>/install-min.js"></script> 
<script src="spacebook.js"></script>
</body>
</html>