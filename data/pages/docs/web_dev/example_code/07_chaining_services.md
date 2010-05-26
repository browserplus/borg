# Chaining Services

This code sample demonstrates a couple key ideas. First, we show how @{service ImageAlter} and @{service FileTransfer} may be
combined in a chain to allow you to upload client modified images. The bigger idea demonstrated here is how the "file" type
in BrowserPlus allows services to be combined in different ways.

@{include examples/chaining_services.raw}

ImageAlter version 2.0.0 and above returns a temporary session scoped "file". This file can then be passed into FileAccess to
generate a in-page preview, it can be passed into FileTransfer to upload the contents, and it can be passed into FileChecksum to
calculate an MD5 of the contents on the client.

A final element of this demonstration is the server code to receive the upload. Given the expressiveness of PHP, it's
actually quite simple:

    header("Content-type: text/plain");
    echo md5(file_get_contents($_FILES['thumbnail']['tmp_name']));

As you can see, the server php code simply calculates the md5 sum of the uploaded content and returns it in the body of the
response.
