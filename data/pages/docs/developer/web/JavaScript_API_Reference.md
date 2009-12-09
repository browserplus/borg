# JavaScript API Reference

<div markdown="1" class="api">

## <a name="clientSystemInfo"></a>Object BrowserPlus.clientSystemInfo()

JavaScript only determination of platform and support level. 

### Returns 
Returns an object containing the following properties:

os (String)
: Linux, Mac, or Windows.

browser (String)
: Firefox, Explorer, or Safari.

version (String)
: Browser version if found. locale (String) - End user's locale (i.e. en-US).

supportLevel (String)
: Either "supported", "experimental", or "unsupported".

### Example 

~~~
var sl = BrowserPlus.clientSystemInfo()
alert(sl.supportLevel);  
~~~



## <a name="init"></a> void BrowserPlus.init( [obj], fn )

Attempts to initialize the BrowserPlus platform.

### Parameters

obj (Object)
: An optional object containing input arguments. The following attributes are supported:

locale (String)
: The locale of the client, which specifies the language that end user facing strings should returned in.

supportLevel (String)
: One of "supported", "experimental" (default), or "all". This controls upon what classes of technology we will
attempt to initialize the BrowserPlus plugin.

fn (Function)
: A callback function that will be invoked once initialization is complete. The function will be passed an object
with the following attributes:

### Returns

success (Boolean)
: The status of the operation. 

[error (String)]
: When success is false , error holds a textual error code. 

[verboseError (String)]
: When success is false , verboseError is optionally present which contains an English string describing what went wrong.

### Example

~~~
BrowserPlus.init({locale: 'en_US'},  
  function(x) {  
    alert("BrowserPlus is " + (x.success ? "" : "NOT") + "installed!");  
});  
~~~



## <a name="initWhenAvailable"></a> void BrowserPlus.initWhenAvailable( obj, fn )

Installs a polling loop to initialize the platform once it's installed.

### Parameters

obj (Object)
: A required object that will be passed to BrowserPlus.init, see documentation above for supported arguments.

fn (Function)
: a callback function that will be invoked once initialization completes successfully .

### Example

~~~
BrowserPlus.initWhenAvailable({},
  function() {  
    alert("BrowserPlus is ready!");  
});  
~~~



## <a name="isInitialized"></a> void BrowserPlus.isInitialized()

Test whether the platform is initialized.

### Returns

A Boolean, true if init() has successfully completed, false otherwise.

### Example

~~~
alert("BrowserPlus is " + (BrowserPlus.isInitialized() ? "" : "NOT") +  " initialized.");  
~~~



## <a name="require"></a> void BrowserPlus.require( obj, fn )

Activate and load a service. If a service is not active, the user will be prompted, and the service installed.

### Parameters

obj (Object) 
: An object containing input arguments. The following attributes are supported:  
**services** (String)  
**progressCallback** (Function)

fn (Function)
: a callback function that will be invoked once activation & loading is complete. The function will be passed an object with the
following attributes:  
**success** (Boolean) - The status of the operation.  
[ **error** (String) ] - When success is false , error holds a textual error code.  
[ **verboseError** (String) ] - When success is false , verboseError is optionally present which contains an English string describing what went wrong.  
[ **value** (String) ] - When success is true , value contains an array of objects. Each object has a 'service' and 'version' attribute which indicate the exact services that were loaded as a result of the require statement.

### Example

