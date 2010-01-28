# File Zipper

File Zipper allows you to select multiple files using @{service FileBrowse}, *zip* them together in a compressed archive with
@{service Zipper}, then uploads them with @{service Uploader}.

@{include examples/file_zipper.raw}

When invoking BrowserPlus.FileBrowse.OpenBrowseDialog(), note how **includeGestureInfo** is set to true on line
`26` and **actualSelection** is used in the call to BrowserPlus.Zipper.createZip() on line `44`. Setting
includeGestureInfo to true returns the actual files the user selected instead of recursing into folders and returning the
child files. If you didn't do this, the Zipper service would recurse itself into all folders and include duplicate files in
the resulting zip file.

