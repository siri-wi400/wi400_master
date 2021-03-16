<?php

	$azione = $actionContext->getAction();
	
	$history->addCurrent();
	
	if($actionContext->getForm()=="DEFAULT") {
		$actionContext->setLabel("Parametri");
		//$fileOutput = wi400File::getCommonFile("REQUEST", "REQ_56387a8edbe359.18834180.txt");
	}else if($actionContext->getForm() == "LIST") {
		
		$utente = $_REQUEST['ZEUTE'];
		$t = $_REQUEST['ZEAZI'];
		$data_ini = $_REQUEST['DATA_INI'];
		$data_fin = $_REQUEST['DATA_FIN'];
		$ora_ini = $_REQUEST['ORA_INI'];
		$ora_fin = $_REQUEST['ORA_FIN'];
		$ip = $_REQUEST['ZEIP'];
		$sessione = $_REQUEST['ZESES'];
		
		$where = array();
		$fields = array();
		if($utente) {
			$where[] = "ZEUTE='$utente'";
			$fields['Utente'] = $utente;
		}
		if($t) {
			$where[] = "ZEAZI='$t'";
			$fields['Azione'] = $t;
		}
		if($data_ini && $data_fin) {
			if(!$ora_ini) $ora_ini = "00:00";
			if(!$ora_fin) $ora_fin = "23:59";
			
			$time_ini = time_to_timestamp($data_ini, $ora_ini);
			$time_fin = time_to_timestamp($data_fin, $ora_fin);
			
			$where[] = "ZETIM >='".$time_ini."' AND ZETIM<='".$time_fin."'";
			$fields['Periodo'] = "Dalle $ora_ini del $data_ini alle $ora_fin del $data_fin";
		}
		if($ip) {
			$where[] = "ZEIP='$ip'";
			$fields['Ip'] = $ip;
		}
		if($sessione) {
			$where[] = "ZESES='$sessione'";
			$fields['ID sessione'] = $sessione;
		}
	}else if($actionContext->getForm() == "READ_REQUEST_FILE") {
		$actionContext->setLabel("Dati request");
		$key = getListKeyArray($azione."_LIST");
	}