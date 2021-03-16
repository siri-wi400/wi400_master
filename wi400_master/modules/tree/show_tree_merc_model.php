<?php 

	require_once $routine_path."/classi/wi400ExportList.cls.php";
	
	$azione = $actionContext->getAction();
	
	if($actionContext->getForm()=="EXPORT_XLS") {
		$treeMerc = new wi400Tree("TREE_TO");
		$treeMerc->setRootFunction("settori_127");
		$treeMerc->setSelectionLevels(array(false, false, false));
		$treeMerc->setFilterLevels(array("T3.T127CD","T2.T127CD","T1.T127CD"));
		
		// echo "SETTORI<br>";
		
		$parameters = array("TREE" => $treeMerc);
		wi400_tree_settori_127($parameters);
//		showArray($treeMerc);
		
//		echo "FAMIGLIE<br>";
		
		$nodeArray = $treeMerc->getNodeArray();
		$settori_array = $nodeArray['settori_127']['ROOT'];
//		showArray($settori);
		
		foreach($settori_array as $set => $set_nodo) {
			$parameters = array("TREE" => $treeMerc, "NODE" => $set);
			wi400_tree_famiglie_127($parameters);
		}		
//		showArray($treeMerc);
		
//		echo "SOTTOFAMIGLIE<br>";
		
		$nodeArray = $treeMerc->getNodeArray();
		$famiglie_array = $nodeArray['famiglie_127'];
//		showArray($famiglie_array);
		
		foreach($famiglie_array as $set => $famiglie) {
			foreach($famiglie as $fam => $fam_nodo) {
				$parameters = array("TREE" => $treeMerc, "NODE" => $fam);
				wi400_tree_sottofamiglie_127($parameters);
			}
		}		
//		showArray($treeMerc);
		
		$nodeArray = $treeMerc->getNodeArray();
		$sottofamiglie_array = $nodeArray['sottofamiglie_127'];
//		showArray($sottofamiglie_array);

		// Aumentata la dimensione del limite della memoria
		ini_set("memory_limit","1000M");
		set_time_limit(0);
	
		// Impostazione grandezza carattere
		$char = 10;		// dimensione dei caratteri
	
//		$exportType = $_REQUEST['TARGET'];
		$exportType = "excel2007";
	
		// Impostazione dei dati del file
		if($exportType=="excel2007")
			$file_type = ".xlsx";
		else if($exportType=="excel5")
			$file_type = ".xls";
		else if($exportType=="csv")
			$file_type = ".csv";
		
		$actionContext->setLabel("Esportazione Albero Merceologico");
		
		$filename = "Albero_merceologico_".date("YmdHis").$file_type;
			
		$export = new wi400ExportList();
	
		if($exportType!="csv") {
			// Esportazione in file Excel
	
			$temp = "export";
			$TypeImage = "xls.png";
	
			$export->setDatiExport($filename, $temp, $TypeImage);
			
			require_once $routine_path."/generali/xls_common.php";
	
			// Create new PHPExcel object
			$objPHPExcel = new PHPExcel();
	
			// Set file properties
			$objPHPExcel->getProperties()->setCreator($settings['cliente_installazione']);
			$objPHPExcel->getProperties()->setLastModifiedBy($settings['cliente_installazione']);
			$objPHPExcel->getProperties()->setTitle("Esportazione albero merceologico");
			$objPHPExcel->getProperties()->setSubject("Esportazione albero merceologico");
			$objPHPExcel->getProperties()->setDescription("Esportazione albero merceologico");
			$objPHPExcel->getProperties()->setKeywords("office 2007 openxml php");
			$objPHPExcel->getProperties()->setCategory("Test result file");
	
			// Primo foglio
			$objPHPExcel->setActiveSheetIndex(0);
			$sheet = $objPHPExcel->getActiveSheet();
			
			$col = 0;
			$riga = 1;
			
			$col_set = 0;
//			$col_fam = 1;
//			$col_suf = 2;
			$col_fam = 2;
			$col_suf = 4;
			
			$colCount = $col_suf+1;
			
			$sheet->setCellValueByColumnAndRow($col_set, $riga, prepare_string(_t("SETTORE")));
			$sheet->setCellValueByColumnAndRow($col_set+1, $riga, prepare_string(_t("DESCRIZIONE_SETTORE")));
			$sheet->setCellValueByColumnAndRow($col_fam, $riga, prepare_string(_t("FAMIGLIA")));
			$sheet->setCellValueByColumnAndRow($col_fam+1, $riga, prepare_string(_t("DESCRIZIONE_FAMIGLIA")));
			$sheet->setCellValueByColumnAndRow($col_suf, $riga, prepare_string(_t("SOTTOFAMIGLIA")));
			$sheet->setCellValueByColumnAndRow($col_suf+1, $riga, prepare_string(_t("DESCRIZIONE_SOTTOFAMIGLIA")));
			
			setRangeStyle($sheet, 0, 1, $colCount, 1, $CenterStyle, true);
			
			$riga++;
			
			foreach($settori_array as $set => $set_nodo) {
				// SETTORE
				$col = $col_set;
				
				$des_lunga = $set_nodo->getDescription();
				$pos = strpos($des_lunga, "-");
				$des = substr($des_lunga, $pos+2);
				
				$sheet->setCellValueExplicitByColumnAndRow($col++, $riga, prepare_string($set),PHPExcel_Cell_DataType::TYPE_STRING);
				$sheet->setCellValueExplicitByColumnAndRow($col, $riga, prepare_string($des, true),PHPExcel_Cell_DataType::TYPE_STRING);
				
				$riga++;
				
				if(array_key_exists($set, $famiglie_array)) {
					foreach($famiglie_array[$set] as $fam => $fam_nodo) {
						$col = $col_fam;
						
						$des_lunga = $fam_nodo->getDescription();
						$pos = strpos($des_lunga, "-");
						$des = substr($des_lunga, $pos+2);
						
						$sheet->setCellValueExplicitByColumnAndRow($col++, $riga, prepare_string($fam),PHPExcel_Cell_DataType::TYPE_STRING);
						$sheet->setCellValueExplicitByColumnAndRow($col, $riga, prepare_string($des, true),PHPExcel_Cell_DataType::TYPE_STRING);
						
						$riga++;
						
						if(array_key_exists($fam, $sottofamiglie_array)) {
							foreach($sottofamiglie_array[$fam] as $suf => $suf_nodo) {
								$col = $col_suf;
									
								$des_lunga = $suf_nodo->getDescription();
								$pos = strpos($des_lunga, "-");
								$des = substr($des_lunga, $pos+2);
									
								$sheet->setCellValueExplicitByColumnAndRow($col++, $riga, prepare_string($suf),PHPExcel_Cell_DataType::TYPE_STRING);
								$sheet->setCellValueExplicitByColumnAndRow($col, $riga, prepare_string($des, true),PHPExcel_Cell_DataType::TYPE_STRING);
								
								$riga++;
							}
						}
					}
				}
			}
								
			$rowCount = $riga-1;
								
			// Style borders
			setRangeStyle($sheet, 0, 1, $colCount, $rowCount, $BorderStyle);
								
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
//			$sheet->setPrintGridlines(true);
		
			// Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$objPHPExcel->setActiveSheetIndex(0);
		
			if($exportType=="excel2007") {
				// Save Excel 2007 file
				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			}
			else if($exportType=="excel5") {
				// Export to Excel5 (.xls)
				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
			}
		
			$objWriter->save($export->get_filepath());
		}
	}