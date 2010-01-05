# Portable Scriptable Plugins for the Browser

BrowserPlus itself is a proposal for a new type of web plugin ("service")  The
service API has the following high level features:

+ **Binary Support** - Similar to ActiveX or NPAPI, this service API makes it
  easy to implement services in native code that are scriptable from JavaScript.
+ **High Level Language Support** - The proposal includes (minimal) facilities
  to make it possible to support interpreted services in any language.
+ **Cross Browser** - A service bundle is able to support all browsers
  with a single implementation.
+ **Cross Platform** - To the extent possible, the task of creating services that
  run on different architectures and platforms is simplified.
+ **Versioning** - Versioning of services is required, and the specification suggests
  that service authors apply robust ["Semantic Versioning"](http://semver.org/).
  Webpage authors are able to express dependencies on a version range which allows
  for automatic bugfixes without API breakage.
+ **Provisioning** - We define a packaging formant and web service API that
  provides a homogenous way for services to be distributed.
+ **Process Isolation** - Services are run in distinct processes, and nothing in
  the service API precludes this implementation.
+ **Revocation** - Services may be revoked by specific version or range.
+ **Self Documenting** - Part of the Service API includes a expression of supported
  functions and gives the developer a place to declare documentation.

## The Proposal

This proposed service API consists of four distinct pieces:

1. [A Web Services API](/docs/developer/platform/WSAPI) - How agents can query/download available services.
2. [Packaging Format](/docs/developer/platform/packaging) - How services should be packaged/signed.
3. [The C Service API](/docs/developer/platform/CAPI) - A simplified NPAPI focused on scriptable services.
4. [The JavaScript API](/docs/developer/platform/JSAPI) - How untrusted websites "require" and use services.

## Future Work

There are a handful of significant design issues remaining:

**Federated Service Distribution** - The mechanism to allow multiple 
organizations run distribution services must be documented.

**Self-published services** - For development, there's no match in 
simplicity of "self published services", that is the ability to package
a service and place it on a web server.  A visitor can automatically
install the service without the  (if the user has 
configured software to allow it and approves installation).  No mechanisms
yet exist to support this.

**User Prompting and Permissioning** - Currently, services explicitly
specify the "permissions" they'll require to run.  These permissions
are a dynamic and user meaningful set of actions, such as "display
notifications on your desktop", or "make connections to IRC servers".
This simple mechanism allows (hopefully) meaningful prompts to be
rendered.  This mechanism should evolve into something that involves
less synchronous user interuption.  Something that ultimately delivers
the same or better control over permissions granted to pages.  And finally,
something that can be reasonably implemented by multiple different browser
vendors.

**Unique Service Naming** - As service development is de-centralized
there must be a naming scheme that is logical, discoverable, and
prevents collision.
