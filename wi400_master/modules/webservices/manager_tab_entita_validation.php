<?php
	
	$stato = '0';
	if(isset($_REQUEST['AENSTA']) && $_REQUEST['AENSTA']) {
		$stato = '1';
	}
	else if(isset($_REQUEST['ASESTA']) && $_REQUEST['ASESTA']) {
		$stato = '1';
	}
	$autentic = 'N';
	if(isset($_REQUEST['ASEAUT']) && $_REQUEST['ASEAUT']) {
		$autentic = 'S';
	}
	
	if($actionContext->getForm() == "NEW_REC") {
		//showArray($_REQUEST);
		$sql = "SELECT aencod FROM faentita where aencod='".$_REQUEST['AENCOD']."'";
		$res = $db->singleQuery($sql);
		if($row = $db->fetch_array($res)) {
			$messageContext->addMessage("ERROR","I'entità ".$_REQUEST['AENCOD']." è già presente nella tabella");
		}
	}
	else if($actionContext->getForm() == "NEW_SEGMEN") {
		$sql = "SELECT asecod FROM fasegmen where aseent='".$_REQUEST['ASEENT']."' and asecod='".$_REQUEST['ASECOD']."'";
		$res = $db->singleQuery($sql);
		if($row = $db->fetch_array($res)) {
			$messageContext->addMessage("ERROR","Il segmento ".$_REQUEST['ASECOD']." con entità ".$_REQUEST['ASEENT']." è già presente nella tabella");
		}
	}
	else if($actionContext->getForm() == "MOD_SEGMEN") {
		if(!isset($_REQUEST['ROUTINE_PHP']) && $_REQUEST['ASERIN'] == "") {
			$_REQUEST['COD_ENT'] = $_REQUEST['ASEENT'];
			$messageContext->addMessage("ERROR","Campo rout. reperimento info vuoto!");
		}
	}