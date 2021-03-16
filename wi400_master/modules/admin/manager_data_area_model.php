<?php

	require_once $routine_path."/os400/wi400Os400Object.cls.php";
	
	$azione = $actionContext->getAction();
	
	$off = 1;
	if(!in_array($actionContext->getForm(), array("UPDATE", "INSERT", "DELETE"))) {
		$off = 2;
		$history->addCurrent();
	}
	
	$steps = $history->getSteps();
//	echo "STEPS:<pre>"; print_r($steps); echo "</pre>";
	
	$array_steps = get_history_steps($off, $steps);
	
	$first_action = $array_steps['FIRST_ACTION'];
	$first_form = $array_steps['FIRST_FORM'];
	
	$last_action = $array_steps['LAST_ACTION'];
	$last_form = $array_steps['LAST_FORM'];
//	echo "LAST_ACTION: $last_action - LAST FORM: $last_form<br>";
	
//	echo "REQUEST:<pre>"; print_r($_REQUEST); echo "</pre>";
//	echo "DETAIL:<pre>"; print_r(wi400Detail::getDetailValues($azione."_SRC")); echo "</pre>";

	if($actionContext->getForm()=="DEFAULT") {
		wi400Detail::cleanSession($azione."_DETAIL");
		wi400Detail::cleanSession($azione."_NEW_DETAIL");
		
		subfileDelete($azione."_LIST");
		
		$sql = "select * from ZDTATABE";
		
		$subfile = new wi400Subfile($db, $azione."_LIST", $settings['db_temp'], 20);
		$subfile->setConfigFileName("MANAGER_DATA_AREA_LIST");
		$subfile->setModulo("admin");
		
		$subfile->setSql($sql);
	}
	else if($actionContext->getForm()=="NEW_DETAIL") {
		$actionContext->setLabel("Nuovo");
	}
	else if($actionContext->getForm()=="DETAIL") {
		$actionContext->setLabel("Dettaglio");
		
		$keyArray = array();
		$keyArray = getListKeyArray($azione."_LIST");
		
		$dta = $keyArray['DTANAM'];
		$dta_lib = $keyArray['DTALIB'];
		
		$sql = "select * from ZDTATABE a where DTANAM='$dta'";
		$sql .= " and DTALIB='$dta_lib'";
//		echo "SQL: $sql<br>";
		
		$result = $db->singleQuery($sql);
		
		if($result) {
			$row = $db->fetch_array($result);
		}
	}
	else if($actionContext->getForm()=="INSERT") {
/*		
		$sql = "select * from ZDTATABE a where DTANAM='".$_POST['DTANAM']."'";
//		echo "SQL: $sql<br>";
		
		$result = $db->singleQuery($sql);
		
		if($row = $db->fetch_array($result)) {
			$messageContext->addMessage("ERROR","Data Area già presente");
		}
		else {
*/			$fieldsValue = array(
				"DTANAM" => $_POST['DTANAM'],
				"DTALIB" => $_POST['DTALIB'],
				"DTADS" => $_POST['DTADS'],
				"DTADSL" => $_POST['DTADSL'],
			);
			
//			echo "UPDATE - FIELDS:<pre>"; print_r($fieldsValue); echo "</pre>";
			
			$stmt_ins = $db->prepare("INSERT", "ZDTATABE", null, array_keys($fieldsValue));
			
			$result = $db->execute($stmt_ins, $fieldsValue);
			
			if(!$result)
				$messageContext->addMessage("ERROR","Errore durante l'inserimento dei dati");
			else
				$messageContext->addMessage("SUCCESS","Inserimento dei dati eseguito con successo");
//		}
		
		$actionContext->onError($azione, "NEW_DETAIL", "", "", true);
		$actionContext->onSuccess($azione, "DEFAULT");
	}
	else if($actionContext->getForm()=="UPDATE") {
		$keyArray = array();
		$keyArray = getListKeyArray($azione."_LIST");
		
		$dta = $keyArray['DTANAM'];
		$dta_lib = $keyArray['DTALIB'];
		
		$keyUpdt = array("DTANAM" => $dta, "DTALIB" => $dta_lib);
		
		$fieldsValue = array(
			"DTADS" => $_POST['DTADS'],
			"DTADSL" => $_POST['DTADSL'],
		);
		
//		echo "UPDATE - FIELDS:<pre>"; print_r($fieldsValue); echo "</pre>";
		
		$stmt_updt = $db->prepare("UPDATE", "ZDTATABE", $keyUpdt, array_keys($fieldsValue));
		
		$result = $db->execute($stmt_updt, $fieldsValue);
		
		if(!$result)
			$messageContext->addMessage("ERROR","Errore durante la modifica dei dati");
		else
			$messageContext->addMessage("SUCCESS","Modifica dei dati eseguita con successo");
		
		$actionContext->onError($azione, "DETAIL", "", "", true);
		$actionContext->onSuccess($azione, "DEFAULT");
	}
	else if($actionContext->getForm()=="DELETE") {
		$keyArray = array();
		$keyArray = getListKeyArray($azione."_LIST");
		
		$dta = $keyArray['DTANAM'];
		$dta_lib = $keyArray['DTALIB'];
				
		$keyDel = array("DTANAM", "DTALIB");
		
		$stmt_del = $db->prepare("DELETE", "ZDTATABE", $keyDel, null);		
		
		$fieldsValue = array($dta, $dta_lib);
		
		$result = $db->execute($stmt_del, $fieldsValue);
		
		if(!$result)
			$messageContext->addMessage("ERROR","Errore durante l'eliminazione dei dati");
		else
			$messageContext->addMessage("SUCCESS","Eliminazione dei dati eseguito con successo");
		
		$actionContext->onError($azione, $last_form, "", "", true);
		$actionContext->onSuccess($azione, "DEFAULT");
	}