# Developing Ruby Services for BrowserPlus

[Ruby](http://ruby-lang.org/) is a flexible, expressive, complete, and small language that is fun to write, and fairly straightforward
to embed. It allows programmers to do a whole lot in just a little code, and has great out of the box APIs for scripting on the desktop.
In selecting our first high level language to support, all of these factors played into our choice of Ruby. Minimal BrowserPlus platform
support combined with the RubyInterpreter service make it possible to write scriptable web plugins in Ruby. This page documents the
"Ruby Service API": how you go about writing services that extend the web in Ruby.

## How Ruby and BrowserPlus are Related

While we've never regretted our first choice of Ruby, we didn't want to too tightly couple BrowserPlus to any one runtime or
interpreter. For this reason the Ruby integration is wholly implemented in a BrowserPlus service. As this suggests, the development
progression of the Ruby service authoring environment is orthogonal to the progression of the platform. Further, the existence of
support for Ruby does not preclude other interpreted environments, (say Python, lua, or php).

Enough with the theory, how about a little practice? The rest of this page will cover the details of writing services in Ruby.

## Turning a Ruby Script into a BrowserPlus Service

The first question that you may have is "What's different about a plain ol' Ruby script and a BrowserPlus service in Ruby?". There are a
couple key quirks and integration points:

1. You must define a variable at the top level scope named rubyCoreletDefinition which is a data structure that describes the interface
   of your service.
2. You must define a class, your "entry-point class" whose name is contained in your rubyCoreletDefinition that implements the
   functionality of your service.
3. You may require Ruby standard libraries using the Kernel#require method, as usual. However, to include additional Ruby files that you
   package in your service, you must use the special bp_require global function.
4. You should _not_ use global variables. Instead use class variables on your "entry-point class".

## Defining Your Service's Interface

The information in the rubyCoreletDefinition data structure will be used to dynamically map functions into the browser that can be
invoked from Javascript, and will be used to validate that functions are invoked with arguments of the correct type. This data structure
serves as the interface definition of your service.

The `rubyCoreletDefinition` is itself a hash with the following top level (string, not symbol) keys:

class
: The name of the "entry-point class"

name
: The name of service as seen by Javascript clients

major_version
: The major version of your service which should be incremented whenever API breaking changes are made.

minor_version
: The minor version of your service which should be incremented whenever non-breaking API changes are made.

micro_version
: The micro version of your service which should be incremented whenever changes are made which do not affect the API (bug fixes,
performance improvements, or security fixes).

documentation
: A human readable english string which describes to a web developer what they can do with this service.

functions
: An array of maps which define the functions that the service exposes.

The functions array is an array of maps, each describing a function available on the entry-point class. The following top level keys
must be defined:

name
: The name of the function, This must match a function present on your entry-point class.

documentation
: A human readable english string which describes to a web developer what this function does. Also in this description the return value
should be documented.

arguments
: A human readable english string which describes to a web developer what this function does. Also in this description the return value
of the functions should be documented.

The arguments array is an array of maps, each describing an argument available on the function. For each map, the following top level
keys must be defined:

name
: The name of the parameter.

documentation
: A human readable english string which describes to a web developer what this parameter does.

type
: The type of data that may be passed in this parameter. Allowable types are "null", "bool", "integer", "double", "string", "map",
"list", or "path".

required
: A boolean which specifies whether the the parameter is required or not.

The BrowserPlus platform will reject function invocations that do not have required parameters, or pass the wrong types, so a degree of
argument validation is done for you.

~~~
rubyCoreletDefinition = {    
  'class' => "MyGreatServiceInstance",    
  'name' => "MyGreatService",    
  'major_version' => 0,    
  'minor_version' => 0,    
  'micro_version' => 1,    
  'documentation' => 'A GREAT service.',    
  'functions' =>    
  [    
    {    
      'name' => 'HelloWorld',    
      'documentation' => "Say \"hello\" to the world",    
      'arguments' => [    
        {    
          'name' => 'who',    
          'type' => 'string',    
          'documentation' => 'who you want to say hello to',    
          'required' => true    
        }    
      ]    
    }      
  ]     
}
~~~

This definition defines a service called "MyGreatService" at version "0.0.1". It provides a single function that a Javascript developer
may invoke, named "HelloWorld", that will return a string. This function accepts a single required string argument named "who" which is
the name of the person to whom the service will say hello in its return value.

## Writing the "Entry-point class"

In your definition you specified a class value which is the name of the Ruby class that will implement your service's interface. In the
example above this class was MyGreatServiceInstance. When implementing that class you must follow a couple rules:

1. The name of the implementation class must match what's in your definition
2. The class must have an initialize member.
3. The class must have one function for each function mentioned in the definition

~~~
class MyGreatServiceInstance    
  def initialize(context)    
  end    
    
  def HelloWorld(transaction, args)    
    transaction.complete("Hello #{args['who']} from my great service!")    
  end    
end  
~~~

This simple "Entry-point" class combined with the description above constitutes the complete Ruby implementation of a BrowserPlus
service.


## Arguments to #initialize

As you'll notice above, the initialize member of your entry point class takes a single argument. This argument is a hash and contains
several string keys which provide information about the environment you're running in and the browser client who is interacting with
you. The keys in the 'context' argument follow:

uri
: string containing a URI of the current client. This is usually the full URL to the webpage that is interacting with browserplus. In
the case of a native local application interacting with browserplus it should be a URI with a method of 'bpclient' (i.e.
bpclient://CLIENTIDENTIFIER').

corelet_dir
: DEPRECATED, use service_dir instead.

service_dir
: absolute path to the directory containing the files distributed with the service.

data_dir
: absolute path to where the service should store any data that needs to persist.

temp_dir
: directory that may be used for temporary data. it will be unique for every instance, and will be purged when the instance is deallocated.

locale
: locale of the end user to which strings, if any, should be localized.

userAgent
: client user-agent string.

clientPid
: process ID of the client.

## Arguments to Methods

Each method on your entry-point class which implements a function exposed to Javascript will take two arguments:

transaction
: object that represents the current transaction (or function invocation).

arguments 
: hash containing the arguments passed in from Javascript.

The return value of your method will be ignored, and instead you should return values by invoking the #complete method of the
transaction object. The primary reason for this pattern is to allow for the asynchronous return of results. In general, none of your
functions should sleep or block, and Ruby threads should be used for long running or synchronous operations.


## The Transaction Object

The transaction object has two interesting methods:

\#complete
: method that takes a single argument which will be the return value passed to Javascript. This function completes the transaction.

\#error 
: method that takes two optional arguments which will end the transaction and return an error value to Javascript. The first optional
argument is an 'error code' that will be passed up to Javascript. The second optional argument is a verbose error code which should
deliver more information about what went wrong to the Javascript developer using the service.

~~~
// calling #complete:
transaction.complete({"this"=>"is", "theReturnValue" => 77});  

// calling #error:
transaction.error("parseError", "the '#{argName}' argument is incorrect");  
~~~


## Parameter Types

Thus far in examples we've only covered the string parameter type to services. Here we'll enumerate the full set of supported types and
how they are mapped into (and out of) Ruby:

null
: parameter of type "null" may only have the value of "null" (or "nil"). This is not a particularly interesting parameter type.

bool
: Booleans hold a value of true or false.

integer
: Integers are represented in the platform as 64 bit signed integers, and may be bound to Fixnum or Bignum in the Ruby environment,
depending on the value.

double
: double precision floating point integers, which May be mapped into Float or Integers in the Ruby environment.

string
: UTF8 encoded strings

map
: associative arrays or hashes. Note, it is not possible to specify the types contained within a map, so the service must do its own
validation when accepting map parameters.

list
: Arrays. As with maps, it is not possible to specify the types of data stored within maps, so the service must perform validation.

callback
: "callback" is currently represented as a number in Ruby, and serves as an opaque handle to the Javascript callback passed in. Callbacks
are custom objects that will be covered in the next section.

path
: paths are handled specially in the BrowserPlus platform for security reasons. The type of a path in Ruby is a Pathname object. More
discussion about paths and security implications is available below.


# Callback Arguments

A function on your service may accept arguments which are callbacks into Javascript. Your function may define any number of these
parameters and may invoke them any number of times before calling #complete or #error on the transaction object. Callbacks are passed
into Ruby as objects which support a single function, #invoke. The invoke function takes a single argument which must consist of the
basic types mentioned above and will be automatically mapped into Javascript.

~~~
def HelloWorld(transaction, args)  
  # this will take a while, so thread it  
  Thread.new(transaction, args['callback']) do |tx,callback |  
    (1..10).each do |x|  
      # calling into javascript  
      callback.invoke("Howdy there, for the #{x}th time!")  
      sleep 0.5  
    end  
    tx.complete("Goodbye for now.")  
  end  
end
~~~

## Breaking your Service Into Lots of Little Files

It is generally a good thing to break you service into lots of little files. This is allowed and encouraged when writing Ruby services,
however there is one catch. For implementation reasons the path to your installed service is not in the Ruby load path, so require
cannot be used. A function is mapped into the Ruby global context, bp_require which allows you to require Ruby libraries using paths
relative to the directory into which your service is installed. Calling bp_require

~~~
bp_require "a_local_ruby_file.rb"  
~~~



## Logging from your Service

A single global function (other than bp_require which we've already covered) is available for use in the BrowserPlus Ruby environment.
This function allows you to log into the same logfile that BrowserPlus uses. bp_log takes two arguments, the first is the log level,
one of 'debug' 'info', 'warn', 'error', or 'fatal', and the second argument is a string to log.

~~~
bp_log("info", "log something #{msg}")  
~~~

The BrowserPlus logfile is stored in a platform dependent user scoped location, and is a useful tool for understanding more about how
BrowserPlus works and debugging problems. The logfile can be found in the following locations on different platforms:

OSX
: $HOME/Library/Application Support/Yahoo!/BrowserPlus/[platform version]/[uuid]/BrowserPlusCore.log

Windows XP
: %USERPROFILE%\Local Settings\Application Data\Yahoo!\BrowserPlus\[platform version]\[uuid]\BrowserPlusCore.log

Windows Vista:
: %HOMEPATH%\AppData\LocalLow\Yahoo!\BrowserPlus\[platform version]\[uuid]\BrowserPlusCore.log

Note: [uuid] is a installation specific UUID that is generated at the time you install BrowserPlus.


## Security concerns and the "path" type

Within the BrowserPlus platform, paths are special entities. Interacting with the user's disk based files is a potentially dangerous
activity which needs special handling to ensure the user's data is not compromised.

For these reasons, except in very special cases, file paths should always be accepted by services as the path type and not as the
string type. Additionally, you should almost never return a path from your service. The only way that a path may be made available to a
webpage is if that path was generated via user interaction, or if the user is explicitly prompted about the types of paths your
services will provide to the webpage. This user interaction is a form of implicit consent to manipulate a specific file. Two examples
of implicit user approval include dragging and dropping the file onto the browser, or interacting with a browse dialog and explicitly
selecting files.

These security mechanisms are designed to minimize user risk, and as your service gets ready for production we can work through
security considerations together.



## Packaging and manifest.json

The final element of writing a service is the 'manifest' file. This is a JSON file that contains metadata about your service. This file
must be named 'manifest.json'. Here is a complete example:

~~~
{  
  "type": "dependent",  
  "uses": {  
    "corelet": "RubyInterpreter",  
    "version": "4",  
    "minversion": "4.2.3"  
  },  
  "arguments": {  
    "ScriptFile": "myService.rb"  
  },  
  "strings": {  
    "en": {  
      "title": "My Great Service",   
      "summary": "A sample service to show you how to build your own."  
    }  
  }  
}  
~~~

In general, the only lines you will need to change are 9, 13, and 14: Containing the name of your top level Ruby script, an end-user
meaningful name for your service, and an end-user meaningful description of what it does.