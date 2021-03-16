<?php

	require_once 'manutenzione_widget_commons.php';

	$azione = $actionContext->getAction();
	$form = $actionContext->getForm();
	
	if(!in_array($form, array("PASS_VALUE", "TOOL_GRUPPI"))) {
		$history->addCurrent();
	}
	
	if($form != "DEFAULT") {
		$key = getListKeyArray($azione."_LIST");
	}
	
	if($form == "DEFAULT") {
		
	}else if($form == "ADD_WIDGET") {
		$actionContext->setLabel("Aggiungi widget");
	}else if($form == "AGGIUNGI_WIDGET") {
		if(isset($_REQUEST['GRUPPO'])) {
			$user = wi400Detail::getDetailValue($azione."_ADD_WIDGET_GRUPPO", 'gruppo');
		}else {
			$user = wi400Detail::getDetailValue($azione."_ADD_WIDGET", 'codusr');
			if(!$user) $user = $_SESSION['user'];
		}
		
		$miaLista = wi400Session::load(wi400Session::$_TYPE_LIST, $azione."_ALL_WIDGET");
		
		$file = "ZWIDGUSR";
		$fields = getDS($file);
		$stmtWidget = $db->prepare("INSERT", $file, null, array_keys($fields));
		
		$sql_progressivo = "SELECT max(WIDPRG) MAX FROM ZWIDGUSR WHERE WIDUSR='$user' AND WIDAZI=?";
		$stmt_progressivo = $db->prepareStatement($sql_progressivo);

		foreach($miaLista->getSelectionArray() as $key => $val) {
			$rs = $db->execute($stmt_progressivo, array($key));
			$row = $db->fetch_array($stmt_progressivo);
			$progressivo = $row['MAX']+1;
			
			$timestamp = getDb2Timestamp();
			$fields['WIDUSR'] = $user;
			$fields['WIDAZI'] = $key;
			$fields['WIDPRG'] = $progressivo;
			$fields['WIDSTA'] = '1';
			$fields['WIDCOL'] = 1;
			$fields['WIDRIG'] = 0;
			$fields['WIDDOC'] = '0';
			$fields['USRINS'] = $_SESSION['user'];
			$fields['TMSINS'] = $timestamp;
			$fields['USRMOD'] = $_SESSION['user'];
			$fields['TMSMOD'] = $timestamp;
			$result = $db->execute($stmtWidget, $fields);
			if($result) {
				$messageContext->addMessage("SUCCESS", "Widget $key aggiunto con successo!");
			}else {
				$messageContext->addMessage("ERROR", "Errore! Widget $key non aggiunto.");
			}
		}
		
		$actionContext->gotoAction("CLOSE", "CLOSE_WINDOW_MSG", "", true);
	}else if($form == "ELIMINA") {
		$miaLista = wi400Session::load(wi400Session::$_TYPE_LIST, $azione."_LIST");
		
		$error = false;
		
		$file = "ZWIDGUSR";
		$key_del = array("WIDUSR", "WIDAZI", "WIDPRG");
		$stmt_delete = $db->prepare("DELETE", $file, $key_del, null);
		
		foreach($miaLista->getSelectionArray() as $key => $val) {
			$key = explode("|", $key);
			$result = $db->execute($stmt_delete, array($key[3], $key[0], $key[1]));
			if(!$result)
				$error = true;
		}
		
		if($error)
			$messageContext->addMessage("ERROR", "Errore durante l'eliminazione dei widget.");
		else
			$messageContext->addMessage("SUCCESS", "Widget eliminati con successo");
		
		$actionContext->gotoAction($azione, "DEFAULT", "", true);
	}else if($form == "MODIFICA") {
		$actionContext->setLabel("Modifica ".$key[0]);
		
		$object = getWidgetObj($key[0], $key[1]);
		$detail = $object->getDetailParam($key[1], $_SESSION['user']);
	}else if($form == "SAVE_PARAM") {
		$in_menu = wi400Detail::getDetailValue("PARAM_WIDGET", "IN_MENU") ? "1" : "0";
		$width_widget = wi400Detail::getDetailValue("PARAM_WIDGET", "NUM_COLONNE");
		if($width_widget > 1 && $in_menu == "1") {
			$messageContext->addMessage("ERROR", "Nel menù laterale possono essere presenti solo widget con larghezza 1.");
			$actionContext->gotoAction($azione, "MODIFICA", "", true);
		}
		
		$sql_delete_param = "DELETE FROM ZWIDGPRM WHERE WIDUSR='{$key[3]}' and WIDAZI='{$key[0]}' and WIDPRG=".$key[1];
		$rs = $db->query($sql_delete_param);
		if(!$rs) $messageContext->addMessage("ERROR", "Errore durante l'eliminazione dei parametri.");
		
		$object = getWidgetObj($key[0], $key[3]);
		$result = $object->saveParams($key[1], $key[3]);
		
		$file = "ZWIDGUSR";
		$fields = getDS($file);
		$where = array("WIDUSR" => $key[3], //utente
						"WIDAZI" => $key[0], //azione
						"WIDPRG" => $key[1]); //progressivo
		$stmt_in_menu = $db->prepare("UPDATE", $file, $where, array("WIDDOC", "WIDCOL"));
		
		$rs = $db->execute($stmt_in_menu, array($in_menu, $width_widget));
		if(!$rs) $result = false;
		
		if(!$result)
			$messageContext->addMessage("ERROR", "Errore durante il salvataggio dei parametri.");
		else
			$messageContext->addMessage("SUCCESS", "Parametri salvati con successo.");
		
		$actionContext->gotoAction("CLOSE", "CLOSE_WINDOW_MSG", "", true);
	}else if($form == "TOOL_GRUPPI") {
		$actionContext->setLabel("Lookup Gruppi WI400");
		
		$nome_subfile = "GRUPPI_WIDGET";
		
		$subfile = new wi400Subfile($db, $nome_subfile, $settings['db_temp'], 20);
		$subfile->setConfigFileName($nome_subfile);
		$subfile->setModulo("widget");
		$subfile->setSql("*AUTOBODY");
	}