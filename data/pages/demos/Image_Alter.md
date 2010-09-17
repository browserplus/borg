# Image Alter

Are your servers feeling the heat? BrowserPlus offers powerful, *client-side* [image
transformation](/demo/imagealter/). The new @{s ImageAlter} service is now built on
[GraphicsMagick](http://www.graphicsmagick.org/). GraphicsMagick offers faster image processing
in a smaller service. In fact, the Windows version has shrunk 50%, from 1.4MB to 700KB. Besides
faster filters and a smaller download, the API has been simplified to allow for the ordering of
transformation operations (first rotate right, then normalize, then despeckle, then add
contrast).

[![Image Alter Screenshot](/i/d/imagealter.jpg)](/demo/imagealter/)

## Example

A typical call to ImageAlter from BrowserPlus looks like this:

~~~~
BrowserPlus.ImageAlter.transform({
    file: <path>, 
    format: "png", 
    quality: 100, 
    actions:[{"crop": [0.2, 0.2, 0.8, 0.8]}, "enhance", "grayscale", {"contrast": 2}]
  }, 
  function(result){});
~~~~

## API

To learn more about ImageAlter, read about the [full API](/docs/services/ImageAlter.html) or
try it out live with the [Service Explorer](/explore/?s=ImageAlter&f=transform&v=4.0.5).

## Extending and Tinkering

Don't see the transformation you need? No problem. ImageAlter(4) is [free and open
source](http://github.com/lloyd/bp-imagealter) software: All you have to do is find the operation
you're looking for in the [GraphicsMagick API](http://www.graphicsmagick.org/api/api.html), and
[add a
transformation](http://github.com/lloyd/bp-imagealter/blob/master/src/Transformations.cpp#L510)
to ImageAlter. As an example, the entire implementation of blur is only about [12 lines of
code](http://github.com/lloyd/bp-imagealter/commit/d9fb80f9402af737830fc3456f98532530cfb973)!