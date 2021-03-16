<?php

	if (isset($_REQUEST["LCK_DLT"])){
		
		// Rimozione di tutti i lock legati alla sessione corrente
		endLock("","",session_id());

		// Rimozione della history
		$history = new wi400History();
		//$_SESSION["WI400_HISTORY"] = $history;
		wi400Session::save(wi400Session::$_TYPE_HISTORY, "BREAD_CRUMBS", $history);	
		// Cancello wizard
		sessionUnregister("WIZARD");
		
		// Cancello tutti i dati delle tabelle temporanee create
		$db->deleteTable(session_id());

	}
	
	
	// Indica che l'azione è stata eseguita attraverso la navigazione dell'history
	$isFromHistory = false;
	if (isset($_REQUEST["HST_NAV"])){
		$isFromHistory = true;
	}
?>