# C++ Services

This tutorial will show you how to create a BrowserPlus Service
in C++.


## Architecture Recap

<div style="float:right; margin-left:30px;margin-bottom:10px"><img src="/i/server_arch.png"></div>

BrowserPlus Services allow new capabilities to be provided to the web.  

As shown in the figure below, browser processes communicate with a BrowserPlus daemon process, which communicates
with BrowserPlus services. Each service is hosted in a "harness" process. The harness performs the Daemon
communication responsibilities, and hosting services in separate processes aids system robustness.

Services are physically packaged as dlls on Windows and dylibs on OSX.

## Service API

As shown in the figure, BrowserPlus services communicate with the BrowserPlus Daemon over the Service API. The
Service API is a C API. The headers for the Service API are on
[github](http://github.com/browserplus/platform/tree/master/src/sdk/service_api/api/ServiceAPI/). The API is
designed to permit discoverability of service methods and their parameters, among other things. See the [Service
API documentation](http://browserplus.github.com/bp-service-api/) for detailed info.


## The Browserplus Service Framework

C++ programmers may find the required Service API methods and types somewhat tedious to implement and interact
with. For this reason the Browserplus Service Framework was created.

The Browserplus Service Framework is a header-only framework that implements several classes, methods, and types to
ease the interaction with the rest of BrowserPlus over the Service API.

The remainder of this tutorial will use the service framework to illustrate implementing a service in C++.


## Step 1: Install BrowserPlus

If not already installed, you can install BrowserPlus through [Service Explorer](/explore/).

## Step 2: Install the BrowserPlus SDK

Download the [BrowserPlus SDK](http://browserplus.org/docs/service_dev/SDK.html) for your platform.

Once downloaded, unzip (or untar) the sdk, and a directory will be created called `bpsdk`.

## Step 3: Download the sample service

Download the sample service code from [here](http://github.com/browserplus/bp-tutorial-cpp1/archives/master).

## Step 4: Let's look at some code!

We're going to create a service called "HelloWorld". It implements one JS-callable method: "greet" that takes one
argument: "name". The service will assemble an appropriate greeting and return that to JS.

Below is the "service.cpp" file from the sample.  

You can see that the service derives from bplus::service::Service. Our method receives its arguments in a
bplus::Map. We use a Transaction object to send our results back asynchronously to JS. More on this later. The last
4 lines of the file are macrology that allows the Browserplus Daemon to introspect the methods and arguments of the
service. This introspection allows the service to document itself, and also allows BrowserPlus to peform runtime
error checking. The "1.0.0" specifies the version of the service, an important topic for which [more
information](/docs/web_dev/JavaScript_API_Overview.html#service_versioning) is available.

~~~
#include <sstream>
#include "bpservice/bpservice.h"

using namespace bplus::service;

class HelloWorld : public Service
{
public:    
    BP_SERVICE( HelloWorld );
    
    void greet( const Transaction& tran, const bplus::Map& args ) {
        std::stringstream ss;
        ss << "Hello, " << std::string(args["name"]) << "!";
        tran.complete( bplus::String( ss.str() ) );
    }
};

BP_SERVICE_DESC( HelloWorld, "HelloWorld", "1.0.0", "A simple Browserplus service" )
    ADD_BP_METHOD( HelloWorld, greet, "Generates a hearty greeting" )
      ADD_BP_METHOD_ARG( greet, "name", String, true, "name to greet" )
END_BP_SERVICE_DESC
~~~

## Step 5: Build the service

Build files are provided in the "src/build" directory for VS 2008 and gcc.
For VS08, just double-click the .sln file.  For osx, type "make" from the build directory.

## Step 6: Install the HelloWorld service

Service binaries must be findable at runtime so that they can be
loaded by the Browserplus daemon.  The daemon loads services that have been
installed locally.  Let's use the ServiceInstaller from the SDK to install our service.

Note: paths are specified below using windows backslashes.  Substitute forward slashes on osx.

Run the ServiceInstaller from the HelloWorld build directory as shown below.  You will
need to specify your specific path to the BrowserPlus SDK:

    build>[path to bpsdk]\bpsdk\bin\ServiceInstaller -v -f HelloWorld
    service initialized: HelloWorld v1.0.0
    installing service locally: HelloWorld, ver 1.0.0
    HelloWorld 1.0.0 validated and installed in 0.375s


## Step 7: Test the HelloWorld service from the console

The BrowserPlus SDK provides a tool called the ServiceRunner that allows you to 
test installed services from the console, without the added complexity of 
browsers and browser plugins.  Let's use ServiceRunner to test our service:

    build>[path to bpsdk]\bpsdk\bin\ServiceRunner HelloWorld 1.0.0
    service initialized: HelloWorld v1.0.0
    > desc

    Describing corelet 'HelloWorld', version: 1.0.0
    A simple Browserplus service

    1 function(s) supported:
    greet:
        Generates a hearty greeting

        string name
            name to greet

    > a
    allocated: 1
    > i greet '{"name":"World"}'
    "Hello, World!"
    > q
    shutting down...

Above you can see that the ServiceRunner "desc" command describes the methods and method arguments of the service.
The "a" command allocates a service "instance" (more on that later).  
The "i" command invokes a named method, with a JSON payload.

[maybe show an arch diagram]


## Step 8: Test the HelloWorld service from a web page

[Service Explorer](/explore/) is a JavaScript web app that dynamically creates a user interface allowing
you to test all of the BrowserPlus services without a single line of code.  It's the easiest way to
test your code.
