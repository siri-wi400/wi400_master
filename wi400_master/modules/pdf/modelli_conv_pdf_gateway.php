<?php

	$azione = $actionContext->getAction();

	if($actionContext->getGateway()=="MODELLO_NEW") {
		wi400Detail::cleanSession($azione."_SRC");
		
		$modello = wi400Detail::getDetailValue($azione."_MODELLO_NEW_DET", "MODNAM");
		
		$fieldObj = new wi400InputText("CODMOD_SRC");
		$fieldObj->setValue(array($modello));
		wi400Detail::setDetailField($azione."_SRC", $fieldObj);
	}