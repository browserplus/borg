# FileBrowser

Present the user with a file browse dialog.

##  BrowserPlus.FileBrowse.OpenBrowseDialog({params}, function(){})

Present the user with a native browse dialog. On OSX and Windows XP, multiple files and folders may be selected. On Windows Vista,
multiple files or a single folder may be selected.

### Parameters

recurse: Boolean *(Optional)*
: If true and a folder is selected, the folder's recursive contents will be returned. If false, the folder itself will be returned. Default
is true.

mimeTypes: List *(Optional)*
: A list of mimetypes to filter against. Only items which match one of these mimetypes will be accepted. Default is empty (all items will be
accepted)

includeGestureInfo: Boolean *(Optional)*
: Should selection gesture information be included in the argument to the callback? Default is false. If false, the argument is an array of
opaque file handles. If true, the argument is a map containing keys 'actualSelection' and 'files'. The 'actualSelection' value is a list
of opaque file handles representing what was actually selected by the UI gesture. The 'files' value is an array of maps, each entry
containing keys 'handle' (value is an opaque file handle) and 'parent' (value is handle id of element in 'actualSelection' list which
resulted in this file being included).

limit: Integer *(Optional)*
: Maximum number of items which will be included. Default is 10000
