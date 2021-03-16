<?php

	require_once 'manager_tab_utenti_common.php';
	
	$azione = $actionContext->getAction();
	
	if(in_array($actionContext->getForm(), array("NEW_ENTI", "MOD_ENTI", "REPARTO_LIST", "NEW_REPARTO", "MOD_REPARTO"))) {
		$key_utenti = getListKeyArray($azione."_UTENTI_LIST");
	}
	
	if($actionContext->getForm()=="DEFAULT") {
		$history->addCurrent();
	}
	else if($actionContext->getForm()=="NEW_UTENTE") {
		//$keyArray = getListKeyArray($azione."_ENTI_LIST");
		$actionContext->setLabel("Nuovo Utente");
	}
	else if($actionContext->getForm()=="MOD_UTENTE") {
		$keyArray = getListKeyArray($azione."_UTENTI_LIST");
		$actionContext->setLabel("Modifica Utente");
	}
	else if(in_array($actionContext->getForm(), array("INS_UTENTE", "UPDT_UTENTE"))) {
		$date = date("ymd");
		$hour = date("His");
		
		//Reperisco i decimi di secondo dall'ora
		list($whole, $decimal) = explode('.', "".round(microtime(true), 3));
		$hour .= $decimal;
		
		$form = "NEW_UTENTE";
		if($actionContext->getForm()=="UPDT_UTENTE") {
			$keyUpdt = array("SEAUSR" => $_REQUEST['SEAUSR']);
				
			$fieldsValue = array();
			
			//Il $stato arriva dal validation
			$fieldsValue['SEASOC'] = $_REQUEST['SEASOC'];
			$fieldsValue['SEASTA'] = $stato; // $stato arriva dal validation.php
			$fieldsValue['SEADMO'] = $date;
			$fieldsValue['SEAHMO'] = $hour;
			$fieldsValue['SEAWHO'] = $_SESSION['user'];
				
			$stmt_updt = $db->prepare("UPDATE", "FSEAUSER", $keyUpdt, array_keys($fieldsValue));
	
			$res = $db->execute($stmt_updt, $fieldsValue);
				
			if(!$res) {
				$messageContext->addMessage("ERROR","Errore durante la modifica dell'utente");
			}else {
				$messageContext->addMessage("SUCCESS","Modifica dell'utente eseguita con successo");
			}
			
			$form = "MOD_UTENTE";
		}else {
			$values = array();
			$values['SEAUSR'] = $_REQUEST['SEAUSR'];
			$values['SEASOC'] = $_REQUEST['SEASOC'];
			$values['SEAAVA'] = date('Y');
			$values['SEAMVA'] = date('m');
			$values['SEAGVA'] = date('d');
			$values['SEAAFV'] = "9999";
			$values['SEAMFV'] = "99";
			$values['SEAGFV'] = "99";
			$values['SEASTA'] = $stato; // $stato arriva dal validation.php
			$values['SEAST1'] = '1';
			$values['SEAST2'] = '1';
			$values['SEADMO'] = $date;
			$values['SEAHMO'] = $hour;
			$values['SEAWHO'] = $_SESSION['user'];
			$values['SEATCK'] = '';
			$values['SEANPD'] = '';
			
			$stmtDoc = $db->prepare("INSERT", "FSEAUSER", null, array_keys($values));
			$res = $db->execute($stmtDoc, $values);
			
			if(!$res) {
				$messageContext->addMessage("ERROR","Errore inserimento nuovo utente");
			}else {
				$messageContext->addMessage("SUCCESS","Nuovo utente aggiunto con successo");
			}
		}
		
		$actionContext->onError($azione, $form, "", "", true);
		$actionContext->onSuccess("CLOSE", "CLOSE_WINDOW");
	}
	else if($actionContext->getForm()=="SOC_LIST") {
		$history->addCurrent();
		$actionContext->setLabel("Dettaglio utente");
	}
	else if($actionContext->getForm()=="DELETE_SOC") {
		$keyArray = getListKeyArray($azione."_SOC_LIST");
		
		$sql = "DELETE FROM FSEBANAG WHERE SEBCDA='".$keyArray[0]."'";
		$res = $db->query($sql);
		
		if(!$res) {
			$messageContext->addMessage("ERROR","Errore eliminazione articolo");
		}else {
			$messageContext->addMessage("SUCCESS","Articolo eliminato con successo");
		}
		
		$actionContext->onError($azione, $form, "", "", true);
		$actionContext->onSuccess($azione, "SOC_LIST");
	}
	else if($actionContext->getForm()=="ENTI_LIST") {
		$history->addCurrent();
		$actionContext->setLabel("Dettaglio entit&agrave;");
	}
	else if($actionContext->getForm()=="NEW_ENTI") {
		$actionContext->setLabel("Nuova entit&agrave;");
	}
	else if($actionContext->getForm()=="MOD_ENTI") {
		$actionContext->setLabel("Modifica entit&agrave;");
		$chiavi = getListKeyArray($azione."_ENTI_LIST");
	}
	else if(in_array($actionContext->getForm(), array("INS_ENTI", "UPDT_ENTI"))) {
		$date = date("ymd");
		$hour = date("His");
	
		//Reperisco i decimi di secondo dall'ora
		list($whole, $decimal) = explode('.', "".round(microtime(true), 3));
		$hour .= $decimal;
	
		$form = "NEW_ENTI";
		if($actionContext->getForm()=="UPDT_ENTI") {
			$keyUpdt = array("SECUSR" => $_REQUEST['SECUSR'],
							"SECSOC" => $_REQUEST['SECSOC'],
							"SECCDE" => $_REQUEST['SECCDE']);
	
			$fieldsValue = array();
				
			$fieldsValue['SECSTA'] = $stato; // $stato arriva dal validation.php
			$fieldsValue['SECDMO'] = $date;
			$fieldsValue['SECHMO'] = $hour;
			$fieldsValue['SECWHO'] = $_SESSION['user'];
	
			$stmt_updt = $db->prepare("UPDATE", "FSECENTI", $keyUpdt, array_keys($fieldsValue));
	
			$res = $db->execute($stmt_updt, $fieldsValue);
	
			if(!$res) {
				$messageContext->addMessage("ERROR","Errore durante la modifica dell'entit&agrave;");
			}else {
				$messageContext->addMessage("SUCCESS","Modifica dell'entit&agrave; eseguita con successo");
			}
				
			$form = "MOD_ENTI";
		}else {
			$values = array();
			$values['SECUSR'] = $_REQUEST['SECUSR'];
			$values['SECCDE'] = $_REQUEST['SECCDE'];
			$values['SECSOC'] = $_REQUEST['SECSOC'];
			$values['SECAVA'] = date('Y');
			$values['SECMVA'] = date('m');
			$values['SECGVA'] = date('d');
			$values['SECAFV'] = "9999";
			$values['SECMFV'] = "99";
			$values['SECGFV'] = "99";
			$values['SECSTA'] = $stato; // $stato arriva dal validation.php
			$values['SECST1'] = '1';
			$values['SECST2'] = '1';
			$values['SECDMO'] = $date;
			$values['SECHMO'] = $hour;
			$values['SECWHO'] = $_SESSION['user'];
			$values['SECTCK'] = '';
			$values['SECNPD'] = '';
				
			$stmtDoc = $db->prepare("INSERT", "FSECENTI", null, array_keys($values));
			$res = $db->execute($stmtDoc, $values);
				
			if(!$res) {
				$messageContext->addMessage("ERROR","Errore inserimento nuova entit&agrave;");
			}else {
				$messageContext->addMessage("SUCCESS","Nuova entit&agrave; aggiunta con successo");
			}
		}
	
		$actionContext->onError($azione, $form, "", "", true);
		$actionContext->onSuccess("CLOSE", "CLOSE_WINDOW");
	}
	else if($actionContext->getForm() == "REPARTO_LIST") {
		$history->addCurrent();
		$actionContext->setLabel("Dettaglio reparto");
		
		$key_enti = getListKeyArray($azione."_ENTI_LIST");
	}
	else if($actionContext->getForm()=="NEW_REPARTO") {
		$actionContext->setLabel("Nuovo reparto");
		$key_enti = getListKeyArray($azione."_ENTI_LIST");
	}
	else if($actionContext->getForm()=="MOD_REPARTO") {
		$actionContext->setLabel("Modifica reparto");
		$key_enti = getListKeyArray($azione."_ENTI_LIST");
		$key_rep = getListKeyArray($azione."_REPARTO_LIST");
	}
	else if(in_array($actionContext->getForm(), array("INS_REPARTO", "UPDT_REPARTO"))) {
		$key_rep = getListKeyArray($azione."_REPARTO_LIST");
		
		showArray($key_rep);
		
		$date = date("ymd");
		$hour = date("His");
	
		//Reperisco i decimi di secondo dall'ora
		list($whole, $decimal) = explode('.', "".round(microtime(true), 3));
		$hour .= $decimal;
	
		$form = "NEW_REPARTO";
		if($actionContext->getForm()=="UPDT_REPARTO") {
			$keyUpdt = array("SEDUSR" => $_REQUEST['SEDUSR'],
					"SEDSOC" => $_REQUEST['SEDSOC'],
					"SEDCDE" => $_REQUEST['SEDCDE'],
					"SEDREP" => $key_rep['SEDREP']);
	
			$fieldsValue = array();
			$fieldsValue['SEDREP'] = $_REQUEST['SEDREP'];
			$fieldsValue['SEDDMO'] = $date;
			$fieldsValue['SEDSTA'] = $stato; // $stato arriva dal validation.php
			$fieldsValue['SEDDMO'] = $date;
			$fieldsValue['SEDHMO'] = $hour;
			$fieldsValue['SEDWHO'] = $_SESSION['user'];
	
			$stmt_updt = $db->prepare("UPDATE", "FSEDREPA", $keyUpdt, array_keys($fieldsValue));
	
			$res = $db->execute($stmt_updt, $fieldsValue);
	
			if(!$res) {
				$messageContext->addMessage("ERROR","Errore durante la modifica del reparto");
			}else {
				$messageContext->addMessage("SUCCESS","Modifica del reparto eseguita con successo");
			}
	
			$form = "MOD_REPARTO";
		}else {
			$values = array();
			$values['SEDUSR'] = $_REQUEST['SEDUSR'];
			$values['SEDCDE'] = $_REQUEST['SEDCDE'];
			$values['SEDREP'] = $_REQUEST['SEDREP'];
			$values['SEDSOC'] = $_REQUEST['SEDSOC'];
			$values['SEDAVA'] = date('Y');
			$values['SEDMVA'] = date('m');
			$values['SEDGVA'] = date('d');
			$values['SEDAFV'] = "9999";
			$values['SEDMFV'] = "99";
			$values['SEDGFV'] = "99";
			$values['SEDSTA'] = $stato; // $stato arriva dal validation.php
			$values['SEDST1'] = '1';
			$values['SEDST2'] = '1';
			$values['SEDDMO'] = $date;
			$values['SEDHMO'] = $hour;
			$values['SEDWHO'] = $_SESSION['user'];
			$values['SEDTCK'] = '';
			$values['SEDNPD'] = '';
	
			$stmtDoc = $db->prepare("INSERT", "FSEDREPA", null, array_keys($values));
			$res = $db->execute($stmtDoc, $values);
	
			if(!$res) {
				$messageContext->addMessage("ERROR","Errore inserimento nuovo reparto");
			}else {
				$messageContext->addMessage("SUCCESS","Nuovo reparto aggiunto con successo");
			}
		}
	
		$actionContext->onError($azione, $form, "", "", true);
		$actionContext->onSuccess("CLOSE", "CLOSE_WINDOW");
	}