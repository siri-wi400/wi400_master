<?php
$azione = $actionContext->getAction();
$form = $actionContext->getForm();
require_once "developer_auth.php";
if($actionContext->getForm()=="DETAIL") {
	$sessione = wi400Detail::getDetailValue("ROUTINE_VIEWER_DETAIL_SRC", "SESSIONE");
} else if($form == "ELIMINA") {
	$sql="DELETE FROM XMLSERVLOG/\"LOG\" WHERE \"KEY\" LIKE '".$sessione."%'";
	$db->query($sql);
	$actionContext->gotoAction($azione, "DETAIL", True);
}