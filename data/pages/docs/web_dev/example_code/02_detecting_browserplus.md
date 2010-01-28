# Detecting BrowserPlus

This code sample demonstrates how you can detect whether BrowserPlus is installed. 

@{include examples/detecting_browserplus.raw}

Inline comments describe the possible cases when you call BrowserPlus.init():

r.success === true
: BrowserPlus is installed and ready to use

r.error === 'bp.notInstalled'
: BrowserPlus is not installed and client platform is supported

r.error === 'bp.unsupportedClient'
: BrowserPlus is not installed and client platform is NOT supported

*otherwise*
: BrowserPlus encountered an error during initialization

The last two cases should be considered failure cases, and as a developer you should disable BrowserPlus functionality. In
the second case you can render a link pointing to <http://browserplus.yahoo.com/install>, and describe to your user what
features will become available to them if they install BrowserPlus. Further, if you would like to automatically detect when
BrowserPlus is installed without a page reload you can use the `BrowserPlus.initWhenAvailable()` platform function which will
set up a poller and once init() completes successfully, will invoke the callback you provide.

Using this handful of tools it should be possible to craft the precise experience you're looking for.