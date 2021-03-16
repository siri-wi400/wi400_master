<?php

	if($actionContext->getForm() == "NUOVO") {
		global $db;
		$societa = $_REQUEST['SOCIETA'];
		$elemento = $_REQUEST['ELEMENTO'];
		$tabella = $_REQUEST['TABELLA'];
		
		// Se tabella parametri , usare il programma ad hoc
//		if ($tabella == "SYSPARAM") $messageContext->addMessage("ERROR", "Tabella parametri , usare Manutenzione parametri!");
	
		$key = getListKeyArray("MANUTENZIONE_TABELLA");
	
		$where = array("SOCIETA='$societa'",
				"TABELLA='$tabella'",
				"ELEMENTO='$elemento'");
		
		$query = "SELECT ELEMENTO FROM ZTABTABE WHERE ".implode(" AND ", $where);
		$rs = $db->query($query);
	
		//echo $query."<br/>";
	
		if($row = $db->fetch_array($rs)) {
			$messageContext->addMessage("ERROR", "Configurazione gi&agrave; esistente!");
		}
	
		$actionContext->onError("MANUTENZIONE_TABELLA", $actionContext->getForm(), "", "", true, true);
	}else if($actionContext->getForm() == "MODIFICA") {
		$societa = $_REQUEST['SOCIETA'];
		$nuovo_nome = $_REQUEST['ELEMENTO'];
		$tabella = $_REQUEST['TABELLA'];
		$key = getListKeyArray("MANUTENZIONE_TABELLA_LIST");
	
		if($key['ELEMENTO'] != $nuovo_nome) {
			$query = "SELECT ELEMENTO FROM ZTABTABE WHERE ELEMENTO='$nuovo_nome' AND SOCIETA='$societa' AND TABELLA='$tabella'";
			$rs = $db->singleQuery($query);
			if($row = $db->fetch_array($rs)) {
				$messageContext->addMessage("ERROR", "Nome parametro gi&agrave; usato!");
			}
		
			$actionContext->onError("MANUTENZIONE_TABELLA", $actionContext->getForm(), "", "", true, true);
		}
	}