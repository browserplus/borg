# Uploader

The BrowserPlus Uploader tool allows you to easily create an advanced uploader widget that supports native drag+drop from the
desktop, multiple file selection from the file browse dialog, multiple simultaneous uploads (3 files at a time), and file compression
(zip, bzip2, gzip). Skinning is also possible with plenty of parameters that may be overridden to specify colors, fonts, labels and
borders. All of this is packaged into two Javascript files (browserplus-min.js and uploader-min.js) that weigh in at a combined 20KB
of minimized Javascript.

![BrowserPlus Uploader Widget](/i/w/uploader.jpg)

## 1. Create Container

Create and place a block type element like div on your webpage. Specify the height and width of the element.

    <div id="myUploader" style="width:600px; height:200px"></div>  

## 2. Include JavaScript

Include the base BrowserPlus library, and the advanced upload JavaScript library at the bottom of your webpage:

    <script src="@{bpminjs}"></script>  
    <script src="http://bp.yahooapis.com/toolbox/uploader/@{uploadver}/uploader-min.js"></script

## 3. Create Upload Object

Create an instance of the Uploader widget and point it at your uploader element. The second argument contains
configuration parameters, detailed below.

    var uploader = BPTool.Uploader.create("myUploader", { /* config params */});  
    uploader.render();  

It's important to understand that this JavaScript library is a convenience wrapper around BrowserPlus services, and
its use is completely optional. Much more flexibility and control is available by directly using the BrowserPlus
JavaScript API.

## 4. Configure Parameters

Optionally customize the look and feel of the uploader. As a second parameter to create(), you may pass in a map
with any of the follow parameters (defaults shown):

~~~~
var customUploader = BPTool.Uploader.create("anotherDiv", {  
  
    uploadUrl: null,        // where files are uploaded  
    fileVarName: "file",    // name of file upload variable  
    archiveFormat: "zip",   // ('zip', 'zip-uncompressed', 'tar', 'tar-gzip', 'tar-bzip2')
  
    // Optional Callbacks, Details below in Step 5  
    fileCB: null,           // called for every file action  
    postCB: null,           // associate data with each file  
    constraintCB: null,     // called for constraint violations  
    uploadCB: null,         // called after all files uploaed  
  
    // Upload constraints  
    maxFileSize: 0,         // maximum size in KB for each file, 0 is any size  
    maxFiles: 0,            // maximum number of files that can be added to  
                            // uploader at one time  
    mimeTypes: null,        //  array of mime types allowed, like:  
                            // ["application/pdf", "application/text"]  
  
    // Upload UI  
    uploadBackground: "#f7f7f7",  
    uploadFont: "8pt tahoma,sans-serif",  
    uploadColor: "#666",  
      
    // Uploader labels  
    fileLabel: "File",  
    filesLabel: "Files",  
    sizeLabel: "Size",  
    removeLabel: "Remove?",  
    trashLabel: "Remove file from list",  
  
    // Files  
    fileBorder: "1px solid #ccc",  
    fileBackground: "#fff",  
    fileColor: "#666",  
  
    // Select and Hover  
    selectBackground: "#006adb",  
    selectColor: "#fff",  
    hoverBackground: "#ffd",  
  
    // Progress  
    progressBackground: "#def",  
    progressTextColor: "#000",  
    progressBarColor: "#b3b3b3",  
    progressBarBackground: "#fff",  
    progressComplete: "Upload Complete!",  
  
    // Footer  
    footerBackground: "#eee",  
    footerFont: "bold 8pt tahoma,sans-serif",  
    footerBorder: "1px solid #ccc",  
    footerColor:"#000",  
  
    // Footer labels (plus file(s)label above)  
    browseLabel: "Browse...",  
    uploadLabel: "Upload",  
    totalLabel: "Total"  
});  
~~~~

### File Color and Font Parameters

![Uploader Font Color and Font Parameters](/i/w/uploader-params.jpg)

### Progress Color and Font Parameters

![Uploader Progress Color and Font Parameters](/i/w/uploader-progress.jpg)

## 5. Optionally Compress Files

Optionally choose to have all files compressed and archived before upload. Allowable values
are: 'zip', 'zip-uncompressed', 'tar', 'tar-gzip' or 'tar-bzip2'.

    var uploader = BPTool.Uploader.create("myUploader", {  archiveFormat: 'zip' });  
    uploader.render();  

## Optionally Register for Callbacks

### File Callback

The fileCB() function is called when a file is added, selected, unselected, removed or uploaded.

~~~~
function fileCB = function(result) {  
    if (result.success) {  
        // result looks like this:  
        result = {  
            id:     "div_id",   // the id of the element you called create with  
            action: "action",   // uploaded | add | remove | select | unselect  
            name:   "filename", // file name  
            size:   "filesize", // size of file in bytes  
            uuid:   "uuid"      // unique id: uploader_inst + file_id  
            value:  {}          // set when action=="uploaded" contains response  
                                // from server.  Example value:   
                                // {  
                                //      statusCode:200,  
                                //      statusString:"200 OK",  
                                //      headers: { /* headers from server */}  
                                //      body: "response body"   
                                // }  
        };  
    } else {  
        // result looks like this:  
        result = {  
            id:           "div_id",  
            error:        "short error message",  
            verboseError: "optional longer error message"  
        };  
    }  
}  
~~~~

### Post Callback

The postCB allows you to associate extra data with each file that is then passed as POST data when each file is
uploaded. You may write a small form that connects to the uploader widget through file select/unselect events
which then allows the user to provide a description of each file uploaded.

    function postCB = function(uuid) {  
        return MyPostData[uuid];  
    } 


### Upload Callback

The uploadCB is fired when all files have been uploaded.

~~~~
function uploadCB = function(resul) {  
    // result is  
    result = {  
        id:      "div_id",  
        success: true/false  
    };  
}  
~~~~

### Constraint Callback

You are notified of constraint violations when you register the constraintCB. You may wish to show an error
message if any of the constraints are violated.

~~~~
function constraintCB = function(res) {  
    // result is  
    result = {  
        id:         "div_id",  
        constraint: "constraint", // "toobig" | "toomany" | "mimeType"  
        name:       "filename",  
        mimeType:   "application/text",  
        size:       123           // size in bytes  
    };  
}
~~~~

## Uploader in Action

* With just 2 javascript includes and 2 lines of code, the [simple demo](/widgets/uploader/standalone) builds a
  complicated uploader widget that allows for drag+drop from the desktop, multiple file section, and simultaneous
  uploads.
* The [Archive demo](/widgets/uploader/zipper) uses the Archive service to ZIP files so that the resulting upload is a
  single, compressed archive file.
* The [colors and constraints demo](/widgets/uploader/colors) changes the default colors of the uploader tool and
  constrains the number, type, and size of files that can be uploaded.
* When BrowserPlus is installed, use JavaScript to [automatically enhance](/widgets/uploader/progressive) your
  &lt;input type="file"&gt; tag with the Upload tool.