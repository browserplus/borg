<?php 
require("../../../php/vars.php");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd"> 
<html>
<head>
<title>PhotoDrop (BrowserPlus&trade;)</title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">

<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/combo?2.7.0/build/reset-fonts-grids/reset-fonts-grids.css&2.7.0/build/assets/skins/sam/skin.css">
<link rel="stylesheet" type="text/css" href="photodrop.css">

</head>

<body class="yui-skin-sam">
<div id="doc2">
<form name="fuploadr" id="fuploadr" method="post">
<div class="hd clearfloat">
    <img src="img/logo.png" alt="BrowserPlus&trade; PhotoDrop" style="float:right;padding:12px 4px">
    <table id="signin"><tbody>
        <tr>
            <td width="50px">
                <img id="buddyIcon" src="http://www.flickr.com/images/buddyicon.jpg" height="48" width="48">
            </td>
            <td id="signinText">
                <div id="buddyWelcome">Offline</div>
                <div id="signinAction">Talking to Flickr...</div>
            </td>
        </tr>
    </tbody></table>

<div class="bd">
<div id="controlArea">

    <div id="uploadrTopBar" class="clearfloat">
        <div id="uploadarea">
            <span id="uploadButton" class="yui-button yui-push-button yui-button-disabled">
                <span class="first-child"><button hidefocus="true">Upload Photos</button></span>
            </span>
        </div>

        <ul id="actionmenu">
            <li id="a_add"><a class="disabled" title="Browse files on your computer" href="#" onclick="return false;"></a></li>
            <li id="a_del"><a class="disabled" title="Remove selected images" href="#" onclick="return false;"></a></li>
            <li id="a_rol"><a class="disabled" title="Rotate selected images left" href="#" onclick="return false;"></a></li>
            <li id="a_ror"><a class="disabled" title="Rotate selected images right" href="#" onclick="return false;"></a></li>
            <li id="a_cro"><a class="disabled" title="Crop selected image" href="#" onclick="return false;"></a></li>
            <li id="a_clr"><a class="disabled" title="Undo all changes" href="#" onclick="return false;"></a></li>
        </ul>

        <div id="effectcontrolarea">
            <span id="effecttitle">Effects:</span>
            <input disabled id="effect_grayscale" name="effect_grayscale" value="0" type="checkbox" class="effectcontrol">
            <label for="effect_grayscale" class="effectlabel effectlabeldisabled">Grayscale</label>
            <input disabled id="effect_sepia" name="effect_sepia" value="0" type="checkbox" class="effectcontrol">
            <label for="effect_sepia" class="effectlabel effectlabeldisabled">Sepia</label>
            <input disabled id="effect_swirl" name="effect_swirl" value="0" type="checkbox" class="effectcontrol">
            <label for="effect_swirl" class="effectlabel effectlabeldisabled">Swirl</label>
            <input disabled id="effect_solarize" name="effect_solarize" value="0" type="checkbox" class="effectcontrol">
            <label for="effect_solarize" class="effectlabel effectlabeldisabled">Solarize</label>
            <input disabled id="effect_oil_paint" name="effect_oil_paint" value="0" type="checkbox" class="effectcontrol">
            <label for="effect_oil_paint" class="effectlabel effectlabeldisabled">Oil Paint</label>
        </div>
    </div> <!-- uploadrTopBar -->
                
    <div class="yui-ge">
        <div class="yui-u first">
            <div id="progress">
                <img id="progress_photo" src="img/ffffff.gif">
                <div class="contents">
                    <div class="pbar_header">Uploading Photos <img id="progress_spinner" src="img/spinner.gif" /></div>
                    <div class="pbar_label"><span class="title">Uploading</span> <span id="progress_file"></span></div>
                    <div class="pbar_border"><div id="photo_upload_pct" class="pbar_bar" style="width:0%"></div></div>
                    <div class="pbar_label"><span class="title">Total Progress</span> <span id="progress_total"></span></div>
                    <div class="pbar_border"><div id="total_upload_pct" class="pbar_bar" style="width:0%"></div>
                </div>
            </div>
        </div>
        
        <div id="finish">
            <h1>Upload Complete!</h1>
            <p><strong><span id="num_photos_uploaded"></span></strong></p>
            <div>
                <span id="finishViewButton" class="yui-button yui-link-button" >
                    <span class="first-child"><a target="_blank" href="http://www.yahoo.com">View Photos on Flickr</a></span>
                </span>
                <span id="finishOkButton" class="yui-button yui-push-button" >
                    <span class="first-child"><button hidefocus="true">OK</button></span>
                </span>
            </div>
        </div>

        <div id="require">
            <div class="pbar_header">Loading Services <img id="require_spinner" src="img/spinner.gif" /></div>
            <div class="pbar_label">
                <span class="title">Downloading</span> 
                <span id="require_file"></span>
            </div>
            <div class="pbar_border">
                <div id="require_local_pct" class="pbar_bar" style="width:0%"></div>
            </div>
            <div class="pbar_label">
                <span class="title">Total Progress</span> 
                <span id="require_total"></span>
            </div>
            <div class="pbar_border">
                <div id="require_total_pct" class="pbar_bar" style="width:0%"></div>
            </div>
        </div>

        <div id="droparea">
            <div id="draghelp"></div>
            <div id="drophelp"></div>
        </div>
    </div>

    <div class="yui-u">
        <div id="imagearea">
            <div id="ciinfo">
                <span id="ciwidth"></span>
                <span id="ciheight"></span>
                <span id="cisize"></span>
            </div>

            <div id="imagecontrolarea">
                <div class="labels">
                    <label for="title">Title:</label>
                </div>
            <div>

            <input disabled id="title" name="title" type="text" class="imagecontrol">
        </div>

        <div class="labels">
            <label for="description">Description:</label>
        </div>
        <div>
            <textarea disabled id="description" name="description" class="imagecontrol"></textarea>
        </div>
        <div class="labels">
            <label for="tags">Tags:</label>
        </div>
        <div>
           <input disabled id="tags" name="tags" type="text" class="imagecontrol">
        </div>
        <div class="labels">
            <label>Who can see it:</label>
        </div>
        <div id="perms">
            <div>
                <input disabled id="onlyyou" name="is_public" value="0" type="radio"  class="imagecontrol">
                <label for="onlyyou">Only You</label>
            </div>
            <div>
                <input disabled id="is_friend" name="is_friend" type="checkbox" value="1" class="imagecontrol">
                <label for="is_friend">Your Friends</label>
            </div>
            <div>
                <input disabled id="is_family" name="is_family" type="checkbox" value="1" class="imagecontrol">
                <label for="is_family">Your Family</label>
            </div>
            <div>
                <input disabled id="anyone" name="is_public" value="1" type="radio" checked class="imagecontrol">
                <label for="anyone">Anyone</label>
            </div>
        </div>
       <div class="labels">
           <label>Safety level:</label>
        </div>
        <div>
            <select disabled id="safety_level" name="safety_level" class="imagecontrol">
                <option value="1">
                    Safe
                </option>
                <option value="2">
                    Moderate
                </option>
                <option value="3">
                    Restricted
                </option>
            </select>
        </div>
        <div id="hidden">
            <input disabled id="hidden" type="checkbox" name="hidden" value="2" class="imagecontrol">
           <label for="hidden">Hide from public searches</label>
        </div>
    </div>
