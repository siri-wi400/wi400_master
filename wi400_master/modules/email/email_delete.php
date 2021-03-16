<?php

	echo "DELETE<br>";
	
	$azione = "MONITOR_EMAIL";

	if($actionContext->getForm()=="DELETE_EMAIL") {
		$idList = "MONITOR_EMAIL_LIST";
		echo "IDLIST: $idList<br>";
	
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
	
		$rowsSelectionArray = $wi400List->getSelectionArray();
	
		$keyDel = array("ID");
	
		$stmt_del = $db->prepare("DELETE", "FPDFCONV", $keyDel, null);
	
		$errors = false;
		foreach($rowsSelectionArray as $key => $value){
			$keyArray = array();
			$keyArray = explode("|",$key);
				
//			$id = $keyArray[1];

			$keyArray = get_list_keys_num_to_campi($wi400List, $keyArray);
			
			$id = $keyArray["ID"];
				
			$campi = array($id);
				
			$res = $db->execute($stmt_del, $campi);
				
			if(!$res)
				$errors = true;
		}
	
		if($errors===true)
			$messageContext->addMessage("ERROR","Errore durante l'eliminazine delle e-mails");
		else
			$messageContext->addMessage("SUCCESS","Eliminazione delle e-mails eseguita con successo");
	
		$actionContext->onSuccess($azione, "EMAIL_LIST");
		$actionContext->onError($azione, "EMAIL_LIST", "", "", true);
	}
	else if($actionContext->getForm()=="DELETE_ATC") {
		$idList = "MONITOR_EMAIL_ATC_LIST";
		echo "IDLIST: $idList<br>";

		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);

		$rowsSelectionArray = $wi400List->getSelectionArray();

		$keyDel = array("ID", "MAIATC");

		$stmt_del = $db->prepare("DELETE", "FEMAILAL a", $keyDel, null);

		$errors = false;
		foreach($rowsSelectionArray as $key => $value){
			$keyArray = array();
			$keyArray = explode("|",$key);
/*	
			$id = $keyArray[1];
			$atc = $keyArray[2];
*/
			$keyArray = get_list_keys_num_to_campi($wi400List, $keyArray);
				
			$id = $keyArray["ID"];
			$atc = $keyArray["MAIATC"];
			
			$campi = array($id, $atc);
	
			$res = $db->execute($stmt_del, $campi);
	
			if(!$res)
				$errors = true;
		}

		if($errors===true)
			$messageContext->addMessage("ERROR","Errore durante l'eliminazine degli allegati");
		else
			$messageContext->addMessage("SUCCESS","Eliminazione degli allegati eseguita con successo");

		$actionContext->onSuccess($azione, "ATC_LIST");
		$actionContext->onError($azione, "ATC_LIST", "", "", true);
	}
	else if($actionContext->getForm()=="DELETE_DEST") {
		$idList = "MONITOR_EMAIL_DEST_LIST";
		echo "IDLIST: $idList<br>";

		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);

		$rowsSelectionArray = $wi400List->getSelectionArray();

		$keyDel = array("ID", "MAITOR");

		$stmt_del = $db->prepare("DELETE", "FEMAILDT a", $keyDel, null);

		$errors = false;
		foreach($rowsSelectionArray as $key => $value){
			$keyArray = array();
			$keyArray = explode("|",$key);
/*
			$id = $keyArray[1];
			$to = $keyArray[2];
*/			
			$keyArray = get_list_keys_num_to_campi($wi400List, $keyArray);
			
			$id = $keyArray["ID"];
			$to = $keyArray["MAITOR"];

			$campi = array($id, $to);

			$res = $db->execute($stmt_del, $campi);

			if(!$res)
				$errors = true;
		}

		if($errors===true)
			$messageContext->addMessage("ERROR","Errore durante l'eliminazine dei destinatari");
		else
			$messageContext->addMessage("SUCCESS","Eliminazione dei destinatari eseguita con successo");

		$actionContext->onSuccess($azione, "DEST_LIST");
		$actionContext->onError($azione, "DEST_LIST", "", "", true);
	}
	else if($actionContext->getForm()=="DELETE_CONTENTS") {
		$idList = "MONITOR_EMAIL_CONTENTS_LIST";
		echo "IDLIST: $idList<br>";
	
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
	
		$rowsSelectionArray = $wi400List->getSelectionArray();
	
		$keyDel = array("ID", "UCTTYP");
	
		$stmt_del = $db->prepare("DELETE", "FEMAILCT a", $keyDel, null);
	
		$errors = false;
		foreach($rowsSelectionArray as $key => $value){
			$keyArray = array();
			$keyArray = explode("|",$key);
/*		
			$id = $keyArray[1];
			$tipo = $keyArray[2];
*/			
			$keyArray = get_list_keys_num_to_campi($wi400List, $keyArray);
			
			$id = $keyArray["ID"];
			$tipo = $keyArray["UCTTYP"];
		
			$campi = array($id, $tipo);
		
			$res = $db->execute($stmt_del, $campi);
		
			if(!$res)
				$errors = true;
		}
	
		if($errors===true)
			$messageContext->addMessage("ERROR","Errore durante l'eliminazine dei contenuti");
		else
			$messageContext->addMessage("SUCCESS","Eliminazione dei contenuti eseguita con successo");
	
		$actionContext->onSuccess($azione, "CONTENTS_LIST");
		$actionContext->onError($azione, "CONTENTS_LIST", "", "", true);
	}
	else if($actionContext->getForm()=="DELETE_MPX") {
		$idList = "MONITOR_EMAIL_LIST";
		echo "IDLIST: $idList<br>";
		
		$keyArray = array();
		$keyArray = getListKeyArray($idList);
		
		$id = $keyArray['ID'];
		
		$keyDel = array("ID");
	
		$stmt_del = $db->prepare("DELETE", "FMPXPARM", $keyDel, null);
	
		$campi = array($id);
			
		$res = $db->execute($stmt_del, $campi);
			
		if(!$res)
			$messageContext->addMessage("ERROR","Errore durante l'eliminazine delle impostazioni MPX");
		else
			$messageContext->addMessage("SUCCESS","Eliminazione delle impostazioni MPX eseguita con successo");
	
		$actionContext->onSuccess($azione, "EMAIL_LIST");
		$actionContext->onError($azione, "EMAIL_LIST", "", "", true);
	}