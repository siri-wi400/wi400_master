<?php 

/*
 * MVC (Model-View-Controller) - File di model
 * Fornisce i metodi per accedere ai dati utili all'applicazione
 * Eventuali stampe a video (es: echo) verranno visualizzate sopra la pagina
 */

	/* Includere sempre il file delle funzioni comuni */
	require_once 'mpx_commons.php';
	
	if($actionContext->getForm() == "DEFAULT") {
		sessionUnregister("MPX_CONV_INVIO");
		
		$history->addCurrent();
	}
	if($actionContext->getForm() == "DELETE") {
		$ID = "";

		/* Recupero della chiave passata */
		$ID = getListKey("MPX_LISTA");
		
		if(!isset($ID) || empty($ID))
			$ID = $_POST['ID'];
		
		$sql = "DELETE FROM FMPXPARM WHERE ID='".$ID."'";
        $result = $db->query($sql); 

        if($result) 
       		$messageContext->addMessage("SUCCESS", "Cancellazione del record eseguita");
	    else
	    	$messageContext->addMessage("ERROR", "Il record ID non è stato cancellato");
        
	    if($_REQUEST['FROM']=='mpx') {
	    	$actionContext->onSuccess("MPX_LIST", "DEFAULT");
	    	$actionContext->onError("MPX_LIST", "DEFAULT");
	    }
	    else if($_REQUEST['FROM']=='conv') {
	    	$actionContext->onSuccess("MPX_CONV_INVIO", "DEFAULT");
    		$actionContext->onError("MPX_CONV_INVIO", "DEFAULT");
	    }
	}

?>