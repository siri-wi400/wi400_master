<?php

	$array_campi = array(
		"SELECT",
		"FROM",
		"WHERE",
		"GROUP_BY",
		"ORDER_BY"
	);
	
//	echo "WI400 GROUPS:<pre>"; print_r($wi400_groups); echo "</pre>";
//	echo "USER GROUPS:<pre>"; print_r($_SESSION ["WI400_GROUPS"]); echo "</pre>";
	
	$readonly = false;
	$blocked = false;
	$load_only = false;
	if(empty($_SESSION ["WI400_GROUPS_BACKUP"])) {
		// file di log
		$file_error_path = get_log_file_path("LOG_ERROR");
		
		if(!file_exists($file_error_path)) {
			wi400_mkdir($file_error_path, 777, true);
		}
		
		$file_error_name = get_log_file_name("LOG_ERROR");
		
		$file_log = $file_error_path.$file_error_name;
		
		$log_msg = "[".date("d-M-Y H:i:s")." ".date_default_timezone_get()."] ";
		
		$log_msg .= "Azione ".$actionContext->getAction()." inesistente o non applicabile all'utente.\r\n";
		
		// fopen() deve essere impostato ad "a" per scrivere sul file senza però riscrivere la stessa riga
		$log_handle = fopen($file_log, "a");
		fwrite($log_handle, $log_msg);
		fclose($log_handle);
		
		$messageContext->addMessage ( "ERROR", $actionContext->getAction() . " inesistente o non applicabile all'utente." );
		// Elimino eventuale wizard
		sessionUnregister("WIZARD");
		$nextUrl = $appBase . "index.php";
		header ( "Location:" . $nextUrl );
		exit ();
	}
	else if(!in_array("QUERY_ADMIN", $_SESSION ["WI400_GROUPS_BACKUP"])) {
		if(in_array("QUERY_USER", $_SESSION ["WI400_GROUPS_BACKUP"])) {
			$blocked = true;
		}
		else if(in_array("QUERY_FILTRO", $_SESSION ["WI400_GROUPS_BACKUP"])) {
			$readonly = true;
			$blocked = true;
			$load_only = true; 
		} 
	}