<?php
if($actionContext->getGateway() == 'TO_DETTAGLIO_CAMPI') {
	// LIBRERIA
	$keyArray = getListKeyArray("CONSOLE_GIWI400_FORM_LIST");
	$fieldObj = new wi400InputText("LIBRERIA");
	$fieldObj->setValue($keyArray['COL2']);
	wi400Detail::setDetailField("DATI_GIWI400", $fieldObj);
	// FILE
	$fieldObj = new wi400InputText("FILE");
	$fieldObj->setValue($keyArray['COL1']);
	wi400Detail::setDetailField("DATI_GIWI400", $fieldObj);
	// Creazione lista al volo per il parametro del form
	$formato = $keyArray['COL0'];
	$file = $keyArray['COL1'];
	$libreria = $keyArray['COL2'];
	// Prima lista Virtuale
	$cliVirtualList = new wi400List("DATI_GIWI400_LIST");
	$cliVirtualList->addKey('OT5KEY');
	$cliVirtualList->addKey('OT5LIB');
	$cliVirtualList->addKey('OT5FIL');
	$sa = array();
	$key=$file."_".$libreria."|".$libreria."|".$file;
	$sa[$key] = "";
	$cliVirtualList->setSelectionArray($sa);
	saveList("DATI_GIWI400_LIST", $cliVirtualList);
	// Seconda Lista Virtuale per FORM
	$cliVirtualList = new wi400List("DATI_GIWI400_LIST_FORM");
	$cliVirtualList->addKey('OT5FMT');
	$sa = array();
	$key=$formato;
	$sa[$key] = "";
	$cliVirtualList->setSelectionArray($sa);
	saveList("DATI_GIWI400_LIST_FORM", $cliVirtualList);
	
}