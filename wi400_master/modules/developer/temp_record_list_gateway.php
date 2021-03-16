<?php
    require_once "anagrafiche_data.php";
      
    $keyArray = getListKeyArray("TEMP_TABLE");
	$actionContext->setLabel("Tabella:".$keyArray['TABLENAME']);

	$history->add($actionContext, "TEMP_RECORD_LIST");
	$tabella = $keyArray['TABLENAME'];
	$libreria = $_REQUEST['LIBRERIA'];
	
?>