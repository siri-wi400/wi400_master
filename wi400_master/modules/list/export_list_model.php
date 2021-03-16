<?php 

	require_once $routine_path."/classi/wi400ExportList.cls.php";
	require_once $routine_path."/classi/wi400invioEmail.cls.php";
	
	require_once 'export_list_pdf.php';
	
	if(isset($settings['classe_export']) && $settings['classe_export']=="PhpSpreadsheet")
		require_once 'export_list_xls_PhpSpreadsheet.php';	// EXCEL - PhpSpreadsheet
	else
		require_once 'export_list_xls.php';					// EXCEL - PhpExcel
		
	require_once 'export_list_xml.php';
	require_once 'export_list_csv.php';
	
	// Aumentata la dimensione del limite della memoria
	ini_set("memory_limit","1000M");
	set_time_limit(0);
	
	$csv_exp = false;
	$csv_limit = false;
	
//	echo "REQUEST:<pre>"; print_r($_REQUEST); echo "</pre>";

	if($actionContext->getForm()=="DEFAULT") {
		$export = new wi400ExportList();
		
		if (isset($_REQUEST['EXP_LIST'])){
			$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $_REQUEST['EXP_LIST']);
			
			// Recupero dei dettagli della lista (per passarli avanti)
			if($wi400List->getNoDetails()===false) {
				$idDetails = array();
				$idDetails = $wi400List->getExportDetails();
				
//				echo "DETAILS:"; print_r($idDetails); echo "<br>";

				if(!empty($idDetails)) {				
					$export->setIdDetails($idDetails);
				}
				else {
					if (isset($_REQUEST['IDDETAIL'])){
//						echo "IDDETAIL<br>";
						$idDetails = array();
						
						if (!is_array($_REQUEST['IDDETAIL'])){
//							echo "Non è un array<br>";
							$idDetails[] = $_REQUEST['IDDETAIL'];
						}
						else
							$idDetails = $_REQUEST['IDDETAIL'];
						
						$export->setIdDetails($idDetails);					
					}
					
					if (isset($_REQUEST['WI400_DETTAGLI'])){
//						echo "WI400_DETTAGLI<br>";
						$idDetails = array();
					
						if (!is_array($_REQUEST['WI400_DETTAGLI'])){
//							echo "Non è un array<br>";
							$idDetails[] = $_REQUEST['WI400_DETTAGLI'];
						}
						else
							$idDetails = $_REQUEST['WI400_DETTAGLI'];
					
						$export->setIdDetails($idDetails);
					}
//					echo "DETAILS:"; print_r($idDetails); echo "<br>";
				}
			}
		}
		else {
			$messageContext->addMessage("ERROR", "Lista non trovata.");
		}
	}
	else if(in_array($actionContext->getForm(), array("EXPORT", "EXPORT_BATCH"))) {
		if(isset($_REQUEST['BATCH']) && $_REQUEST['BATCH']=="BATCH") {
			require_once $routine_path."/classi/wi400Batch.cls.php";
			
			$idList = $_REQUEST['EXP_LIST'];
			
			$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);

			// @todo Copia della lista su file temporaneo (per ritirarla su in batch non si può far conto della sessione, perchè cambia)
//			$idList_new = "EXP_LIST_BATCH_".$idList;
//			wi400Session::save(wi400Session::$_TYPE_LIST, $idList_new, $wi400List);
//			wi400Session::save(wi400Session::$_TYPE_SUBFILE,  $wi400List->getSubfile(), $wi400Subfile);
				
//			$batch = new wi400Batch($_SESSION['user']);
			$batch = new wi400Batch("QPGMR", "QPGMR");
			
			$batch->setAction("EXPORT_LIST_BATCH");
			
			$batch->addParameter("FORMAT", $_REQUEST['FORMAT']);
			$batch->addParameter("TARGET", $_REQUEST['TARGET']);
			$batch->addParameter("ORIENTATION", $_REQUEST['ORIENTATION']);
			
			// Controllo se è stata richiesta l'esportazione dell'indicazione dei filtri della lista utilizzati
			if(isset($_REQUEST['FILTERS']))
				$batch->addParameter("FILTERS", $_REQUEST['FILTERS']);
			
			$batch->addParameter("EXP_LIST", $idList);
			
			$id = $batch->getId();
			
			// Recupero la lista
			$list_file = wi400Session::getFileName(wi400Session::$_TYPE_LIST, $idList);
			
			$batch->duplicateFileBatch($list_file, $idList, wi400Session::$_TYPE_LIST);
			
			// Recupero un eventuale subfile
			if($wi400List->getSubfile()!=null) {
				$sub_file = wi400Session::getFileName(wi400Session::$_TYPE_SUBFILE, $wi400List->getSubfile());
				$batch->duplicateFileBatch($sub_file, $wi400List->getSubfile(), wi400Session::$_TYPE_SUBFILE);
			}
				
			// Recupero i dettagli da stampare
			if(isset($_REQUEST['ID_DETAILS'])){
				$batch->addParameter("ID_DETAILS", $_REQUEST['ID_DETAILS']);
			}
				
			if(isset($_REQUEST['ZIP'])) {
				$batch->addParameter("ZIP", $_REQUEST['ZIP']);
			}
			
			if(isset($_REQUEST['NOTIFICA'])) {
				$batch->addParameter("NOTIFICA", $_REQUEST['NOTIFICA']);
			}
	
			$batch->addParameter("USERNAME", $_SESSION['user']);
			
			$batch->addParameter("USER_LOCALE", $_SESSION['locale']);
				
			$area_fun = "";
			if(isset($_SESSION["LOGIN_PROFILE"]['AREA']))
				$area_fun = $_SESSION["LOGIN_PROFILE"]['AREA'];
			$batch->addParameter("AREA_FUN", $area_fun);
				
			if(isset($_SESSION["CUSTOM_LANGUAGE"]))
				$language = getLanguageID($_SESSION["CUSTOM_LANGUAGE"], true);
			else if(isset($_SESSION["USER_LANGUAGE"]))
				$language = getLanguageID($_SESSION["USER_LANGUAGE"], true);
			
			$batch->addParameter("custom_language", $language);
			$batch->addParameter("name_job", "EXP_LIST_BATCH");
			$batch->addParameter("des_job", "Esportazione lista");
			
			$result_batch = $batch->call($connzend);
			
			$actionContext->gotoAction("CLOSE", "CLOSE_WINDOW_MSG", "", true);
		}
		else {
			$exportFormat = $_REQUEST['FORMAT'];
			$exportTarget = $_REQUEST['TARGET'];
			$exportOrientation = $_REQUEST['ORIENTATION'];
		
			// Controllo se è stata richiesta l'esportazione dell'indicazione dei filtri della lista utilizzati
			$exportFilters = false;
			if(isset($_REQUEST['FILTERS']) && $_REQUEST['FILTERS']=="FILTERS")
				$exportFilters = true;
		
			$idList = $_REQUEST['EXP_LIST'];
//			echo "IDLIST: $idList<br>";
			
			// Recupero i dettagli da stampare
			$idDetails = array();
			if(isset($_REQUEST['ID_DETAILS'])){
				$idDetails = unserialize($_REQUEST['ID_DETAILS']);					
			}			
//			echo "DETAILS:"; print_r($idDetails); echo "<br>";

			if($exportTarget!="PAGE") {
				$pos = array_search("LABEL_PAGE_DIALOG", $idDetails);
				if($pos!==false)
					unset($idDetails[$pos]);
//				echo "DETAILS:"; print_r($idDetails); echo "<br>";
			}
			
			$zip = false;
			if(isset($_REQUEST['ZIP']) && $_REQUEST['ZIP']=="ZIP") {
				$zip = true;
			}
			
//			$notifica = "";
			if(isset($_REQUEST['NOTIFICA'])) {
				$notifica = $_REQUEST['NOTIFICA'];
			}
			
			$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
		
			if ($wi400List->getIncludeFile()!="") {
				require_once $wi400List->getIncludeFile();
			}
			
			$export = new wi400ExportList($exportTarget, $wi400List, $exportFilters, $exportOrientation);
			
			// Recupero la query della lista (compresi i filtri utilizzati)
			$export->prepare();
		
//			$sql_list = $export->get_query();
//			echo "SQL: $sql_list\r\n";
		
			// Recupero i dettagli da stampare
			if(!empty($idDetails)){
				$export->setIdDetails($idDetails);	
			}
		
//			echo "EXPORT FORMAT: $exportFormat<br>";
		
			// Lancio l'esportazione a seconda del formato (impostato nel codice html che si trova nella _view.php)
			// e ottengo il filename di ritorno in modo che sia comune con la _view.php
			switch($exportFormat) {
				case "pdf":
//					exportPDF($export, $idList, $exportTarget, $exportOrientation, isset($_REQUEST['GET_PREVIEW']));
					exportPDF($export, $idList, $exportTarget, $exportFilters, $exportOrientation, isset($_REQUEST['GET_PREVIEW']));
					break;
				case "excel5":
				case "excel2007":
					$tot_rows = $wi400List->getTotalRows();
//					echo "TOT RIGHE: $tot_rows<br>";
				
					// Impostazione del limite di righe esportabili in XLS/XLSX,
					// se viene superato l'esportazione si interrompe e viene suggerito di rieseguire l'esportazione in formato CSV
					if($exportTarget=="ALL" && isset($settings['max_export_rows_xls']) && $tot_rows>0 && $tot_rows>$settings['max_export_rows_xls']) {
						$csv_exp = true;
//						echo "CSV EXP<br>";
					}
					else {
						$csv_exp = exportXLS($export, $idList, $exportFormat, $exportTarget, $exportFilters);
					}
						
					if($csv_exp===true) {
						$msg = "Raggiunto limite di esportazione in XLS/XLSX di ".$settings['max_export_rows_xls']." righe. Eseguire l'esportazione in formato CSV.";
						$messageContext->addMessage("ERROR", $msg);
						$actionContext->setForm("DEFAULT");
					}
					
					break;
				case "xml":
					exportXML($export, $idList, $exportTarget);
					break;
				case "csv":
					$tot_rows = $wi400List->getTotalRows();
//					echo "TOT RIGHE: $tot_rows<br>";
	
					if($exportTarget=="ALL" && isset($settings['max_export_rows_csv']) && $tot_rows>0 && $tot_rows>$settings['max_export_rows_csv']) {
						$csv_limit = true;
					}
					else {
						$csv_limit = exportCSV($export, $idList, $exportTarget);
					}
					
					if($csv_limit===true) {
						$msg = "Raggiunto limite di esportazione in CSV di ".$settings['max_export_rows_csv']." righe. Esportazione annullata.";
						$messageContext->addMessage("ERROR", $msg);
						$actionContext->setForm("DEFAULT");
					}
					
					break;
			}
			
			if($csv_exp===false) {
				// Recupero dei parametri del file generato necessari per il download
				$filename = $export->getFilename();
				$filepath = $export->get_filepath();
				$TypeImage = $export->getTypeImage();
				$temp = $export->getTemp();
				
				// Zip del file
				if($zip===true) {
					$filepath = $export->get_filepath();
					
					$zip_parts = explode(".", basename($filepath));
					$zip_path = dirname($filepath)."/".$zip_parts[0].'.zip';
				
					wi400invioEmail::compress(array($filepath),$zip_path);
					
					$filename = basename($zip_path);
					$TypeImage = "zip";
					
					unlink($filepath);
					
					$filepath = $zip_path;
				}
			}
//			echo "FILENAME: $filename - TYPE: $TypeImage - TEMP: $temp - FILEPATH: $filepath<br>";
		}
	}