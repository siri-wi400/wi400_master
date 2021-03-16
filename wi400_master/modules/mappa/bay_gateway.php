<?php
// Controllo il gateway che è stato richiesto
if ($actionContext->getGateway() == "DIFF_INVENT"){
	
		$key = getListKeyArray("DIFF_INVENT_DETT");	
		$posto = $key['ZONA']."-".$key['CORRIDOIO']."-".$key['BAY']."-".$key['POSTO'];
		$_REQUEST['POSITION']= $posto;
}