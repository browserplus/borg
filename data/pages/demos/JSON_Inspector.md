# JSON Inspector

In addition to adding fun and powerful tools like desktop notifications and drag-and-drop,
BrowserPlus can be used to introduce reference implementations for emerging web standards. Here we
implement the [JSONRequest proposal](http://www.json.org/JSONRequest.html) as BrowserPlus service
to enable secure cross-site JSON requests. The resulting @{s JSONRequest} API is then used to make a
simple explorer for the output of JSON web services.

[![JSON Inspector Screenshot](/i/d/jsonrequest.jpg)](/demo/jsonrequest/)

## Discussion

The internet is a wild and wonderful place where much of humanity's knowledge is accessible. The
problem is that the standards of the web today are so entrenched that it's difficult to innovate
new and richer types of web applications without sacrificing accessibility, or a paying a massive
development cost. Standards implementation and acceptance is naturally a slow process, and in turn
the web gets better, but slowly.

The BrowserPlus platform, and other similar technologies, give us a way to short circuit this
trend, implementing and using the standards of tomorrow, today. This demo shows how the
JSONRequest proposal by Douglas Crockford could be simply implemented in a cross-browser and
cross-platform manner, without waiting for browser implementors to come to consensus.

While the ideas may be ambitious, the demo is a simple JSON Inspector which allows you to see the
output of any JSON web service on the web in your browser. Enjoy!