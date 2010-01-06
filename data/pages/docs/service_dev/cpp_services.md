# Developing Native Services for BrowserPlus

There is a lot that can be done in high level languages, but every once in a while we have a problem that requires native code. Reasons
to write native services in BrowserPlus range from performance requirements to API accessibility.

## Test

This is some really valuable info - right here.

## Design Notes on the C Service API

You may notice that the APIs we expose for services are very similar to NPAPI. At the core of the BrowserPlus project all we're really
doing is taking a subset of plugin APIs that can run cross browser (in ActiveX and NPAPI plugins). Because NPAPI is by far the most
successful multi-vendor plugin standard, we borrowed heavily from the design. We then augmented this subset a bit with features we
thought were important to scriptable web plugins, and tried to revise some of the semantics that we felt could be improved (for
instance, supporting calling back into the host from different threads). Hopefully this gives you a bit of background on the flavor of
the APIs, and with that you're ready jump in and check it out...

## Building a native service

The first step is to [download our SDK](SDK.html). The SDK includes code samples, full API documentation, C headers, and a tool that will help you
validate and install services you build. Once you have the SDK, we recommend you start with our 15 minute tutorial. While this tutorial
guides you through the building and installation of a Ruby service, the development flow and tools are similar for native services.
After getting through the tutorial, you'll be ready to build and install the sample native service in the SDK. Having a full example of
a working service to start with, you can get to hacking!

If you have questions that aren't covered in our documentation, or have any feedback, please come talk with us and the rest of the
community.