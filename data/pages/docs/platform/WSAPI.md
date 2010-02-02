
# Web Services API - v3

<div markdown="1" class="api">

The BrowserPlus web services API is a RESTFUL means of accessing information about
three different types of information:

+ **Available Services** - Query or download available services
+ **Permissions** - Attain a signed "permissions" bundle
+ **Updates** - Determine the latest version of BrowserPlus software available 



### Distribution Server Roles

At present there are different roles a distribution server can serve.  *Primary* distribution
servers are used for all three types of information enumerated above.  *Secondary* distribution
servers are used only to query and attain services.  


### Documentation Conventions

All web services return [JSON](http://json.org).  Throughout this document the return values of services 
are normatively expressed in the [orderly schema language](http://orderly-json.org).

Finally, when API URI paths are specified, there a couple different placeholders
that specify "parameters" to the web service.  Those different placeholders are described here:

&lt;platform&gt;
: Currently, &lt;platform&gt; may be one of two values:  
**osx** - Mac OSX 10.4 or greater (32bit or 64bit)  
**win32** - WinXP or greater


&lt;version&gt;
: A version includes a major, minor, and micro version number.  A well formed version will match the following regular expression: `/([1-9][0-9]*|0)\.([1-9][0-9]*|0)\.([1-9][0-9]*|0)/`

&lt;name&gt;
: A Service Name may consist of any valid (url encoded) characters.

### "Primary Server" API Documentation

A primary distribution server provides access to permissions and platform updates *in addition to services*.  It is only 
useful to host a primary distribution server (and hence the api calls documented here) if you wish to maintain a custom 
version of the BrowserPlus platform.  

## /api/v3/platform/latest/version/&lt;platform&gt;

Determine the latest available platform version.  Response payload is JSON
of the following form:

### Response

~~~
object {
  # A string representation of the latest version of the BrowserPlus
  # available from this distribution server.
  string version /([1-9][0-9]*|0)\.([1-9][0-9]*|0)\.([1-9][0-9]*|0)/;
  # size in bytes of the update package  
  integer size;
};
~~~

### Example

~~~
{
  "version":"2.4.21",
  "size":2174174
}
~~~

## /api/v3/platform/latest/update/&lt;platform&gt;

Returns an LZMA compressed [signed bundle](packaging.html) containing an update to the BrowserPlus platform.
The contents of this bundle are out of the scope of the web service api.  Further information
can be found in the BrowserPlus platform [source code](http://github.com/browserplus/platform).

## /api/v3/permissions

Returns a [signed bundle](packaging.html) containing "permissions".  The JSON file contained within
the signed bundle has the following format:

### Response

~~~
object {
  # An array of services that should be 'blacklisted', that is denied
  # the ability to run.
  array [
    array {
      # the name of the service to blacklist
      string;
      # A version specification:  may be a major version "1",
      # major and minor "1.2", or an exact version "1.2.3".  
      string /^([1-9][0-9]*|0)(\.([1-9][0-9]*|0)(\.([1-9][0-9]*|0))?)?$/;
    };
  ] blacklist;

  # an array of versions of the platform that should be blacklisted.
  array [
    string /^([1-9][0-9]*|0)\.([1-9][0-9]*|0)\.([1-9][0-9]*|0)$/;  
  ] platformBlacklist;

  # Services may require permissions (in their manifest.json XXX: link me),
  # Those permissions are a dynamic set of strings that are localized in this
  # file.
  object {
    # keys are the names of permissions.  Values are objects whose keys are
    # in turn locales (RFC5646) that map to utf8 localized strings which are
    # a user visible description of the permission.

    # NOTE: orderly's handling of "additionalProperties" should be augmented
    # so that we can properly represent this a schema.
  }* servicePermissionLocalizations;
};
~~~

### Example

~~~
{                
  "blacklist" : [
    [ "EvilService", "1"],
    [ "BrokenService", "3.0.2"]
  ],
  "platformBlacklist" : [
    "2.0.3", "2.0.4", "2.0.6", "2.1.6", "2.1.7", "2.1.11",
    "2.1.14", "2.1.19", "2.2.0", "2.3.1", "2.4.6"
  ],
  "servicePermissionLocalizations" : {
    "DisplayNotifications" : {
      "it":"Visualizza notifiche sul desktop",
      "de":"Desktop-Benachrichtigungen anzeigen",
      "fr":"Afficher les notifications sur le Bureau",
      "es":"Mostrar notificaciones de escritorio",
      "sv":"Visa skrivbordsmeddelanden",
      "zh-Hans":"显示桌面通知",
      "zh-Hant":"顯示桌面訊息通知",
      "en":"Display desktop notifications",
    },
    "ConnectToIRCServer" : {
      "it":"Stabilisci connessioni con gli host IRC",
      "de":"Verbindungen zu IRC-Hosts herstellen",
      "fr":"Établir la connexion avec des hôtes IRC",
      "es":"Realizar conexiones a los host de IRC",
      "sv":"Gör anslutningar till IRC-värdar",
      "zh-Hans":"连线至 IRC 主机",
      "zh-Hant":"連線至 IRC 主機",
      "en":"Make connections to IRC hosts"
    }
  }
}
~~~

### "Secondary Server" API Documentation

## /api/v3/corelets/&lt;platform&gt;

## /api/v3/corelet/metadata/&lt;name&gt;/&lt;version&gt;/&lt;platform&gt;

## /api/v3/corelet/synopsis/&lt;name&gt;/&lt;version&gt;/&lt;platform&gt;

## /api/v3/corelet/package/&lt;name&gt;/&lt;version&gt;/&lt;platform&gt;

</div>

