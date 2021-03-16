<?php

$azione = $actionContext->getAction();

$history->addCurrent();

// Paramentri scelta libreria
if($actionContext->getForm()=="DEFAULT") {
	$actionContext->setLabel("Parametri");
}

// Dettaglio Sorgenti
elseif($actionContext->getForm()=="LIST") {
	$libreria = wi400Detail::getDetailValue($azione."_DET", 'LIBRERIA');
	$actionContext->setLabel("ListaSRC");
	// Query SQL
	$where_array = array();
	//$where_array[] = "SYSTEM_TABLE <> 'Y'";
	//$where_array[] = "FILE_TYPE = 'D'";
	$where_array[] = "TABLE_TYPE IN('P', 'T')";
	$where_array[] = "TABLE_SCHEMA='$libreria'";
	//showArray($where_array);

}

// Dettaglio Membri
elseif($actionContext->getForm()=="LMBR") {
	$libreria = wi400Detail::getDetailValue($azione."_DET", 'LIBRERIA');
	$actionContext->setLabel("ListaMBR");
	// Parametri FORM LIST
	$implis = getListKeyArray($azione."_LIST");
	$file = $implis['TABLE_NAME'];
	// Query SQL
	$where_array = array();
	// Tipo movimento
	//$where_array[]="TABLE_NAME='".$implis['TABLE_NAME']."'";
	//$where_array[] = "TABLE_SCHEMA='$libreria'";
	//showArray($where_array);
	
	// Oggetto per routine ZMBRTMP - Estrazione Contenuto File
	$zmbrtmp = new wi400Routine('zmbrtmp', $connzend);
    $zmbrtmp ->load_description();
	$zmbrtmp->prepare();
	$zmbrtmp->set('LIBRERIA', $libreria);
	$zmbrtmp->set('FILE', $file);
	$zmbrtmp->set('TIPO', "*ALL");
	// Punto il record da aggiornare
	$zmbrtmp->set('FLAGO', '1');
	$zmbrtmp->call();
	//die("SUCCESS");

}

// Contenuto File
elseif($actionContext->getForm()=="LCON") {
	$libreria = wi400Detail::getDetailValue($azione."_DET", 'LIBRERIA');
	$actionContext->setLabel("ContenutoFile");
	// Parametri FORM LIST
	$implis = getListKeyArray($azione."_LIST");
	$file = $implis['TABLE_NAME'];
	// Parametri FORM LMBR
	$impmbr = getListKeyArray($azione."_LMBR");
	$membro = $impmbr['SRCNAM'];

}


// Contenuto PGM
elseif($actionContext->getForm()=="LCOM") {
	$libreria = wi400Detail::getDetailValue($azione."_DET", 'LIBRERIA');
	$oggetto=$_REQUEST['OGGETTO'];
	$actionContext->setLabel("ContenutoPGM");
	// Parametri FORM LIST
	$implis = getListKeyArray($azione."_LIST");
	$file = $implis['TABLE_NAME'];
	// Parametri FORM LMBR
	$impmbr = getListKeyArray($azione."_LMBR");
	$membro = $impmbr['SRCNAM'];
	// Parametri FORM LMBR
	$impmbc = getListKeyArray($azione."_LCON");
	$membro = $impmbc['SRCNAM'];
	
	// Oggetto per routine ZCBRTMP - Reperimento source
	$zcbrtmp = new wi400Routine('zcbrtmp', $connzend);
	$zcbrtmp ->load_description();
	$zcbrtmp->prepare();
	$zcbrtmp->set('PGM', $oggetto);
	// Punto il record da aggiornare
	$zcbrtmp->set('FLAGO', '1');
	$zcbrtmp->call();
	//die("SUCCESS");
	$libreria=$zcbrtmp->get('LIBRERIA');
	$file=$zcbrtmp->get('FILE');
}