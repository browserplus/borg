## File Access


The @{service FileAccess} service allows you to attain the contents of a file selected by the user. The service exposes two
functions: **GetURL** will attain a temporal localhost URL to access the content. **Read** will allow javascript direct
access to the contents of a file. **Read** today has two important restrictions:

1. A limit of 2mb is imposed on the amount of data that may be read.
2. binary data (more specifically, files with embedded null bytes) may not be read.  

@{include examples/file_access.raw}

This example demonstrates how to set up a drop target over a text area
and use both functions of the FileAccess service.
Note that in a real world application you would probably want to leverage
the mime type filtering features exposed by the 
@{service FileBrowse} and @{service DragAndDrop} services.