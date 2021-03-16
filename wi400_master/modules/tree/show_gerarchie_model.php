<?php

	require_once 'show_gerarchie_common.php';
	require_once $routine_path."/classi/wi400ExportList.cls.php";

	$azione = $actionContext->getAction();

	$gerarchia_src = wi400Detail::getDetailValue($azione.'_LIBERE_SRC',"GERARCHIA_LIB");
//	echo "GERARCHIA LIBERA: $gerarchia_src<br>";
	$ger_cls_src = wi400Detail::getDetailValue($azione.'_CLASSICHE_SRC',"GERARCHIA_CLS");
//	echo "GERARCHIA CLASSICA: $ger_cls_src<br>";
	
	if($actionContext->getForm()=="EXPORT_LIB_SEL") {
		// Azione corrente
		$actionContext->setLabel("Esportazione albero gerarchia libera ($gerarchia_src)");
	
		$export = new wi400ExportList();
	}
	else if($actionContext->getForm()=="EXPORT_CLS_SEL") {
		// Azione corrente
		$actionContext->setLabel("Esportazione albero gerarchia classica ($gerarchie_classiche[$ger_cls_src])");
	
		$export = new wi400ExportList();
	}
	else if(in_array($actionContext->getForm(), array("EXPORT_LIB", "EXPORT_CLS"))) {
		// Aumentata la dimensione del limite della memoria
		ini_set("memory_limit","1000M");
		set_time_limit(0);
		
		// Impostazione grandezza carattere
		$char = 10;		// dimensione dei caratteri
		
		$exportType = $_REQUEST['TARGET'];
//		$exportType = "excel5";
		
		// Impostazione dei dati del file
		if($exportType=="excel2007")
			$file_type = ".xlsx";
		else if($exportType=="excel5")
			$file_type = ".xls";
		else if($exportType=="csv")
			$file_type = ".csv";
		
		if($actionContext->getForm()=="EXPORT_LIB") {
			$actionContext->setLabel("Esportazione albero gerarchia libera ($gerarchia_src)");
			
			$filename = "Albero_gerarchia_libera_(".$gerarchia_src.")_".date("YmdHis").$file_type;
		}
		else {
			$actionContext->setLabel("Esportazione albero gerarchia classica ($ger_cls_src)");
			
			$filename = "Albero_gerarchia_classica_(Tipo_".$ger_cls_src.")_".date("YmdHis").$file_type;
		}
			
//		$filepath = $path."/".$filename;
			
		$export = new wi400ExportList();
		
		if($exportType!="csv") {
			// Esportazione in file Excel
		
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
			$objPHPExcel->getProperties()->setTitle("Esportazione albero gerarchie");
			$objPHPExcel->getProperties()->setSubject("Esportazione albero gerarchie");
			$objPHPExcel->getProperties()->setDescription("Esportazione albero gerarchie");
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
			
			// Format Wrap Style
			$WrapStyle['alignment']['wrap'] = true;
			
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
			
			// Primo foglio
			$objPHPExcel->setActiveSheetIndex(0);
			$sheet = $objPHPExcel->getActiveSheet();
			
			$riga = 2;
			$col = 0;
			
			if($actionContext->getForm()=="EXPORT_LIB") {
				$sql_ger = "SELECT * 
					FROM fmaagere 
					WHERE maager='$gerarchia_src' and maasta='1'
					order by MAARAM, MAALIV, MAACDE";
//				echo "SQL: $sql_ger<br>";
				
				$result = $db->query($sql_ger, false, 0);
				
				$titles = array();
				
				while($row = $db->fetch_array($result)) {
//					echo "ROW:<pre>"; print_r($row); echo "</pre>";
					
					$codice = $row['MAACDE'];
					$descrizione = get_campo_ente($codice, $_SESSION['data_validita'], "MAFDSE");
					$livello = $row['MAALIV'];
					
					if(!in_array("LIVELLO $livello", $titles)) {
						$titles[] = "LIVELLO $livello";
					}
					
					$col = 2*$livello;
					
					$sheet->setCellValueByColumnAndRow($col,$riga,$codice);
					$sheet->setCellValueByColumnAndRow($col+1,$riga++,$descrizione);
				}
				
				$rowCount = $riga-1;
					
				$col = 0;
				foreach($titles as $val) {
					setRangeStyle($sheet, $col, 1, $col, $rowCount, $StringFormat);
					$sheet->setCellValueByColumnAndRow($col++,1,$val);
					$sheet->setCellValueByColumnAndRow($col++,1,"DESCRIZIONE $val");
				}
			}
			else {
				$cmatgebc = new wi400Routine('CMATGEBC', $connzend);
				$cmatgebc->load_description();
				$cmatgebc->prepare();
				$cmatgebc->set('TIPO', $ger_cls_src);
				$cmatgebc->call();
				
				$sql_ger = "SELECT *
					FROM QTEMP/FTEMPGER
					order by GETLIV, GETSUP, GETENT";
//				echo "SQL: $sql_ger<br>";
				
				$result = $db->query($sql_ger, false, 0);

				$ger_array = array();
				
				while($row = $db->fetch_array($result)) {
					$codice = $row['GETENT'];
//					$descrizione = get_campo_ente($codice, $_SESSION['data_validita'], "MAFDSE");
					$descrizione = $row['GETDES'];
					$livello = $row['GETLIV'];
					$liv_sup = $row['GETSUP'];
					
					if($liv_sup!="")
						$ger_array[$livello][$liv_sup][$codice] = $descrizione;
					else
						$ger_array[$livello][$codice] = $descrizione;
				}
//				echo "GER_ARRAY:<pre>"; print_r($ger_array); echo "</pre>";
//				echo "COUNT GER_ARRAY: ".count($ger_array)."<br>";
				
				$riga = get_ger_elem($sheet, $ger_array, 1, "", $riga);
				
				$rowCount = $riga-1;
					
				$col = 0;
				foreach($ger_array as $key => $val) {
					setRangeStyle($sheet, $col, 1, $col, $rowCount, $StringFormat);
					$sheet->setCellValueByColumnAndRow($col++,1,"LIVELLO $key");
					$sheet->setCellValueByColumnAndRow($col++,1,"DESCRIZIONE $key");
				}
			}
			
			$colCount = $col-1;
			
			setRangeStyle($sheet, 0, 1, $colCount, 1, $CenterStyle, true);
			
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