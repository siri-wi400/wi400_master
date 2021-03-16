<?php

	$azione = $actionContext->getAction();
	
	$menu_array = array();
	if(wi400Detail::getDetailValue('EXPORT_MENU_STRUTTURA', 'MENU')!="")
		$menu_array = wi400Detail::getDetailValue('EXPORT_MENU_STRUTTURA', 'MENU');
	
	$user_array_1 = array();
	if(wi400Detail::getDetailValue('EXPORT_MENU_STRUTTURA_USER', 'USER_1')!="")
		$user_array_1 = wi400Detail::getDetailValue('EXPORT_MENU_STRUTTURA_USER', 'USER_1');
	
	$azione_search = wi400Detail::getDetailValue('EXPORT_SEARCH_ACTION','AZIONE');
	
	$user_array_2 = array();
	if(wi400Detail::getDetailValue('EXPORT_SEARCH_ACTION', 'USER_2')!="")
		$user_array_2 = wi400Detail::getDetailValue('EXPORT_SEARCH_ACTION', 'USER_2');

	if($actionContext->getForm()=="DEFAULT") {
		
	}
	else {	
		require_once $routine_path."/classi/wi400ExportList.cls.php";
		require_once $routine_path."/classi/wi400invioEmail.cls.php";
		
		require_once 'struttura_menu_common.php';
		
		$in_azioni = array("MENU", "USER", "SEARCH_AZIONE");
		
		if(in_array($actionContext->getForm(), $in_azioni)) {
			// Aumentata la dimensione del limite della memoria
			ini_set("memory_limit","1000M");
			set_time_limit(0);
		
			$exportType = "excel2007";
		
			// Impostazione dei dati del file
			if($exportType=="excel2007")
				$file_type = ".xlsx";
			else if($exportType=="excel5")
				$file_type = ".xls";
			else if($exportType=="csv")
				$file_type = ".csv";
				
			$export = new wi400ExportList();
		
			// Esportazione in file Excel
			$filename = "Struttura_Menu_".date("YmdHis").$file_type;
			if($actionContext->getForm()=="USER") {
				$filename = "Struttura_Menu_Utenti_".date("YmdHis").$file_type;
			}
				
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
			$objPHPExcel->getProperties()->setTitle("Struttura Menu");
			$objPHPExcel->getProperties()->setSubject("Struttura Menu");
			$objPHPExcel->getProperties()->setDescription("Struttura Menu");
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
//			echo "HEADER STYLE:<pre>"; print_r($header_style); echo "</pre>";
		
			$objPHPExcel->setActiveSheetIndex();
			$sheet = $objPHPExcel->getActiveSheet();
		}
	
		if($actionContext->getForm()=="MENU") {
			$menu_search = array();
			if(wi400Detail::getDetailValue('EXPORT_MENU_STRUTTURA', 'MENU')!="")
				$menu_search = wi400Detail::getDetailValue('EXPORT_MENU_STRUTTURA', 'MENU');
//				echo "MENU SEARCH:<pre>"; print_r($menu_search); echo "</pre>";
	
			$menu_array = get_menu_array($menu_search);
		
			if(!empty($menu_array)) {
				ksort($menu_array);
					
				foreach($menu_array as $menu => $vals) {
					$colCount = print_menu($sheet, $menu, 0);
				}
			}
		}
		else if($actionContext->getForm()=="USER") {
			$sql_user = get_user_sql($user_array_1);
		
			$res_user = $db->query($sql_user, false, 0);
		
			$user_menu = array();
			while($row_user = $db->fetch_array($res_user)) {
				$user_menu = array(
					"MENU" => strtoupper($row_user['MENU']),
					"USER_MENU" => strtoupper($row_user['USER_MENU'])
				);
//				echo "USER MENU:<pre>"; print_r($user_menu); echo "</pre>";

				$user_info = array(
					"USER_NAME" => $row_user['USER_NAME'],
					"NAZIONE" => $row_user['NAZIONE'],
					"AREAFUN" => $row_user['AREAFUN']
				);
//				echo "USER INFO:<pre>"; print_r($user_info); echo "</pre>";
				
				$colCount = print_user_menu($sheet, $user_info, $user_menu, $azione_search);
//				echo "COL COUNT:$colCount<br>";
			}
		}
		else if($actionContext->getForm()=="SEARCH_AZIONE") {
			$in_menu = search_action_in_menu($azione_search);
//			echo "IN MENU:<pre>"; print_r($in_menu); echo "</pre>";
		
			if(!empty($in_menu)) {
				$sql_user = get_user_sql($user_array_2, $in_menu);
			}
	
			$res_user = $db->query($sql_user, false, 0);
	
			$user_menu = array();
			while($row_user = $db->fetch_array($res_user)) {
				$user_menu = array(
					"MENU" => strtoupper($row_user['MENU']),
					"USER_MENU" => strtoupper($row_user['USER_MENU'])
				);
//				echo "USER MENU:<pre>"; print_r($user_menu); echo "</pre>";

				$user_info = array(
					"USER_NAME" => $row_user['USER_NAME'],
					"NAZIONE" => $row_user['NAZIONE'],
					"AREAFUN" => $row_user['AREAFUN']
				);
//				echo "USER INFO:<pre>"; print_r($user_info); echo "</pre>";
	
				$colCount = print_user_menu($sheet, $user_info, $user_menu, $azione_search);
//				echo "COL COUNT:$colCount<br>";
			}
		}
	
		if(in_array($actionContext->getForm(), $in_azioni)) {
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
	
//			$objWriter->setPreCalculateFormulas(false);
	
			$filepath = $export->get_filepath();
	
			$objWriter->save($filepath);
		}
	}