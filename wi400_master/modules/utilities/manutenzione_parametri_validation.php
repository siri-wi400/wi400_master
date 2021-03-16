<?php

	if($actionContext->getForm() == "NUOVA_CONFIGURAZIONE") {
		global $db;
		$societa = $_REQUEST['SOCIETA'];
		$sito = $_REQUEST['SITO'];
		$deposito = $_REQUEST['DEPOSITO'];
		$interlocutore = $_REQUEST['INTERLOCUTORE'];
		if(!isset($_REQUEST['VALORE'])) {
			$_REQUEST['VALORE'] = 0;
		}
		$valore = $_REQUEST['VALORE'];
		
		$key = getListKeyArray("MANUTENZIONE_PARAMETRI_PARAMETRI");
		
		$where = array("SOCIETA='$societa'",
						"SITO='$sito'",
						"DEPOSITO='$deposito'",
						"INTERLOCUTORE='$interlocutore'",
						"PARAMETRO='{$key['ELEMENTO']}'");
		
		$query = "SELECT PARAMETRO FROM ZSYSPARM WHERE ".implode(" AND ", $where);
		$rs = $db->query($query);
		
		//echo $query."<br/>";

		if($row = $db->fetch_array($rs)) {
			$messageContext->addMessage("ERROR", "Configurazione gi&agrave; esistente!");
		}
		
		$actionContext->onError("OP_MANUTENZIONE_PARAMETRI", $actionContext->getForm(), "", "", true, true);
	}/*else if($actionContext->getForm() == "MODIFICA_PARAMETRO") {
		$nuovo_nome = $_REQUEST['NOME'];
		
		$query = "SELECT PARAMETRO FROM ZSYSPARM WHERE PARAMETRO='$nuovo_nome'";
		$rs = $db->singleQuery($query);
		if($row = $db->fetch_array($rs)) {
			$messageContext->addMessage("ERROR", "Nome parametro gi&agrave; usato!");
		}
		
		$actionContext->onError("MANUTENZIONE_PARAMETRI", $actionContext->getForm(), "", "", true, true);
	}*/