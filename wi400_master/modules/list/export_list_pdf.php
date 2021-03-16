<?php

	// Esportazione di una lista in formato PDF
//	function exportPDF($export, $idList, $exportTarget, $exportOrientation="L", $preview = false) {
	function exportPDF($export, $idList, $exportTarget, $exportFilters, $exportOrientation="L", $preview = false) {
		//getMicroTimeStep("Inizio");
		global $db, $routine_path, $settings;
/*		
		$exportFormat = $_REQUEST['FORMAT'];
		$exportTarget = $_REQUEST['TARGET'];
		$exportFilters = "";
		if(isset($_REQUEST['FILTERS']) && $_REQUEST['FILTERS']!="")
			$exportFilters = $_REQUEST['FILTERS'];
		$idList = $_REQUEST['IDLIST'];
*/			
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
		
//		$export->setIdDetail($wi400List->getExportDetails());
		
		// Reperisco la tabella di base della lista per SQL
		$lib_table = explode('/',$wi400List->getFrom());
		if (isset($lib_table[1])) {
			$tabella = $lib_table[1];
		    $colonne = getColumnListFromTable($tabella, $settings['db_temp']);
		}
		
		$lunghezzaTotale = 0;
		
		$len = array();
		
		$iter = 0;
		$num_str = 0;
		$h_r = 4;
		$altezza = $h_r+1;
		$h_r = 4.5;
		
		foreach ($wi400List->getColumnsOrder() as $columnKey) {
			$wi400Column = $wi400List->getCol($columnKey);
			if ($wi400Column != null && $wi400Column->getShow() && $wi400Column->getExportable()) {
				$descr = $wi400Column->getDescription();
				
				if (strpos($descr, "<br>")!==False) {
					$stringhe = explode("<br>",$descr);
					$jlung = 0;
					foreach($stringhe as $Dkey=>$Dvalue) {
						$Dvalue = prepare_string($Dvalue);
						if (strlen(trim($Dvalue))>$jlung) {
							$jlung = strlen(trim($Dvalue));
							$descr = $Dvalue;	
						}
					}
					
					$lunghezza = $jlung;
					if(count($stringhe)>$num_str) {
						$num_str = count($stringhe);
						$altezza = ($num_str*$h_r)+$num_str;
					}
				}
				else {
					$descr = prepare_string($descr);
					$lunghezza = strlen(trim($descr));
				}
				
				if (!isset($len[$iter])) 
					$len[$iter]= 0;
				if ($lunghezza > $len[$iter]) 
					$len[$iter]=$lunghezza;
				$iter ++;
				$lunghezzaTotale = $lunghezzaTotale + $lunghezza;
			}
		}
		
		// Calcolo Lughezza massima totali
		if($wi400List->getSubfile() != null) {

			$wi400Subfile = wi400Session::load(wi400Session::$_TYPE_SUBFILE, $wi400List->getSubfile());
			$totalArray = $wi400Subfile->getTotals();
			$extraRowsArray = $wi400Subfile->getExtraRowsExport();
			$TotalListArray = $wi400List->getTotals();
			
			$array_totals = array(
				"SUBTOTAL" => $TotalListArray,
				"TOTAL" => $totalArray 
			);
			
			// Toali e subtotali
			foreach($array_totals as $key => $totals) {
				if($key=="TOTAL" && $exportTarget!="ALL")
					continue;
					
				if(sizeof($totals)>0) {
					$iter = 0;
					foreach ($wi400List->getColumnsOrder() as $columnKey) {
						$wi400Column = $wi400List->getCol($columnKey);
						if ($wi400Column != null && $wi400Column->getShow() && $wi400Column->getExportable()) {						
							if(isset($totals[$columnKey])) {
								$rowValue = $totals[$columnKey];
								$rowValue = wi400List::applyEval($rowValue, $totals, $wi400Subfile->getParameters());
								
								$rowFormat = $wi400Column->getFormat('EXPORT_PDF');
								
								// Cerco un formato alternativo dalla struttura delle colonne sul DB
								if ($rowFormat==""){
									if (isset($colonne[$columnKey])) {
									    $wi400Column = $colonne[$columnKey];
									    $rowFormat = $wi400Column->getFormat();
									}	    
								}
								
								$rowValue = prepare_string($rowValue);
								$rowValue = wi400List::applyFormat($rowValue, $rowFormat);
								
								$lunghezza = strlen(trim($rowValue));							
								if ($lunghezza > $len[$iter]) 
									$len[$iter]=$lunghezza;
							}
							$iter++;
						}
					}
				}
			}
			
			// ExtraRows
			if($exportTarget=="ALL") {
				foreach($extraRowsArray as $key => $extraRow) {
					if(sizeof($extraRow)>0) {
						$iter = 0;
						foreach ($wi400List->getColumnsOrder() as $columnKey) {
							$wi400Column = $wi400List->getCol($columnKey);
							if ($wi400Column != null && $wi400Column->getShow() && $wi400Column->getExportable()) {						
								if(isset($extraRow[$columnKey])) {
									$rowValue = $extraRow[$columnKey];
									$rowFormat = $wi400Column->getFormat('EXPORT_PDF');
									
									if ($rowFormat==""){
										if (isset($colonne[$columnKey])) {
										    $wi400Column = $colonne[$columnKey];
										    $rowFormat = $wi400Column->getFormat();
										}	    
									}
									
									$rowValue = prepare_string($rowValue);
									$rowValue = wi400List::applyFormat($rowValue, $rowFormat);
									
									$lunghezza = strlen(trim($rowValue));							
									if ($lunghezza > $len[$iter]) 
										$len[$iter]=$lunghezza;
								}
								$iter++;
							}
						}
					}
				}
			}
		}
		
		$sql_list = $export->get_query();

		// Primo ciclo per contare la lunghezza massima del testo e di ogni singola colonna
		if($exportTarget == "PAGE")
			$resultSet = $db->query($sql_list, true, $wi400List->getPageRows());
		else
			$resultSet = $db->query($sql_list, False, 0);
			
		if($exportTarget=="PAGE") {
			// Posizionamento su record
			if ($export->getStartFrom()>0) 
				$db->fetch_array($resultSet,$export->getStartFrom());
		}
		else if($exportTarget=="SELECTED") {
			$rowsSelectionArray = $wi400List->getSelectionArray();
		}
		
		$i=0;
		while($row = $db->fetch_array($resultSet)) {
			if ($exportTarget == "PAGE" && $i == $wi400List->getPageRows())
				break;
			else if ($exportTarget == "SELECTED") {
				if ($i < count($rowsSelectionArray)) {
					$keysRow = "";
					$keyValue = "";
					$isFirst = true;
					foreach ($wi400List->getKeys() as $key => $keyColumn) {
						if (isset($row[$key]))
							$keyValue = $row[$key];
						else
							$keyValue = $wi400Column->getValue();
						
						$keyValue = wi400List::applyFormat($keyValue, $keyColumn->getFormat());
							
						if (!$isFirst)
							$keysRow = $keysRow."|".$keyValue;
						else{
							$isFirst = false;
							$keysRow = $keysRow."".$keyValue;
						}
					}
					$keysRow = trim($keysRow);
					$isSelected = false;
					if (isset($rowsSelectionArray[$keysRow]))
						$isSelected = true;
					
					if($isSelected == false)
						continue;
				}
				else
					break;
			}
//			echo "<b>COL ORD:"; print_r($wi400List->getColumnsOrder()); echo "</b><br>";				
			$iter = 0;
			$lunghezzaParziale = 0;	
			foreach ($wi400List->getColumnsOrder() as $columnKey) {	
//				echo "<b>COL KEY:</b> $columnKey - ITER: $iter<br>";		
				$wi400Column = $wi400List->getCol($columnKey);
				if ($wi400Column != null && $wi400Column->getShow() && $wi400Column->getExportable()) {
					if (isset($row[$columnKey])) {
						$rowValue = $row[$columnKey];
						
						$stampa = true;
						
						if(is_numeric($rowValue)) {
							if ($wi400Column->getDefaultValue() != ""){
								$defaultValue = $wi400Column->getDefaultValue();
								if (is_array($defaultValue)>0){
									$condition = false;
									foreach($defaultValue as $rowCondition){
										$evalValue = substr($rowCondition[0],5).";";
										eval('$condition='.$evalValue.';');
										if ($condition){
											$rowValue = $rowCondition[1];
											$rowValue = prepare_string($rowCondition[1]);
							
											$lunghezza = strlen(trim($rowValue));
											if (!isset($len[$iter]))
												$len[$iter]= 0;
											if ($lunghezza > $len[$iter])
												$len[$iter]=$lunghezza;
										}
									}
									$stampa = false;
								}
								else if (strpos($defaultValue, "EVAL:")===0){
									$evalValue = substr($defaultValue,5).";";
									eval('$rowValue='.$evalValue);
//									$rowValue = prepare_string($rowValue);
							
									$rowFormat = $wi400Column->getFormat('EXPORT_PDF');
									// Cerco un formato alternativo dalla struttura delle colonne sul DB
									if ($rowFormat==""){
										if (isset($colonne[$columnKey])) {
											$wi400Column = $colonne[$columnKey];
											$rowFormat = $wi400Column->getFormat();
										}
									}
							
									$rowValue = wi400List::applyFormat($rowValue, $rowFormat);
									$rowValue = prepare_string($rowValue);
							
									$lunghezza = strlen(trim($rowValue));
									if (!isset($len[$iter]))
										$len[$iter]= 0;
									if ($lunghezza > $len[$iter])
										$len[$iter]=$lunghezza;
//									echo "1 - VALUE: $rowValue - LUNGHEZZA: $lunghezza<br>";
									$stampa = false;
								}
								// Per inserire in array colonne proiettate
								if (!isset($row[$columnKey])) {
									$row[$columnKey] = $rowValue;
								}
							}
						}
						
						if($stampa===true) {
							$rowFormat = $wi400Column->getFormat('EXPORT_PDF');
							// Cerco un formato alternativo dalla struttura delle colonne sul DB
							if ($rowFormat==""){
								if (isset($colonne[$columnKey])) {
								    $wi400Column = $colonne[$columnKey];
								    $rowFormat = $wi400Column->getFormat();
								}	    
							}
							
							$rowValue = prepare_string($rowValue);
							$rowValue = wi400List::applyFormat($rowValue, $rowFormat);
							$lunghezza = strlen(trim($rowValue));
//							echo "VAL: $rowValue - LEN: $lunghezza<br>";
							if (!isset($len[$iter])) 
								$len[$iter]= 0;
							if ($lunghezza > $len[$iter]) {
								/*if(is_numeric($row[$columnKey])) {
									if($lunghezza>8)
										$lunghezza += (($lunghezza-8)*3);
								}*/
								$len[$iter]=$lunghezza;
							}
						}
					} 
					else {
						if ($wi400Column->getDefaultValue() != ""){
							$defaultValue = $wi400Column->getDefaultValue();
							if (is_array($defaultValue)>0){
								$condition = false;
								foreach($defaultValue as $rowCondition){
									$evalValue = substr($rowCondition[0],5).";";
									eval('$condition='.$evalValue.';');
									if ($condition){
										$rowValue = $rowCondition[1];
										$rowValue = prepare_string($rowCondition[1]);
										
										$lunghezza = strlen(trim($rowValue));
										if (!isset($len[$iter])) 
											$len[$iter]= 0;
										if ($lunghezza > $len[$iter])
											$len[$iter]=$lunghezza;									
									}
								}
							}
							else if (strpos($defaultValue, "EVAL:")===0){
								$evalValue = substr($defaultValue,5).";";
								eval('$rowValue='.$evalValue);
//								$rowValue = prepare_string($rowValue);
								
								$rowFormat = $wi400Column->getFormat('EXPORT_PDF');
								// Cerco un formato alternativo dalla struttura delle colonne sul DB
								if ($rowFormat==""){
									if (isset($colonne[$columnKey])) {
									    $wi400Column = $colonne[$columnKey];
									    $rowFormat = $wi400Column->getFormat();
									}	    
								}
								
								$rowValue = wi400List::applyFormat($rowValue, $rowFormat);
//								$rowValue = prepare_string($rowValue);
								$rowValue = prepare_string($rowValue, isHtml($rowValue));
								
								$lunghezza = strlen(trim($rowValue));
								if (!isset($len[$iter])) 
									$len[$iter]= 0;
								if ($lunghezza > $len[$iter])
									$len[$iter]=$lunghezza;	
//								echo "2 - VALUE: $rowValue - LUNGHEZZA: $lunghezza<br>";
							}
							// Per inserire in array colonne proiettate
							if (!isset($row[$columnKey])) {
								$row[$columnKey] = $rowValue;
							}
						}
						if($wi400Column->getDecodeKey()) {
							$rowValue = wi400List::applyDecode($row[$wi400Column->getDecodeKey()], $wi400Column->getDecode());
							$lunghezza = strlen(trim($rowValue));
							if (!isset($len[$iter])) 
								$len[$iter]= 0;
							if ($lunghezza > $len[$iter])
								$len[$iter]=$lunghezza;	
						}
						
					}
					$iter ++;
					$lunghezzaParziale = $lunghezzaParziale + $lunghezza;
				}
			}
			if ($lunghezzaParziale>$lunghezzaTotale) 
				$lunghezzaTotale = $lunghezzaParziale;
			$i++;
		}	
		
		// Cerco di capire quanto grande fare il carattere	
    	if ($lunghezzaTotale<80) {
    		$char = 12;
    		$mult = 2.9;
    		$pagina = 230;
    		$h_r = 5.5;
    	} else if ($lunghezzaTotale<100) {
    		$char = 10;
    		$mult = 2.7;
    		$pagina = 230;
    		$h_r = 5.5;
    	} else if ($lunghezzaTotale<130) {
    		$char = 9;
    		$mult = 2.3;
    		$pagina = 230;
    		$h_r = 5.5;
    	} else if ($lunghezzaTotale<160) {
    		$char = 8;
    		$mult = 2;
    		$pagina = 230;
    		$h_r = 4.5;
		} else if ($lunghezzaTotale<190) {
    		$char = 6;
    		$mult = 1.8;
    		$pagina = 230;
    		$h_r = 4.5;
    	} else {
    		$char = 4;
    		$mult = 1.5;
    		$pagina = 230;
    		$h_r = 4.5;
		}
		
		$export->impostaDatiPDF();
		
		$pdf = $export->createPDF($char, $pagina, $exportOrientation);
		// Performance TEST
		$pdf->setFontSubsetting(false);
		
		$subject = "Esportazione dati".$wi400List->getIdList();
		
		$export->dettagliPDF($pdf, $subject);
		
		$first_page = false;
		if($exportFilters===true) {
			$export->stampaPrimaPaginaPDF($pdf, $char, $pagina);
			
		}
		else {
			
			$pag_or = $export->getExportOrientation();
			
			$pdf->SetFont('Courier', '' ,$char);
			$pdf->AddPage($pag_or);
			$pdf->SetFillColor(198,226,255);
			
			$export->oldPage = $pdf->PageNo();
//			$export->printNewPagePdf($pdf, $pagina, $char);

			$first_page = true;
		}
		
//		echo "PAG: ".$pdf->PageNo()." - OLD PAGE:".$export->oldPage." - FIRST PAGE: $first_page<br>";
		
		$lf = 0;
		$ciclo = 0;
	
		// Ciclo per tutte le righe
		if($exportTarget == "PAGE")
			$resultSet = $db->query($sql_list, true, $wi400List->getPageRows());
		else
			$resultSet = $db->query($sql_list, False, 0);
			
		if($exportTarget=="PAGE") {
			// Posizionamento su record
			if ($export->getStartFrom()>0) 
				$db->fetch_array($resultSet,$export->getStartFrom());
		}
		else if($exportTarget=="SELECTED") {
			$rowsSelectionArray = $wi400List->getSelectionArray();
		}
		
		// Posizionamento su record
		if ($export->getStartFrom()>0) 
			$db->fetch_array($resultSet,$export->getStartFrom());
			
		$col_groups = $wi400List->getColsGroups();
	
		if(!empty($col_groups)) {
			$num_str_gr = 1;
			$h_gr = 4;
			$group_des_array = $wi400List->getGroupDescriptions();
			
			foreach($group_des_array as $des_gr) {
				if (strpos($des_gr, "<br>")!==False) {
					$stringhe = explode("<br>",$des_gr);
					if(count($stringhe)>$num_str_gr) {
						$num_str_gr = count($stringhe);
						$h_gr = ($num_str_gr*$h_gr)+$num_str_gr;
//						echo "NUM RIGHE GRP: $num_str_gr<br>";
					}
				}
			}
		}
		
		while($row = $db->fetch_array($resultSet)) {
			if ($exportTarget == "PAGE" && $ciclo == $wi400List->getPageRows())
				break;
			else if ($exportTarget == "SELECTED") {
				if ($ciclo < count($rowsSelectionArray)) {
					$keysRow = "";
					$keyValue = "";
					$isFirst = true;
					foreach ($wi400List->getKeys() as $key => $keyColumn) {
						if (isset($row[$key]))
							$keyValue = $row[$key];
						else
							$keyValue = $wi400Column->getValue();
						
						$keyValue = wi400List::applyFormat($keyValue, $keyColumn->getFormat());
							
						if (!$isFirst)
							$keysRow = $keysRow."|".$keyValue;
						else{
							$isFirst = false;
							$keysRow = $keysRow."".$keyValue;
						}
					}
					$keysRow = trim($keysRow);
					$isSelected = false;
					if (isset($rowsSelectionArray[$keysRow]))
						$isSelected = true;
					
					if($isSelected == false)
						continue;
				}
				else
					break;
			}

//			if (($pdf->PageNo()) > $export->oldPage) {
			if (($pdf->PageNo()) > $export->oldPage || $first_page===true) {
				$first_page = false;
				
				$export->oldPage = $pdf->PageNo();
				$export->printNewPagePdf($pdf, $pagina, $char);
				/*
				 * SIRI: Accorgimento per evitare che la prima riga della lista delle pagine soggette 
				 * a page break venga stampata troppo in alto rispetto a quella della prima pagina 
				 */
				if($ciclo == 0) {
					$riga = $export->getStart();
//					echo "PRIMA PAGINA<br>";
				}
				else {
//					$riga = ($export->getStart()+$h_r)+.3*4;
					$riga = ($export->getStart()+$h_r);
//					echo "PAGE: ".$pdf->PageNo()."<br>";
				}
			}
//	    	echo "<font color='blue'>$riga</font></br>";
			$pdf->SetXY($export->getColonna(), $riga);	    	
 
        	if($ciclo == 0) {
        		$pdf->SetFont('Courier', 'B' ,$char);
        		
//        		$col_groups = $wi400List->getColsGroups();

        		if(!empty($col_groups)) {
        			$riga_groups = $riga;
//        			$riga_titles = ($riga_groups+$h_gr)+.3*4;
        			$riga_titles = ($riga_groups+$h_gr);
					$pdf->SetXY($export->getColonna() ,$riga_titles);
        		}
        		else
        			$riga_titles = $riga;
        		
        		$iter = 0;
        		$isFirst = true;
        		$grp = "";
	        	foreach ($wi400List->getColumnsOrder() as $columnKey) {		
					$wi400Column = $wi400List->getCol($columnKey);
					if ($wi400Column != null && $wi400Column->getShow() && $wi400Column->getExportable()) {
						if(!empty($col_groups)) {			
		        			$group = $wi400Column->getGroup();
		        			
							if($isFirst===false) {
								if($group=="" || $group!=$grp) {
									$isFirst = true;
									$x_fin = $pdf->GetX();
									$pdf->SetXY($x_ini , $riga_groups);
									$des_grp = $wi400List->getGroupDescription($grp);
//									echo "DES: $des_grp<br>";
									if (strpos($des_grp, "<br>")!==False) {
										$stringhe = explode("<br>",$des_grp);
										
										$x = $pdf->getX();
										$y = $pdf->getY();
										
										$y1 = $y + 1;
										foreach($stringhe as $Dkey=>$Dvalue) {
											$pdf->SetXY($x, $y1);
//											echo "VAL: $Dvalue - X:$x - Y:$y1<br>";
											$Dvalue = prepare_string($Dvalue);
											
											$pdf->Write($h_gr, $Dvalue);
											
											$y1 += $h_gr;
										}
										
										$pdf->SetXY($x, $y);
										
										$pdf->cell(($x_fin-$x_ini), $h_gr, "", 1, 0, "L",$lf);
										$pdf->SetXY($x_fin ,$riga_titles);
									}
									else {
										$pdf->cell(($x_fin-$x_ini), $h_gr, $des_grp, 1, 0, "L",$lf);
										$pdf->SetXY($x_fin ,$riga_titles);
									}
								}
							}
		        			
							if($group!="" && $isFirst===true) {
								$isFirst = false;
								$x_ini = $pdf->GetX();
								$grp = $group;
							}
		        		}
						
						$descr = $wi400Column->getDescription();
						
						if (strpos($descr, "<br>")!==False) {
							$stringhe = explode("<br>",$descr);
							
							$x = $pdf->getX();
							$y = $pdf->getY();
							
//							$pdf->cell($len[$iter]*$mult, $altezza, "", 1, 0, "R",$lf);
							
							$y1 = $y + 1;
							foreach($stringhe as $Dkey=>$Dvalue) {
								$pdf->SetXY($x, $y1);
								
								$Dvalue = prepare_string($Dvalue);
								
								$pdf->Write($h_r, $Dvalue);
								
								$y1 += $h_r;
							}
							
							$pdf->SetXY($x, $y);
							
							$pdf->cell($len[$iter]*$mult, $altezza, "", 1, 0, "R",$lf);
						}
						else {
							$descr = prepare_string($descr);
							
							if(is_numeric($descr))
								$pdf->cell($len[$iter]*$mult, $altezza, $descr, 1, 0, "R",$lf);
							else
								$pdf->cell($len[$iter]*$mult, $altezza, $descr, 1, 0, "L",$lf);
						}
/*						
						$descr = prepare_string($descr);
						if(is_numeric($descr))
							$pdf->Multicell($len[$iter]*$mult, $altezza, $descr, 1, 0, "R",$lf);
						else
							$pdf->Multicell($len[$iter]*$mult, $altezza, $descr, 1, 0, "L",$lf);
*/						
						$iter++;
					}
				}
				
				if(!empty($col_groups)) {
					if($isFirst===false) {						
						$x_fin = $pdf->GetX();
						$pdf->SetXY($x_ini , $riga_groups);
						$des_grp = $wi400List->getGroupDescription($grp);
						
						if (strpos($des_grp, "<br>")!==False) {
							$stringhe = explode("<br>",$des_grp);
							
							$x = $pdf->getX();
							$y = $pdf->getY();
							
							$y1 = $y + 1;
							foreach($stringhe as $Dkey=>$Dvalue) {
								$pdf->SetXY($x, $y1);
								
								$Dvalue = prepare_string($Dvalue);
								
								$pdf->Write($h_gr, $Dvalue);
								
								$y1 += $h_gr;
							}
							
							$pdf->SetXY($x, $y);
							
							$pdf->cell(($x_fin-$x_ini), $h_gr, "", 1, 0, "L",$lf);
							$pdf->SetXY($x_fin ,$riga_titles);
						}
						else {
							$pdf->cell(($x_fin-$x_ini), $h_gr, $des_grp, 1, 0, "L",$lf);
							$pdf->SetXY($x_fin ,$riga_titles);
						}
					}
				}
				
//				$riga = ($export->getStart()+4)+.3*4;
//				$riga = ($riga_titles+$altezza)+.3*4;
				$riga = ($riga_titles+$altezza);
				$pdf->SetXY($export->getColonna() ,$riga);
				$pdf->SetFont('Courier', '' ,$char);
//				echo "<font color='green'>TITLE RIGA:</font> $riga<br>";
        	}
        	else {
 //       		echo "<font color='pink'>$riga</font><br>";
        	}	
	
        	$iter = 0;
			foreach ($wi400List->getColumnsOrder() as $columnKey) {			
				$wi400Column = $wi400List->getCol($columnKey);
				if ($wi400Column != null && $wi400Column->getShow() && $wi400Column->getExportable()) {
//					if (isset($row[$columnKey])) {
					if(isset($row[$columnKey]) && !empty($row[$columnKey])) {
						$rowValue = $row[$columnKey];

						$stampa = true;
						
						if(is_numeric($rowValue)) {
								if ($wi400Column->getDefaultValue() != ""){
								$defaultValue = $wi400Column->getDefaultValue();
								if (is_array($defaultValue)>0){
									$condition = false;
									foreach($defaultValue as $rowCondition){
										$evalValue = substr($rowCondition[0],5).";";
										eval('$condition='.$evalValue.';');
//										echo "<font color='green'>".'$condition='.$evalValue.' -> '.$condition.'</font><br>';
										if ($condition){
											$rowValue = prepare_string($rowCondition[1]);
//											echo "<font color='blue'>DEF VALUE: $rowValue</font> - ";
											$pdf->cell($len[$iter]*$mult, $h_r, $rowValue, 1, 0, "L",$lf);
											$stampa = false;
											break;
										}
									}
/*							
									if(!$condition) {
//										echo "NO CONDITION - ";
										$pdf->cell($len[$iter]*$mult, $h_r, '', 1, 0, "L",$lf);
									}
*/
//									echo "DEFAULT - ARRAY: $rowValue<br>";
								}
								else if (strpos($defaultValue, "EVAL:")===0){
									$evalValue = substr($defaultValue,5).";";
									eval('$rowValue='.$evalValue);
//									$rowValue = prepare_string($rowValue);
//									echo "EVAL: $rowValue<br>";
//									$pdf->cell($len[$iter]*$mult, 4, $rowValue, 1, 0, "L",$lf);
							
									$rowFormat = $wi400Column->getFormat('EXPORT_PDF');
									// Cerco un formato alternativo dalla struttura delle colonne sul DB
									if ($rowFormat==""){
										if (isset($colonne[$columnKey])) {
											$wi400Column = $colonne[$columnKey];
											$rowFormat = $wi400Column->getFormat();
										}
									}
							
									$rowValue = wi400List::applyFormat($rowValue, $rowFormat);
									$rowValue = prepare_string($rowValue);
									
//									echo "3 - VALUE: $rowValue<br>";
									
									if($rowFormat != ""){
										$pdf->cell($len[$iter]*$mult, $h_r, $rowValue, 1, 0, "R",$lf);
									} else {
										$pdf->cell($len[$iter]*$mult, $h_r, $rowValue, 1, 0, "L",$lf);
									}
									$stampa = false;
								}
/*								
								else {
//									echo "DEFAULT<br>";
									$pdf->cell($len[$iter]*$mult, $h_r, '', 1, 0, "L",$lf);
								}
*/								
							}
/*							
							else {
//								echo "NO DEF VALUE<br>";
								$pdf->cell($len[$iter]*$mult, $h_r, '', 1, 0, "L",$lf);
							}
*/							
						}
						
						if($stampa===true) {
							$rowFormat = $wi400Column->getFormat('EXPORT_PDF');
							// Cerco un formato alternativo dalla struttura delle colonne sul DB
							if ($rowFormat==""){
								if (isset($colonne[$columnKey])) {
									$wi400Column = $colonne[$columnKey];
									$rowFormat = $wi400Column->getFormat();
								}
							}
							
							$rowValue = wi400List::applyFormat($rowValue, $rowFormat);
							$rowValue = prepare_string($rowValue);
//							echo 'isset($row[$columnKey]): '.$rowValue.'<br>';
							
							if($rowFormat != ""){
								$pdf->cell($len[$iter]*$mult, $h_r, $rowValue, 1, 0, "R",$lf);
							} else {
								$pdf->cell($len[$iter]*$mult, $h_r, $rowValue, 1, 0, "L",$lf);
							}
						}
						
//						if($rowValue=="0101246")
//							echo "<font color='red'>0101246</font><br>";
//						else
//							echo "$rowValue<br>";
					} else {
						if ($wi400Column->getDefaultValue() != ""){
							$defaultValue = $wi400Column->getDefaultValue();
							if (is_array($defaultValue)>0){
								$condition = false;
								foreach($defaultValue as $rowCondition){
									$evalValue = substr($rowCondition[0],5).";";
									eval('$condition='.$evalValue.';');
//									echo "<font color='green'>".'$condition='.$evalValue.' -> '.$condition.'</font><br>';
									if ($condition){
										$rowValue = prepare_string($rowCondition[1]);
//										echo "<font color='blue'>DEF VALUE: $rowValue</font> - ";
										$pdf->cell($len[$iter]*$mult, $h_r, $rowValue, 1, 0, "L",$lf);
										break;
									}
								}
								
								if(!$condition) {
//									echo "NO CONDITION - ";
									$pdf->cell($len[$iter]*$mult, $h_r, '', 1, 0, "L",$lf);
								}
								
//								echo "DEFAULT - ARRAY: $rowValue<br>";
							} 
							else if (strpos($defaultValue, "EVAL:")===0){
								$evalValue = substr($defaultValue,5).";";
								eval('$rowValue='.$evalValue);
//								$rowValue = prepare_string($rowValue);
//								echo "EVAL: $rowValue<br>";
//								$pdf->cell($len[$iter]*$mult, 4, $rowValue, 1, 0, "L",$lf);
								
								$rowFormat = $wi400Column->getFormat('EXPORT_PDF');
								// Cerco un formato alternativo dalla struttura delle colonne sul DB
								if ($rowFormat==""){
									if (isset($colonne[$columnKey])) {
									    $wi400Column = $colonne[$columnKey];
									    $rowFormat = $wi400Column->getFormat();
									}	    
								}
								
								$rowValue = wi400List::applyFormat($rowValue, $rowFormat);
//								$rowValue = prepare_string($rowValue);
								$rowValue = prepare_string($rowValue, isHtml($rowValue));
								
//								echo "4 - VALUE: $rowValue<br>";
								
								if($rowFormat != ""){
									$pdf->cell($len[$iter]*$mult, $h_r, $rowValue, 1, 0, "R",$lf);
								} else {
									$pdf->cell($len[$iter]*$mult, $h_r, $rowValue, 1, 0, "L",$lf);
								}
							}
							else {
//								echo "DEFAULT<br>";
								$pdf->cell($len[$iter]*$mult, $h_r, '', 1, 0, "L",$lf);
							}
							// Per inserire in array colonne proiettate
							if (!isset($row[$columnKey])) {
								$row[$columnKey] = $rowValue;
							}
						}
						else { 
//							echo "NO DEF VALUE<br>";
							$pdf->cell($len[$iter]*$mult, $h_r, '', 1, 0, "L",$lf);
						}
						
						if($wi400Column->getDecodeKey()) {
							$rowValue = wi400List::applyDecode($row[$wi400Column->getDecodeKey()], $wi400Column->getDecode());
							$rowValue = prepare_string($rowValue);
//							echo "DECODE KEY: $rowValue<br>";
						    $pdf->cell($len[$iter]*$mult, $h_r, $rowValue, 1, 0, "L",$lf);
						} 
//						else {
//							echo "<font color='red'>NO DECODE</font><br>";
//							$pdf->cell($len[$iter]*$mult, 4, "P", 1, 0, "L",$lf);
//						}
					}
					$iter++;
				}
			}
		
			if ($lf==0) 
				$lf=1; 
			else 
				$lf=0; 
            
//			$riga = $riga+5;
			$riga += $h_r;		
//			echo "<font color='red'>$riga</font></br>";
            $ciclo++;
		}
		
		// Totali, subtotali ed ExtraRows
		if($wi400List->getSubfile() != null) {
			$wi400Subfile = wi400Session::load(wi400Session::$_TYPE_SUBFILE, $wi400List->getSubfile());
			$totalArray = $wi400Subfile->getTotals();
			$isExport = false;
			$extraRowsArray = $wi400Subfile->getExtraRowsExport();
			if(count($extraRowsArray)>0)
				$isExport = true;
			$TotalListArray = $wi400List->getTotals();
			
			$array_totals = array(
				"SUBTOTAL" => $TotalListArray,
				"TOTAL" => $totalArray 
			);
			
			// Toali e subtotali
			foreach($array_totals as $key => $totals) {
				if($key=="TOTAL" && $exportTarget!="ALL")
					continue;
					
				if (($pdf->PageNo()) > $export->oldPage) {
					$export->oldPage = $pdf->PageNo();
					$export->printNewPagePdf($pdf, $pagina, $char);
					$riga = $export->getStart()+5;
					$pdf->SetTopMargin($riga);
//					echo "<font color='blue'>PAG: ".$pdf->PageNo()." - Y: $riga</font><br>";
				}
				
				if(sizeof($totals)>0) {
					$iter = 0;
					$pdf->SetFont('Courier', 'B' ,$char);
					$pdf->SetXY($export->getColonna(),$riga);
					foreach ($wi400List->getColumnsOrder() as $columnKey) {
						$wi400Column = $wi400List->getCol($columnKey);
						if ($wi400Column != null && $wi400Column->getShow() && $wi400Column->getExportable()) {						
							if(isset($totals[$columnKey])) {
								$rowValue = $totals[$columnKey];
								
								$rowValue = wi400List::applyEval($rowValue, $totals, $wi400Subfile->getParameters());
								$rowValue = wi400List::applyFormat($rowValue,$wi400Column->getFormat('EXPORT_PDF'));
								$rowValue = prepare_string($rowValue);
								
								if($wi400Column->getFormat('EXPORT_PDF') != ""){
									$pdf->cell($len[$iter]*$mult, $h_r, $rowValue, 1, 0, "R",$lf);
								} else {
									$pdf->cell($len[$iter]*$mult, $h_r, $rowValue, 1, 0, "L",$lf);
								}
							} else {
								$pdf->cell($len[$iter]*$mult, $h_r, '', 1, 0, "L",$lf);
							}
								
							$iter++;
						}
					}
//					$riga = $riga+5;
					$riga += $h_r;
				}
			}
			
			if($exportTarget=="ALL") {
				// ExtraRows
				foreach($extraRowsArray as $key => $extraRow) {
					if (($pdf->PageNo()) > $export->oldPage) {
						$export->oldPage = $pdf->PageNo();
						$export->printNewPagePdf($pdf, $pagina, $char);
						$riga = $export->getStart()+5;
						$pdf->SetTopMargin($riga);
//						echo "<font color='green'>PAG: ".$pdf->PageNo()." - Y: $riga</font><br>";
					}
					
					if(sizeof($extraRow)>0) {
						$iter = 0;
						$pdf->SetFont('Courier', 'B' ,$char);
						$pdf->SetXY($export->getColonna(),$riga);
						foreach ($wi400List->getColumnsOrder() as $columnKey) {
							$wi400Column = $wi400List->getCol($columnKey);
							if ($wi400Column != null && $wi400Column->getShow() && $wi400Column->getExportable()) {						
								if(isset($extraRow[$columnKey])) {
									$rowValue = prepare_string($extraRow[$columnKey]);
									$rowFormat = $wi400Column->getFormat('EXPORT_PDF');
									
									if($rowFormat != "" && $isExport===false){
										if(is_callable("wi400_format_".$rowFormat,false)) {
											$rowValue = call_user_func("wi400_format_".$rowFormat, $rowValue);
											$pdf->cell($len[$iter]*$mult, $h_r, $rowValue, 1, 0, "R",$lf);
										}
									}
									else if($isExport===true) {
										$pdf->cell($len[$iter]*$mult, $h_r, $rowValue, 1, 0, "R",$lf);
									}
									else {
										$pdf->cell($len[$iter]*$mult, $h_r, $rowValue, 1, 0, "L",$lf);
									}
								}
								else {
									$pdf->cell($len[$iter]*$mult, $h_r, '', 1, 0, "L",$lf);
								}
									
								$iter++;
							}
						}
//						$riga = $riga+5;
						$riga += $h_r;
					}
				}
			}
		}

		/*
		 * Accorgimento per la stampa dell'intestazione delle pagine nel caso in cui l'ultima riga
		 * stampata sia stata stampata a page break (cioè è la prima linea della nuova pagina)
		 */
		if (($pdf->PageNo()) > $export->oldPage) {
			$export->oldPage = $pdf->PageNo();
			// Stampa intestazione della pagina
			$export->printNewPagePdf($pdf, $pagina, $char);
		}
		
		$type_output = 'F';
		if($preview) $type_output = 'I'; 
		// Produco il pdf
		$pdf->Output($export->get_filepath(), $type_output);
		//getMicroTimeStep("Fine");
	}

?>