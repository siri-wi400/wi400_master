<?php

	if($actionContext->getForm() == "NUOVO") {
		global $db;
		
		$parametro = $_REQUEST['PARAMETRO'];
	
		$key = getListKeyArray("MANUTENZIONE_PARAMETRI_PARAMETRI");
	
		$where = array("PARAMETRO='$parametro'");
	
		$query = "SELECT PARAMETRO FROM ZTABSETP WHERE ".implode(" AND ", $where);
		$rs = $db->query($query);
	
		//echo $query."<br/>";
	
		if($row = $db->fetch_array($rs)) {
			$messageContext->addMessage("ERROR", "Nome parametro gi&agrave; esistente!");
		}
	
		$actionContext->onError("GESTIONE_SETTINGS", $actionContext->getForm(), "", "", true, true);
	}else if($actionContext->getForm() == "MODIFICA") {
		$nuovo_nome = $_REQUEST['PARAMETRO'];
		$key = getListKeyArray("GESTIONE_SETTINGS_LIST");
	
		if($key['PARAMETRO'] != $nuovo_nome) {
			$query = "SELECT PARAMETRO FROM ZTABSETP WHERE PARAMETRO='$nuovo_nome'";
			$rs = $db->singleQuery($query);
			if($row = $db->fetch_array($rs)) {
				$messageContext->addMessage("ERROR", "Nome parametro gi&agrave; usato!");
			}
		
			$actionContext->onError("GESTIONE_SETTINGS", $actionContext->getForm(), "", "", true, true);
		}
	}