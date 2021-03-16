<?php 

	checkIfZipLoaded();
	
	// Error reporting
	error_reporting(E_ALL);
	
	// PHPExcel
	require_once $routine_path."/excel/PHPExcel.php";
	
	// PHPExcel_IOFactory
	require_once $routine_path.'/excel/PHPExcel/IOFactory.php';

	$azione = $actionContext->getAction();

	if($actionContext->getForm()=="IMPORT") {
		if (isset($_REQUEST["CLEAR_FILE"])){
			$clear = true;
//			echo "CLEAR: $clear<br>";
			
			// Cancello articoli del gruppo
			$stmtdelete = $db->prepare("DELETE", "FCAROIL"); 		
		  	$result = $db->execute($stmtdelete);
		}

		if(isset($_FILES['IMPORT_FILE']) && is_uploaded_file($_FILES['IMPORT_FILE']['tmp_name'])) {
			// Controllo che il file non superi i 1 MB
			if($_FILES['IMPORT_FILE']['size'] > 1000000) {
				$messageContext->addMessage("ERROR","Il file non deve superare 1 MB!");
			}
			else {
				$imgExt = substr($_FILES['IMPORT_FILE']['name'],strrpos($_FILES['IMPORT_FILE']['name'],".")+1);
				
				if($imgExt == "xlsx") {
					$messageContext->addMessage("ERROR","Il file deve essere in formato xls 2003.");
				}
				else if($imgExt != "xls" && $imgExt != "XLS") {
					$messageContext->addMessage("ERROR","Il file deve essere in formato xls.");
				}
				else {
					if(!file_exists($_FILES['IMPORT_FILE']['tmp_name'])) {
						$messageContext->addMessage("ERROR","File non trovato.");
					}
					
					$file_name = $_FILES['IMPORT_FILE']['tmp_name'];
//					echo "FILE: $file_name<br>";
					$objPHPExcel = PHPExcel_IOFactory::load($file_name);
					
					$sheet = $objPHPExcel->getActiveSheet();
					
					$max_rows = $sheet->getHighestRow();
//					echo "MAX ROWS: $max_rows<br>";
					
					for($i=1; $i<$max_rows+1; $i++) {
						$cod_atg_title = trim($sheet->getCellByColumnAndRow(0,$i)->getCalculatedValue());
						$cod_atg_title = clean_string($cod_atg_title);

						if($cod_atg_title=="COD_ATG")
							break;
					}
					
					if($cod_atg_title=="COD_ATG") {
						$qta_title = trim($sheet->getCellByColumnAndRow(1,$i)->getCalculatedValue());
						$qta_title = clean_string($qta_title);
	
						$costo_title = trim($sheet->getCellByColumnAndRow(2,$i)->getCalculatedValue());
						$costo_title = clean_string($costo_title);

//						echo "TITOLI: $cod_atg_title - $qta_title - $costo_title<br>";

						if($cod_atg_title=="COD_ATG" && $qta_title=="QTA'" && $costo_title=="COSTO UNITARIO") {
//							echo "OK<br>";

							$error = false;
							
							$fields = array("WARTIC", "WQUANT", "WCOSTO");
							$stmtinsert = $db->prepare("INSERT", "FCAROIL", null, $fields);
					
							for($r=$i+1; $r<$max_rows+1; $r++) {
								$cod_atg = trim($sheet->getCellByColumnAndRow(0,$r)->getCalculatedValue());
								if($cod_atg=="")
									continue;
								
								$qta = trim($sheet->getCellByColumnAndRow(1,$r)->getCalculatedValue());
								if($qta=="")
									$qta = 0;
								$costo = trim($sheet->getCellByColumnAndRow(2,$r)->getCalculatedValue());
								if($costo=="")
									$costo = 0;
//								echo "VALORI: $cod_atg - $qta - $costo<br>";

								if($qta==0 && $costo==0)
									continue;

								$fieldsValue = array($cod_atg, $qta, $costo);
								$result = $db->execute($stmtinsert, $fieldsValue);
	
								if(!$result)
									$error = true;
							}

							if($error===true)
								$messageContext->addMessage("ERROR","Errore durante l'importazione.");
							else
								$messageContext->addMessage("SUCCESS","Importazione completata con successo.");
						} 
						else {
							$messageContext->addMessage("ERROR","<p>I titoli non coincidono!!</p>");
						}
					}
					else {
						$messageContext->addMessage("ERROR","<p>I titoli non coincidono!!</p>");
					}
				} // END CHECK EXTENSION
			} // END CHECK FILE SIZE
		}
		else {
			$messageContext->addMessage("ERROR","<p>Errore nel caricamento del file!!</p>");
		} // END CHECK UPLOAD
		
		$actionContext->setForm("LIST");
	}

	$history->addCurrent();
	
	if($actionContext->getForm()=="LIST") {
		// Azione corrente
		$actionContext->setLabel("Inventario oil");
	}

?>