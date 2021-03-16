<?php

	$azione = $actionContext->getAction();
	
	$tabella = $batchContext->TABELLA;
	$libreria = $batchContext->LIBRERIA;
	$exportFormat = $batchContext->TIPO_EXP;
	$to_path = $batchContext->TO_PATH;
	$des_titoli = $batchContext->DES_TITOLI;
	$notifica = $batchContext->NOTIFICA;
	$zip = $batchContext->ZIP;
	
	$popola = $batchContext->POPOLA;
	echo "POPOLA: $popola\r\n";
	
	$subject = $batchContext->SUBJECT;
	$body = $batchContext->BODY;
//	echo "SUBJECT: $subject\r\n";
//	echo "BODY: $body\r\n";

	$file_name = $batchContext->FILE_NAME;
//	echo "FILE NAME: $file_name\r\n";
	
	$username = $batchContext->USERNAME;
	
	$user_loc = $batchContext->USER_LOCALE;
	$area_fun = $batchContext->AREA_FUN;
	
	$batch_id = $batchContext->id;
	
	$file = $batchContext->file;
	$batch_path = dirname($file);
	
	$from = $tabella;
	if($libreria!="")
		$from = $libreria."/".$tabella;
	
	// ESPORTAZIONE
	require_once $routine_path."/classi/wi400ExportList.cls.php";
	require_once $routine_path."/classi/wi400invioEmail.cls.php";
	
	require_once $moduli_path.'/list/export_list_xls.php';
	require_once $moduli_path.'/list/export_list_csv.php';
	
	require_once p13n("/modules/analisi/export_table_functions.php");
	
	// Aumentata la dimensione del limite della memoria
	ini_set("memory_limit","1000M");
	set_time_limit(0);
	
	$csv_exp = false;
	
	if($popola!="") {
		$funzione = "popolazione_tabella_".$popola;
		
		$funzione();
	}
	
	$idList = $azione."_LIST";
	
	$wi400List = new wi400List($azione."_LIST", true);
	$wi400List->setFrom($from);
	
	$campi = $db->columns($tabella, "", False, "", $libreria);
//	echo "CAMPI:<pre>"; print_r($campi); echo "</pre>";
	
	if(!empty($campi)) {
		foreach($campi as $key => $vals) {
//			echo "VALS:<pre>"; print_r($vals); echo "</pre>";
				
//			$len = $vals['LENGTH_PRECISION'];
				
			$tipo = "STRING";
			$align = "left";
			switch($vals['DATA_TYPE_STRING']) {
				case "INTEGER":
					$tipo = "INTEGER";
					$align = "right";
					break;
				case "DECIMAL":
				case "NUMERIC":
				case "FLOAT":
					$dec = $vals['NUM_SCALE'];
					if($dec==0)
						$tipo = "INTEGER";
					else
						$tipo = "DOUBLE_".$dec;
					$align = "right";
					break;
				case "TIMESTAMP":
					$tipo = "COMPLETE_TIMESTAMP";
					break;
				case "DATE";
				$tipo = "SHORT_TIMESTAMP";
				break;
			}
			
			$label = $key;
			if($des_titoli=="S") {
//				$label = $vals['HEADING'];
				$label = $vals['REMARKS'];
				
//				$label = wordwrap($label, 10, "\r\n");
			}
	
			$wi400List->addCol(new wi400Column($key, $label, $tipo, $align));
		}
	}
	
	saveList($idList, $wi400List);
	
	$export = new wi400ExportList("ALL", $wi400List);
	
	// Recupero la query della lista (compresi i filtri utilizzati)
	$export->prepare();
	
	// Lancio l'esportazione a seconda del formato (impostato nel codice html che si trova nella _view.php)
	// e ottengo il filename di ritorno in modo che sia comune con la _view.php
	switch($exportFormat) {
		case "excel5":
		case "excel2007":
			exportXLS($export, $idList, $exportFormat, "ALL", "");
			break;
		case "csv":
			exportCSV($export, $idList, "ALL");
			break;
	}
	
	// Recupero dei parametri del file generato necessari per il download
	$filename = $export->getFilename();
	$filepath = $export->get_filepath();
//	$TypeImage = $export->getTypeImage();
//	$temp = $export->getTemp();
	echo "FILEPATH - ORIGINALE: $filepath\r\n";
	
	$dir_path = dirname($filepath);
	
	$file_parts = pathinfo($filename);
	$file_type = ".".$file_parts['extension'];
	
	if(isset($file_name) && !empty($file_name)) {
		$filename = $file_name."_".date("YmdHis").$file_type;
		$rename = $dir_path."/".$filename;
	}
	else {
		$filename = "Esportazione_tabella_".$tabella."_".date("YmdHis").$file_type;
		$rename = $dir_path."/".$filename;
	}
	echo "FILEPATH - RIDENOMINATO: $rename\r\n";
		
	$batch_file = $batch_path."/".$filename;
	copy($filepath, $batch_file);
	chmod($batch_file, 777);
	echo "FILEPATH - BATCH: $batch_file\r\n";
	
	if($to_path!="") {
		if(!file_exists($to_path))
			wi400_mkdir($to_path, 777);
			
		$rename = $to_path."/".$filename;
//		echo "RENAME: $rename<br>";
	
		$dir_path = $to_path;
		
		echo "FILEPATH - SPOSTATO: $rename\r\n";
	}
