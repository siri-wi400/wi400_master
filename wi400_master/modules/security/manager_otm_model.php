<?php

	require_once 'manager_otm_common.php';
	
	$azione = $actionContext->getAction();
	
	$off = 1;
	if(!in_array($actionContext->getForm(), array("UPDT_OTM", "INS_OTM"))) {
		$off = 2;
		$history->addCurrent();
	}
	
	if($actionContext->getForm()=="DEFAULT") {
		wi400Detail::cleanSession($azione."_MOD_OTM_DET");
		wi400Detail::cleanSession($azione."_NEW_OTM_DET");
	}
	else if($actionContext->getForm()=="MOD_OTM") {
		$actionContext->setLabel("Dettaglio");
		
		$keyArray = array();
		$keyArray = getListKeyArray($azione."_LIST");
		
		$id_otm = $keyArray['OTMID'];
		$user = $keyArray['OTMUSR'];
		
		$sql = "select * from SIR_OTM where OTMID='$id_otm' and OTMUSR='$user'";
		$res = $db->singleQuery($sql);
		$row = $db->fetch_array($res);
	}
	else if($actionContext->getForm()=="NEW_OTM") {
		$actionContext->setLabel("Nuova OTM");
		
		$timeStamp = getDb2Timestamp();
	}
	else if($actionContext->getForm()=="UPDT_OTM") {
//		echo "POST:<pre>"; print_r($_POST); echo "</pre>";die("HERE");
		
		$keyArray = array();
		$keyArray = getListKeyArray($azione."_LIST");
		
		$id_otm = $keyArray['OTMID'];
		$user = $keyArray['OTMUSR'];
		$tipo = $keyArray['OTMTYP'];
				
		$keyUpdt = array("OTMID" => $id_otm, "OTMUSR" => $user);
				
		$fieldsValue = array();
		
		$new_id = trim($_POST['OTMID']);
		if(empty($new_id))
			$new_id = create_OTM($_POST['OTMUSR'], false);
		
		$fieldsValue['OTMID'] = $new_id;
		
		$fieldsValue['OTMUSR'] = $_POST['OTMUSR'];
		$fieldsValue['OTMEXP'] = $_POST['OTMEXP'];
		$fieldsValue['OTMSTA'] = $_POST['OTMSTA'];
		if($tipo!="STATIC")
			$fieldsValue['OTMTYP'] = $_POST['OTMTYP'];
		$fieldsValue['OTMCON'] = $_POST['OTMCON'];
		
		echo "UPDATE - FIELDS:<pre>"; print_r($fieldsValue); echo "</pre>";
		
		$stmt_updt = $db->prepare("UPDATE", "SIR_OTM", $keyUpdt, array_keys($fieldsValue));

		$result = $db->execute($stmt_updt, $fieldsValue);

		if(!$result)
			$messageContext->addMessage("ERROR","Errore durante la modifica dei dati dell'OTM");
		else {
			if($tipo=="STATIC" || $_POST['OTMTYP']=="STATIC") {
				// UPDATE White List per STATIC
				$otm = new wi400Otm();
				$otm->updateWitheList();
			}
			
			$messageContext->addMessage("SUCCESS","Modifica dei dati dell'OTM eseguita con successo");
			wi400Detail::cleanSession($azione."_MOD_OTM_DET");
		}
			
		$actionContext->onError($azione, "MOD_OTM", "", "", true);
		$actionContext->onSuccess($azione, "DEFAULT");
	}
	else if($actionContext->getForm()=="INS_OTM") {
		$fieldsValue = getDs("SIR_OTM");

		$stmt_ins = $db->prepare("INSERT", "SIR_OTM", null, array_keys($fieldsValue));
		
		$otm = new wi400Otm();
		
		$new_id = trim($_POST['OTMID']);
		$white_list = false;
		if(empty($new_id)) {
//			$new_id = create_OTM($_POST['OTMUSR'], false);

//			$result = $otm->getOtmPassword($_POST['OTMUSR'], $_POST['OTMTYP'], "ACTIVATE=S", "");
			
			if (isset($settings['private_key']) && $settings['private_key']!="") {
				$privateKey = $settings['private_key'];
			}
			else {
				$privateKey = $otm->get_defaultKey();
			}
			// Recupero chiave cifrata
			$new_id = $otm->create_key($privateKey);
		}
		else {
			$white_list = true;
		}
//		echo "NEW_ID: $new_id<br>";
			
		$fieldsValue['OTMID'] = $new_id;
		
		$fieldsValue['OTMUSR'] = $_POST['OTMUSR'];
		
//		$fieldsValue['OTMTIM'] = getDb2Timestamp();
		$fieldsValue['OTMTIM'] = $_POST['OTMTIM'];
		
//		$fieldsValue['OTMEXP'] = getDb2Timestamp();
		$fieldsValue['OTMEXP'] = $_POST['OTMEXP'];
		
		$fieldsValue['OTMSTA'] = $_POST['OTMSTA'];
		$fieldsValue['OTMTYP'] = $_POST['OTMTYP'];
		$fieldsValue['OTMCON'] = $_POST['OTMCON'];

		echo "INSERT - FIELDS:<pre>"; print_r($fieldsValue); echo "</pre>";

		$result = $db->execute($stmt_ins, $fieldsValue);
		
		if(!$result)
			$messageContext->addMessage("ERROR","Errore durante l'aggiunta dell'OTM");
		else {
			if($white_list===true) {
				// UPDATE White List per STATIC
//				$otm->updateWitheList();
			}
			
			$messageContext->addMessage("SUCCESS","Aggiunta dell'OTM eseguita con successo");
			wi400Detail::cleanSession($azione."_NEW_OTM_DET");
		}
		
		$actionContext->onError($azione, "NEW_OTM", "", "", true);
		$actionContext->onSuccess($azione, "DEFAULT");
	}
	else if($actionContext->getForm()=="DELETE_OTM") {
		$keyArray = array();
		$keyArray = getListKeyArray($azione."_LIST");
		
		$id_otm = $keyArray['OTMID'];
		$user = $keyArray['OTMUSR'];
		
		$keyDel = array("OTMID", "OTMUSR");
		
		$stmt_del = $db->prepare("DELETE", "SIR_OTM", $keyDel, null);
		
		$campi = array($id_otm, $user);
		
		$result = $db->execute($stmt_del, $campi);

		if(!$result)
			$messageContext->addMessage("ERROR","Errore durante l'eliminazione dell'OTM");
		else
			$messageContext->addMessage("SUCCESS","Eliminazione dell'OTM eseguita con successo");
			
		$actionContext->onError($azione, "MOD_OTM", "", "", true);
		$actionContext->onSuccess($azione, "DEFAULT");
	}
	else if($actionContext->getForm()=="UPDT_WHITE_LIST") {
		// UPDATE White List per STATIC
		$otm = new wi400Otm();
		$otm->updateWitheList();
		
		$messageContext->addMessage("SUCCESS","Aggiornata White List");
		$actionContext->onSuccess($azione, "DEFAULT");
	}