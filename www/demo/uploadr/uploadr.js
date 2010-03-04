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
    var TIME_OFFSET = 0;
    var TIME_KEEPER= {};

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

	// Tests which files to compress with LZMA - skips all images	 
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

    // ----------- Timing + Status functions ----------
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
		var endTime = new Date() - uploadObj.startTime, 
		    i, bytes = 0, nodes = Y.all("#r"+CURRENT_ROW+" td");

		for (i = 0; i < uploadObj.totalFiles; i++) {
			bytes += uploadObj.bytesUploaded[i];
		}

		nodes.item(FILE_NODE).setContent(uploadObj.curFile + "/" + uploadObj.totalFiles);
		nodes.item(SIZE_NODE).setContent(prettySize(bytes));
		nodes.item(TIME_NODE+TIME_OFFSET).setContent(prettyTime(endTime));

        // keep track of all times so we can mark the fastest
		TIME_KEEPER["r"+CURRENT_ROW + "c" + (TIME_NODE+TIME_OFFSET)] = endTime;
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

    // ---------- UPLOAD TESTS ----------
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
				startNextUploadTest();
			}
		};

		doNextUpload();
	}

    function parallelOrigUploadTest() {
        _parallelUploadTest(RESIZE_NO);
    }
    
    function parallelResizeUploadTest() {
        _parallelUploadTest(RESIZE_YES);
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
				startNextUploadTest();												 
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
				startNextUploadTest();
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
					startNextUploadTest();
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
		_serialArchiveTest("zip");
	}
	
	function serialArchiveGZipTest() {
		_serialArchiveTest("tar-gzip");
	}

	function serialArchiveBZip2Test() {
		_serialArchiveTest("tar-bzip2");
	}
	
    // ---------- RESUABLE UPLOAD TESTS (called from above) ----------
	function _parallelUploadTest(resize) {
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
					startNextUploadTest();
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
	
	function _serialArchiveTest(fmt) {
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
					startNextUploadTest();
				});
			});
		};
		
		resizeImages(function(files){
			archiveIt(files);
		});
	}

    function startNextUploadTest() {
        var test = TEST_FUNCS.shift(), nextUploadTest = test[0];
        // we're at end of a round of tests, clear previous file/size columns
        if (test[2] > 0 && test[2] != TIME_OFFSET) {
            Y.all(".c0").setContent("&nbsp;");
            Y.all(".c1").setContent("&nbsp;");
        }

        CURRENT_ROW = test[1];
        TIME_OFFSET = test[2];

        // first, find min value
        var min = Number.MAX_VALUE;
        var r, c, cell;
        for (r = 1; r <= NUM_TESTS; r++) {
            for (c = 0; c < TIME_OFFSET+1; c++) {
                cell = "r"+r+"c"+(TIME_NODE+c);
                if (TIME_KEEPER[cell]) {
                    min = Math.min(min, TIME_KEEPER[cell]);
                }
            }
        }

        // don't unset the "winner" when test is over
        if (CURRENT_ROW != -1) {
            // clear out all previous "winners"
            Y.all(".winner").toggleClass("winner");
        }

        if (min != Number.MAX_VALUE) {
            // highlight the winners (there may be more than 1)
            for (r = 1; r <= NUM_TESTS; r++) {
                for (c = 0; c < TIME_OFFSET+1; c++) {
                    cell = "r"+r+"c"+(TIME_NODE+c);
                    if (TIME_KEEPER[cell] === min) {
                        Y.one("#r"+r + " .c" + (TIME_NODE+c)).toggleClass("winner");
                    }
                }
            }
        }

        // perform next test
        nextUploadTest();
    }
    

    // ---------- WE ARE DONE ----------
	function uploadTestComplete() {
		log(METHOD, "Test Complete.	 Reload the page to try again.");
	}

    // ---------- BROWSERPLUS INIT + REQUIRE + DRAG AND DROP -----------
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
		if (Y.Lang.isArray(arg.value)) {
			arg = arg.value;
		}

		for (var i = 0; i < arg.length; i++) {
			if (arg[i].mimeType[0] == "application/x-folder") { continue; }// skip folders
			filesToUpload.push(arg[i]);
		} 

		// now let's disable that pesky drop target.
		log(START, "Now processing " + filesToUpload.length + " files...");
		enableDropTarget(false);
		startNextUploadTest();
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

	
    // TEST_FUNCS is defined on the bottom so that the functions referenced
    // are already defined.  Array is:
    //    [TestFunction, TableRow, TimeColOffset]
    var NUM_TESTS = 9;
    var NUM_ROUNDS = 3;
    var TEST_FUNCS = [
        [ serialUploadTest,             1,  0],
        [ parallelOrigUploadTest,       2,  0],
        [ serialChunkUploadTest,        3,  0],
        [ parallelResizeUploadTest,     4,  0],
        [ serialLZMAUploadTest,         5,  0],
        [ parallelLZMAUploadTest,       6,  0],
        [ serialArchiveZipTest,         7,  0],
        [ serialArchiveGZipTest,        8,  0],
        [ serialArchiveBZip2Test,       9,  0],
        [ serialUploadTest,             1,  1],
        [ parallelOrigUploadTest,       2,  1],
        [ serialChunkUploadTest,        3,  1],
        [ parallelResizeUploadTest,     4,  1],
        [ serialLZMAUploadTest,         5,  1],
        [ parallelLZMAUploadTest,       6,  1],
        [ serialArchiveZipTest,         7,  1],
        [ serialArchiveGZipTest,        8,  1],
        [ serialArchiveBZip2Test,       9,  1],
        [ serialUploadTest,             1,  2],
        [ parallelOrigUploadTest,       2,  2],
        [ serialChunkUploadTest,        3,  2],
        [ parallelResizeUploadTest,     4,  2],
        [ serialLZMAUploadTest,         5,  2],
        [ parallelLZMAUploadTest,       6,  2],
        [ serialArchiveZipTest,         7,  2],
        [ serialArchiveGZipTest,        8,  2],
        [ serialArchiveBZip2Test,       9,  2],
        [ uploadTestComplete,           -1,  -1]
    ];

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