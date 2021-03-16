<?php

	$array_campi = array(
		"SELECT" => "SELECT",
		"FROM" => "FROM",
		"WHERE" => "WHERE",
		"GROUP_BY" => "GROUP BY",
		"ORDER_BY" => "ORDER BY",
	);
	
//	echo "WI400 GROUPS:<pre>"; print_r($wi400_groups); echo "</pre>";
//	echo "USER GROUPS:<pre>"; print_r($_SESSION ["WI400_GROUPS"]); echo "</pre>";

	/***
	 * in wi400.conf.php $settings['wi400_groups']
	 * 
	 * Gruppi di appartenza degli utenti, impostabili nella gestione del profilo
	 * 
	 * QUERY_ADMIN: l'utente può utilizzare tutte le potenzialità del query tool
	 * QUERY_USER: l'utente può scrivere query indirizzate (select, from, where, ...), ma può solo eseguire query libere
	 * QUERY_FILTRO: l'utente può utilizzare solo le query memorizzate tra i filtri salvati, senza poterle modificare
	 * QUERY_MARKER: come QUERY_FILTRO, ma l'utente non può visualizzare le query
	 * 
	 * $blocked: true, l'utente non può passare a scrivere query libere, ma può, in caso, eseguirle (campo inserimento bloccato)
	 * $readonly: true, l'utente può solo eseguire le query (campi inserimento bloccati)
	 * $hide_query: true, come $readonly, ma in più l'utente non potrà visualizzare il testo della query selezionata (campi nascosti)
	 * $loadonly: true, come $readonly, ma in più l'utente non ha il tasto per passare da un tipo di query all'altra (da indirizzata a libera e viceversa)
	 * 			(serve per poter nascondere il tasto 'Query indirizzata' nel caso in cui si sia in query libera (QUERY_FILTRO e QUERY_MARKER), 
	 * 			cosa non possibile con solo $readonly=true in quanto questo comprende anche il caso in cui uno sia abilitato
	 * 			a scrivere query indirizzate, ma non quelle libere (QUERY_USER))
	 */
	
	$readonly = false;
	$blocked = false;
	$hide_query = false;
	$loadonly = false;
	$detonly = false;
	$exe_button = false;
	
	$gateway = "";
	
	$query_admin_level = "";
			
	if(!empty($_SESSION ["WI400_GROUPS_BACKUP"])) {
		if(in_array("QUERY_ADMIN", $_SESSION ["WI400_GROUPS_BACKUP"]))
			$query_admin_level = "QUERY_ADMIN";
		
		if(in_array("QUERY_USER", $_SESSION ["WI400_GROUPS_BACKUP"]))
			$query_admin_level = "QUERY_USER";
		
		if(in_array("QUERY_FILTRO", $_SESSION ["WI400_GROUPS_BACKUP"]))
			$query_admin_level = "QUERY_FILTRO";
		
		if(in_array("QUERY_MARKER", $_SESSION ["WI400_GROUPS_BACKUP"]))
			$query_admin_level = "QUERY_MARKER";
	}
	
//	echo "GATEWAY: ".$actionContext->getGateway()."<br>";

	$azione = $actionContext->getAction();

	$steps = $history->getSteps();
//	echo "STEPS:<pre>"; print_r($steps); echo "</pre>";

	if(in_array("QUERY_TOOL_DB_PIN_DEFAULT", $steps)) {
//		$query_admin_level = "QUERY_MARKER";
	}
		
//	echo "ADMIN LEVEL: $query_admin_level<br>";
		
	if($query_admin_level!="QUERY_ADMIN") {
		$blocked = true;
	}
		
	if($query_admin_level=="QUERY_FILTRO") {
		$readonly = true;
	}
	else if($query_admin_level=="QUERY_MARKER") {
		$hide_query = true;
	}
	
	if($hide_query===true) {
		$readonly = true;
	}
	
	if($readonly===true) {
		$loadonly = true;
	}
	
	if($actionContext->getGateway()=="QUERY_MANAGER_DB") {
		$readonly = true;
		$detonly = true;
	}
	else if($actionContext->getGateway()=="QUERY_TOOL_DB_MULTI") {
		$readonly = true;
		$detonly = true;
		$exe_button = true;
	}		
	else if($actionContext->getGateway()=="QUERY_TOOL_DB_MARKERS") {
		$blocked = true;
		$readonly = true;
		$detonly = true;
		$exe_button = true;
		$hide_query = true;
		
		$query_admin_level = "QUERY_MARKER";
	}
		
//	echo "ADMIN LEVEL: $query_admin_level<br>";
		
//	echo "BLOCKED: $blocked<br>";
//	echo "READONLY: $readonly<br>";
//	echo "DET ONLY: $detonly<br>";
//	echo "EXE BUTTON: $exe_button<br>";
//	echo "HIDE QUERY: $hide_query<br>";
//	echo "LOAD ONLY: $loadonly<br>";
	
	if($query_admin_level=="") {
		// file di log
		$file_error_path = get_log_file_path("LOG_ERROR");
		
		if(!file_exists($file_error_path)) {
			wi400_mkdir($file_error_path, 777, true);
		}
		
		$file_error_name = get_log_file_name("LOG_ERROR");
		
		$file_log = $file_error_path.$file_error_name;
		
		$log_msg = "[".date("d-M-Y H:i:s")." ".date_default_timezone_get()."] ";
		
		$log_msg .= "Azione ".$actionContext->getAction()." inesistente o non applicabile all'utente (DB).\r\n";
		
		// fopen() deve essere impostato ad "a" per scrivere sul file senza però riscrivere la stessa riga
		$log_handle = fopen($file_log, "a");
		fwrite($log_handle, $log_msg);
		fclose($log_handle);
		
		$messageContext->addMessage ( "ERROR", $actionContext->getAction() . " inesistente o non applicabile all'utente." );
		$nextUrl = $appBase . "index.php";
		header ( "Location:" . $nextUrl );
		exit ();
	}
	
	// MARKERS
/*	
	require p13n("/modules/query/query_tool_common_markers.php");
	require_once p13n("/modules/query/query_tool_common_markers_functions.php");
	require_once p13n("/modules/query/query_tool_common_markers_fields.php");
*/
	require "query_tool_common_markers.php";
	require_once "query_tool_common_markers_functions.php";	
//	require_once p13n("/modules/query/query_tool_common_markers_functions_pers.php");
	require p13nPackage("query_tool_common_markers_functions_pers", "querymarker");