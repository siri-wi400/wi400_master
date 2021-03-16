<?php

	require_once 'icone_font_awesome_commons.php';
	require_once 'icone_font_awesome_functions.php';

	$azione = $actionContext->getAction();
	$form = $actionContext->getForm();
	
	if(!in_array($form, array("ciao")))
		$history->addCurrent();

	if($form == 'DEFAULT') {
		
	}