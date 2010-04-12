# A BrowserPlus Service in 15 minutes.

This page contains all necessary information to build, install, and use a BrowserPlus service written in ruby. While the tutorial
demonstrates how to build a Ruby service, the process is similar for native services, so it serves as a good starting point for any
Service Developer.

The tutorial requires you have the BrowserPlus platform installed, and the RubyInterpreter service, however you'll be walked through the
process of installing these dependencies in page. If this takes longer than 15 minutes, we've done something wrong! So let's get
started...

## Step 1: Install BrowserPlus

<div id="gotbp">Checking for BrowserPlus...</div>
<div id="downloadLink"></div>

## Step 2: Installation of the RubyInterpreter service

We will automatically grab the correct interpreter service for you...

<div id="gotruby">Checking ...</div>

## Step 3: Download and unpack BrowserPlus SDK

Download and unpack the latest SDK appropriate for your platform: <http://github.com/browserplus/platform/downloads/>.

Once downloaded, you should unzip (or untar) the sdk, and a directory will be created called `bpsdk`.

## Step 4: Create a ruby service

A Ruby service is just a directory with a couple files in it:

manifest.json
: the "service manifest" which contains some metadata about the service.

somefile.rb
: the ruby file that implements the service

So, first, create a directory which is a peer to the the SDK. I'm going to call my new directory `myGreatRubyService`.

Now let's write a manifest, copy this text into a file named "manifest.json" in the directory you just created:

~~~
{  
  "type": "dependent",  
  "uses": {  
    "corelet": "RubyInterpreter",  
    "version": "4",  
    "minversion": "@{rubyver}"  
  },  
  "arguments": {  
    "ScriptFile": "myService.rb"  
  },  
  "strings": {  
    "en": {  
      "title": "My Great Service",   
      "summary": "A sample service to show you how to build your own."  
    }  
  }  
}  
~~~

Now let's write the service, copy this text into a file named myService.rb in the directory you just created:

~~~
class MyGreatServiceInstance  
  def initialize(context)  
  end  
  
  def HelloWorld(transaction, args)  
    transaction.complete("Hello #{args['who']} from my great service!")  
  end  
end  
  
rubyCoreletDefinition = {  
  'class' => "MyGreatServiceInstance",  
  'name' => "MyGreatService",  
  'major_version' => 0,  
  'minor_version' => 0,  
  'micro_version' => 1,  
  'documentation' => 'A GREAT service.',  
  'functions' =>  
  [  
    {  
      'name' => 'HelloWorld',  
      'documentation' => "Say \"hello\" to the world",  
      'arguments' => [  
        {  
          'name' => 'who',  
          'type' => 'string',  
          'documentation' => 'who you want to say hello to',  
          'required' => true  
        }  
      ]  
    }    
  ]   
}  
~~~

You're done! You've just written a browser plugin in ruby!


## Step 5: Verify the syntax of your service

Now that you've written your service, you'll want to use the "ServiceInstaller" program in our SDK to verify that the syntax is
correct. Assume that you've put your service in a directory called myGreatRubyService right next to the unpacked SDK. Open a terminal
window (or command prompt) and change into the myGreatRubyService directory, then type:

~~~
$ ../bpsdk/bin/ServiceInstaller -n -v .
Dependent service using: 'RubyInterpreter', version @{rubyver}
MyGreatService 0.0.1 validated in 0.179s
~~~

**Note**: on windows you'll replace '/' with '\'.

`ServiceInstaller -h` will give you help on the usage of this program.

## Step 6: Publish your service to your local machine

"Local publishing" really means copying the service into a user scoped service directory. The "ServiceInstaller" binary will do this
for you when you omit the -n flag. So, again assume that you've put your service in a directory called myGreatRubyService right next to
the unpacked SDK:

~~~
$ ../bpsdk/bin/ServiceInstaller -v .
Dependent service using: 'RubyInterpreter', version @{rubyver}
installing service locally: MyGreatService, ver 0.0.1
MyGreatService 0.0.1 validated and installed in 0.259s
~~~

**Note**: on windows you'll replace '/' with '\'

You did it! You've installed your new service, let's try it out!

## Step 7: Make sure BrowserPlus knows about your service

BrowserPlus will not rescan services while running for performance reasons, so services that are installed using ServiceInstaller will
not neccesarily be detected. We can quickly solve this problem by using the BrowserPlus configuration panel. Find it in Start -
Programs - Yahoo! BrowserPlus on Windows, or in your System Preferences on OSX.

Once you've found it, navigate to the 'Troubleshooting' pane and click on "Restart BrowserPlus"

