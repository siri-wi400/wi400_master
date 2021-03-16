<?php

	require_once 'export_table_common.php';
	
//	echo "REQUEST:<pre>"; print_r($_REQUEST); echo "</pre>";
	
	$azione = $actionContext->getAction();
	
	if(in_array($actionContext->getForm(), array("DEFAULT", "LIST")))
		$history->addCurrent();
	
	$tabella = wi400Detail::getDetailValue($azione."_SRC", 'TABELLA');
	$libreria = wi400Detail::getDetailValue($azione."_SRC", 'LIBRERIA');
	$exportFormat = wi400Detail::getDetailValue($azione."_SRC", 'TIPO_EXP');
	$to_path = wi400Detail::getDetailValue($azione."_SRC", 'TO_PATH');
	
	$check_des_titoli = get_switch_bool_value($azione."_SRC", "DES_TITOLI");
	$des_titoli = get_switch_value($azione."_SRC", "DES_TITOLI");
//	echo "CHECK DES TITOLI: $check_des_titoli - DES TITOLI: $des_titoli<br>";
/*	
	// Notifica
	$check_notifica = get_switch_bool_value($azione."_SRC", "NOTIFICA");
	$notifica = "";
	if($check_notifica!=false)
		$notifica = "NOTIFICA";	
//	echo "CHECK NOTIFICA: $check_notifica - NOTIFICA: $notifica<br>";
*/
//	$notifica = $_REQUEST['NOTIFICA'];
	$notifica = wi400Detail::getDetailValue($azione."_SRC", 'NOTIFICA');
//	echo "NOTIFICA: $notifica<br>";
	
	// Zip
	$check_zip = get_switch_bool_value($azione."_SRC", "ZIP");
//	$zip = get_switch_value($azione."_SRC", "ZIP");
	$zip = "";
	if($check_zip!=false)
		$zip = "ZIP";
//	echo "CHECK ZIP: $check_zip - ZIP: $zip<br>";

	$subject = wi400Detail::getDetailValue($azione."_SRC", 'SUBJECT');
	$body = wi400Detail::getDetailValue($azione."_SRC", 'BODY');
	$file_name = wi400Detail::getDetailValue($azione."_SRC", 'FILE_NAME');
//	echo "FILE NAME: $file_name<br>";

	$popola = wi400Detail::getDetailValue($azione."_SRC", 'POPOLA');
