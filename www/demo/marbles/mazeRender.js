function BrowserPlusMaze(data) {
  var baseDir = "yui/build";

  var validateMazeData = (function() {
	var L = YUI({base:baseDir}).Lang;

	// functions for validating types
	function valNum(v) {
	  if (!L.isNumber(v)) {
		throw "not a number";
	  }	
	}

	function valPoint(v) {
	  if (!L.isArray(v) || v.length !== 2 ||
		  !L.isNumber(v[0]) || !L.isNumber(v[1]))
	  {
		throw "not a valid point";
	  }	
	}

	function valPointArray(v) {
	  try {
		if (!L.isArray(v)) throw "meh";
		for (var i = 0; i < v.length; i += 1) { valPoint(v[i]); }
	  } catch (e) {
		throw "not a valid array of points";
	  }	
	}

	function valPointPairArray(v) {
	  try {
		if (!L.isArray(v)) throw "meh";
		for (var i = 0; i < v.length; i += 1) {
		  if (!L.isArray(v[i]) || v[i].length != 2) throw "meh";
		  valPointArray(v[i]);
		}		  
	  } catch (e) {
		throw "not a valid array of pairs of points";	  
	  }
	}

	// a terse representation of a "grammar" for mazeData
	var mazeDataGrammar = {
	  width: [ valNum, 600 ],
	  height: [ valNum, 400 ],
// fixed at 15 px for now
//	  ballRadius: [ valNum, 20 ],
// fixed at 15 px for now, should be per-hole?
//	  holeSize: [ valNum, 30],
	  start: [valPoint],
	  finish: [valPoint],
	  walls: [valPointPairArray, []],
	  holes: [valPointArray, []]
	};

	return function(d) {
	  for (k in mazeDataGrammar) {
		if (!mazeDataGrammar.hasOwnProperty(k)) continue;
		
		// assign default value if not present
		if (!d.hasOwnProperty(k)) {
		  if (mazeDataGrammar[k].length > 1) {
			d[k] = mazeDataGrammar[k][1];
		  } else {
			throw("invalid maze data, missing required field: " + k);
		  }
		} 
		// otherwise, validate type
		else {
		  try {
			mazeDataGrammar[k][0](d[k]);
		  } catch (e) {
			throw("invalid maze data, '" + k + "' is malformatted (" + e + ")");
		  }
		}
	  }
	  return d;
	}
  })();
  var data = validateMazeData(data);

  return (function() {
	var wallNodes = [];
	var holeNodes = [];
	var finishNode;
	var ballNode;

	// calibration information for the accelerometer
	var level = [ 0 , 0 ];

	// current velocity of the ball, expressed as a vector.  pixels
	// of movement per period
	var velocity = [ 0, 0 ];
	var board;

	var elasticity = .65;
	var refreshPeriodMS = 25;
	var completeCallback;

	function adjustRegion(r, p) {
	  r.top += p[1];
	  r.bottom += p[1];
	  r.left += p[0];
	  r.right += p[0];
	}
	function setRegion(lhs, rhs) {
	  lhs.top = rhs.top;
	  lhs.bottom = rhs.bottom;
	  lhs.left = rhs.left;
	  lhs.right = rhs.right;
	  lhs.width = rhs.width;
	  lhs.height = rhs.height;
	}

	function uncollide(r, disp, checkFunc) {
	  // now see how far we can get in the X direction 
	  xadj = (disp[0] < 0) ? -1 : +1;
	  for (var j = 0 ; j != Math.round(disp[0]); j += xadj) {
		adjustRegion(r, [xadj,0]);
		if (!checkFunc(r)) {
		  adjustRegion(r, [-xadj,0])
		  break;
		}
	  }

	  // now see how far we can get in the Y direction 
	  yadj = (disp[1] < 0) ? -1 : +1;

	  for (var j = 0 ; j != Math.round(disp[1]); j += yadj) {
		adjustRegion(r, [0,yadj]);
		if (!checkFunc(r)) {
		  adjustRegion(r, [0,-yadj])
		  break;
		}
	  }
	}

	// check for a collision with walls, dstRegion wil be modified if
	// collision is detected.
	// return value is true if collision detected
	function collisionCheck(origRegion, dstRegion)
	{
	  var collision = false;

	  // have you won? grab the center point of the dst region
	  // and check if it's in the finish "hole"
	  var centerPoint = {
		width: Math.floor(dstRegion.width / 3),
		height: Math.floor(dstRegion.width / 3),
		left: dstRegion.left + Math.floor(dstRegion.width / 3),
		right: dstRegion.right - Math.floor(dstRegion.width / 3),
		top: dstRegion.top + Math.floor(dstRegion.height / 3),
		bottom: dstRegion.bottom - Math.floor(dstRegion.height / 3)
	  };
	  if (ballNode.inRegion(finishNode, true, centerPoint)) {
		throw "win";
	  }

	  for (var i = 0; i < holeNodes.length; i++) {
		if (holeNodes[i].inRegion(holeNodes[i], true, centerPoint)) {
		  throw "lose";
		}
	  }

	  if (!ballNode.inRegion(board, true, dstRegion)) {
		setRegion(dstRegion, origRegion);
		uncollide(dstRegion, velocity, function(r) {
		  return (ballNode.inRegion(board, true, r));
		})
		collision = true;
	  }

	  for (var i = 0; i < wallNodes.length; i++) {
		if (wallNodes[i].inRegion(dstRegion, false)) {
		  setRegion(dstRegion, origRegion);
		  collision = true;

		  uncollide(dstRegion, velocity, function(newRegion) {
			return !(wallNodes[i].inRegion(newRegion, false));
		  })
		}
	  } 
	  return collision;
	}

	return {
	  render: function(id) {
		Y = YUI().use('node', function(Y) {
		  // render the border
		  var mainDiv = Y.get(id);
		  // clear all children
		  while (mainDiv.hasChildNodes()) {
			mainDiv.removeChild(mainDiv.get("firstChild"));
		  }

		  mainDiv.setStyles({
			border: "4px solid #CDA95F",
			width: data.width,
			height: data.height
		  });

		  // render the board
		  board = Y.Node.create('<div></div>');		  
		  board.setStyles({
			position: 'relative',
			width: data.width,
			height: data.height,
			background: "#D2C397"
		  });
		  mainDiv.appendChild(board);
		  var origin = board.getXY();

		  // render some walls
		  for (var i = 0 ; i < data.walls.length; i += 1) {
			var wd = data.walls[i];
			var e = Y.Node.create('<div></div>');
			e.setStyles({
			  width: wd[1][0] - wd[0][0],
			  height: wd[1][1] - wd[0][1],
			  position: 'absolute',
			  background: "#CDA95F",
			  border: "1px solid #666666"
			});
			wallNodes.push(e);
			board.appendChild(e);
			e.setX(wd[0][0] + origin[0]);
			e.setY(wd[0][1] + origin[1]);
		  }

		  // render some holes
		  for (var i = 0 ; i < data.holes.length; i++) {
			var hd = data.holes[i];
			var e = Y.Node.create('<div></div>');			
			e.setStyles({
			  width: 40, //XXX: data.holeRadius * 2,
			  position: 'absolute',
			  height: 40 //XXX: data.holeRadius * 2,
			});
			var holeImg = Y.Node.create('<img src="img/hole.gif">');
			holeImg.setStyles({
			  width:  40, //XXX: data.ballRadius * 2,
			  height: 40 //XXX: data.ballRadius * 2,
			});
			e.appendChild(holeImg);
			board.appendChild(e);
			holeNodes.push(e);
			e.setX(hd[0] + origin[0]);
			e.setY(hd[1] + origin[1]);
		  }

		  // now render the finish
		  finishNode = Y.Node.create('<div></div>');
		  finishNode.setStyles({
			width: 40, //XXX: data.holeRadius * 2,
			position: 'absolute',
			height: 40 //XXX: data.holeRadius * 2,
		  });
		  finishImg = Y.Node.create('<img src="img/finish.gif">');
		  finishImg.setStyles({
			width:  40, //XXX: data.ballRadius * 2,
			height: 40 //XXX: data.ballRadius * 2,
		  });
		  finishNode.appendChild(finishImg);
		  board.appendChild(finishNode);
		  finishNode.setX(data.finish[0] + origin[0]);
		  finishNode.setY(data.finish[1] + origin[1]);

		  // now render the ball
		  ballNode = Y.Node.create('<div></div>');
		  ballNode.setStyles({
			width: 30, //XXX: data.ballRadius * 2,
			height: 30, //XXX: data.ballRadius * 2,
			position: 'absolute'
		  });
		  ballImg = Y.Node.create('<img src="img/ball.gif">');
		  ballImg.setStyles({
			width: 30, //XXX: data.ballRadius * 2,
			height: 30 //XXX: data.ballRadius * 2,
		  });
		  ballNode.appendChild(ballImg);
		  board.appendChild(ballNode);
		  ballNode.setX(data.start[0] + origin[0]);
		  ballNode.setY(data.start[1] + origin[1]);
		});
	  },
	  calibrate: function(callback) {
		var count = 0;
		var x = 0;
		var y = 0; 

		// take 10 samples over the course of a second 
		var calibrateFunc = function() {
		  if (count >= 10) {
			level = [Math.round(x),Math.round(y)];
			callback();
		  } else {
			BrowserPlus.Motion.Coords({ method: 'motion' }, function(rv) {
			  if (!rv.success) {
				alert("error: " + rv.error + " - " + rv.verboseError);
			  } else {
				x = ((count * x) + rv.value.x) / (count + 1);
				y = ((count * y) + rv.value.y) / (count + 1);
				count += 1;
				setTimeout(calibrateFunc, 100);
			  }
			});
		  }
		};

		calibrateFunc();
	  },
	  start: function(input, cb) {
		completeCallback = cb;

		// basic algorithm.  pull orientation data, translate
		// into acceleration.  apply accel to ball.  move ball.
		// check for collisions.
		var update = function() {
		  BrowserPlus.Motion.Coords({ method: input }, function(rv) {		
			rv.value.x -= level[0];
			rv.value.y -= level[1];
			// now scale this single sample into the 0..5 range
			var accel = [ (rv.value.x / -256) * 3, (rv.value.y / 256) * 3 ];
			velocity[0] += accel[0];
			velocity[1] += accel[1];

			// get original region, move ball, get destination region
			var origRegion = ballNode.get('region');
			var dstRegion = {
			  top: origRegion.top + Math.round(velocity[1]),
			  bottom: origRegion.bottom + Math.round(velocity[1]),
			  left: origRegion.left + Math.round(velocity[0]),
			  right: origRegion.right + Math.round(velocity[0]),
			  width: origRegion.width,
			  height: origRegion.height
			};

			try {
			  if (origRegion.left != dstRegion.left ||
				  origRegion.top != dstRegion.top)
			  {
				if (collisionCheck(origRegion, dstRegion)) {
				  // handle velocity changes
				  if (Math.round(dstRegion.left - origRegion.left) !=
					  Math.round(velocity[0]))
				  {
					velocity[0] = -velocity[0] * .6;
				  }
				  if (Math.round(dstRegion.top - origRegion.top) !=
					  Math.round(velocity[1])) {
					velocity[1] = -velocity[1] * .6;
				  }
				}
			  }

			  // update node position
			  ballNode.setX(dstRegion.left);
			  ballNode.setY(dstRegion.top);

			  setTimeout(update, refreshPeriodMS);
			} catch (e) {
			  // update node position one last time!
			  ballNode.setX(dstRegion.left);
			  ballNode.setY(dstRegion.top);

			  if (completeCallback) completeCallback(e);
			}
		  });
		}
		update();
	  }
	};
  })();
}
