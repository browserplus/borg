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

## API Documentation

### Specifying <platform> 

Currently, <platform> may be one of two values: 

* osx - Mac OSX 10.4 or greater (32bit or 64bit)
* win32 - WinXP or greater

NOTE: In the future it would be useful to make this a tad richer.

### Specifying <version>

A version includes a major, minor, and micro version number.  A well formed version will match the following regular expression:

    /([1-9][0-9]*|0)\.([1-9][0-9]*|0)\.([1-9][0-9]*|0)/

### Specifying <name>

A Service Name may consist of any valid (url encoded) characters

### `/api/v3/platform/latest/version/<platform>`

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

### `/api/v3/platform/latest/update/<platform>`

### `/api/v3/permissions`

### `/api/v3/corelets/<platform>`

### `/api/v3/corelet/metadata/<name>/<version>/<platform>`

### `/api/v3/corelet/synopsis/<name>/<version>/<platform>`

### `/api/v3/corelet/package/<name>/<version>/<platform>`

