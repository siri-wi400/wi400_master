<?php

	require_once $moduli_path.'/user/estensione_utenti_vega_common.php';

	$azione = $actionContext->getAction();
	
	$steps = $history->getSteps();
//	echo "STEPS:<pre>"; print_r($steps); echo "</pre>";
	
	if(empty($steps)) {
		wi400Detail::cleanSession($azione."_CHECK_PIN_SRC");
	}
	
	if($actionContext->getForm()=="DEFAULT") {
		$history->addCurrent();
		
		$sql_amm = "select * from ".$tipo_tab_ext." where USER_NAME='".$_SESSION['user']."'";
		$res_amm = $db->singleQuery($sql_amm);
		
		$tipo_amm = "";
		$pin_amm = "";
		
		if($row_amm = $db->fetch_array($res_amm)) {
			$tipo_amm = $row_amm['TIPOUSR'];
			$pin_amm = $row_amm['USRPIN'];
		}
//		echo "TIPO AMM: $tipo_amm - PIN AMM: $pin_amm<br>";
		
//		$campo_pin = "CHECK_PIN";
		
		$pin_fields = wi400Detail::getDetailFields($azione."_CHECK_PIN_SRC");
//		echo "PIN_FIELDS:<pre>"; print_r($pin_fields); echo "</pre>";
		
		$campo_pin = "";
		foreach($pin_fields as $key => $val) {
			if(substr($key, 0, strlen("CHECK_PIN_"))=="CHECK_PIN_")
				$campo_pin = $key;
		}
		
//		echo "CAMPO PIN: $campo_pin<br>";
		
		if(wi400Detail::getDetailValue($azione."_CHECK_PIN_SRC", $campo_pin)!="")
			$check_pin = wi400Detail::getDetailValue($azione."_CHECK_PIN_SRC", $campo_pin);
		
//		echo "CHECK PIN: "; var_dump($check_pin); echo "<br>";
		
		$has_access = false;
		if(isset($check_pin) && !empty($check_pin)) {
			if($check_pin===$pin_amm) {
				// PIN corretto
//				$messageContext->addMessage("SUCCESS", "PIN Corretto. Accesso consentito.");
				$has_access = true;
			}
			else {
				// PIN errato -> NON ha accesso
				$messageContext->addMessage("ERROR", "PIN Errato! Accesso non consentito. Riprovare.");
				$actionContext->gotoAction($azione, "CHECK_PIN_SEL", "", true);
			}
		}
		else {
			if($tipo_amm=="SAM") {
				// Utente SOLO Amministrativo -> Accesso
				$has_access = true;
			}
			else if($tipo_amm=="AAM") {
				// Utente ANCHE Amministrativo -> Accesso con PIN
				$actionContext->gotoAction($azione, "CHECK_PIN_SEL", "", true);
			}
//			else if($tipo_amm=="NAM") {
			else {
				// Utente NON Amministrativo -> NON ha accesso
				$messageContext->addMessage("WARNING", "Utente NON Amministrativo. Accesso non consentito.");
			}
		}
		
		if($has_access===true)
			$actionContext->gotoAction("QUERY_TOOL_DB", "DEFAULT", "", true);
		else
			$actionContext->onError($azione, "DEFAULT", "", "", true);
	}
	else if($actionContext->getForm()=="CHECK_PIN_SEL") {
		if($messageContext->getSeverity()!="ERROR")
			$messageContext->addMessage("WARNING", "Utente ANCHE Amministrativo. Inserire il PIN.");
	}