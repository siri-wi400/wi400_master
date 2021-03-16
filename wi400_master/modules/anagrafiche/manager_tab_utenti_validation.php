<?php
	$stato = '0';
	if(isset($_REQUEST['SEASTA']) && $_REQUEST['SEASTA']) {
		$stato = '1';
	}
	if(isset($_REQUEST['SECSTA']) && $_REQUEST['SECSTA']) {
		$stato = '1';
	}
	if(isset($_REQUEST['SEDSTA']) && $_REQUEST['SEDSTA']) {
		$stato = '1';
	}
	
	if($actionContext->getForm() == "NEW_UTENTE") {
		//showArray($_REQUEST);
		$sql = "SELECT seausr FROM fseauser where seausr='".$_REQUEST['SEAUSR']."'";
		$res = $db->singleQuery($sql);
		if($row = $db->fetch_array($res)) {
			$messageContext->addMessage("ERROR","L'utente ".$_REQUEST['SEAUSR']." &egrave; gi&agrave; presente nella tabella");
		}
	}
	
	if($actionContext->getForm() == "NEW_ENTI") {
		$key_utenti = getListKeyArray($azione."_UTENTI_LIST");
				
		$sql = "SELECT secusr FROM fsecenti where secusr='".$_REQUEST['SECUSR']."' AND seccde='{$_REQUEST['SECCDE']}' AND secsoc='{$_REQUEST['SECSOC']}'";

		$res = $db->singleQuery($sql);
		if($row = $db->fetch_array($res)) {
			$messageContext->addMessage("ERROR","L'entit&agrave; con codice PDV ".$_REQUEST['SECCDE']." &egrave; gi&agrave; presente nella tabella");
		}
	}
	
	if($actionContext->getForm() == "NEW_REPARTO") {
		$key_utenti = getListKeyArray($azione."_UTENTI_LIST");
	
		$sql = "SELECT sedusr FROM fsedrepa where sedsoc='{$_REQUEST['SEDSOC']}' AND sedrep='{$_REQUEST['SEDREP']}'";
	
		$res = $db->singleQuery($sql);
		if($row = $db->fetch_array($res)) {
			$messageContext->addMessage("ERROR","Il reparto con codice ".$_REQUEST['SEDREP']." &egrave; gi&agrave; stato assegnato per la societ&agrave; ".$_REQUEST['SEDSOC']);
		}
	}