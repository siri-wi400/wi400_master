<?php 

//	echo "MENU GATEWAY<br>";
	
	if($actionContext->getGateway()=="AZIONI") {
		$menu = wi400Detail::getDetailValue("SEARCH_ACTION","codazi");
		
		$gatewayContext->azione = new wi400InputText("codmen");
		$gatewayContext->azione->setValue($menu);
		
		wi400Detail::setDetailField("ricercaMenu",$gatewayContext->azione);
	}

?>