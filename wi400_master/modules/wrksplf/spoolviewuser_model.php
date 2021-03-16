<?php

	require_once 'spoollist_model_common.php';
	
	$spooluser = $_SESSION['user'];
		
	if($actionContext->getForm()=="DEFAULT") {
		$actionContext->setLabel(_t('SPOOL_TITLE').$spooluser);
		
		$history->addCurrent();
		
		$desc = array(
	        "username" => $spooluser,
		    "outq" => "*ALL",
	        "userdata" => "*ALL"
    	);
	}

?>