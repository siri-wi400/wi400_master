<?php

	$azione = $actionContext->getAction();
	
	$actionContext->setLabel("Help Tool");
	
	$html = get_template_html($_REQUEST['ARGOMENTO'], $_REQUEST['SCHEDA']);
	
	if(!$html) {
		$html = "Documento non trovato";
	}