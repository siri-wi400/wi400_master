<?php

	require_once 'log_commons.php';
	
	$steps = $history->getSteps();
	
	if(in_array("BATCHJOB_USER_DEFAULT", $steps)) {
		$exclude_types = array("OUT", "TXT");
	}

	require_once $moduli_path.'/analisi/files_manager_model.php';
	
	$actionContext->setLabel('Elenco dei files');