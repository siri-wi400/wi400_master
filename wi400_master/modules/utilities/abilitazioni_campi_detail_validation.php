<?php
	$curr_azione = $_REQUEST['CURRENT_ACTION'];

	if($actionContext->getForm() == "DETAIL_COPIA") {
		$key_azi = getListKeyArray($curr_azione."_AZIONI");
		$key_det = getListKeyArray($curr_azione."_DETAIL");
		
		//Utente da copiare
		$azione_det = $key_azi['WIDAZI'];
		$detailId = $key_det['WIDID'];
		$arr_utenti = $_REQUEST['TIPO_UTENTE'] ? $_REQUEST['TIPO_UTENTE'] : array();
		$arr_gruppi = $_REQUEST['TIPO_GRUPPO'] ? $_REQUEST['TIPO_GRUPPO'] : array();
		$da_copiare = array_merge($arr_utenti, $arr_gruppi);
		
		/*showArray($arr_utenti);
		showArray($arr_gruppi);
		showArray($da_copiare);*/
		
		if(!$arr_utenti && !$arr_gruppi) {
			$messageContext->addMessage("ERROR", "Valorizzare uno dei 2 campi!");
		}
		
		$sql = "SELECT widkey FROM zwidetpa WHERE widazi='$azione_det' AND WIDID='$detailId' and WIDDOL='{$key_det['WIDDOL']}' and  WIDKEY in ('".implode("', '", $da_copiare)."')
				GROUP BY WIDKEY";
		$rs = $db->query($sql);
		$has_user = array();
		while($row = $db->fetch_array($rs)) {
//			$messageContext->addMessage("ERROR", $row['WIDKEY']." gi&agrave; esistente");
			$has_user[] = $row['WIDKEY'];
		}
		
		if(!empty($has_user)) {
			$messageContext->addMessage("ERROR", "Operazione di copia non eseguita, i seguenti utenti esistono già: ".implode(", ", $has_user));
		}
		
	}else if($actionContext->getForm() == "NUOVO_PARAMETRO") {
		$nome_param = $_REQUEST['NOME_PARAMETRO'];
		
		$key_azi = getListKeyArray($curr_azione."_AZIONI");
		
		$sql = "SELECT widreq FROM zwidetpa WHERE widazi='{$key_azi['WIDAZI']}' and widreq='$nome_param' and widdol='P'";
		$rs = $db->singleQuery($sql);
		if($row = $db->fetch_array($rs)) {
			$messageContext->addMessage("ERROR", "Nome parametro gi&agrave; esistente!", "NOME_PARAMETRO");
		}
		
		$actionContext->onError($curr_azione, "NUOVO_PARAMETRO", "", "", true, false);
	}