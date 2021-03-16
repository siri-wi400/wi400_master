<?php 

/*
 * MVC (Model-View-Controller) - File di model
 * Fornisce i metodi per accedere ai dati utili all'applicazione
 * Eventuali stampe a video (es: echo) verranno visualizzate sopra la pagina
 */

	/* Includere sempre il file delle funzioni comuni */
	require_once 'mpx_commons.php';

	$ID = "";
	$atc = "";
	
	$ID = getListKey("MPX_CONV_INVIO", 0);

	if($actionContext->getForm()!="INSERT") {
		/* Recupero della chiave passata */
		$ID = getListKey("MPX_ATC_LIST", 0);
		$atc = getListKey("MPX_ATC_LIST", 1);
	}
	
	if($actionContext->getForm() == "DEFAULT" || $actionContext->getForm() == "UPDATE") {
		/* Creazione di una query SQL */
		$statement = $db->prepareStatement("SELECT * FROM FEMAILAL WHERE ID= ? AND MAIATC= ?");
		$db->execute($statement, array($ID, $atc));
		$mpxArray = $db->fetch_array($statement);
		
		if($actionContext->getForm() == "DEFAULT")
			$history->addCurrent();
	}
	else if($actionContext->getForm() == "SAVE") {
		$fields = array("MAIATC", "MAIPAT", "CONV", "TPCONV", "MAIMOD", "MAIARG", "MAINAM", "FILZIP");
		$keys = array("ID"=>$ID, "MAIATC"=>$atc);	
		$stmt = $db->prepare("UPDATE", "FEMAILAL", $keys, $fields);
		$campi = array($_POST['MAIATC'], $_POST['MAIPAT'], $_POST['CONV'], $_POST['TPCONV'], $_POST['MAIMOD'],
			$_POST['MAIARG'], $_POST['MAINAM'], $_POST['FILZIP']);
		$result = $db->execute($stmt, $campi);

		if($result)
			$messageContext->addMessage("SUCCESS", "Aggiornamento record eseguito con successo");
		else
			$messageContext->addMessage("ERROR", "Si sono verificati degli errori nell'aggiornamento");

//		$actionContext->onSuccess("MPX_ATC_DETAIL", "DEFAULT");
		$actionContext->onSuccess("MPX_ATC_LIST", "DEFAULT");
		$actionContext->onError("MPX_ATC_DETAIL", "DEFAULT");
	}
/*	else if($actionContext->getForm() == "DELETE") {
        $sql = "DELETE FROM FEMAILAL WHERE ID='$ID' AND MAIATC='$atc'";
        $result = $db->query($sql); 

       	if($result) 
       		$messageContext->addMessage("SUCCESS", "Cancellazione del record eseguita");
	    else
	    	$messageContext->addMessage("ERROR", "Il record ID non è stato cancellato");
	    	
	    $actionContext->onSuccess("MPX_ATC_LIST", "DEFAULT");
    	$actionContext->onError("MPX_ATC_DETAIL", "DEFAULT");
	}
*/	else if($actionContext->getForm() == "INSERT") {
//		$ID = $_REQUEST['ID'];
	
		$actionContext->setLabel("Inserimento allegato");
		
		$history->addCurrent();
	}
	else if($actionContext->getForm() == "SAVE_INS") {
		$fields = array("ID", "MAIATC", "MAIPAT", "CONV", "TPCONV", "MAIMOD", "MAIARG", "MAINAM", "FILZIP");
		$keys = array();
		$stmt = $db->prepare("INSERT", "FEMAILAL", $keys, $fields);
		$campi = array($_POST['ID'], $_POST['MAIATC'], $_POST['MAIPAT'], $_POST['CONV'], $_POST['TPCONV'], 
			$_POST['MAIMOD'], $_POST['MAIARG'], $_POST['MAINAM'], $_POST['FILZIP']);
		$result = $db->execute($stmt, $campi);
		
		if($result)
			$messageContext->addMessage("SUCCESS", "Inserimento record eseguito con successo");
		else
			$messageContext->addMessage("ERROR", "Si sono verificati degli errori nell'inserimento");

//		$actionContext->onSuccess("MPX_ATC_DETAIL", "DEFAULT");
		$actionContext->onSuccess("MPX_ATC_LIST", "DEFAULT");
		$actionContext->onError("MPX_ATC_LIST", "DEFAULT");
	}

?>