~~~
BrowserPlus.require({  
    services: [{name: "ServiceName", version: "1", minversion: "1.1.2"}]  
  },  
  function (x) {  
    if (x.success) alert("ServiceName successfully loaded!");  
  }  
~~~



## <a href="isServiceLoaded"></a> boolean BrowserPlus.isServiceLoaded(name, [version])

Tests whether a service has been loaded using BrowserPlus.require( ).

### Parameters

name (String)
: the name of the service to test for.

version (String)
: the exact version of the service to test for.

### Returns

A Boolean which indicates whether the service has been loaded.

### Example

~~~
alert("TextToSpeech is " +  
  (BrowserPlus.isServiceLoaded("TextToSpeech") ? "" : "NOT ") + "loaded.");  
~~~


## <a name="getPlatformInfo"></a>String BrowserPlus.getPlatformInfo();

Returns the version of the currently installed BrowserPlus platform. Will throw if BrowserPlus.init() has not completed successfully.

### Returns

A map containing information about the BrowserPlus platform. The following attributes are present

version (String)
: The version of the BrowserPlus platform.

os (String)
: The BrowserPlus platform's identification of the operating system, at present either 'win32' or 'osx'.

buildType (String) 
: The type of build installed on the client machine. Currently is one of 'devel', 'internal', or 'production'. Build types will differ
in which distribution servers they use to consume services, as well as what code signing certifications they honor (available in
platform 2.4.13 and above).

### Example

~~~
BrowserPlus.init({locale: 'en_US'},  function(x) {  
  if (x.success) alert(BrowserPlus.getPlatformInfo().version);  
});  
~~~


## <a name="getPlatformInfo"></a> void BrowserPlus.listActiveServices( fn )

List all of the active (or installed) BrowserPlus services.

### Parameters

**fn (Function)**
: a callback function that will be invoked with the results of the function call. The function will be passed an object with the
following attributes:  
**success (Boolean)** - The status of the operation.  
**[error (String>)]** - When success is false , error holds a textual error code.  
**[verboseError (String)]** - When success is false , verboseError is optionally present which contains an English string describing what went wrong.  
**[value (Object)]** - When success is true, Value will contain an array of Objects, described below.

The **value** object in the array above consists of the following parameters:

name (String)
: the service name

version (String)
: the service version number

type (String)
: the service type which is one of four values:  
**"built-in"** - The service is part of the platform, no end-user installation is required.  
**"standalone"** - A typical BrowserPlus Service.  
**"provider"** - A service which can be used by other services but may still expose a JS API.  
**"dependent"** - A service which depends on others to function, but aside from this behaves as a typical BrowserPlus service.

### Example

~~~
BrowserPlus.listActiveServices(function(x) {  
  if (x.success) {  
    var resultStr = "These services are installed:\n";  
    for (i in x.value) {  
      resultStr += x.value[i].name + " v" + x.value[i].version;  
    }  
    alert(resultStr);  
  }  
});  
~~~



## <a name="describeService"></a> void BrowserPlus.describeService( obj, fn )

Get a JavaScript data structure describing the interface of a service.

### Parameters

obj (Object)
: An object containing input arguments. The following attributes are supported:  
**service (String)** - The name of the service  
**version (String)** - A full or partial version for the service  
**minversion (String)** - A "minimum version" specification. Only services which match or exceed this specification will be returned.

fn (Function)
: A callback function that will be invoked with the results of the function call. The function will be passed an object with the 
following form:  
**success (Boolean)** - The status of the operation.  
**[error (String)]** - When success is false , error holds a textual error code.  
**[verboseError (String)]** - When success is false , verboseError is optionally present which contains an English string 
describing what went wrong.  
**[value (Object)]** - When success is true , Value will contain a data structure in the form of the object below:

~~~
{  
    "name": "NameOfTheService",  
    "version": {  
      "major": 1,  
      "minor": 2,  
      "micro": 1  
    },  
    "versionString": "1.2.1"  
    "documentation": "A String documenting what the service does",  
    "functions": [  
      {  
        "documentation": "A String documenting what the Function does.",  
        "name": "TheNameOfTheFunction",  
        "parameters": [  
          {  
            "documentation": "What the parameter does",  
            "name": "allParametersAreNamed",  
            "required": true,  
            "type": "path"  
          }  
        ]  
      }  
    ]  
  }  
}
~~~

### Example

~~~
BrowserPlus.describeService({  
    service: "TextToSpeech",  
    version: "1",  
    minversion "1.2.4"  
  },  
  function(x) {  
    if (x.success) {  
      alert("Got a description for " + x.value.name + " v" + x.value.versionString);
    }
});
~~~



## <a name="isServiceActivated"></a> void BrowserPlus.isServiceActivated( obj, fn )

Test if a Service is installed on the local machine and ready for use.

### Parameters

obj (Object)
: An object containing input arguments. The following attributes are supported:  
**service (String)** - The name of the service  
**version (String)** - A full or partial version for the service  
**minversion (String)** - A "minimum version" specification. Only services which match or exceed this specification will be returned.

fn (Function)
: A callback function that will be invoked with the results of the function call. The function will be passed a Boolean, true 
if a service is active, false otherwise.

### Example

~~~
BrowserPlus.isServiceActivated({  
    service: "TextToSpeech",  
    version: "1",  
    minversion "1.2.4"  
  },  
  function(haveIt) {  
    alert("TextToSpeech is" + (haveIt ? "" : "n't") + " activated.");  
  });  
~~~

</div>