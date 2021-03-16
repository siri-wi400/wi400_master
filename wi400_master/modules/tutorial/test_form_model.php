<?php


	if (($actionContext->getForm() == "DEFAULT" || $actionContext->getForm() == "CANCEL")
			&& $messageContext->getSeverity() != "ERROR"){
		
		wi400Detail::cleanSession("TEST_VALIDATION");
		
	}
	
?>