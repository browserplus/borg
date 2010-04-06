# Bit Squeezr

[Bit Squeezr](/demo/squeezr/) lets you leverage one of the most [advanced compression
algorithms](http://en.wikipedia.org/wiki/LZMA) around to squeeze your files down to a fraction of
their original size. Here we've brought awesome compression to the web in a simple browser based
tool, and the best part is that the services it leverages are available for anyone to integrate.

[![Bit Squeezr Screenshot](/i/d/squeezr.jpg)](/demo/squeezr/)

## Discussion

One of the ideas present in BrowserPlus that we find most powerful is the notion that, with a
little help, browser plugins can be safely shared by multiple sites on the web, and further can be
*combined to accomplish tasks*. The idea of many small, focused, and complementary tools isn't
novel -- It's the idea that originally made the Unix shell as pervasive as it is.

BrowserPlus is already widely used for a more efficient and streamlined user experience during
content upload. This demo showcases what we believe to be the next evolutionary improvement in
content upload: Sophisticated client side compression.

**Bit Squeezr** is fueled by a tight 200 lines of portable JavaScript that accepts any number of
files dropped by the user, combines them into a single file using the Tar service, and Compresses
them down using LZMA. Finally, by leveraging @{s FileAccess} we provide a way to allow the browser
to prompt the user to save the compressed result.

## Open Source Services!

Many moons ago we made a commitment to open source, understanding that a sincere attempt to
forward the open web must be accompanied by efforts to both solicit feedback from the community as
well as to empower them to improve, derive, and create on top of the BrowserPlus platform. Given
this understanding, It's with great pride that both the @{s LZMA} and @{s Tar} service are
available now and maintained on [GitHub](http://github.com/). Happy forking!