The BrowserPlus @{service DragAndDrop} (v2.0) service allows a web developer to make any HTML node a target for
native drops of files. For 2.0, only the actual files dropped are passed on through the DragAndDrop interface. To
recurse into folders, use the @{service Directory} service.

The general usage of DragAndDrop is thus:

1. Developer `requires()` the DragAndDrop built-in service
2. `DragAndDrop.AddDropTarget()` is used to make a HTML entity an active dropzone.
3. `DragAndDrop.AttachCallbacks()` indicates which functions should be called when the user hovers over the drop area, and what should be called when the user drops on the zone.
4. When the end user hovers or drops, the callbacks set are invoked.

@{include examples/drag_and_drop_v2.raw}

DragAndDrop gives javascript a means of attaining opaque FileHandles, which can then be passed to other services,
such as @{service ImageAlter}. A "FileHandle" is simply a javascript object which contains an opaque numeric
identifier of the selected file, and a "display name" in the `BrowserPlusHandleName` property. The latter is simply
the filename with path information removed, which makes it meaningful for display to the end user.
