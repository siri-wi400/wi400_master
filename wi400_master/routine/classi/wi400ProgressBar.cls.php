<?php
class wi400ProgressBar {
	
	
	private $id;
	private $time;
	private $idList;
	
	public function __construct($id, $id_lista){
		
		$this->id = $id;
		$this->time = 3;
		
		if($id_lista) {
			$this->idList = $id_lista;
		}

		$this->setPercentage($id,0);
		
	}
	
	public static function setIdList($id){
		$this->idList = $id;
	}

	public static function getIdList(){
		return $this->idList;
	}
	
	public static function setPercentage($id, $perc){
		
		$filePerc = wi400File::getUserFile("tmp", session_id().$id.".perc");
	
		$handle = fopen($filePerc, "w");
		if (flock($handle, LOCK_EX)){
			$putfile = True;
		} else {
			$putfile = False;
		    fclose($handle);
		}
		if ($putfile){
		 	fputs($handle, $perc);
			flock($handle, LOCK_UN);
		 	fclose($handle);
		}
		
	}
	
	public static function getPercentage($id){
		
		$perc = 0;
		$filePerc = wi400File::getUserFile("tmp", session_id().$id.".perc");
 		if (file_exists($filePerc)) {
			$handle = fopen($filePerc, "r");
			$perc = fread($handle, filesize($filePerc));
   			fclose($handle);
		}
		$perc = floor($perc);
		if ($perc >= 100){
			$perc = 100;
			
			// Cancello file
			unlink($filePerc);
		}
		return $perc;
	}
    
	public function getTime(){
		return $this->time;
	}
    
	public function setTime($time){
		$this->time = $time;
	}
	
	public function getCallBack(){
		return $this->callBack;
	}
    
	public function setCallBack($callBack){
		$this->callBack = $callBack;
	}
	
	public function dispose2() {
?>
		<script>
			function openProgressBar() {
				jQuery.ajax({  
					type: "GET",
					url: _APP_BASE + "index.php?t=PROGRESS_BAR&TIME=<?= $this->time ?>&PROGRESS_BAR=<?= $this->id ?>&ID_LIST=<?= $this->idList ?>"
				}).done(function ( response ) {
	 				//var progressJSON = jQuery.parseJSON(response);
	 				console.log(response);
		    	});
				//openWindow(_APP_BASE + "index.php?t=PROGRESS_BAR&PROGRESS_BAR=<?= $this->id ?>&TIME=<?= $this->time ?>", "progressBar", 450, 180, true, false);
			}
			
			
			
			setTimeout("openProgressBar()",0);
		</script>
<?	}
	
	public function dispose() {
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
		$string .= "</div>";
		
		//showArray($_REQUEST);
		
		if($this->idList) {
?> 
			<script>
				function openProgressBar(id_list) {
					//console.log("id_lista: "+id_list)
					//console.log(jQuery("div[id$='Container']"));
					var list = jQuery("#"+id_list+"Container");
					//console.log(list);
					jQuery(list).css("width", "100%");
					jQuery(list).html("<?= $string ?>");
					
					setTimeout("checkProgressBar(0)", 1000);
					//checkProgressBar();
				}

				function checkProgressBar(valore){
					try {
						jQuery.ajax({  
							type: "POST",
							url: _APP_BASE + "modules/utilities/getProgressBar.php",
							data: {
								'ID': '<?= session_id()?>',
								'PROGRESS_BAR': '<?= $this->id ?>',
								'UTENTE': '<?= $_SESSION['user']?>',
								'VALORE': valore
							}
						}).done(function ( response ) {
			 				var progressJSON = jQuery.parseJSON(response);
			 				//console.log(progressJSON);
			
			 				//console.log(document.getElementById("percLabel"));
			 				var labelPercentage = document.getElementById("percLabel");
			 				if(document.getElementById("percLabel")) { // L'ultima chiamata restituisce null poichè viene rimossa la progress bar
				 				labelPercentage.innerHTML = progressJSON.percentage;
				 				valore = progressJSON.percentage;
				 				jQuery('#progressBar').animate({
				 					width: (progressJSON.percentage * 4)
				 				}, 1000, function() {
				 					// Animation complete.
				 				});
			 				}
			
			    			if (progressJSON.percentage < 100){
			    				setTimeout("checkProgressBar("+valore+")", <?=$this->time?>000);
			    			}
				    	});
			
						return false;
					}catch(e) {
						console.log("Errore done ajax");
					}	
				}

				setTimeout("openProgressBar('<?=$this->idList?>')", 0);
			</script>
<?		
		}else {
			echo "<font color='red' size='13'>Devi settare l'id lista alla progress bar</font>";
		}
	}
}
?>