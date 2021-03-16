<?php

	if($actionContext->getForm() == "NUOVO") {
		global $db;
		$societa = $_REQUEST['SOCIETA'];
		$elemento = $_REQUEST['ELEMENTO'];
	
		$key = getListKeyArray("MANUTENZIONE_PARAMETRI_PARAMETRI");
	
		$where = array("SOCIETA='$societa'",
				"TABELLA='SYSPARAM'",
				"ELEMENTO='$elemento'");
	
		$query = "SELECT ELEMENTO FROM ZTABTABE WHERE ".implode(" AND ", $where);
		$rs = $db->query($query);
	
		//echo $query."<br/>";
	
		if($row = $db->fetch_array($rs)) {
			$messageContext->addMessage("ERROR", "Configurazione gi&agrave; esistente!");
		}
	
		$actionContext->onError("GESTIONE_PARAMETRI", $actionContext->getForm(), "", "", true, true);
	}else if($actionContext->getForm() == "MODIFICA") {
		$societa = $_REQUEST['SOCIETA'];
		$nuovo_nome = $_REQUEST['ELEMENTO'];
		$key = getListKeyArray("GESTIONE_PARAMETRI_LIST");
	
		if($key['ELEMENTO'] != $nuovo_nome) {
			$query = "SELECT ELEMENTO FROM ZTABTABE WHERE ELEMENTO='$nuovo_nome' AND SOCIETA='$societa' AND TABELLA='SYSPARAM'";
			$rs = $db->singleQuery($query);
			if($row = $db->fetch_array($rs)) {
				$messageContext->addMessage("ERROR", "Nome parametro gi&agrave; usato!");
			}
		
			$actionContext->onError("GESTIONE_PARAMETRI", $actionContext->getForm(), "", "", true, true);
		}
	}