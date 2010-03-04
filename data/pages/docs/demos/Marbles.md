# Marble Maze

A [simple javascript game](/demo/marbles/) that lets you control the plane of a marble puzzle
using your laptops built-in motion sensor or mouse pointer (if you don't have a motion sensor).
The goal of the game is to get the marble to the finish without falling in any holes (l00z3r).

This demonstration is built on [YUI 3 pr1](http://developer.yahoo.com/yui/3) and uses ideas and
techniques proposed by [Amit Singh](http://www.kernelthread.com/software/ams/ams.html).

[![Marble Maze Screenshot](/i/d/marbles.jpg)](/demo/marbles/)

## Discussion

Who knew that many laptops have accelerometers? This is a piece of hardware that lets us know
about the physical orientation and current acceleration of your laptop. Amit Singh has written
quite a bit about the devices, how you interface them, how they work, and what they're used for.

The BrowserPlus team, claiming to be playful, decided to wrap up access to device in the @{s
Motion} service. Once that was done... well... What can you do with such a thing? Write a game, of
course!

The most interesting thing that this demonstration shows is how little known custom hardware can
be safely exposed to web developers everywhere. We think there are a whole lot of interesting
applications involving custom hardware when you sit down and think about it. The great thing about
BrowserPlus that makes this possible, is the **dynamic** acquisition of services. To add custom
device support, we don't have to bloat the core platform. Users only get the bits that they want
to use!