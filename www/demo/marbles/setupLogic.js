(function() {
  var renderFunc = function(input) {
	// now we should present our level selection dialog
	YUI().use('node', 'json', function(Y) {
	  // toggle visibility
	  {
		var d = Y.get("#greetingsDiv");
		d.setStyle("visibility", "hidden");
		d = Y.get("#selectDiv");		
		d.setStyle("visibility", "visible");
	  }

	  // inline function to load maze data    
	  function loadDataFile(dataPath) {
//		alert("load: " + dataPath); 
//		var loc = String(window.location);
//		dataPath = loc.substring(0, loc.lastIndexOf("/")) + "/" + dataPath;
		
		BrowserPlus.JSONRequest.get(
		  { url: dataPath },
		  function (rv) {
			if (!rv.success) {
			  alert("failed to load JSON: " + rv.error +
					(rv.verboseError ? " - " + rv.verboseError : ""));
			  return;
			}

			var m = BrowserPlusMaze(rv.value);
			m.render("#mazeDiv");
			Y.get("#status").set('innerHTML', "Calibrating...");

			function startGame() { 
			  var startTime = new Date();
			  Y.get("#status").set('innerHTML', "Calibrated");
			  m.start(input, function(x) {
				var completeStr;
				if (x === "win") {
				  completeStr = "You won! it took you " +
					(new Date() - startTime) + "ms";
				} else if (x === "lose") {
				  completeStr = "Watch out for the holes!";
				}
				Y.get("#status").set('innerHTML', completeStr);
			  });      
			}
			if (input === "motion") {
			  m.calibrate(function() { startGame() });
			} else {
			  startGame();
			}
		  });
	  }

	  var levels = Y.get("#selectDiv > ol");
	  for (var i = 0; i < builtInLevels.length; i++) {
		var l = Y.Node.create('<li></li>');
		var lnk = Y.Node.create('<a href="#' + builtInLevels[i].title
         						+'">' + builtInLevels[i].title
								+ '</a> ');
		l.appendChild(lnk);
		var a = Y.Node.create("&nbsp;(" + builtInLevels[i].author + ")");
		l.appendChild(a);
		levels.appendChild(l);
		
		// now set up a handler 
		var f = (function() {
		  var _path =	builtInLevels[i].path;
		  return function() { loadDataFile(_path) };
		})();
		Y.on("click", f, l); 
	  } 
	  // now for custom json levels
	  var f = Y.get("#selectDiv > form");
	  f.on("submit",
		   function(e) {
			 var v = Y.get("#selectDiv > form > input");
			 loadDataFile(v.get("value"));
			 e.halt();
			 e.preventDefault();
		   }, f);
	});
  }

  var installFunc = function() {
	function initCallback(r) {
	  YUI().use('node', function(Y) {
		var pd = Y.get("#playDescDiv");

		function renderHTML(errMsg, r) {
		  var str = errMsg;
		  if (r) {
			str = 'Yikes!  I failed to ' + errMsg + ': <pre>'
			  + r.error
			  + (r.verboseError ? ": " + r.verboseError : "") +
			  "</pre>"
		  } 
		  pd.removeChild(pd.get("firstChild"));		  
		  var a = Y.Node.create('<div></div>');
		  a.set("innerHTML", str);
		  pd.appendChild(a);
		}

		if (r.success) {
		  // render control choices
		  BrowserPlus.require(
			{
			  progressCallback: function(v) {
				renderHTML("Loading " + v.name + " - "
						   + v.totalPercentage + "%");
			  },
			  services: [
				{ service: "Motion", version: "0", minversion: "0.1" },
				{ service: "JSONRequest", version: "1" }
			  ]}, 
			function(rv) {
			  if (!rv.success) {
				renderHTML('activate BrowserPlus services', rv);
				return; 
			  }
			  // cool.  we got what we need!  let's render the input choices
			  BrowserPlus.Motion.Methods(
				{}, function(r) {
				  if (!r.success) {
					renderHTML("query available input methods", r);
					return;
				  }
				  var str = "Your platform supports the following:<ul>";
				  for (var i = 0; i < r.value.length; i += 1)
				  {
					if (r.value[i] === "mouse") {
					  str += "<li> <a href='#' id='selectMouse'>Mouse input</a>: ";
					  str += "The offset from center screen of your mouse ";
					  str += "point will represent the 'tilt' of ";
					  str += "the game board. (NOTE: if you have multiple ";
					  str += "monitors, one of them will be ignored)";
					} else if (r.value[i] === "motion") {
					  str += "<li> <a href='#' id='selectMotion'>Motion input</a>: ";
					  str += "The tilt of your computer will control the ";
					  str += "direction that the ball rolls.";
					}
				  }
				  str += "</ul> Click on the desired input option to play.";
				  renderHTML(str);
				  var lnk = Y.get("#selectMouse");
				  if (lnk) {
					Y.on("click", function() { renderFunc("mouse"); }, lnk);
				  }
				  var lnk = Y.get("#selectMotion");
				  if (lnk) {
					Y.on("click", function() { renderFunc("motion"); }, lnk);
				  }
				});
			}
		  );
		} else if (r.error === "bp.notInstalled" ||
				   r.error == "bp.unsupportedClient")
		{
		  // render upsell
		  renderHTML('This game requires Yahoo! <a href="http://browserplus.yahoo.com"> BrowserPlus </a> to run.  <a target="_blank" href="/install">Click here to install BrowserPlus</a>.');
		} else {
		  // render failed to load
		  renderHTML("Oops, failed to initialize BrowserPlus!  Here's the problem: <pre>"+r.error +": "+r.verboseError+"</pre>  Maybe head over to the <a href=\"browserplus.yahoo.com\">BrowserPlus website</a> for help?");
		}
	  });
	}
	BrowserPlus.init({}, initCallback);
  };

  // YUI 3 PR1's onload don't work in IE afaict.  
  if (window.addEventListener) {
	window.addEventListener("load", installFunc, true);
  } else if (window.attachEvent) {
	window.attachEvent("onload", installFunc);
  }
})();  
