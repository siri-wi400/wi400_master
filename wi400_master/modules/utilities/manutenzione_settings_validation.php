<?php

	if($actionContext->getForm() == "NUOVA_CONFIGURAZIONE") {

		$chiave = $_REQUEST['CHIAVE'];
		$ambiente = strtoupper(substr($appBase, 1, -1));
		$pgr = $_REQUEST['PROGRESSIVO'];
		
		if(!isset($_REQUEST['VALORE'])) {
			$_REQUEST['VALORE'] = 0;
		}
		$valore = $_REQUEST['VALORE'];
		
		$key = getListKeyArray("MANUTENZIONE_SETTINGS_PARAMETRI");
		
		$where = array(
					"AMBIENTE='$ambiente'",
					"CHIAVE='$chiave'", 
					"PARAMETRO='{$key['PARAMETRO']}'");
		
		$query = "SELECT PARAMETRO FROM ZTABSETV WHERE ".implode(" AND ", $where);
		$rs = $db->query($query);
		
		//echo $query."<br/>";

		if($row = $db->fetch_array($rs)) {
			$messageContext->addMessage("ERROR", "Chiave gi&agrave; esistente!");
		}
		
		$actionContext->onError("MANUTENZIONE_SETTINGS", $actionContext->getForm(), "", "", true, true);
	}/*else if($actionContext->getForm() == "MODIFICA_PARAMETRO") {
		$nuovo_nome = $_REQUEST['NOME'];
		
		$query = "SELECT PARAMETRO FROM ZSYSPARM WHERE PARAMETRO='$nuovo_nome'";
		$rs = $db->singleQuery($query);
		if($row = $db->fetch_array($rs)) {
			$messageContext->addMessage("ERROR", "Nome parametro gi&agrave; usato!");
		}
		
		$actionContext->onError("MANUTENZIONE_PARAMETRI", $actionContext->getForm(), "", "", true, true);
	}*/