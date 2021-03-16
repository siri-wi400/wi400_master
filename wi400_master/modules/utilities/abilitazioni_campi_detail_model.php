<?php
	
	require_once 'abilitazioni_campi_detail_commons.php';

	$azione = $actionContext->getAction();
	$form = $actionContext->getForm();
	
	if(!in_array($form, array("REST_ORDINAMENTO", "ELIMINA_PARAMETRO"))) 
		$history->addCurrent();
	
	if(!in_array($actionContext->getForm(), array("DEFAULT", "MAP_DETAIL", ""))) {
		$key_azi = getListKeyArray($azione."_AZIONI");
	}
	if(!in_array($actionContext->getForm(), array("DEFAULT", "DETAIL", "MAP_DETAIL"))) {
		$key_det = getListKeyArray($azione."_DETAIL");
	}
	/*if(in_array($actionContext->getForm(), array("MODIFICA_PARAMETRO", "LIST_PARAMETRI", "LIST_ABILITAZIONI", "COPIA", "UPDATE_VALUES", 
												"INSERT_NUOVO_PARAMETRO", "UPDATE_PARAMETER", 
												"CONFIGURA_LIST", "ORDINAMENTO_COL", "RESET_ORDINAMENTO"))) {*/
	if(!in_array($actionContext->getForm(), array("DEFAULT", "DETAIL", "MAP_DETAIL", "UTENTI"))) {
		$key_ute = getListKeyArray($azione."_UTENTI");
	}
	
	if($actionContext->getForm() == "DEFAULT") {
		$actionContext->setLabel("Lista Azioni");

		if(isset($settings['*all_lista']) && $settings['*all_lista']) {
			creazioneAllGenerale('*all_lista', "L");
		}
		if(isset($settings['*all_detail']) && $settings['*all_detail']) {
			creazioneAllGenerale('*all_detail', "D");
		}
	}else if($actionContext->getForm() == "CHECK_CONF_EXIST") {
		$check_azione = wi400Detail::getDetailValue("DETTAGLIO_AZIONE","codazi");
		
	}else if($actionContext->getForm() == "CREATE_CONF") {
		$check_azione = wi400Detail::getDetailValue("DETTAGLIO_AZIONE","codazi");
		 
		$fields_file = array("WIDAZI" => $check_azione,
				"WIDDOL" => "P",
				"WIDID" => "",
				"WIDKEY" => "*ALL"
		);
		
		$stmt = $db->prepare("INSERT", "ZWIDETPA", null, array_keys($fields_file));
		$result = $db->execute($stmt, $fields_file);
		if(!$result) {
			$messageContext->addMessage("ERROR", "Errore creazione configurazione parametri");
		}
		
		$actionContext->onSuccess($azione, "UTENTI", "", "UTENTI&AZI=".$check_azione);
		$actionContext->onError($azione, "CHECK_CONF_EXIST", "", "", true);
		
	}/*else if($actionContext->getForm() == "NUOVO_PARAMETRO") {
		$actionContext->setLabel("Nuovo parametro");
		
		$gestione_param = getParamConfigurati();
	}else if($actionContext->getForm() == "INSERT_NUOVO_PARAMETRO") {
		$nome = wi400Detail::getDetailValue($azione."_DETAIL","NOME_PARAMETRO");
		$valore = wi400Detail::getDetailValue($azione."_DETAIL","VALORE_PARAMETRO");
		
		showArray($key_ute);
		
		$error = false;
		
		if($key_ute['WIDKEY']) {
			$rs = insertParametro($key_azi['WIDAZI'], $key_ute['WIDKEY'], $nome, $valore);
			$error = !$rs;
		}else {
			$query = "SELECT WIDKEY FROM $tabella 
					WHERE widazi='".$key_azi['WIDAZI']."' AND WIDID='' and WIDDOL='{$key_det['WIDDOL']}'
					GROUP BY WIDKEY";
			
			
			$res = $db->query($query);
			while($row = $db->fetch_array($res)) {
				//$rs = in
				$rs = insertParametro($key_azi['WIDAZI'], $row['WIDKEY'], $nome, $valore);
				if(!$rs) $error = true;
			}
		}
		
		if(!$error) {
			$messageContext->addMessage("SUCCESS", "Parametro creato con successo!");
			$actionContext->gotoAction("CLOSE", "CLOSE_WINDOW_MSG", "", true, true);
		}
		
		//$actionContext->onSuccess("CLOSE", "CLOSE_WINDOW_MSG");
		$actionContext->onError($azione, "NUOVO_PARAMETRO", "", "", true, false);
	}*/
	else if($actionContext->getForm() == "DETAIL") {
		$actionContext->setLabel("Lista detail");
		
	}else if($actionContext->getForm() == "UTENTI") {
		$actionContext->setLabel("Lista utenti");
		
		//$query = "SELECT WIDKEY FROM zwidetpa WHERE widazi='{$key_azi['WIDAZI']}' AND WIDID='{$key_det['WIDID']}' AND WIDDOL='{$key_det['WIDDOL']}' GROUP BY widkey";
		
		$where = array(
			"WIDAZI='{$key_azi['WIDAZI']}'",
			"WIDID='{$key_det['WIDID']}'",
			"WIDDOL='{$key_det['WIDDOL']}'"
		);
		
	}else if($actionContext->getForm() == "CLEAN_DETAIL_COPIA") {
		wi400Detail::cleanSession($azione."_COPIA");
		
		$actionContext->gotoAction($azione, "DETAIL_COPIA", false, true);
	}else if($actionContext->getForm() == "DETAIL_COPIA") {
		$actionContext->setLabel("Copia abilitazioni");
	
	}else if($actionContext->getForm() == "COPIA") {
		$file = "ZWIDETPA";
		$field = getDs($file);
		$errore = false;
		
		$stmt = $db->prepare("INSERT", $file, null, array_keys($field));
		
		$query = "SELECT * FROM $file WHERE widazi='$azione_det' AND WIDID='$detailId' and WIDDOL='{$key_det['WIDDOL']}' and WIDKEY='{$key_ute['WIDKEY']}'";
		$rs = $db->query($query);
		$dati = array();
		while($row = $db->fetch_array($rs)) {
			$dati[] = $row;
		}
		
		//$da_copiare arriva dal validation
		foreach($da_copiare as $utente) {
			foreach($dati as $row) {
				foreach($row as $key => $val) {
					$field[$key] = $val;
				}
	 			$field['WIDAZI'] = $azione_det;
				$field['WIDDOL'] = $key_det['WIDDOL'];
				$field['WIDID'] = $detailId;
				$field['WIDKEY'] = $utente;
				
				$result = $db->execute($stmt, $field);
				if(!$result) {
					$errore = true;
				}
			}
		}
		
		elimina_cache();
		
		if($errore) {
			$messageContext->addMessage("ERROR", "Errore copia abilitazioni");
		}
		
		$actionContext->gotoAction("CLOSE", "CLOSE_WINDOW", false, true);
	}/*else if($actionContext->getForm() == "ELIMINA_PARAMETRO") {
		
		foreach($wi400List->getSelectionArray() as $key => $campi) {
			$key = get_list_keys_num_to_campi($wi400List, explode("|", $key));
			showArray($key);
		}
		
		$actionContext->gotoAction($azione, "LIST_ABILITAZIONI", "", true);
	}*/else if($actionContext->getForm() == "ELIMINA_UTENTE") {
		$valori = array_keys($wi400List->getSelectionArray());
		
		$file = "ZWIDETPA";
		$sql = "DELETE FROM $file WHERE widazi='".$key_azi['WIDAZI']."' AND widid='".$key_det['WIDID']."' AND widdol='".$key_det['WIDDOL']."' AND widkey in ('".implode("', '", $valori)."')";
		
		if($db->query($sql)) {
			$messageContext->addMessage("SUCCESS", "Eliminazione effettuata con successo!");
		}else {
			$messageContext->addMessage("ERROR", "Errore eliminazione utenti!");
		}
		
		elimina_cache();
		
		$actionContext->gotoAction($azione, "UTENTI", false, true);
		
	}else if($actionContext->getForm() == "ELIMINA_DETAIL") {
		$valori = array();
		foreach($wi400List->getSelectionArray() as $key => $ele) {
			$dati = explode("|", $key);
			$valori[] = $dati[0];
		}
		
		$file = "ZWIDETPA";
		$sql = "DELETE FROM $file WHERE widazi='".$key_azi['WIDAZI']."' AND widid in ('".implode("', '", $valori)."')";
		
		if($db->query($sql)) {
			$messageContext->addMessage("SUCCESS", "Eliminazione effettuata con successo!");
		}else {
			$messageContext->addMessage("ERROR", "Errore eliminazione detail!");
		}
		
		elimina_cache();
		
		$actionContext->gotoAction($azione, "DETAIL", false, true);
		
	}else if($actionContext->getForm() == "CHECK_ALL") {	
		
		$checkAction = $_GET["VALUE"];
		$colKey = $_GET["COL"];
		$idList = $_GET['IDLIST'];
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
		
		/*$file = fopen("/www/zendsvr/htdocs/wi400_pasin/zAlberto.txt", "a");
		fwrite($file, "valore: ".$checkAction."  ".$colKey."  ".$idList."\r\n");*/
		
		$wi400List->setHeaderValue($colKey, 0);
		
		wi400Session::save(wi400Session::$_TYPE_LIST, $idList, $wi400List);
		
		die();
	}else if($actionContext->getForm() == "UPDATE_PARAMETER") {
		/*showArray($key_azi);
		showArray($key_det);
		showArray($key_ute);*/
		
		$file = "ZWIDETPA";
		$fieldUpdate = array("WIDDFV");
		$where = array("WIDAZI" => $key_azi['WIDAZI'], "WIDDOL" => $key_det['WIDDOL'], "WIDID" => $key_det['WIDID'], "WIDKEY" => $key_ute['WIDKEY'], "WIDREQ" => '?');
		$stmtTes = $db->prepare("UPDATE", $file, $where, $fieldUpdate);
		
		//showArray($wi400List->getSelectionArray());
		
		foreach($wi400List->getSelectionArray() as $key => $campi) {
			$vecc_campi = explode("|", $key);
			$fieldId = $vecc_campi[0];
		
			if($vecc_campi[1] != $campi['WIDDFV']) {
				$result = $db->execute($stmtTes, array($campi['WIDDFV'], $fieldId));
				
				if(!$result) {
					$messageContext->addMessage("ERROR","Errore salvataggio campo ".$fieldId);
				}
			}
		}
		
		elimina_cache();
		
		$actionContext->gotoAction($azione, "LIST_ABILITAZIONI", "", true);
		
	}else if($actionContext->getForm() == "UPDATE_VALUES") {
		//showArray($wi400List->getSelectionArray());

		$file = "ZWIDETPA";
		$fieldUpdate = array("WIDABI", "WIDHID", "WIDFIL", "WIDDFT", "WIDDFV", "WIDSTA");
		$where = array("WIDAZI" => $key_azi['WIDAZI'], "WIDDOL" => $key_det['WIDDOL'], "WIDID" => $key_det['WIDID'], "WIDKEY" => $key_ute['WIDKEY'], "WIDREQ" => '?');
		$stmtTes = $db->prepare("UPDATE", $file, $where, $fieldUpdate);
		
		$whereEstendi = $where;
		unset($whereEstendi['WIDKEY']);
		$stmtEstendi = $db->prepare("UPDATE", $file, $whereEstendi, $fieldUpdate);
		
		foreach($wi400List->getSelectionArray() as $key => $campi) {
			$vecc_campi = explode("|", $key);
			$fieldId = $vecc_campi[0];
			
			$select = array("WIDABI", "WIDHID", "WIDFIL", "WIDDFT", "WIDDFV", "WIDSTA");
			$vecc_campi = getKeyValue($key_azi['WIDAZI'], $key_det['WIDDOL'], $key_det['WIDID'], $key_ute['WIDKEY'], $fieldId, implode(", ", $select));
			if($vecc_campi['WIDDFT'] == "") $vecc_campi['WIDDFT'] = 0;
			$estendi = false;
			if(isset($campi['ESTENDI'])) {
				$vecc_campi['ESTENDI'] = 0;
				if($campi['ESTENDI']) $estendi = true;
			}

			//showArray($vecc_campi);
			//showArray($campi);
				
			//unset($vecc_campi[0]);
			//$vecc_campi = array_values($vecc_campi);
			
			//if($vecc_campi != array_values($campi)) {
			if($vecc_campi != $campi) {
				$update_value = array();
				$update_value[] = $campi['WIDABI'];
				$update_value[] = $campi['WIDHID'];
				$update_value[] = $campi['WIDFIL'];
				$update_value[] = $campi['WIDDFT'];
				$update_value[] = $campi['WIDDFV'];
				$update_value[] = $campi['WIDSTA'];
				$update_value[] = $fieldId;
				
				if($estendi) {
					$result = $db->execute($stmtEstendi, $update_value);
				}else {
					$result = $db->execute($stmtTes, $update_value);
				}
				if(!$result) {
					$messageContext->addMessage("ERROR","Errore salvataggio campo ".$fieldId);
				}
			}
		}
		
		elimina_cache();
		
		$actionContext->gotoAction($azione, "LIST_ABILITAZIONI", false, true);
	}else if($actionContext->getForm() == "LIST_ABILITAZIONI") {
		$label_WIDDFT = "Abilita default";
		$label_WIDDFV = "Default value";
		if($key_det['WIDDOL'] == 'L') {
			$label_WIDDFT = "Abilita nome label";
			$label_WIDDFV = "Nome label";
		}
		
		if(isset($_SESSION['ABIL_ORD_COL'])) {
			$ord_col = $_SESSION['ABIL_ORD_COL'];
			$dati = getOrdinamentoCol($key_azi['WIDAZI'], $key_det['WIDID'], $key_ute['WIDKEY']);
			
			/*showArray($ord_col);
			showArray($dati);*/
			
			if($dati != $ord_col) {
				elimina_cache();
			}
			
			unset($_SESSION['ABIL_ORD_COL']);
			//die("<br/>alberto check");
		}
	}else if($actionContext->getForm() == "LIST_PARAMETRI") {
		$actionContext->setLabel("Abilitazioni parametri");
		
		$left_join = array(
			"TABELLA=WIDAZI",
			"WIDKEY='{$key_ute['WIDKEY']}'",
			"ELEMENTO=WIDREQ",
			"WIDDOL='P'",
			"WIDSEQ='0'"
		);
		
		$where = array(
			"TABELLA='{$key_azi['WIDAZI']}'"
		);
	}else if($actionContext->getForm() == "MODIFICA_PARAMETRO") {
		$actionContext->setLabel("Modifica");
		
		require_once 'manutenzione_parametri_commons.php';
		
		$key_param = getListKeyArray($azione."_PARAMETRI");
		
		//showArray($key_ute);
		//showArray($key_param);
		
		$dati = getTracciatoParamAzione($key_azi['WIDAZI'], $key_ute['WIDKEY'], $key_param['ELEMENTO']);
		
		$row = $dati[0];
		$multi_val = array();
		if($row['MULTI']) {
			foreach($dati as $riga) {
				$multi_val[] = getValueParam($riga);
			}
		}
		
	}else if(in_array($actionContext->getForm(), array("INSERT_PARAMETRO", "UPDATE_PARAMETRO"))) {
		
		$key_param = getListKeyArray($azione."_PARAMETRI");
		
		$valore = wi400Detail::getDetailValue($azione."_MODIFICA_PARAM", "VALORE");
		
		//showArray($key_ute);
		//showArray($key_param);
		$error = false;
		if($form == "UPDATE_PARAMETRO") {
			$rs = deleteParametro($key_azi['WIDAZI'], $key_ute['WIDKEY'], $key_param['ELEMENTO']);
			if(!$rs) $error = true;
		}
		
		//echo "valore___".$valore."___<br/>";
		if(!$error)  {
			if(!is_array($valore)) $valore = array($valore);
			foreach($valore as $sequenza => $val) {
				$rs = insertParametro($key_azi['WIDAZI'], $key_ute['WIDKEY'], $key_param['ELEMENTO'], $val, $sequenza);
				if(!$rs) $error = true;
			}
			
			if(!$error) {
				$messageContext->addMessage("SUCCESS", "Parametro modificato con successo!");
			}
			
		}
		
		$actionContext->gotoAction("CLOSE", "CLOSE_WINDOW_MSG", "", true);
		
	}else if($actionContext->getForm() == "ELIMINA_CONF_PARAMETRO") {
		/*$key_param = getListKeyArray($azione."_PARAMETRI");
		
		$valore = wi400Detail::getDetailValue($azione."_MODIFICA_PARAM", "VALORE");*/
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $azione."_PARAMETRI");
		
		foreach($wi400List->getSelectionArray() as $key => $valori) {
			$key_param = get_list_keys_num_to_campi($wi400List, explode("|", $key));
			
			$rs = deleteParametro($key_azi['WIDAZI'], $key_ute['WIDKEY'], $key_param['ELEMENTO']);
			if($rs) {
				$messageContext->addMessage("SUCCESS", "Configurazione per il parametro {$key_param['ELEMENTO']} eliminata con successo!");
			}
		}
		
		$actionContext->gotoAction($azione, "LIST_PARAMETRI", "", true);
	}else if($actionContext->getForm() == "ORDINAMENTO_COL") {
		$ord_col = getOrdinamentoCol($key_azi['WIDAZI'], $key_det['WIDID'], $key_ute['WIDKEY']);
		
		$_SESSION['ABIL_ORD_COL'] = $ord_col;
		
		$actionContext->gotoAction("SORT_LIST&IDLIST={$azione}_ABIL", "DEFAULT", "", true);
	}else if($actionContext->getForm() == "RESET_ORDINAMENTO") {
		$where = array("WIDAZI='".$key_azi['WIDAZI']."'",
						"WIDID='".$key_det['WIDID']."'",
						"WIDKEY='".$key_ute['WIDKEY']."'");
		$sql = "UPDATE $tabella SET WIDSEQ=WIDSAV WHERE ".implode(" and ", $where);
		//$stmt_reset_ord = $db->prepare("UPDATE", $tabella, $where, array("WIDSEQ"));
		//$rs = $db->execute($stmt_reset_ord, array(0));
		$rs = $db->query($sql);
		
		if($rs) {
			$messageContext->addMessage("SUCCESS", "Reset ordinamento completato con successo!");
			elimina_cache();
		}else {
			$messageContext->addMessage("ERROR", "Errore durante il reset dell'ordinamento.");
		}
		
		//$query = "UPDATE $tabella SET WIDSEQ=0 WHERE WIDAZI='{$key_azi['WIDAZI']}' AND WIDID='{$key_det['WIDID']}' and WIDKEY='{$key_ute['WIDKEY']}'";
	
		$actionContext->gotoAction($azione, "LIST_ABILITAZIONI", "", true);
	}else if($actionContext->getForm() == "MAP_DETAIL") {
		$errore = false;
		$insert = false;
		$file_name = "ZWIDETPA";
		$fields_file = getDs($file_name);
		
		$isDetail = "L";
		if(isset($_REQUEST['MAP_DETAIL'])) {
			$widid = $_REQUEST['MAP_DETAIL'];
			$isDetail = "D";
			
			$detail = getDetail($widid);
			$widded = $detail['TITLE'];
		}else {
			$widid = $_REQUEST['MAP_LIST'];
			
			$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $widid);
			$widded = $wi400List->getTitle();
		}
		
		
		if(!isset($_REQUEST['TITLE_DETAIL'])) {
			$_REQUEST['TITLE_DETAIL'] = $widded;
		}
		
		$widazi = $_REQUEST['CURRENT_ACTION'];
		if(isset($_REQUEST['WIDAZI'])) {
			$widazi = $_REQUEST['WIDAZI'];
		}
		
		$sql = "SELECT WIDKEY FROM ZWIDETPA WHERE WIDAZI='".$widazi."' and WIDDOL='$isDetail' GROUP BY widkey";
		$rs = $db->query($sql);
		$utenti = array();
		while($row = $db->fetch_array($rs)) {
			$utenti[] = $row['WIDKEY'];
		}
		if(!$utenti) $utenti[] = "*ALL";
		
		$stmt_exists = $db->prepareStatement("SELECT WIDREQ FROM ZWIDETPA WHERE WIDAZI='".$widazi."' AND WIDID='$widid' AND WIDDOL='$isDetail' AND WIDKEY='".$utenti[0]."' AND WIDREQ=?");
		
		$fields_file['WIDAZI'] = $widazi;
		$fields_file['WIDDOL'] = $isDetail;
		$fields_file['WIDID'] = $widid;
		$fields_file['WIDDED'] = $_REQUEST['TITLE_DETAIL'];
		$fields_file['WIDREQ'] = "";
		$fields_file['WIDDER'] = " ";
		$fields_file['WIDABI'] = 1;
		$fields_file['WIDHID'] = 0;
		$fields_file['WIDDFT'] = 0;
		$fields_file['WIDDFV'] = "";
		$fields_file['WIDTYP'] = "";
		$fields_file['WIDFUN'] = "";
		$fields_file['WIDSAV'] = "0";
		$fields_file['WIDSTA'] = 1;
		$fields_file['WIDSEQ'] = 0;
		
		$stmt = $db->prepare("INSERT", $file_name, null, array_keys($fields_file));
		
		if($isDetail == "D") {
			
			
			$sql = "SELECT * FROM ZWIDETPA WHERE WIDAZI='*ALL_DETAIL' AND WIDKEY='*ALL'";
			$rs = $db->query($sql);
			while($row = $db->fetch_array($rs)) {
				$db->execute($stmt_exists, array($row['WIDREQ']));
				if(!$exi = $db->fetch_array($stmt_exists)) {
					foreach($utenti as $ute) {
						$fields_file['WIDKEY'] = $ute;
						$fields_file['WIDREQ'] = $row['WIDREQ'];
						$fields_file['WIDABI'] = $row['WIDABI'];
						$fields_file['WIDDFT'] = $row['WIDDFT'];
						$fields_file['WIDDFV'] = $row['WIDDFV'];
						$label = $row['WIDDER'];
						if (!isset($label)) $label ="";
						$fields_file['WIDDER'] = $label;
						$fields_file['WIDTYP'] = $row['WIDTYP'];
						$fields_file['WIDSTA'] = $row['WIDSTA'];
						
						$result = $db->execute($stmt, $fields_file);
						if(!$result) {
							$messageContext->addMessage("ERROR", "Errore mappatura dettaglio ".$_REQUEST['MAP_DETAIL']);
							$errore = true;
						}else {
							$insert = true;
						}
					}
				}
			}
			
			foreach($detail['FIELDS'] as $key => $field) {
				$db->execute($stmt_exists, array($field->getId()));
				if(!$row = $db->fetch_array($stmt_exists)) {
					foreach($utenti as $ute) {
						$fields_file['WIDKEY'] = $ute;
						$fields_file['WIDREQ'] = $field->getId();
						$fields_file['WIDABI'] = 1;
						$fields_file['WIDDFT'] = 0;
						$fields_file['WIDDFV'] = '';
						$label = $field->getLabel();
						if (!isset($label)) $label ="";
						$fields_file['WIDDER'] = $label;
						$fields_file['WIDTYP'] = $field->getType();
						$fields_file['WIDSTA'] = 1;
						
						$result = $db->execute($stmt, $fields_file);
						if(!$result) {
							$messageContext->addMessage("ERROR", "Errore mappatura dettaglio ".$_REQUEST['MAP_DETAIL']);
							$errore = true;
						}else {
							$insert = true;
						}
					}
				}
			}
			
			foreach($detail['BUTTONS'] as $bottone) {
				$db->execute($stmt_exists, array($bottone->getId()));
				if(!$row = $db->fetch_array($stmt_exists)) {
					foreach($utenti as $ute) {
						$fields_file['WIDKEY'] = $ute;
						$fields_file['WIDREQ'] = $bottone->getId();
						$fields_file['WIDABI'] = 1;
						$label = $field->getLabel();
						if (!isset($label)) $label ="";
						$fields_file['WIDDER'] = $label;
						$fields_file['WIDTYP'] = "BUTTON";
						$fields_file['WIDSTA'] = 1;
						
						$result = $db->execute($stmt, $fields_file);
						if(!$result) {
							$messageContext->addMessage("ERROR", "Errore mappatura bottone ".$bottone->getLabel());
							$errore = true;
						}else {
							$insert = true;
						}
					}
				}
			}
		}else {
			
			foreach($wi400List->getActions() as $action) {
				$db->execute($stmt_exists, array($action->getLabel()));
				if(!$row = $db->fetch_array($stmt_exists)) {
					foreach($utenti as $ute) {
						$fields_file['WIDKEY'] = $ute;
						$fields_file['WIDREQ'] = $action->getLabel();
						$fields_file['WIDDER'] = "";
						$fields_file['WIDTYP'] = "ACTION";
						$fields_file['WIDSTA'] = 1;
				
						$result = $db->execute($stmt, $fields_file);
						if(!$result) {
							$messageContext->addMessage("ERROR", "Errore mappatura lista tool ".$widid);
							$errore = true;
						}else {
							$insert = true;
						}
					}
				}
			}
			
			$sequenza = 1;
			foreach($wi400List->getCols() as $col) {
				$db->execute($stmt_exists, array($col->getKey()));
				if(!$row = $db->fetch_array($stmt_exists)) {
					foreach($utenti as $ute) {
						$fields_file['WIDKEY'] = $ute;
						$fields_file['WIDHID'] = $col->getShow() ? '0' : '1';
						$fields_file['WIDREQ'] = $col->getKey();
						$fields_file['WIDDER'] = substr($col->getDescription(), 0, 50);;
						$fields_file['WIDTYP'] = "COLUMN";
						$fields_file['WIDSEQ'] = $sequenza;
						$fields_file['WIDSAV'] = 0;//$sequenza;
						$fields_file['WIDSTA'] = 1;
				
						$result = $db->execute($stmt, $fields_file);
						if(!$result) {
							$messageContext->addMessage("ERROR", "Errore mappatura lista column ".$widid." -> ".$col->getDescription());
							$errore = true;
						}else {
							$insert = true;
							$sequenza++;
						}
					}
				}
			}
			
			$fields_file['WIDHID'] = '0';
			
			$sql = "SELECT * FROM ZWIDETPA WHERE WIDAZI='*ALL_LISTA' AND WIDKEY='*ALL'";
			$rs = $db->query($sql);
			while($row = $db->fetch_array($rs)) {
				$db->execute($stmt_exists, array($row['WIDREQ']));
				if(!$exi = $db->fetch_array($stmt_exists)) {
					foreach($utenti as $ute) {
						$fields_file['WIDKEY'] = $ute;
						$fields_file['WIDREQ'] = $row['WIDREQ'];
						$fields_file['WIDABI'] = $row['WIDABI'];
						$fields_file['WIDDFT'] = $row['WIDDFT'];
						$fields_file['WIDDFV'] = $row['WIDDFV'];
						$label = $row['WIDDER'];
						if (!isset($label)) $label ="";
						$fields_file['WIDDER'] = $label;
						$fields_file['WIDTYP'] = $row['WIDTYP'];
						$fields_file['WIDSTA'] = $row['WIDSTA'];
						$fields_file['WIDSEQ'] = 0;
						$fields_file['WIDSAV'] = "0";
								
						$result = $db->execute($stmt, $fields_file);
						if(!$result) {
							$messageContext->addMessage("ERROR", "Errore mappatura lista tool ".$widid);
							$errore = true;
						}else {
							$insert = true;
						}
					}
				}
			}
			
			foreach($wi400List->getTools() as $tool) {
				$name = explode("/", $tool->getIco());
				$name = explode(".", array_pop($name));
				$name = $name[0];
				
				$db->execute($stmt_exists, array($name));
				if(!$row = $db->fetch_array($stmt_exists)) {
					foreach($utenti as $ute) {
						$fields_file['WIDKEY'] = $ute;
						$fields_file['WIDREQ'] = $name;
						$fields_file['WIDDER'] = "";
						$fields_file['WIDTYP'] = "TOOL";
						$fields_file['WIDSTA'] = 1;
				
						$result = $db->execute($stmt, $fields_file);
						if(!$result) {
							$messageContext->addMessage("ERROR", "Errore mappatura lista tool ".$widid);
							$errore = true;
						}else {
							$insert = true;
						}
					}
				}
			}
		}
		
		if($insert && !$errore) {
			$messageContext->addMessage("SUCCESS", "Mappatura dettaglio eseguita con successo!");
		}else {
			$messageContext->addMessage("WARNING", "Mappatura aggiornata con successo!");
		}
		
		$actionContext->gotoAction($_REQUEST['CURRENT_ACTION'], $_REQUEST['CURRENT_FORM'], false, true);
	}