<?php
    require_once "anagrafiche_data.php";
      
    $keyArray = getListKeyArray("TABLELIST");
	$actionContext->setLabel("Tabella:".$keyArray['TABLENAME']);

	$history->add($actionContext, "RECORDLIST");
	$tabella = $keyArray['TABLENAME'];
	$libreria = $_REQUEST['LIBRERIA'];
	
?>