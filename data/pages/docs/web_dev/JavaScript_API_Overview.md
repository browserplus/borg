# JavaScript API Overview


## Overview Of Operation

BrowserPlus is a browser plugin technology which is cross-platform and cross-browser. It allows services
to be attained and activated on the fly which expose new functions to JavaScript that provide scriptable APIs for
interacting with the client machine.

For a website to use BrowserPlus, the platform must be installed on the client machine. The platform provides APIs
to help handle the problem of initial distribution, making it possible for web developers to detect whether
BrowserPlus is installed and if not, whether it's supported on the client machine.

Web developers include a small JavaScript library in their pages which provides the high level BrowserPlus
JavaScript API. This library is included from a well known location:

    <script src="http://bp.yahooapis.com/@{bpver}/browserplus-min.js"></script>  

Once the JavaScript library is included, the web developer may call functions on it to test for the presence of
BrowserPlus, "activate" services on the fly (which perhaps will require an in-page installation), and to use the
functionality provided by the activated services.


## Levels of Support

BrowserPlus runs on many different browsers on a variety of platforms. With the advent of "site specific" browsers
and flexible HTML parsing and rendering libraries (such as webkit and gecko), there are an enormous number of
browser-like applications out there. The good news is that because these different implementations often use the
same frameworks, the BrowserPlus plugin will likely work in many of these different environments. That said, the
BrowserPlus team only tests a subset of the environments in which BrowserPlus runs.

