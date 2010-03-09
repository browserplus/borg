# Notifier

This page is a demonstration of how to use the BrowserPlus Desktop Notification widget. This tool gives you
the power to show native Desktop notifications if BrowserPlus is installed or plain HTML notifications if it is
not.

![BrowserPlus Notification Widget](/i/w/notify.jpg)

## 1. Include JavaScript

Include the base BrowserPlus library and the advanced notification JavaScript tool at the bottom of your webpage:

    <script src="http://bp.yahooapis.com/@{bpver}/browserplus-min.js"></script>  
    <script src="http://bp.yahooapis.com/toolbox/notify/@{notifyver}/notify-min.js"></script>  

## 2. Create Notify Object

Create a notify object. Now, whenever you want to notify the user, call show() to display a desktop notification
message (or a HTML message if BrowserPlus is not installed).

    var notify = BPTool.Notify.create();  

## 3. Show Message
    // [code] ... something interesting happens, notify the user:  
    notify.show("The Title", "The Message", "http://optional/icon.gif");  

## 4. Optionally Configure HTML Fallback

If BrowserPlus is not installed and your messages are shown in HTML, you can customize the look and feel of the
notification with any of the following optional parameters:

~~~~
var notify = BPTool.Notify.create( {  
    duration: "3", // duration in seconds  
    displayLocation: "center", // "center" or "topRight"  
    htmlFallback: true, // if BrowserPlus not installed, show HTML notifications  
  
    div: {  
        backgroundColor: "#222",  
        color: "white",  
        opacity: "0.90",  
        padding: "5px 20px",  
        fontFamily: '"Lucida Grande", Arial, Helvetica, Verdana, sans-serif',  
        textAlign: "center",  
        width: "200px",  
        boxShadow: "4px 6px 25px #333",  
        borderRadius: "20px"  
    },  
  
    img: {  
        margin: "12px"  
    },  
  
    h1: {  
        fontSize: "13px",  
        textAlign: "left"  
    },  
  
    p: {  
        fontSize: "11px",  
        textAlign: "left"  
    }  
});  
  
notify.show("The Title", "The Message", "http://optional/icon.gif");  
~~~~

## Notifications in Action

View [notification widget](/widgets/notify/standalone) demo.