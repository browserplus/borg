This sample demonstrates the conventions used when data is returned from a BrowserPlus service. The @{service PStore}
Service is the tool we use to explore return values.

@{include examples/error_handling.raw}

In the previous sample we covered the asynchronous nature of service invocation, in this sample we'll go a little further
into the structure of information that's returned. Here we can see that `BrowserPlus.PStore.get()` is invoked with two
arguments: the first is an object holding named arguments, the second is a function which will be invoked when the call
completes. This function accepts a single argument which holds the status of the call and returned data. This two argument
calling convention is common to all functions on all services.



What we haven't covered yet is how information and errors are returned inside the argument to the callback function. Result
callbacks will always be invoked with an object. That object will always have a `success` property. When `success` is true,
the `value` property will contain the return value of the function. Here is a JSON depiction of a return object resulting
from a successful service invocation:


    {
        "success": true,
         "value": [ 1, 2, 3, "bar"]
    }

When there are errors in the invocation, the `success` property
holds boolean `false`, and an `error` member is present
which includes a textual error code.  Optionally, a ` verboseError `
property may also be present which holds a developer readable english
string describing what went wrong:


    {
        "error": "PStore.invalidArguments",
        "success": false,
        "verboseError": "argument 'bogus' not supported by function 'get'"
    }


With this last bit of foundation laid, we're ready to jump in and start building web applications on BrowserPlus. Next we'll
look at a key BrowserPlus feature, which [allows javascript to get at drag and drop](drag_and_drop.html) from the desktop...

