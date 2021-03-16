<?php
?>
<script>
doAggiorna();
function doAggiorna() {
jQuery.ajax({  
	type: "GET",
	url: _APP_BASE + APP_SCRIPT + "?t=PERFORMANCE&DECORATION=clean"
	}).done(function ( response ) {  
		//alert(response);
		//console.log(_APP_BASE + APP_SCRIPT + "?t=PERFORMANCE&DECORATION=clean);
		var dati = jQuery.parseJSON(response);
    	var htmlout = "";
    	for (var item in dati) {
        	//console.log(item+": "+dati[item]);
        	htmlout += '<font id="'+item+'" value="'+dati[item]+'">'+item+' =>'+dati[item]+'</font><br/>';
    	}
    	jQuery('#alberto').html(htmlout);
    	
		repeat = setTimeout(doAggiorna, 1000);
	}).fail(function ( data ) {  
		//alert("fail");
		repeat = setTimeout(doAggiorna, 1000);
	});
}
</script>
<iframe id="cpu_usage" src="http://10.0.40.1:89/WI400_PASIN/modules/analisi/monitor_sistema_cpu.php" width="450px" height="350px"></iframe>
<iframe id="cpu_usage_1" src="http://10.0.40.1:89/WI400_PASIN/modules/analisi/monitor_sistema_disk.php" width="450px" height="350px"></iframe><br/>
<iframe id="cpu_usage_2" src="http://10.0.40.1:89/WI400_PASIN/modules/analisi/monitor_sistema_meter.php" width="450px" height="350px"></iframe>
<iframe id="cpu_usage_4" src="http://10.0.40.1:89/WI400_PASIN/modules/analisi/monitor_sistema_bar.php" width="450px" height="350px"></iframe><br/>
<iframe id="cpu_usage_3" src="http://10.0.40.1:89/WI400_PASIN/modules/analisi/monitor_sistema_piramid.php" width="600px" height="650px"></iframe>
<div id="alberto" style="position: relative; width: 300px; height: 300px; background: red; overflow: auto; display: visible;">
Non ci sono dati
</div>
