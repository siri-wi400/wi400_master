<?php

	use PhpOffice\PhpSpreadsheet\Spreadsheet as PHPExcel;
	use PhpOffice\PhpSpreadsheet\IOFactory as PHPExcel_IOFactory;
	
	use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup as PHPExcel_Worksheet_PageSetup;
	
	use PhpOffice\PhpSpreadsheet\Cell\Coordinate as PHPExcel_Cell;
	use PhpOffice\PhpSpreadsheet\Cell\DataType as PHPExcel_Cell_DataType;
	
	use PhpOffice\PhpSpreadsheet\Style\Alignment as PHPExcel_Style_Alignment;
	use PhpOffice\PhpSpreadsheet\Style\Border as PHPExcel_Style_Border;
	use PhpOffice\PhpSpreadsheet\Style\Color as PHPExcel_Style_Color;
	use PhpOffice\PhpSpreadsheet\Style\Fill as PHPExcel_Style_Fill;
	use PhpOffice\PhpSpreadsheet\Style\NumberFormat as PHPExcel_Style_NumberFormat;
	use PhpOffice\PhpSpreadsheet\Style\Protection as PHPExcel_Style_Protection;

	// Esportazione di una lista in formato XLS
	function exportXLS($export, $idList, $exportFormat, $exportTarget, $exportFilters, $exportOrientation="L", $exportFilename="") {
		global $db, $routine_path, $settings;
		
		echo "PHPSPREADSHEET<br>";
		
		require_once $routine_path."/generali/xls_common_PhpSpreadsheet.php";
//		require_once $routine_path.'/vendor/autoload.php';
		
		error_reporting(E_ALL);		
		ini_set("memory_limit","1000M");		
		set_time_limit(0);
/*		
		// Recupero delle selezioni
		$exportFormat = $_REQUEST['FORMAT'];
		$exportTarget = $_REQUEST['TARGET'];
		$idList       = $_REQUEST['IDLIST'];
*/		
//		echo "FORMAT: $exportFormat - TARGET: $exportTarget - LIST: $idList<br>";
			
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
		
		// Impostazione dei dati del file
		if($exportFormat=="excel2007")
			$type = ".xlsx";
		else if($exportFormat=="excel5")
			$type = ".xls";
		
		if ($exportFilename == "")
		  $filename =  date("YmdHis")."_".$idList.$type;
		else 
		    $filename =  $exportFilename."_".date("YmdHis").$type;		
		    
		$temp = "export";
		$TypeImage = "xls.png";
		
//		echo "FILENAME: $filename<br>";
		
		$export->setDatiExport($filename, $temp, $TypeImage);

		// Tipo cahcing
/*		
//		$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
		$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory_serialized;
		$cacheSettings = array('memoryCacheSize' => '32MB');
//		PHPExcel_Settings::setCacheStorageMethod($cacheMethod);
		PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
*/
		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();
		
		// Set file properties
		$objPHPExcel->getProperties()->setCreator($settings['cliente_installazione']);
		$objPHPExcel->getProperties()->setLastModifiedBy($settings['cliente_installazione']);
		$objPHPExcel->getProperties()->setTitle($idList);
		$objPHPExcel->getProperties()->setSubject($idList);
		$objPHPExcel->getProperties()->setDescription($idList);
		$objPHPExcel->getProperties()->setKeywords("office 2007 openxml php");
		$objPHPExcel->getProperties()->setCategory("Test result file");
		
		// Impostazione dei formati delle celle
		$format_header = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
					'wrap' => true
			),
			'font' => array(
				'bold' => true
			),
			'numberformat' => array(
				'code' => PHPExcel_Style_NumberFormat::FORMAT_TEXT
			)
		);
/*		
		$format_normal = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			)
		);	
*/		
		// Primo foglio
		$objPHPExcel->setActiveSheetIndex(0);
		$sheet = $objPHPExcel->getActiveSheet();
		
/*			
		// @todo ESEMPIO DI PROTEZIONE DI UN FILE
		 
		// NOTA: per come è fatta adesso la classe PHPExcel anche per proteggere solo delle determinate celle, 
		// è necessario proteggere prima tutto il file e poi sproteggere le celle che non devono essere protette,
		// ciò però comporta diversi limiti in quanto è tutto dipendente da range fissi
		
		$sheet->getProtection()->setPassword('PHPExcel');		// Protezione dell'intero file rimovibile con password
		$sheet->getProtection()->setSheet(true);				// OBBLIGATORIO
		$sheet->protectCells('A1:B2', 'PHPExcel');				// Protezione di range di celle rimovibile con password
		$sheet->getStyle('C1:D2')->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);	// Rimozione della protezione da range di celle
*/		
		
		$colCount = 1;
		$rowCounter = 1;
			
		$sql_list = $export->get_query();
