# Web Services API - v3

The BrowserPlus web services API is a RESTFUL means of accessing information about
three different types of information:

+ **Available Services** - Query or download available services
+ **Permissions** - Attain a signed "permissions" bundle
+ **Updates** - Determine the latest version of BrowserPlus software available 

## Distribution Server Roles

At present there are different roles a distribution server can serve.  *Primary* distribution
servers are used for all three types of information enumerated above.  *Secondary* distribution
servers are used only to query and attain services.  

## Documentation Conventions

All web services return [JSON](http://json.org).  Throughout this document the return values of services 
are normatively expressed in the [orderly schema language](http://orderly-json.org).

Finally, when API URI paths are specified, there a couple different placeholders
that specify "parameters" to the web service.  Those different placeholders are described here:

### &lt;platform&gt; 

Currently, &lt;platform&gt; may be one of two values: 

* osx - Mac OSX 10.4 or greater (32bit or 64bit)
* win32 - WinXP or greater

NOTE: In the future it would be useful to make this a tad richer.

### &lt;version&gt;

A version includes a major, minor, and micro version number.  A well formed version will match the following regular expression:

    /([1-9][0-9]*|0)\.([1-9][0-9]*|0)\.([1-9][0-9]*|0)/

### &lt;name&gt;

A Service Name may consist of any valid (url encoded) characters.

## API Documentation



### /api/v3/platform/latest/version/&lt;platform>

(Primary distribution servers only).

Determine the latest available platform version.  Response payload is JSON
of the following form:

~~~
object {
  # A string representation of the latest version of the BrowserPlus
  # available from this distribution server.
  string version /([1-9][0-9]*|0)\.([1-9][0-9]*|0)\.([1-9][0-9]*|0)/;
  # size in bytes of the update package  
  integer size;
};
~~~
Example output:
~~~
{
  "version":"2.4.21",
  "size":2174174
}
~~~

### /api/v3/platform/latest/update/&lt;platform>


### /api/v3/permissions

### /api/v3/corelets/&lt;platform>

### /api/v3/corelet/metadata/&lt;name>/&lt;version>/&lt;platform>

### /api/v3/corelet/synopsis/&lt;name>/&lt;version>/&lt;platform>

### /api/v3/corelet/package/&lt;name>/&lt;version>/&lt;platform>
