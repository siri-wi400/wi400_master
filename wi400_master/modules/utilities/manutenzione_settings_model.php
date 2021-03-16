<?php
	require_once 'manutenzione_settings_commons.php';

	$azione = $actionContext->getAction();
	$form = $actionContext->getForm();
	
	$history->addCurrent();
	
	if($form != "DEFAULT") {
		$p = getListKeyArray($azione."_PARAMETRI");
		$parametro = $p['PARAMETRO'];
	}
	
	if($form == "DEFAULT") {
		
	}else if($form == "DETAIL") {
		$actionContext->setLabel("Dettaglio parametro");
		
		$parm_settings = getRowSettings($parametro);
		$parm_valori = getRowValori($parametro);
		$formato = $p['FORMATO'];
		
		//echo $tipo."__<br>";
		//showArray($parm_valori);

	}else if(in_array($form, array("INSERT_PARAMETRO", "INSERT_CONFIGURAZIONE"))) {
		$fields = getDs($tabValori);
		
		$fields['AMBIENTE'] = $ambiente;

		$timestamp = getDb2Timestamp();
		
		if($form == "INSERT_PARAMETRO") {
			$param = wi400Detail::getDetailValues($azione."_NUOVO_PARAMETRO");
			$fields['PARAMETRO'] = $param['NOME'];
			$fields['VALORE'] = $param['VALORE'];
			
			$mess_log = array("Parametro aggiunto con successo!", "Errore creazione nuovo parametro!");
		}else {
			$fields['PARAMETRO'] = $parametro;
			$fields['PGR'] = $pgr ? $pgr : 0;
			$fields['CHIAVE'] = $chiave;
			$fields['VALORE'] = $valore;
			
			$mess_log = array("Configurazione aggiunta con successo!", "Errore creazione nuova configurazione!");
		}
		
		$stmt =  $db->prepare("INSERT", $tabValori, null, array_keys($fields));
		
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
		
		$query = "UPDATE $tabValori SET PARAMETRO='$nuovo_nome' WHERE AMBIENTE='$ambiente' and PARAMETRO='$parametro'";
		
		echo $query."<br/>";
		
		$rs = $db->query($query);
		if($rs) {
			$messageContext->addMessage("SUCCESS", "Modifica effettuata con successo!");
			//deleteCacheSysParameter();
		}else {
			$messageContext->addMessage("SUCCESS", "Errore modifica parametro!");
		}
		
		$actionContext->gotoAction("CLOSE", "CLOSE_WINDOW_MSG", "", true);
	}else if($form == "SALVA_VALORE") {
		
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $azione."_PARAMETRI_DETAIL");
		
		//showArray($wi400List->getSelectionArray());
		
		$query = "UPDATE $tabValori SET PGR=?, CHIAVE=?, VALORE=?, USRMOD='".$_SESSION['user']."', TMSMOD='".getDb2Timestamp()."' 
					WHERE AMBIENTE='$ambiente' and PGR=? AND CHIAVE=? AND VALORE=? AND PARAMETRO=?";
		$stmt_valore = $db->prepareStatement($query);
		
		$error = false;
		foreach($wi400List->getSelectionArray() as $chiave => $input) {
			//Parametri vecchi
			$key = get_list_keys_num_to_campi($wi400List, explode("|", $chiave));
			
			
			if(!isset($input['PGR']) || !$input['PGR']) $input['PGR'] = 0;
			if(!isset($input['CHIAVE'])) $input['CHIAVE'] = '';
			//if(!isset($input['VALORE'])) $input['VALORE'] = 0;
			//IF($key['VALORE'])
			
			showArray($key);
			showArray($input);
			//DIE("alberto");
			
			$exit = existValore($parametro, $input['PGR'], $key['PGR']);
			if(!$exit) { 
				if(!isset($input['VALORE'])) $input['VALORE'] = 0;
			
				$rs = $db->execute($stmt_valore, array($input['PGR'], $input['CHIAVE'], $input['VALORE'], $key['PGR'], $key['CHIAVE'], $key['VALORE'], $parametro));
				if(!$rs) {
					$error = true;
					$messageContext->addMessage("ERROR", "Errore salvataggio configurazione: ".$chiave);
				}
			}else {
				$messageContext->addMessage("ERROR", "Errore salvataggio configurazione: esiste già un valore con progressivo ".$input['PGR']);
			}
		}
		
		if(!$error) {
			$messageContext->addMessage("SUCCESS", "Salvataggio effettuato con successo!");
			//deleteCacheSysParameter();
		}

		$actionContext->gotoAction($azione, "DETAIL", "", true);
	}else if($form == "NUOVA_CONFIGURAZIONE") {
		//require_once 'op_resi_vuoti_commons.php';
		
		$actionContext->setLabel("Nuova configurazione");
		
	}else if($form == "ELIMINA_CONFIGURAZIONE") {
		$query = "DELETE FROM $tabValori WHERE AMBIENTE='$ambiente' and PARAMETRO=? and PGR=?";
		$stmt_elimina = $db->prepareStatement($query);
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $azione."_PARAMETRI_DETAIL");
		
		$error = false;
		foreach($wi400List->getSelectionArray() as $chiave => $input) {
			$key = get_list_keys_num_to_campi($wi400List, explode("|", $chiave));
			$rs = $db->execute($stmt_elimina, array($parametro, $key['PGR']));
			if(!$rs) {
				$error = true;
				$messageContext->addMessage("ERROR", "Errore eliminazione configurazione: ".$chiave);
			}
		}
		
		if(!$error) {
			$messageContext->addMessage("SUCCESS", "Elimazione effettuata con successo!");
			//deleteCacheSysParameter();
		}

		$actionContext->gotoAction($azione, "DETAIL", "", true);
	}
	
	
	
	