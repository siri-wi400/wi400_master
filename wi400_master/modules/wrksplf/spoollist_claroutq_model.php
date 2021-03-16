<?php

//	echo "CLAROUT SESSION: ".session_id()."<br>";
//	echo "CLAROUT IP: ".$_SESSION['IP']." - MY IP: ".$_SERVER['MY_IP']."<br>";

	require_once 'spoollist_model_common.php';	

	if($actionContext->getForm()=="DEFAULT") {
		$keyArray = getListKeyArray("OUTQLIST");
		$actionContext->setLabel("Spool file CLAROUTQ");
	
		$history->add($actionContext, "SPOOLLIST");
		$desc = array(
	        "username" => "*ALL",
		    "outq" => "CLARFIL   CLAROUTQ",
	        "userdata" => "*ALL"
	    );
	}
	
?>