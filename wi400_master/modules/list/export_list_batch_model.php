<?php

	// Recupero oggetti

	$object = $batchContext->OBJECT;
//	echo "OBJECT: object\r\n";
	$object = base64_decode($object);
//	echo "OBJECT: $object\r\n";
	$object = unserialize($object);	
//	echo "OBJECT: $object\r\n";
//	$object = unserialize($object);
//	echo "OBJECT: "; showArray($object);
	
	$subfile = False;
	$subfile_id = "";
	$lista ="";
	
	// Ciclo sui file passati come parametri e li copio nella sessione attuale del batch
	foreach ($object as $key => $value) {
		// Recupero i valori
		$id = $value[0];
		$type = $value[1];
		$file = $value[2];
		
		// Creo il file di sessione
		$fileSessione = wi400Session::getFileName($type, $id);
		copy($file, $fileSessione);
		
		// Se e' un subfile devo resettare il nome della tabella
		if ($type == wi400Session::$_TYPE_SUBFILE) {
			$wi400Subfile = wi400Session::load(wi400Session::$_TYPE_SUBFILE, $id);
			$wi400Subfile->setCustomTableName(strtoupper($wi400Subfile->getIdTable()."_".session_id()));
			$wi400Subfile->setInizialized(false);
			$wi400Subfile->setFinalized(false);
			wi400Session::save(wi400Session::$_TYPE_SUBFILE,  $id, $wi400Subfile);
		}
		
		if ($type == wi400Session::$_TYPE_SUBFILE) {
			$subfile = True;
			$subfile_id = $id;
		}
		
		if ($type == wi400Session::$_TYPE_LIST) {
			$lista = $id;
		}	
	}
	
	// Se è presente un subfile devo lanciare la paginazione per estrarlo dato che non è attivo per la sessione
	if ($subfile == True) {
		//open connection NO PERCHé GENERA UNA NUOVA SESSIONE
/*
		$ch = curl_init();
		$url = "http://".$_SERVER['SERVER_ADDR'].":".$_SERVER['SERVER_PORT'].$appBase."index.php?IDLIST=$lista&PAGINATION=REGENERATE";
		// set the url, number of POST vars, POST data
		curl_setopt($ch, CURLOPT_URL, $url);
		$curl_result = curl_exec($ch);
*/
		$wi400Subfile = wi400Session::load(wi400Session::$_TYPE_SUBFILE, $subfile_id);
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $lista);
		wi400List::disposeSubfile($wi400List, $wi400Subfile, "REGENERATE");
	}
	else if($lista!="") {
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $lista);
	}
	
	$actionContext->setForm("EXPORT");
	
//	echo "PARAMETRI ESPORTAZIONE LISTA BATCH\r\n";
		
	$_REQUEST['FORMAT'] = $batchContext->FORMAT;
	$_REQUEST['TARGET'] = $batchContext->TARGET;
	$_REQUEST['ORIENTATION'] = $batchContext->ORIENTATION;
	echo "FORMAT: ".$batchContext->FORMAT." - TARGET: ".$batchContext->TARGET." - ORIENTATION: ".$batchContext->ORIENTATION."\r\n";
		
	// Controllo se è stata richiesta l'esportazione dell'indicazione dei filtri della lista utilizzati
	if(isset($batchContext->FILTERS))
		$_REQUEST['FILTERS'] = $batchContext->FILTERS;
	
	$_REQUEST['EXP_LIST'] = $batchContext->EXP_LIST;
	echo "IDLIST: ".$batchContext->EXP_LIST."\r\n";
		
	// Recupero i dettagli da stampare
	if(isset($batchContext->ID_DETAILS))
		$_REQUEST['ID_DETAILS'] = $batchContext->ID_DETAILS;
		
	if(isset($batchContext->ZIP))
		$_REQUEST['ZIP'] = $batchContext->ZIP;
	echo "ZIP: ".$batchContext->ZIP."\r\n";
		
	if(isset($batchContext->NOTIFICA))
		$_REQUEST['NOTIFICA'] = $batchContext->NOTIFICA;
	echo "NOTIFICA: ".$batchContext->NOTIFICA."\r\n";
	
	echo "EXPORT_BATCH INIZIO\r\n";
	
	require_once 'export_list_model.php';
	
	echo "EXPORT_BATCH FINE\r\n";
	
	if($csv_exp===false) {
		// Copio i file dalla export
		$path_parts = pathinfo($file);
		$new_path = $path_parts['dirname']."/".$filename;
//		$new_path = $settings['data_path']."batch/ID/".$batchContext->id."/".$filename;
		echo "FILE PATH: $filepath\r\n";
		echo "BATCH PATH: $new_path\r\n";
		
		rename($filepath, $new_path);
		
		if(in_array($batchContext->NOTIFICA, array("NOTIFICA", "ALLEGATO"))) {
			echo "INVIO DI UN'E-MAIL DI NOTIFICA\r\n";
	
//			$from = $siri_server_settings['smtp_user'];
	
			$username = $batchContext->USERNAME;
			
			$user_loc = $batchContext->USER_LOCALE;
			$area_fun = $batchContext->AREA_FUN;
				
			$SMTP = array();
			$SMTP['user'] = $settings['smtp_user'];
			$SMTP['pass'] = $settings['smtp_pass'];
			$SMTP['mail_host'] = $settings['smtp_host'];
//			$SMTP['from_name'] = "SISTEMIINFORMATIVI@AUTOGRILL.NET";
			$SMTP['from_name'] = $settings['smtp_from'];
//			$SMTP['from_name'] = "rzz@sipe.it";
			$SMTP['SMTPauth'] = $settings['smtp_auth'];
				
			$to_array = array();
//			$userMail = getUserMail($username);
			$userMail = getUserMail($username, $user_loc, $area_fun);
			echo "Destinatario $userMail \r\n";
			$to_array[] = trim($userMail);
	
//			echo "EMAIL:".$row['EMAIL'];
	
			$subject = "Esportazione lista";
				
			$body = "L'esportazione è stata eseguita con successo.";
				
			$files = array();
			if($batchContext->NOTIFICA=="ALLEGATO") {
				echo "INVIO DEL FILE COME ALLEGATO NELL'E-MAIL\r\n";
				$body .= "\r\nIl file esportato è stato allegato a quest'e-mail";
					
				if($zip===true) {
					$body .= " in formato compresso (zip).";
				}
				
				$files[] = $new_path;
			}
	
			// @todo Perchè non abbiamo usato la classe???
			$sent = wi400invioEmail::invioEmail('',$to_array,'',$subject,$body,$files,$SMTP);
				
			if($sent===false) {
//				$messageContext->addMessage("ERROR", "Errore durante l'invio dell'email");
				echo "Errore durante l'invio dell'email\r\n";
			}
			else {
//				$messageContext->addMessage("SUCCESS", "Email inviata con successo");
				echo "Email inviata con successo\r\n";
			}
		}
		else {
			echo "NON NOTIFICARE\r\n";
		}
	}