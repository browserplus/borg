# Image Alter

Upstream bandwidth bills burning through your cash faster than a laser beam through butter?
If you don't want to pay for all those bits, then you're going to have to reduce the number 
sent.  But how?

BrowserPlus includes a full arsenal of tools to support the next generation uploader. The shiny new @{s Archiver}
service provides enhanced file combination options (including tar and zip), as well as rounding out client side
compression support. Archiver includes both **GZip** and **BZip2** in addition to pre-exisiting @{s LZMA} support.

@{s FileAccess} v2.0 and above supports "file chunking", allowing you to take a slice of an existing file to create
a new file. This low-level feature can allow web applications to build higher-level features such as *resumable
uploads* (especially combined with client side with file checksumming from @{s FileChecksum}) or more efficient
*parallel uploading*.

[![File Uploader Screenshot](/i/d/uploadr.jpg)](/demo/uploadr/)

## Discussion

Using the full combination of BrowserPlus file tools, the Uploadr demo uploads the selected (@{s DragAndDrop})
files in *nine different ways*.  Each upload method is run three times to round out testing bias.

What type of files do your users typically upload?  Depending on the answer, this upload test should point
you to the fastest upload method.  With the right services in place, BrowserPlus can save your bandwidth
through smaller uploads and your users time with smaller transfers.