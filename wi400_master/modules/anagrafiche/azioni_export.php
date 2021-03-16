<?php
/*
 * Esportazione delle azioni in formato Excel (XLS)
*/
function exportXLS($export, $idList, $tabella, $menu_array=null){
	global $db, $routine_path, $settings, $messageContext, $actionContext;
	
//	echo "EXPORT: $idList - TABELLA: $tabella<br>";

	$exportFormat = $_REQUEST['FORMAT'];
	$exportTarget = $_REQUEST['TARGET'];

	// Impostazione dei dati del file
	if($exportFormat=="excel2007")
		$file_type = ".xlsx";
	else if($exportFormat=="excel5")
		$file_type = ".xls";

	$filename =  date("YmdHis")."_".$idList.$file_type;
	$temp = "export";
	$TypeImage = "xls.png";

	$export->setDatiExport($filename, $temp, $TypeImage);

	checkIfZipLoaded();
		
	// Error reporting
	error_reporting(E_ALL);
		
	// PHPExcel
	require_once $routine_path."/excel/PHPExcel.php";
		
	// PHPExcel_IOFactory
	require_once $routine_path.'/excel/PHPExcel/IOFactory.php';
		
	// Create new PHPExcel object
	$objPHPExcel = new PHPExcel();
		
	// Set file properties
	$objPHPExcel->getProperties()->setCreator($settings['cliente_installazione']);
	$objPHPExcel->getProperties()->setLastModifiedBy($settings['cliente_installazione']);
	$objPHPExcel->getProperties()->setTitle("Esportazione Azioni");
	$objPHPExcel->getProperties()->setSubject("Esportazione Azioni");
	$objPHPExcel->getProperties()->setDescription("Esportazione Azioni");
	$objPHPExcel->getProperties()->setKeywords("office 2007 openxml php");
	$objPHPExcel->getProperties()->setCategory("Test result file");
		
	// Impostazione dei formati delle celle
	// Center Style
	$CenterStyle = array(
		'alignment' => array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
		)
	);
		
	// Format Bold Style
	$BoldStyle['font'] = array('bold' => true);
		
	// Border Style
	$BorderStyle = array(
		'borders' => array(
			'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN
			),
		)
	);
		
	$StringFormat = array(
		'numberformat' => array(
			'code' => PHPExcel_Style_NumberFormat::FORMAT_TEXT
		)
	);
		
	// Format Wrap Style
	$WrapStyle['alignment']['wrap'] = true;
		
	$header_style['alignment'] = array_merge($CenterStyle['alignment'], $WrapStyle['alignment']);
	$header_style = array_merge($header_style, $BoldStyle);
//	echo "HEADER STYLE:<pre>"; print_r($header_style); echo "</pre>";
		
	// Primo foglio
	$objPHPExcel->setActiveSheetIndex(0);
	$sheet = $objPHPExcel->getActiveSheet();
		
	$riga = 1;
	$col = 0;

//	$exportCols = $db->columns($tabella, "", True);
//	echo "EXPORT:<pre>"; print_r($exportCols); echo "</pre>";
	$colsData = $db->columns($tabella);
//	echo "COLS DATA:<pre>"; print_r($colsData); echo "</pre>";
	$exportCols = array_keys($colsData);
//	echo "COLUMNS:<pre>"; print_r($exportCols); echo "</pre>";

	if(!isset($menu_array)) {
		$wi400List = getList($idList);

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
			
			if(empty($rowsSelectionArray)) {
				$messageContext->addMessage("ERROR", "Nessun elemento selezionato");
				return false;
			}
		}
	}
	else {
		$sql_list = "select * from $tabella where AZIONE in ('".implode("', '", $menu_array)."')";
		$resultSet = $db->query($sql_list, false, 0);
	}

	$string_cols = array();
	$menu_azi = array();

	$i=0;
	while($row = $db->fetch_array($resultSet)) {
		if(!isset($menu_array)) {
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
			
			if($idList=="MENU_EXP") {
				$menu_azi_array = explode(";", $row['AZIONI']);
				$menu_azi = array_merge($menu_azi, $menu_azi_array);
				$menu_azi[] = $row['MENU'];
			}
		}
			
		$col = 0;

		foreach ($exportCols as $columnKey) {
			// Stampa Titoli
			if($riga==1) {
				$descr = $columnKey;
				// Accorgimento per stampare i codici con zeri davanti (senza che vengano cancellati)
				if(strncmp(prepare_string($descr),"0",1)==0 && $descr!=0) {
					$sheet->setCellValueExplicitByColumnAndRow($col,$riga,prepare_string($descr),PHPExcel_Cell_DataType::TYPE_STRING);
					$sheet->getStyleByColumnAndRow($col, $riga)->applyFromArray($StringFormat);
				}
				else
					$sheet->setCellValueByColumnAndRow($col,$riga,prepare_string($descr));
				
				if(in_array($colsData[$columnKey]['DATA_TYPE_STRING'], array("VARCHAR", "CHAR"))) {
					$string_cols[] = $columnKey;
				}
			}
			
			// Accorgimento per stampare i codici con zeri davanti (senza che vengano cancellati)
			if(in_array($columnKey, $string_cols)) {
				$sheet->setCellValueExplicitByColumnAndRow($col,$riga+1,prepare_string($row[$columnKey]),PHPExcel_Cell_DataType::TYPE_STRING);
			}
			else
				$sheet->setCellValueByColumnAndRow($col,$riga+1,prepare_string($row[$columnKey]));

			$col++;
		}
			
		$riga++;
		$i++;
	}

	$colCount = $col-1;

	setRangeStyle($sheet, 0, 1, $colCount, 1, $header_style, true);

	$rigaCount = $riga;

	if(!empty($string_cols)) {
		foreach($string_cols as $col) {
			setRangeStyle($sheet, $col, 2, $col, $rigaCount, $StringFormat);
		}
	}

	// Style borders
//	setRangeStyle($sheet, 0, 1, $colCount, $rigaCount, $BorderStyle);

	// AutoSize delle colonne
	for($i=0; $i<=$colCount; $i++) {
		$sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
	}

	// Set page orientation and size
	$sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
	$sheet->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

	// Rename sheet
	$sheet->setTitle('Dati');
		
	// Stampa la griglia
//	$sheet->setPrintGridlines(true);
		
	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex(0);
		
	if($exportFormat=="excel2007") {
		// Save Excel 2007 file
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	}
	else if($exportFormat=="excel5") {
		// Export to Excel5 (.xls)
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	}
		
	$objWriter->save($export->get_filepath());
	
	if($idList=="MENU_EXP")
		return $menu_azi;
}

function setRangeStyle($sheet, $col_ini, $row_ini, $col_fin, $row_fin, $style=null, $bold=false) {
	$range_ini = PHPExcel_Cell::stringFromColumnIndex($col_ini).$row_ini;
	$range_fin = PHPExcel_Cell::stringFromColumnIndex($col_fin).$row_fin;

	if(!empty($style))
		$sheet->getStyle($range_ini.":".$range_fin)->applyFromArray($style);

	if($bold===true)
		$sheet->getStyle($range_ini.":".$range_fin)->getFont()->setBold($bold);

	return $sheet;
}