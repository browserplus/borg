# Publish Subscribe

@{service PublishSubscribe} allows multiple browser windows to communicate which
each other.  The windows don't even have to be from the same browser ... 
for example, a message sent from Internet Explorer can be received from
a Firefox browser window.  This service is inspired by HTML5's
[Cross Document Messaging](http://www.whatwg.org/specs/web-apps/current-work/multipage/comms.html)

@{include examples/publish_subscribe.raw}

Make sure to run this code <a target="bw2" href="run.html?i=2">in a second</a> 
(and <a target="bw3" href="run.html?i=3">even third</a>)
window to see the messages sent back and forth.  While you're at it, you can
even come back to the code demo in **a different browser**.


NOTE
: No *IFRAMEs* were hurt in the making of this demo.
