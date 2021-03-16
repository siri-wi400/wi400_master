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
	
	if($actionContext->getForm() == "DEFAULT" || $actionContext->getForm() == "UPDATE") {
		$stmt = $db->prepareStatement("SELECT * FROM FPDFCONV WHERE ID = ?");
		$db->execute($stmt, array($ID));
		$mpxArray = $db->fetch_array($stmt);
		
		if($actionContext->getForm() == "DEFAULT")
			$history->addCurrent();
	}
	else if($actionContext->getForm() == "SAVE") {
		/* Impostazione dei campi da modificare */
		$fields = array("MAIUSR", "MAIJOB", "MAINBR", "MAIEMA", "MAIMPX", "MAIFRM", "MAIALI", "MAISBJ", 
			"MAISTA", "MAIAMB", "MAIWDW", "MAILIB","MAIRIS", "MAIERR", "MAIDER", "MAIINS", "MAIELA");
		/* Impostazione della condizione WHERE */
		$keys = array("ID"=>$ID);
		/*
		 * Impostazione della query
		 * $db->prepare(<operazione>, <tabella>, <where>, <campi da modificare [set]>)
		 */		
		$stmt = $db->prepare("UPDATE", "FPDFCONV", $keys, $fields);
		/* Impostazione dei valori da assegnare ai campi */
		$campi = array($_POST['MAIUSR'], $_POST['MAIJOB'], $_POST['MAINBR'], $_POST['MAIEMA'], $_POST['MAIMPX'], 
			$_POST['MAIFRM'], $_POST['MAIALI'], $_POST['MAISBJ'], $_POST['MAISTA'], $_POST['MAIAMB'], 
			$_POST['MAIWDW'], $_POST['MAILIB'], $_POST['MAIRIS'], $_POST['MAIERR'], $_POST['MAIDER'],
			$_POST['MAIINS'], $_POST['MAIELA']);
		$result = $db->execute($stmt, $campi);

		if($result)
			$messageContext->addMessage("SUCCESS", "Aggiornamento record eseguito con successo");
		else
			$messageContext->addMessage("ERROR", "Si sono verificati degli errori nell'aggiornamento");
			
		$actionContext->onSuccess("MPX_CONV_INVIO_DET", "DEFAULT");
		$actionContext->onError("MPX_CONV_INVIO_DET", "DEFAULT");
	}
	else if($actionContext->getForm() == "INSERT") {
		$actionContext->setLabel("Inserimento conversione ed invio");
		
		$history->addCurrent();
	}
	else if($actionContext->getForm() == "SAVE_INS") {
		$fields = array("ID", "MAIUSR", "MAIJOB", "MAINBR", "MAIEMA", "MAIMPX", "MAIFRM", "MAIALI", "MAISBJ", 
			"MAISTA", "MAIAMB", "MAIWDW", "MAILIB","MAIRIS", "MAIERR", "MAIDER", "MAIINS", "MAIELA");
		$keys = array();
		$stmt = $db->prepare("INSERT", "FPDFCONV", $keys, $fields);
		$campi = array($_POST['ID'], $_POST['MAIUSR'], $_POST['MAIJOB'], $_POST['MAINBR'], $_POST['MAIEMA'], $_POST['MAIMPX'], 
			$_POST['MAIFRM'], $_POST['MAIALI'], $_POST['MAISBJ'], $_POST['MAISTA'], $_POST['MAIAMB'], 
			$_POST['MAIWDW'], $_POST['MAILIB'], $_POST['MAIRIS'], $_POST['MAIERR'], $_POST['MAIDER'],
			$_POST['MAIINS'], $_POST['MAIELA']);
		$result = $db->execute($stmt, $campi);
		
		if($result)
			$messageContext->addMessage("SUCCESS", "Inserimento record eseguito con successo");
		else
			$messageContext->addMessage("ERROR", "Si sono verificati degli errori nell'inserimento");
			
		$actionContext->onSuccess("MPX_CONV_INVIO_DET", "DEFAULT");
		$actionContext->onError("MPX_CONV_INVIO", "DEFAULT");
	}

?>