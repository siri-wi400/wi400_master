<?php 

$file = file_get_contents("/www/zendsvr/perfomance.data");
$dati = unserialize($file);
/*echo "<pre>";
print_r($dati);
echo "</pre>";*/
$diskTot = $dati['SYSASP']/1000;
$diskUse = ($dati['SYSASP']*($dati['PSYSASP']/10000))/100000;
$diskFree = ($diskTot - $diskUse);
$diskUse = number_format($diskUse, 1);
$diskFree = number_format($diskFree, 1);
$stringa = "[";
//$stringa .= "['Da elaborare ({$keyArray['DA_RIS']})', {$keyArray['DA_RIS']}],";
$stringa .= " ['Utilizzato ($diskUse GB)', $diskUse],";
$stringa .= " ['Libero ($diskFree GB)', $diskFree],";
$stringa .="]";
?>
<!DOCTYPE html>
<html>
<head>
	<script class="include" type="text/javascript" src="/WI400_LZOVI/routine/jquery/jqplot/jquery.js"></script>
    <script class="include" type="text/javascript" src="/WI400_LZOVI/routine/jquery/jqplot/jquery.jqplot.min.js"></script>
    <script type="text/javascript" src="/WI400_LZOVI/routine/jquery/jqplot/plugins/jqplot.pieRenderer.min.js"></script>
	<script type="text/javascript" src="/WI400_LZOVI/routine/jquery/jqplot/plugins/jqplot.donutRenderer.min.js"></script>

	<link href="/WI400_LZOVI/routine/jquery/jqplot/jquery.jqplot.min.css" rel="stylesheet" type="text/css"/>
	<!--[if lt IE 9]><script language="javascript" type="text/javascript" src="../excanvas.js"></script><![endif]-->
	<!--<script class="include" type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>-->
</head>
<body>
	<div id="pie8" style="width: 100%; height: 100%"></div>
	<button onClick="location.reload();">Refresh</button>
	<script class="code" type="text/javascript">
		jQuery(document).ready(function(){
			var s1 = <?php echo $stringa ?>;
			//var s1 = parent.jQuery('#SYSASP').attr('value');
		        
		    var plot8 = jQuery.jqplot('pie8', [s1], {
				title:'Utilizzo Disco',
		        grid: {
		            drawBorder: false, 
		            drawGridlines: false,
		            background: '#ffffff',
		            shadow:false
		        },
		        axesDefaults: {
		            
		        },
		        seriesDefaults:{
		            renderer:jQuery.jqplot.PieRenderer,
		            rendererOptions: {
		                showDataLabels: true
		            }
		        },
		        legend: {
		            show: true,
		            location: 'e'
		        }
		    }); 
		});
	</script>
</body>
</html>