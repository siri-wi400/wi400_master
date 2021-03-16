<?php
require_once 'telnet_5250_common.php';
echo getTema5250();
disableInputFocusStyle();
require_once "telnet_5250_script.php";
//echo '<script type="text/javascript" src="jquery.ba-hashchange.js"></script>';
if($actionContext->getForm()=="DEFAULT") {
	//error_reporting(E_ALL);
	//ini_set("display_errors", 1);

	//getMicroTimeStep("INIZIO");
	// Cicolo di lettura della maschera
	if (!isset($Sessione5250)) {
		$Sessione5250 = new wi400AS400Session(wi400AS400Func::getSessionId());
	}
	$display = $Sessione5250->manage5250();
	
	// Esecuzione parametri e macro
	if ($Sessione5250->getMacroScript()!="") {
		$Sessione5250->executeMacro($display);		
	}

	$display->setDisposeContainer(true);
	$display->setDisposeFunctionButton(true);
	$html = $display->display();
	
	echo $html;
	
	getMicroTimeStep("FINE");
}
if($actionContext->getForm()=="DISCONNECT") {
	$Sessione5250->closeTerminalConnection();
}

if(isset($_REQUEST['WI400_IS_WINDOW'])) {
?>
	<script>
		var numWindow = wi400top.wi400_window_counter;
		
		jQuery(document).ready(function(){
			//console.log("lookup"+wi400top.wi400_window_counter);
			//console.log(numWindow);
			wi400top.jQuery('#lookup'+wi400top.wi400_window_counter).dialog('option', 'close', 
				function(event, ui) {
					console.log("close window");
					closeSession5250(function() {
						eval("closeLookUp()");
						wi400top.jQuery('#lookup'+wi400_window_counter).remove();
					});
				}
			);
		});
	</script>
<?php 
}

if($form == "DOWNLOAD_EXTRACTION") {
	
	downloadDetail($TypeImage, $filename, $temp, "Esportazione completata");
	
}



