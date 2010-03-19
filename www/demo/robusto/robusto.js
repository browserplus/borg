YUI().use('node', function(Y) {

	var FileTooBig = 5*1024*1024;
	var UploadUrl = "/demo/robusto/upload.php";

	var FileHandle;
	var FileMD5;
	var FileChunksUploaded; // array of file chunk nums already uploaded [0,1,2]
	var UploadFlag = true;
	
	
	function getUploadUrl(md5, name) {
		return UploadUrl + "?md5="+md5+"&name=" + name;
	}

	// Return a human presentable size string like "1.2GB"
	function prettySize(size) {
		var i, units = ['B','KB','MB','GB','TB'];
		for (i = 0; size > 1024; i++) {
			size /= 1024;
		}
		return Math.round(size*10)/10 + units[i];
	}

	// add commas to big ints
	function numberFormat(n) {
		var str = "" + n, re = /(\d+)(\d{3})/;
		while (re.test(str)) {
			str = str.replace(re, '$1' + ',' + '$2');
		}
		
		return str;
	}
	
	function c_err(str) {
		return '<span class="err">' + str + '</span>'; 
	}

	function c_hi(str) {
		return '<span class="hi">' + str + '</span>'; 
	}

	function getChunkSize(fileSize) {
		var kb = 1024, mb = kb*kb;

		if (fileSize < 512*kb) {
			return 16*kb;
		} else if (fileSize < 2*mb) {
			return 32*kb;
		} else if (fileSize < 4*mb) {
			return 64*kb;
		} else {
			return 128*kb;
		}
	}

	function getNumChunks(fileSize, chunkSize) {
		return Math.ceil(fileSize / chunkSize);
	}

	function log(str) {
		Y.one("#d_status").setContent(str);
	}

	// draw a litle box to represent each file chunk
	function drawChunks(numChunks) {
		var i, chunks = [];
		for (i = 0; i < numChunks; i++) {
			chunks.push('<span id="chunk' + i + '">' + i + '</span>');
		}
		
		Y.one("#chunks").setContent(chunks.join(""));
	}

	// highlight already uploaded chunks
	function highlightChunks(uploadedChunks) {
		var i, len;
		for (i = 0, len=uploadedChunks.length; i < len; i++) {
			Y.one("#chunk"+uploadedChunks[i]).addClass("complete");
		}
	}

	// called for new and resumed files
	function startActualUpload() {
		var i, len, tbl = {}, chunksNotUploaded = [],
			file = FileHandle, 
			chunksAlreadyUploaded = FileChunksUploaded, 
			chunkSize = getChunkSize(file.size), 
			numChunks = getNumChunks(file.size, chunkSize);

		// put chunks already uploaded in table for easy lookup
		for (i = 0, len = chunksAlreadyUploaded.length; i < len; i++) {
			tbl[chunksAlreadyUploaded[i]] = true;
		}

		// compute chunks that actually need to be uploaded
		for (i = 0; i < numChunks; i++) {
			if (!tbl[i]) { chunksNotUploaded.push(i); }
		}

		var uploadIt = function(chunk) {
			var url, offset = chunk * chunkSize; // chunkSize comes from outer context
			
			url = getUploadUrl(FileMD5, FileHandle.name);

			log("Uploading chunk# " + chunk);
			BrowserPlus.FileAccess.slice({file:file, offset:offset, size:chunkSize}, function(r1) {

				var vars = {
					chunk:  ""+chunk,
					chunks: ""+numChunks
				};
				
				BrowserPlus.Uploader.upload({ files: { "file": r1.value }, url: url, postvars: vars}, function(r2) {
					var results;
					if (r2.success) {
						results = JSON.parse(r2.value.body);
						
						if (results.status == "partial") {
							log("Uploaded chunk#" + chunk);
							FileChunksUploaded.push(chunk);
							highlightChunks([chunk]);
						} else if (results.status == "complete") {
							log('File uploaded!  <a href="' + results.value + '">Download it</a>.');
							FileChunksUploaded.push(chunk);
							highlightChunks([chunk]);
						} else {
							log("Upload error: " + results.value);
						}

					} else {
						error("upload", r2);
						return;
					}
					doNextUpload(chunk);
				});			
			});
		};

		var doNextUpload = function(chunk) {
			if (UploadFlag && chunksNotUploaded.length > 0) {
				uploadIt(chunksNotUploaded.shift());
			} else {
				Y.one("#b_stop").set("disabled", true);
				Y.one("#b_start").set("disabled", !chunk || chunk == chunksNotUploaded.length);
				Y.one("#b_file").set("disabled", false);
			}
		};
		
		doNextUpload();
	}

	function startResumableUpload(numChunks) {
		var file = FileHandle;

		log("Computing MD5 Checksum of file...");
		BrowserPlus.FileChecksum.md5({file:file}, function(checksum) {
			if (checksum.success) {

				var url;

				// save ref to MD5 for use in actual upload
				FileMD5 = checksum.value;

				url = getUploadUrl(FileMD5, file.name);

				log("Querying server for previously uploaded chunk...");
				BrowserPlus.JSONRequest.get({url:url}, function(req){
					var v = req.value;
					if (req.success) {

						// 3 states - new|partial|complete
						// partial comes back with csv list of previously uploaded chunks
						if (v.status === "partial") {
							log("Press " + c_hi("Start") + " to resume the upload.");
							FileChunksUploaded = v.value.split(",");
							drawChunks(numChunks);
							highlightChunks(FileChunksUploaded);
						} else if (v.status === "new") {
							log("Press " + c_hi("Start") + " to upload the file.");
							drawChunks(numChunks);
							FileChunksUploaded = [];
						} else if (v.status === "complete") {
							Y.one("#chunks").setContent("&nbsp;");
							log('File already uploaded.  <a href="' + v.value + '">Download it</a>.');
						} else {
							log("Error: " + v.value);
						}
						
						Y.one("#b_stop").set("disabled", true);
						Y.one("#b_start").set("disabled", false);
						Y.one("#b_file").set("disabled", false);

					} else {
						error("JSONRequest", req);
					}
				});
			} else {
				error("md5 failed", checksum);
			}
		});

	}

	function fileSelectEvent() {
		BrowserPlus.FileBrowse.OpenBrowseDialog({}, function(r){
			var file, chunkSize, numChunks;

			// user canceled dialog
			if (!r.success) { return; }

			// This is a demo.  Just look at first file.
			file = r.value.files[0];

			if (file.mimeType == "application/x-folder") {
				log(c_err("Folder selected instead of file"));
				return;
			}


			// store globalie reference to file so we do lots of async calling
			FileHandle = file;

			// ok, we have a file
			chunkSize = getChunkSize(file.size);
			numChunks = getNumChunks(file.size, chunkSize);

			if (file.size > FileTooBig) {
				log(c_err("Sorry, file is too big for this demo (" + 
					Math.floor(FileTooBig/(1024*1024)) + "MB max)"));
				Y.one("#d_file").setContent("&nbsp;");
				Y.one("#d_size").setContent("&nbsp;");
				Y.one("#d_chunks").setContent("&nbsp;");
				drawChunks(0);
			} else {
				Y.one("#d_file").setContent(file.name);
				Y.one("#d_size").setContent(prettySize(file.size));
				Y.one("#d_chunks").setContent(numberFormat(numChunks) + " @ " + prettySize(chunkSize));
				Y.one("#b_stop").set("disabled", false);
				Y.one("#b_start").set("disabled", true);
				Y.one("#b_file").set("disabled", true);
				UploadFlag = true;

				startResumableUpload(numChunks);
			}
		});
	}

	function clickEvent(e) {
		var node = e.target, id=node.get("id");
		e.preventDefault();

		if (id === "b_file") {
			fileSelectEvent();
		} else if (id === "b_start") {
			// need lots of globalies :(
			UploadFlag = true;
			Y.one("#b_stop").set("disabled", false);
			Y.one("#b_start").set("disabled", true);
			Y.one("#b_file").set("disabled", true);
			startActualUpload();
		} else if (id === "b_stop") {
			UploadFlag = false;
			log("");
			Y.one("#b_stop").set("disabled", true);
			Y.one("#b_start").set("disabled", false);
			Y.one("#b_file").set("disabled", false);
		}
	}
	
	function error(what, result) {
		log("Error in " + what + ": " + result.error + (result.verboseError ? ", " + result.verboseError : ""));
		Y.one("#b_stop").set("disabled", true);
		Y.one("#b_start").set("disabled", false);
		Y.one("#b_file").set("disabled", false);
	}

	BrowserPlus.init(function(init) {
		if (init.success) {
			log("BrowserPlus initialized, loading services...");
			// lots of different services for all the various upload options
			BrowserPlus.require({
				services: [
					{service: 'FileAccess',   version: "2", minversion: "2.0.1" },
					{service: 'FileBrowse',   version: "2"},
					{service: 'FileChecksum', version: "1"},
					{service: 'JSONRequest',  version: "1"},
					{service: 'Uploader',     version: "3", minversion: "3.1.5" }
			]},
			function(require) {
				if (require.success) {
					log("Sevices Loaded.  Ready!");
					Y.on("click",  clickEvent, '#frm');
					Y.one("#b_file").set("disabled", false);
				} else {
					error("Error Loading Services", require);
				}
			});
		} else {
			error("Init", init);
		}
	}); 
});