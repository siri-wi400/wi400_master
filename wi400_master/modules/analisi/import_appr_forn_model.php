<?php

	set_time_limit(0);

	// Estensioni necessarie per il funzionamento delle classi di PHPExcel
	// Example loading an extension based on OS
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
			$stmtdelete = $db->prepare("DELETE", "FAREADIR"); 		
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
				else if(!file_exists($_FILES['IMPORT_FILE']['tmp_name'])) {
					$messageContext->addMessage("ERROR","File non trovato.");
				}
				else {
					$file_name = $_FILES['IMPORT_FILE']['tmp_name'];
					
					$objPHPExcel = PHPExcel_IOFactory::load($file_name);
					
					$sheets = $objPHPExcel->getAllSheets();
//					echo "SHEETS:<pre>"; print_r(array_keys($sheets)); echo "</pre>";
					
					$fields = array(
						"AREBUY" => "",
						"AREDBU" => "",
						"ARECDF" => "",
						"AREDCF" => "",
						"ARECL3" => "",
						"AREDC3" => "",
						"ARECL2" => "",
						"AREDC2" => "",
						"AREANN" => 0,
						"AREMES" => 0,
						"ARENPV" => 0,
						"AREREG" => "",
						"AREPRO" => "",
						"ARECDC" => "",
						"ARENO" => "",
						"ARENOT" => ""
					);
					
					$keys_array = array_keys($fields);
					
//					echo "KEYS ARRAY:<pre>"; print_r($keys_array); echo "</pre>";
					
					// Impostazione della query di inserimento
					$stmt_ins = $db->prepare("INSERT", "FAREADIR", null, $keys_array);
					
					// Impostazione della query per il recupero del codice dei cluster
					$sql_clus = "SELECT mafcde 
						FROM fmafenti o, LATERAL ( 
							SELECT rrn(i) AS NREL                                                    
			                FROM fmafenti i                                 
			                WHERE o.mafcde = i.mafcde and digits(mafava)!!digits(mafmva)!!digits(mafgva) <= ".date("Ymd")." 
			                order by digits(mafava)!!digits(mafmva)!!digits(mafgva) desc      
			                FETCH FIRST ROW ONLY ) AS x                       
						WHERE rrn(o) = x.NREL and mafdse = ?";
				    $stmt_clus = $db->singlePrepare($sql_clus,0,true);
					
					$errors = false;
					
					foreach($sheets as $key => $sheet) {
//						$sheet = $sheets[5];
					
						$max_rows = $sheet->getHighestRow();
//						echo "MAX ROWS: $max_rows<br>";
//						$max_cols = $sheet->getHighestColumn();
						$max_cols = 13;
//						echo "MAX COLS: $max_cols<br>";
						
						for($i=1; $i<=$max_rows; $i++) {
							$title = trim($sheet->getCellByColumnAndRow(0,$i)->getCalculatedValue());
							$title = prepare_string($title);
	
							if($title=="BUYER") {
								$row_titles = $i;
								break;
							}
						}
						
						if($title!=="BUYER") {
							$messageContext->addMessage("ERROR","<p>I titoli non coincidono!!</p>");
						}
						else {
							for($i=$row_titles+1; $i<=$max_rows; $i++) {
								// Buyer
								$buyer = trim($sheet->getCellByColumnAndRow(0,$i)->getCalculatedValue());
								$buyer = prepare_string($buyer);
								if($fields['AREBUY']=="" || ($fields['AREBUY']!=$buyer && $buyer!="")) {
									$fields['AREBUY'] = $buyer;
									
									$des_buyer = trim($sheet->getCellByColumnAndRow(1,$i)->getCalculatedValue());
									$des_buyer = prepare_string($des_buyer);
									
									$fields['AREDBU'] = $des_buyer;
								}
								
								// Fornitore
								$fornitore = trim($sheet->getCellByColumnAndRow(2,$i)->getCalculatedValue());
								$fornitore = prepare_string($fornitore);
								if($fields['ARECDF']=="" || ($fields['ARECDF']!=$fornitore && $fornitore!="")) {
									$fields['ARECDF'] = $fornitore;
									
									$des_forn = trim($sheet->getCellByColumnAndRow(3,$i)->getCalculatedValue());
									$des_forn = prepare_string($des_forn);
								    
									$fields['AREDCF'] = $des_forn;
									
								}
								
								// Cluster livello 3
								$des_cluster_3 = trim($sheet->getCellByColumnAndRow(4,$i)->getCalculatedValue());
								$des_cluster_3 = prepare_string($des_cluster_3);
								if($fields['AREDC3']=="" || ($fields['AREDC3']!=$des_cluster_3 && $des_cluster_3!="")) {
									$fields['AREDC3'] = $des_cluster_3;
									
									$result_clus = $db->execute($stmt_clus,array($des_cluster_3));
									if($cluster = $db->fetch_array($stmt_clus)) {
										if($cluster['MAFCDE']!="")
											$fields['ARECL3'] = $cluster['MAFCDE'];
									}
								}

								// Cluster livello 2
								$des_cluster_2 = trim($sheet->getCellByColumnAndRow(5,$i)->getCalculatedValue());
								$des_cluster_2 = prepare_string($des_cluster_2);
								if($fields['AREDC2']=="" || ($fields['AREDC2']!=$des_cluster_2 && $des_cluster_2!="")) {
									$fields['AREDC2'] = $des_cluster_2;
									
									$result_clus = $db->execute($stmt_clus,array($des_cluster_2));
									if($cluster = $db->fetch_array($stmt_clus)) {
										if($cluster['MAFCDE']!="")
											$fields['ARECL2'] = $cluster['MAFCDE'];
									}
								}
								
								for($j=6; $j<=$max_cols; $j++) {
									$val = trim($sheet->getCellByColumnAndRow($j,$i)->getCalculatedValue());
									$val = prepare_string($val);
									
									$campo = $keys_array[$j+2];
									if(!empty($val))
										$fields[$campo] = $val;
									else if(!in_array($campo,array("AREANN","AREMES","ARENPV")))
										$fields[$campo] = "";
								}
								
//								$fields['ARENOT'] = substr($fields['ARENOT'],0,30);
								
//								echo "FIELDS:<pre>"; print_r($fields); echo "</pre>";
//								continue;
								
								$result_ins = $db->execute($stmt_ins, $fields);
								
								if(!$result_ins) {
									$errors = true;
//									echo "FIELDS:<pre>"; print_r($fields); echo "</pre>";
								}
							}
							
							if($errors===true)
								$messageContext->addMessage("ERROR","Errore durante l'importazione dei dati.");
							else if($errors===false || $messageContext->getSeverity()=="")
								$messageContext->addMessage("SUCCESS","Importazione dei dati avvenuta con successo.");
								
							$actionContext->onError($azione,"LIST");
							$actionContext->onSuccess($azione,"LIST");	
						}
					}
				}
			}
		}
		else {
			$messageContext->addMessage("ERROR","<p>Errore nel caricamento del file!!</p>");
		}
	}
	
	if($actionContext->getForm()!="INSERT")
		$history->addCurrent();
	
	if($actionContext->getForm()=="LIST") {
		// Azione corrente
		$actionContext->setLabel("Approvvigionamenti del fornitore");
	}
	

?>