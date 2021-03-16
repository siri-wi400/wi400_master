<?php
if ($actionContext->getGateway() == "UTENTI") {
	$fieldObj = new wi400InputText("tipo");
	$fieldObj->setValue("UTE");
	wi400Detail::setDetailField("IMAGE_WINDOW_MANAGER_SRC", $fieldObj);

	$fieldObj = new wi400InputText("articolo");
	$fieldObj->setValue($_POST["user"]);
	wi400Detail::setDetailField("IMAGE_WINDOW_MANAGER_SRC", $fieldObj);

}
if ($actionContext->getGateway() == "ARTICOLI") {
	$fieldObj = new wi400InputText("tipo");
	$fieldObj->setValue("ART");
	wi400Detail::setDetailField("IMAGE_WINDOW_MANAGER_SRC", $fieldObj);
	$fieldObj = new wi400InputText("codice");
	$codice = wi400Detail::getDetailValue("DETTAGLIO_ARTICOLI", "codart");
	//echo "<br>Da Detail:".$codice;
	$fieldObj->setValue($_REQUEST["codart"]);
	wi400Detail::setDetailField("IMAGE_WINDOW_MANAGER_SRC", $fieldObj);
}