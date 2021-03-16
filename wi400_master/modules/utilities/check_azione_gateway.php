<?php

 if($actionContext->getGateway()=="CHECK_AZIONE") {
	$codazi = wi400Detail::getDetailValue("SEARCH_ACTION","codazi");
	
	$gatewayContext->azione = new wi400InputText("CODAZI");
	$gatewayContext->azione->setValue($codazi);
	wi400Detail::setDetailField("Check Action",$gatewayContext->azione);
}