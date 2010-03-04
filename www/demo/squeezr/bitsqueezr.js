(function(){
    var BP = BrowserPlus;
    var Busy = false;
    var Services = [
        {service: 'LZMA', version: '1'},
        {service: 'DragAndDrop', version: '1'},
        {service: 'Tar', version: '1'},
        {service: 'FileAccess', version: '1', minversion: '1.0.9'}
    ];


    var DragString       = "Drag Files from Desktop";
    var DropString       = "Drop Files Now";
    var CombineString    = "Combine Files";
    var CompressString   = "Compress File";
    var DownloadString   = "Download File";
    var ResetString      = "Reset";
    var DefaultFilename  = "download.tar.lz";
    var Filename         = null;

    var DropItem     = document.getElementById("dropitem");
    var DropText     = document.getElementById("droptext");
    var CombineItem  = document.getElementById("combineitem");
    var CombineText  = document.getElementById("combinetext");
    var CompressItem = document.getElementById("compressitem");
    var CompressText = document.getElementById("compresstext");
    var DownloadItem = document.getElementById("downloaditem");
    var DownloadText = document.getElementById("downloadtext");
    var ResetText    = document.getElementById("resettext");
    var ResetLink    = document.getElementById("reset");

    
    // Add listener to element.
    function addListener(element, type, action)
    {
        var bubbling = false;
 
        if(window.addEventListener) { // everything but
            element.addEventListener(type, action, bubbling);
        } else if(window.attachEvent) { // IE
            element.attachEvent('on' + type, action);
        }
    }

    // Return a human presentable size string like "1.2GB"
    function humanSize(size) {
        var i, units = ['B','KB','MB','GB','TB'];
        for (i = 0; size > 1024; i++) {
            size /= 1024;
        }
        return Math.round(size*10)/10 + units[i];
    }

    // Reset interface into initiali state.
    function reset() {
        DropItem.className = "active";
        DropText.innerHTML = DragString;

        CombineItem.className = "pending";
        CombineText.innerHTML = CombineString;

        CompressText.innerHTML = CompressString;
        CompressItem.className = "pending";

        DownloadText.innerHTML = DownloadString;
        DownloadItem.className = "pending";
        
        ResetLink.innerHTML = ResetString;
        ResetText.style.display = "none";
        Busy = false;
        
        Filename = DefaultFilename;
    }

    // Reset link clicked.
    function resetClicked(ev) {
        if (ev.stopPropagation) { ev.stopPropagation(); }
        if (ev.preventDefault)  { ev.preventDefault(); }
        reset();
    }

    // display error and reset interface.
    function error(str, res) {
        alert(str + ":" + res.error + " - " + res.verboseError);
        reset();
    }


    // Show "Drag Files" or "Drop Files" message based on hover state of current drag
    function dragHover(hovering) {
        if (Busy) { return; }
        DropText.innerHTML = hovering ? DropString : DragString;
    }

    // User drop files
    function dragDrop(res) {
        var files = res.actualSelection;
        if (files.length > 0) {
            Busy = true;
            combineFiles(files);
        }
    }

    // Tar files if folder or more than 1 file dropped.  Otherwise, go straight to compress stage.
    function combineFiles(files) {
        var thang;
        if (files.length === 1) {
            if (files[0].mimeType == "application/x-folder") {
                thang = "Folder";
                Filename = files[0].name + ".tar.lz";
            } else {
                thang = "File";
                Filename = files[0].name + ".lz";
            }
        } else {
            thang = "Files";
            Filename = DefaultFilename; // download.tar.lz
        }

        DropText.innerHTML = "" + files.length + " " + thang + " dropped";
        DropItem.className = "done";

        CombineItem.className = "active";
        CombineText.innerHTML = "Combining Files";

        // TAR if one folder or more than one file is selected
        if (files.length > 1 || (files.length === 1 && files[0].mimeType == "application/x-folder")) {
            BP.Tar.tar(
                {
                    files: files, 
                    progressCallback: function(x) { CombineText.innerHTML = "Combining Files (" + x.percent.toFixed() + "%)"; }
                }, 
                function(tar) {
                    if (tar.success) {
                        compressFile(tar.value.tarFile, "Files");
                    } else {
                        error("Error creating tar file", tar);
                    }
                });
        } else { // otherwise, just need to compress file
            compressFile(files[0], "File");
        }
    }

    // Compress file with LZMA
    function compressFile(file, thang) {
        CombineItem.className = "done";
        CombineText.innerHTML = thang + " combined (" + humanSize(file.size) + ")";

        CompressItem.className = "active";
        CompressText.innerHTML = "Compressing File";

        BP.LZMA.compress(
            {
                file: file, 
                progressCB: function(x) { CompressText.innerHTML = "Compressing File (" + x.toFixed() + "%)"; }
            }, 
            function(lzma){
                if (lzma.success) {
                    downloadFile(lzma.value);
                }
            });
    }
    
    // Compression complete - create link to compress file.
    function downloadFile(file) {
        CompressItem.className = "done";
        CompressText.innerHTML = "File Compressed (" + humanSize(file.size) + ")";
        
        DownloadItem.className = "active";
        BP.FileAccess.GetURL({file: file }, function (geturl) {
            if (geturl.success) {
                ResetText.style.display = "inline";
                DownloadText.innerHTML = '<a href="' + geturl.value + '/' + Filename + '">' + DownloadString + '</a>';
            } else {
                error("File Access Error", geturl);
            }
        });
    }

    // Put UI into initialize state.
    reset();

    // Initialize BrowserPlus
    BPTool.Installer.show({}, function(init) {
        dragHover(false);
        addListener(ResetLink, "click", resetClicked);
        
        if (init.success) {
            BP.require({services: Services}, function(require) {
                if (require.success) {
                    BP.DragAndDrop.AddDropTarget({id: "dropitem", includeGestureInfo:true}, function(adt) {
                        BP.DragAndDrop.AttachCallbacks({ id: "dropitem", hover: dragHover, drop: dragDrop}, function(){});
                    });
                } else {
                    error("Error Loading Services", require);
                }
            });
        } else {
            error("Failed to initialize BrowserPlus", init);
        }
    }); 

    
}());    