To support experimentation, we have a tiered model for expressing the support level of client side technology (OS & browser). The
platform function [BrowserPlus.clientSystemInfo()](JavaScript_API_Reference.html#clientSystemInfo) encapsulates a lookup table with the
currently supported browsers and their support level. The following code will alert the level of support for the end-users browser:

    var csi = BrowserPlus.clientSystemInfo();  
    alert(csi.supportLevel);  

We have three support levels with different meanings:

* **supported** - These browser/OS combinations are supported and tested rigorously before each platform release.
* **experimental** - These browser/OS combinations are supported but have been minimally tested. 
  It's possible that not all features may function.
* **unsupported** - This is not a platform that's been tested by the BrowserPlus team. Your mileage may vary.

This checking is available whether or not the BrowserPlus plugin is loaded, and will function even if the platform
is not installed on the client machine.

The main idea is that we make information about support levels available to web developers, and leave the ultimate
decision of what levels of support to allow up to the site author.


## JS Library Versioning

As you'll notice above, the BrowserPlus platform version is included in the URL to attain the BrowserPlus
JavaScript library. In general, you may use any version of the library to work with the latest (major) version of
the platform. Within a major version, we do our best to preserve backwards compatibility. As we release new
platforms, it's very likely that new functionality will be added to the JavaScript library. The presence of new
functionality is indicated with the increment of minor version.

For a web developer, it's sufficient to use the latest available version, and to "upgrade" to the newest as part
of each significant site modification.

While we are aggressive about background downloading and updating of the platform on client machines, we realize that requiring all
sites using BrowserPlus to update in lock-step is impractical. For this reason we aim to minimize judicious API major version updates
while maintaining backwards compatibility through a major version. BrowserPlus.init() does checking for major version by default, but
this behavior may be altered by supplying optional arguments.


## Initializing BrowserPlus

BrowserPlus.init() will attempt to initialize the BrowserPlus platform. Init returning false can mean a
number of things. The most common reasons are the platform is not installed, or the site attempting to load the
platform is not allowed to do so. In general it is sufficient to consider four classes of errors:

* BrowserPlus is installed and ready (result.success === true)
* BrowserPlus is not installed (result.error === 'bp.notInstalled')
* BrowserPlus is not supported on the client browser/os (result.error === 'bp.unsupportedClient')
* An unexpected error occured, (such as the end user refused a request to activate services or grant permissions).
  You may inspect the error and verboseError members of the return value for detailed information, and should
  disable features of the site which require BrowserPlus


## Offering BrowserPlus for Installation

When BrowserPlus.init() returns an error value of `bp.notInstalled`, you may offer the end user a link to install
BrowserPlus. The official installation link is <http://browserplus.yahoo.com/install/>.

A user clicking on the link suggests they intend to install the platform. If you would like your page to receive a notifcation when
platform installation is complete, you may use the [BrowserPlus.initWhenAvailable()](JavaScript_API_Reference.html#initWhenAvailable)
function. This will install a polling loop which will return when the platform is detected and successfully initialized.

The core idea here is that you may dynamically render UI which uses BrowserPlus once it's installed, without
requiring your users to reload the webpage, much less restart their browser (warning: Google Chrome requires a
browser restart before it will properly detect the presence of BrowserPlus, this is a known issue).


<h2 id="service_versioning">Service Versioning</h2>

Every Service is versioned. Service versions consist of a major, minor, and micro number. A string representation
of version is these three numbers concatenated together with separating dots. So 1.0.0 or 2.32.781 are
possible version strings.

An important idea here is that Service versioning is more like shared library versioning than product versioning.
The end user never sees these version numbers. They are a tool to allow Service authors and web-page authors to
work together easily.

The meaning of these numbers is:

* **major** - A number that is incremented every time a API breaking change is made (removed a function, changed
  arguments to a function.
* **minor** - A number that is incremented every time a non-breaking API change is made (added optional parameter,
  added new function).
* **micro** - A bug fix or change was made that does not affect the service's API.

For service authors, the algorithm to determine what the new version of the Service should be, given recent
changes is thus:

1. Will this new version break existing pages? If so, bump major, reset minor and micro to zero.
2. If not, does this version provide new functionality in terms of new functions or optional parameters? If so,
   bump minor, reset micro to zero.
3. If not, bump micro.


## Requiring Services

When you're writing a web-page that uses BrowserPlus, you use the [BrowserPlus.require()](JavaScript_API_Reference.html#require)
function to express the service/versions that you wish to use. The require function accepts an array of *service specifications*, which
is nothing more than a JS object with up to three properties:

* **service** - The name of the service.
* **version** - [optional] A pattern representing the version of the service that you desire. This is a string
  representation which may specify only the major version (i.e. "2"), major and minor ("1.5"), or all three
  ("1.1.1"). For a service to match the version, everything specified in this string must be the same as the
  service version.
* **minversion** - [optional] A minimum allowable version which further restricts the set of services that will
  satisfy the requirement.

This specification structure allows us to handle a representative set of use cases:


### Use Case 1

Web-page author wishes to use the latest version of a service "FooService" that doesn't break the API, but needs
at least the functionality available in the 1.2 series.

~~~
var serviceSpecification = {  
   service:  "FooService",  
   version:  "1",  
   minversion: "1.2"  
};  
~~~ 

This little bit of code will keep the page on the latest available version of the service greater than 1.2.

### Use Case 2

Web-page author is distrustful of service author, and wants to ensure that all users are using an exact version of
the FooService service:

~~~
var serviceSpecification = {  
   service:  "FooService",  
   version:  "1.2.17"  
};  
~~~

This allows the web-page author to tightly control the service version they're running on top of. The author may
QA new service versions and manually bump the version number each time a new service that she's comfortable with
comes out. This usage is discouraged, as it is possible that a security hole could be found in the precise version
you're locked onto. If FooService 1.2.17 were to be blacklisted, the page would cease to function, failing with an
error emitted from BrowserPlus.require().

The philosophy is that service authors should always respect the above conventions, so that web-page authors can
simply specify service versions and allow automatic updates to be provisioned, which gets bug fixes and
performance improvements to end users automatically.


## Require and Installation delays

A BrowserPlus.require() statement may trigger the download and install of a new service. This process can take a
long time depending on the size of the service distribution package and the end user's connection speed. For this
reason, we recommend displaying something visually pleasing in the time between the invocation and the call-back.
A skeleton of the proposed code is:

~~~
function allServicesLoaded(results) {  
  if (!results.success) {  
    // oops!  services couldn't be loaded!  no version available for this  
    // platform?  no Internet connection right now?   
    ... code to disable feature ...  
  } else {  
    // sweet, the service is installed and loaded,  let's enable the  
    // feature that is supports  
    ... code to enable feature ...  
  }  
}   
  
function updateProgress(progress) {  
  ... display something useful ...      
}  
  
var requiredServices = [  
  { service: "TextToSpeech", version: "1" },  
  { service: "BitTorrent", version: "4" }    
];  
  
BrowserPlus.require({  
    services: requiredServices  
    progressCallback: updateProgress   
  },
  allServicesLoaded);  
~~~


## Automatic Service Update

There is no guarantee that the latest version of a service will be installed. Here's the scenario, assume that
FooService versions 1.1.0 and 1.1.1 are available. Further assume that the require code looks like this:

~~~
requiredServices = [{  
  service: "FooService",  
  version:  "1",  
  minversion:  "1.1"  
}];  
  
BrowserPlus.require(  
  {services: requiredServices},  
  function(x) { ..do thing ... }  
);  
~~~

Note that both service versions match this specification.. The logic in BrowserPlus is:

1. If a require statement is received for which there is a downloaded update that would best satisfy the statement,
   offer the user installation.
2. If there are no updates, and an installed service exists that satisfies the service/version/minversion
   specification - use it.
3. If no installed services satisfy the require statement, attempt to attain a service from the distribution
   server, prompting the user if found, failing if not.

Back to our previous example, If FooService 1.1.0 is installed on an end user machine, and a page runs this
require statement, the distribution server will not be queried for the new service version.

**NOTE**: A webpage author may force upgrades by using the minversion parameter in their service specifications.


## Asynchronous Function Invocation

All invocation of funtions on services occurs asynchronously in the platform. All invocation of functions takes an
argument object as the first parameter, and a callback function as the second. Here's a typical call:

~~~
BrowserPlus.PStore.get(  
  {key: "foo"},  
  function(x) { ... do something with returned value in x.value ... }  
);  
~~~

The argument object supplies named arguments, in this case a key, and the second argument provides a function to
invoke when the call is complete.


## Service Function Return Values

Just as all functions on Services are invoked in the same way, they return data and errors in the same way. We've
focused one of our [code samples](http://browserplus.yahoo.com/developer/web/code/error_handling/) on this
pattern, and encourage you to find full documentation and discussion there.

## File Handles

"Opaque File Handles" are a key means of representing user file selections as well as passing information from
service to service when using the BrowserPlus platform. The key ideas of file handles are:

* A webpage may only interact with files that the user has selected (i.e. via FileBrowse or DragAndDrop)
* Selected files have a lifetime scoped to the page within which a user selects a file.
* Files are represented in javascript as objects which contain only "safe" information about the file, including
  size, mime-type, and file name (but not a full path).
* Full paths are kept in the platform, out of reach of javascript in the webpage.
* Certain services which modify files may return new file handles which reference temporary files with a lifetime
  scoped to the BrowserPlus session (aka webpage lifetime).

A JavaScript file handle representation contains the following elements:

* **name**: the name of the file
* **size**: the size of the file in bytes
* **mimeType**: An array of one or more mimetypes guessed based on the file's extension.
* **BrowserPlusHandleID**: a randomly integer which is the file handle's ID.

~~~
{
    "BrowserPlusType": "BPTPath",
    "BrowserPlusHandleID": 16807,
    "name": "bizplan.xls",
    "size": 118784,
    "mimeType": [
      "application/excel",
      "application/vnd.ms-excel",
      "application/x-excel",
      "application/x-msexcel"
    ]
}
~~~