//	echo "POPOLA: $popola<br>";

	$esporta = true;
	
	if($actionContext->getForm()=="DEFAULT") {
		
	}
	else if($actionContext->getForm()=="EXPORT") {
		if(in_array($exportFormat, array("excel5", "excel2007"))) {
			$from = $tabella;
			if($libreria!="")
				$from = $libreria."/".$tabella;
			
			$sql = "select * from $from";
			$res = $db->query($sql, false, 0);
			$tot_rows = $db->num_rows($res);
//			echo "TOT RIGHE: $tot_rows<br>";

			if($tot_rows>0 && $tot_rows>$settings['max_export_rows_xls']) {
				$esporta = false;
				$actionContext->setForm("DEFAULT");
			}
		}
		
//		echo "CSV EXP<br>";

		if($esporta===true) {
			require_once $routine_path."/classi/wi400Batch.cls.php";
/*		
			$zip = "";
			if(isset($_REQUEST['ZIP']) && !empty($_REQUEST['ZIP']))
				$zip = $_REQUEST['ZIP'];
			
			$notifica = "";
			if(isset($_REQUEST['NOTIFICA']) && !empty($_REQUEST['NOTIFICA']))
				$notifica = $_REQUEST['NOTIFICA'];
*/
			$batch = new wi400Batch($_SESSION['user']);
			$batch->setAction("EXPORT_TABLE_BATCH");
			
			$batch->addParameter("TABELLA", $tabella);
			$batch->addParameter("LIBRERIA", $libreria);
			$batch->addParameter("TIPO_EXP", $exportFormat);
			$batch->addParameter("TO_PATH", $to_path);			
			$batch->addParameter("DES_TITOLI", $des_titoli);
			
			$batch->addParameter("POPOLA", $popola);
			
			$batch->addParameter("NOTIFICA", $notifica);
			$batch->addParameter("ZIP", $zip);
			$batch->addParameter("USERNAME", $_SESSION['user']);
			
			$batch->addParameter("USER_LOCALE", $_SESSION['locale']);
			
			$area_fun = "";
			if(isset($_SESSION["LOGIN_PROFILE"]['AREA']))
				$area_fun = $_SESSION["LOGIN_PROFILE"]['AREA'];
			$batch->addParameter("AREA_FUN", $area_fun);
			
//			if(isset($subject) && $subject!="")
				$batch->addParameter("SUBJECT", $subject);
//			if(isset($body) && $body!="")
				$batch->addParameter("BODY", $body);
//			if(isset($file_name) && $file_name!="")
				$batch->addParameter("FILE_NAME", $file_name);
			
			$batch->addParameter("name_job", "EXPORT_TABLE");
			$batch->addParameter("des_job", "Esportazione file Excel di una tabella AS400");
			
			$result_batch = $batch->call($connzend);
			
			$steps = $history->getSteps();
//			echo "STEPS:<pre>"; print_r($steps); echo "</pre>";
			
			$first_step = $steps[0];
//			echo "FIRST STEP: $first_step<br>";
				
			$first_action_obj = $history->getAction($first_step);
			if (isset($first_action_obj)) {
				$first_action = $first_action_obj->getAction();
				$first_form = $first_action_obj->getForm();
			}
//			echo "FIRST ACTION: $first_action - FIRST FORM: $first_form<br>";
			
//			$actionContext->gotoAction($azione, "DEFAULT", "", true);
			$actionContext->gotoAction($first_action, $first_form, "", true);
		}		
	}
	else if($actionContext->getForm()=="EXPORT_DIRETTA") {
		$actionContext->setLabel("Esportazione diretta");
		
		$from = $tabella;
		if($libreria!="")
			$from = $libreria."/".$tabella;
//		echo "FROM: $from<br>";
		
		// ESPORTAZIONE
		require_once $routine_path."/classi/wi400ExportList.cls.php";
		require_once $routine_path."/classi/wi400invioEmail.cls.php";
		
		require_once $moduli_path.'/list/export_list_xls.php';
		require_once $moduli_path.'/list/export_list_csv.php';
		
		// Aumentata la dimensione del limite della memoria
		ini_set("memory_limit","1000M");
		set_time_limit(0);
		
		$csv_exp = false;
		
		$idList = $azione."_LIST";
		
		$wi400List = new wi400List($azione."_LIST", true);
		$wi400List->setFrom($from);
		
		$campi = $db->columns($tabella, "", False, "", $libreria);
//		echo "CAMPI:<pre>"; print_r($campi); echo "</pre>";
/*
		if($des_titoli=="S") {
			require_once $routine_path."/generali/db_support.php";
			$dati = getCurrentOpenFile();
			$arrayCampi = getAllFieldUsed($dati);
		}
*/		
		if(!empty($campi)) {
			foreach($campi as $key => $vals) {
//				echo "VALS:<pre>"; print_r($vals); echo "</pre>";

//				$len = $vals['LENGTH_PRECISION'];
		
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
//					$label = $vals['HEADING'];
					$label = $vals['REMARKS'];
					
//					$label = getCustomFieldDesc($key, $arrayCampi, True, True);

//					$label = wordwrap($label, 25, "\r\n");
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
		$TypeImage = $export->getTypeImage();
		$temp = $export->getTemp();
		
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
//		echo "FILEPATH - RIDENOMINATO: $rename\r\n";
		
		if($to_path!="") {
			if(!file_exists($to_path))
				wi400_mkdir($to_path, 777);
				
			$rename = $to_path."/".$filename;
//			echo "RENAME: $rename<br>";
	
			$dir_path = $to_path;
			
//			echo "FILEPATH - SPOSTATO: $rename\r\n";
		}
//		echo "RENAME: $rename<br>";
		
		if($rename!="") {
			copy($filepath, $rename);
			chmod($rename, 777);
		
			unlink($filepath);
		
			$filepath = $rename;
		}
		
		if($zip=="ZIP") {
			$zip_parts = explode(".", $filename);
			$zip_path = $dir_path."/".$zip_parts[0].'.zip';
		
			wi400invioEmail::compress(array($filepath),$zip_path);
			
			unlink($filepath);
				
			$filepath = $zip_path;
			$TypeImage = "zip.png";
		}
/*		
		// NOTIFICA
		if($notifica=="NOTIFICA") {
//			echo "INVIO DI UN'E-MAIL DI NOTIFICA\r\n";
		
//			$from = $siri_server_settings['smtp_user'];
		
			$username = $_SESSION['user'];
		
			$SMTP = array();
			$SMTP['user'] = $settings['smtp_user'];
			$SMTP['pass'] = $settings['smtp_pass'];
			$SMTP['mail_host'] = $settings['smtp_host'];
//			$SMTP['from_name'] = "SISTEMIINFORMATIVI@AUTOGRILL.NET";
			$SMTP['from_name'] = $settings['smtp_from'];
			$SMTP['SMTPauth'] = $settings['smtp_auth'];
		
			$to_array = array();
			$userMail = getUserMail($username);
//			$to_array[] = trim($userMail);
			$to_array[] = "valeria.porrazzo@siri-informatica.it";
		
//			echo "EMAIL:".$row['EMAIL'];
		
			$subject = "Esportazione tabella AS400";
		
			$body = "L'esportazione della tabella AS400 $from è stata eseguita con successo.";
		
			// @todo Perchè non abbiamo usato la classe???
			$sent = wi400invioEmail::invioEmail('',$to_array,'',$subject,$body,"",$SMTP);
		
			if($sent===false)
				$messageContext->addMessage("ERROR", "Errore durante l'invio dell'email");
//				echo "Errore durante l'invio dell'email\r\n";
			else
				$messageContext->addMessage("SUCCESS", "Email inviata con successo");
//				echo "Email inviata con successo\r\n";
		}
		else {
			echo "NON NOTIFICARE\r\n";
		}
*/		
		$messageContext->addMessage("SUCCESS", "Esportazione tabella eseguita con successo");
		
//		$actionContext->gotoAction($azione, "DEFAULT", "", true);
	}