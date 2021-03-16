<?php
require_once "developer_auth.php";
$azione = $actionContext->getAction();
if($actionContext->getForm()=="DETAIL") {
	$sessione = wi400Detail::getDetailValue("TEMP_TABLE_SRC", "SESSIONE");
	subfileDelete("TEMP_TABLE");
}
if($actionContext->getForm()=="CANCELLA") {
	$keyArray = getListKeyArray("TEMP_TABLE_LIST_LIST");
	$tabella = $keyArray['TABLENAME'];
	$query = "DROP TABLE ".$settings['db_temp']."/$tabella";
	$db->query($query);
	$messageContext->addMessage("SUCCESS", "Cancellazione Eseguita");
	$actionContext->onSuccess($azione, "DETAIL");
	$actionContext->onError($azione,"DETAIL","","",true);
}	
?>