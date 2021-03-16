<?php
	require_once "developer_auth.php";
    
	$keyArray = getListKeyArray("TEMP_TABLE_LIST_LIST");
	$actionContext->setLabel("Tabella:".$keyArray['TABLENAME']);
	$history->addCurrent();
	$tabella = $keyArray['TABLENAME'];
	$libreria = $_REQUEST['LIBRERIA'];
	
?>