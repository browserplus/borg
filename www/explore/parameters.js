var ExParamValues = {

	Archiver_archive_format: "tar-gzip",
	
	DragAndDrop_AddDropTarget_id: "result",
	DragAndDrop_AttachCallbacks_id: "result",

	FileTransfer_download_url: "http://browserplus.org/images/site-logo.png",
	InactiveServices_All_platform: "win32",
	InactiveServices_Describe_service: "Uploader",
	InactiveServices_Describe_version: "3.2.5",
	InactiveServices_Describe_platform: "osx",
	Motion_Coords_method: "motion", 
	Uploader_upload_url: "/misc/upload.php",
	Uploader_simpleUpload_url: "/misc/upload.php",
	FileTransfer_upload_url: "/misc/upload.php",
	FileTransfer_simpleUpload_url: "/misc/upload.php",
	Log_Fatal_location: "FatalMethodTest",
	Log_Fatal_message: "Fatal - Hello World!",
	Log_Error_location: "ErrorMethodTest",
	Log_Error_message: "Error - Hello World!",
	Log_Warn_location: "MethodWarnTest",
	Log_Warn_message: "Warn - Hello World!",
	Log_Info_location: "MethodInfoTest",
	Log_Info_message: "Info - Hello World!",
	Log_Debug_location: "MethodDebugTest",
	Log_Debug_message: "Debug - Hello World!",
	
    Notify_show_title: "Hello World!",
    Notify_show_message: "Pretty cool - native notifications driven by JavaScript.",
    Notify_show_icon: "http://l.yimg.com/a/i/us/bp/s/1.106/tool/notify-icon3.png",
    
    PStore_get_key: "hello",
    PStore_set_key: "hello",
    PStore_set_value: "world",
    
	PublishSubscribe_postMessage_data: "Hello World!",
	PublishSubscribe_postMessage_targetOrigin: "*",

	ImageAlter_transform_actions: '[{"thumbnail":{"maxheight":150}}, {"rotate":90}]',
	
	JSONRequest_get_url: "http://browserplus.yahoo.com/api/v2/corelets/osx"
};

var ExParamHelp = {
	Archiver_archive_format: "use 'zip', 'zip-uncompressed', 'tar', 'tar-gzip' or 'tar-bzip2'",
	Archiver_archive_followLinks: "If true, symbolic links will be followed. Default is false.",

	Directory_list_followLinks: "If true, symbolic links will be followed. Default is true.", 
	Directory_list_mimetypes: 'Optional list of mimetype filters to apply (e.g.["image/jpeg"]',
	Directory_list_limit: "Maximum number of items to examine. Default is 1000.",
	Directory_list_callback: "Optional callback with will be invoked with each path",
	
	Directory_recursiveList_followLinks: "If true, symbolic links will be followed. Default is true.", 
	Directory_recursiveList_mimetypes: 'Optional list of mimetype filters to apply (e.g.["image/jpeg"]',
	Directory_recursiveList_limit: "Maximum number of items to examine. Default is 1000.",
	Directory_recursiveList_callback: "Optional callback with will be invoked with each path",

	Directory_recursiveListWithStructure_followLinks: "If true, symbolic links will be followed. Default is true.", 
	Directory_recursiveListWithStructure_mimetypes: 'Optional list of mimetype filters to apply (e.g.["image/jpeg"]',
	Directory_recursiveListWithStructure_limit: "Maximum number of items to examine. Default is 1000.",
	Directory_recursiveListWithStructure_callback: "Optional callback with will be invoked with each path",

	Downloader_get_url: "The url to the content to download",
	Downloader_get_callback: "Download progress",
	
	DragAndDrop_AddDropTarget_id: 'use "result" to drag+drop files to "Console" below, then run AttachCallbacks()',
	DragAndDrop_AddDropTarget_mimeTypes: 'Ex/ ["image/gif", "image/jpeg"]',
	DragAndDrop_AttachCallbacks_id: 'use "result" to drag+drop files to "Console" box below',

	FileAccess_read_offset: "The beginning byte offset.",
	FileAccess_read_size: "The amount of data.",

	FileAccess_slice_offset: "The beginning byte offset.",
	FileAccess_slice_size: "The amount of data.",
	
	FileAccess_chunk_chunkSize: "The desired chunk size, not to exceed 2MB. Default is 2MB.",
	
	FileBrowse_OpenBrowseDialog_mimeTypes: 'Ex/ ["image/png", "image/jpeg", "image/gif"]',
	FileBrowse_saveAs_name: 'The default filename',

	FileTransfer_download_url: "The URL of the file to download",
	FileTransfer_download_file: "The default name of the saved file",

	ImageAlter_Alter_crop: "Ex/ {x1: 0.25, y1: 0.25, x2: 0.75, y2: 0.75}",
	ImageAlter_Alter_quality: 'high | medium | low',
	ImageAlter_Alter_effects: 'Ex/ ["sepia", "swirl"]', 
	ImageAlter_Alter_format:  'gif | jpeg | png',

	// ImageAlter 4+
	ImageAlter_transform_actions: 'or try: ["sepia", {"contrast":1}]',
	
	InactiveServices_All_platform: '"osx" or "win32"',
	InactiveServices_Describe_platform: '"osx" or "win32"'

};

var ExParamHints = {
	Uploader_upload_files: "filemap",
	FileTransfer_upload_files: "filemap",
	FileTransfer_download_file: "writablefile",
	Zipper_createZip_files: "filelist",	 // version < 2
	Zipper_create_files: "filelist",  // version < 2
	Zipper_zip_files: "filelist",	  // version >= 2
	Tar_create_files: "filelist",	  // version < 2
	Tar_tar_files: "filelist",		   // version >= 2
	Directory_list_files: "filelist",
	Directory_recursiveList_files: "filelist",
	Directory_recursiveListWithStructure_files: "filelist",
	Archiver_archive_files: "filelist"
};
