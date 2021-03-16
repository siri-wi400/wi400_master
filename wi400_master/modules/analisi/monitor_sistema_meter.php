<!DOCTYPE html>
<html>
<head>
	<script class="include" type="text/javascript" src="/WI400_LZOVI/routine/jquery/jqplot/jquery.js"></script>
    <script class="include" type="text/javascript" src="/WI400_LZOVI/routine/jquery/jqplot/jquery.jqplot.min.js"></script>
    <script type="text/javascript" src="/WI400_LZOVI/routine/jquery/jqplot/plugins/jqplot.meterGaugeRenderer.min.js"></script>
    
	<link href="/WI400_LZOVI/routine/jquery/jqplot/jquery.jqplot.min.css" rel="stylesheet" type="text/css"/>
</head>
<body>
	<div id="meter1" style="height: 300px; width: 400px; position: relative;"></div>
	<script type="text/javascript">

	var storedData  = 0;
	var meter1;
	var tickCount = 0;
	var newVal = 0;
	var repeat;
	
	renderGraph();
	doUpdate();
	
	function renderGraph() {
	    if (meter1) {
	        meter1.destroy();
	    }
	    //console.log(storedData);
		meter1 = $.jqplot('meter1',[[storedData]],{
			seriesDefaults: {
				renderer: $.jqplot.MeterGaugeRenderer,
				rendererOptions: {
					label: 'CPU %',
					labelPosition: 'bottom',
					labelHeightAdjust: 0,
					intervalOuterRadius: 85,
					ticks: [0, 25, 50, 75, 100],
					intervals:[25, 75, 100],
					intervalColors:['#66cc66', '#E7E658','#cc6666']
				}
			}
		});
		//console.log(meter1);
	}
	
	function doUpdate() {
	    if (tickCount < 100) {
			tickCount = tickCount +1;
			var newVal = parent.jQuery('#PPROCESU').attr('value')/10;
			//console.log(newVal);
			storedData = newVal;
            renderGraph();
            repeat = setTimeout(doUpdate, 1000);
	    }
	}
	</script>
</body>
</html>