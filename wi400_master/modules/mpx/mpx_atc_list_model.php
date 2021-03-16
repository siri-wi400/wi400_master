<?php 

/*
 * MVC (Model-View-Controller) - File di model
 * Fornisce i metodi per accedere ai dati utili all'applicazione
 * Eventuali stampe a video (es: echo) verranno visualizzate sopra la pagina
 */

	/* Includere sempre il file delle funzioni comuni */
	require_once 'mpx_commons.php';
	
	if($actionContext->getForm() == "DEFAULT") {
		$ID = "";

		$ID = getListKey("MPX_CONV_INVIO");
		
		$history->addCurrent();
	}
	if($actionContext->getForm() == "DELETE") {
		$ID = "";
		$atc = "";

		/* Recupero della chiave passata */
		$ID = getListKey("MPX_ATC_LIST", 0);
		$atc = getListKey("MPX_ATC_LIST", 1);
		
		if(!isset($ID) || empty($ID)) {
			$ID = $_POST['ID'];
			$atc = $_POST['MAIATC'];
		}
	
		$sql = "DELETE FROM FEMAILAL WHERE ID='$ID' AND MAIATC='$atc'";
        $result = $db->query($sql); 

       	if($result) 
       		$messageContext->addMessage("SUCCESS", "Cancellazione del record eseguita");
	    else
	    	$messageContext->addMessage("ERROR", "Il record ID non è stato cancellato");
	    	
	    $actionContext->onSuccess("MPX_ATC_LIST", "DEFAULT");
    	$actionContext->onError("MPX_ATC_LIST", "DEFAULT");
	}
	else if($actionContext->getForm()=="ATC_PRV") {
		$campo = $_REQUEST['CAMPO'];
//		echo "CAMPO: $campo<br>";

//		echo "REQUEST:<pre>"; print_r($_REQUEST); echo "</pre>";
		
//		echo "DETAIL KEY: ".$_REQUEST["DETAIL_KEY"]."<br>";
		
		$atc_param = explode('|', $_REQUEST["DETAIL_KEY"]);
		$id = trim($atc_param[0]);
		
		if($campo=="atc") {
			$filename = $atc_param[1];
			$temp = "";
		}
		else if($campo=="pat") {
			$filename = $atc_param[2];
			$temp = "";
		}
		else if($campo=="nam") {
			$filename = $atc_param[3];
			
			$file_path = dirname($filename);
//			echo "FILE PATH: $file_path<br>";
			if($file_path==".") {
				$file_path = dirname($atc_param[2]);
				$filename = $file_path."/".$filename;
			}
			$temp = "";
		}
		
//		echo "FILE: $filename<br>";
		
		$TypeImage = "";
		$file_parts = pathinfo($filename);
		if(isset($file_parts['extension']))
			$TypeImage = strtolower($file_parts['extension']);
	}

?>