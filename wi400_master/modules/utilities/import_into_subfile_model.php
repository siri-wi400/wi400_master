<?php

	$azione = $actionContext->getAction();
	
	$foglio = wi400Detail::getDetailValue($azione."_FILTRI_LIST_DET", 'FOGLIO');
//	echo "FOGLIO: $foglio<br>";
	
	$row_title = wi400Detail::getDetailValue($azione."_FILTRI_LIST_DET", 'RIGA');
	if(is_null($row_title))
		$row_title = 1;
//	echo "RIGA TITOLI: $row_title<br>";
	
	$check_overwrite = get_switch_bool_value($azione."_FILTRI_LIST_DET", "OVERWRITE");
	$overwrite_src = get_switch_value($azione."_FILTRI_LIST_DET", "OVERWRITE");
//	echo "CHECK OVERWRITE: $overwrite_src<br>";
	
	// Reperisco Subfile e Custom Subfile
	$mialista = "";
	if (isset($_GET['IDLIST'])) {
		$mialista = $_GET['IDLIST'];
		$_SESSION['IMPORT_SUBFILE_ACTION_LIST']=$mialista;
	}
	else {
		$mialista = $_SESSION['IMPORT_SUBFILE_ACTION_LIST'];
	}
	
	$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $mialista);
	$wi400Subfile = wi400Session::load(wi400Session::$_TYPE_SUBFILE, $wi400List->getSubfile());
	
//	echo "SUBFILE NAME: ".$wi400Subfile->getFullTableName()."<br>";
	
	$modulo = $wi400Subfile->getModulo();
	
	$percorso_classe ="";
	if ($modulo!='') {
		$percorso_classe = p13n('modules/'.$modulo.'/subfile/'.$wi400Subfile->getConfigFileName().".cls.php");
	}
	else {
		$percorso_classe = $routine_path.'/classi/subfile/'.$wi400Subfile->getConfigFileName().".cls.php";
	}
	
	// Patch per vecchi subfile all'interno del model
	if (file_exists($percorso_classe)) {
		require_once($percorso_classe);
		
		$subfileClassName = $wi400Subfile->getConfigFileName();
		
		$customSubfile = new $subfileClassName($wi400Subfile->getParameters());
		$customSubfile->setFullTableName($wi400Subfile->getFullTableName());
	}
	else {
		developer_debug("Subfile ".$wi400Subfile->getConfigFileName()." non trovato");
	}
	
	$cols_import = $customSubfile->import_get_column();
//	echo "COLS IMPORT:<pre>"; print_r($cols_import); echo "</pre>";
	
	$cols_values = array();
	foreach($cols_import as $key => $des) {
		if(is_numeric($key))
			$key = $des;
	
//		$val = wi400Detail::getDetailValue($azione."_FILTRI_LIST_DET", $key);
		$val = wi400Detail::getDetailValue($azione."_CAMPI_IMP_DET", $key);
	
		if(is_null($val))
			$val = $des;
	
		$cols_values[$key] = $val;
	}
