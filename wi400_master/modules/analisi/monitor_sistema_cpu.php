<!DOCTYPE html>
<html>
<head>
	<script class="include" type="text/javascript" src="/WI400_LZOVI/routine/jquery/jqplot/jquery.js"></script>
    <script class="include" type="text/javascript" src="/WI400_LZOVI/routine/jquery/jqplot/jquery.jqplot.min.js"></script>
    <script type="text/javascript" src="/WI400_LZOVI/routine/jquery/jqplot/plugins/jqplot.highlighter.min.js"></script>
    <script type="text/javascript" src="/WI400_LZOVI/routine/jquery/jqplot/plugins/jqplot.canvasTextRenderer.min.js"></script>
    <script type="text/javascript" src="/WI400_LZOVI/routine/jquery/jqplot/plugins/jqplot.canvasAxisLabelRenderer.min.js"></script>
    
	<link href="/WI400_LZOVI/routine/jquery/jqplot/jquery.jqplot.min.css" rel="stylesheet" type="text/css"/>
</head>
<body>
	<div id="chart1" style="height: 300px; width: 400px; position: relative;"></div>
	<div id="messaggio"></div>
	<script>
	/* store empty array or array of original data to plot on page load */
	
	var storedData  = [0];
	var plot1;
	var tickCount = 0;
	var newVal = 0;
	var repeat;
	
	renderGraph();
	doUpdate();
	
	function renderGraph() {
	    if (plot1) {
	        plot1.destroy();
	    }
	    plot1 = $.jqplot('chart1', [storedData], {
			title:'Utilizzo CPU %',
			seriesDefaults: {
				showMarker:true,
			},
			axesDefaults: {
				labelRenderer: $.jqplot.CanvasAxisLabelRenderer
			},
			axes: {
		        xaxis: {
					label: "Secondi",
					min:(storedData.length-10),
					max:(storedData.length+2)
				}, 
				yaxis: {
					label: "CPU %",
					min:0, 
					max:100
				},
			},
			highlighter: {
				show: true,
				sizeAdjust: 7.5, //Grandezza pallino selezionato
				showTooltip: true,
				useAxesFormatters: false,
				tooltipFormatString: '%s'
			}
		});
	}
	
	function doUpdate() {
	    if (tickCount < 200) {
			tickCount = tickCount +1;
			var newVal = parent.jQuery('#PPROCESU').attr('value')/10;
			//console.log(newVal);
	        /*$.post('monitor_sistema_dati.php', {
	            html: tickCount
	        }, function(response) {
				var decodeJSON = jQuery.parseJSON(response);
				//console.log(decodeJSON);
	            newVal = decodeJSON.PPROCESU/10;
				if (newVal==0) {
					newVal = oldVal;
				} else {
					oldVal = newVal;
				}
				storedData.push(newVal);

	            renderGraph();               
	            log("CPU %: "+newVal);
	            repeat = setTimeout(doUpdate, 1000);
	        });*/
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