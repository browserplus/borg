var ExParamValues = {

	DragAndDrop_AddDropTarget_id: "result",
	DragAndDrop_AttachCallbacks_id: "result",

	InactiveServices_All_platform: "win32",

	InactiveServices_Describe_service: "Uploader",
	InactiveServices_Describe_version: "3.2.5",
	InactiveServices_Describe_platform: "osx",
	Motion_Coords_method: "motion", 
	Uploader_upload_url: "/misc/upload.php",
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
	
	PublishSubscribe_postMessage_data: "Hello World!",
	PublishSubscribe_postMessage_targetOrigin: "*",

	JSONRequest_get_url: "http://browserplus.yahoo.com/api/v2/corelets/osx"
};

var ExParamHelp = {
	DragAndDrop_AddDropTarget_id: 'use "result" to drag+drop files to "Console" below, then run AttachCallbacks()',
	DragAndDrop_AddDropTarget_mimeTypes: 'Ex/ ["image/gif", "image/jpeg"]',
	DragAndDrop_AttachCallbacks_id: 'use "result" to drag+drop files to "Console" box below',
	
	FileBrowse_OpenBrowseDialog_mimeTypes: 'Ex/ ["image/png", "image/jpeg", "image/gif"]',
	
	ImageAlter_Alter_crop: "Ex/ {x1: 0.25, y1: 0.25, x2: 0.75, y2: 0.75}",
	ImageAlter_Alter_quality: 'high | medium | low',
	ImageAlter_Alter_effects: 'Ex/ ["sepia", "swirl"]', 
	ImageAlter_Alter_format:  'gif | jpeg | png',

	InactiveServices_All_platform: '"osx" or "win32"',
	InactiveServices_Describe_platform: '"osx" or "win32"'

};

var ExParamHints = {
	Uploader_upload_files: "filemap",
	Zipper_createZip_files: "filelist",  // version < 2
	Zipper_create_files: "filelist",  // version < 2
	Zipper_zip_files: "filelist",     // version >= 2
	Tar_create_files: "filelist",     // version < 2
    Tar_tar_files: "filelist",         // version >= 2
    Directory_list_files: "filelist",
    Directory_recursiveList_files: "filelist",
    Directory_recursiveListWithStructure_files: "filelist",
    Archiver_archive_files: "filelist"
};
