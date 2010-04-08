String.prototype.trim = function () {
	return this.replace(/^\s+|\s+$/g, "");
};

function flickrUploadrDemo() {

	var YE = YAHOO.util.Event;
	var YD = YAHOO.util.Dom;
	var DDM = YAHOO.util.DragDropMgr;
	var BP = BrowserPlus;
	
	var AppTitle = "BrowserPlus PhotoDrop";

	var NextImageId = 1;
	var ImageStore = {};
	var EmptyPicture = 'img/picture.gif';
	var EmptyImageData;
	var ImageCount = 0;
	var KeyListener;
	var LastSelectedId = null;
	var PhotoSiteURL = 'http://www.flickr.com/tools/uploader_edit.gne?ids=';
	//var UserPlatform = BP._detectBrowser();
	var ThumbWidth	= 120;
	var ThumbHeight = 120;
	var EditWidth = 400;
	var EditHeight = 400;
	var MsgDropPhotos = "Drop Photos Here";
	var MsgDragPhotos = "Drag Photos From Desktop<br><span id='drophelpsub'>(or click the Add button above to " + 
		"find photos on your computer)</span>";
	var MsgDropWhileUploading = "Drag Photos From Desktop<br>" + 
		"<span id='drophelpsub'>(you can still add more photos while uploading this set)</span>";
	var StatusTimeoutId = 0;
	var StatusDelay = 8000;
	var ImagePanel = null;
	var CropImg = null;
	var CropImgId = null;
	var CropImgName = "img2crop";
	var CurrentImage = "curimg"; // The selected image shown unmodified on the right side of page
	var CurrentFlickrNSID = '';
	var UploadFileNumber = 0;
	var UploadTotal = 0;
	var UploadPercentage = 0;
	var UploadThumb = {};
	
	var Ids = {
		CompleteAuthDialog: 'completeAuthDialog',
		UploadButton:		'uploadButton',
		PhotoUploadPct:		'photo_upload_pct',
		TotalUploadPct:		'total_upload_pct',
		ProgressPhoto:		'progress_photo',
		ProgressFile:		'progress_file',
		ProgressTotal:		'progress_total',
		ProgressDialog:		'progress',
		FinishDialog:		'finish',
		PhotosUploaded:		'num_photos_uploaded',
		FinishView:			'finishViewButton',
		FinishOk:			'finishOkButton',
		DropArea:			'droparea',
		DropHelp:			'drophelp',
		DragHelp:			'draghelp',
		CurrentHeight:		'ciheight',
		CurrentWidth:		'ciwidth',
		CurrentSize:		'cisize',
		ResizeCheck:		'doResizeCheck',
		ResizeValue:		'resize',
		AddAction:			'a_add',
		DeleteAction:		'a_del',
		RotateLeftAction:	'a_rol',
		RotateRightAction:	'a_ror',
		CropAction:			'a_cro',
		ClearAction:		'a_clr',
		ActionMenu:			'actionmenu',
		EffectArea:			'effectcontrolarea',
		StatusText:			'statusText',
		BuddyIcon:			'buddyIcon',
		BuddyWelcome:		'buddyWelcome',
		SigninAction:		'signinAction',
		Require:			'require',
		RequireFile:		'require_file',
		RequireTotal:		'require_total',
		RequireLocalPct:	'require_local_pct',
		RequireTotalPct:	'require_total_pct'
	};
	
	var RequiredServices = 
	[
		{service: "FileBrowse", version: "1"},
		{service: "FileAccess", version: "1"},
		{service: "DragAndDrop", version: "1"},
		{service: "ImageAlter", version: "2", minversion: "2.1.3" },
		{service: "FlickrUploader", version: "2", minversion: "2.0.14"}
	];

	function dbg(s) {
		if (window.console != undefined) {
			console.log(s);
		}
	}
	
	function createImageData(id, file, title, width, height, turl, tw, th)
	{
		return {
			id:			  id,
			file:		  file,
			filename:	  title,
			thumb_url:	  turl,
			thumb_width:  tw,
			thumb_height: th,
			width:		  width,
			height:		  height,
			size:		  0,
			rotate:		  0,
			effects:	  [],
			crop:		  {x1:0, y1:0, x2:1, y2:1}, // crop percentages
			wf:			  1, // width factor for cropping
			hf:			  1, // height factor for cropping
			title:		  title,
			description:  "",
			tags:		  "",
			is_public:	  "1",
			is_friend:	  "0",
			is_family:	  "0",
			safety_level: "1",
			hidden:		  "1",
			content_type: "1"
		};	
	}
	
	function alterImage(params, alterCB)
	{
		var s = "", t;
		for (key in params) {
			if (params.hasOwnProperty(key)) {
				if (key === "file") {
					s += "FILE, ";
				} else if (key==="crop") {
					t = "(";
					for (k in params.crop) {
						if (params.crop.hasOwnProperty(k)) {
						   t += k + "=" + params.crop[k] + ", ";
						}
					}

					s += " crop=" + t + "), ";
					
				} else {
					s += key + "=" + params[key] + ", ";
				}
			}
		}
		dbg("alterImage(" + s + ")");
		
		BP.ImageAlter.Alter(params, function(res) {
			if (res.success) {
				BP.FileAccess.GetURL({file: res.value.file}, function(r) {
					dbg(
						"w="   + res.value.width + 
						", h=" + res.value.height + 
						", ow=" + res.value.orig_width + 
						", oh=" + res.value.orig_height + 
						", url=" + r.value);


					if (r.success) {  
						alterCB({
							success: true, 
							value: {
								url:		 r.value, 
								width:		 res.value.width, 
								height:		 res.value.height,
								orig_width:	 res.value.orig_width, 
								orig_height: res.value.orig_height
							}
						});
					}
				});
			}
		});
	}
	

	function redrawImage(image)
	{
		var alterCB = function(image) {
			var i = image;
			return function(result) {
				if (!result.success) { return; }

				var w, h;
				var el = YD.get(i.id);
				var src = result.value;

				i.thumb_url	   = src.url;
				i.thumb_width  = src.width;
				i.thumb_height = src.height;
			
				displayImageDimensions(i.id);

				el.style.backgroundImage = 'url('+src.url+')';
				el.style.width = parseInt(src.width) + 'px';
				el.style.height = parseInt(src.height) + 'px';
				// force reflow so safari redraws image
				var x = document.createTextNode("reflow, you!"); 
				el.appendChild(x); 
				el.removeChild(x);
			};
		}(image);

		// Redraw Image
		alterImage(
			{
				file: image.file, 
				maxwidth: ThumbWidth, 
				maxheight: ThumbHeight, 
				quality: "low", 
				rotation:image.rotate,
				effects: image.effects,
				crop: image.crop
			}, 
			alterCB);

		/*
		BP.ImageAlter.Alter(
			{
				file: image.file, 
				maxwidth: ThumbWidth, 
				maxheight: ThumbHeight, 
				quality: "low", 
				rotation:image.rotate,
				effects: image.effects,
				crop: image.crop
			}, 
			alterCB
		);
		*/
	}

	function getSelectedImages()
	{
		return YD.getElementsByClassName('selected', 'div', Ids.DropArea);
	}

	
	function getSelectedImage()
	{
		var els = getSelectedImages();
		return (els.length === 1 ? els[0] : null);
	}

	function enableControl(el, enabled)
	{
		el.disabled = !enabled;
	}

	function checkControl(el, checked)
	{
		el.checked = checked;
	}

	function changeControls(type, enabled, func)
	{
		var i, els = YD.getElementsByClassName(type + 'control', null, type + 'controlarea');

		for (i = 0; i < els.length; i++) {
			if (els[i].id != 'resize')func(els[i], enabled);
		}
	}

	function enableBatchControls(enabled)
	{
		changeControls('batch', enabled, enableControl);
	}

  function enableUploadButton(enabled)
  {
	  uploadButton.set('disabled',!enabled);
  }
	
	function enableImageControls(enabled)
	{
		changeControls('image', enabled, enableControl);
	}

	function enableEffectControls(enabled)
	{
		changeControls('effect', enabled, enableControl);
		var els = YD.getElementsByClassName('effectlabel', null, 'effectcontrolarea');
		if (enabled) {
			YD.replaceClass(els, 'effectlabeldisabled', 'effectlabelenabled');
		} else {
			YD.replaceClass(els, 'effectlabelenabled', 'effectlabeldisabled');
		}
	}

	function checkEffectControls(check)
	{
		changeControls('effect', check, checkControl);
	}

	
	function enableImageButtons(ids, enabled)
	{
		function firstChild(id)
		{
			var el = YD.get(id);
			return el.childNodes[0];
		}
		
		function enable(el, enabled)
		{
			if (enabled) {
				YD.replaceClass(el, 'disabled', 'enabled');
			} else {
				YD.replaceClass(el, 'enabled', 'disabled');
			}
		}

		var i;
		for (i = 0; i < ids.length; i++) {
			enable(firstChild(ids[i]), enabled);
		}
	}

	function getChecked(el, defaultValue)
	{
		if (el && el.length) {
			for (var i = 0; i < el.length; i++) {
				if (el[i].checked) {
					return el[i].value;
				}
			}
			return defaultValue;
		} else {
			return (el.checked ? el.value : defaultValue);
		}
	}

	function setChecked(el, val) 
	{
		if (el && el.length) {
			for (var i = 0; i < el.length; i++) {
				el[i].checked = false;
				if(el[i].value == val) {
					el[i].checked = true;
				}
			}
		} else {
			el.checked = (el.value == val);
		}
	}

	function setOption(el, val)
	{
		for (var i = 0; i < el.length; i++) {
			if(el[i].value == val) {
				el.selectedIndex = i;
			}
		}
	}	

	function human_readable_size(size)
	{
		var i, units = ['B','KB','MB','GB','TB'];

		for (i = 0; size > 1024; i++) {
			size /= 1024;
		}

		return Math.round(size*10)/10 + units[i];
	}

	// save form data for individual image (triggered when user clicks on another image)
	function saveData(id)
	{
		var f = document.fuploadr;
		var data = ImageStore[id];
		
		data.title		  = f.title.value;
		data.description  = f.description.value;
		data.tags		  = f.tags.value;
		data.is_public	  = getChecked(f.is_public, 0);
		data.is_friend	  = getChecked(f.is_friend, 0);
		data.is_family	  = getChecked(f.is_family, 0);
		data.safety_level = f.safety_level.value;
		data.hidden		  = getChecked(f.hidden, 1);
		//data.content_type = f.content_type.value;
	}

	function notify(s,persist)
	{
		if (StatusTimeoutId != 0) {
			clearTimeout(StatusTimeoutId);
		}

		YD.get(Ids.StatusText).innerHTML = s;
		if(!persist) {
		  StatusTimeoutId = setTimeout(function() { YD.get(Ids.StatusText).innerHTML="";}, StatusDelay);
		}
	}

	function addImage(file)
	{
		
		var nextId = "id" + NextImageId++;
		
		var alterCB = function(id, imgFile)	 {
			// thanks to closure, we get to use id and imgFile
			return function(result) {
				if (!result.success) { return;}
				var src = result.value;

				var imgHolder = document.createElement('div');
				imgHolder.style.backgroundImage = 'url('+src.url+')';
				imgHolder.style.width = parseInt(src.width) + 'px';
				imgHolder.style.height = parseInt(src.height) + 'px';
				imgHolder.id = id;
				imgHolder.className = 'imageHolder';

				var imageContainer = document.createElement('div');
				imageContainer.appendChild(imgHolder);
				imageContainer.className = 'imageContainer';
				imageContainer.id = 'imageContainer-' + id;
				ImageStore[id] = createImageData(
					id, imgFile, imgFile.name, 
					src.orig_width, src.orig_height,
					src.url, src.width, src.height
				);

				ImageCount++;

				YD.get(Ids.DropArea).appendChild(imageContainer);
			};
		}(nextId, file);


		// AddImage
		alterImage(
			{
				file: file, 
				maxwidth: ThumbWidth, 
				maxheight: ThumbHeight, 
				quality: "low",
				rotation: 0 
			},
			alterCB
		);
		/*
		BP.ImageAlter.Alter(
			{
				file: file, 
				maxwidth: ThumbWidth, 
				maxheight: ThumbHeight, 
				quality: "low",
				rotation: 0 
			},
			alterCB
		);
		*/
	}

	function getPhotosetsCB(result)
	{
		if (result.success)
		{
			var widget, i, sets, option;
			sets = result.value;
			widget = document.fuploadr.setname;
			widget.options.length = 0;
			widget.options[0] = new Option("Choose a set", 0);
			for (i = 0; i < sets.length; i++) {
				widget.options[widget.options.length] = new Option(sets[i].title, sets[i].id);
			}
		}
	}
	
	function signoutCB(result)
	{
		location.reload();
	}
	
	function personInfoCB(result)
	{
	  if(result.success) {
	  // http://farm{icon-farm}.static.flickr.com/{icon-server}/buddyicons/{nsid}.jpg
	  var pinfo = result.value;
	  if(parseInt(pinfo.iconfarm,10) && parseInt(pinfo.iconserver,10)) {
			YD.get(Ids.BuddyIcon).src = 'http://farm'+ pinfo.iconfarm +
			  '.static.flickr.com/'+pinfo.iconserver + '/buddyicons/' + CurrentFlickrNSID + '.jpg';
		}
	  }
	}

	function show_signin_status(auth)
	{
		if (auth.authorized) {
			CurrentFlickrNSID = auth.nsid;
			BP.FlickrUploader.get_people_info({'nsid':auth.nsid}, personInfoCB);
		  
			YD.get(Ids.BuddyWelcome).innerHTML = " Hi " + auth.username + "!";
			YD.get(Ids.SigninAction).innerHTML = '';
			var signoutLink = YD.get(Ids.SigninAction).appendChild(document.createElement('a'));
			signoutLink.appendChild(document.createTextNode('Sign Out'));
			signoutLink.onclick = function(){BP.FlickrUploader.signout({nsid:auth.nsid},signoutCB);};
			signoutLink.href = '#';

			enableUploadButton(true);

			//BP.FlickrUploader.get_photosets({}, getPhotosetsCB);
		} 
		else {
			YD.get(Ids.BuddyIcon).src = 'http://www.flickr.com/images/buddyicon.jpg';
			YD.get(Ids.BuddyWelcome).innerHTML = "Offline";
			YD.get(Ids.SigninAction).innerHTML = '';
			var signinLink = YD.get(Ids.SigninAction).appendChild(document.createElement('a'));
			signinLink.appendChild(document.createTextNode('Sign In'));
			signinLink.onclick = function(){requestFlickrAuth(auth);return true;};
			signinLink.href = auth.auth_url;
			signinLink.target = '_blank';
			YD.get(Ids.SigninAction).appendChild(document.createTextNode(' to Flickr to upload photos...'));
		}
	}

	function displayUploadImage(url, width, height)
	{
		var el = YD.get(Ids.ProgressPhoto);
		el.setAttribute("src", url);
		el.setAttribute("width", width);
		el.setAttribute("height", height);
		YD.setStyle(el, "paddingTop", ((ThumbHeight - height)/2) + 'px');
	}

	function displayUploadProgressDialog()
	{
		displayUploadImage(EmptyPicture, 120, 120);
		YD.setStyle(Ids.PhotoUploadPct, "width", "0%");
		YD.setStyle(Ids.TotalUploadPct, "width", "0%");
		YD.get(Ids.ProgressFile).innerHTML = "";
		YD.get(Ids.ProgressTotal).innerHTML = "";
		YD.setStyle(Ids.DropArea, 'height', '340px');
		YD.setStyle(Ids.ProgressDialog, 'display', 'block');
	}

	function displayUploadFinishedDialog()
	{
		YD.setStyle(Ids.FinishDialog, 'display', 'block');
	}

	function hideUploadFinishedDialog()
	{
		YD.setStyle(Ids.FinishDialog, 'display', 'none');
		YD.setStyle(Ids.DropArea, 'height', '490px');
		YD.setStyle(Ids.DropHelp, 'margin-top', '100px');
		enableUploadButton(true);
		YD.get(Ids.DropHelp).innerHTML = MsgDragPhotos;		
	}

	function hideUploadProgressDialog()
	{
		YD.setStyle(Ids.ProgressDialog, 'display', 'none');
	}

	function imageProgressCB(result)
	{
		// file, userdata, pc, pos, size
		if (result.pc === 0)
		{
		  UploadFileNumber++;
		  YD.get(Ids.ProgressFile).innerHTML = result.file;
		  YD.get(Ids.ProgressTotal).innerHTML = UploadFileNumber + " of " + UploadTotal;

			// Display Thumbnail in Upload Dialog
		  var ut = UploadThumb[result.userdata];
		  displayUploadImage(ut.url, ut.width, ut.height);
		}
		else
		{
		  YD.setStyle(Ids.PhotoUploadPct, "width", Math.min((UploadTotal==UploadFileNumber)?100:96, result.pc) + "%");
		  UploadPercentage = ((UploadFileNumber-1) * 100/UploadTotal) + result.pc/UploadTotal;
		  YD.setStyle(Ids.TotalUploadPct, "width", Math.min(100, Math.floor(UploadPercentage)) + "%");
		}
	}

	function imagesUploadedCB(result)
	{
		//		enableUploadButton(true);  /* don't enable until the user has clicked OK or View */
	  hideUploadProgressDialog();

		if (result.success === true) {
		displayUploadFinishedDialog();
			var uploaded = result.value.files;
			var errors	 = result.value.errors;
			var i, photoIds = [], uplen=uploaded.length, erlen = errors.length;

		var photos = (uploaded.length === 1 ? "photo" : "photos");
			
		YD.get(Ids.PhotosUploaded).innerHTML = uploaded.length + " " + photos + " uploaded";
			
		if (uploaded.length > 0)
		{
			for (i = 0; i < uplen; i++) {
				photoIds.push(uploaded[i].response);
			}
			finishViewButton.set('href', PhotoSiteURL + photoIds.join(','));
		}

		if (erlen > 0)
		{
			var errs = [];
			for (i = 0; i < erlen; i++) {
				errs.push(erlen.response);
		  } 
			notify(errs.join("; "));
		}
	  } 
	  else {
		YD.setStyle(Ids.DropArea, 'height', '520px');
		YD.setStyle(Ids.DropHelp, 'margin-top', '100px');
		notify("Error: " + result.error + ", " + result.verboseError);
	  }
		//enableImageControls(true);
	}

	// load form with data from just clicked image
	function loadData(data)
	{
		var f = document.fuploadr;

		f.title.value		= data.title;
		f.description.value = data.description;
		f.tags.value		= data.tags;
		setChecked(f.is_public, data.is_public);
		setChecked(f.is_friend, data.is_friend);
		setChecked(f.is_family, data.is_family);
		setChecked(f.hidden, data.hidden);
		setOption(f.safety_level, data.safety_level);
	}

	function dropCB(args)
	{
		if (YAHOO.lang.isArray(args.value)) {
			args = args.value;
		}

		var msg = "";
		var fnames = [];
		var dropCount = 0;
		for (var i = 0; i < args.length; i++) {
			var regexp = /(\.jpe?g$)|(\.gif$)|(\.png$)/i;
			
			if (!regexp.test(args[i].name)) { 
				notify("Only JPEG, PNG and GIF Images supported at this time: " + args[i].name);
			} else {
				addImage(args[i]);
				fnames.push(args[i].name);
				dropCount++;
				notify("");
			}
		}

		if (dropCount > 0) {
			var drophelp = YD.get(Ids.DropHelp);
			if (drophelp) {
				YD.setStyle(Ids.DropHelp, 'display', 'none');
				YD.setStyle(Ids.DragHelp, 'visibility', 'visible');
			}
		}
	}

	function disableAllImageControls()
	{
		enableImageControls(false);
		enableEffectControls(false);
		checkEffectControls(false);
	}

	function unselectCurrentImage()
	{
		var el = getSelectedImage();
		if (el)	 {
			displayImageDimensions();
			YD.removeClass(el, 'selected');
			YD.setStyle(el, 'border-color', '#ffffff');
			saveData(el.id);
			loadData(EmptyImageData);
			disableAllImageControls();
		}
		
	}
	
	function selectImage(el, selected)
	{
		if (selected) {
			YD.addClass(el, 'selected');
			YD.setStyle(el, 'border-color', '#ff007b');
		} else {
			YD.removeClass(el, 'selected');
			YD.setStyle(el, 'border-color', '#ffffff');
			LastSelectedId = null;
		}
	}
	
	function displayImageDimensions(id)
	{
		var f = document.fuploadr;

		var rf=1, resize, max, w, h, image = ImageStore[id];


		if (id === undefined) {
			YD.get(Ids.CurrentWidth).innerHTML = '';
			YD.get(Ids.CurrentHeight).innerHTML = '';
		} else {

			if (YD.get(Ids.ResizeCheck).checked) {
				resize = YD.get(Ids.ResizeValue).value;
				var max = Math.max(image.width, image.height);
				if (max > resize) {
					rf = resize / max;
				}
			}
			if (image.rotate === 90 || image.rotate === 270) {
				w = Math.round(image.height * image.wf * rf);
				h = Math.round(image.width * image.hf * rf);
			} else {
				w = Math.round(image.width * image.wf * rf);
				h = Math.round(image.height * image.hf * rf);
			}

			YD.get(Ids.CurrentWidth).innerHTML = w;
			YD.get(Ids.CurrentHeight).innerHTML = h;
		}
	}

	function imageClicked(e, obj) 
	{
		var i, els, target = YE.getTarget(e);
		
		els = getSelectedImages();
		if (els.length == 1) {
			saveData(els[0].id);
			checkEffectControls(false);
		}

		// image clicked on
		if (ImageStore[target.id])
		{
			// toggle
			if (e.metaKey || e.ctrlKey)
			{
				selectImage(target.id, !YD.hasClass(target.id, 'selected'));
				els = [target.id];
				LastSelectedId = target.id;
			}
			else if (e.shiftKey && LastSelectedId)
			{
				els = YD.getElementsByClassName('imageHolder', 'div', Ids.DropArea);

				var inrange = false;

				for (i = 0; i < els.length; i++)
				{
					if (inrange === false && (els[i].id === target.id || els[i].id === LastSelectedId)) {
						inrange = true;
						selectImage(els[i], true);
					} else if (inrange) {
						selectImage(els[i], true);
						if (els[i].id === target.id || els[i].id === LastSelectedId) {
							LastSelectedId = target.id;
							break;
						}
					}
				}
			}
			else
			{
				els = getSelectedImages();
				selectImage(els, false);
				selectImage(target.id, true);
				LastSelectedId = target.id;
			}
		}
		else
		{
			els = getSelectedImages();
			selectImage(els, false);
			LastSelectedId = null;
		}

		els = getSelectedImages();

		enableImageButtons([Ids.DeleteAction], els.length > 0);
		// enable when more than 1 selected??
		enableImageButtons([Ids.RotateLeftAction, Ids.RotateRightAction, Ids.CropAction, Ids.ClearAction], els.length === 1);
	
		if (els.length === 1) 
		{
			loadData(ImageStore[els[0].id]);
			displayImageDimensions([els[0].id]);

			enableImageControls(true);
			enableEffectControls(true);
			var effects = ImageStore[els[0].id].effects;
			for (i = 0; i < effects.length; i++) {
				YD.get('effect_'+effects[i]).checked = true;
			}
		}
		else
		{
			loadData(EmptyImageData);
			displayImageDimensions();
			disableAllImageControls();
		}
	}

	function browserClicked()
	{
		BP.FileBrowse.OpenBrowseDialog({}, dropCB);
	}

  function removeImageConstruct(id)
  {
	  YD.get(Ids.DropArea).removeChild(document.getElementById('imageContainer-' + id));
  }

	// file browser button pressed
	function removeClicked()
	{
		var i, els = getSelectedImages();

		for (i = 0; i < els.length; i++) {
			removeImageConstruct(els[i].id);
			delete ImageStore[els[i].id];

		}

		if (els.length > 0) {
			enableImageButtons([Ids.DeleteAction, Ids.RotateLeftAction, Ids.RotateRightAction, Ids.CropAction, Ids.ClearAction], false);
			loadData(EmptyImageData);
			disableAllImageControls();
			ImageCount -= els.length;
		}
		
		if (ImageCount === 0) {
			YD.setStyle(Ids.DropHelp, 'display', 'block');
			YD.get(Ids.DropHelp).innerHTML = MsgDragPhotos;
		}

		displayImageDimensions();
	}

	function effectClicked(e, obj)
	{
		var effects = [], i, els;

		if (YE.getTarget(e).id.indexOf("effect_") === 0) {

			// which effects are checked
			els = YD.getElementsByClassName('effectcontrol', 'input', 'effectcontrolarea');

			for (i = 0; i < els.length; i++) {
				if (YD.get(els[i]).checked) {
					effects.push(els[i].id.substring(7));
				}
			}

			// apply effects to selected images
			els	 = getSelectedImages();
			
			for (i = 0; i < els.length; i++) {
				ImageStore[els[i].id].effects = effects;
				redrawImage(ImageStore[els[i].id]);
			}
		}
	}

	function rotate(dir)
	{
		var i, id, angle, els, crop;

		els	 = getSelectedImages();

		for (i = 0; i < els.length; i++)
		{
			id = els[i].id;
			angle = ImageStore[id].rotate;

			if (dir === 'left')	 {
				angle -= 90;
				if (ImageStore[id].wf !== 1 && ImageStore[id].hf !== 1) {
					crop_rotate_left(id);
				}
			} else { // right
				angle += 90;
				if (ImageStore[id].wf !== 1 && ImageStore[id].hf !== 1) {
					crop_rotate_right(id);
				}
			}

			if (angle < 0) {
				angle = 270;
			} else if (angle >= 360) {
				angle = 0;
			}

			ImageStore[id].rotate = angle;
			redrawImage(ImageStore[id]);
		}
	}
	
	function actionMenuClicked(e, obj)
	{
		var target = YE.getTarget(e);
		if (YD.hasClass(target, 'enabled')) {
			switch(target.parentNode.id)
			{
			case Ids.AddAction:
				browserClicked();
				break;
			case Ids.DeleteAction:
				removeClicked();
				break;
			case Ids.RotateLeftAction:
				rotate('left');
				break;
			case Ids.RotateRightAction:
				rotate('right');
				break;
			case Ids.CropAction:
				var els	 = getSelectedImages();
				if (els.length > 0) {
					createImagePanel(els[0].id);
				}
				break;
			case Ids.ClearAction:
				var els	 = getSelectedImages();
				var i, id, len = els.length;
				for (i = 0; i < len; i++) {
					id = els[i].id;
					ImageStore[id].rotate  = 0;
					ImageStore[id].effects = [];
					ImageStore[id].crop	   = {x1:0, y1:0, x2:1, y2:1};
					ImageStore[id].wf	   = 1;
					ImageStore[id].hf	   = 1;
					redrawImage(ImageStore[id]);
				}
				checkEffectControls(false);
				break;
			default:
				break;
			}
		}
		return false;
	}

	function uploadClicked()
	{
		if (ImageCount < 1) { return;}

		var f = document.fuploadr;
		var batch_tags = f.batchtags.value.trim();
		var batch_set  = 0; // TODO set photoset after pictures uploaded
		var doResize = f.doResizeCheck.checked;
		var resize	   = parseInt(f.resize.value, 10);
		var i, id, params, s;
		var filesToUpload = [];

		enableUploadButton(false);
		ImageCount = 0;
		unselectCurrentImage();

	// remove images since they're about to be uploaded
		var els = YD.getElementsByClassName('imageHolder', 'div', Ids.DropArea);
		for (i = 0; i < els.length; i++) {
			removeImageConstruct(els[i].id);
		}
		
		YD.get(Ids.DropHelp).innerHTML = MsgDropWhileUploading;
		YD.setStyle(Ids.DropHelp, 'display', 'block');
		YD.setStyle(Ids.DropHelp, 'margin-top', '80px');
		YD.setStyle(Ids.DragHelp, 'visibility', 'hidden');



		if(!doResize) { resize = 0; }

		var uploadCount = 0;
		UploadThumb = {};

		for (id in ImageStore) {
			if (ImageStore.hasOwnProperty(id)) {
				uploadCount++;
			}
		}
		
		var filesToUpload  = [];

		for (id in ImageStore) 
		{
			if (ImageStore.hasOwnProperty(id)) 
			{
				s = ImageStore[id];
				// remove from drag/drop
			//	s.dd.unreg();
				var tags = s.tags.trim();
				if (tags.length > 0) {
					tags = tags + " " + batch_tags;
				}

				params = {
					title:		  s.title,
					description:  s.description,
					tags:		  tags,
					is_public:	  parseInt(s.is_public, 10),
					is_friend:	  parseInt(s.is_friend, 10),
					is_family:	  parseInt(s.is_family, 10),
					safety_level: parseInt(s.safety_level, 10),
					hidden:		  parseInt(s.hidden, 10)
				};

				// used in imageProgressCB to display image being uploaded
				UploadThumb[id] = {
					url:	s.thumb_url,
					width:	s.thumb_width,
					height: s.thumb_height
				};

				// after image is altered, upload it
				var alterCB = function() {
					// JavaScript closure here so we get our own unique set of vars from loop
					var myid		 = id;
					var myparams	 = params;
					var myfilename	 = s.filename;

					delete ImageStore[id];

					return function(result) 
					{
						if (!result.success) { return; }

						// so we don't overwhelm the upload server, store files to upload in array and wait...
						filesToUpload.push({ 
							postvars: myparams, 
							userdata: myid,
							file: result.value.url, 
							filename: myfilename 
						});

						// TODO delete thumbnail image in droparea (some type of animation would be cool)

						// ...until all files are here.	 Corelet uploads 1 file at a time.
						if (filesToUpload.length == uploadCount) {
							UploadTotal = uploadCount;
							UploadPercentage = 0;
							UploadFileNumber = 0;
							displayUploadProgressDialog();
							BP.FlickrUploader.upload({callback: imageProgressCB,files: filesToUpload},imagesUploadedCB);
						}
					};
				}();

				if (resize > 0 || s.rotate > 0 || s.effects.length > 0 ||(s.wf !== 1 && s.hf !== 1)) 
				{
					// if any resize/rotate/effects/crop applied, get file url from image alter
					// Upload Image

					alterImage(
						{
							file:	   s.file, 
							maxwidth:  resize, 
							maxheight: resize, 
							quality:   "high", 
							rotation:  s.rotate,
							effects:   s.effects,
							crop:	   s.crop
						},
						alterCB
					);

					/*
					BP.ImageAlter.Alter(
						{
							file:	   s.file, 
							maxwidth:  resize, 
							maxheight: resize, 
							quality:   "high", 
							rotation:  s.rotate,
							effects:   s.effects,
							crop:	   s.crop
						}, alterCB);
					*/
				} else {
					// no image alternation needed, just pass file directly
					alterCB({success:true, value: {url: s.file}});
				}

			} // if hasOwn
		} // for in
	}

	function cropCanceled()
	{ 
		CropImg.destroy(); 
		CropImg = null; 
		ImagePanel.hide();
	}

	function cropClicked(e, obj) 
	{
		var image = ImageStore[CropImgId];

		var r = CropImg.getCropCoords();

		var img =  YD.get(CropImgName);

		// compute crop box in percentage
		var x1 = r.left / img.width;
		var x2 = (r.left + r.width) / img.width;
		var y1 = r.top / img.height;
		var y2 = (r.top + r.height) / img.height;

		var dx = Math.round((x2-x1)*100)/100;
		var dy = Math.round((y2-y1)*100)/100;

		x1 = image.crop['x1'] + (x1 * image.wf);
		x2 = image.crop['x2'] - ((1 - x2) * image.wf);
		y1 = image.crop['y1'] + (y1 * image.hf);
		y2 = image.crop['y2'] - ((1 - y2) * image.hf);

		image.wf = image.wf * dx;
		image.hf = image.hf * dy;
		image.crop =  {'x1': x1, 'x2': x2, 'y1': y1, 'y2': y2};

		CropImg.destroy(); 
		CropImg = null; 
		ImagePanel.hide();

		redrawImage(image);
	}

	// rotate crop box when image is rotated
	function crop_rotate_right(id)
	{
		var x1, y1, x2, y2, c = ImageStore[id].crop;
		//	{x1: 1-c.y2,  y1: c.x1,	 x2: 1-c.y1,  y2: c.x2}
		x1 = 1 - c.y2;
		y1 = c.x1;
		x2 = 1 - c.y1;
		y2 = c.x2;
		
		ImageStore[id].crop = {'x1': x1, 'y1': y1, 'x2': x2, 'y2': y2};
	}

	// rotate crop box when image is rotated
	function crop_rotate_left(id)
	{
		// {x1: c.y1,  y1: 1-c.x2,	x2: c.y2,  y3: 1-c.x1  }
		var x1, y1, x2, y2, c = ImageStore[id].crop;
		x1 = c.y1;
		y1 = 1 - c.x2;
		x2 = c.y2;
		y2 = 1 - c.x1;

		ImageStore[id].crop = {'x1': x1, 'y1': y1, 'x2': x2, 'y2': y2};
	}

	function hoverCB(hovering)
	{
		var el = YD.get(Ids.DropArea);
		if (hovering === true) {
			YD.get(Ids.DropHelp).innerHTML = MsgDropPhotos;
		} else {
			YD.get(Ids.DropHelp).innerHTML = MsgDragPhotos;
		}
	}	

	function completeFlickrAuthCB(result)
	{
		if (result.success) {
			show_signin_status(result.value);	   
		} else {
			YD.get(Ids.SigninAction).innerHTML = "Authorization failed!";
			// TODO now what, disable drag+drop and file browser?
		}
	}

	function getAuthCompleteDialog(auth)
	{
		var dialog = new YAHOO.widget.Dialog(Ids.CompleteAuthDialog,
			{ width: "350px",
			  fixedcenter: true,
			  modal: true,
			  close: false,
			  draggable: false,
			  zindex: 4,
			  postmethod: "manual",
			  visible: true,
			  constraintoviewport: true,
			  buttons: [ { text:"Complete Authorization", handler: function() {
							  dialog.hide();
									dialog.destroy();
									BP.FlickrUploader.get_auth_token({'frob': auth.frob}, completeFlickrAuthCB);				   
							}, isDefault:true },
						 { text:"Cancel", handler:function() {this.cancel();} } ]
		});

		YD.setStyle(Ids.CompleteAuthDialog, 'display', 'block');

		return dialog;
	}

	function checkAuthCB(result)
	{
		if (result.success) {
			show_signin_status(result.value);
		}
	}
  
  function requestFlickrAuth(auth) {
			var dialog = getAuthCompleteDialog(auth);
			dialog.render();
			dialog.show();
  }

	function keyboardCB(type, args, obj)
	{
		var e = args[1];
		var tagName = e.target.tagName.toUpperCase();
		if (tagName === "TEXTAREA" || tagName === "INPUT") {
			return;
		}
		
		if (e.keyCode === 8 || e.keyCode === 46) {
			// [backspace] or [delete] remove image
			removeClicked();
			YE.preventDefault(e);
		} else if (e.keyCode === 61 || e.keyCode === 107 || e.keyCode === 187) {
			// '=' or '+' on numeric keypad pop file browser
			browserClicked();
			YE.preventDefault(e);
		} else if (e.keyCode === 82) {
			rotate('right');
		} else if (e.keyCode === 76) {
			rotate('left');
		}
	}
	
	function createImagePanel(imageId)
	{
		CropImgId = imageId;

		var image = ImageStore[CropImgId];
		
		if (ImagePanel == null) {

			ImagePanel = new YAHOO.widget.Panel(
				'ImagePanel',
				{ 
					width: "650px", 
					fixedcenter: true,
					close: true, 
					draggable: true, 
					zindex: 4,
					modal: true,
					visible: false,
					constraintoviewport:true
				}
			);

			ImagePanel.setHeader("Crop Image");
			ImagePanel.setBody(
				'<div>' + 
				'<div id="iep"></div>' +
				'<br>' +
				'<input id="editCrop" type="button" value="Crop">' +
				'&nbsp;' +
				'<input id="editCancel" type="button" value="Cancel">' + 
				'</div>');

			ImagePanel.render(document.body);

			YE.addListener('editCrop', 'click', cropClicked, this, true);
			YE.addListener('editCancel', 'click', cropCanceled, this, true);
		}

		var alterCB = function(result) {
			if (!result.success) { return; }

			var src = result.value;

			YD.get("iep").innerHTML = '<img src="' + src.url + '" width="' + src.width + 
				'" height="' + src.height + '" id="' + CropImgName + '">';

			ImagePanel.show();
			(function() {
				var size = 0.8;
				var x = Math.round((src.width * (1 - size))/2);
				var y = Math.round((src.height * (1 - size))/2);
				var w = Math.round(src.width * size);
				var h = Math.round(src.height * size);

				CropImg = new YAHOO.widget.ImageCropper(CropImgName, {
					initialXY: [x, y],
					initWidth: w,
					initHeight: h,
					keyTick: 5,
					ratio: false,
					shiftKeyTick: 50
				});
			})();
		};

		// CropImage
		alterImage(
			{
				file: image.file, 
				maxwidth: EditWidth, 
				maxheight: EditHeight,
				quality: "medium",
				crop: image.crop,
				rotation:image.rotate,
				effects: image.effects
			},
			alterCB
		);

		/*
		BP.ImageAlter.Alter(
			{
				file: image.file, 
				maxwidth: EditWidth, 
				maxheight: EditHeight,
				quality: "medium",
				crop: image.crop,
				rotation:image.rotate,
				effects: image.effects
			}, 
			alterCB
		);
		*/
	}

	function imageDblClicked(e, obj)
	{
		var el	= getSelectedImage();
		if (el) {
			createImagePanel(el.id);
		}
	}

	function doResizeClicked(e, obj)
	{
		YD.get(Ids.ResizeValue).disabled = !YD.get(Ids.ResizeCheck).checked;
		var el = getSelectedImage();
		if (el) {
			displayImageDimensions(el.id);
		}
	}

	function resizeChanged(e, obj)
	{
		var el	= getSelectedImage();
		if (el) {
			displayImageDimensions(el.id);
		}
	}

	function loadUI()
	{
		EmptyImageData = createImageData(null, null, '', 0, 0);

		KeyListener = new YAHOO.util.KeyListener(document, { keys:[8, 46, 61, 107, 187, 82, 76] }, keyboardCB);
		KeyListener.enable();
		
		YE.addListener(Ids.DropArea,	'click', imageClicked);
		YE.addListener(Ids.DropArea,	'dblclick', imageDblClicked);
		YE.addListener(Ids.ActionMenu,	'click', actionMenuClicked);
		YE.addListener(Ids.EffectArea,	'click', effectClicked);
		YE.addListener(Ids.ResizeCheck, 'click', doResizeClicked);
		YE.addListener(Ids.ResizeValue, 'change', resizeChanged);

		enableImageControls(false);
		enableBatchControls(true);
		enableImageButtons([Ids.AddAction], true);
		YD.get(Ids.ResizeCheck).checked = false;
		BP.DragAndDrop.AddDropTarget({id: Ids.DropArea}, function(){});
		BP.DragAndDrop.AttachCallbacks({id: Ids.DropArea, hover: hoverCB, drop: dropCB}, function(){});
		YD.get(Ids.DropHelp).innerHTML = MsgDragPhotos;

		//var dropTarget = new YAHOO.util.DDTarget(Ids.DropArea);
		BP.FlickrUploader.check_auth({}, checkAuthCB);
	}
  
	function doRequire() {
		BP.require(
			{
				services: RequiredServices, 
				progressCallback: function(status) {
					if (status.totalPercentage === 0) {
						YD.setStyle(Ids.Require, 'display', 'block');
					}

					YD.get(Ids.RequireFile).innerHTML = status.name;
					YD.get(Ids.RequireLocalPct).style.width = status.localPercentage + "%";
					YD.get(Ids.RequireTotal).innerHTML = status.totalPercentage + "%";
					YD.get(Ids.RequireTotalPct).style.width = status.totalPercentage + "%";
				}
			}, 
			function(result) { // requireCB
				YD.setStyle(Ids.Require, 'display', 'none');

				if(result.success) { 
					loadUI(); 
				} else {
					YD.get(Ids.SigninAction).innerHTML = 'Please ';
					var signinLink = YD.get(Ids.SigninAction).appendChild(document.createElement('a'));
					signinLink.appendChild(document.createTextNode('update services'));
					signinLink.onclick = function(){doRequire();return false;};
					signinLink.href = '#';
					YD.get(Ids.SigninAction).appendChild(document.createTextNode(' to run this application.'));
				}
			}
		);	  
	}
  
	function initCB(result) {
		if(result.success) {
			doRequire();
		} else {
			// show a modal error dialog...
			YAHOO.bputil.createErrorPanel(
				'BrowserPlus Failure',
				'BrowserPlus failed to initialize. ' + 
				'Try restarting your browser to resolve this issue. (' +
				result.error + ": " + result.verboseError).show();
		}
	}

	var uploadButton	 = new YAHOO.widget.Button(Ids.UploadButton, {disabled:true});
	uploadButton.addListener('click', uploadClicked);

	var finishViewButton = new YAHOO.widget.Button(Ids.FinishView);
	finishViewButton.addListener("click", hideUploadFinishedDialog);

	var finishOkButton	 = new YAHOO.widget.Button(Ids.FinishOk);
	finishOkButton.addListener("click", hideUploadFinishedDialog);

	YE.addListener(window, "load", function() {
		BPTool.Installer.show({}, initCB);
	});
}

flickrUploadrDemo();