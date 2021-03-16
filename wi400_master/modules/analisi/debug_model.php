<?php
	
	$azione = $actionContext->getAction();

	if ($actionContext->getForm() == "SAVE"){
		if(isset($_SESSION["DEBUG"]) && !isset($_REQUEST["DEBUG_ON"])) {
			unset($_SESSION["DEBUG"]);
			$messageContext->addMessage("ALERT","Debug disattivato!");
		}
		else if(!isset($_SESSION["DEBUG"]) && isset($_REQUEST["DEBUG_ON"])) {
			$_SESSION["DEBUG"] = true;
			$messageContext->addMessage("SUCCESS","Debug attivato!");
		}
		
		if(isset($_SESSION["XMLSERVICE_DEBUG"]) && !isset($_REQUEST["XMLSERVICE_DEBUG"])) {
			unset($_SESSION["XMLSERVICE_DEBUG"]);
			$_SESSION["XMLSERVICE_DEBUG_ACTIVE"] = false;
			$messageContext->addMessage("ALERT","Debug xmlservice disattivato!");
		}
		else if(!isset($_SESSION["XMLSERVICE_DEBUG"]) && isset($_REQUEST["XMLSERVICE_DEBUG"])) {
			$_SESSION["XMLSERVICE_DEBUG"] = true;
			$_SESSION["XMLSERVICE_DEBUG_ACTIVE"] = false;
			$messageContext->addMessage("SUCCESS","Debug xmlservice attivato!");
		}
	}
	
	if ($actionContext->getForm() == "SET_XMLSERVICE_DEBUG_ACTIVE") {
		if($_REQUEST['value']=="0") {
			$_SESSION['XMLSERVICE_DEBUG_ACTIVE'] = false;
		}else {
			$_SESSION['XMLSERVICE_DEBUG_ACTIVE'] = true;
		}
	}
	
?>