<?php

	require_once 'manutenzione_parametri_commons.php';
	
	$azione = $actionContext->getAction();
	$form = $actionContext->getForm();
	
	$history->addCurrent();
	
	if($form == "DEFAULT") {
		
		if(isset($_REQUEST['DA_ABILITAZIONI'])) {
			$_SESSION['DA_ABILITAZIONI'] = $_REQUEST['DA_ABILITAZIONI'];
			$_SESSION['DA_IFRAME'] = $_REQUEST['DA_IFRAME'];
		}else {
			unset($_SESSION['DA_ABILITAZIONI']);
			unset($_SESSION['DA_IFRAME']);
		}
	}else if(in_array($form, array("NUOVO", "MODIFICA"))) {
		//require_once 'op_resi_vuoti_commons.php';
		
		$mod = false;
		if($form == "NUOVO") {
			$actionContext->setLabel("Nuovo parametro");
			$salva_form = "INSERT";
			$key = array('TABELLA' => 'PARMAZI');
			if(isset($_SESSION['DA_ABILITAZIONI'])) {
				$mod = true;
				$key = array('TABELLA' => $_SESSION['DA_ABILITAZIONI'], 'MULTI' => false);
			}
		}else {
			$actionContext->setLabel("Modifica parametro");
			$salva_form = "UPDATE";
			$key = getListKeyArray($azione."_LIST");
			$mod = true;
		}
		
		if(isset($_REQUEST['CHANGE_PARAM'])) {
			$key = wi400Detail::getDetailValues($azione."_NEW_MOD");
		}
		
		//get valori template
		$template = array_diff(scandir($template_path), array('..', '.', 'deposito_lookup_ajax.php'));
		
	}else if(in_array($form, array("INSERT", "UPDATE"))) {
		$file = "ZTABTABE";
		$fields = getDs($file);
		
		$key = wi400Detail::getDetailValues($azione."_NEW_MOD");
		$listKey = getListKeyArray($azione."_LIST");
		
		if($form == "INSERT") {
			$stmt =  $db->prepare("INSERT", $file, null, array_keys($fields));
			
			$fields['SOCIETA'] = $key['SOCIETA'];
			$fields['TABELLA'] = $key['TABELLA'];
			$fields['USRINS'] = $_SESSION['user'];
			$fields['TMSINS'] = getDb2Timestamp();
			
			$succ_mess = "Nuovo elemento effettuato con successo!";
			$error_mess = "Errore inserimento nuovo parametro!";
		}else {
			unset($fields['SOCIETA']);
			unset($fields['TABELLA']);
			unset($fields['USRINS']);
			unset($fields['TMSINS']);
			
			$where = array("SOCIETA" => $key['SOCIETA'], "TABELLA" => $key['TABELLA'], "ELEMENTO" => $listKey['ELEMENTO']);
			$stmt =  $db->prepare("UPDATE", $file, $where, array_keys($fields));
			
			$succ_mess = "Elemento modificato effettuata con successo!";
			$error_mess = "Errore modifica parametro!";
		}
		
		$fields['ELEMENTO'] = $key['ELEMENTO'];
		$fields['ELEMENTO2'] = $key['ELEMENTO2'];
		$fields['VALORE'] = $key['VALORE'];
		$fields['VALORE1'] = $key['VALORE1'];
		$fields['VALORE2'] = $key['VALORE2'];
		$fields['VALORE3'] = $key['VALORE3'];
		$fields['MULTI'] = $key['MULTI'];
		$fields['TEMPLATE'] = $key['TEMPLATE'];
		$fields['DEFAULT'] = $key['DEFAULT'];
		$fields['USRMOD'] = $_SESSION['user'];
		$fields['TMSMOD'] = getDb2Timestamp();
		$fields['STATO'] = '1';
		
		$rs = $db->execute($stmt, $fields);
		
		if($rs) {
			$messageContext->addMessage("SUCCESS", $succ_mess);
			
			if($listKey['ELEMENTO'] != $key['ELEMENTO']) {
				$query = "UPDATE ZSYSPARM SET PARAMETRO='{$key['ELEMENTO']}' WHERE PARAMETRO='{$listKey['ELEMENTO']}'";
				$db->query($query);
			}
		}else {
			$messageContext->addMessage("ERROR", $error_mess);
		}
		
		if(isset($_SESSION['DA_ABILITAZIONI'])) {
			if(isset($_SESSION['DA_IFRAME'])) {
				$actionContext->gotoAction("CLOSE", "RELOAD_PREVIOUS_WINDOW", "", true);
			}else {
				$actionContext->gotoAction("CLOSE", "RELOAD_PREVIOUS_WINDOW", "", true);
			}
		}else {
			$actionContext->gotoAction("CLOSE", "CLOSE_WINDOW_MSG", "", true);
		}
	}else if($form == "ELIMINA") {
		$key = getListKeyArray($azione."_LIST");
		
		$query = "DELETE FROM ZTABTABE WHERE SOCIETA=? AND TABELLA=? AND ELEMENTO=?";
		$stmt_elimina = $db->prepareStatement($query);
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $azione."_LIST");
		
		$error = false;
		foreach($wi400List->getSelectionArray() as $chiave => $input) {
			$key = explode("|", $chiave);
			$rs = $db->execute($stmt_elimina, array($key[0], $key[7], $key[1]));
			if(!$rs) {
				$error = true;
				$messageContext->addMessage("ERROR", "Errore eliminazione parametro: ".$chiave);
			}
		}
		
		if(!$error) {
			$messageContext->addMessage("SUCCESS", "Elimazione effettuata con successo!");
		}

		if(isset($_SESSION['DA_ABILITAZIONI'])) {
			$da_iframe = "";
			if(isset($_REQUEST['DECORATION']) && $_REQUEST['DECORATION'] == 'lookUp') {
				$da_iframe = "&DA_IFRAME=si";
			}
			$actionContext->gotoAction($azione, "DEFAULT&DA_ABILITAZIONI=".$_SESSION['DA_ABILITAZIONI'].$da_iframe, "", true);
		}else {
			$actionContext->gotoAction($azione, "DEFAULT", "", true);
		}
	}