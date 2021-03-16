<?php 
/*
	use PhpOffice\PhpSpreadsheet\IOFactory as PHPExcel_IOFactory;
	use PhpOffice\PhpSpreadsheet\Cell\Coordinate as PHPExcel_Cell;
*/
	$azione = $actionContext->getAction();

	// Tabella
	$tabella = wi400Detail::getDetailValue($azione."_SRC","TABELLA");
//	echo "TABELLA: $tabella<br>";

	// Libreria
	$libreria = "";
	if(wi400Detail::getDetailValue($azione."_SRC","LIBRERIA")!="")
		$libreria = wi400Detail::getDetailValue($azione."_SRC","LIBRERIA");
//	echo "LIBRERIA: $libreria<br>";

	$from = $tabella;
	if($libreria!="")
		$from = $libreria."/".$tabella;
//	echo "FROM: $from<br>";
	
	// Foglio
	$foglio = "";
	if(wi400Detail::getDetailValue($azione."_SRC","FOGLIO")!="")
		$foglio = trim(wi400Detail::getDetailValue($azione."_SRC","FOGLIO"));
//	echo "FOGLIO: $foglio<br>";

	// Riga dati
	$start_row = 1;
	if(wi400Detail::getDetailValue($azione."_SRC","START_ROW")!="")
		$start_row = wi400Detail::getDetailValue($azione."_SRC","START_ROW");
//	echo "RIGA DATI: $start_row<br>";

	// Colonne
	$colonne_array = array();
	if(wi400Detail::getDetailValue($azione."_SRC","COLONNA")!="")
		$colonne_array = wi400Detail::getDetailValue($azione."_SRC","COLONNA");
//	echo "COLONNE:<pre>"; print_r($colonne_array); echo "</pre>";

	// Campi
	$campi_array = array();
	if(wi400Detail::getDetailValue($azione."_SRC","CAMPO")!="")
		$campi_array = wi400Detail::getDetailValue($azione."_SRC","CAMPO");
