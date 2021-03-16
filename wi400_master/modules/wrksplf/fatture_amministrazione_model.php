<?php
	$keyArray = getListKeyArray("OUTQLIST");
	$actionContext->setLabel("Fatture presenti su coda ".$keyArray['OUTQNAME']);

	$history->add($actionContext, "SPOOLLIST");
	$desc = array(
        "username" => "*ALL",
	    "outq" => 'QGPL/FATTURE',
        "userdata" => "*ALL"
    );
	
?>