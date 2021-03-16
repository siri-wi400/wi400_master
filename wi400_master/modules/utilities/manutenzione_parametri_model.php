<?php
	require_once 'manutenzione_parametri_commons.php';
	//require_once 'op_resi_vuoti_commons.php';

	$azione = $actionContext->getAction();
	$form = $actionContext->getForm();
	
	$history->addCurrent();
	
	if($form != "DEFAULT") {
		$p = getListKeyArray($azione."_PARAMETRI");
		$parametro = $p['ELEMENTO'];
	}
	
	if($form == "DEFAULT") {
		
	}else if($form == "DETAIL") {
		$actionContext->setLabel("Dettaglio parametro");

	}else if(in_array($form, array("INSERT_PARAMETRO", "INSERT_CONFIGURAZIONE"))) {
		$file = "ZSYSPARM";
		$fields = getDs($file);
		
		$timestamp = getDb2Timestamp();
		if($form == "INSERT_PARAMETRO") {
			$param = wi400Detail::getDetailValues($azione."_NUOVO_PARAMETRO");
			$fields['PARAMETRO'] = $param['NOME'];
			$fields['VALORE'] = $param['VALORE'];
			
			$mess_log = array("Parametro aggiunto con successo!", "Errore creazione nuovo parametro!");
		}else {
			$fields['SOCIETA'] = $societa;
			$fields['SITO'] = $sito;
			$fields['DEPOSITO'] = $deposito;
			$fields['INTERLOCUTORE'] = $interlocutore;
			$fields['PARAMETRO'] = $key['ELEMENTO'];
			$fields['VALORE'] = $valore;
			
			$mess_log = array("Configurazione aggiunta con successo!", "Errore creazione nuova configurazione!");
		}
		
		$stmt =  $db->prepare("INSERT", $file, null, array_keys($fields));
		
		$fields['USRMOD'] = $_SESSION['user'];
		$fields['USRINS'] = $_SESSION['user'];
		$fields['TMSINS'] = $timestamp;
		
		//showArray($fields);
		$rs = $db->execute($stmt, $fields);

		if($rs) {
			$messageContext->addMessage("SUCCESS", $mess_log[0]);
		}else {
			$messageContext->addMessage("ERROR", $mess_log[1]);
		}
		
		$actionContext->gotoAction("CLOSE", "CLOSE_WINDOW_MSG", "", true);
	}else if($form == "UPDATE_PARAMETRO") {
		
		$query = "UPDATE ZSYSPARM SET PARAMETRO='$nuovo_nome' WHERE PARAMETRO='$parametro'";
		
		echo $query."<br/>";
		
		$rs = $db->query($query);
		if($rs) {
			$messageContext->addMessage("SUCCESS", "Modifica effettuata con successo!");
			deleteCacheSysParameter();
		}else {
			$messageContext->addMessage("SUCCESS", "Errore modifica parametro!");
		}
		
		$actionContext->gotoAction("CLOSE", "CLOSE_WINDOW_MSG", "", true);
	}else if($form == "SALVA_VALORE") {
		
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $azione."_PARAMETRI_DETAIL");
		
		//showArray($wi400List->getSelectionArray());
		
		$query = "UPDATE ZSYSPARM SET VALORE=?, USRMOD='".$_SESSION['user']."', TMSMOD='".getDb2Timestamp()."' WHERE SOCIETA=? AND SITO=? AND DEPOSITO=? AND INTERLOCUTORE=? AND PARAMETRO=?";
		$stmt_valore = $db->prepareStatement($query);
		
		$error = false;
		foreach($wi400List->getSelectionArray() as $chiave => $input) {
			$key = explode("|", $chiave);
			if(!isset($input['VALORE'])) $input['VALORE'] = 0;
			
			if($key[4] != $input['VALORE']) {
				$rs = $db->execute($stmt_valore, array($input['VALORE'], $key[0], $key[1], $key[2], $key[3], $parametro));
				if(!$rs) {
					$error = true;
					$messageContext->addMessage("ERROR", "Errore salvataggio configurazione: ".$chiave);
				}
			}
		}
		
		if(!$error) {
			$messageContext->addMessage("SUCCESS", "Salvataggio effettuato con successo!");
			deleteCacheSysParameter();
		}

		$actionContext->gotoAction($azione, "DETAIL", "", true);
	}else if($form == "NUOVA_CONFIGURAZIONE") {
		//require_once 'op_resi_vuoti_commons.php';
		
		$actionContext->setLabel("Nuova configurazione");
		
	}else if($form == "ELIMINA_CONFIGURAZIONE") {
		$query = "DELETE FROM ZSYSPARM WHERE SOCIETA=? AND SITO=? AND DEPOSITO=? AND INTERLOCUTORE=? AND PARAMETRO=?";
		$stmt_elimina = $db->prepareStatement($query);
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $azione."_PARAMETRI_DETAIL");
		
		$error = false;
		foreach($wi400List->getSelectionArray() as $chiave => $input) {
			$key = explode("|", $chiave);
			$rs = $db->execute($stmt_elimina, array($key[0], $key[1], $key[2], $key[3], $parametro));
			if(!$rs) {
				$error = true;
				$messageContext->addMessage("ERROR", "Errore eliminazione configurazione: ".$chiave);
			}
		}
		
		if(!$error) {
			$messageContext->addMessage("SUCCESS", "Elimazione effettuata con successo!");
			deleteCacheSysParameter();
		}

		$actionContext->gotoAction($azione, "DETAIL", "", true);
	}
	
	
	
	