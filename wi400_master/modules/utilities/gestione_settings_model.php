<?php

	require_once 'manutenzione_settings_commons.php';
	
	$azione = $actionContext->getAction();
	$form = $actionContext->getForm();
	
	if(!in_array($form, array("IMPORT_SETTINGS", "IMPORT_VALORI"))) 
		$history->addCurrent();
	
	if($form == "DEFAULT") {
		
	}else if(in_array($form, array("NUOVO", "MODIFICA"))) {
		//require_once 'op_resi_vuoti_commons.php';
		$mod = false;
		if($form == "NUOVO") {
			$actionContext->setLabel("Nuovo parametro");
			$salva_form = "INSERT";
		}else {
			$actionContext->setLabel("Modifica parametro");
			$salva_form = "UPDATE";
			$key = getListKeyArray($azione."_LIST");
			$mod = true;
		}
		
	}else if(in_array($form, array("INSERT", "UPDATE"))) {
		$fields = getDs($tabSettings);
		
		$key = wi400Detail::getDetailValues($azione."_NEW_MOD");
		$listKey = getListKeyArray($azione."_LIST");
		
		$stato = $key['STATO'] ? '1' : '0';
		
		if($form == "INSERT") {
			$stmt =  $db->prepare("INSERT", $tabSettings, null, array_keys($fields));
			
			$fields['USRINS'] = $_SESSION['user'];
			$fields['TMSINS'] = getDb2Timestamp();
			
			$succ_mess = "Nuovo parametro inserito con successo!";
			$error_mess = "Errore inserimento nuovo parametro!";
		}else {
			unset($fields['USRINS']);
			unset($fields['TMSINS']);
			
			$where = array("PARAMETRO" => $listKey['PARAMETRO']);
			$stmt =  $db->prepare("UPDATE", $tabSettings, $where, array_keys($fields));
			
			$succ_mess = "Parametro modificato con successo!";
			$error_mess = "Errore modifica parametro!";
		}
		
		$fields['PARAMETRO'] = $key['PARAMETRO'];
		$fields['PARAM_DES'] = $key['DESCRIZIONE'];
		$fields['TIPO'] = $key['TIPO'];
		$fields['OGGETTO'] = $key['OGGETTO'];
		$fields['FORMATO'] = $key['FORMATO'];
		$fields['LUNGHEZZA'] = $key['LUNGHEZZA'] ? $key['LUNGHEZZA'] : 50;
		//$fields['STATO'] = $key['STATO'];
		$fields['USRMOD'] = $_SESSION['user'];
		$fields['TMSMOD'] = getDb2Timestamp();
		$fields['STATO'] = $stato;
		
		$rs = $db->execute($stmt, $fields);
		
		if($rs) {
			$messageContext->addMessage("SUCCESS", $succ_mess);
			
			if($listKey['PARAMETRO'] != $key['PARAMETRO']) {
				$query = "UPDATE $tabValori SET PARAMETRO='{$key['PARAMETRO']}' WHERE AMBIENTE='$ambiente' and PARAMETRO='{$listKey['PARAMETRO']}'";
				$db->query($query);
			}
		}else {
			$messageContext->addMessage("ERROR", $error_mess);
		}
		
		$actionContext->gotoAction("CLOSE", "CLOSE_WINDOW_MSG", "", true);
	}else if($form == "ELIMINA") {
		$key = getListKeyArray($azione."_LIST");
		
		//Eliminare anche il valore in ztabsetv???
		$query = "DELETE FROM $tabSettings WHERE PARAMETRO=?";
		$stmt_elimina = $db->prepareStatement($query);
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $azione."_LIST");
		
		$error = false;
		foreach($wi400List->getSelectionArray() as $chiave => $input) {
			$key = get_list_keys_num_to_campi($wi400List, explode("|", $chiave));
			
			$rs = $db->execute($stmt_elimina, array($key['PARAMETRO']));
			if(!$rs) {
				$error = true;
				$messageContext->addMessage("ERROR", "Errore eliminazione parametro: ".$chiave);
			}
		}
		
		if(!$error) {
			$messageContext->addMessage("SUCCESS", "Elimazione effettuata con successo!");
		}

		$actionContext->gotoAction($azione, "DEFAULT", "", true);
	}else if($form == "IMPORT_SETTINGS") {
		$parameters = getWi400ConfParameters();
		
// 		showArray($parameters);
		//$sql_param = "DELETE FROM $tabSettings";
		//$rs = $db->query($sql_param); 
		$rs = true;
		
		if($rs) {
			$fields = getDs($tabSettings);
			
			foreach($parameters as $nome_parm => $valore) {
				if(!existParametro($nome_parm)) {
					$type = gettype($valore);
					
					$tipo = "classe";
					$oggetto = "wi400InputText";
					$formato = "string";
					$lunghezza = 150;
					
					if($type == "boolean") {
						$oggetto = "wi400InputCheckBox";
						$formato = "integer";
						$lunghezza = 1;
						
						$valore = $valore ? 1 : 0;
					}else if($type == "array") {
						$formato = "array";
					}else if($type == "integer") {
						$formato = "integer";
					}
					
					$fields['PARAMETRO'] = $nome_parm;
					$fields['PARAM_DES'] = "";
					$fields['TIPO'] = $tipo;
					$fields['OGGETTO'] = $oggetto;
					$fields['FORMATO'] = $formato;
					$fields['LUNGHEZZA'] = $lunghezza;
					$fields['STATO'] = '1';
					$fields['USRINS'] = $_SESSION['user'];
					$fields['TMSINS'] = getDb2Timestamp();
					
					$rs = insertParametro($fields);
					if(!$rs) {
						$messageContext->addMessage("ERROR", "Errore inserimento parametro ".$nome_parm);
					}
				}
			}
		}else {
			$messageContext->addMessage("ERROR", "Errore importazione parametri da file");
		}
		
		$actionContext->gotoAction($azione, "DEFAULT", "", true);
	}else if($form == "IMPORT_VALORI") {
		$parameters = getWi400ConfParameters();
		
		//$ambiente = "WI400_LZOVI";
		
		$sql_valori = "DELETE FROM $tabValori WHERE AMBIENTE='$ambiente'";
		$rs = $db->query($sql_valori);
		
		if($rs) {
			$valori = getDs($tabValori);
				
			foreach($parameters as $nome_parm => $valore) {
				$type = gettype($valore);
				$pgr = 0;
				$valori['AMBIENTE'] = $ambiente;
				$valori['PARAMETRO'] = $nome_parm;
					
				if($type == "array") {
					foreach ($valore as $chiave => $val) {
						if(gettype($chiave) == "integer")   $chiave = '';
						$valori['CHIAVE'] = $chiave;
						$valori['PGR'] = $pgr;
						$valori['VALORE'] = $val;
						$valori['USRINS'] = $_SESSION['user'];
						$valori['TMSINS'] = getDb2Timestamp();
							
						$result = insertValore($valori);
						if($result) {
							$pgr++;
						}else {
							$messageContext->addMessage("ERROR", "Errore importazione valore ".$nome_parm." => ".$val);
						}
					}
				}else {
					$valori['CHIAVE'] = '';
					$valori['PGR'] = $pgr;
					$valori['VALORE'] = $valore;
					$valori['USRINS'] = $_SESSION['user'];
					$valori['TMSINS'] = getDb2Timestamp();
				
					$result = insertValore($valori);
					if(!$result) {
						$messageContext->addMessage("ERROR", "Errore importazione valore ".$nome_parm." => ".$valore);
					}
				}
			}
		}else {
			$messageContext->addMessage("ERROR", "Errore importazione parametri da file");
		}
		
		$actionContext->gotoAction("MANUTENZIONE_SETTINGS", "DEFAULT", "", true);
	}