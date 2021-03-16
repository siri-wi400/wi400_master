<?php
require_once "developer_functions.php";
$azione = $actionContext->getAction();
if($actionContext->getForm()=="DEFAULT") {
	$history->addCurrent();
} else if ($actionContext->getForm()=="DETAIL") {
	$file = wi400Detail::getDetailValue("NAVIGATE_OBJECT_DETAIL", "FILE");
	$tipo = wi400Detail::getDetailValue("NAVIGATE_OBJECT_DETAIL", "TIPO");
	$gateway = wi400Detail::getDetailValue("NAVIGATE_OBJECT_DETAIL", "FROM_GATEWAY");
}