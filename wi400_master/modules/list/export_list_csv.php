<?php

	// Esportazione di una lista in formato CSV
	function exportCSV($export, $idList, $exportTarget){
		global $db, $settings;
/*		
		$exportFormat = $_REQUEST['FORMAT'];
		$exportTarget = $_REQUEST['TARGET'];
		$idList       = $_REQUEST['IDLIST'];
*/			
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
		
		$filename  = date("YmdHis")."_".$wi400List->getIdList().".csv";
		$temp = "export";
		$TypeImage = "csv.png";
		
		$export->setDatiExport($filename, $temp, $TypeImage);
		
	    if (!$handle = fopen(wi400File::getUserFile($temp, $filename), 'w')) {
	         echo "Non si riesce ad aprire il file ($filename)";
	         exit;
	    }
    
		$exportCols = array();
		foreach ($wi400List->getColumnsOrder() as $columnKey) {
			$wi400Column = $wi400List->getCol($columnKey);
			if ($wi400Column != null && $wi400Column->getShow() && $wi400Column->getExportable()){
				$description = $wi400Column->getDescription();
				$description = str_replace(array('<br>','</br>'), " ", $description);
//				$description = prepare_string($description);
				
				$exportCols[] = $description;
			}
		}
    
	    $export->writeCsv($handle, $exportCols);
	    $sql_list = $export->get_query();
    
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
			// Impostazione del limite di righe esportabili in CSV,
			// se viene superato l'esportazione si interrompe
			if(isset($settings['max_export_rows_csv']) && $i>$settings['max_export_rows_csv'])
				return true;
			
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
			
			$exportCols = array();	
			foreach ($wi400List->getColumnsOrder() as $columnKey) {
				/*
				 * Serve annullare il valore di $rowValue ad ogni ciclo perchè altrimenti non lo fa da solo e
				 * nei campi vuoti verrebbe mantenuto il valore del campo precedente
				 */
				$rowValue="";		
				$wi400Column = $wi400List->getCol($columnKey);
				if ($wi400Column != null && $wi400Column->getShow() && $wi400Column->getExportable()) {	
//					echo "<font color='red'>COL:</font> $columnKey<br>";
					
					if (isset($row[$wi400Column->getKey()]) && $row[$wi400Column->getKey()] != ""){
						$rowValue = $row[$wi400Column->getKey()];
						
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
											break;
										}
									}
									$stampa = false;
								}else if (strpos($defaultValue, "EVAL:")===0){
							
									$evalValue = substr($defaultValue,5).";";
							
									eval('$rowValue='.$evalValue);
									//$pdf->cell($len[$iter]*$mult, 4, html_entity_decode($rowValue), 1, 0, "L",$lf);
									
//									echo "1 - VALUE: $rowValue<br>";
									
									$rowFormat = $wi400Column->getFormat('EXPORT_CSV');
									$rowValue = wi400List::applyFormat($rowValue, $rowFormat);
									
									$stampa = false;
								}
								// Per inserire in array colonne proiettate
								if (!isset($row[$columnKey])) {
									$row[$columnKey] = $rowValue;
								}
							}
						}
						
						if($stampa===true) {
							$rowFormat = $wi400Column->getFormat('EXPORT_CSV');
							$rowValue = wi400List::applyFormat($rowValue, $rowFormat);
						}
					} else {
						if ($wi400Column->getDefaultValue() != ""){
							$defaultValue = $wi400Column->getDefaultValue();
							if (is_array($defaultValue)>0){
								$condition = false;
								foreach($defaultValue as $rowCondition){
									$evalValue = substr($rowCondition[0],5).";";
									eval('$condition='.$evalValue.';');
									if ($condition){
										$rowValue = $rowCondition[1];
										break;
									}
								}
							}else if (strpos($defaultValue, "EVAL:")===0){
								
								$evalValue = substr($defaultValue,5).";";
								
								eval('$rowValue='.$evalValue);
								//$pdf->cell($len[$iter]*$mult, 4, html_entity_decode($rowValue), 1, 0, "L",$lf);
								
								$rowValue = prepare_string($rowValue, isHtml($rowValue));
								
//								echo "2 - VALUE: $rowValue<br>";
								
								$rowFormat = $wi400Column->getFormat('EXPORT_CSV');
								$rowValue = wi400List::applyFormat($rowValue, $rowFormat);
							}
							// Per inserire in array colonne proiettate
							if (!isset($row[$columnKey])) {
								$row[$columnKey] = $rowValue;
							}
						}
						if($wi400Column->getDecodeKey()) {
							$rowValue = wi400List::applyDecode($row[$wi400Column->getDecodeKey()], $wi400Column->getDecode());
							//$pdf->cell($len[$iter]*$mult, 4, html_entity_decode($rowValue), 1, 0, "L",$lf);
						}
					}
					$exportCols[] =  $rowValue;
				}
			}		
			
			$export->writeCsv($handle, $exportCols);			
			$i++;		
		}
		fclose($handle);
	}
/*	
	function writeCsv($fp, $content, $deliminator=";") {
		$line = "";
		
		foreach($content as $val) {
			$val = str_replace("\r\n", "\n", html_entity_decode($val));
			$val = '"'.str_replace('"', '""', $val).'"';
			$line .= $val.$deliminator;
		}
		$line = substr($line, 0, (strlen($deliminator) * -1));
		$line .= "\r\n";
		return fwrite($fp, $line);
	}
*/
?>