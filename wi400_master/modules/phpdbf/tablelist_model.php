<?php
	$keyArray = getListKeyArray("LIBLIST");
	$actionContext->setLabel("Tabelle libreria:".$keyArray['LIBNAME']);

	$history->add($actionContext, "TABLELIST");
	$libreria = $keyArray['LIBNAME'];
	
	wi400Detail::cleanSession("GESTIONE_DATA_SI");
?>