<?php 

	require_once 'import_art_iva_21_commons.php';

	$azione = $actionContext->getAction();

	// Controllo se pulire i dati già presenti
	$clear_file ="N";
	if(isset($_POST['CLEAR_FILE'])) {
		$clear_file = "S";
		// Cancello articoli del gruppo
		$stmtdelete = $db->prepare("DELETE", $tabella); 		
	  	$result = $db->execute($stmtdelete);
	}
	
	if($actionContext->getForm()=="IMPORT") {		
		// Aumentata la dimensione del limite della memoria
		ini_set("memory_limit","1000M");
		set_time_limit(0);
	
//		echo "FILE: <pre>"; print_r($_FILES['IMPORT_FILE']); echo "</pre>";
	
		if(isset($_FILES['IMPORT_FILE']) && is_uploaded_file($_FILES['IMPORT_FILE']['tmp_name'])) {
			// Controllo che il file non superi i 1 MB
			if($_FILES['IMPORT_FILE']['size'] > 1200000) {
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
					
					$sheet = $objPHPExcel->getActiveSheet();
//					echo "<font color='red'>LOAD FILE</font><br>";
					
					$max_rows = $sheet->getHighestRow();
//					echo "MAX ROWS: $max_rows<br>";

					$fields = array("ARTICOLO");
					$stmtinsert = $db->prepare("INSERT", $tabella, null, $fields);
					
					$cod_art_title = "";
					$error = false;
					for($i=1; $i<$max_rows+1; $i++) {
						if($cod_art_title!="Articolo") {
							$cod_art_title = trim($sheet->getCellByColumnAndRow(0,$i)->getCalculatedValue());
							$cod_art_title = clean_string($cod_art_title);
						}
						else if($cod_art_title=="Articolo") {
							$cod_art = trim($sheet->getCellByColumnAndRow(0,$i)->getCalculatedValue());
							$cod_art = clean_string($cod_art);
							
							$fieldsValue = array($cod_art);
							$result = $db->execute($stmtinsert, $fieldsValue);

							if(!$result)
								$error = true;
						}
					}
					
					if($error===true)
						$messageContext->addMessage("ERROR","Errore durante l'importazione.");
					else
						$messageContext->addMessage("SUCCESS","Importazione completata con successo.");
					
				}
			}
		}
		
//		echo "FINE<br>";
		
		$actionContext->onSuccess($azione, "LIST");
		$actionContext->onError($azione, "DEFAULT","","",true);
//		echo "SEVERITY: ".$messageContext->getSeverity()."<br>";
//		echo "FORM: ".$actionContext->getForm()."<br>";
	}

?>