//	echo "RENAME: $rename<br>";
	
	if($rename!="") {
		copy($filepath, $rename);
		chmod($rename, 777);
	
		unlink($filepath);
	
		$filepath = $rename;
	}
/*	
	// NOTIFICA
	if($notifica=="NOTIFICA") {
		echo "INVIO DI UN'E-MAIL DI NOTIFICA\r\n";
	
//		$from = $siri_server_settings['smtp_user'];
	
		$SMTP = array();
		$SMTP['user'] = $settings['smtp_user'];
		$SMTP['pass'] = $settings['smtp_pass'];
		$SMTP['mail_host'] = $settings['smtp_host'];
//		$SMTP['from_name'] = "SISTEMIINFORMATIVI@AUTOGRILL.NET";
		$SMTP['from_name'] = $settings['smtp_from'];
		$SMTP['SMTPauth'] = $settings['smtp_auth'];
	
		$to_array = array();
		$userMail = getUserMail($username);
//		$to_array[] = trim($userMail);
		$to_array[] = "valeria.porrazzo@siri-informatica.it";
	
//		echo "EMAIL:".$row['EMAIL'];
	
		$subject = "Esportazione tabella AS400";
	
		$body = "L'esportazione della tabella AS400 $from è stata eseguita con successo.";
	
		// @todo Perchè non abbiamo usato la classe???
		$sent = wi400invioEmail::invioEmail('',$to_array,'',$subject,$body,"",$SMTP);
	
		if($sent===false)
//			$messageContext->addMessage("ERROR", "Errore durante l'invio dell'email");
			echo "Errore durante l'invio dell'email\r\n";
		else
//			$messageContext->addMessage("SUCCESS", "Email inviata con successo");
			echo "Email inviata con successo\r\n";
	}
	else {
		echo "NON NOTIFICARE\r\n";
	}
*/
	if($zip=="ZIP") {
		echo "ZIP DEL FILE\r\n";
	
		$zip_parts = explode(".", $filename);
		$zip_path = $dir_path."/".$zip_parts[0].'.zip';
	
		wi400invioEmail::compress(array($filepath),$zip_path);
	}
	
	if(in_array($notifica,array("NOTIFICA","ALLEGATO"))) {
		echo "INVIO DI UN'E-MAIL DI NOTIFICA\r\n";
		
		$SMTP = array();
		$SMTP['user'] = $settings['smtp_user'];
		$SMTP['pass'] = $settings['smtp_pass'];
		$SMTP['mail_host'] = $settings['smtp_host'];
		$SMTP['from_name'] = $settings['smtp_from'];
		$SMTP['SMTPauth'] = $settings['smtp_auth'];
	
//		$from = $siri_server_settings['smtp_user'];
	
		$to_array = array();
//		$userMail = getUserMail($username);
		$userMail = getUserMail($username, $user_loc, $area_fun);
		$to_array[] = trim($userMail);
	
//		echo "EMAIL:".$row['EMAIL'];

		if(!isset($subject) || empty($subject))
			$subject = "Esportazione tabella AS400";
		
		if(!isset($body) || empty($body))
			$body = "L'esportazione della tabella AS400 $from è stata eseguita con successo.";
	
		$files = array();
		if($notifica=="ALLEGATO") {
			echo "INVIO DEL FILE COME ALLEGATO NELL'E-MAIL\r\n";
	
			if($zip=="ZIP") {
				$allegato = $zip_path;
			}
			else {
				$allegato = $filepath;
			}
	
//			$file_size = filesize($allegato);
			$file_size = File_Size($allegato, "MB", false);
			echo "FILE SIZE: $file_size\r\n";
	
			if($file_size>=5) {
//				$body .= "\r\nNon è stato possibile allegare il file esportato in quanto troppo grande";
//				$body .= "\r\nPotete recuperare il file dal sistema attraverso la lista dei lavori batch";
//				$body .= "\r\nID lavoro: $batch_id";
	
				$body .= "\r\n"._t('NO_ALLEGATO_TROPPO_GRANDE');
				$body .= "\r\n"._t('RECUPERO_FILE');
				$body .= "\r\n"._t('ID_LAVORO').$batch_id;
			}
			else {
//				$body .= "\r\nIl file esportato è stato allegato a quest'e-mail";
				$body .= "\r\n"._t('FILE_ESP_ALLEGATO');
	
				if($zip=="ZIP") {
//					$body .= " in formato compresso (zip).";
					$body .= _t('IN_FORMATO_ZIP');
				}
	
//				$body .= "\r\nID lavoro: $batch_id";
				$body .= "\r\n"._t('ID_LAVORO').$batch_id;
	
				$files[] = $allegato;
			}
		}
		
//		$subject = prepare_string($subject);
//		$body = prepare_string($body, false, true);
		$body = utf8_encode($body);
		
		// @todo Perchè non abbiamo usato la classe???
		$sent = wi400invioEmail::invioEmail('',$to_array,'',$subject,$body,$files,$SMTP);
	
		if($sent===false)
//			$messageContext->addMessage("ERROR", "Errore durante l'invio dell'email");
			echo "Errore durante l'invio dell'email\r\n";
		else
//			$messageContext->addMessage("SUCCESS", "Email inviata con successo");
			echo "Email inviata con successo\r\n";
	}
	else {
		echo "NON NOTIFICARE\r\n";
	}
	
	if($zip=="ZIP") {
		echo "DELETE FILE\r\n";
		unlink($filepath);
	}