</div>
</div>
</div>
<div id="uploadrBottomBar" class="clearfloat">
   <div id="batchControlLabel">Batch Settings</div> 
        <div id="batchcontrolarea">
            <label for="batchtags">Tags:</label> 
            <input id="batchtags" name="batchtags" type="text" class="batchcontrol"> 
            <input name="doResizeCheck" id="doResizeCheck" type="checkbox" />
            <span id="resizeControls">
                <label for="doResizeCheck">Scale down to</label>
                <select id="resize" name="resize" class="batchcontrol" disabled="disabled">
                    <option value="2048">
                        2048 pixels
                    </option>
                    <option value="1600">
                        1600 pixels
                    </option>
                    <option selected="selected" value="1280">
                        1280 pixels
                    </option>
                    <option value="800">
                        800 pixels
                    </option>
                    <option value="640">
                        640 pixels
                    </option>
                    <option value="320">
                        320 pixels
                    </option>
                </select> 
                <span class="scaledownFootnote">(maximum)</span>
            </span>
        </div> 
    </div>
</div>
</div> <!-- bd -->

<div class="ft">
    <img src="../bp-logo-sm.png" style="margin:5px 0;float:right">
    <div id="statusBar"><span id="statusText"></span></div>
</div>
</form>

<div class="disclaimer">
    <b>Note:</b> This product uses Flickr web services but is neither endorsed nor sponsored by Flickr.
</div>

</div><!-- doc -->

<div id="completeAuthDialog" class="uploadrDialog" style="visibility:hidden;display:none">
    <div class="hd">Flickr Login</div>
    <div class="bd">
        <p>
            <strong>Return to this window after you have finished the authorization process on flickr.com.</strong>
        </p>
        <p>
            Once you are done, click the <em>Complete Authorization</em> button below and begin using 
            <em>Flickr Uploadr</em>.
        </p>
    </div>
</div>



<script src="http://yui.yahooapis.com/combo?2.7.0/build/yahoo-dom-event/yahoo-dom-event.js&2.7.0/build/element/element-min.js&2.7.0/build/button/button-min.js&2.7.0/build/dragdrop/dragdrop-min.js&2.7.0/build/container/container-min.js&2.7.0/build/resize/resize-min.js&2.7.0/build/imagecropper/imagecropper-min.js"></script> 
<script src="http://bp.yahooapis.com/2.4.21/browserplus-min.js"></script>
<script src="http://bp.yahooapis.com/<?php echo BROWSERPLUS_JS_VERSION; ?>/browserplus-min.js"></script>
<script src="http://bp.yahooapis.com/toolbox/installer/<?php echo INSTALL_WIDGET_VERSION; ?>/install-min.js"></script> 
<script src="photodrop.js"></script>
</body>
</html>