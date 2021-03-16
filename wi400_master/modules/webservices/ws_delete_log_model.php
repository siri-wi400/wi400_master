<?php

	$azione = $actionContext->getAction();
	
	$history->addCurrent();
	
	if($actionContext->getForm()=="DEFAULT") {
		$actionContext->setLabel("Parametri");
	}else if($actionContext->getForm()=="DELETE_LOG") {
		$actionContext->setLabel("DELETE_LOG");
		
		$data_eliminazione = $_REQUEST['DATA_ELIMINAZIONE'];
		
		list($gg, $mm, $yyyy) = explode("/", $data_eliminazione);
		
		$data_eliminazione = "$yyyy-$mm-$gg";
		
		//$messageContext->addMessage("SUCC", "Pulizia eseguita con successo!");
		
		//$actionContext->gotoAction($azione, "DEFAULT", false, true);
	}