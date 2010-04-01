<?php 
require("../../../php/vars.php");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<title>Client Side Image Processing</title>

<link type="text/css" rel="stylesheet" href="http://yui.yahooapis.com/3.0.0/build/cssfonts/fonts-min.css" />
<link rel="stylesheet" type="text/css" href="imagealter.css">
<script src="http://yui.yahooapis.com/3.0.0/build/yui/yui-min.js"></script>
<script src="http://bp.yahooapis.com/<?php echo BROWSERPLUS_JS_VERSION; ?>/browserplus-min.js"></script>
<script src="http://bp.yahooapis.com/toolbox/installer/<?php echo INSTALL_WIDGET_VERSION; ?>/install-min.js"></script> 
</head>

<body class=" yui-skin-sam">
<div id="content">
<h1>Client Side Image Processing</h1>

<div class="intro">
	<p>
	    Your servers are busy enough already!  Use ImageAlter from BrowserPlus to perform
	    client-side image transformations.  To start, drag an image from the desktop and drop it on the
	    area below.  Once you do, a list of transformations will appear.  You can apply the transformations
	    more than once.
	</p>
</div>


<div id="play">

    <!-- Added table cause IE6 is putting imagewell on newline after image drop. Not required for modern browsers. -->
    <table><tr><td>

        <div id="lists">
            <ul id="choose"></ul>
            <ul id="apply"><div id="applytext"></div></ul>
        </div>

    </td><td>

        <div id="imagewell">
            <div id="imagetext"></div>
            <div id="image"></div>
        </div>

    </td></tr></table>
</div>

