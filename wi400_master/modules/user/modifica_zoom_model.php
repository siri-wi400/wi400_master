<?php

	$azione = $actionContext->getAction();
	$form = $actionContext->getForm();
	
	if($form == "DEFAULT") {
		$init_scale = isset($_SESSION['zoom_scale']) ? $_SESSION['zoom_scale'] : "1.1";
	}else if($form == "SALVA") {
		$_SESSION['zoom_scale'] = $_REQUEST['ZOOM'];
		
		$actionContext->gotoAction("CLOSE", "CLOSE_WINDOW", "", true);
	}