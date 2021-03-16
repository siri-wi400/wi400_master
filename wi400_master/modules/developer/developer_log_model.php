<?php
$azione = $actionContext->getAction();
$form = $actionContext->getForm();
require_once "developer_functions.php";
require_once "developer_auth.php";
if($form == "ELIMINA_FILE") {
	// CAPISCO CHI MI HA CHIMATO E FACCIO LA CANCELLAZIONE
	unlink($_REQUEST['FILE']);
	$actionContext->gotoAction($azione, "DEFAULT", True, True);
}
// Controllo se abilitato XMLSERVICE - AS400
$showxml=False;
if (isset($settings["xmlservice"]) && $settings["xmlservice"] == True){
	$showxml=True;
}