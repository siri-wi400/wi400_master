<?php

	require_once 'telnet_5250_common.php';

	$azione = $actionContext->getAction();
	$form = $actionContext->getForm();
	
	if(!in_array($form, array("LISTA", "ELIMINA", "ELIMINA_TUTTO"))) {
		$history->addCurrent();
	}
	
	$data_ini = wi400Detail::getDetailValue($azione."_PARAM", "DATA_INI");
	$data_fin = wi400Detail::getDetailValue($azione."_PARAM", "DATA_FIN");
	$ora_ini = wi400Detail::getDetailValue($azione."_PARAM", "ORA_INI");
	$ora_fin = wi400Detail::getDetailValue($azione."_PARAM", "ORA_FIN");
	
	/*echo $data_ini."_<br/>";
	echo $data_fin."_<br>";*/
	
	if($form == "DEFAULT") {
		
	}else if($form == "LISTA") {
		$history->removeLast();
		
		$history->addCurrent();
		
		$time_ini = time_to_timestamp($data_ini,$ora_ini);
		$time_fin = time_to_timestamp($data_fin,$ora_fin);
		
		//echo $time_ini."___".$time_fin."__<br/>";
		
		$where = array(
			"LOGSTP<>'Q'",
			"LOGSTM between '$time_ini' and '$time_fin'",
		);
	}else if(in_array($form, array("MONITOR", "MONITOR2"))) {
		$actionContext->setLabel("Mostra schermata");
		
		require_once "telnet_5250_class.php";
		
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $azione."_LIST");
		$rows = $wi400List->getSelectionArray();
		
		//inizio di ogni riga un 3
		//righe separate da un !
		$field_300 = "";
		$tagname = "";
		
		$righe = array();
		foreach($rows as $chiave => $val) {
			$key = get_list_keys_num_to_campi($wi400List, explode("|", $chiave));
			
			//showArray($key);
			//if($key['LOGSRO'] == 300) $field_300 = $key['LOGSTM'];
			$field_300 = $key['LOGSTM'];
			$righe[] = "3".substr($key['DATI'], 0, $key['LOGLEN']*2);
		}
		
		$dati = implode('!', $righe);
		
		$sql = "SELECT LOGSTM, LPGTAG FROM zopnlogs WHERE LOGSTM='$field_300'";
		$rs = $db->singleQuery($sql);
		$row = $db->fetch_array($rs);
		if($row) $tagname = $row['LPGTAG'];
		
	}else if($form == "ELIMINA") {
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $azione."_LIST");
		$rows = $wi400List->getSelectionArray();
		$time = array();
		foreach($rows as $chiave => $val) {
			$key = get_list_keys_num_to_campi($wi400List, explode("|", $chiave));
				
			$time[] = $key['LOGSTM'];
		}
		
		$query = "DELETE FROM zopnlogs WHERE LOGSTM IN ('".implode("', '", $time)."')";
		$rs = $db->query($query);
		if($rs) {
			$messageContext->addMessage("SUCCESS", "Eliminazione eseguita con successo");
		}else {
			$messageContext->addMessage("ERROR", "Errore durante l'eliminazione");
		}
		
		$actionContext->gotoAction($azione, "LISTA", "", true);
	}else if($form == "ELIMINA_TUTTO") {
		$query = "DELETE FROM zopnlogs WHERE LPGTAG=''";
		$rs = $db->query($query);
		if($rs) {
			$messageContext->addMessage("SUCCESS", "Eliminazione eseguita con successo");
		}else {
			$messageContext->addMessage("ERROR", "Errore durante l'eliminazione");
		}
		
		$actionContext->gotoAction($azione, "LISTA", "", true);
	}else if($form == "ADD_TAG") {
		$actionContext->setLabel("Aggiungi tag");
		
		if($_REQUEST['CURRENT_ACTION'] == "TELNET_5250") {
			$sql = "SELECT LOGSTM FROM zopnlogs WHERE LOGSRO='300' ORDER BY LOGSTM DESC";
			$rs = $db->singleQuery($sql);
			$row = $db->fetch_array($rs);
			
			$myField = new wi400InputText("TIMESTAMP");
			$myField->setValue($row['LOGSTM']);
			wi400Detail::setDetailField($azione."_INFO", $myField);
		}
	}else if($form == "SALVA_TAG") {
		$tagname = wi400Detail::getDetailValue($azione."_DETAIL_TAG", "TAGNAME");
		$timestamp = wi400Detail::getDetailValue($azione."_INFO", "TIMESTAMP");
		
		$rs = add_tag($tagname, $timestamp);
		if($rs) {
			$messageContext->addMessage("SUCCESS", "Tag aggiunto con successo.");
		}else {
			$messageContext->addMessage("ERROR", "Errore aggiunta tag.");
		}
		
		if($_REQUEST['DA_DOVE'] == "TELNET_5250") {
			$actionContext->gotoAction("CLOSE", "CLOSE_LOOKUP", "", true);
		}else {
			$actionContext->gotoAction("CLOSE", "CLOSE_WINDOW_MSG", "", true);
		}
	}
	