# C++ Services

Write a BrowserPlus Service in C++.

## Architecture

BrowserPlus Services allow new capabilities to be provided to the
web.  

As shown in the Architecture figure, browser processes communicate
with a BrowserPlus daemon process, which communicates with BrowserPlus
services, each of which is hosted in a harness process.

![Server Architecture](/i/server_arch.png)

The services are packaged as dlls on Windows and dylibs on OSX.

This tutorial will show you how to create a BrowserPlus Service
in C++.


## Service API

As shown in the figure, BrowserPlus services implement the Service
API.  This is a C API that is declared [here].  The API is designed
to permit introspection of service methods and their parameters.

C++ programmers may find the required Service API methods and
types somewhat tedious to implement and interact with.  For this
reason the Browserplus Service Framework was created.


## Browserplus Service Framework

The Browserplus Service Framework is a header-only framework that
implements several classes, methods, and types to ease the interaction
with the rest of BrowserPlus over the Service API.

The remainder of this tutorial will use the service framework to
illustrate implementing a service in C++.


## Build a sample service using the Service Framework

Let's build a sample service, the HelloWorld service. 

### Step 1: Install BrowserPlus

[installer tool]

### Step 2: Install the BrowserPlus SDK (show version like b.y.c/tutorial does)

http://browserplus.yahoo.com/developer/service/sdk/ [proper version]

### Step 3: Download the sample service

You may download the sample code from [here].  

### Step 4: Build the service

Build the service for your platform using the instructions in the
downloaded file "building".

### Step 5: Install the HelloWorld service

Service binaries must be findable at runtime so that they can be
loaded by the Browserplus daemon.  The daemon loads services that are
installed locally.  If necessary it will fetch service binaries from
BrowserPlus distribution servers.  For this demo we will just install the
sample service on the local machine.

Run the ServiceInstaller from the build directory as shown below.  You will
need to specify your specific path to the BrowserPlus SDK:

    build> [path to ServiceInstaller]/ServiceInstaller -v -f HelloWorld
    
    C:\dev\bp-tutorial-hello\src\build>..\..\..\bpsdk\bin\ServiceInstaller -v -f HelloWorld
    service initialized: HelloWorld v1.0.0
    installing service locally: HelloWorld, ver 1.0.0
    HelloWorld 1.0.0 validated and installed in 0.375s


## Test the HelloWorld service from the console

    [path to ServiceRunner]/ServiceRunner HelloWorld 1.0.0
    > desc
    > a
    > i greet '{"name":"World"}'
    > q
    [show entire session]
    
    C:\dev\bp-tutorial-hello\src\build>..\..\..\bpsdk\bin\ServiceInstaller -v -f HelloWorld
    service initialized: HelloWorld v1.0.0
    installing service locally: HelloWorld, ver 1.0.0
    HelloWorld 1.0.0 validated and installed in 0.375s
    
    C:\dev\bp-tutorial-hello\src\build>..\..\..\bpsdk\bin\ServiceRunner HelloWorld 1.0.0
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


[maybe show an arch diagram]


## Test the HelloWorld service from a web page

We provide a web-based tool called the Service Explorer.  It has two modes.
In the "List" mode it will show all services available on our
distribution servers.  In "Test" mode it will show all services
currently installed on your local machine and allow you to exercise
them.

1. Launch [Service Explore](http://browserplus.yahoo.com/developer/explore)
2. Click "Test Services"
3. Open the HelloWorld 1.0.0 dropdown
4. Select the greet method
5. Fill in the "name" box and Execute the method

Note you can also use this tool to view the documentation for methods on services.
This is done via metadata provided by the service author.

 ![Hello world](/i/explorer_hello_world.png)


