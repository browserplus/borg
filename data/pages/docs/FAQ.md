# Frequently Asked Questions

1. [How do I get help with BrowserPlus?](#support)
1. [How do I diagnose installer problems?](#diagnoseInstallerProblem)
1. [Where are the files installed?](#installationLocation)
1. [How do I uninstall BrowserPlus?](#uninstall)
1. [How do I use BrowserPlus on my site only if it's already installed?](#initProgressively)
1. [How do I find BrowserPlus logs?](#findLogfiles)
1. [How do I require a specific BrowserPlus platform version?](#specificPlatform)
1. [How do I send a bug report?](#bugReport)
1. [How do I get BrowserPlus to display an installation dialog if not already installed?](#installDialog)
1. [How do I call a specific version of a service if more than one is loaded?](#specificVersion)



<a name="support"></a>
## How do I get help with BrowserPlus?

If you are a BrowserPlus **user** and need help, please head over to [our forums](http://developer.yahoo.net/forum/index.php?s=887c0d15830324ed1fd45a1180d3dcf6&showforum=83).

If you are a BrowserPlus **developer** and need help, checkout out the [developer documentation](/docs/) and join us on IRC at 
`irc.freenode.net #browserplus`.



<a name="diagnoseInstallerProblem"></a>
## How do I diagnose installer problems?

If BrowserPlus installation problems occur, your best friend is the installer log.  

On OSX 10.4, 10.5, and 10.6 you can find the logfile here:

    ~/Library/Caches/TemporaryItems/BrowserPlusInstaller.log

On Windows XP you can find the logfile here:

    C:\Documents and Settings\<user>\Local Settings\Temp\BrowserPlusInstaller.log

Finally on Windows Vista and Windows 7, the logfile can be found here:

    C:\Users\<user>\AppData\Local\Temp\BrowserPlusInstaller.log

If you're so inclined, take a look at the log. You may be able to diagnose the problem yourself. In any case, we'd love to see that
logfile so that we can fix the problem. Please attach it to an email to <browserplus-feedback@yahoo-inc.com>. Please use a descriptive
Subject line (like "YIKES, Installer failure!").




<a name="installationLocation"></a>
## Where are the Files Installed?

BrowserPlus follows the *Principle of Least Privilege*, meaning it never has to ask for administrative privileges.
And that motivates the installation locations.  The location depends upon your operating system:

On **OS X**, files are installed to:

    ~/Library/Application Support/Yahoo!/BrowserPlus/
    ~/Library/Internet Plug-Ins/BrowserPlus_<version>.plugin
    ~/Library/PreferencePanes/BrowserPlusPrefs.prefPane

On **Windows 7** and **Vista**, files are installed to:

    C:\Users\<username>\AppData\Local\Yahoo!\BrowserPlus
    C:\Users\<username>\AppData\LocalLow\Yahoo!\BrowserPlus

And on **Windows XP**, files are installed to:

	C:\Documents and Settings\<username>\Local Settings\Application Data\Yahoo!\BrowserPlus

Note that all Windows installations write to the Registry as well.

For precise details, the source code is [open and online](http://github.com/browserplus/platform/tree/master/src/libs/installer/lib).




<a name="uninstall"></a>
## How do I uninstall BrowserPlus?

The BrowserPlus unininstaller can be reached via the Windows Control Panel ("Add/Remove Programs" or "Programs and Features", depending on your version of Windows). 

On OSX, the uninstaller can be reached via a preference pane in "System Preferences". 

If for some reason the uninstall doesn't work for you, just manually remove the following directory:

    \Users\<yourname>\AppData\Local\Yahoo!\BrowserPlus (Vista, W7)
    \Documents and Settings\<yourname>\Local Settings\Application Data\Yahoo!\BrowserPlus (XP)
    /Users/<yourname>/Library/Application Support/Yahoo!/BrowserPlus (OSX)




<a name="initProgressively"></a>
## How do I use BrowserPlus on my site only if it's already installed?

You can progressively enhance a web page to only use BrowserPlus if it is installed with the built-in init method.

    <script src="http://bp.yahooapis.com/@{bpver}/browserplus-min.js"></script>  
    <script>
    BrowserPlus.init({}, function(result){
        if (result.success) {
            // use BrowserPlus
        } else {
            // fallback to original page
        }
    }); 
    </script>



<a name="findLogfiles"></a>
## How do I find BrowserPlus logs?

BrowserPlus has a logfile that captures information while it's
running.  Including this logfile in problem reports can help the
reader to diagnose many issues.  The logfile lives in a _user scoped_
location on your disk, and that location is platform dependent.

On OSX 10.4, 10.5, and 10.6 you can find the logfile here:

    ~/Library/Application Support/Yahoo!/BrowserPlus/<version>/<guid>/*.log

On Windows XP you can find the logs in:

    C:\Documents and Settings\<user>\Local Settings\Application Data\Yahoo!\BrowserPlus\<version>\<guid>\*.log

Finally on Windows Vista and Windows 7, your logfiles can be found here:

    C:\Users\<user>\AppData\LocalLow\Yahoo!\BrowserPlus\<version>\<guid>\*.log



<a name="specificPlatform"></a>
## How do I require a specific BrowserPlus platform version?

Currently we don't have any built in mechanisms to require a certain platform version, nor force an update.

But you can do it at the page level with [getPlatformInfo()](http://browserplus.yahoo.com/developer/web/api/#bpgetplatforminfo).

This will let you get the platform version, and appropriately message the end user, or refuse to run the page.

Alternatively, you should see that your regular users are updated within 48 hours if they use your service at least once a day, I'll
dig up and repost the precise update logic in browserplus to give you a concrete idea how it works


<a name="bugReport"></a>
## How do I send a bug report?

BrowserPlus has a built in mechanism for sending bug reports.  This
allows you to share a small amount of anonymous information that
can help us diagnose and quickly fix issues with the software.  Sending
a report is easy:

1. reproduce the issue inside your web browser
2. STOP
3. open up the BrowserPlus control panel.  
  a. On windows this is `Start -> Programs -> BrowserPlus -> Configuration`
  b. On OSX this is found in `Start -> Programs -> BrowserPlus -> Configuration`
4. Click on the "Troubleshooting" tab
5. Include some descriptive text about what site you were visiting and what you did when the problem happened.
6. click send!

Subsequent to sending your report you can drop us a note [on twitter](http://twitter.com/browserplus) to follow up.

Finally, please make sure you supply any relevant links in your bug report, as this will help us get to the bottom of things.

The trouble shooting tab looks like this:

  ![troubleshooting](/i/troubleshooting.jpg)



<a name="installDialog"></a>
## How do I get BrowserPlus to display an installation dialog if it is not already installed?

If users have to have BrowserPlus installed to use a webpage, use the [BrowserPlus
Installer](http://browserplus.yahoo.com/developer/web/toolbox/installer/) to present 
the user with a graphical dialog that guides through installation. If BrowserPlus is 
already installed, then no dialog is presented.

    <script src="http://bp.yahooapis.com/@{bpver}/browserplus-min.js"></script>  
    <script src="http://bp.yahooapis.com/toolbox/installer/@{installver}/install-min.js"></script>  
    <script>
    BPTool.Installer.show({}, function(result){
        if (result.success) {
            // page is ready, require services and go
        }
    });
    </script>


<a name="specificVersion"></a>
## How do I call a specific version of a service if more than one is loaded?

In the rare case that you have more than 1 version of a specific service loaded, you can specify the version
to call in the following way:

	BrowserPlus.DragAndDrop["1.0.1"].ListTargets({}, function(){})
	BrowserPlus.DragAndDrop["2.0.0"].ListTargets({}, function(){})
    