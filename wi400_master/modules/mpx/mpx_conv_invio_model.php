<?php 

/*
 * MVC (Model-View-Controller) - File di model
 * Fornisce i metodi per accedere ai dati utili all'applicazione
 * Eventuali stampe a video (es: echo) verranno visualizzate sopra la pagina
 */

	/* Includere sempre il file delle funzioni comuni */
	require_once 'mpx_commons.php';
	
	if($actionContext->getForm() == "DEFAULT") {
		/*
 		 * Aggiungo l'azione corrente all'elenco visualizzato della history
 		 * history->addCurrent(): aggiunge l'azione corrente, ricorrendo ai dati dell'azione impostati nell'anagrafica
 		 * history->add(<dati azione>,<label>): aggiunge l'azione corrente e permette di impostare un label
		 */
		$history->addCurrent();
	}

	/*
	 * Eliminazione del record $ID selezionato e di tutti i record delle altre tabelle ad esso collegato
	 * (tabelle interessate: FPDFCONV, FMPXPARM, FEMAILDT, FEMAILAL)
	 */
	if($actionContext->getForm() == "DELETE") {
		$ID = "";

		/* Recupero della chiave passata */
		$ID = getListKey("MPX_CONV_INVIO");
		
		$sql = "DELETE FROM FPDFCONV WHERE ID='".$ID."'";
        $result = $db->query($sql); 
       	if($result) 
       		$messageContext->addMessage("SUCCESS", "Cancellazione del record ID ".$ID." nella tabella FPDFCONV eseguita");
	    else
	    	$messageContext->addMessage("ERROR", "Il record ID ".$ID." della tabella FPDFCONV non è stato cancellato");
	    	
	    $sql = "DELETE FROM FMPXPARM WHERE ID='".$ID."'";
        $result = $db->query($sql); 
      	if($result) 
       		$messageContext->addMessage("SUCCESS", "Cancellazione del record ID ".$ID." nella tabella FMPXPARM eseguita");
	    else
	    	$messageContext->addMessage("ERROR", "Il record ID ".$ID." della tabella FMPXPARM non è stato cancellato");
		
	    $sql = "DELETE FROM FEMAILAL WHERE ID='".$ID."'";
        $result = $db->query($sql); 
      	if($result) 
       		$messageContext->addMessage("SUCCESS", "Cancellazione del record ID ".$ID." nella tabella FEMAILAL eseguita");
	    else
	    	$messageContext->addMessage("ERROR", "Il record ID ".$ID." della tabella FEMAILAL non è stato cancellato");
	    		    
	    $sql = "DELETE FROM FEMAILDT WHERE ID='".$ID."'";
        $result = $db->query($sql); 
      	if($result) 
       		$messageContext->addMessage("SUCCESS", "Cancellazione del record ID ".$ID." nella tabella FEMAILDT eseguita");
	    else
	    	$messageContext->addMessage("ERROR", "Il record ID ".$ID." della tabella FEMAILDT non è stato cancellato");
	    		    	
	    $actionContext->onSuccess("MPX_CONV_INVIO", "DEFAULT");
    	$actionContext->onError("MPX_CONV_INVIO", "DEFAULT");
	}

?>