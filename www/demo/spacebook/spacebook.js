(function() {
    var YE = YAHOO.util.Event;
    var YD = YAHOO.util.Dom;
    var UploadURL = "upload.php";
    var ImagePanel = null;
    var ImageFile = null;
    var DoUploadFile = true;
    var ImageCrop = null;
    var BP = BrowserPlus;
    var ProfileParams = {maxwidth:150};
    var DialogParams = {maxwidth:500, maxheight:340};
    var RegisterDoubleClick = true;
    var DoubleClickHelp = "Double-click to edit your profile.";
    var DragHelp = "To change your picture, just drag a new one from your desktop and drop it on the current image above. ";
    
    var Filter = null;
    var Image = null;

    function err(s, r) {
        if (window.console) {
            console.log(s + ": " + r.error + "- " + r.verboseError);
        }
    }

    function displayImage(file, id, params, uploadFile) {
        if (file === null) {
            return;
        }
        params["file"] = file;
        params["quality"] = "high";
        if (Filter) {
            params["effects"] = [Filter];
        } else {
            params["effects"] = [];
        }
        
        if (Image) {
            params["crop"] = Image.crop;
        }

        BP.ImageAlter.Alter(params, function(res) {
            if (res.success) {
                var width = res.value.width;
                var height = res.value.height;

                if (uploadFile === DoUploadFile) {
                    YD.get("progress").style.visibility = "visible";
                    repaint();
                    BrowserPlus.FileTransfer.upload({
                        url: UploadURL,
                        files: {file: res.value.file}
                    }, function(upload) {
                        if (!upload.success) {
                            err("UPLOAD", upload);
                        }
                        YD.get("progress").style.visibility = "hidden";
                        repaint();
                    });

                }

                BP.FileAccess.GetURL({file: res.value.file}, function(r) {
                    var img;
                    if (r.success) {  
                        img = YD.get(id);
                        img.src = r.value;
                    } else {
                        err("ACCESS ERROR", r);
                    }
                });

            } else {
                err("ALTER ERROR", res);
            }
        });
    }

    function hovering(entered) {
        YD.get("profile").className = (entered ? "hoverYes" : "hoverNo");
        repaint();
    }
    
    function dropped(args) {
        var file, i, j, mlen, flen = args.length, mimeTypes, mimeType;

        // if user drops more than 1, process the first image
        for (i = 0; i < flen; i++) {

            file = args[i];
            mimeTypes = file.mimeType;
            mlen = mimeTypes.length;
            for (j = 0; j < mlen; j++) {
                mimeType = mimeTypes[j];

                if (mimeType === "image/jpeg" || mimeType === "image/png" || mimeType === "image/gif") {
                    ImageFile = file;
                    Crop = null;
                    Filter = null;
                    displayImage(file, "profileImage", ProfileParams);
                    displayImage(file, "dialogImage", DialogParams, DoUploadFile);
                    Image = {
                        crop:   {x1:0, y1:0, x2:1, y2:1}, // crop percentages
                        wf:     1, // width factor for cropping
                        hf:     1 // height factor for cropping
                    };

                    if (RegisterDoubleClick) {
                        // once the user actually dropped an image we can edit, register
                        // double click handler
                        RegisterDoubleClick = false;
                        YE.addListener('profile', 'dblclick', showProfilePanel, this, true);
                        YD.get("dblclickhelp").innerHTML = DoubleClickHelp;
                        setTimeout(function() {
                            YD.get("dblclickhelp").className = "";
                        }, 3500);

                    }

                    return;
                }
            }
        }
    }

    function showDropTarget() {
        YD.setStyle("profile", "border", "2px solid red");

        setTimeout(function() {
            YD.setStyle("profile", "border", "2px solid white");

            YD.get("profile").className = "hoverNo";
        }, 2500);

        return false;
    }

    function repaint()
    {
        var el = YD.get("ft");
        var x = document.createTextNode(".");
        el.appendChild(x);
        el.removeChild(x);
    }
    

    function showProfilePanel() {

        displayImage(ImageFile, "dialogImage", DialogParams);

        if (ImagePanel === null) {
            ImagePanel = new YAHOO.widget.Panel(
                'dialog',
                { 
                    width: "600px", 
                    fixedcenter: true,
                    close: true, 
                    draggable: false,
                    zindex: 4,
                    modal: true,
                    visible: true,
                    constraintoviewport:true
                }
            );

            ImagePanel.hideEvent.subscribe(function() {
                if (ImageCrop) {
                    ImageCrop.destroy();
                    ImageCrop = null;
                }

            });
            YD.get("dialog").style.display="block";
            ImagePanel.render(document.body);

        } else {
            ImagePanel.show();
        }
    }
    
    function closeProfilePanel() {
        ImagePanel.hide();
    }

    function acceptProfilePanel() {
        var r, img, x1, x2, y1, y2;
        if (ImageFile) {
            if (ImageCrop) {
                r = ImageCrop.getCropCoords();
                img =  YD.get("dialogImage");

                // compute crop box in percentage
                x1 = Image.crop.x1 + ((r.left / img.width) * Image.wf);
                x2 = Image.crop.x2 - ((1 - ((r.left + r.width) / img.width)) * Image.wf);
                y1 = Image.crop.y1 + ((r.top / img.height) * Image.hf);
                y2 = Image.crop.y2 - ((1 - ((r.top + r.height) / img.height)) * Image.hf);

                Image.wf = Image.wf * Math.round((x2-x1)*100)/100;
                Image.hf = Image.hf * Math.round((y2-y1)*100)/100;
                Image.crop =  {'x1': x1, 'x2': x2, 'y1': y1, 'y2': y2};
            }


            displayImage(ImageFile, "profileImage", ProfileParams, DoUploadFile);
        }

        ImagePanel.hide();
    }

    function alterImage(e, obj) {
        var action, target = YE.getTarget(e).id;

        if (target.indexOf("action_") === 0) {
            action = target.substring(7);
            var destroyCrop = true;
            if (action === "crop") {
                if (ImageCrop === null) {
                    destroyCrop = false;
                    ImageCrop = new YAHOO.widget.ImageCropper('dialogImage', {keyTick: 5, ratio: false, shiftKeyTick: 50}); 
                } 
            } else if (action === "nada") {
                Filter = null;
            } else {
                Filter = action;
            }
            
            if (ImageCrop && destroyCrop) {
                ImageCrop.destroy();
                ImageCrop = null;
            }

            displayImage(ImageFile, "dialogImage", DialogParams);
        }
    }

    function initUI() {
        YE.addListener('cancel', 'click', closeProfilePanel, this, true);
        YE.addListener('ok', 'click', acceptProfilePanel, this, true);
        YE.addListener('actions', 'click', alterImage, this, true);
        YE.addListener('showDropTarget', 'click', showDropTarget, this, true);
        YD.get("status").innerHTML = DragHelp;
        
        BP.DragAndDrop.AddDropTarget({id: "profile"},
            function(res) {
                BP.DragAndDrop.AttachCallbacks({id: "profile", hover: hovering, drop: dropped}, function(){});
        });
    }

    function updateProgress(progress) {
        // progress = {name:"ServiceName", localPercentage: 50, totalPercentage: 25};
        YD.get("status").innerHTML = "Loading " + progress.name + " (" + progress.totalPercentage + "%)";
    }

    function initBrowserPlus(){
        var services = [
            {service: 'DragAndDrop'},
            {service: "FileAccess", version: "1"},
            {service: "ImageAlter", version: "2"},
            {service: 'FileTransfer', version: "1"}
        ];

        // require BrowserPlus services
        BP.require({services: services, progressCallback: updateProgress}, function(res) {
            if (res.success) {
                initUI();
            } else {
                alert("Require failed: " + res.error + " - " + res.verboseError);
            }
        });

    }

    BPTool.Installer.show({}, function(res) {
        if (res.success) {
            initBrowserPlus();
        } else {
            alert("Problem encountered! " + res.error + " - " + res.verboseError);
        }
    });
    
})();