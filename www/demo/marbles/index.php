<?php 
require("../../../php/vars.php");
?>
<html>
  <head>
    <title> The BrowserPlus Marble Maze </title>
    <link rel="stylesheet" href="maze.css" type="text/css" media="screen" />
  </head>
  <body>
	<div id="bplusLogo">
    	<img src="/i/bp-logo-sm.png" style="margin:20px 0;">
	</div>
	<div id="pageTitle">
	  Marble Maze
	</div>
	<center>
    <div id="mazeDiv">
	  <div id="greetingsDiv" class="displayDiv">
		<span class="title"> The BrowserPlus Marble Maze game </span>

		<div class="desc">
		  A simple javascript game that lets you control the plane of a
		  marble puzzle using your laptops built-in motion sensor, or mouse
		  pointer (if you don't have a motion sensor).  The goal of the game
		  is to get the marble to the finish without falling in any holes
          (l00z3r).
		  <p>
		  This demonstration is built on
		  <a href="http://developer.yahoo.com/yui/3">YUI 3 pr1</a>
		  and uses ideas and techniques proposed by
		  <a href="http://www.kernelthread.com/software/ams/">Amit Singh</a>
		</div>

		<span class="title"> Play! </span>
		<div id="playDescDiv" class="desc"> Loading... </div>
      </div>
      <div id="selectDiv" class="displayDiv">
		<span class="title"> Select A Built-In Level </span>
		<ol> 
          <!-- levels will be added here -->
		</ol>
		<br>
		<span class="title"> Select A Custom Level </span>
		<p></p>
		<form action="#">
          <input type="text" size="80" value="http://hackthebrowser.org/holeymoley.json" />
		</form>
	  </div>
	</div>
	<div id="status"> Loading... </div>
	</center>
    <script src="<?php echo BROWSERPLUS_MIN_JS; ?>"></script>
    <script src="http://yui.yahooapis.com/3.0.0pr1/build/yui/yui-min.js"></script>
	<!-- sample data -->
    <scriptsrc="data.js"></script>
    <scriptsrc="mazeRender.js"></script>
    <scriptsrc="setupLogic.js"></script>
  </body>
</html>
