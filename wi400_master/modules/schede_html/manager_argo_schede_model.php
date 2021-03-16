<?php

	require_once 'manager_argo_schede_common.php';

	$azione = $actionContext->getAction();
	
	if(in_array($actionContext->getForm(), array("DEFAULT", "DETAIL"))) {
		$history->addCurrent();
	}

//	echo "REQUEST:<pre>"; print_r($_REQUEST); echo "</pre>";

	if($actionContext->getForm() == "DEFAULT") {
		
	}
	else if($actionContext->getForm() == "DETAIL") {
		$actionContext->setLabel("Dettaglio tipo schede");
		
		$key  = getListKeyArray($azione."_ARGO");
	}
	else if($actionContext->getForm() == "NEW_ARGO") {
		$actionContext->setLabel("Nuovo argomento");
	}
	else if($actionContext->getForm() == "MOD_ARGO") {
		$key  = getListKeyArray($azione."_ARGO");
		$actionContext->setLabel("Modifica ".$key['FLD_ARGO']);
	}
	else if($actionContext->getForm() == "MOD_TIPO_SCHEDA") {
		$key_detail  = getListKeyArray($azione."_DETAIL");
//		echo "KEY_DETAIL:<pre>"; print_r($key_detail); echo "</pre>";
		
		$single = $key_detail['FLD_SINGLE'];
	}
	else if(in_array($actionContext->getForm(), array("INSERT_ARGO", "UPDATE_ARGO"))) {
		if($actionContext->getForm() == "INSERT_ARGO") {
			$fieldsName = array("FLD_ARGO", "FLD_DESC", "FLD_TYPE", '"USER"', "TMSMOD");
			$stmtinsert = $db->prepare("INSERT", "ZFLDARGD", null, $fieldsName);
			$fieldsValue = array($_REQUEST['FLD_ARGO'], $_REQUEST['FLD_DESC'], "****", $_SESSION['user'], getDb2Timestamp('*INZ'));
			
			$result = $db->execute($stmtinsert, $fieldsValue);
			
			$succ = "Argomento inserito con successo!";
			$error = "Errore inserimento nuovo argomento!";
		}
		else {
			$key = getListKeyArray($azione."_ARGO");
			$keysName    = array("FLD_ARGO" => $key['FLD_ARGO']);
			$fieldsName  = array("FLD_ARGO", "FLD_DESC", '"USER"', "TMSMOD");
			$stmtupdate  = $db->prepare("UPDATE", "ZFLDARGD", $keysName, $fieldsName);
			$fieldsValue = array($_REQUEST['FLD_ARGO'], $_REQUEST['FLD_DESC'], $_SESSION['user'], getDb2Timestamp());
			$result = $db->execute($stmtupdate, $fieldsValue);
			
			$succ = "Argomento modificato con successo!";
			$error = "Errore modifica argomento!";
		}
			
		if ($result){
			$messageContext->addMessage("SUCCESS", $succ);
		}
		else{
			$messageContext->addMessage("ERROR", $error);
		}
		
		$actionContext->gotoAction("CLOSE", "CLOSE_WINDOW_MSG", false, true);
		
	}
	else if(in_array($actionContext->getForm(), array("INSERT_TIPO_SCHEDA", "UPDATE_TIPO_SCHEDA"))) {
		$key = getListKeyArray($azione."_ARGO");
		
		$single = "";
		if(isset($_REQUEST['FLD_SINGLE']))
			$single = "S";
		
		if($actionContext->getForm() == "INSERT_TIPO_SCHEDA") {
			$fieldsName = array("FLD_ARGO", "FLD_TYPE", "FLD_TYPED", "FLD_ORDER", "FLD_USO", "FLD_SINGLE", '"USER"', "TMSMOD");
			$stmtinsert = $db->prepare("INSERT", "ZFLDARGD", null, $fieldsName);
			$fieldsValue = array($key['FLD_ARGO'], $_REQUEST['FLD_TYPE'], $_REQUEST['FLD_TYPED'], $_REQUEST['FLD_ORDER'], $_REQUEST['FLD_USO'], $single, $_SESSION['user'], getDb2Timestamp('*INZ'));
				
			$result = $db->execute($stmtinsert, $fieldsValue);
				
			$succ = "Tipo scheda inserita con successo!";
			$error = "Errore inserimento nuova scheda!";
		}
		else {
			$key_detail = getListKeyArray($azione."_DETAIL");
			$keysName    = array("FLD_ARGO" => $key['FLD_ARGO'], "FLD_TYPE" => $key_detail['FLD_TYPE']);
			$fieldsName  = array("FLD_TYPE", "FLD_TYPED", "FLD_ORDER", "FLD_USO", "FLD_SINGLE", '"USER"', "TMSMOD");
			$stmtupdate  = $db->prepare("UPDATE", "ZFLDARGD", $keysName, $fieldsName);
			$fieldsValue = array($_REQUEST['FLD_TYPE'], $_REQUEST['FLD_TYPED'], $_REQUEST['FLD_ORDER'], $_REQUEST['FLD_USO'], $single, $_SESSION['user'], getDb2Timestamp());
			$result = $db->execute($stmtupdate, $fieldsValue);
				
			$succ = "Scheda modificata con successo!";
			$error = "Errore modifica scheda!";
		}
			
		if ($result){
			$messageContext->addMessage("SUCCESS", $succ);
		}
		else{
			$messageContext->addMessage("ERROR", $error);
		}
		
		$actionContext->gotoAction("CLOSE", "CLOSE_WINDOW_MSG", false, true);
	}
	else if($actionContext->getForm() == "ELIMINA_ARGO") {
		$key = getListKeyArray($azione."_ARGO");
		
		$query = "DELETE FROM ZFLDARGD where FLD_ARGO='{$key['FLD_ARGO']}'";
		$result = $db->query($query);
		
		if ($result){
			$messageContext->addMessage("SUCCESS", "Argomento eliminato con successo!");
		}
		else{
			$messageContext->addMessage("ERROR", "Errore eliminazione argomento!");
		}
		
		$actionContext->gotoAction($azione, "DEFAULT", false, true);
		
	}
	else if($actionContext->getForm() == "ELIMINA_TIPO_SCHEDA") {
		$key = getListKeyArray($azione."_ARGO");
		$key_detail = getListKeyArray($azione."_DETAIL");
		
		$query = "DELETE FROM ZFLDARGD where FLD_ARGO='{$key['FLD_ARGO']}' and FLD_TYPE='{$key_detail['FLD_TYPE']}'";
		$result = $db->query($query);
		
		if ($result){
			$messageContext->addMessage("SUCCESS", "Tipo scheda eliminata con successo!");
		}
		else{
			$messageContext->addMessage("ERROR", "Errore eliminazione tipo scheda!");
		}
		
		$actionContext->gotoAction($azione, "DETAIL", false, true);
	}