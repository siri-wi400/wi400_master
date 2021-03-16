<?php

	$azione = $actionContext->getAction();
	$form = $actionContext->getForm();
	require_once "developer_functions.php";
	require_once "developer_auth.php";
	
	// Controllo se abilitato XMLSERVICE - AS400
	$showxml=False;
	if (isset($settings["xmlservice"]) && $settings["xmlservice"] == True){
		$showxml=True;
	}
	
	if($form == "DETAIL") {
		$_SESSION['DEVELOPER_MAPPING_OBJ'] = $_SESSION['DEVELOPER_RUNTIME_FIELD']['WI400'];
	}
	
	if($form == "ELIMINA_FILE") {
		// CAPISCO CHI MI HA CHIMATO E FACCIO LA CANCELLAZIONE
		unlink($_REQUEST['FILE']);
		$actionContext->gotoAction($azione, "DETAIL", True, True);
	}
