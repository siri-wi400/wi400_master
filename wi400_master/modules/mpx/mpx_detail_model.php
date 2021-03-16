<?php 

/*
 * MVC (Model-View-Controller) - File di model
 * Fornisce i metodi per accedere ai dati utili all'applicazione
 * Eventuali stampe a video (es: echo) verranno visualizzate sopra la pagina
 */

	/* Includere sempre il file delle funzioni comuni */
	require_once 'mpx_commons.php';

	$ID = "";

	/* Recupero della chiave passata */
	$ID = getListKey("MPX_CONV_INVIO");
	
	if(!isset($ID) || empty($ID))
		$ID = getListKey("MPX_LISTA");

	if($actionContext->getForm() == "DEFAULT" || $actionContext->getForm() == "UPDATE") {
		$stmt = $db->prepareStatement("SELECT * FROM FMPXPARM WHERE ID = ?");
		$db->execute($stmt, array($ID));
		$mpxArray = $db->fetch_array($stmt);

		if($actionContext->getForm() == "DEFAULT")
			$history->addCurrent();
	}
	else if($actionContext->getForm() == "SAVE") {
		$fields = array("TEST", "NUMPAG", "WKPRID", "ADDR1", "ADDR2", "ADDR3", "CAP", "CITTA", "PROV", "NAZ", 
			"GLOCOD", "SETID", "SETCOD", "PDFCOD", "ENVCOD");
		$keys = array("ID"=>$ID);
		$stmt = $db->prepare("UPDATE", "FMPXPARM", $keys, $fields);
		$campi = array($_POST['TEST'], $_POST['NUMPAG'], $_POST['WKPRID'], $_POST['ADDR1'], $_POST['ADDR2'], 
			$_POST['ADDR3'], $_POST['CAP'], $_POST['CITTA'], $_POST['PROV'], $_POST['NAZ'], 
			$_POST['GLOCOD'], $_POST['SETID'], $_POST['SETCOD'], $_POST['PDFCOD'], $_POST['ENVCOD']);
		$result = $db->execute($stmt, $campi);

		if($result)
			$messageContext->addMessage("SUCCESS", "Aggiornamento record eseguito con successo");
		else
			$messageContext->addMessage("ERROR", "Si sono verificati degli errori nell'aggiornamento");
			
		$actionContext->onSuccess("MPX_DETAIL&FROM=".$_REQUEST['FROM'], "DEFAULT");
		$actionContext->onError("MPX_DETAIL&FROM=".$_REQUEST['FROM'], "DEFAULT");
	}
/*	
	else if($actionContext->getForm() == "DELETE") {
        $sql = "DELETE FROM FMPXPARM WHERE ID='$ID'";
        $result = $db->query($sql); 

       	if($result) 
       		$messageContext->addMessage("SUCCESS", "Cancellazione del record eseguita");
	    else
	    	$messageContext->addMessage("ERROR", "Il record ID non è stato cancellato");
	    
		if($_REQUEST['FROM']=='conv') {	
			$actionContext->onSuccess("MPX_CONV_INVIO", "DEFAULT");
			$actionContext->onError("MPX_CONV_INVIO", "DEFAULT");
		}
		else if($_REQUEST['FROM']=='mpx') {
			$actionContext->onSuccess("MPX_LIST", "DEFAULT");
			$actionContext->onError("MPX_LIST", "DEFAULT");
		}
	}
*/	
	else if($actionContext->getForm() == "INSERT") {
		$actionContext->setLabel("Inserimento impostazioni MPX");
		
		$history->addCurrent();
	}
	else if($actionContext->getForm() == "SAVE_INS") {
		$fields = array("ID", "TEST", "NUMPAG", "WKPRID", "ADDR1", "ADDR2", "ADDR3", "CAP", "CITTA", "PROV", "NAZ", 
			"GLOCOD", "SETID", "SETCOD", "PDFCOD", "ENVCOD");
		$keys = array();
		$stmt = $db->prepare("INSERT", "FMPXPARM", $keys, $fields);
		$campi = array($_POST['ID'], $_POST['TEST'], $_POST['NUMPAG'], $_POST['WKPRID'], $_POST['ADDR1'], $_POST['ADDR2'], 
			$_POST['ADDR3'], $_POST['CAP'], $_POST['CITTA'], $_POST['PROV'], $_POST['NAZ'], 
			$_POST['GLOCOD'], $_POST['SETID'], $_POST['SETCOD'], $_POST['PDFCOD'], $_POST['ENVCOD']);
		$result = $db->execute($stmt, $campi);
		
		if($result)
			$messageContext->addMessage("SUCCESS", "Inserimento record eseguito con successo");
		else
			$messageContext->addMessage("ERROR", "Si sono verificati degli errori nell'inserimento");
			
//		$actionContext->onSuccess("MPX_DETAIL&FROM=".$_REQUEST['FROM'], "DEFAULT");
		if($_REQUEST['FROM']=='conv') {
			$actionContext->onSuccess("MPX_CONV_INVIO", "DEFAULT");
			$actionContext->onError("MPX_CONV_INVIO", "DEFAULT");
		}
		else if($_REQUEST['FROM']=='mpx') {
			$actionContext->onSuccess("MPX_LIST", "DEFAULT");
			$actionContext->onError("MPX_LIST", "DEFAULT");
		}
	}

?>