//		echo "SQL: $sql_list<br>";
		
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
			
		$col = 1;
		
		$range_Header_ini = PHPExcel_Cell::stringFromColumnIndex($colCount).$rowCounter;
		
		$firstFilter = true;
		
		// Stampa dei dettagli della lista
//		echo "FILTER: ".$_REQUEST['FILTERS']."<br>";
//		if(isset($_REQUEST['FILTERS'])&& $_REQUEST['FILTERS']=="FILTERS") {
		if($exportFilters===true) {
			$idDetails = $export->getIdDetails();
		
			if(!empty($idDetails)) {
				foreach($idDetails as $idDetail) {
					$detailFields = wi400Detail::getDetailFields($idDetail);
					
					foreach($detailFields as $idField => $fieldObj){
						$label = $fieldObj->getLabel(); // Etichetta
						$value = $fieldObj->getValue(); // Valore
//						echo "LABEL $label -  VALUE: $value<br>";

						$value = str_replace("<br>", "\r\n", $value);
//						echo "LABEL $label -  VALUE: $value<br>";

						if($firstFilter===true) {
							$sheet->setCellValueByColumnAndRow($col++,$rowCounter,prepare_string(_t('PARAMETRI')));
							$firstFilter = false;
						}
						
						$label = prepare_string($label, true);
						$value = prepare_string($value, true);
//						echo "LABEL $label -  VALUE: $value<br>";
						
//						$sheet->setCellValueByColumnAndRow($col,$rowCounter,$label);
//						$sheet->setCellValueByColumnAndRow($col+1,$rowCounter++,$value);
						
						$sheet->setCellValueExplicitByColumnAndRow($col,$rowCounter,$label,PHPExcel_Cell_DataType::TYPE_STRING);
						$sheet->setCellValueExplicitByColumnAndRow($col+1,$rowCounter++,$value,PHPExcel_Cell_DataType::TYPE_STRING);
					}
				}
			}
		}
		
		$colCount = $col+1;
		$rowHeader = $rowCounter;
		
		$range_Header_fin = PHPExcel_Cell::stringFromColumnIndex($colCount-1).($rowHeader-1);
		
		// Stile dei parametri
		if($firstFilter===false) {
			$sheet->getStyle($range_Header_ini.":".$range_Header_fin)->applyFromArray($format_header);
				
			$r_i = PHPExcel_Cell::stringFromColumnIndex($colCount)."1";
			$r_f = PHPExcel_Cell::stringFromColumnIndex($colCount).($rowHeader-1);
			$sheet->getStyle($r_i.":".$r_f)->applyFromArray($StringFormat);
			$sheet->getStyle($r_i.":".$r_f)->applyFromArray($WrapStyle);
		}
		
		$range_Header_ini = PHPExcel_Cell::stringFromColumnIndex(1).$rowHeader;
		
		$col_groups = $wi400List->getColsGroups();