![troubleshooting tab](http://browserplus.yahoo.com/developer/service/tutorial/troubleshooting.jpg)

Once that's done, you should be able to hop over to the 'services' pane and see your great new service.

![service tab](http://browserplus.yahoo.com/developer/service/tutorial/greatservice.jpg)

**Note**: As of BrowserPlus 2.1.12 and greater, there's a new "Developer Mode" feature that lets BrowserPlus instantly discover new
locally published services. To enable, edit BrowserPlusCore.config (on windows in `C:\Program Files\Yahoo! BrowserPlus\@{bpver}\` or on mac
in `~/Library/Application Support/Yahoo!/BrowserPlus/@{bpver}`), and change "DeveloperMode" to true.



## Step 8: Test your service API online

You should now be able to see your service in the Service Explorer by clicking on the 'activated' tab:

![service explorer](http://browserplus.yahoo.com/developer/service/tutorial/explorer.jpg)

This tool will let you excercise the functions of your service without writing another line of code.



## Step 9: write a webpage that uses your service


    <html>  
    <head>
      <title>My Great Service</title></head>  
      <script src="http://bp.yahooapis.com/@{bpver}/browserplus-min.js"></script>  
    <body>
      <p>testing...</p>
    </body>
    <script>  
    BrowserPlus.init(  
      function(installed)   
      {  
         if (installed.success)   
         {  
           BrowserPlus.require({services: [{"service": "MyGreatService"}]},
             function() {  
               BrowserPlus.MyGreatService.HelloWorld({ who:"you" },  
                 function(rez) {
                   alert(rez.value);
               });  
           });
         }  
      });  
    </script>  
    </html>  


Copy this text into an html file on disk, open it in your favorite supported web browser, and check out your great service in action!
You've done it! You've extended the web in about 15 minutes. Neat, eh? Now what are you gonna build?

<script src="http://bp.yahooapis.com/@{bpver}/browserplus-min.js"></script>  
<script>
localPageCB = function () {
  var rubyServiceDesc = {  
    service: "RubyInterpreter",  
    version: "4",  
    minversion: "@{rubyver}"
  };

  function rubyProgressCB(v) {
    var gotRubyDiv = document.getElementById("gotruby");
	var txt = "Installing RubyInterpreter service: ";
	txt += v.totalPercentage + "% complete..";
	gotRubyDiv.innerHTML = txt;
  }

  function rubyCheckCB(v) {
    var gotRubyDiv = document.getElementById("gotruby");
	var txt = "";
	if (v.success) {
	  txt += "You're good to go - " + v.value[0].service + " v. "
		+ v.value[0].version + " installed.";
	} else {
	  txt += "Uh oh, couldn't install RubyInterpreter - <pre>" + v.error + ": " +
		v.verboseError + "</pre> -- reload this page to try again";
	}
	gotRubyDiv.innerHTML = txt;
  }

  function myInitCB(r) {
	var BP = BrowserPlus;
    var instDiv = document.getElementById("gotbp");
	if (r.success)
	{
      instDiv.innerHTML = "BrowserPlus installed!  Ver. " +
		BP.getPlatformInfo().version;
	  
	  // now let's install ruby if needed
	  // XXX: it would be nice to not have to prompt until the user
	  //      clicks on a "get ruby" link.
	  BrowserPlus.require(
		{
		  services: [ rubyServiceDesc ],
		  progressCallback: rubyProgressCB
		}, rubyCheckCB);
	}
	else if (r.error === 'bp.notInstalled')
	{
      // render an upsell link for inpage installation
	  while (instDiv.firstChild) instDiv.removeChild(instDiv.firstChild);
	  var lnk = document.createElement("a");
      lnk.onclick = function () {
        BPTool.Installer.show({}, myInitCB);
      }         
	  lnk.innerHTML = "install BrowserPlus now";
	  lnk.href= "#";
      instDiv.appendChild(lnk);
    }
	else if (r.error === 'bp.notInstalled')
	{
      instDiv.innerHTML = "Sorry, your platform isn't yet supported, please " +
		"try again on a <a href='/install'>supported platform</a>."; 
	}
	else
	{
      instDiv.innerHTML =
		"Yikes, BrowserPlus encountered an error (" + r.error + ": " +
		r.verboseError+"), please try restarting your browser, or visit " +
		"the Troubleshooting page of the BrowserPlus Configuration panel for "
		+ "more help in figuring out what went wrong.";
	}
  }

  BrowserPlus.init({}, myInitCB);
};

if (window.attachEvent) {
  window.attachEvent("onload", function(){localPageCB()});
} else {
  localPageCB();
}

</script>