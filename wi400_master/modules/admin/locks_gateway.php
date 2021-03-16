<?php
if($actionContext->getGateway()=="DEVELOPER_DOC") {
	
	$sessione = wi400Detail::getDetailValue("DEVELOPER_DOC_SRC", "SESSIONE");

	$int = new wi400InputText("SESSIONE");
	$int->setValue($sessione);
	wi400Detail::setDetailField("LOCKS_SRC",$int);
	
	$gat = new wi400InputText("FROM_GATEWAY");
	$gat->setValue("DEVELOPER_DOC");
	wi400Detail::setDetailField("LOCKS_SRC",$gat);
	wi400Detail::cleanSession("LOCKS_DETAIL");
	
}