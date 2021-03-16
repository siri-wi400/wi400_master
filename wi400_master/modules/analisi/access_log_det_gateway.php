<?php

	if($actionContext->getGateway()=="ACCESS_LOG") {
		$dati = getListKeyArray("ACCESS_LOG_LIST");
		
		$idDetail = $actionContext->getAction()."_PAR";
		
		wi400Detail::cleanSession($idDetail);
		
		$_REQUEST['ZEUTE'] = $dati['ZSUTE'];
		$_REQUEST['ZEIP'] = $dati['ZSIP'];
		$_REQUEST['ZESES'] = $dati['ZFRE'];
	}