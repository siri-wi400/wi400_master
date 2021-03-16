<?php

	require_once 'manager_messages_function.php';
	require_once 'import_messaggi_common.php';

	$azione = $actionContext->getAction();
	
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

	$actionContext->setLabel("Importazione Messaggi");
	
	if($actionContext->getForm()=="DEFAULT") {
		$actionContext->setLabel("Parametri");
	}
	else if($actionContext->getForm()=="IMPORT") {
		// LETTURA XLSM
		
		if(isset($_FILES['IMPORT_FILE']) && is_uploaded_file($_FILES['IMPORT_FILE']['tmp_name'])) {
			// Controllo che il file non superi i 1 MB
			if($_FILES['IMPORT_FILE']['size'] > 1200000000) {
				$messageContext->addMessage("ERROR","Il file non deve superare 1 MB!");
			}
			else {
				$file_name = $_FILES['IMPORT_FILE']['name'];
				$file_parts = pathinfo($file_name);
				//echo "FILE PARTS:<pre>"; print_r($file_parts); echo "</pre>";
		
				$imgExt = strtoupper($file_parts['extension']);
		
				if(!in_array($imgExt, array("XLS", "XLSX", "XLSM"))) {
					$messageContext->addMessage("ERROR","Il file deve essere in formato xlsm/xlsx/xls.");
				}
				else if(!file_exists($_FILES['IMPORT_FILE']['tmp_name'])) {
					$messageContext->addMessage("ERROR","File non trovato.");
				}
				else {
					$load_file_name = $_FILES['IMPORT_FILE']['tmp_name'];
//					echo "FILE: ".$load_file_name."<br>";
/*		
					use PhpOffice\PhpSpreadsheet\IOFactory as PHPExcel_IOFactory;
					use PhpOffice\PhpSpreadsheet\Cell\Coordinate as PHPExcel_Cell;
*/		
					$classe_export = "";
					if(isset($settings['classe_export']) && $settings['classe_export']=="PhpSpreadsheet") {
						$classe_export = "PhpSpreadsheet";
						require_once $routine_path."/generali/xls_common_PhpSpreadsheet.php";
//						require_once $routine_path.'/vendor/autoload.php';
						$col_ini = 1;
					
						$importType = PhpOffice\PhpSpreadsheet\IOFactory::identify($load_file_name);
					}
					else {
						$classe_export = "PhpExcel";
						require_once $routine_path."/generali/xls_common.php";
						$col_ini = 0;
						
						switch($imgExt) {
							case "XLS":
								$importType = "Excel5";
								break;
							case "XLSX":
							case "XLSM":
								$importType = "Excel2007";
								break;
						}
					}
//					echo "CLASSE_EXPORT: $classe_export<br>";
//					echo "COL_INI: $col_ini<br>";
//					echo "FILE TYPE: $importType<br>";
							
					$PhpS_use = false;			// true se abbandoniamo completamente PhpExcel e attiviamo gli use all'inizio del programma
						
					// Lettura del file Excel
					
					// Create new PHPExcel object
					if($classe_export=="PhpSpreadsheet" && $PhpS_use===false) {
						echo "INIZIO PHPSPREADSHEET<br>";
						
						$objReader = PhpOffice\PhpSpreadsheet\IOFactory::createReader($importType);
					}
					else {
//						echo "INIZIO PHPEXCEL<br>";
						
						$objReader = PHPExcel_IOFactory::createReader($importType);
					}
						
					$objPHPExcel = $objReader->load($load_file_name);
//					echo "<font color='red'>LOAD FILE</font><br>";

					$error = false;

//					$sheet = $objPHPExcel->getActiveSheet();

					// FOGLIO "Testata Messaggio"
					$sheet = search_sheet_xls($objPHPExcel, "Testata Messaggio");
					
					$max_rows = $sheet->getHighestRow();
//					echo "MAX ROWS: $max_rows<br>";
					if($classe_export=="PhpSpreadsheet" && $PhpS_use===false)
						$max_cols = PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sheet->getHighestColumn());
					else
						$max_cols = PHPExcel_Cell::columnIndexFromString($sheet->getHighestColumn());
					$max_cols += $col_ini;
//					echo "MAX COLS: $max_cols<br>";
					
					$col = 1+$col_ini;

					$riga = 1;
					$titolo_msg = $sheet->getCellByColumnAndRow($col, $riga)->getCalculatedValue();
//					echo "Titolo Messaggio - COL: $col - RIGA: $riga - VAL: $titolo_msg<br>";
					
					$riga = 2;
					$format = $sheet->getCellByColumnAndRow($col, $riga)->getStyle()->getNumberFormat()->getFormatCode();
					$data_pub = $sheet->getCellByColumnAndRow($col, $riga)->getFormattedValue();
					$data_pub = check_data_val($data_pub, $format);
					$data_pub = dateModelToView($data_pub);
//					echo "Data Pubblicazione - COL: $col - RIGA: $riga - VAL: $data_pub<br>";
					
					$riga = 3;
					$format = $sheet->getCellByColumnAndRow($col, $riga)->getStyle()->getNumberFormat()->getFormatCode();
					$data_scad = $sheet->getCellByColumnAndRow($col, $riga)->getFormattedValue();
					$data_scad = check_data_val($data_scad, $format);
					$data_scad = dateModelToView($data_scad);
//					echo "Data Scadenza - COL: $col - RIGA: $riga - VAL: $data_scad<br>";
					
					$riga = 4;
					$visibile = $sheet->getCellByColumnAndRow($col, $riga)->getCalculatedValue();
//					echo "Sempre Visibile - COL: $col - RIGA: $riga - VAL: $visibile<br>";
					
					$riga = 5;
					$testo_msg = $sheet->getCellByColumnAndRow($col, $riga)->getCalculatedValue();
//					echo "Testo Messaggio - COL: $col - RIGA: $riga - VAL: $testo_msg<br>";
					
					$riga = 11;
					$template = $sheet->getCellByColumnAndRow($col, $riga)->getCalculatedValue();
//					echo "Template - COL: $col - RIGA: $riga - VAL: $template<br>";
					
					// FOGLIO "Destinatari"
					$sheet = search_sheet_xls($objPHPExcel, "Destinatari");
						
					$max_rows = $sheet->getHighestRow();
//					echo "MAX ROWS: $max_rows<br>";
					if($classe_export=="PhpSpreadsheet" && $PhpS_use===false)
					$max_cols = PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sheet->getHighestColumn());
					else
						$max_cols = PHPExcel_Cell::columnIndexFromString($sheet->getHighestColumn());
					$max_cols += $col_ini;
//					echo "MAX COLS: $max_cols<br>";
				
					$col = 0+$col_ini;
					$clienti_array = array();
					for($riga=2; $riga<=$max_rows; $riga++) {
						$val = $sheet->getCellByColumnAndRow($col, $riga)->getCalculatedValue();
						$val = trim($val);
					
						if($val=="")
							break;
						
//						$des = getDesDecode($val, "interlocutore");
						$des = getDescrizione("*INT", $val);
						if($des===false) {
							$error = true;
							$messageContext->addMessage("ERROR", "Cliente $val non valido");
							continue;
						}
					
						$clienti_array[] = $val;
					}
//					echo "CLIENTI:<pre>"; print_r($clienti_array); echo "</pre>";
					
					$col = 2+$col_ini;
					$utenti_array = array();
					for($riga=2; $riga<=$max_rows; $riga++) {
						$val = $sheet->getCellByColumnAndRow($col, $riga)->getCalculatedValue();
						$val = trim($val);
							
						if($val=="")
							break;
						
//						$des = getDescrizioneUser($val);
						$des = getDescrizione("*USER", $val);
						if($des===false) {
							$error = true;
							$messageContext->addMessage("ERROR", "Utente $val non valido");
							continue;
						}
							
						$utenti_array[] = $val;
					}
//					echo "UTENTI:<pre>"; print_r($utenti_array); echo "</pre>";
					
					$col = 4+$col_ini;
					$gruppi_array = array();
					for($riga=2; $riga<=$max_rows; $riga++) {
						$val = $sheet->getCellByColumnAndRow($col, $riga)->getCalculatedValue();
						$val = trim($val);
							
						if($val=="")
							break;
						
						$des = getDesDecode($val, "ente");
//						$des = getDescrizione("*GRUPPO", $val);		// @todo Adeguare la decodifica di *GRUPPO ?????
						if($des===false) {
							$error = true;
							$messageContext->addMessage("ERROR", "Gruppo $val non valido");
							continue;
						}
							
						$gruppi_array[] = $val;
					}
//					echo "GRUPPI:<pre>"; print_r($gruppi_array); echo "</pre>";
					
					// FOGLIO "Allegati"
					$sheet = search_sheet_xls($objPHPExcel, "Allegati");
					
					$max_rows = $sheet->getHighestRow();
//					echo "MAX ROWS: $max_rows<br>";
					if($classe_export=="PhpSpreadsheet" && $PhpS_use===false)
					$max_cols = PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sheet->getHighestColumn());
					else
						$max_cols = PHPExcel_Cell::columnIndexFromString($sheet->getHighestColumn());
					$max_cols += $col_ini;
//					echo "MAX COLS: $max_cols<br>";
					
					$atc_array = array();
					$col = 0+$col_ini;
					
					for($riga=2; $riga<=$max_rows; $riga++) {
						$val = $sheet->getCellByColumnAndRow($col, $riga)->getCalculatedValue();
						$val = trim($val);
							
						if($val=="")
							continue;
						
//						if(substr($val, 0, strlen("Allegato")=="Allegato"))
							$atc = $sheet->getCellByColumnAndRow($col+1, $riga)->getCalculatedValue();
						$atc = trim($atc);
						
						if($atc=="")
							continue;
							
						$atc_array[] = $atc;
					}
//					echo "ALLEGATI:<pre>"; print_r($atc_array); echo "</pre>";
					
					// SCARICAMENTO ALLEGATI
					$tmp_path = wi400File::getUserFile("tmp", "");
					
					$tmp_path_1 = $tmp_path.basename($file_name, ".xlsm")."/";
//					echo "TMP PATH 1: $tmp_path_1<br>";
					
					$za = new ZipArchive();
					
					$za->open($load_file_name);
					
					$file_extract = array();
					for($i=0; $i<$za->numFiles; $i++){
//						$stat = $za->statIndex($i);
//						$filename = $stat['name'];
						$filename = $za->getNameIndex($i);
//						echo "<font color='red'>EXT_1:</font> $filename<br>";
							
						$dir = dirname($filename);
//						echo "DIR: $dir<br>";
							
						if($dir=="xl/embeddings") {
							$file_ext = $tmp_path_1.$filename;
//							$za->extractTo($tmp_path_1, array($filename));
							$file_extract[] = $file_ext;
					
//							copy($tmp_path_1.$filename, $tmp_path_1.basename($file_ext));
//							$file_extract[] = $tmp_path_1.basename($file_ext);
						}
					}
//					echo "EXTRACT:<pre>"; print_r($file_extract); echo "</pre>";
					
					if($za->numFiles>0)
						$za->extractTo($tmp_path_1);
					
//					$za->close();
					
					$file_atc_array = array();
					if(!empty($file_extract)) {
						foreach($file_extract as $k => $file) {
//							echo "FILE: $file<br>";
							
							$path_info = pathinfo($file);
//							echo "PATH_INFO:<pre>"; print_r($path_info); echo "</pre>";

							$filename = "";
							if(!empty($atc_array))
								$filename = $atc_array[count($atc_array)-$k-1];
//							echo "FILENAME: $filename<br>";
							
							if($path_info['extension']=="bin") {
//								echo "<font color='blue'>BIN:</font> $file<br>";
					
								$cont = file_get_contents($file);
								$cont = substr($cont, strpos($cont, "%PDF"));
//								echo "CONT: $cont<br>";
					
								if($filename!="")
									$file_out = $tmp_path.basename($filename);
								else
									$file_out = $tmp_path.basename($file, ".bin").".pdf";
				
								$fh = fopen($file_out, "w");
								fwrite($fh, $cont);
								fclose($fh);
								
								$file_atc_array[] = $file_out;
							}
							else {
//								$file_out = $tmp_path.basename($file);
								$file_out = $tmp_path.basename($filename);
								
								copy($file, $file_out);
								
								$file_atc_array[] = $file_out;
							}
						}
					}
//					echo "FILE ATC ARRAY:<pre>"; print_r($file_atc_array); echo "</pre>";

					if($error===false) {
						// Scrittura Del nuovo messaggio
						$message = new wi400AnnounceMessage();
						$id = $message->getMessageId();
						$messageSet = new wi400AnnounceMessageSet($id);
						//$messageSet->setContenuto($testo_msg);
						
						$folder_mess = $data_path."messages/".$id."/";
							
						if(!file_exists($folder_mess)) {
							wi400_mkdir($folder_mess);
						}
						
						foreach ($file_atc_array as $key => $file) {
							$newname = $folder_mess.basename($file); 
							rename($file, $newname);
							$file_atc_array[$key] = $newname;
						}
//						echo "FILE ATC ARRAY:<pre>"; print_r($file_atc_array); echo "</pre>";
					
						$messageSet->setDataPubblicazione($data_pub);
						$messageSet->setDataScadenza($data_scad);
						$messageSet->setSempreVisibile($visibile);
						$messageSet->setConfermaLettura("S");
						$messageSet->setFormato("HTML");
						$messageSet->setGruppo("MSG_SEGRETERIA");
						$messageSet->setDivulgazione("*LOGIN");
						$messageSet->setHtml($testo_msg);
						$messageSet->setTitolo($titolo_msg);
						$messageSet->setTipo("SEGRETERIA");
						$messageSet->setArea("SEGVENDITE");
						
						// Destinatari CLIENTI
						$xx = 0;
						foreach ($clienti_array as $Ckey => $Cvalue) {
							$xx++;
							$messageSet->setDestinatario($xx, "*INT", $Cvalue);
						}
						
						// Destinatari UTENTI
						foreach ($utenti_array as $Ckey => $Cvalue) {
							$xx++;
							$messageSet->setDestinatario($xx, "*USER", strtoupper($Cvalue));
						}
						
						// Destinatari GRUPPI
						foreach ($gruppi_array as $Ckey => $Cvalue) {
							$xx++;
							$messageSet->setDestinatario($xx, "*GRUPPO", strtoupper($Cvalue));
						}
						
						// Allegati
						$xx = 0;
						foreach ($file_atc_array as $Ckey => $Cvalue) {
							$messageSet->setAllegato($Cvalue);
						}
					
						// Informazioni per il template
						if ($template!="") {
							$messageSet->setExtraParm("BUTTON_ENABLE", "S");
							$messageSet->setExtraParm("BUTTON_AZIONE", "GESTIONE_AUTOBOLLE_RESO_RETTIFICHE");
							$messageSet->setExtraParm("BUTTON_FORM", "LISTA");
							$messageSet->setExtraParm("BUTTON_GATEWAY", "FROM_TEMPLATE");
							$messageSet->setExtraParm("BUTTON_LABEL", "Esegui Azione");					
							$messageSet->setExtraParm("BUTTON_EXTRAPARM", "&TEMPLATE=".strtoupper($template));
						}
						
						try {
							$messageSet->writeMessaggio();
							//$messageSet->pubblicaMessaggio();
							$messageContext->addMessage("SUCCESS", "Importazione messaggio $id avvenuta con successo.");
							
							$res_pub = pubblica_messaggio($id);
							
							$actionContext->gotoAction("MANAGER_MESSAGES","","",True, False);
						}
						catch (Exception $e) {
							$messageContext->addMessage("ERROR", "Si sono verificati degli errori di importazione");
							$messageContext->addMessage("INFO", $e->getMessage());
							//echo "<br>".$e->getMessage();
						}
					}
					else {
						$messageContext->addMessage("ERROR", "Si sono verificati degli errori di importazione");
					}
					
					unlink($tmp_path_1);					
				}
			}
		}
		else {
			$messageContext->addMessage("ERROR","Selezionare un file");
			$actionContext->gotoAction($azione, "DEFAULT", "", true);
		}
		
		$actionContext->onSuccess($azione, "DEFAULT");
		$actionContext->onError($azione, "DEFAULT", "", "", true);
	}