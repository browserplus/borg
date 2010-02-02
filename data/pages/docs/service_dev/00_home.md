# BrowserPlus for Service Developers

So you wanna extend the web, eh? Well you're in the right place! This section of our site has all the tools you'll need to build and
install BrowserPlus services that expose new APIs to browser-based JavaScript.

## Why build a BrowserPlus service?

Services allow you expose new functionality to javascript running in a wide range of popular web browsers, on multiple OS's. While it's already possible 
to do this today by authoring web browser Add-Ons (or Extensions) or by writing native plugins, the problem is that it's difficult
and costly to build something that runs in a lot of places, and features such as ease of installation, versioning, update, and security
add a lot more complexity to the project. Further it's clumsy or impossible to safely share tools that we build across multiple sites.

The BrowserPlus platform centrally solves many of these problems, and we hope that you'll agree, the result is that BrowserPlus
services:

1. Are simpler to write.
2. Are easier for the end-user to install.
3. Leverage robust platform-provided security mechanisms.
4. Are accesible to almost anything you can run in a browser, from JavaScript to Flash.
5. So if you have an idea of something that we can do today in a native application but not in all the popular browsers on the planet, and you think it could be safely exposed to a web browser environment, we hope you'll check out our tutorials and realize your idea!

## What Services are currently available?

For inspiration, check out the [currently-published services](/docs/services/).  This will give you an idea of the kinds of things that can be done, and how broad the opportunity really is.  You can browse the API of these services to see how their authors decomposed the problem and to see some of our programming idioms (e.g. callbacks, opaque file paths, etc.).

## Nope, my idea hasn't been implemented yet.

Great!  How fun for you.  Let's show you how to write a new service.  Currently we support writing services in C/C++ and Ruby.  Check out these links for more info:

* [C++ Tutorial](/docs/service_dev/cpp_tutorial.html)
* [Ruby Service Overview](/docs/service_dev/ruby_services.html)
* [Ruby Service Tutorial](/docs/service_dev/ruby_tutorial.html)

## How do I get my service published?

While the SDKs and documentation available here will allow you to get started building new services, you may not directly publish
services for end users to consume. The reason for this is that because services run with full user privileges on end user machines,
there are many potential pitfalls which could compromise end user safety.

The model to get a service published is simple today: come to our [forums](http://developer.yahoo.net/forum/index.php?showforum=90) and
talk to us. In the meantime, we continue to spend time thinking about a more formal and scalable model for helping you to get your
services published faster while still maintaining the integrity and security of the platform.