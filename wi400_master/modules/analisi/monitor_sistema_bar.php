<!DOCTYPE html>
<html>
<head>
	<script class="include" type="text/javascript" src="/WI400_LZOVI/routine/jquery/jqplot/jquery.js"></script>
    <script class="include" type="text/javascript" src="/WI400_LZOVI/routine/jquery/jqplot/jquery.jqplot.min.js"></script>
    <script type="text/javascript" src="/WI400_LZOVI/routine/jquery/jqplot/plugins/jqplot.barRenderer.min.js"></script>
	<script type="text/javascript" src="/WI400_LZOVI/routine/jquery/jqplot/plugins/jqplot.pieRenderer.min.js"></script>
	<script type="text/javascript" src="/WI400_LZOVI/routine/jquery/jqplot/plugins/jqplot.categoryAxisRenderer.min.js"></script>
	<script type="text/javascript" src="/WI400_LZOVI/routine/jquery/jqplot/plugins/jqplot.pointLabels.min.js"></script>
    
	<link href="/WI400_LZOVI/routine/jquery/jqplot/jquery.jqplot.min.css" rel="stylesheet" type="text/css"/>
</head>
<body>
	<div id="bar1" style="height: 300px; width: 400px; position: relative;"></div>
	<script>
	/* store empty array or array of original data to plot on page load */
	
	var storedData  = [1, 2 , 4, 6];
	var ticks = ['a', 'b', 'c', 'd'];
	var plot1;
	var tickCount = 0;
	var newVal = 0;
	var repeat;

	$.jqplot.config.enablePlugins = true;
	
	renderGraph();
	//doUpdate();
	
	function renderGraph() {
	    if (plot1) {
	        plot1.destroy();
	    }
	    
	    plot1 = $.jqplot('bar1', [storedData], {
	    	// Only animate if we're not using excanvas (not in IE 7 or IE 8)..
            animate: !$.jqplot.use_excanvas,
            seriesDefaults:{
                renderer:$.jqplot.BarRenderer,
                pointLabels: { show: true }
            },
            axes: {
                xaxis: {
                    renderer: $.jqplot.CategoryAxisRenderer,
                    ticks: ticks
                }
            },
            highlighter: { show: false }
		});
	}
	
	function doUpdate() {
	    if (tickCount < 200) {
			tickCount = tickCount +1;
			var newVal = parent.jQuery('#PPROCESU').attr('value')/10;
			
			storedData.push(newVal);

            renderGraph();         
            log("CPU %: "+newVal);
            repeat = setTimeout(doUpdate, 1000);
	    }
	}
	function log(msg) {
		$("#messaggio").html(msg)
	}
	</script>
</body>
</html>
