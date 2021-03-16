<?php

	require_once 'spoollist_model_common.php';
	
	$steps = $history->getSteps();
//	echo "STEPS:<pre>"; print_r($steps); echo "</pre>";

	if($actionContext->getForm()=="DEFAULT") {
		if(in_array("OUTQLIST_PARAMS_DEFAULT", $steps))
			$keyArray = getListKeyArray("OUTQLIST_PARAMS_LIST");
		else
			$keyArray = getListKeyArray("OUTQLIST");
//		echo "KEYS:<pre>"; print_r($keyArray); echo "</pre>";
		$actionContext->setLabel("Spool file OUTQ:".$keyArray['OUTQNAME']);
	
		$history->add($actionContext, "SPOOLLIST");
		$desc = array(
	        "username" => "*ALL",
		    "outq" => str_pad(str_pad($keyArray['OUTQNAME'], 10, " ").$keyArray['OUTQLIB'], 10, " "),
	        "userdata" => "*ALL"
	    );
	}

?>