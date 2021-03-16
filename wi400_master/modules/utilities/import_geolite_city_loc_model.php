<?php

	require_once p13n("/modules/siri_import_xls/configurazione.php");
	
	require_once 'import_geolite_city_loc_common.php';
	
//	require_once $moduli_path."/siri_import_xls/import_xls_common.php";
	
	$azione = $actionContext->getAction();
	
	$idDetail = $azione."_SRC";
//	echo "ID DETAIL: $idDetail<br>";
	
	$off = 1;
	if(!in_array($actionContext->getForm(), array("IMPORT"))) {
		$off = 2;
		$history->addCurrent();
	}
	
	$steps = $history->getSteps();
//	echo "STEPS:<pre>"; print_r($steps); echo "</pre>";
	
	$array_steps = get_history_steps($off, $steps);
	
	$first_action = $array_steps['FIRST_ACTION'];
	$first_form = $array_steps['FIRST_FORM'];
	
	$last_action = $array_steps['LAST_ACTION'];
	$last_form = $array_steps['LAST_FORM'];
//	echo "LAST_ACTION: $last_action - LAST FORM: $last_form<br>";
	
	$check_clean_tab = get_switch_bool_value($idDetail, "CLEAN_TAB");	// Pulire tabella
//	echo "CLEAN TAB: "; var_dump($check_clean_tab); echo "<br>";

	$csv_as_txt = get_switch_bool_value($idDetail, "TEST_TXT");
//	echo "TEST_TXT: "; var_dump($csv_as_txt); echo "<br>";
	
	$tipo_imp = wi400Detail::getDetailValue($idDetail,'TIPO_IMP');
	if(is_null($tipo_imp)) {
		$tipo_imp = "GEOLITE";
	}
