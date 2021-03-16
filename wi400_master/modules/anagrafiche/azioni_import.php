<?php

$azione = $actionContext->getAction();

if($azione=="AZIONI_IMPORT")
	importXLS("FAZISIRI", "AZIONE");
else if($azione=="MENU_IMPORT")
	importXLS("FMNUSIRI", "MENU");

function importXLS($tabella, $chiave){
	global $db, $routine_path, $messageContext;
	
	if (isset($_REQUEST["OVERWRITE_EXP"])){
		$overwrite = true;
	}
	else {
		$overwrite = false;
	}
	
	// Aumentata la dimensione del limite della memoria
	ini_set("memory_limit","1000M");
	set_time_limit(0);
	
//	echo "FILE: <pre>"; print_r($_FILES['IMPORT_FILE']); echo "</pre>";

	if(isset($_FILES['IMPORT_FILE']) && is_uploaded_file($_FILES['IMPORT_FILE']['tmp_name'])) {
		// Controllo che il file non superi i 1 MB
		if($_FILES['IMPORT_FILE']['size'] > 1200000) {
			$messageContext->addMessage("ERROR","Il file non deve superare 1 MB!");
		}
		else {
			$file_name = $_FILES['IMPORT_FILE']['name'];
			$file_parts = pathinfo($file_name);
//			echo "FILE PARTS:<pre>"; print_r($file_parts); echo "</pre>";
	
			$imgExt = $file_parts['extension'];
	
			if(!in_array($imgExt, array("xls", "xlsx", "XLS", "XLSX"))) {
				$messageContext->addMessage("ERROR","Il file deve essere in formato xls/xlsx.");
			}
			else if(!file_exists($_FILES['IMPORT_FILE']['tmp_name'])) {
				$messageContext->addMessage("ERROR","File non trovato.");
			}
			else {
				checkIfZipLoaded();
					
				// PHPExcel
				require_once $routine_path."/excel/PHPExcel.php";
					
				// PHPExcel_IOFactory
				require_once $routine_path.'/excel/PHPExcel/IOFactory.php';
					
				$load_file_name = $_FILES['IMPORT_FILE']['tmp_name'];
					
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
					
				// Lettura del file Excel
					
				// Create new PHPExcel object
				$objReader = PHPExcel_IOFactory::createReader($importType);
				$objPHPExcel = $objReader->load($load_file_name);
//				echo "<font color='red'>LOAD FILE</font><br>";

				$sheet = $objPHPExcel->getActiveSheet();
				
//				$tableCols = $db->columns($tabella, "", True);
//				echo "TABLE:<pre>"; print_r($tableCols); echo "</pre>";
				$colsData = $db->columns($tabella);
//				echo "COLS DATA:<pre>"; print_r($colsData); echo "</pre>";
				$tableCols = array_keys($colsData);
//				echo "COLUMNS:<pre>"; print_r($tableCols); echo "</pre>";
				
				$max_rows = $sheet->getHighestRow();
//				echo "MAX ROWS: $max_rows<br>";
				$max_cols = PHPExcel_Cell::columnIndexFromString($sheet->getHighestColumn());
//				echo "MAX COLS: $max_cols<br>";

				$struttura = true;
				$fields = array();
				for($col=0; $col<$max_cols; $col++) {
					$campo = trim($sheet->getCellByColumnAndRow($col,1)->getCalculatedValue());
					
					if($campo=="") {
						$max_cols = $col;
						break;
					}
					
					if(!array_key_exists($campo, $colsData)) {
						$struttura = false;
						break;
					}
					
					if($campo!="ID")
						$fields[$campo] = $col;
				}
				
//				echo "FIELDS:<pre>"; print_r($fields); echo "</pre>";
				
				if($struttura===true) {
					// SELECT **************************
					$sql = "select $chiave from $tabella where $chiave=?";
					$stmtSelect = $db->singlePrepare($sql, 0, true);
				
					// INSERT **************************
//					$field = array("AZIONE", "DESCRIZIONE", "MODULO", "TIPO", "ICOMENU", "CHKPGM", "EXPICO", "MODEL", "VIEW", "TIPOPGM", "WI400_GROUPS", "GATEWAY", "VALIDATION", "SYSTEM", "PACKAGE", "LOG_AZIONE", "URL", "URL_OPEN", "URL_MODAL", "HAS_WIDGET");

					$fieldsValue = getDs($tabella);
					unset($fieldsValue['ID']);
					
					$field = array_keys($fieldsValue);
					
					$field = $db->escapeSpecialKey($field);
//					echo "FIELD:<pre>"; print_r($field); echo "</pre>";
					
//					$stmtInsert = $db->prepare("INSERT", $tabella, null, array_keys($fields));
					$stmtInsert = $db->prepare("INSERT", $tabella, null, $field);
										
					// DELETE **************************
					$stmtDelete = $db->prepare("DELETE", $tabella, array($chiave), null);
					
					$num_fields = count($fields);
					
					$Inserted = 0;
					$Updated = 0;
					$NotUpdated = 0;
					$InsertError = 0;
					$OverwriteError = 0;
					$DeleteError = 0;
				
					for($riga=2; $riga<=$max_rows; $riga++) {
						$dati = array();
						$dati = $fieldsValue;
						
						$empty = 0;
						foreach($fields as $key => $col) {
							$val = trim($sheet->getCellByColumnAndRow($col, $riga)->getCalculatedValue());
//							$val = clean_string($val);
							$val = prepare_string($val);
							
							if($val=="") {
								$empty++;
							}
								
							$dati[$key] = $val;
						}
//						echo "DATI:<pre>"; print_r($dati); echo "</pre>";

						if($empty==$num_fields) {
//							echo "<font color='blue'>EMPTY</font><br>";
							$max_rows = $riga;
							break;
						}
						
						foreach($dati as $key => $val) {
							if($val=="") {
								switch($colsData[$key]['DATA_TYPE_STRING']) {
									case "DECIMAL":
									case "NUMERIC":
										$val = 0;
										break;
									case "TIMESTMP":
										$val = date("Y-m-d-H.i.s.u", mktime(0,0,0,0,0,0));
										break;
								}
//								echo "CAMPO: $key - TIPO: ".$campi[$key]['DATA_VAL_STRING']." - VAL: $val<br>";

								$dati[$key] = $val;
							}
						}
//						echo "DATI:<pre>"; print_r($dati); echo "</pre>";

						// Controllo l'esistenza dell'azione
						$res = $db->execute($stmtSelect, array($dati[$chiave]));
						
						if($row = $db->fetch_array($stmtSelect)) {
//							echo "ROW: ".$row['AZIONE']."<br>";
							if($overwrite===true) {
//								echo "OVERWRITE<br>";
								// SOVRASCRITTURA = CANCELLAZIONE + INSERT
								$res_del = $db->execute($stmtDelete, array($dati[$chiave]));
								
								if($res_del) {
									// INSERT
									$res_ins = $db->execute($stmtInsert, $dati);
									
									if($res_ins)
										$Updated++;
									else
										$OverwriteError++;
								}
								else {
									$DeleteError++;
								}
							}
							else {
								$NotUpdated++;
								continue;
							}
						}
						else {
//							echo "INSERT: ".$dati[$chiave]."<br>";
//							echo "DATI:<pre>"; print_r($dati); echo "</pre>";
							// INSERT
							$res_ins = $db->execute($stmtInsert, $dati);
							
							if($res_ins)
								$Inserted++;
							else
								$InsertError++;
						}
					}
					
					if($chiave=="AZIONE")
						$txt = "Azioni";
					else if($chiave=="MENU")
						$txt = "Menu";

					if($Inserted != 0) {
						$messageContext->addMessage("SUCCESS","$txt inserite: ".$Inserted);
					}
					if($Updated != 0) {
						$messageContext->addMessage("SUCCESS","$txt aggiornate: ".$Updated);
					}
					if($NotUpdated != 0) {
						$messageContext->addMessage("SUCCESS","$txt non aggiornate: ".$NotUpdated);
					}
					$errori = $InsertError + $OverwriteError + $DeleteError;
					if($errori==0) {
						$messageContext->addMessage("SUCCESS","File caricato in modo corretto.");
					}
					else {die("sdadsad");
						$messageContext->addMessage("ERROR", "Si sono verificati ".$errori." errori nel caricamento del file");
						if($InsertError != 0) {
							$messageContext->addMessage("ERROR", "Errori di inserimento: ".$InsertError);
						}
						if($OverwriteError != 0) {
							$messageContext->addMessage("ERROR", "Errori di overwrite: ".$OverwriteError);
						}
						if($DeleteError != 0) {
							$messageContext->addMessage("ERROR", "Errori di cancellazione: ".$DeleteError);
						}
					}
				}
				else {
					$messageContext->addMessage("ERROR", "E' presente un campo non appartente alla tabella $tabella");
				}
			}
		}
	}
	else{
		$messageContext->addMessage("ERROR","<p>Errore nel caricamento del file.</p>");
	}
}