<script>
YUI({combine: true, timeout: 10000}).use('anim', function(Y) {

    var OrigFileHandle = null;
    var ThumbFileHandle = null;
    var ListsAreHidden = true;
    var ApplyText = "Click on a transformation to add it";
    var DropImageText = "Drop Image Now";
    var DragImageText = "Drag Image from Desktop"
    var BP = BrowserPlus;
    var Services = [
        {service: "DragAndDrop", version: "1"},
        {service: "ImageAlter",  version: "4", minversion: "4.0.4"},
        {service: "FileAccess",  version: "1", minversion: "1.0.9"}
    ];
    var Transformations = {
        'a_blur':        { 'label': 'Blur',          'action':'blur'},
        'a_trast_more':  { 'label': 'Contrast More', 'action':{'contrast': 1}},
        'a_trast_less':  { 'label': 'Contrast Less', 'action':{'contrast': -1}},
        'a_despeckle':   { 'label': 'Despeckle',     'action':'despeckle'},
        'a_dither':      { 'label': 'Dither',        'action':'dither'},
        'a_enhance':     { 'label': 'Enhance',       'action':'enhance'},
        'a_equalize':    { 'label': 'Equalize',      'action':'equalize'},
        'a_grayscale':   { 'label': 'Grayscale',     'action':'grayscale'},
        'a_negate':      { 'label': 'Negate',        'action':'negate'},
        'a_normalize':   { 'label': 'Normalize',     'action':'normalize'},
        'a_oilpaint':    { 'label': 'OilPaint',      'action':'oilpaint'},
        'a_psychedelic': { 'label': 'Psychedelic',   'action':'psychedelic'},
        'a_rot_left':    { 'label': 'Rotate Left',   'action':{'rotate': -90}},
        'a_rot_right':   { 'label': 'Rotate Right',  'action':{'rotate': 90}},
        'a_sepia':       { 'label': 'Sepia',         'action':'sepia'},
        'a_sharpen':     { 'label': 'Sharpen',       'action':'sharpen'},
        'a_solarize':    { 'label': 'Solarize',      'action':'solarize'},
        'a_swirl':       { 'label': 'Swirl',         'action':{'swirl': 90}},
        'a_unsharpen':   { 'label': 'Unsharpen',     'action':'unsharpen'}
    };

    function fireActionChange() {
        var actions = [];
        Y.Node.all('#apply li').each(function(v,k) {
            actions.push(v.getAttribute('action'));
        });

        Y.fire('actions:change', actions);
        
    }

    function renderActions(actions) {
        var arr = [];
        for(var k in actions) {
            if (actions.hasOwnProperty(k)) {
                arr.push(Transformations[actions[k]].action);
            }
        }

        processImage(ThumbFileHandle, arr);
    }
    
    // User click on item in apply list.  Remove it and fire action listener.
    function applyListClicked(e) {
        if (e.target.get('tagName') === "LI") {
            e.target.remove();
            fireActionChange();
            if (Y.Node.all('#apply li').size() === 0) {
                Y.one('#apply').append('<div id="applytext">' + ApplyText + '</div>');
            }
        }
    }

    function chooseListClicked(e) {
        if (e.target.get('tagName') === "LI") {
            var id = e.target.get('id');
            var d = Transformations[e.target.get('id')];

            var node = Y.Node.get("#applytext");
            if (node) {
                node.remove();
            }

            node = Y.Node.create('<li class="list2">' + Transformations[id].label + '</li>');
            node.setAttribute('action', id);
            Y.one('#apply').appendChild(node);
            fireActionChange();
        }
    }

    function error(str, res) {
        alert(str + ":" + res.error + " - " + res.verboseError);
    }

    function processImage(file, actions) {
        if (!file) {return;}

        BP.ImageAlter.transform({"file": file, "quality": 100, "actions": actions||[]}, function (transform) {
            if (transform.success) {
                BP.FileAccess.GetURL({ file: transform.value.file }, function (r) {
                    if (r.success) {
                        var w = transform.value.width, h = transform.value.height;
                        Y.one("#image").setContent('<img src="' + r.value + '" width="' + w + '" height="' + h + '">');
                    }
                });
            }
        });
    }

    function isImage(file) {
        var i, len = file.mimeType.length, mime;
        for (i = 0; i < len; i++) {
            mime = file.mimeType[i];
            if (mime === "image/jpeg" || mime === "image/pjpeg" || mime === "image/gif" || mime === "image/png") {
                return true;
            }
        }
        return false;
    }

    function dragDrop(res) {
        var files = res.actualSelection, i, len = files.length;
        for (i = 0; i < len; i++) {
            if (isImage(files[i])) {

                Y.one("#imagetext").setStyle("display", "none");

                OrigFileHandle = files[i];

                // scale image and use the result for all further processing
                var params = {
                    "file": files[i],
                    "quality": 100,
                    "actions": [{"scale": {"maxwidth": 400, "maxheight": 400}}]
                };

                BP.ImageAlter.transform(params, function (transform) {
                    if (transform.success) {
                        // ThumbFileHandle has module scope
                        ThumbFileHandle = transform.value.file;
                        processImage(ThumbFileHandle);
                        Y.Node.all('#apply li').each(function(v,k) {
                            v.remove();
                        });

                        if (ListsAreHidden) {
                            ListsAreHidden = !ListsAreHidden;
                            Y.one("#lists").setStyle("visibility", "visible");
                            var anim = new Y.Anim({node: '#lists', to: { opacity: 1 } });
                            anim.run();
                        }

                    }
                });

                break;
            }
        }
    }

    function dragHover(over) {
        Y.one("#imagetext").setContent(over ? DropImageText : DragImageText);
    }


    Y.on("click", chooseListClicked, "#choose");
    Y.on("click", applyListClicked, "#apply");
    Y.on("actions:change", renderActions);
    Y.one("#applytext").setContent(ApplyText);

    // Populate list of files from the local Transformation object
    Y.each(Transformations, function(v, k) {
        Y.one("#choose").appendChild(Y.Node.create('<li id="' + k + '" class="list1">' + Transformations[k].label + '</li>'));
    });


    // Initialize BrowserPlus
    BPTool.Installer.show({}, function(init) {
        if (init.success) {
            BP.require({services: Services}, function(require) {
                if (require.success) {
                    BP.DragAndDrop.AddDropTarget({id: "imagewell", includeGestureInfo:true}, function(adt) {
                        // set initial text in Image box
                        dragHover(false);
                        BP.DragAndDrop.AttachCallbacks({ id: "imagewell", hover: dragHover, drop: dragDrop}, function(){});
                    });
                } else {
                    error("Error Loading Services", require);
                }
            });
        } else {
            error("Failed to initialize BrowserPlus", init);
        }
    }); 
});
</script>
</div>
</body>
</html>