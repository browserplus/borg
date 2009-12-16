# InactiveServices

Get a list of all available services.

##  BrowserPlus.InactiveServices.All({params}, function(){})

The platform (either 'osx' or 'win32') for which you would like to see available corelets.

### Parameters

platform: String *(Optional)*
: Allows for the exploration of available services, which may be downloaded and activated.



## BrowserPlus.InactiveServices.Describe({params}, function(){})

Get a data structure describing the interface of a specified service.

### Parameters

service: String
: The name of the service.

version: String
: The exact version of the service.

platform: String *(Optional)*
: The platform (either 'osx' or 'win32') for which you would like a service description.