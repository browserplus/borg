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
    actions:[{"scale": [0.2, 0.2, 0.8, 0.8]}, "enhance", "grayscale", {"contrast": 2}]
  }, 
  function(result){});
~~~~

## ImageAlter API

file: path (req)
: The image to transform.

format: string (opt)
: The format of the output image. Default is to output in the same format as the input image. A
string, one of: jpg, gif, or png

quality: integer (opt)
: The quality of the output image. From 0-100. Lower qualities result in faster operations and
smaller file sizes, at the cost of image quality.

actions: list (opt)

: An array of actions to perform. Each action is either a string (i.e. { actions: [ 'solarize' ]
}) , or an object with a single property, where the property name is the action to perform, and
the property value is the argument (i.e. { actions: [{rotate: 90}] }.

#### Supported Actions

blur - actions: ['blur']
: Blur an image.

contrast - actions: [{'contrast': 1}]
: adjust the image's contrast, accepts an optional numeric argument between -10 and 10

crop - actions: [{'crop': [0.1, 0.1, 0.9, 0.9]}]
: select a subset of an image, accepts an array of four floating point numbers: x1,y1,x2,y2 which are between 0.0 and 1.0 and are relative coordinates to the upper left hand corner of the image

despeckle - actions: ['despeckle']
: reduces the speckle noise in an image while preserving the edges of the original image, accepts no arguments

dither - actions: ['dither']
: Use the ordered dithering technique of reducing color images to monochrome

enhance - actions: ['enhance']
: Applies a digital filter that improves the quality of a noisy image, accepts no arguments

equalize - actions: ['equalize']
: Applies a histogram equalization to the image

grayscale - actions: ['grayscale']
: remove the color from an image, accepts no arguments

greyscale - actions: ['greyscale']
: an alias for 'grayscale'

negate - actions: ['negate']
: negate the colors of the image, accepts no arguments

noop - actions: ['noop']
: do nothing. may be applied multiple times. still does nothing.

normalize - actions: ['normalize']
: Enhance the contrast of a color image by adjusting the pixels color to span the entire range of colors available.

oilpaint - actions: ['oilpaint']
: an effect that will make the image look like an oil painting, accepts no arguments

psychedelic - actions: ['psychedelic']
: trip out an image. takes no arguments. may be applied multiple times.

rotate - actions: [{'rotate': -45}]
: rotate an image by some number of degrees, takes a single numeric argument

scale - actions: [{'scale': {'maxwidth':320, 'maxheight': 320}}]
: downscale an image preserving aspect ratio. you may provide the integer arguments maxwidth and/or maxheight which limit the image in the specified direction. units are pixels.

sepia - actions: ['sepia']
: Sepia tone an image. no arguments. Apply contrast after this.

sharpen - actions: ['sharpen']
: Sharpens an image. Similar but not the same as 'unsharpen'.

solarize - ['solarize']
: Solarize an image. No arguments

swirl - actions: [{'swirl', 180}]
: Swirl an image. Optionally a numeric argument specifies the degrees to swirl, default is 90 degrees.

unsharpen - actions: ['unsharpen']
: Sharpens an image. Similar but not the same as 'sharpen'. No arguments.

## Extending and Tinkering

Don't see the transformation you need? No problem. ImageAlter(4) is [free and open
source](http://github.com/lloyd/bp-imagealter) software: All you have to do is find the operation
you're looking for in the [GraphicsMagick API](http://www.graphicsmagick.org/api/api.html), and
[add a
transformation](http://github.com/lloyd/bp-imagealter/blob/master/src/Transformations.cpp#L510)
to ImageAlter. As an example, the entire implementation of blur is only about [12 lines of
code](http://github.com/lloyd/bp-imagealter/commit/d9fb80f9402af737830fc3456f98532530cfb973)!