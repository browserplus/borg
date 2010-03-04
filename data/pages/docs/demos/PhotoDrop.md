# PhotoDrop

This example speaks to one of the most commonly under-served needs of the web: media upload. Here,
we mimic the features of a desktop client, Flickr Uploadr, to create a handy tool for editing
photos before saving them to the net.

[![PhotoDrop Screenshot](/i/d/photodrop.jpg)](/demo/photodrop/)

## Discussion

This demonstration has a lot of parts to it so lets break it down. First of all, it makes use of 
a number of BrowserPlus services including @{s DragAndDrop}, @{s FileBrowse}, @{s FlickrUploader}
and @{s ImageAlter}.

We'll start with talking about how we use the first two services, both built-in to BrowserPlus, to
enable selection of files. The most significant of the two is DragAndDrop since it allows
something that no web browser can do on it's own today - accept files dragged from the desktop. In
this sample we designate a DIV, droparea, as a drop target and we can get notifications when new
files enter or are released on the area. FileBrowse enhances the standard browser file selection
control by allowing multiple file selection, while returning a secure BrowserPlus-provided file
handle for use by other services.

ImageAlter is a service based on the popular open-source ImageMagick library. This gives the
uploader demo access to powerful, fast image-editing features that are traditionally only
available to desktop applications. We add scaling, cropping, rotation, and image effects to photo
pre-processing - certainly not a Photoshop replacement, but reducing a 3MB photo to something 25%
of its size is a big boon to conserving upstream bandwidth.

Finally, FlickrUploader is nothing more than a time-saving wrapper around Flickr's Authorization
and Upload APIs. It's tricky stuff so under the hood this service leverages a Ruby library that
has already implemented these facilities. As a bonus, this service also handles cross-site
requests to Flickr on the client side (avoiding iFrames or server-side proxies) and gives the
application a mild boost in upload speed by bypassing the browser's sometimes-crippled networking
stack.

## Why Ruby?

You'll notice the references in the description of this demo, and throughout this site to the Ruby
programming language. So the natural questions are:

* How is BrowserPlus related to Ruby?
* Why use a high level language, and more specifically, why choose Ruby?

Good questions! To answer the first, you'll notice in [service explorer](/explore) that there is a
published RubyInterpreter service. This thing offers no functions for use by web developers, so
what is it? Well, precisely it's a special typeof service, a provider service, that is available
for other services to use. RubyInterpreter embeds a full Ruby interpreter (hence the name) and
allows services to be written in Ruby. The benefits of authoring services in high level languages
include the speed of implementation and portability. For example, the entire implementation of
@{s JSONRequest} ends up being 142 lines of portable ruby code. This feature of the platform, allowing
services to be authored in high level languages, means we can rapidly move from ideas to reality
in terms of new services.

So why Ruby exactly? The answer here is pragmatic: Ruby provides a full featured environment with
a fairly complete standard library than can be crammed into a download which is 2mb or less.
Futher, it's a fun language to work in, and we're actively following and contributing to it's
progress. All that said, RubyInterpreter is a service just like any other, and its existence does
not preclude support for other high level languages.

In summary, extending the web with high level languages is a powerful idea that allows for rapid
innovation. Embedding Ruby is the specific way that we've realized this idea.