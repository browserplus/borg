# Including BrowserPlus

This is the simplest possible BrowserPlus website. 

@{include examples/including_browserplus.raw}

The script tag on line `05` includes the BrowserPlus javascript library
which populates the BrowserPlus namespace with a number of functions.

Once this small (and standalone) library is loaded, we can initialize the BrowserPlus plugin, and invoke a built-in function:
`BrowserPlus.getPlatformInfo()`. This function returns the current installed version of the BrowserPlus platform.

Now that you see using BrowserPlus basically involves just the inclusion of a JavaScript library which exposes an API, what
happens if BrowserPlus isn't installed?  Let's move on to Detecting BrowserPlus...