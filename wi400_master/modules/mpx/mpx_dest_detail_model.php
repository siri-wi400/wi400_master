<?php 

/*
 * MVC (Model-View-Controller) - File di model
 * Fornisce i metodi per accedere ai dati utili all'applicazione
 * Eventuali stampe a video (es: echo) verranno visualizzate sopra la pagina
 */

	/* Includere sempre il file delle funzioni comuni */
	require_once 'mpx_commons.php';

	$ID = "";
	$dest = "";
	$tpdest = "";

	/* Recupero della chiave passata */
	$ID = getListKey("MPX_CONV_INVIO", 0);
	$dest = getListKey("MPX_DEST_LIST", 1);
	$tpdest = getListKey("MPX_DEST_LIST", 2);
	
	if($actionContext->getForm() == "DEFAULT" || $actionContext->getForm() == "UPDATE") {
		/* Creazione di una query SQL */
		$statement = $db->prepareStatement("SELECT * FROM FEMAILDT WHERE ID= ? AND MAITOR= ? AND MATPTO= ?");
		$db->execute($statement, array($ID, $dest, $tpdest));
		$mpxArray = $db->fetch_array($statement);

		if($actionContext->getForm() == "DEFAULT")
			$history->addCurrent();
	}
	else if($actionContext->getForm() == "SAVE") {
		$fields = array("MAITOR", "MAIALI", "MATPTO");
		$keys = array("ID"=>$ID, "MAITOR"=>$dest, "MATPTO"=>$tpdest);	
		$stmt = $db->prepare("UPDATE", "FEMAILDT", $keys, $fields);
		$campi = array($_POST['MAITOR'], $_POST['MAIALI'], $_POST['MATPTO']);
		$result = $db->execute($stmt, $campi);

		if($result)
			$messageContext->addMessage("SUCCESS", "Aggiornamento record eseguito con successo");
		else
			$messageContext->addMessage("ERROR", "Si sono verificati degli errori nell'aggiornamento");
		
//		$actionContext->onSuccess("MPX_DEST_DETAIL", "DEFAULT");
		$actionContext->onSuccess("MPX_DEST_LIST", "DEFAULT");
		$actionContext->onError("MPX_DEST_DETAIL", "DEFAULT");
	}
/*	else if($actionContext->getForm() == "DELETE") {
        $sql = "DELETE FROM FEMAILDT WHERE ID='$ID' AND MAITOR='$dest' AND MATPTO='$tpdest'";
        $result = $db->query($sql); 

       	if($result) 
       		$messageContext->addMessage("SUCCESS", "Cancellazione del record eseguita");
	    else
	    	$messageContext->addMessage("ERROR", "Il record ID non è stato cancellato");
	    	
	    $actionContext->onSuccess("MPX_DEST_LIST", "DEFAULT");
    	$actionContext->onError("MPX_DEST_DETAIL", "DEFAULT");
	}
*/	else if($actionContext->getForm() == "INSERT") {
		$actionContext->setLabel("Inserimento destinatario");
		
		$history->addCurrent();
	}
	else if($actionContext->getForm() == "SAVE_INS") {
		$fields = array("ID", "MAITOR", "MAIALI", "MATPTO");
		$keys = array();
		$stmt = $db->prepare("INSERT", "FEMAILDT", $keys, $fields);
		$campi = array($_POST['ID'], $_POST['MAITOR'], $_POST['MAIALI'], $_POST['MATPTO']);
		$result = $db->execute($stmt, $campi);
		
		if($result)
			$messageContext->addMessage("SUCCESS", "Inserimento record eseguito con successo");
		else
			$messageContext->addMessage("ERROR", "Si sono verificati degli errori nell'inserimento");

//		$actionContext->onSuccess("MPX_DEST_DETAIL", "DEFAULT");
		$actionContext->onSuccess("MPX_DEST_LIST", "DEFAULT");			
		$actionContext->onError("MPX_DEST_LIST", "DEFAULT");
	}

?>