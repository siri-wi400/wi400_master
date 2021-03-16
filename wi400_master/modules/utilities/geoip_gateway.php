<?php

	if($actionContext->getGateway()=="ACCESS_LOG") {
		$keyArray = array();
		$keyArray = getListKeyArray("ACCESS_LOG_LIST");
		
		$ip = $keyArray['ZSIP'];
	}
	
	if($actionContext->getGateway()=="LAVORI_ATTIVI") {
		$keyArray = array();
		$keyArray = getListKeyArray("LAVORI_ATTIVI_LIST");
		
		$ip = $keyArray['IP'];
	}

?>