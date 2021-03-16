<?php

	$azione = $actionContext->getAction();
//	echo "AZIONE: $azione<br>";
//	echo "FORM: ".$actionContext->getForm()."<br>";
//	echo "GATEWAY: ".$actionContext->getGateway()."<br>";
/*
	if ($actionContext->getGateway()=="NEW_EMAIL") {
		wi400Detail::cleanSession($azione."_SRC");
		
//		$id = wi400Detail::getDetailValue($azione."_NEW_EMAIL_DET", "ID");
		$id = $_REQUEST['ID_EMAIL'];
//		echo "ID: $id<br>";
		
		$fieldObj = new wi400InputText("ID_SRC");
		$fieldObj->setValue(array($id));
		wi400Detail::setDetailField($azione."_SRC", $fieldObj);
	}
*/