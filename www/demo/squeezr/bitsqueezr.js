YUI().use('node', function(Y) {
	var BP = BrowserPlus;
	var Busy = false;
	var Services = [
		{service: 'LZMA', version: '1', minversion: '1.0.2'},
		{service: 'FileBrowse', version: '2'},
		{service: 'DragAndDrop', version: '2'},
		{service: 'Archiver', version: '1', minversion: '1.1.0'},
		{service: 'FileAccess', version: '2', minversion: '2.0.1'}
	];

	var DragString		 = "Drag (or Select) Files from Desktop";
	var DropString		 = "Drop Files Now";
	var CombineString	 = "Combine Files";
	var CompressString	 = "Compress File";
	var DownloadString	 = "Download File";
	var ResetString		 = "Reset";
	var DefaultFilename	 = "download.tar.lz";
	var Filename		 = null;

	var DropItem	 = Y.one("#dropitem");
	var DropText	 = Y.one("#droptext");
	var CombineItem	 = Y.one("#combineitem");
	var CombineText	 = Y.one("#combinetext");
	var CompressItem = Y.one("#compressitem");
	var CompressText = Y.one("#compresstext");
	var DownloadItem = Y.one("#downloaditem");
	var DownloadText = Y.one("#downloadtext");
	var ResetText	 = Y.one("#resettext");
	var ResetLink	 = Y.one("#reset");
	var SelectButton = Y.one("#selectbutton")
	
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
		DropItem.set("className", "active");
		DropText.setContent(DragString);

		CombineItem.set("className",  "pending");
		CombineText.setContent(CombineString);

		CompressItem.set("className", "pending");
		CompressText.setContent(CompressString);

		DownloadItem.set("className", "pending");
		DownloadText.setContent(DownloadString);
		
		ResetLink.setContent(ResetString);
		ResetText.setStyle("display", "none");

		Busy = false;
		Filename = DefaultFilename;
	}

	// Reset link clicked.
	function resetClicked(ev) {
		ev.preventDefault();
		reset();
		return false;
	}

	// display error and reset interface.
	function error(str, res) {
		alert(str + ":" + res.error + " - " + res.verboseError);
		reset();
	}


	// Show "Drag Files" or "Drop Files" message based on hover state of current drag
	function dragHover(hovering) {
		if (Busy) { return; }
		DropText.setContent(hovering ? DropString : DragString);
	}

	// User drop files
	function dragDrop(files) {
		if (files.length > 0) {
			Busy = true;
			combineFiles(files);
		}
	}

	function fileBrowse() {
		YAHOO.bp.FileBrowse.OpenBrowseDialog({}, function(res) {
			if (res.success) {
				combineFiles(res.value.files);
			}
		});
	}

	// Tar files if folder or more than 1 file dropped.	 Otherwise, go straight to compress stage.
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

		DropText.setContent("" + files.length + " " + thang + " chosen");
		DropItem.set("className", "done");

		CombineItem.set("className", "active");
		CombineText.setContent("Combining Files");

		// TAR if one folder or more than one file is selected
		if (files.length > 1 || (files.length === 1 && files[0].mimeType == "application/x-folder")) {
			BP.Archiver.archive(
				{
					files: files, 
					format: "tar",
					progressCallback: function(x) { CombineText.setContent("Combining Files (" + x.percent.toFixed() + "%)"); }
				}, 
				function(tar) {
					if (tar.success) {
						compressFile(tar.value.archiveFile, "Files");
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
		CombineItem.set("className", "done");
		CombineText.setContent(thang + " combined (" + humanSize(file.size) + ")");

		CompressItem.set("className", "active");
		CompressText.setContent("Compressing File");

		BP.LZMA.compress(
			{
				file: file, 
				progressCB: function(x) { CompressText.setContent("Compressing File (" + x.toFixed() + "%)"); }
			}, 
			function(lzma){
				if (lzma.success) {
					downloadFile(lzma.value);
				}
			});
	}
	
	// Compression complete - create link to compress file.
	function downloadFile(file) {
		CompressItem.set("className", "done");
		CompressText.setContent("File Compressed (" + humanSize(file.size) + ")");
		
		DownloadItem.set("className", "active");
		BP.FileAccess.getURL({file: file }, function (geturl) {
			if (geturl.success) {
				ResetText.setStyle("display", "inline");
				DownloadText.setContent('<a href="' + geturl.value + '/' + Filename + '">' + DownloadString + '</a>');
			} else {
				error("File Access Error", geturl);
			}
		});
	}

	
	// Put UI into initialize state.
	reset();

	// Initialize BrowserPlus
	BPInstallerUI.start({pathToJar: "/installer"}, function(init) {
		dragHover(false);
		Y.on("click", resetClicked, ResetLink);
		Y.on("click", fileBrowse, SelectButton);
		
		if (init.success) {
			BP.require({services: Services}, function(require) {
				if (require.success) {
					BP.DragAndDrop.AddDropTarget({id: "dropitem"}, function(adt) {
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

	
});	   