//	echo "COLS VALUES:<pre>"; print_r($cols_values); echo "</pre>";

	if($actionContext->getForm()=="DEFAULT") {
		wi400Detail::cleanSession($azione."_FILTRI_LIST_DET");
		wi400Detail::cleanSession($azione."_CAMPI_IMP_DET");
		
		$actionContext->gotoAction($azione, "DEFAULT_2", "", true);
	}
	else if($actionContext->getForm()=="IMPORT") {
		// Aumentata la dimensione del limite della memoria
		set_time_limit(0);
		ini_set("memory_limit","1000M");
		ini_set("post_max_size ","60M");
		ini_set("upload_max_filesize","60M");
	    
	    // Codice di Importazione
	    if(isset($_FILES['IMPORT_FILE']) && is_uploaded_file($_FILES['IMPORT_FILE']['tmp_name'])) {
	    	$file_name = $_FILES['IMPORT_FILE']['name'];
	    	$file_parts = pathinfo($file_name);
	    	$imgExt = $file_parts['extension'];
	    	
	    	// Controllo che il file non superi i 1 MB
	    	if($_FILES['IMPORT_FILE']['size'] > 1200000) {
	    		$messageContext->addMessage("ERROR","Il file non deve superare 1 MB!");
	    	}
	    	
	    	if(!in_array($imgExt, array("xls", "xlsx", "XLS", "XLSX"))) {
	    		$messageContext->addMessage("ERROR","Il file deve essere in formato xls/xlsx.");
	    	}
	    	else if(!file_exists($_FILES['IMPORT_FILE']['tmp_name'])) {
	    		$messageContext->addMessage("ERROR","File non trovato.");
	    	}
	    	else {
	    		require_once $routine_path."/generali/xls_common.php";
	    		
	    		require_once $routine_path."/classi/wi400ExportList.cls.php";
	    		
	    		$load_file_name = $_FILES['IMPORT_FILE']['tmp_name'];
	    		$file_name = $_FILES['IMPORT_FILE']['name'];
	    		
	    		$file_parts = pathinfo($file_name);
//	    		echo "FILE PARTS:<pre>"; print_r($file_parts); echo "</pre>";

	    		$extension = $file_parts['extension'];
	    		
	    		switch(strtoupper($extension)) {
	    			case "XLS":
	    				$importType = "Excel5";
	    				break;
	    			case "XLSX":
	    				$importType = "Excel2007";
	    				break;
	    		}
	    		
	    		// Lettura del file Excel
	    			
	    		// Create new PHPExcel object
	    		$objReader = PHPExcel_IOFactory::createReader($importType);
	    			
	    		$objPHPExcel = $objReader->load($load_file_name);
	    			
//				echo "<font color='green'>LOAD FILE</font><br>";
	    		
	    		$execute = true;
	    		
	    		// Controllo dell'esistenza dei fogli
	    		$sheet = search_sheet_xls($objPHPExcel, $foglio);
	    			
	    		if($sheet==false) {
//					$messageContext->addMessage("ERROR","Foglio '$foglio' non trovato.");
	    			$msg = "Foglio '$foglio' non trovato.";
	    			show_message("ERROR", array($msg));
	    		}
	    		else {
	    			$num_errori = 0;
	    			$num_warning = 0;
	    			$num_info = 0;
	    				
//					echo "<font color='blue'>CONTROLLI DI TESTATA</font><br>";
	    		
//	    			$row_title = 1;
	    				
//					$max_cols = PHPExcel_Cell::columnIndexFromString($sheet->getHighestColumn());
//					echo "MAX COLS: $max_cols<br>";
	    				
	    			// Controllo esistenza colonne obbligatorie e recupero posizione
	    			if($execute===true) {
/*	    				
	    				$cols_import = $customSubfile->import_get_column();
	    				echo "COLS IMPORT:<pre>"; print_r($cols_import); echo "</pre>";
	    				
	    				$cols_must = array();
	    				foreach($cols_import as $key => $des) {
	    					if(is_numeric($key))
	    						$key = $des;
	    					
//	    					$val = wi400Detail::getDetailValue($azione."_FILTRI_LIST_DET", $key);
	    					$val = wi400Detail::getDetailValue($azione."_CAMPI_IMP_DET", $key);
	    					
	    					$cols_must[$val] = "";
	    				}
	    				echo "COLS MUST:<pre>"; print_r($cols_must); echo "</pre>";
*/
	    				$cols_must = array();
	    				foreach($cols_values as $key => $des) {
//	    					$val = wi400Detail::getDetailValue($azione."_FILTRI_LIST_DET", $key);
	    					$val = wi400Detail::getDetailValue($azione."_CAMPI_IMP_DET", $key);
	    				
	    					$cols_must[$val] = "";
	    				}
//	    				echo "COLS MUST:<pre>"; print_r($cols_must); echo "</pre>";
	    				
	    				$max_cols = 0;
	    				$cols_titles = array();
	    				
	    				if(isset($cols_must) && !empty($cols_must)) {
	    					$c = 0;
	    					for($j=0; ; $j++) {
	    						$val = trim($sheet->getCellByColumnAndRow($j, $row_title)->getCalculatedValue());
//								echo "VAL: $val<br>";
	    			
	    						if($val=="") {
	    							break;
	    						}
	    						
	    						$cols_titles[$val] = $j;
	    			
	    						if(isset($cols_must) && !empty($cols_must)) {
//	    							$val = strtoupper($val);
	    			
	    							if(array_key_exists($val, $cols_must)) {
	    								$cols_must[$val] = $j;
	    								$c++;
	    							}
	    						}
	    					}
	    					
	    					if($max_cols<($j-1))
	    						$max_cols = $j-1;
	    			
	    					if($c!=count($cols_must)) {
	    						foreach($cols_must as $key => $val) {
	    							if($val=="") {
	    								$messageContext->addMessage("ERROR","Foglio $foglio - Colonna '$key' non trovata.");
	    								$execute = false;
	    							}
	    						}
	    					}
	    				}
	    			}
//	    			echo "COLS TITLES:<pre>"; print_r($cols_titles); echo "</pre>";
//					echo "COLS MUST:<pre>"; print_r($cols_must); echo "</pre>";
	    			
	    			if($execute===true) {
	    				$import_array = array();
	    				
	    				$row_dati = $row_title+1;
	    				
	    				// ciclo sulle righe e le faccio verificare al subfile
	    				$row = array();
	    				
	    				for($i=$row_dati; ; $i++) {
	    					$row_empty = true;
	    					
	    					$row = array();
	    					
	    					for($j=0; $j<=$max_cols; $j++) {
	    						$val = trim($sheet->getCellByColumnAndRow($j, $i)->getCalculatedValue());
	    						
//	    						$row[$j] = $val;
	    						
	    						$col = array_search($j, $cols_titles);	    						
	    						$row[$col] = $val;
	    						
	    						if($val!="") {
	    							$row_empty = false;
	    						}
	    					}
//	    					echo "ROW:<pre>"; print_r($row); echo "</pre>";
	    					
	    					if($row_empty===true) {
	    						break;
	    					}
	    					else {
//	    						$check = $customSubfile->import_check_riga($row, $cols_titles);
	    						$check = $customSubfile->import_check_riga($row);
	    						
	    						if($check===false) {
	    							$messageContext->addMessage("ERRROR", "Errore rilevato durante il controllo delle righe");
	    							$execute = false;
	    							break;
	    						}
	    					}
	    				}
	    					
	    				$max_rows = $i-1;
	    					
//						echo "MAX ROWS: $max_rows - MAX COLS: $max_cols<br>";
						
						if($execute===true) {
							// se impostata la pulizia sovrascrittura pulisco tutto il subfile
							if($overwrite_src=="S") {
								$stmtdelete = $db->prepare("DELETE", $customSubfile->getFullTableName(), null, null);
								
								$result = $db->execute($stmtdelete);
							}
														
							// se tutto a posto riciclo chiamando la routine di scrittura del subfile							
							$error = false;
							
							for($i=$row_dati; $i<=$max_rows; $i++) {
								$writeRow = array();
							
								foreach($cols_must as $key => $j) {
									$val = trim($sheet->getCellByColumnAndRow($j, $i)->getCalculatedValue());
									
//									$writeRow[$key] = $val;

									$col = array_search($key, $cols_values);									
									$writeRow[$col] = $val;
								}
//								echo "WRITE ROW:<pre>"; print_r($writeRow); echo "</pre>";
								
								$res = $customSubfile->import_write_row($writeRow);
//								$res = $customSubfile->import_write_row($writeRow, $cols_import);
//								$res = $customSubfile->import_write_row($writeRow, $cols_values);
								
								if($res===false)
									$error = true;
							}
							
							if($error===true)
								$messageContext->addMessage("ERROR", "Errore durante l'importazione");
    						else
	    						$messageContext->addMessage("SUCCESS", "Importazione Completata. Controllare i dati");
						}						
	    			}
	    		}
	    	}	
	    }
	    else {
			$messageContext->addMessage("ERROR", _t('ERROR_FILE_UPLOAD'));
		}  
//die("HERE");	    
//		$actionContext->onSuccess("CLOSE", "CLOSE_WINDOW_MSG");
		$actionContext->onSuccess($azione, "SUCCESS");
		$actionContext->onError($azione, "DEFAULT_2", "", "", true);
	}