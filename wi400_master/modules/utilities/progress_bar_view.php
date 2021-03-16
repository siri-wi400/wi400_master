<?php


	if ($actionContext->getForm() == "CHECK"){
		// Return percentage Status
		$current = wi400ProgressBar::getPercentage($progressBarId);
		
		echo json_encode(array("percentage" => $current));
	}else{
		global $temaDir;
		
		//Blu
		$color1 = "#F5FAFF";
		$color2 = "#00D8FF";
		
		//Rossa
		//$color1 = "#FFF4F4";
		//$color2 = "#FF0000";
		
		//showArray($_REQUEST);
		$string = "<div style='position: relative; width: 100%; height: 300px;'>";
		$string .= "<div align='center' style='position: absolute; width: 450px; height: 150px; top: 50%; left: 50%; margin-left: -225px; margin-top: -75px;'>";
		$string .=	"<br/>";
		$string .= 		"<img src='".$temaDir."images/loading.gif' width='32' height='32'>";
		$string .= 		"<br/><br/>";
		$string .= 		"<div style='width:400px;border:1px solid #CFCFCF;text-align: left; overflow: hidden; border-radius: 10px;'>";
		if(preg_match('/(?i)msie [2-9]/', $_SERVER['HTTP_USER_AGENT'])) {
			$string .= 			"<div id='progressBar' style='background:$color2; width: 0px; height:20px;'>";
		}else {
			$string .= 			"<div id='progressBar' style='background:#dddddd; width: 0px; height:20px;";
			$string .= 											"background: -webkit-linear-gradient($color1,  $color2);";
			$string .= 											"background: linear-gradient($color1, $color2);";
			$string .= 											"background: -o-linear-gradient($color1, $color2);";
			$string .= 											"background: -moz-linear-gradient($color1, $color2);'>";
		}
		$string .= 			"</div>";
		$string .= 		"</div><br/>";
		$string .= 		"<div class='textLight' style='text-align:center'><span id='percLabel'>0</span>% completato. Attendere prego...</div>";
		$string .= 	"</div>";
		$string .="</div>";
		
		
		//echo $string;

		if($id_list) {
?> 
			
			<script>
				//console.log(jQuery("div[id$='Container']"));
				var list = jQuery("#<?=$id_list?>Container").children();
				//console.log(list);
				jQuery(list).append("<?= $string ?>");
				try {
					function checkProgressBar(){
						jQuery.ajax({  
							type: "GET",
							url: _APP_BASE + APP_SCRIPT + "?t=PROGRESS_BAR&f=CHECK&DECORATION=clean&TIME=<?= $progressBarTime ?>&PROGRESS_BAR=<?= $progressBarId ?>&ID_LIST=<?= $id_list ?>"
						}).done(function ( response ) {
			 				var progressJSON = jQuery.parseJSON(response);
			 				//console.log(progressJSON);
			
			 				//console.log(document.getElementById("percLabel"));
			 				var labelPercentage = document.getElementById("percLabel");
			 				if(document.getElementById("percLabel")) { // L'ultima chiamata restituisce null poichè viene rimossa la progress bar
				 				labelPercentage.innerHTML = progressJSON.percentage;
				 				jQuery('#progressBar').animate({
				 					width: (progressJSON.percentage * 4)
				 				}, 1000, function() {
				 					// Animation complete.
				 				});
			 				}
			
			    			if (progressJSON.percentage < 100){
			    				setTimeout("checkProgressBar()", <?=$progressBarTime?>000);
			    			}
				    	});
			
						return false;
					}
				}catch(e) {
					console.log("Errore done ajax");
				}	
				
				setTimeout("checkProgressBar()", 0);
			</script>
<?		
		}else {
			echo "<font color='red' size='13'>Devi settare l'id lista alla progress bar</font>";
		}
	}
?>