//	echo "TIPO IMP: $tipo_imp<br>";
	
	if($actionContext->getForm()=="DEFAULT") {
		$label = $actionContext->getLabel();
		$actionContext->setLabel("Parametri");
	}
	else if($actionContext->getForm()=="LIST") {
	
	}
	else if($actionContext->getForm()=="IMPORT") {
		// Aumentata la dimensione del limite della memoria
		ini_set("memory_limit","10000M");
		ini_set("post_max_size ","200M");
		ini_set("upload_max_filesize","200M");
		set_time_limit(0);
		
		wi400Session::delete(wi400Session::$_TYPE_LIST, $azione."_LIST");
		
		if($csv_as_txt===true) {
			$loaded_file = check_load_file("IMPORT_FILE", array("CSV", "TXT"));
		}
		else {
			if(isset($_REQUEST['FILE_GOOGLE_DRIVE']) && $_REQUEST['FILE_GOOGLE_DRIVE']) {
				$file = $_FILES['IMPORT_FILE'];
					
				$check = check_file_cond($file['tmp_name'], $file['name'], $file['size'], array("XLS", "XLSX", "CSV"));
					
				$loaded_file = false;
				if($check !== false) {
					$loaded_file = $file;
				}
			}
			else {
				$loaded_file = check_load_file("IMPORT_FILE", array("XLS", "XLSX", "CSV"));
			}
		}
		
		if($load_file_name!==false) {
			if($check_clean_tab==true) {
//				echo "<font color='red'>CLEAN TABLE:</font> $from<br>";
				// Pulisco l'intera tabella
				$stmtdelete = $db->prepare("DELETE", $from, null, null);
					
				$result = $db->execute($stmtdelete);
			}
			
			$load_file_name = $loaded_file['tmp_name'];
			
			$file_name = $loaded_file['name'];
//			echo "FILE NAME: $file_name<br>";
				
			$file_parts = pathinfo($file_name);
//			echo "FILE PARTS:<pre>"; print_r($file_parts); echo "</pre>";
			
			$extension = $file_parts['extension'];
		}
		
		if($loaded_file!==false && (isset($csv_as_txt) && $csv_as_txt===true)) {
			$handle = fopen($load_file_name, "r");
			
			if($handle!==false) {
/*				
				 while($riga = fgetcsv($handle)) {
			    	echo "RIGA:<pre>"; print_r($riga); echo "</pre>";	
				 }
*/				 
				$error = false;
				
				$count = 0;
				while($val = fgets($handle)) {
//				 	echo "VAL: $val<br>";

					$count++;
					if($count===1)
						continue;
				 	
				 	if($val=="")
				 		break;
				 	
				 	if($tipo_imp=="GEOLITE") {
				 		$val_array = get_val_array($val, $array_campi, $csv_as_txt);
				 	}
				 	else if($tipo_imp=="COMUNI") {
				 		$campi_array = explode(";", $val);
				 		
				 		$cod_reg = trim($campi_array[0]);
						$cod_met = trim($campi_array[1]);
						$cod_prov = trim($campi_array[2]);
						$prog_comu = trim($campi_array[3]);
						$cod_comu = trim($campi_array[4]);
						$city = trim($campi_array[5]);
						$des_reg = trim($campi_array[9]);
						$des_met = trim($campi_array[10]);
						$des_prov = trim($campi_array[11]);
						$cod_auto = trim($campi_array[13]);
						
						$val_array = get_val_array_it($cod_reg, $cod_met, $cod_prov, $prog_comu, $cod_comu, $city, $des_reg, $des_met, $des_prov, $cod_auto, $campiFile);
				 	}
//				 	echo "VAL ARRAY:<pre>"; print_r($val_array); echo "</pre>";
//continue;				 	
				 	if($val_array===false)
				 		continue;
				 	
				 	$error = insert_val_array($val_array, $campiFile, $from, $error, $ins_prepare);
				 }
				 
				 fclose($handle);
			}
			else {
				$messageContext->addMessage("ERROR","Non è stato possibile leggere il file");
			}
		}
		else if($loaded_file!==false) {
			require_once $routine_path."/classi/wi400ExportList.cls.php";
			
			checkIfZipLoaded();
			
			require_once $routine_path."/generali/xls_common.php";
			
			$importType = "Excel2007";
			$exportType = "Excel2007";
			$file_type = ".xlsx";
			$TypeImage = "xlsx";
				
			switch(strtoupper($extension)) {
				case "XLS":
					$importType = "Excel5";
					break;
				case "XLSX":
					break;
				case "CSV":
					$importType = "CSV";
					$exportType = "CSV";
					$file_type = ".csv";
					$TypeImage = "csv";
					break;
			}
				
			$temp = "export";
			
			// Lettura del file Excel
				
			// Create new PHPExcel object
			$objReader = PHPExcel_IOFactory::createReader($importType);
			
			if($importType=="CSV") {
				$objReader->setDelimiter(';');
				$objReader->setEnclosure('');
				$objReader->setLineEnding("\r\n");
				$objReader->setSheetIndex(0);
			}
			
			$objPHPExcel = $objReader->load($load_file_name);
//			echo "<font color='red'>LOAD FILE</font><br>";
			
			$sheet = $objPHPExcel->getActiveSheet();
			
			$row_header = 1;
			$start_row = 2;
			$col = 0;
//			echo "ROW HEADER: $row_header - START ROW: $start_row - COL: $col<br>";
/*			
			$titoli = trim($sheet->getCellByColumnAndRow($col, $row_header)->getCalculatedValue());
//			echo "TITOLI: $titoli<br>";
			
			$titoli_array = explode(",", $titoli);
//			echo "TITOLI ARRAY:<pre>"; print_r($titoli_array); echo "</pre>";
*/			
			$error = false;
			for($riga=$start_row; ; $riga++) {
//				echo "<font color='blue'>RIGA:</font> $riga<br>";
			
				set_time_limit(0);
				
				if($tipo_imp=="GEOLITE") {
					$val = trim($sheet->getCellByColumnAndRow($col, $riga)->getCalculatedValue());					
//					echo "VAL: $val<br>";
						
					if($val=="")
						break;
					
					$val_array = get_val_array($val, $array_campi);
				}
				else if($tipo_imp=="COMUNI") {
					$cod_reg = trim($sheet->getCellByColumnAndRow(0, $riga)->getCalculatedValue());
					$cod_met = trim($sheet->getCellByColumnAndRow(1, $riga)->getCalculatedValue());
					$cod_prov = trim($sheet->getCellByColumnAndRow(2, $riga)->getCalculatedValue());
					$prog_comu = trim($sheet->getCellByColumnAndRow(3, $riga)->getCalculatedValue());
					$cod_comu = trim($sheet->getCellByColumnAndRow(4, $riga)->getCalculatedValue());
					$city = trim($sheet->getCellByColumnAndRow(5, $riga)->getCalculatedValue());
					$des_reg = trim($sheet->getCellByColumnAndRow(9, $riga)->getCalculatedValue());
					$des_met = trim($sheet->getCellByColumnAndRow(10, $riga)->getCalculatedValue());
					$des_prov = trim($sheet->getCellByColumnAndRow(11, $riga)->getCalculatedValue());
					$cod_auto = trim($sheet->getCellByColumnAndRow(13, $riga)->getCalculatedValue());
					
					if($cod_comu=="")
						break;
					
					$val_array = get_val_array_it($cod_reg, $cod_met, $cod_prov, $prog_comu, $cod_comu, $city, $des_reg, $des_met, $des_prov, $cod_auto, $campiFile);
				}
//				echo "VAL ARRAY:<pre>"; print_r($val_array); echo "</pre>";
//continue;					
				if($val_array===false)
					continue;
				
				$error = insert_val_array($val_array, $campiFile, $from, $error, $ins_prepare);
			}	
		}
//die("HERE");			
		if($error===true) {
//			$messageContext->addMessage("ERROR","Errore durante l'inserimento dei dati");
		}
		else {
			$messageContext->addMessage("SUCCESS","Inserimento dei dati eseguito con successo");
		}
		
		$actionContext->onSuccess($azione, "LIST");
//		$actionContext->onError($azione, "DEFAULT", "", "", true);
		$actionContext->onError($azione, "LIST", "", "", true);
	}