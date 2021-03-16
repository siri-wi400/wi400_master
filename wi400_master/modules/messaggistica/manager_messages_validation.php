<?php

	//echo $actionContext->getForm();
	if(in_array($actionContext->getForm(), array("NEW_MESSAGE", "MOD_MESSAGE"))) {
		$tespub = date('d/m/Y');
		$tessca = "";
		$tescle = "N";
		$tesrpy = "N";
		$tesevr = "N";
		$tesprv = "N";
		$error = "";
		
		if(isset($_REQUEST['TESCLE'])) {
			$tescle = "S";
		}
		if(isset($_REQUEST['TESRPY'])) {
			$tesrpy = "S";
		}
		if(isset($_REQUEST['TESPRV'])) {
			$tesprv = "S"; 
			//$_REQUEST['TESDIV'] = "*IMMED";
		}
		if(isset($_REQUEST['TESEVR'])) {
			$tesevr = "S";
		}
		if(isset($_REQUEST['TESPUB']) && $_REQUEST['TESPUB']) {
			$tespub = $_REQUEST['TESPUB'];
		}
		/*if(isset($_REQUEST['TESSCA']) && !$_REQUEST['TESSCA']) {
			list($gg, $mm, $aaaa) = explode("/", $tespub);
			$tessca = date('d/m/Y',strtotime($gg."-".$mm."-".$aaaa."+10 year"));
		}else {
			$tessca = $_REQUEST['TESSCA'];
		}*/
		
		if($_REQUEST['TESVIS'] == "*ACTION" && !$_REQUEST['TESAZI']) {
			$messageContext->addMessage("ERROR", "Hai settato visualizzazione a '*ACTION'! Campo azione vuoto!", "TESAZI");
			$error = 1;
		}
		
		$query = "SELECT DSTTYP FROM ZMSGDST WHERE DSTID='{$_REQUEST['TESID']}' AND DSTTYP IN ('*INT', '*ENTE')";
		$result = $db->query($query);
		
		if(!$db->num_rows($result) || $_REQUEST['TESDIV'] == "*LOGIN") {
			if($tesprv == "S" && $_REQUEST['TESDIV'] != "*IMMED") {
				$messageContext->addMessage("ERROR","Il campo divulgazione deve essere *IMMED con messaggio chiuso a 'si'", "TESDIV");
				$error = 1;
			}
		}else {
			$messageContext->addMessage("ERROR","Il campo non pu&ograve; essere settato *IMMED con destinatari *INT o *ENTE", "TESDIV");
			$error = 1;
		}
		//echo "Periodo $tespub > $tessca?: ".check_periodo($tespub, $tessca);
		if(!check_periodo($_REQUEST['TESPUB'], $_REQUEST['TESSCA'])) {
			$messageContext->addMessage("ERROR", "Errore data pubblicazione maggiore della data di scadenza" , "TESPUB");
			$error = 1;
		}
		
		if($error) {
			//$messageContext->addMessage("ERROR", implode("<br/>", $error));
			
			$_REQUEST['CLEAR_DETAIL_MESSAGE'] = "no";
		}
	}else if(in_array($actionContext->getForm(), array("NEW_DESTINATARIO", "MOD_DESTINATARIO"))) {
		global $users_table;
				
		/*if($_REQUEST['DSTTYP'] == "*USER") {
			$sql = "SELECT USER_NAME FROM ".$users_table." WHERE USER_NAME='".$_REQUEST['DSTDST']."'";
			
			$rs = $db->singleQuery($sql);
			if(!$db->num_rows($rs)) {
				$error_vali = 1;
				
				$messageContext->addMessage("ERROR", "Destinatario inesistente!" , "DSTDST");
			}
		}else*/
		
		//showArray($_REQUEST);
		
		//$query = "SELECT tesdiv FROM zmsgtes WHERE tesid='{$_REQUEST['MSG_ID']}' AND tesdiv=''"
		
		//if($_REQUEST['DSTTYP'] != "*INT" && $_REQUEST['DSTTYP'] != "*ENTE" && $_REQUEST['TESDIVULG'] != '*IMMED') {
		if(($_REQUEST['DSTTYP'] != "*INT" && $_REQUEST['DSTTYP'] != "*ENTE") || $_REQUEST['TESDIVULG'] != '*IMMED') {
			if ($_REQUEST['DSTTYP'] == "*GRUPPO") {
				$gruppi = explode(";", $settings['wi400_groups']);
	
				if(!in_array($_REQUEST['DSTDST'], $gruppi)) {
					$error_vali = 1;
					
					$messageContext->addMessage("ERROR", "Gruppo inesistente!" , "DSTDST");
				}
			}
		}else {
			$messageContext->addMessage("ERROR", "Impossibile salvare il destinatario con tipo divulgazione *IMMED");
		}
	}