//	echo "CAMPI:<pre>"; print_r($campi_array); echo "</pre>";
		
	// Controllo se pulire i dati già presenti
	$clear_file ="N";
	$err_del = false;
	if(isset($_POST['CLEAR_FILE'])) {
		$clear_file = "S";
		// Cancello articoli del gruppo
	  	$stmtdelete = $db->prepare("DELETE", $from); 		
	  	$result_del = $db->execute($stmtdelete);
	  	
	  	if(!$result_del) {
	  		$err_del = true;
			$messageContext->addMessage("ERROR","Errore durante la pulizia dei dati già presenti.");
			$messageContext->addMessage("ERROR","Importazione interrotta.");
	  	}
		else
			$messageContext->addMessage("SUCCESS","Pulizia dei dati già presenti completata con successo.");
			
		$actionContext->onSuccess($azione, "LIST");
		$actionContext->onError($azione, "DEFAULT","","",true);
	}
	
	$ignora_struttura = "N";
	if(isset($_POST['IGNORA_STRUTTURA'])) {
		$ignora_struttura = "S";
	}
	
	if($actionContext->getForm()=="IMPORT" && $err_del===false) {
		// Aumentata la dimensione del limite della memoria
		ini_set("memory_limit","1000M");
		set_time_limit(0);
	
//		echo "FILE: <pre>"; print_r($_FILES['IMPORT_FILE']); echo "</pre>";
	
		if(isset($_FILES['IMPORT_FILE']) && is_uploaded_file($_FILES['IMPORT_FILE']['tmp_name'])) {
			// Controllo che il file non superi i 10 MB
			if($_FILES['IMPORT_FILE']['size'] > 1200000000) {
				$messageContext->addMessage("ERROR","Il file non deve superare 1 MB!");
			}
			else {
				$file_name = $_FILES['IMPORT_FILE']['name'];
				$file_parts = pathinfo($file_name);
//				echo "FILE PARTS:<pre>"; print_r($file_parts); echo "</pre>";
				
				$imgExt = $file_parts['extension'];
				
				if(!in_array($imgExt, array("xls", "xlsx", "XLS", "XLSX"))) {
					$messageContext->addMessage("ERROR","Il file deve essere in formato xls/xlsx.");
				}
				else if(!file_exists($_FILES['IMPORT_FILE']['tmp_name'])) {
					$messageContext->addMessage("ERROR","File non trovato.");
				}
				else {
/*					
					checkIfZipLoaded();
					
					// PHPExcel
					require_once $routine_path."/excel/PHPExcel.php";
					
					// PHPExcel_IOFactory
					require_once $routine_path.'/excel/PHPExcel/IOFactory.php';
*/					
					$load_file_name = $_FILES['IMPORT_FILE']['tmp_name'];
					
					$classe_export = "";
					if(isset($settings['classe_export']) && $settings['classe_export']=="PhpSpreadsheet") {
//						require_once $routine_path."/generali/xls_common_PhpSpreadsheet.php";
						require_once $routine_path.'/vendor/autoload.php';
						
						$classe_export = "PhpSpreadsheet";
						$col_ini = 1;
							
						$importType = PhpOffice\PhpSpreadsheet\IOFactory::identify($load_file_name);
					}
					else {
						$classe_export = "PhpExcel";
						require_once $routine_path."/generali/xls_common.php";
						$col_ini = 0;
							
						switch($imgExt) {
							case "xls":
							case "XLS":
								$importType = "Excel5";
								break;
							case "xlsx":
							case "XLSX":
								$importType = "Excel2007";
								break;
						}
					}
					echo "CLASSE_EXPORT: $classe_export<br>";
					echo "COL_INI: $col_ini<br>";
					echo "FILE TYPE: $importType<br>";
					
					$PhpS_use = false;			// true se abbandoniamo completamente PhpExcel e attiviamo gli use all'inizio del programma
					
					// Lettura del file Excel
					
					// Create new PHPExcel object
					if($classe_export=="PhpSpreadsheet" && $PhpS_use===false) {
						echo "INIZIO PHPSPREADSHEET<br>";
						
						$objReader = PhpOffice\PhpSpreadsheet\IOFactory::createReader($importType);
					}
					else {
						echo "INIZIO PHPEXCEL<br>";
						
						$objReader = PHPExcel_IOFactory::createReader($importType);
					}
					
					$objPHPExcel = $objReader->load($load_file_name);
//					echo "<font color='red'>LOAD FILE</font><br>";		

//					echo "NUM SHEETS: ".count($objPHPExcel->getAllSheets())."<br>";
//					echo "SHEETS:<pre>"; print_r($objPHPExcel->getAllSheets()); echo "</pre>";
					
					$found = false;
					if($foglio=="") { 
//						echo "CURRENT SHEET<br>";						
						$sheet = $objPHPExcel->getActiveSheet();
						$found = true;
					}
					else {
//						echo "FOGLIO: $foglio<br>";
						
						$num_sheets = count($objPHPExcel->getAllSheets());
//						echo "NUM SHEETS: $num_sheets<br>";
						
						for($s=0; $s<$num_sheets; $s++) {
							$sheet = $objPHPExcel->getSheet($s);
//							echo "TITLE: ".$sheet->getTitle()."<br>";
							if(strncmp($sheet->getTitle(),$foglio,strlen($foglio))==0) {
//								echo "<font color='red'>TITLE FOUND:</font> ".$sheet->getTitle()."<br>";
								$found = true;
								break;
							}
						}
					}
					
					if($found==false) {
						$messageContext->addMessage("ERROR","Foglio '$foglio' non trovato.");
					}
					else if($found===true) {
						$max_rows = $sheet->getHighestRow();
//						echo "MAX ROWS: $max_rows<br>";

						$struttura = true;
						$campi = array();

						if($ignora_struttura=="N") {
							$campi = $db->columns($tabella, "", False, "", $libreria);
//							echo "CAMPI DB:<pre>"; print_r($campi); echo "</pre>";
	
							$struttura = true;
							foreach($campi_array as $c) {
								if(!array_key_exists($c, $campi)) {
//									echo "STRUTTURA - FALSE<br>";
									$struttura = false;
									break;
								}
							}
						}
//die("HERE");
						if($struttura===true) {
							$fields = array();
							$fields = array_combine($campi_array, $colonne_array);
//							echo "FIELDS:<pre>"; print_r($fields); echo "</pre>";

							$num_fields = count($fields);
	
							$stmtinsert = $db->prepare("INSERT", $from, null, $campi_array);
						
							$error = false;
							for($i=$start_row; $i<$max_rows+1; $i++) {
								$dati = array();
								$empty = 0;
								foreach($fields as $key => $col) {
									$val = trim($sheet->getCell($col.$i)->getCalculatedValue());
									$val = clean_string($val);
									$val = utf8_decode($val);
//									$val = prepare_string($val);
									
									if($val=="") {
										$empty++;
									}
									
									$dati[$key] = $val;
								}
//								echo "DATI:<pre>"; print_r($dati); echo "</pre>";
//								echo "NUM FIELDS: $num_fields - EMPTY: $empty<br>";
							
								if($empty==$num_fields) {
//									echo "<font color='blue'>EMPTY</font><br>";
									continue;
								}
								
								if(!empty($campi)) {
									foreach($dati as $key => $val) {
										if($val=="") {
											switch($campi[$key]['DATA_TYPE_STRING']) {
												case "DECIMAL":
												case "NUMERIC":
													$val = 0;
													break;
												case "TIMESTMP":
													$val = date("Y-m-d-H.i.s.u", mktime(0,0,0,0,0,0));
													break;
											}
//											echo "CAMPO: $key - TIPO: ".$campi[$key]['DATA_VAL_STRING']." - VAL: $val<br>";
										
											$dati[$key] = $val;
										}
									}
								}
//								echo "DATI:<pre>"; print_r($dati); echo "</pre>";

								$result = $db->execute($stmtinsert, $dati);

								if(!$result) {
									$msg = "Errore di inserimento a riga $i";										
									$messageContext->addMessage("LOG", $msg);
									
									echo "ERRORE - RIGA: $i - DATI:<pre>"; print_r($dati); echo "</pre>";
die("HERE");									
									$error = true;
								}
							}
					
							if($error===true)
								$messageContext->addMessage("ERROR","Errore durante l'importazione.");
							else
								$messageContext->addMessage("SUCCESS","Importazione completata con successo.");
						}
						else {
							$messageContext->addMessage("ERROR","E' presente un campo non appartenente alla tabella.");
						}
					}
				}
			}
		}
		
//		echo "FINE<br>";
//die();		
		$actionContext->onSuccess($azione, "LIST");
		$actionContext->onError($azione, "DEFAULT","","",true);
//		echo "SEVERITY: ".$messageContext->getSeverity()."<br>";
//		echo "FORM: ".$actionContext->getForm()."<br>";
	}

?>