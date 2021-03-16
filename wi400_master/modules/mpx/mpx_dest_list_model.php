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
		$dest = "";
		$tpdest = "";

		/* Recupero della chiave passata */
		$ID = getListKey("MPX_DEST_LIST", 0);
		$dest = getListKey("MPX_DEST_LIST", 1);
		$tpdest = getListKey("MPX_DEST_LIST", 2);
		
		if(!isset($ID) || empty($ID)) {
			$ID = $_POST['ID'];
			$atc = $_POST['MAIATC'];
			$tpdest = $_POST['MATPTO'];
		}
	
		$sql = "DELETE FROM FEMAILDT WHERE ID='$ID' AND MAITOR='$dest' AND MATPTO='$tpdest'";
        $result = $db->query($sql); 

       	if($result) 
       		$messageContext->addMessage("SUCCESS", "Cancellazione del record eseguita");
	    else
	    	$messageContext->addMessage("ERROR", "Il record ID non è stato cancellato");
	    	
	    $actionContext->onSuccess("MPX_DEST_LIST", "DEFAULT");
    	$actionContext->onError("MPX_DEST_LIST", "DEFAULT");
	}

?>