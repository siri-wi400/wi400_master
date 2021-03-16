<?php
if ($actionContext->getForm() == "PANEL") {
	$azioniDetail = $wi400GO->getObject('DEVELOPER_MASTER');
	$azioniDetail->addTab("user_1", "Lock");
	
	// LOCKS 
	$scheda = "user_1";
	$iframe = new wi400Iframe("user_1", "LOCKS", "DETAIL", "DEVELOPER_DOC");
	$iframe->setDecoration("lookup");
	$iframe->setStyle("height: 100%;");
	$iframe->setAutoLoad(True);
	$myField = new wi400InputText('DEVELOPER_LOCKS');
	$myField->setCustomHTML($iframe->getHtml());
	$myField->setHeight(500);
	$azioniDetail->addField($myField, $scheda);
	
}