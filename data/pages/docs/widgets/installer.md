# Installer

This page is a demonstration of how to use the BrowserPlus Installer widget.

![BrowserPlus Installer Widget](/i/w/installer.jpg)

## 1. Include JavaScript

Include the base BrowserPlus library and the advanced installer JavaScript tool at the bottom of your webpage:

    <script src="http://bp.yahooapis.com/@{bpver}/browserplus-min.js"></script>  
    <script src="http://bp.yahooapis.com/toolbox/installer/@{installver}/install-min.js"></script>  

## 2. Choose an installation method

Automatically show the graphical dialog (above) when BrowserPlus is not installed (if BrowserPlus is required for
the page).

    // initArgs: passed into BrowserPlus.init, probably empty object {}   
    // initFunc: function(res){} normal BrowserPlus init callback  
  
    BPTool.Installer.show(initArgs, initFunc);  
  
    // OPTIONALLY, you can specify a third 'config' parameter, which   
    // tells the installer whether or not to use a Java Applet that   
    // can make installation a smoother experience.  
    //  
    // If not specified, the defaults are: {macJava: true, winJava: false}  


## 3. Render It

Render an upsell link if BrowserPlus is not installed. Call BrowserPlus.init(), rendering an upsell link on error
code bp.notInstalled.

    BrowserPlus.init({}, function (r) {  
        if (!r.success && r.error === 'bp.notInstalled') {  
            // render upsell link here  
            var lnk = document.getElementById("install_link");  
            lnk.onclick = function () {  
                BPTool.Installer.show({}, your_init_callback);  
            }  
        }  
    });  


## Installer in Action

* Automatically [display a dialog](/widgets/installer/popup) when the user has to have BrowserPlus 
  installed.
* Display a [display upsell link](/widgets/installer/upsell) when BrowserPlus is not required, 
  but used to enhance the site when it is installed.

Note If BrowserPlus is already installed, you won't see the installer in action.  BrowserPlus just starts
up in the background and your web app is ready to go.
