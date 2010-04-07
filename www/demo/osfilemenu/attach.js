YUI().use('node', function(Y) {

	// Return a human presentable size string like "1.2GB"
	function prettySize(size) {
		var i, units = ['B','KB','MB','GB','TB'];
		for (i = 0; size > 1024; i++) {
			size /= 1024;
		}
		return Math.round(size*10)/10 + units[i];
	}

	function log(s) { 
		if (window.console) {
			console.log(s); 
		}
	}

	function error(what, result) {
		log(what + ": " + result.error + (result.verboseError ? ", " + result.verboseError : ""));
	}

	var getQueryParams = function() {
		var i, nv, ret = {}, q = window.location.search.substring(1).split("&"), len = q.length;

		for (i = 0; i < len; i++) {
			nv = q[i].split("=");
			ret[nv[0]] = nv[1];
		}
	
		return ret;
	};

	function getSquareThumbActions(size, width, height) {
		var r = 0.8, rx = 1, ry = 1, x1, x2, y1, y2, actions = [];

		// only crop photos bigger than thumb size
		if (width > size && height > size) {
			if (width > height) {
				rx = height / width;
			} else if (height < width) {
				ry = width / height;
			}

			// crop from very center of photo
			x1 = 0.5 - (rx * r / 2);
			y1 = 0.5 - (ry * r / 2);

			x2 = x1 + (r * rx);
			y2 = y1 + (r * ry);

			//log("crop(" + width + ", " + height + "): x1=" + x1 + ", y1=" + y1 + ", x2=" + x2 + ", y2=" + y2);
			actions.push({"crop": [x1, y1, x2, y2]});
		}

		// create square thumb
		actions.push({"scale": {"maxwidth": size, "maxheight": size}});
		return actions;
	}
		
	function isImage(mimeType) {
		var i;
		for (i = 0; i < mimeType.length; i++) {
			switch(mimeType[i]) {
			case "image/gif":
			case "image/jpeg":
			case "image/pjpeg":
			case "image/png":
				return true;
			default:
				continue;
			}
		}
		
		return false;
	}

	function addImageNode(src, name, size) {
		var node = Y.Node.create(
			'<div class="fileFrame">' +
				'<div class="imageFrame">' +
					'<img src="' + src + '">' +
				'</div>' +
				'<div class="textHolder">' + name + '</div>' +
				'<div class="textHolder">' + prettySize(size) + '</div>' +
			'</div>');
		Y.one("#attacharea").appendChild(node);
	}

	function showFile(file) {
		if (isImage(file.mimeType)) {

			//=================================================================
			// just scale, no square crop 
			var actions = [];
			actions.push({"scale": {"maxwidth": 100, "maxheight": 64}});
			BrowserPlus.ImageAlter.transform({"file": file, actions: actions}, function(t1) {
				if (t1.success) {
					BrowserPlus.FileAccess.getURL({ file: t1.value.file }, function (r) {
						if (r.success) {
							addImageNode(r.value, file.name, file.size);
						} else {
							error("FileAccess", r);
						}
					});
				} else {
					error("t1 failed", t1);
				}
			});
			//=================================================================


			//=================================================================
			// SQUARE Crop ... Bug in ImageAlter doesn't scale correctly
			// after crop, so commented out for now:
			/*
			// get image dimensions
			BrowserPlus.ImageAlter.transform({"file": file}, function(t1) {
				if (t1.success) {

					var actions = getSquareThumbActions(64, t1.value.width, t1.value.height);
					BrowserPlus.ImageAlter.transform({"file": file, quality: 70, actions: actions}, function(t2) {
						if (t2.success) {
							BrowserPlus.FileAccess.getURL({ file: t2.value.file }, function (r) {
								if (r.success) {
									addImageNode(r.value, file.name, file.size);
								} else {
									error("FileAccess", r);
								}
							});
						} else {
							error("t2 failed", t2);
						}
					});
				} else {
					error("t1 failed", t1);
				}
			});
			*/
			//=================================================================

		} else {
			addImageNode('fileicon.png', file.name, file.size);
		}
	}

	function getActualFiles(files) {
		BrowserPlus.Directory.recursiveList({files: files, followLinks: true}, function(r){
			var i, files, len;
			if (r.success) {
				files = r.value.files;
				for (i = 0, len = files.length; i < len; i++) {
					if (files[i].mimeType[0] !== "application/x-folder") { 
						showFile(files[i]);
					}
				}
			} else {
				error("getActualFiles", r);
			}
		});
	}

	function attachFiles() {
		var params = getQueryParams();
		if (params.token !== undefined) {
			BrowserPlus.FileToWeb.getSelection({token: params.token}, function(r){
				if (r.success) {
					getActualFiles(r.value);
				} else {
					error("getSelection()", r);
				}
			});
		}
	}

	BrowserPlus.init(function(init) {
		if (init.success) {
			// lots of different services for all the various upload options
			BrowserPlus.require({
				services: [
					{service: 'Directory',	 version: "2", minversion: "2.0.4" },
					{service: 'FileAccess',	 version: "2", minversion: "2.0.1" },
					{service: 'ImageAlter',	 version: "4" },
					{service: 'FileToWeb', version: "0"}
				]},
				function(require) {
					if (require.success) {
						attachFiles();
					} else {
						error("Error Loading Services", require);
					}
				});
		} else {
			error("Init", init);
		}
	}); 
});