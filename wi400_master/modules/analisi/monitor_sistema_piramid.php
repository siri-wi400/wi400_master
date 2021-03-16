<!DOCTYPE html>
<html>
<head>
	<script class="include" type="text/javascript" src="/WI400_LZOVI/routine/jquery/jqplot/jquery.js"></script>
    <script class="include" type="text/javascript" src="/WI400_LZOVI/routine/jquery/jqplot/jquery.jqplot.min.js"></script>
    <script type="text/javascript" src="/WI400_LZOVI/routine/jquery/jqplot/plugins/jqplot.categoryAxisRenderer.min.js"></script>
	<script type="text/javascript" src="/WI400_LZOVI/routine/jquery/jqplot/plugins/jqplot.pyramidAxisRenderer.min.js"></script>
	<script type="text/javascript" src="/WI400_LZOVI/routine/jquery/jqplot/plugins/jqplot.pyramidGridRenderer.min.js"></script>
	<script type="text/javascript" src="/WI400_LZOVI/routine/jquery/jqplot/plugins/jqplot.pyramidRenderer.min.js"></script>
	<script type="text/javascript" src="/WI400_LZOVI/routine/jquery/jqplot/plugins/jqplot.canvasTextRenderer.min.js"></script>
	<script type="text/javascript" src="/WI400_LZOVI/routine/jquery/jqplot/plugins/jqplot.canvasAxisLabelRenderer.min.js"></script>
    
	<link href="/WI400_LZOVI/routine/jquery/jqplot/jquery.jqplot.min.css" rel="stylesheet" type="text/css"/>
	<link href="/WI400_LZOVI/routine/jquery/css/jquery-ui.min.css" rel="stylesheet" type="text/css"/>
</head>
<body>
	<div id="piramid1" style="height: 530px; width: 100%; position: relative;"></div><br/>
	<font size="5">Lavori attivi: <b id="attivi"></b></font><br/>
	<font size="5">Lavori batch: <b id="batch"></b></font>
	<script type="text/javascript">

		var piramid1;
		var tickCount = 0;
		var newVal = 0;
		var repeat;
		var righeGrafico = 50;

		var ticks = [];
		for(var i=1; i<=righeGrafico; i++) {
			ticks.push(i);
		}
	 
	    var workActive = [0];
	    var workBatch = [0];
	 
	    var greenColors = ["#526D2C", "#77933C", "#C57225", "#C57225"];
	    var blueColors = ["#3F7492", "#4F9AB8", "#C57225", "#C57225"];

	    renderGraph();
		doUpdate();

		$('#piramid1').bind('jqplotDataHighlight', function(evt, seriesIndex, pointIndex, data) {
	        var attivi = Math.abs(piramid1.series[0].data[pointIndex][1]);
	        var batch = Math.abs(piramid1.series[1].data[pointIndex][1]);

	        //var ratio = femalePopulation / malePopulation * 100;
			$('#attivi').stop(true, true).css("opacity", 0).html(attivi).animate({
				opacity: 1,
			}, 500, function() {
				//console.log("Animazione completata*******************************************");
			});
	        $('#batch').stop(true, true).css("opacity", 0).html(batch).animate({
				opacity: 1,
			}, 500, function() {
				//console.log("Animazione completata///////////////////////////////////");
			});
	    });
		
		function renderGraph() {
		    if (piramid1) {
		        piramid1.destroy();
		    }

		    if(tickCount > righeGrafico/2) {
			    //console.log("Sono dentro: "+tickCount);
			    ticks = [];
		    	for(var i=tickCount-(righeGrafico/2); i<=tickCount+(righeGrafico/2); i++) {
					ticks.push(i);
				}
		    }
		    
		    var plotOptions = {
		        title: '<div style="float:left;width:50%;text-align:center">Lavori attivi</div><div style="float:right;width:50%;text-align:center">Lavori batch</div>',
		        seriesColors: greenColors,
		        grid: {
		            drawBorder: false,
		            shadow: false,
		            background: 'white',
		            rendererOptions: {
		                plotBands: {
		                    show: false
		                }
		            }
		        },
		 
		        defaultAxisStart: 0,
		        seriesDefaults: {
		            renderer: $.jqplot.PyramidRenderer,
		            rendererOptions: {
		                barPadding: 2,
		                offsetBars: true
		            },
		            yaxis: 'yaxis',
		            shadow: false
		        },
		 
		        series: [
		            {
		                rendererOptions:{
		                    side: 'left',
		                    synchronizeHighlight: 1
		                }
		            },
		            {
		                yaxis: 'y2axis',
		                rendererOptions:{
		                    synchronizeHighlight: 0
		                }
		            },
		            // Pyramid series are filled bars by default.
		            // The overlay series will be unfilled lines.
		            {
		                rendererOptions: {
		                    fill: false,
		                    side: 'left'
		                }
		            },
		            {
		                yaxis: 'y2axis',
		                rendererOptions: {
		                    fill: false
		                }
		            }
		        ],
		        axes: {
		            xaxis: {
		                tickOptions: {},
		                rendererOptions: {
		                    baselineWidth: 2
		                }
		            },
		            yaxis: {
		                label: 'Tempo',
		                // Use canvas label renderer to get rotated labels.
		                labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
		                // include empty tick options, they will be used
		                // as users set options with plot controls.
		                tickOptions: {},
		                tickInterval: 5,
		                showMinorTicks: true,
		                ticks: ticks,
		                rendererOptions: {
		                    category: false,
		                    baselineWidth: 2
		                }
		            },
		            y2axis: {
		                label: 'Tempo',
		                // Use canvas label renderer to get rotated labels.
		                labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
		                // include empty tick options, they will be used
		                // as users set options with plot controls.
		                tickOptions: {},
		                tickInterval: 5,
		                showMinorTicks: true,
		                ticks: ticks,
		                rendererOptions: {
		                    category: false,
		                    baselineWidth: 2
		                }
		            }
		        }
		    };
			piramid1 = $.jqplot('piramid1', [workActive, workBatch], plotOptions);
		}
		
		function doUpdate() {
		    if (tickCount < 100) {
				tickCount = tickCount +1;
				var newVal = parent.jQuery('#ACTIVEJOB').attr('value');
				var newVal2 = parent.jQuery('#BJRUN').attr('value');
				workActive.push(newVal);
				workBatch.push(newVal2);

				storedData = newVal;
	            renderGraph();
	            repeat = setTimeout(doUpdate, 1000);
		    }
		}
	</script>
</body>
</html>
