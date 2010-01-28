# Using Services

The BrowserPlus platform itself, without any services, offers
very little interesting new functionality.  Most of the new features
available to javascript are implemented in *dynamically loadable*
"services".  The flow of a typical BrowserPlus webpage involves:


* Checking if BrowserPlus is installed
* if no, redirecting or disabling advanced features
* if yes, specifying the services used by the site using `BrowserPlus.require()`
* doing cool stuff...

@{include examples/using_services.raw}

This sample is dedicated to step #3.  We begin with lines `18`
to `22`.  This is a javascript object which specifies services.
All services have a name and a version, and when authoring a page that
uses a BrowserPlus service you can specify as tightly as you like what
version range of that service you require.  In this case we specify
we require the `TextToSpeech` service (line `19`),
Major version 1 (line `20`), no less than version 1.0.2
(line `21`).  This specification is declared in a data structure
because it will be reused in different places in the code.  Henceforth,
a javascript object which specifies a service will be referred to as a
**service specification**.

Lines `26` to `36` define a utility function which can 
check for the presence of the service, and output the results of this
check into an HTML node.  This function is a wrapper around the platform
function `BrowserPlus.isServiceActivated` which allows javascript to
check for the presence of services without causing the user to be
prompted for installation.

Finally, lines `38` to `51` are the "main" function of this
page.  We initialize the platform on line `38`.  If successful, we
invoke the `checkForTestToSpeech()` to test if the service is
available before we invoke `require` on line `41`.  

`BrowserPlus.require()` is a very important function in the BrowserPlus
platform as it is the way you actually dynamically provision the end
user with new services, a process we call **service activation**.  The
most important side affect of this process is that `require` can 
take an arbitrary amount of time to complete.  If a service is not
present the end user will be prompted to approve it's activation.  For the
end user's sake, it's best to only prompt once.  The way this is achieved
is by bundling all of your service specifications into a single require
call, enabling the BrowserPlus platform to display a single prompt
including all updates or new services that will need to be installed.
To this end, observe on line `41` require accepts a parameter
named `services` that is an array of service specifications.

We've mentioned here that `require` may take some time to complete,
so you're probably wondering how we can give the end user feedback about
how long the process is going to take.  `BrowserPlus.require` takes
an optional `progressCallback` argument, which is a javascript
function that will be invoked by the platform with the current service
being installed, it's progress, as well as an estimate of the total
progress of the installation of all services.


Whew.  We've covered a lot in this sample.  The last bit of new material
we're introducing is the invocation of a function on a service once it's
activated and loaded (via `require`), line `45` actually
invokes TextToSpeech to say something to the end user.  As you'll see,
all BrowserPlus services expose functions which work in the same way.
All functions take an object containing named parameters as the first
argument, and a completion callback as the second.  That is, all
BrowserPlus services only export *asynchronous* functions, so that 
the web browser is able to continuously update the UI while work is
performed in the background.

By now you've learned the majority of the new knowledge you'll
need to build sites using the BrowserPlus platform, that is you understand
how to *specify* and *active* services, and you know how to
use them.  After learning how the platform returns
errors and return values, it's all a matter
of picking the services you want to use and building next generation
web apps.  Onward to [error handling](04_error_handling.html)!