<?php

	$azione = $actionContext->getAction();
	
	if($actionContext->getForm()=="DEFAULT") {
		if($actionContext->getGateway()=="WI400_WIDGET") {
			$actionContext->gotoAction($azione, "ACTION&ALL_MESS=1", "WI400_WIDGET", true);
		}
	}
	
	if(!isset($area))
		$area = "";
