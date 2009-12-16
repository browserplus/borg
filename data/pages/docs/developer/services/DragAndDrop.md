# DragAndDrop

Support drag and drop of files from desktop to web browser.

## BrowserPlus.DragAndDrop.AddDropTarget({params}, function(){})

Starts monitoring drag/drop activity for the specified element.

### Parameters

id: string
: The 'id' of the registered DOM element to operate on

mimeTypes: list *(Optional)*
: A list of mimetypes to filter against. Only items which match one of these mimetypes will be accepted. Default is empty (all dropped
items will be accepted).

includeGestureInfo: boolean *(Optional)*
: Should selection gesture information be included in the argument to the 'drop' callback? Default is false. If false, the argument is an
array of opaque file handles. If true, the argument is a map containing keys 'actualSelection' and 'files'. The 'actualSelection' value is
a list of opaque file handles representing what was actually selected by the UI gesture. The 'files' value is an array of maps, each entry
containing keys 'handle' (value is an opaque file handle) and 'parent' (value is handle id of element in 'actualSelection' list which
resulted in this file being included).

limit: integer *(Optional)*
: Maximum number of items which will be included in a drop. Default is 10000



## BrowserPlus.DragAndDrop.AttachCallbacks({params}, function(){})

AttachCallbacks to a registered drop target. This function will not return until RemoveDropTarget is called, so it should not be invoked
synchronously.

### Parameters

id:	string
: The 'id' of the registered DOM element to which you wish to attach

hover: callback *(Optional)*

: A function that will be invoked when the user hovers over the drop target. Argument is a boolean which when true means the user has
entered the region, and when false means they have exited.

drop: callback *(Optional)*

: A function that will be invoked when the user drops files on your drop target. Arguments to the callback vary depending on whether
'includeGestureInfo' was set true for the target. See the documentation for the 'includeGestureInfo' argument to 'AddDropTarget()'



##  BrowserPlus.DragAndDrop.EnableDropTarget({params}, function(){})

Enable/disable an element for drag/drop activity. AddDropTarget must have already been called for the element.

### Parameters

id: string
: The 'id' of the registered DOM element for which you wish to enable/disable drag/drop activity.

enable: boolean
: A boolean indicating whether activity should be enabled (true) or disabled (false).



##  BrowserPlus.DragAndDrop.ListTargets({params}, function(){})

Returns a list of the ids of the currently registered drop targets.

### Parameters

*No parameters.*



##  BrowserPlus.DragAndDrop.RemoveDropTarget({params}, function(){})

Stop monitoring an element for drag/drop activity.

### Parameters

id: string 
: The 'id' of the DOM element to operate on