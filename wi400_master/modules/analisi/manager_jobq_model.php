<?php

	require_once 'manager_jobq_common.php';

	$azione = $actionContext->getAction();
	
	$off = 1;
	if(!in_array($actionContext->getForm(), array("UPDT_JOBQ", "INS_JOBQ"))) {
		$off = 2;
		$history->addCurrent();
	}
	
	if($actionContext->getForm()=="DEFAULT") {
		wi400Detail::cleanSession($azione."_MOD_JOBQ_DET");
		wi400Detail::cleanSession($azione."_NEW_JOBQ_DET");
	}
	else if($actionContext->getForm()=="MOD_JOBQ") {
		$actionContext->setLabel("Dettaglio");
		
		$keyArray = array();
		$keyArray = getListKeyArray($azione."_LIST");
		
		$id_jobq = $keyArray['ID'];
		$tipo = $keyArray['TIPO'];
		
		$sql = "select * from ZJOBQUEE where ID='$id_jobq' and TIPO='$tipo'";
		$res = $db->singleQuery($sql);
		$row = $db->fetch_array($res);
	}
	else if($actionContext->getForm()=="NEW_JOBQ") {
		$actionContext->setLabel("Nuovo JOBQ");
		
		$timeStamp = getDb2Timestamp();
	}
	else if($actionContext->getForm()=="UPDT_JOBQ") {
//		echo "POST:<pre>"; print_r($_POST); echo "</pre>";die("HERE");
		
		$keyArray = array();
		$keyArray = getListKeyArray($azione."_LIST");
		
		$id_jobq = $keyArray['ID'];
		$tipo = $keyArray['TIPO'];
				
		$keyUpdt = array("ID" => $id_jobq, "TIPO" => $tipo);
				
		$fieldsValue = array();
		
		$new_id = trim($_POST['ID']);
		
		$tipo = $_POST['TIPO'];
		
		$namobj = "";
		$libobj = "";
		$keyobj = "";
		$ipserv = "";
		$ipport = "";
		if(in_array($tipo, array("AS400", "DB"))) {
			$namobj = $_POST['NAMOBJ'];
			$libobj = $_POST['LIBOBJ'];
			$keyobj = $_POST['KEYOBJ'];
		}
		else if(in_array($tipo, array("REDIS"))) {
			$ipserv = $_POST['IPSERV'];
			$ipport = $_POST['IPPORT'];;
		}
		
		$fieldsValue['ID'] = $new_id;
		$fieldsValue['TIPO'] = $tipo;
//		$fieldsValue['LASTRUN'] = getDb2Timestamp("*INZ");
		$fieldsValue['STATUS'] = $_POST['STATUS'];
		$fieldsValue['NAMOBJ'] = $namobj;
		$fieldsValue['LIBOBJ'] = $libobj;
		$fieldsValue['KEYOBJ'] = $keyobj;
		$fieldsValue['IPSERV'] = $ipserv;
		$fieldsValue['IPPORT'] = $ipport;
		$fieldsValue['AZIGO'] = $_POST['AZIGO'];
		$fieldsValue['AZIRUN'] = $_POST['AZIRUN'];
		
//		echo "UPDATE - FIELDS:<pre>"; print_r($fieldsValue); echo "</pre>";
		
		$stmt_updt = $db->prepare("UPDATE", "ZJOBQUEE", $keyUpdt, array_keys($fieldsValue));

		$result = $db->execute($stmt_updt, $fieldsValue);

		if(!$result)
			$messageContext->addMessage("ERROR","Errore durante la modifica dei dati del JOBQ");
		else {
			$messageContext->addMessage("SUCCESS","Modifica dei dati del JOBQ eseguita con successo");
			wi400Detail::cleanSession($azione."_MOD_JOBQ_DET");
		}
			
		$actionContext->onError($azione, "MOD_JOBQ", "", "", true);
		$actionContext->onSuccess($azione, "DEFAULT");
	}
	else if($actionContext->getForm()=="INS_JOBQ") {
		$fieldsValue = getDs("ZJOBQUEE");

		$stmt_ins = $db->prepare("INSERT", "ZJOBQUEE", null, array_keys($fieldsValue));
		
		$otm = new wi400Otm();
		
		$new_id = trim($_POST['ID']);
		
		$tipo = $_POST['TIPO'];
		
		$namobj = "";
		$libobj = "";
		$keyobj = "";
		$ipserv = "";
		$ipport = "";
		if(in_array($tipo, array("AS400", "DB"))) {
			$namobj = $_POST['NAMOBJ'];
			$libobj = $_POST['LIBOBJ'];
			$keyobj = $_POST['KEYOBJ'];
		}
		else if(in_array($tipo, array("REDIS"))) {
			$ipserv = $_POST['IPSERV'];
			$ipport = $_POST['IPPORT'];;
		}
		
		$fieldsValue['ID'] = $new_id;
		$fieldsValue['TIPO'] = $tipo;
		$fieldsValue['LASTRUN'] = getDb2Timestamp("*INZ");
		$fieldsValue['STATUS'] = $_POST['STATUS'];
		$fieldsValue['NAMOBJ'] = $namobj;
		$fieldsValue['LIBOBJ'] = $libobj;
		$fieldsValue['KEYOBJ'] = $keyobj;
		$fieldsValue['IPSERV'] = $ipserv;
		$fieldsValue['IPPORT'] = $ipport;
		$fieldsValue['AZIGO'] = $_POST['AZIGO'];
		$fieldsValue['AZIRUN'] = $_POST['AZIRUN'];

//		echo "INSERT - FIELDS:<pre>"; print_r($fieldsValue); echo "</pre>";

		$result = $db->execute($stmt_ins, $fieldsValue);
		
		if(!$result)
			$messageContext->addMessage("ERROR","Errore durante l'aggiunta del JOBQ");
		else {
			$messageContext->addMessage("SUCCESS","Aggiunta del JOBQ eseguita con successo");
			wi400Detail::cleanSession($azione."_NEW_JOBQ_DET");
		}
		
		$actionContext->onError($azione, "NEW_JOBQ", "", "", true);
		$actionContext->onSuccess($azione, "DEFAULT");
	}
	else if($actionContext->getForm()=="DELETE_JOBQ") {
		$keyArray = array();
		$keyArray = getListKeyArray($azione."_LIST");
		
		$id_jobq = $keyArray['ID'];
		$tipo = $keyArray['TIPO'];
		
		$keyDel = array("ID", "TIPO");
		
		$stmt_del = $db->prepare("DELETE", "ZJOBQUEE", $keyDel, null);
		
		$campi = array($id_jobq, $tipo);
		
		$result = $db->execute($stmt_del, $campi);

		if(!$result)
			$messageContext->addMessage("ERROR","Errore durante l'eliminazione del JOBQ");
		else
			$messageContext->addMessage("SUCCESS","Eliminazione del JOBQ eseguita con successo");
			
		$actionContext->onError($azione, "MOD_JOBQ", "", "", true);
		$actionContext->onSuccess($azione, "DEFAULT");
	}