//		echo "GROUPS:<pre>"; print_r($col_groups); echo "</pre>";
		if(!empty($col_groups))
			$rowHeader++;
		
		// Stampa dei titoli delle colonne della lista
		$grp = "";
		$isFirst = true;
		$hasGroup = false;
		$j = 1;
		foreach ($wi400List->getColumnsOrder() as $columnKey) {
			$wi400Column = $wi400List->getCol($columnKey);
		
			// Stampa delle colonne esportabili o in vista
			if ($wi400Column != null && $wi400Column->getShow() && $wi400Column->getExportable()) {
				// Stampa dei titoli delle colonne della lista
				if(!empty($col_groups)) {
					$group = $wi400Column->getGroup();
//					echo "GROUP: $group<br>";

					if($group!=$grp) {
						$col_fin = $j-1;
						
						$des_grp = $wi400List->getGroupDescription($grp);
						$des_grp = prepare_string($des_grp);
//						echo "DES GROUP: $des_grp<br>";
//						echo "COL INI: $col_ini - COL FIN: $col_fin<br>";
							
						if($isFirst===false) {
//							$sheet->setCellValueByColumnAndRow($col_ini,$rowHeader-1,$des_grp);
							$sheet->setCellValueExplicitByColumnAndRow($col_ini,$rowHeader-1,$des_grp,PHPExcel_Cell_DataType::TYPE_STRING);
							$sheet->mergeCellsByColumnAndRow($col_ini, $rowHeader-1, $col_fin, $rowHeader-1);
						}
						
						$grp = $group;
						$col_ini = $j;
						
						if($group!="") {							
							$hasGroup = true;
							$isFirst = false;
						}
						else {
							$hasGroup = false;
							$isFirst = true;
						}
					}					
				}
		
				$descr = $wi400Column->getDescription();
				$descr = prepare_string($descr);
//				$descr = prepare_string($descr, isHtml($descr));
	
//				$sheet->setCellValueByColumnAndRow($j,$rowHeader,$descr);
				$sheet->setCellValueExplicitByColumnAndRow($j,$rowHeader,$descr,PHPExcel_Cell_DataType::TYPE_STRING);
				
				if($wi400Column->getOrientation()=="vertical"){
//					$sheet->getStyleByColumnAndRow($j,$rowHeader)->applyFromArray($VerticalStyle);
					$sheet->getStyleByColumnAndRow($j,$rowHeader)->getAlignment()->setTextRotation(90);
				}
									
				$j++;
			}
		}
		
		if($hasGroup===true) {
			$col_fin = $j-1;
			
			$des_grp = $wi400List->getGroupDescription($grp);
			$des_grp = prepare_string($des_grp);
//			echo "DES GROUP: $des_grp<br>";
//			echo "COL INI: $col_ini - COL FIN: $col_fin<br>";
			
//			$sheet->setCellValueByColumnAndRow($col_ini,$rowHeader-1,$des_grp);
			$sheet->setCellValueExplicitByColumnAndRow($col_ini,$rowHeader-1,$des_grp,PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->mergeCellsByColumnAndRow($col_ini, $rowHeader-1, $col_fin, $rowHeader-1);
		}
		
		$colCount = $j-1;
		
		// Stampa delle righe
		$i=0;
		while($row = $db->fetch_array($resultSet)) {
			// Impostazione del limite di righe esportabili in XLS/XLSX,
			// se viene superato l'esportazione si interrompe e viene suggerito di rieseguire l'esportazione in formato CSV
			if(isset($settings['max_export_rows_xls']) && $i>$settings['max_export_rows_xls'])
				return true;
			
			if(isset($settings['max_export_cels_xls'])) {
				$tot_celle = $i*$colCount;
//				echo "XLS - TOT CELLE: ".$tot_celle."<br>";
					
				if($tot_celle>0 && $tot_celle>$settings['max_export_cels_xls']) {
//					echo "XLS - CSV EXP<br>";
					return true;
				}
			}
			
			// Interruzione della stampa nei casi di stampa delle pagina corrente o delle righe selezionate
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
			
			$j=1;
			$isFirst = true;
        	$grp = "";
			foreach ($wi400List->getColumnsOrder() as $columnKey) {
//				echo "<font color='red'>COL:</font> $columnKey<br>";
				
				$wi400Column = $wi400List->getCol($columnKey);
//				echo "COL OBJ:<pre>"; print_r($wi400Column); echo "</pre>";
				
				// Stampa delle colonne esportabili o in vista
				if ($wi400Column != null && $wi400Column->getShow() && $wi400Column->getExportable()) {
					$rowFormat = $wi400Column->getFormat('EXPORT_XLS');
//					echo "FORMAT: $rowFormat<br>";
					
					// Stampa dei dati
					if(isset($row[$columnKey]) && !empty($row[$columnKey])) {
						$rowValue = prepare_string($row[$columnKey]);
//						$rowValue = prepare_string($row[$columnKey], isHtml($rowValue));
//						echo "VALUE: $rowValue<br>";
						
						$stampa = true;
						
						if(is_numeric($rowValue)) {
							// Se la cella è vuota stampare il dato di default
//							echo "DEFAULT VALUE:<pre>"; print_r($wi400Column->getDefaultValue()); echo "</pre>";
							if($wi400Column->getDefaultValue()!="") {
								$defaultValue = $wi400Column->getDefaultValue();
								if (is_array($defaultValue)>0){
									// Se il valore di default è un array
									$condition = false;
									foreach($defaultValue as $rowCondition){
										$evalValue = substr($rowCondition[0],5).";";
										eval('$condition='.$evalValue.';');
//										echo "<font color='green'>".'$condition='.$evalValue.' -> '.$condition.'</font><br>';
										if($condition) {
											$rowValue = $rowCondition[1];
//											$rowValue = prepare_string($rowCondition[1], isHtml($rowValue));
//											echo "WRITE_1<br>";
											$sheet->setCellValueByColumnAndRow($j,$rowHeader+$i+1,$rowValue);
											break;
										}
									}
									$stampa = false;
								}
								else if(strpos($defaultValue, "EVAL:")===0) {
//									echo "EVAL:<br>";
									// Se il valore di default è un EVAL
									$evalValue = substr($defaultValue,5).";";
//									echo "EVAL VALUE: $evalValue<br>";
									eval('$rowValue='.$evalValue);
									$rowValue = prepare_string($rowValue);
//									$rowValue = prepare_string($rowValue, isHtml($rowValue));
//									echo "EVAL - ROW VALUE: $rowValue<br>";
//									$sheet->setCellValueByColumnAndRow($j,$rowHeader+$i+1,$rowValue);
							
//									$rowFormat = $wi400Column->getFormat('EXPORT_XLS');
//									if($rowFormat != "") {
									if($rowFormat != "" && $rowFormat!="STRING") {
										if(is_callable("wi400_format_".$rowFormat,false)) {
											$rowValue = call_user_func("wi400_format_".$rowFormat, $rowValue);
											$rowValue = str_replace(".", "", $rowValue);
											$rowValue = str_replace(",", ".", $rowValue);
		
//											echo "WRITE_2<br>";
											$sheet->setCellValueByColumnAndRow($j,$rowHeader+$i+1,$rowValue);
										}
									}
									else {
//										echo "WRITE_3<br>";
										$sheet->setCellValueExplicitByColumnAndRow($j,$rowHeader+$i+1,$rowValue,PHPExcel_Cell_DataType::TYPE_STRING);
										$sheet->getStyleByColumnAndRow($j,$rowHeader+$i+1)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
									}
									$stampa = false;
								}
								// Per inserire in array colonne proiettate
								if (!isset($row[$columnKey])) {
									$row[$columnKey] = $rowValue;
								}
							}
						}
						
						if($stampa===true) {						
//							$rowFormat = $wi400Column->getFormat('EXPORT_XLS');
//							echo 'isset($row[$columnKey]): '.$rowValue.'<br>';
//							if($rowFormat != "") {
							if($rowFormat != "" && $rowFormat!="STRING") {
//								echo "ROW FORMAT: "; print_r($rowFormat); echo "<br>";
								if(is_callable("wi400_format_".$rowFormat,false)) {
									$rowValue = call_user_func("wi400_format_".$rowFormat, $rowValue);
									$rowValue = str_replace(".", "", $rowValue);
									$rowValue = str_replace(",", ".", $rowValue);
									
//									echo "WRITE_4<br>";
									$sheet->setCellValueByColumnAndRow($j,$rowHeader+$i+1,$rowValue);
								}
							}
							else {
//								echo "WRITE_5<br>";
								$sheet->setCellValueExplicitByColumnAndRow($j,$rowHeader+$i+1,$rowValue,PHPExcel_Cell_DataType::TYPE_STRING);
								$sheet->getStyleByColumnAndRow($j,$rowHeader+$i+1)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
							}
						}
					}
					else {
						// Se la cella è vuota stampare il dato di default
						if($wi400Column->getDefaultValue()!="") {
							$defaultValue = $wi400Column->getDefaultValue();
							if (is_array($defaultValue)>0){
								// Se il valore di default è un array
								$condition = false;
								foreach($defaultValue as $rowCondition){
									$evalValue = substr($rowCondition[0],5).";";
									eval('$condition='.$evalValue.';');
//									echo "<font color='green'>".'$condition='.$evalValue.' -> '.$condition.'</font><br>';
									if($condition) {
										$rowValue = $rowCondition[1];
//										$rowValue = prepare_string($rowCondition[1], isHtml($rowValue));
//										echo "WRITE_6<br>";
										$sheet->setCellValueByColumnAndRow($j,$rowHeader+$i+1,$rowValue);
										break;
									}
								}
							}
							else if(strpos($defaultValue, "EVAL:")===0) {
								// Se il valore di default è un EVAL
								$evalValue = substr($defaultValue,5).";";
								eval('$rowValue='.$evalValue);
//								$rowValue = prepare_string($rowValue);
								$rowValue = prepare_string($rowValue, isHtml($rowValue));
//								echo "EVAL: $rowValue<br>";
//								$sheet->setCellValueByColumnAndRow($j,$rowHeader+$i+1,$rowValue);

								$rowFormat = $wi400Column->getFormat('EXPORT_XLS');
//								if($rowFormat != "") {
								if($rowFormat != "" && $rowFormat!="STRING") {
									if(is_callable("wi400_format_".$rowFormat,false)) {
										$rowValue = call_user_func("wi400_format_".$rowFormat, $rowValue);
										$rowValue = str_replace(".", "", $rowValue);
										$rowValue = str_replace(",", ".", $rowValue);
										
//										echo "WRITE_7<br>";
										$sheet->setCellValueByColumnAndRow($j,$rowHeader+$i+1,$rowValue);
									}
								}
								else {
//									echo "WRITE_8<br>";
									$sheet->setCellValueExplicitByColumnAndRow($j,$rowHeader+$i+1,$rowValue,PHPExcel_Cell_DataType::TYPE_STRING);
									$sheet->getStyleByColumnAndRow($j,$rowHeader+$i+1)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
								}
							}
							// Per inserire in array colonne proiettate
							if (!isset($row[$columnKey])) {
								$row[$columnKey] = $rowValue;
							}
						}
						
						if($wi400Column->getDecodeKey()) {
							$rowValue = wi400List::applyDecode($row[$wi400Column->getDecodeKey()], $wi400Column->getDecode());
							$rowValue = prepare_string($rowValue);
//							$rowValue = prepare_string($rowValue, isHtml($rowValue));
//							echo "DECODE KEY: $rowValue<br>";
//							echo "WRITE_9<br>";
							$sheet->setCellValueByColumnAndRow($j,$rowHeader+$i+1,$rowValue);
						}
					}
					
					$j++;
				}
			}
			
			if($isFirst===false) {
				$isFirst = true;
				$col_fin = $j-1;
				$des_grp = $wi400List->getGroupDescription($grp);
				$des_grp = prepare_string($des_grp);
				$sheet->setCellValueByColumnAndRow($col_ini,$rowHeader-1,$des_grp);
				$sheet->mergeCellsByColumnAndRow($col_ini, $rowHeader-1, $col_fin, $rowHeader-1);
			}
			
			$i++;
		}
		
		$rowData = $rowHeader+$i;
		
		$j=1;
		// Totali, subtotali ed ExtraRows
		if($wi400List->getSubfile() != null && $i>0) {
			$wi400Subfile = wi400Session::load(wi400Session::$_TYPE_SUBFILE, $wi400List->getSubfile());
			// Totali del subfile
			$totalArray = $wi400Subfile->getTotals();
			// ExtraRows
			$isExport = false;
			$extraRowsArray = $wi400Subfile->getExtraRowsExport();
			if(count($extraRowsArray)>0)
				$isExport = true;
			// Subtotali della lista
			$TotalListArray = $wi400List->getTotals();
			
			$array_totals = array(
				"SUBTOTAL" => $TotalListArray,
				"TOTAL" => $totalArray 
			);
			
			// Stampa dei toali e dei subtotali
			foreach($array_totals as $key => $totals) {
				// Stampa i totali del subfile solo se viene eseguita la stampa di tutta la lista 
				// (altrimenti stampa solo i subtotali)
				if($key=="TOTAL" && $exportTarget!="ALL")
					continue;
					
				if(count($totals)>0) {
					$i++;
					$j=1;
					foreach ($wi400List->getColumnsOrder() as $columnKey) {
						$wi400Column = $wi400List->getCol($columnKey);
						if ($wi400Column != null && $wi400Column->getShow() && $wi400Column->getExportable()) {
							if(isset($totals[$columnKey])) {
								$rowValue = wi400List::applyEval($totals[$columnKey],$totals, $wi400Subfile->getParameters());
//								$rowValue = prepare_string($rowValue, isHtml($rowValue));
								$rowFormat = $wi400Column->getFormat('EXPORT_XLS');
								if($rowFormat != ""){
									$rowValue = wi400List::applyFormat($rowValue,$rowFormat);
									$rowValue = str_replace(".", "", $rowValue);
									$rowValue = str_replace(",", ".", $rowValue);
									$sheet->setCellValueByColumnAndRow($j,$rowHeader+$i,$rowValue);
								} 
								else {
									$rowValue = prepare_string($rowValue);
//									$rowValue = prepare_string($rowValue, isHtml($rowValue));
									$sheet->setCellValueExplicitByColumnAndRow($j,$rowHeader+$i,$rowValue,PHPExcel_Cell_DataType::TYPE_STRING);
								}
							}
							$j++;
						}
					}
				}
			}
			
			// Stampa delle ExtraRows
			if($exportTarget=="ALL") {
				// ExtraRows
				foreach($extraRowsArray as $key => $extraRow) {
					if(sizeof($extraRow)>0) {
						$i++;
						$j=1;
						foreach ($wi400List->getColumnsOrder() as $columnKey) {
							$wi400Column = $wi400List->getCol($columnKey);
							if ($wi400Column != null && $wi400Column->getShow() && $wi400Column->getExportable()) {
								if(isset($extraRow[$columnKey])) {
									$rowValue = prepare_string($extraRow[$columnKey]);
//									$rowValue = prepare_string($extraRow[$columnKey], isHtml($rowValue));
									$rowFormat = $wi400Column->getFormat('EXPORT_XLS');
									
									if($rowFormat != "" && $isExport===false){
										if(is_callable("wi400_format_".$rowFormat,false)) {
											$rowValue = call_user_func("wi400_format_".$rowFormat, $rowValue);
											$rowValue = str_replace(".", "", $rowValue);
											$rowValue = str_replace(",", ".", $rowValue);
											$sheet->setCellValueByColumnAndRow($j,$rowHeader+$i,$rowValue);
										}
									}
									else {
										$sheet->setCellValueExplicitByColumnAndRow($j,$rowHeader+$i,$rowValue,PHPExcel_Cell_DataType::TYPE_STRING);
									}
								}
								
								$j++;
							}
						}
					}
				}
			}
		} else {
			// Stampa dei soli totali di lista
			$totals = $wi400List->getTotals();
			if (count($totals)>0) {
				$i++;
				foreach ($wi400List->getColumnsOrder() as $columnKey) {
					$wi400Column = $wi400List->getCol($columnKey);
					if ($wi400Column != null && $wi400Column->getShow() && $wi400Column->getExportable()) {
						if(isset($totals[$columnKey])) {
							$rowValue = wi400List::applyEval($totals[$columnKey],$totals);
							$rowFormat = $wi400Column->getFormat('EXPORT_XLS');
							if($rowFormat != ""){
								$rowValue = wi400List::applyFormat($rowValue,$rowFormat);
								$rowValue = str_replace(".", "", $rowValue);
								$rowValue = str_replace(",", ".", $rowValue);
								$sheet->setCellValueByColumnAndRow($j,$rowHeader+$i,$rowValue);
							}
							else {
								$rowValue = prepare_string($rowValue);
								$sheet->setCellValueExplicitByColumnAndRow($j,$rowHeader+$i,$rowValue,PHPExcel_Cell_DataType::TYPE_STRING);
							}
						}
						$j++;
					}
				}
			}
		}
		
		$rowFooter = $rowHeader+$i;
//		echo "ROW HEADER: $rowHeader - DATA: $rowData - FOOTER: $rowFooter<br>";
		
		if($j>$colCount)
			$colCount = $j-1;
			
//		echo "COL: $colCount<br>";

		// Stile dei titoli delle colonne
		$range_Header_fin = PHPExcel_Cell::stringFromColumnIndex($colCount).$rowHeader;
		$sheet->getStyle($range_Header_ini.":".$range_Header_fin)->applyFromArray($format_header);

		// Stile dei totali, subtotali ed ExtraRows
		if($rowFooter>$rowData) {
			$range_Footer_ini = PHPExcel_Cell::stringFromColumnIndex(1).($rowData+1);
			$range_Footer_fin = PHPExcel_Cell::stringFromColumnIndex($colCount).$rowFooter;
			$sheet->getStyle($range_Footer_ini.":".$range_Footer_fin)->applyFromArray($format_header);
		}
		
		// AutoSize delle colonne
		for($j=1; $j<=$colCount; $j++) {					
			$sheet->getColumnDimensionByColumn($j)->setAutoSize(true);
		}
		
		// Set page orientation and size
		if($exportOrientation=="L")
			$sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		else if($exportOrientation=="P")
			$sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
		$sheet->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
		
		// Rename sheet
		$sheet->setTitle('Dati');
		
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);
		
		if($exportFormat=="excel2007") {
			// Save Excel 2007 file
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Xlsx');
			$objWriter->save($export->get_filepath());
		}
		else if($exportFormat=="excel5") {
			// Export to Excel5 (.xls)
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Xls');
			$objWriter->save($export->get_filepath());
		}
		
		$objPHPExcel->disconnectWorksheets();
		unset($objPHPExcel);
		
		return false;
	}

?>