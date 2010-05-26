# Resumable Uploads

Serving a market with less than reliable connections?  Why let faulty connections frustrate
your users when with a little dose of BrowserPlus and  a sprinkle of server side logic
you can offer [resumable, fault-tolerant uploads](/demo/robusto/).

For resumable uploads, a file is sliced into chunks with @{s FileAccess}.  The file's 
contents are finger printed with @{s MD5} so we can know once the file is reassembled
on the server, that every bit made the journey.  And to send those bits,  @{s FileTransfer}
is pressed into service yet again.

[![Resumable Uploads Screenshot](/i/d/robusto.jpg)](/demo/robusto/)

## Discussion

### API
To keep track of what's been uploaded, the typical upload script has been modified to provide
a little bit of a web services api.  Four JSON encoded results may be returned.

| Status      | Value  | Description
|-------------|--------|--------------------------------------------
| new         | " "    | Initial response before file is uploaded, empty string.
| partial     | 1,2,3  | A comma separated string containing previously uploaded chunk numbers
| complete    | *url*  | The url pointing to the uploaded file.  Because this is a demo, the actual file is never stored on disk.
| error       | *msg*  | The error message.

For example, when a file is partially uploaded, this response is returned:

~~~
{
    "status" : "partial",
    "value"  : "0,1,2,3,4"
}
~~~

### Farther

This demo artificially limits the user to uploading a single file, one chunk at a time.  With a 
few adjustments, the code could be extended to upload multiple chunks at once, as well as handle
multiple files.  And as [Uploadr](/demos/docs/Upload.html) shows, files could easily be archived
or compressed before uploading to save users with slow links.

While the demo does MD5 checksumming on the entire contents of the file, each individual chunk 
could be finger printed with the client and then checked upon arrival at the server.

Happy hacking.  

**Note:** [Source on GitHub](http://github.com/browserplus/borg/tree/master/www/demo/robusto).
