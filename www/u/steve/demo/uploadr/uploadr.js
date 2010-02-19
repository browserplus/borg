YUI().use('node', function(Y) {
	// a "global" containing files for uploading
	var filesToUpload = [];

	// for log(LEVEL, str)
	var START  = "start";
	var END	   = "end";
	var RESULT = "result";
	var DBG	   = "dbg";
	var METHOD = "method";
	var ERROR  = "error";
	var TIME   = "time";

	var RESIZE_DIMENSION = 800;
	var RESIZE_QUALITY = 80;

	var RESIZE_NO = 0;
	var RESIZE_YES = 1;

	// INDEX TO TABLE COLUMNS
	var FILE_NODE = 0;
	var SIZE_NODE = 1;
	var TIME_NODE = 2;

	// smaller than 2MB default for demoing
	var CHUNK_SIZE = 512*1024;
	
	var UPLOAD_URL = "http://browserpl.us/misc/upload.php";
	
	// The current table row
	var CURRENT_ROW = 1;


	function getTimeStamp() {
		var d = new Date(),
			h = d.getHours(),
			m = d.getMinutes(),
			s = d.getSeconds();
		h = (h < 10 ? "0" + h : h);
		m = (m < 10 ? "0" + m : m);
		s = (s < 10 ? "0" + s : s);
		return ("[" + h + ":" + m + ":" + s + "] ");
	}

	function log(level, str) {
		var r = Y.one("#result");
		if (!str) {
			str = level;
			level = DBG;
		}
		str = str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
		r.append('<div><span class="log_' + TIME + '">' + getTimeStamp() + '</span>' + 
			'<span class="log_' + level + '">' + str + "</span></div>");
		r.set("scrollTop", r.get("scrollHeight"));
	}

	// Tests which files to compress with LZMA.	 
	// Skips all images.
	function isCompressable(file) {
		var i, len, mime, mimeTypes = file.mimeType;
		for (i = 0, len = mimeTypes.length; i < len; i++) {
			mime = mimeTypes[i];
			if (/^image\//.test(mime)) {
				return false;
			}
		}
		return true;
	}


	// Return a human presentable size string like "1.2GB"
	function prettySize(size) {
		var i, units = ['B','KB','MB','GB','TB'];
		for (i = 0; size > 1024; i++) {
			size /= 1024;
		}
		return Math.round(size*10)/10 + units[i];
	}
	
	function prettyTime(time) {
		return ""+(Math.round(time/10)/100) + "s";
	}

	function uploadStart(title) {
		log(METHOD, "==== " + title + " ==== ");
		Y.all("#r"+CURRENT_ROW+" td").toggleClass("active");

		return {
			title: title,
			startTime: new Date(),
			curFile: 0,
			totalFiles: 0,
			totalBytes: 0,
			bytesUploaded: null
		};
	}

	function uploadSetNumFiles(uploadObj, numFiles) {
		var i, arr = new Array(numFiles);
		// array stores current number of bytes uploaded, indexed per file (fileObj.index)
		for (i = 0; i < numFiles; i++) { arr[i] = 0; }
		uploadObj.totalFiles = numFiles;
		uploadObj.bytesUploaded = arr;
	}
	
	function uploadEnd(uploadObj) {
		var endTime = new Date() - uploadObj.startTime;
		var nodes = Y.all("#r"+CURRENT_ROW+" td");

		// set final number of bytes uploaded using totalBytes instead of "current" array
		nodes.item(SIZE_NODE).setContent(prettySize(uploadObj.totalBytes));

		Y.all("#r"+CURRENT_ROW+" td").toggleClass("active");
		log(RESULT, uploadObj.title + ": " + prettySize(uploadObj.totalBytes) + 
		    " uploaded in " + prettyTime(endTime));
		CURRENT_ROW++;		  
	}
	
	function fileStart(uploadObj, file, index) {
		return {
			file: file,
			index: index,
			origSize: file.size,
			compressTime: 0,
			uploadTime: 0
		};
	}

	function fileCompressStart(uploadObj, fileObj) {
		fileObj.compressTime = new Date();
		log(START, "compressing " + fileObj.file.name);
	}

	function fileCompressEnd(uploadObj, fileObj) {
		var endTime = new Date() - fileObj.compressTime;
		displayStats(uploadObj);
		log(END, "COMPRESSED " + fileObj.file.name + " from " + prettySize(fileObj.origSize) + 
			" to " + prettySize(fileObj.file.size) + " in " + prettyTime(endTime));
	}

	function fileUploadStart(uploadObj, fileObj) {
		fileObj.uploadTime = new Date();
		log(START, "uploading " + fileObj.file.name + "(" + prettySize(fileObj.file.size) + ")");
	}

	function fileUploadProgress(uploadObj, fileObj, progress) {
		uploadObj.bytesUploaded[fileObj.index] = (progress.filePercent/100 * fileObj.file.size);
		displayStats(uploadObj);
	}

	function fileUploadEnd(uploadObj, fileObj) {
		var endTime = new Date() - fileObj.uploadTime;
		uploadObj.curFile++;
		uploadObj.totalBytes += fileObj.file.size;
		displayStats(uploadObj);
		log(END, "UPLOADED " + fileObj.file.name + " in " + prettyTime(endTime));
	}
	
	function displayStats(uploadObj) {
		var i, bytes = 0, nodes = Y.all("#r"+CURRENT_ROW+" td");
		for (i = 0; i < uploadObj.totalFiles; i++) {
			bytes += uploadObj.bytesUploaded[i];
		}
		nodes.item(FILE_NODE).setContent(uploadObj.curFile + "/" + uploadObj.totalFiles);
		nodes.item(SIZE_NODE).setContent(prettySize(bytes));
		nodes.item(TIME_NODE).setContent(prettyTime(new Date() - uploadObj.startTime));
	}

	// resize single file if it is an image
	function resizeIfImage(file, cb) {
		var startTime = new Date();
		if (/\.(png|jpg|jpeg)$/i.test(file.name)) {
			BrowserPlus.ImageAlter.transform(
				{
					file : file,
					quality : RESIZE_QUALITY,
					actions : [{ scale : { maxwidth : RESIZE_DIMENSION, maxheight : RESIZE_DIMENSION } }]
				}, function(res) {
					var endTime = new Date() - startTime;
					log(END, "RESIZED " + file.name + " from " + prettySize(file.size) +
						" to " + prettySize(res.value.file.size) + " in " + prettyTime(endTime));
					cb(res.value.file);
				});
		} else {
			cb(file);
		}
	}

    // resize all images
	function resizeImages(resizedCB) {
		var i, files = [];

		var resizeImageCB = function(f) {
			files.push(f);
			if (files.length === filesToUpload.length) {
				resizedCB(files);
			}
		};

		for (i = 0; i < filesToUpload.length; i++) {
	        resizeIfImage(filesToUpload[i], resizeImageCB);
		}
	}

	function serialUploadTest() {
		var i, fileStack = [], fileIndex = 0, uploadObj = uploadStart("serialUploadTest");

		uploadSetNumFiles(uploadObj, filesToUpload.length);

		for (i = 0; i < filesToUpload.length; i++) {
		    fileStack.push(filesToUpload[i]);
	    }


		var doNextUpload = function() {
			if (fileStack.length > 0) {
				var currentFile = fileStack.shift();
				var fileObj = fileStart(uploadObj, currentFile, fileIndex++);
				fileUploadStart(uploadObj, fileObj);

				BrowserPlus.Uploader.upload({
					files: { "file": currentFile },
					url:   UPLOAD_URL,
					progressCallback: function(p) {
						fileUploadProgress(uploadObj, fileObj, p);
					}
				}, function(r) {
					fileUploadEnd(uploadObj, fileObj);
					doNextUpload();
				});
			} else {
				uploadEnd(uploadObj);
				// we're done, call the next one
				parallelOrigUploadTest();
			}
		};

		doNextUpload();
	}

    function parallelOrigUploadTest() {
        _parallelUploadTest(RESIZE_NO, serialChunkUploadTest);
    }
    
    function parallelResizeUploadTest() {
        _parallelUploadTest(RESIZE_YES, serialLZMAUploadTest);
    }
    
	function _parallelUploadTest(resize, nextTest) {
		var fileIndex = 0, numComplete = 0, uploadObj = uploadStart("parallelUploadTest");
		uploadSetNumFiles(uploadObj, filesToUpload.length);
		
		var runUpload = function(currentFile) {
			var fileObj = fileStart(uploadObj, currentFile, fileIndex++);
			fileUploadStart(uploadObj, fileObj);

			BrowserPlus.Uploader.upload({
				files: { "file": currentFile },
				url:   UPLOAD_URL,
				progressCallback: function(p) {
					fileUploadProgress(uploadObj, fileObj, p);
				}
			}, function(r) {
				fileUploadEnd(uploadObj, fileObj);
				// are we all done!?
				numComplete += 1;
				if (numComplete == filesToUpload.length) {
					uploadEnd(uploadObj);
					nextTest();
				}
			});
		};

        if (resize === RESIZE_YES) {
		    resizeImages(function(files) {
                for (var i = 0; i < files.length; i++) {
			        runUpload(files[i]);
		        }
	        });
        } else {
            for (var i = 0; i < filesToUpload.length; i++) {
                runUpload(filesToUpload[i]);
            }
        }
	}
	
	function serialChunkUploadTest() {
		var fileIndex = 0, chunksComplete = 0, chunkFilesToUpload = [];
		    uploadObj = uploadStart("serialChunkUploadTest");
			
        var doNextUpload = function() {
            if (chunkFilesToUpload.length > 0) {
                var currentFile = chunkFilesToUpload.shift();
			    var fileObj = fileStart(uploadObj, currentFile, fileIndex++);
			    fileUploadStart(uploadObj, fileObj);

			    BrowserPlus.Uploader.upload({
				    files: { "file": currentFile },
				    url:   UPLOAD_URL,
				    progressCallback: function(p) {
					    fileUploadProgress(uploadObj, fileObj, p);
				    }
			    }, function(r) {
				    fileUploadEnd(uploadObj, fileObj);
				    doNextUpload();
			    });
		    } else {
				uploadEnd(uploadObj);
				parallelResizeUploadTest();												 
			}
		};

		resizeImages(function(files) {
			var k, filesComplete = 0;

            var chunkCB = function(r) {
			    var chunks = r.value;

			    for (var i = 0; i < chunks.length; i++) {
				    chunkFilesToUpload.push(chunks[i]);
			    }

			    if (++filesComplete == files.length) {
			        uploadSetNumFiles(uploadObj, chunkFilesToUpload.length);
                    doNextUpload();
			    }
            };

			for (k = 0; k < files.length; k++) {
			    BrowserPlus.FileAccess.chunk({file:files[k], chunkSize: CHUNK_SIZE}, chunkCB);
		    }
		});
	}

	function serialLZMAUploadTest () {
		var k, fileStack = [], fileIndex = 0, uploadObj = uploadStart("serialLZMAUploadTest");
		uploadSetNumFiles(uploadObj, filesToUpload.length);

        for (k = 0; k < filesToUpload.length; k++) {
		    fileStack.push(filesToUpload[k]);                
        }
        
		var uploadIt = function(fileObj) {
			fileUploadStart(uploadObj, fileObj);

			BrowserPlus.Uploader.upload({
				files: { "file": fileObj.file },
				url:   UPLOAD_URL,
				progressCallback: function(p) {
					fileUploadProgress(uploadObj, fileObj, p);
				}
			}, function(r) {
				fileUploadEnd(uploadObj, fileObj);
				doNextUpload();
			});
		};

		var doNextUpload = function() {
			if (fileStack.length > 0) {
				var currentFile = fileStack.shift();
				var fileObj = fileStart(uploadObj, currentFile, fileIndex++);

				if (isCompressable(currentFile)) {
					fileCompressStart(uploadObj, fileObj);

					BrowserPlus.LZMA.compress({
						file: currentFile
					}, function(r) {
						fileObj.file = r.value;
						fileCompressEnd(uploadObj, fileObj);
						uploadIt(fileObj);
					});
				} else {
					resizeIfImage(currentFile, function(f){
						fileObj.file = f;
						uploadIt(fileObj);
					});
				}
			} else {
				uploadEnd(uploadObj);
				parallelLZMAUploadTest();
			}
		};
		
		doNextUpload();
	}

	function parallelLZMAUploadTest() {
		var fileIndex = 0, numComplete = 0, uploadObj = uploadStart("parallelLZMAUploadTest");

		uploadSetNumFiles(uploadObj, filesToUpload.length);

		var uploadIt = function(fileObj) {
			fileUploadStart(uploadObj, fileObj);
					
			BrowserPlus.Uploader.upload({
				files: { "file": fileObj.file },
				url:   UPLOAD_URL,
				progressCallback: function(p) {
					fileUploadProgress(uploadObj, fileObj, p);
				}
			}, function(r) {
				fileUploadEnd(uploadObj, fileObj);

				numComplete += 1;
				if (numComplete == filesToUpload.length) {
					uploadEnd(uploadObj);
					serialArchiveZipTest();
				}
			}); 
		};
		
		var compressIt = function(currentFile) {
			var fileObj = fileStart(uploadObj, currentFile, fileIndex++);

			if (isCompressable(currentFile)) {
				fileCompressStart(uploadObj, fileObj);

				BrowserPlus.LZMA.compress({
					file: currentFile
				}, function(r) {
					fileObj.file = r.value;
					fileCompressEnd(uploadObj, fileObj);
					uploadIt(fileObj);
				});
			} else {
				uploadIt(fileObj);
			}
		};

		resizeImages(function(files){
			for (var i = 0; i < files.length; i++) {
			    compressIt(files[i]);
			}
		});
	}

	function serialArchiveZipTest() {
		_serialArchiveTest("zip", serialArchiveGZipTest);
	}
	
	function serialArchiveGZipTest() {
		_serialArchiveTest("tar-gzip", serialArchiveBZip2Test);
	}

	function serialArchiveBZip2Test() {
		_serialArchiveTest("tar-bzip2", uploadTestComplete);
	}
	
	function uploadTestComplete() {
		log(METHOD, "Test Complete.	 Reload the page to try again.");
	}

	function _serialArchiveTest(fmt, nextTestFunc) {
		var uploadObj = uploadStart("SerialArchiveUploadTest(" + fmt + ")");

		uploadSetNumFiles(uploadObj, 1);

		var archiveIt = function(files) {
			var k, origSize = 0, compressTime;

			for (k in files) {
                if (files.hasOwnProperty(k)) {
				    origSize += files[k].size;
			    }
			}

			log(START, "archiving " + files.length + " files into 1 " + fmt);
			compressTime = new Date();

			BrowserPlus.Archiver.archive({
				files:	files,
				format: fmt
			}, function(r){
				var currentFile = r.value.archiveFile, fileObj = fileStart(uploadObj, currentFile, 0),
					endTime = new Date() - compressTime;

				displayStats(uploadObj);
				log(END, "ARCHIVED " + fileObj.file.name + " from " + prettySize(origSize) + " to " + 
				    prettySize(fileObj.file.size) +  " in " + prettyTime(endTime));

				fileUploadStart(uploadObj, fileObj);			

				BrowserPlus.Uploader.upload({
					files: { "file": fileObj.file },
					url:   UPLOAD_URL,
					progressCallback: function(p) {
						fileUploadProgress(uploadObj, fileObj, p);
					}
				}, function(res) {
					fileUploadEnd(uploadObj, fileObj);
					uploadEnd(uploadObj);
					nextTestFunc();
				});
			});
		};
		
		resizeImages(function(files){
			archiveIt(files);
		});
	}

	// a function that will be invoked when the user is hovering over the drop target.
	function dragHover(hoverOn) {
		if (hoverOn) {
			Y.one("#dropArea").set("className", "hoverOn");
		} else {
			Y.one("#dropArea").set("className", "hoverOff");
		}
	} 

	// a function invoked when the user drops content on the target
	function dragDropped(arg) {
		for (var i = 0; i < arg.length; i++) {
			if (arg[i].mimeType[0] == "application/x-folder") { continue; }// skip folders
			filesToUpload.push(arg[i]);
		} 

		// now let's disable that pesky drop target.
		log(START, "Now processing " + filesToUpload.length + " files...");
		enableDropTarget(false);
		serialUploadTest();
	}  

	function enableDropTarget(enable) {
		var $DND = BrowserPlus.DragAndDrop;

		if (enable) {
			$DND.AddDropTarget(
				{ id: "dropArea" },
				function(res) {
					$DND.AttachCallbacks({id: "dropArea", hover: dragHover, drop: dragDropped },
					function(){});	
				}
			);
		} else {
			$DND.RemoveDropTarget({ id: "dropArea" }, function(res) { });
			Y.one("#dropArea").setStyle("visibility", "hidden");
		}
	}

	
	BrowserPlus.init(function(res) {
		if (res.success) {
			log(END, "BrowserPlus initialized, loading services...");
			// lots of different services for all the various upload options
			BrowserPlus.require({
				services: [
				{service: 'DragAndDrop', version: "1"},
				{service: 'Uploader', version: "3", minversion: "3.1.5" },
				{service: 'LZMA', version: "1", minversion: "1.0.1" },
				{service: 'FileAccess', version: "2", minversion: "2.0.1" },
				{service: 'Archiver', version: "1"},
				{service: 'ImageAlter', version: "4"}
			]},
			function(res) {
				if (res.success) {
					log(RESULT, "Services Loaded");
					enableDropTarget(true);
				} else {
					log(ERROR, "Loading Services: " + res.error + 
						(res.verboseError ? (" (" + res.verboseError + ")") : ""));
				}
			});
		} else {
			log(ERROR, "Failed to initialize BrowserPlus: " + res.error + 
				(res.verboseError ? (" (" + res.verboseError + ")") : ""));
		}
	}); 
});