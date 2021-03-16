<?php

require_once $routine_path."/classi/wi400ExportList.cls.php";
require_once $routine_path."/classi/wi400invioEmail.cls.php";
//	require_once "conf/CONV_include.php";
//	require_once $conf_path."/CONV_include.php";

// Aumentata la dimensione del limite della memoria
//ini_set("memory_limit","1000M");
ini_set('memory_limit', '-1');
set_time_limit(0);

//	$start = startwatch();
//	stopwatch($start, "START ESTRAZIONE");

//	$idList = $batchContext->IDLIST;
$exportTarget = $batchContext->TARGET;
$exportFormat = $batchContext->FORMAT;
$data_val = $batchContext->DATA_VAL;
$sql = $batchContext->QUERY;
$pageRows = $batchContext->PAGE_ROWS;
$startFrom = $batchContext->START_FROM;
$rowsSelection = $batchContext->SEL_ROWS;
$notifica = $batchContext->NOTIFICA;
$zip = $batchContext->ZIP;
$username = $batchContext->USERNAME;

$user_loc = $batchContext->USER_LOCALE;
$area_fun = $batchContext->AREA_FUN;

$SMTP = array();
$SMTP['user'] = $settings['smtp_user'];
$SMTP['pass'] = $settings['smtp_pass'];
$SMTP['mail_host'] = $settings['smtp_host'];
//	$SMTP['from_name'] = "SISTEMIINFORMATIVI@AUTOGRILL.NET";
$SMTP['from_name'] = $settings['smtp_from'];
$SMTP['SMTPauth'] = $settings['smtp_auth'];

$rowsSelectionArray = array();
if(!empty($rowsSelection)) {
	$rowsSelectionArray = unserialize($rowsSelection);
	//		echo "<b>ROWS SELECTION ARRAY:</b> "; print_r($rowsSelectionArray); echo "<br>";
}
else {
	//		echo "NO SELS<br>";
	die();
}
$file = $batchContext->file;

$path = dirname($file);

if(empty($exportFormat))
	$exportType = "excel5";
else
	$exportType = $exportFormat;

// Impostazione dei dati del file
if($exportType=="excel2007")
	$file_type = ".xlsx";
else if($exportType=="excel5")
	$file_type = ".xls";
else if($exportType=="csv")
	$file_type = ".csv";

$filename = "Destocking_".date("YmdHis").$file_type;

$filepath = $path."/".$filename;

$export = new wi400ExportList();

if($exportType!="csv") {
	// Esportazione in file Excel

	checkIfZipLoaded();

	// Error reporting
	error_reporting(E_ALL);

	// PHPExcel
	require_once $routine_path."/excel/PHPExcel.php";

	// PHPExcel_IOFactory
	require_once $routine_path.'/excel/PHPExcel/IOFactory.php';

	// Create new PHPExcel object
	$objPHPExcel = new PHPExcel();

	//	stopwatch($start, "START EXCEL");

	// Set file properties
	$objPHPExcel->getProperties()->setCreator($settings['cliente_installazione']);
	$objPHPExcel->getProperties()->setLastModifiedBy($settings['cliente_installazione']);
	$objPHPExcel->getProperties()->setTitle("Progressivo coperture");
	$objPHPExcel->getProperties()->setSubject("Progressivo coperture");
	$objPHPExcel->getProperties()->setDescription("Progressivo coperture");
	$objPHPExcel->getProperties()->setKeywords("office 2007 openxml php");
	$objPHPExcel->getProperties()->setCategory("Test result file");

	// Impostazione dei formati delle celle
	// Center Bold Style
	$CenterBoldStyle = array(
			'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			),

			'font' => array(
					'bold' => true
			)
	);

	// Primo foglio
	$objPHPExcel->setActiveSheetIndex(0);
	$sheet = $objPHPExcel->getActiveSheet();

	if($exportTarget == "PAGE")
		$resultSet = $db->query($sql);
	else
		$resultSet = $db->query($sql, False, 0);

	if($exportTarget=="PAGE") {
		// Posizionamento su record
		if ($startFrom>0)
			$db->fetch_array($resultSet,$startFrom);
	}

	$array_destocking = array();
	if(isset($rowsSelectionArray) && !empty($rowsSelectionArray)) {
		foreach($rowsSelectionArray as $sel => $val) {
			$keys = array();
			$keys = explode("|", $sel);

			$array_destocking[] = $keys[0];
		}
	}

	$riga_Header_ini = 1;
	$riga = $riga_Header_ini;
	$colonna = 0;

	$range_Header_ini = PHPExcel_Cell::stringFromColumnIndex($colonna).$riga_Header_ini;

	$sheet->setCellValueByColumnAndRow($colonna++,$riga,'Periodo');
	$sheet->setCellValueByColumnAndRow($colonna++,$riga,'Locale');
	$sheet->setCellValueByColumnAndRow($colonna++,$riga,'Descrizione Locale');
	$sheet->setCellValueByColumnAndRow($colonna++,$riga,'Post');
	$sheet->setCellValueByColumnAndRow($colonna++,$riga,'Cumulativo');
	$sheet->setCellValueByColumnAndRow($colonna++,$riga,'Descrizione Cumulativo');
	$sheet->setCellValueByColumnAndRow($colonna++,$riga,'Articolo');
	$sheet->setCellValueByColumnAndRow($colonna++,$riga,'Descrizione Articolo');
	$sheet->setCellValueByColumnAndRow($colonna++,$riga,'Fattore di conversione');
	$sheet->setCellValueByColumnAndRow($colonna++,$riga,'Inventario iniziale');
	$sheet->setCellValueByColumnAndRow($colonna++,$riga,'Entrate');
	$sheet->setCellValueByColumnAndRow($colonna++,$riga,'Uscite');
	$sheet->setCellValueByColumnAndRow($colonna++,$riga,'Inventario finale');
	$sheet->setCellValueByColumnAndRow($colonna++,$riga,'Delta');
	$sheet->setCellValueByColumnAndRow($colonna++,$riga,'Delta utilizzato');
	$sheet->setCellValueByColumnAndRow($colonna++,$riga,'Percentuale di incidenza');
	$sheet->setCellValueByColumnAndRow($colonna,$riga,'Note');

	$colCount = $colonna;

	$range_Header_fin = PHPExcel_Cell::stringFromColumnIndex($colCount).$riga;

	//	$sheet->setCellValueByColumnAndRow(0,1,$filtri);
	//	$sheet->getStyleByColumnAndRow(0,1)->getAlignment()->setWrapText(true);
	//	$sheet->mergeCellsByColumnAndRow(0,1,$colCount,1);

	$sheet->getStyle($range_Header_ini.":".$range_Header_fin)->applyFromArray($CenterBoldStyle);

		$riga++;
		$colonna = 0;
	
	$i=0;

	while($row_desk = $db->fetch_array($resultSet)) {
		if ($exportTarget == "PAGE" && $i == $pageRows)
			break;
		else if ($exportTarget == "SELECTED") {
			if ($i < count($array_destocking)) {
				if(!in_array($row_desk['LOGCDA'],$array_destocking))
					continue;
			}
			else
				break;
		}

		$periodo = (dateModelToView($row_desk['LOGDTA']));
//		$periodo = $row_desk['LOGDTA'];
//		$des_ricetta = $row_desk['RICDSA'];
		$locale = $row_desk['LOGCDE'];
		$post = $row_desk['LOGPST'];
		$cumulativo = $row_desk['LOGCUM'];
		$articolo= $row_desk['LOGCDA'];
		$fattconv= $row_desk['LOGFTC'];
		$invini= $row_desk['LOGINI'];
		$entrate= $row_desk['LOGCAR'];
		$uscite= $row_desk['LOGSCA'];
		$invfin= $row_desk['LOGFIN'];
		$delta= $row_desk['LOGQTS'];
		$deltau= $row_desk['LOGDLT'];
		$incidenza= $row_desk['LOGINC'];
		$note= $row_desk['LOGNOT'];
		
		$delocale = get_campo_ente($locale, date("Ymd"),"MAFDSE");		
		$decumulativo = get_campo_articolo($cumulativo, date("Ymd"),"MDADSA");	
		$dearticolo = get_campo_articolo($articolo, date("Ymd"),"MDADSA");
	
			$sheet->setCellValueExplicitByColumnAndRow($colonna++,$riga,$periodo);
			$sheet->setCellValueExplicitByColumnAndRow($colonna++,$riga,$locale);
			$sheet->setCellValueExplicitByColumnAndRow($colonna++,$riga,$delocale);
			$sheet->setCellValueExplicitByColumnAndRow($colonna++,$riga,$post);
			$sheet->setCellValueExplicitByColumnAndRow($colonna++,$riga,$cumulativo);
			$sheet->setCellValueExplicitByColumnAndRow($colonna++,$riga,$decumulativo);
			$sheet->setCellValueExplicitByColumnAndRow($colonna++,$riga,$articolo);
			$sheet->setCellValueExplicitByColumnAndRow($colonna++,$riga,$dearticolo);
			$sheet->setCellValueExplicitByColumnAndRow($colonna++,$riga,$fattconv,PHPExcel_Cell_DataType::TYPE_NUMERIC);
			$sheet->setCellValueExplicitByColumnAndRow($colonna++,$riga,$invini,PHPExcel_Cell_DataType::TYPE_NUMERIC);
			$sheet->setCellValueExplicitByColumnAndRow($colonna++,$riga,$entrate,PHPExcel_Cell_DataType::TYPE_NUMERIC);
			$sheet->setCellValueExplicitByColumnAndRow($colonna++,$riga,$uscite,PHPExcel_Cell_DataType::TYPE_NUMERIC);
			$sheet->setCellValueExplicitByColumnAndRow($colonna++,$riga,$invfin,PHPExcel_Cell_DataType::TYPE_NUMERIC);
			$sheet->setCellValueExplicitByColumnAndRow($colonna++,$riga,$delta,PHPExcel_Cell_DataType::TYPE_NUMERIC);
			$sheet->setCellValueExplicitByColumnAndRow($colonna++,$riga,$deltau,PHPExcel_Cell_DataType::TYPE_NUMERIC);
			$sheet->setCellValueExplicitByColumnAndRow($colonna++,$riga,$incidenza,PHPExcel_Cell_DataType::TYPE_NUMERIC);
			$sheet->setCellValueExplicitByColumnAndRow($colonna++,$riga,$note);

				$riga++;
		$colonna = 0;
	}

		$i++;
	}

	//	print_log("RIGHE TOT: $riga");

	//	stopwatch($start, "PRIMA DI UNSET");

	$db->freeResult($resultSet);


	//	stopwatch($start, "DOPO UNSET");
	/*
	 $borderStyle = array(
	 		'borders' => array(
	 				'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
	 				'top' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
	 				'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
	 				'right'	=> array('style' => PHPExcel_Style_Border::BORDER_THIN)
	 		)
	 );

	// AutoSize delle colonne
	for($i=0; $i<=$colCount; $i++) {
	$sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
	for($j=1; $j<=$rowCount; $j++) {
	$sheet->getStyleByColumnAndRow($i,$j)->applyFromArray($borderStyle);
	}
	}
	*/
	// Set page orientation and size
	$sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
	$sheet->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

	// Rename sheet
	$sheet->setTitle('Dati');

	// Stampa la graglia
	//	$sheet->setPrintGridlines(true);

	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex(0);

	//	stopwatch($start, "FILE: $filepath");

	if($exportType=="excel2007") {
		// Save Excel 2007 file
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		//		$objWriter->save($export->get_filepath());
		$objWriter->save($filepath);
	}
	else if($exportType=="excel5") {
		// Export to Excel5 (.xls)
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		//		$objWriter->save($export->get_filepath());
		$objWriter->save($filepath);
	}

	//	stopwatch($start, "END EXCEL");


else {
	// Esportazione in file CSV

	//	stopwatch($start, "START CSV");

	if (!$handle = fopen($filepath, 'w')) {
		echo "Non si riesce ad aprire il file ($filename)\r\n";
		exit;
	}

	if($exportTarget == "PAGE")
		$resultSet = $db->query($sql);
	else
		$resultSet = $db->query($sql, False, 0);

	if($exportTarget=="PAGE") {
		// Posizionamento su record
		if ($startFrom>0)
			$db->fetch_array($resultSet,$startFrom);
	}

	$array_destocking = array();
	if(isset($rowsSelectionArray) && !empty($rowsSelectionArray)) {
		foreach($rowsSelectionArray as $sel => $val) {
			$keys = array();
			$keys = explode("|", $sel);

			$array_destocking[] = $keys[0];
		}
	}

	$riga = 0;
	$colonna = 0;

	$exportCols = array();

	$exportCols[] = 'Periodo';
	$exportCols[] = 'Locale';
	$exportCols[] = 'Descrizione Locale';
	$exportCols[] = 'Post';
	$exportCols[] = 'Cumulativo';
	$exportCols[] = 'Descrizione Cumulativo';
	$exportCols[] = 'Articolo';
	$exportCols[] = 'Descrizione Articolo';
	$exportCols[] = 'Fatore di conversione';
	$exportCols[] = 'Inventario iniziale';
	$exportCols[] = 'Entrate';
	$exportCols[] = 'Uscite';
	$exportCols[] = 'Inventario finale';
	$exportCols[] = 'Delta';
	$exportCols[] = 'Delta utilizzato';
	$exportCols[] = 'Percentuale di incidenza';
	$exportCols[] = 'Note';

	$colCount = $colonna;

	$export->writeCsv($handle, $exportCols);

	$exportCols = array();

	$i=0;

	$riga = -1;

	while($row_desk = $db->fetch_array($resultSet)) {
		if ($exportTarget == "PAGE" && $i == $pageRows)
			break;
		else if ($exportTarget == "SELECTED") {
			if ($i < count($array_destocking)) {
				if(!in_array($row_desk['LOGCDA'],$array_destocking))
					continue;
			}
			else
				break;
		}

		$ricetta = $row_desk['LOGDTA'];
//		$des_ricetta = $row_desk['RICDSA'];
		$locale = $row_desk['LOGCDE'];
		$post = $row_desk['LOGPST'];
		$cumulativo = $row_desk['LOGCUM'];
		$articolo= $row_desk['LOGCDA'];
		$fattconv= $row_desk['LOGFTC'];
		$invini= $row_desk['LOGINI'];
		$entrate= $row_desk['LOGCAR'];
		$uscite= $row_desk['LOGSCA'];
		$invfin= $row_desk['LOGFIN'];
		$delta= $row_desk['LOGQTS'];
		$deltau= $row_desk['LOGDLT'];
		$incidenza= $row_desk['LOGINC'];
		$note= $row_desk['LOGNOT'];

		$delocale = get_campo_ente($locale, date("Ymd"),"MAFDSE");		
		$decumulativo = get_campo_articolo($cumulativo, date("Ymd"),"MDADSA");	
		$dearticolo = get_campo_articolo($articolo, date("Ymd"),"MDADSA");

	$exportCols[] = prepare_string($periodo);
	$exportCols[] = prepare_string($locale);
	$exportCols[] = prepare_string($delocale);
	$exportCols[] = prepare_string($post);
	$exportCols[] = prepare_string($cumulativo);
	$exportCols[] = prepare_string($decumulativo);
	$exportCols[] = prepare_string($articolo);
	$exportCols[] = prepare_string($dearticolo);
	$exportCols[] = wi400_format_DOUBLE_4($fattconv);
	$exportCols[] = wi400_format_DOUBLE_2($invini);
	$exportCols[] = wi400_format_DOUBLE_2($entrate);
	$exportCols[] = wi400_format_DOUBLE_2($uscite);
	$exportCols[] = wi400_format_DOUBLE_2($invfin);
	$exportCols[] = wi400_format_DOUBLE_2($delta);
	$exportCols[] = wi400_format_DOUBLE_7($deltau);
	$exportCols[] = wi400_format_DOUBLE_7($incidenza);
	$exportCols[] = prepare_string($note);
				
			$export->writeCsv($handle, $exportCols);
			$exportCols = array();
		}

		$i++;
	}

	//	print_log("RIGHE TOT: $riga");

	//	stopwatch($start, "PRIMA DI UNSET");

	$db->freeResult($resultSet);
	
	fclose($handle);

	//	stopwatch($start, "END CSV");


if($zip=="ZIP") {
	echo "ZIP DEL FILE\r\n";

	$zip_parts = explode(".", basename($filepath));
	$zip_path = dirname($filepath)."/".$zip_parts[0].'.zip';

	wi400invioEmail::compress(array($filepath),$zip_path);

	//		stopwatch($start, "ZIP");
}

if(in_array($notifica,array("NOTIFICA","ALLEGATO"))) {
	echo "INVIO DI UN'E-MAIL DI NOTIFICA\r\n";

	//		$from = $siri_server_settings['smtp_user'];

	$to_array = array();
//	$userMail = getUserMail($username);
	$userMail = getUserMail($username, $user_loc, $area_fun);
	$to_array[] = trim($userMail);

	//		echo "EMAIL:".$row['EMAIL'];

	$subject = "Esportazione lista di destocking";

	$body = "L'esportazione della lista di destocking è stata eseguita con successo.";

	$files = array();
	if($notifica=="ALLEGATO") {
		echo "INVIO DEL FILE COME ALLEGATO NELL'E-MAIL\r\n";
		$body .= "\r\nIl file esportato è stato allegato a quest'e-mail";

		if($zip=="ZIP") {
			$body .= " in formato compresso (zip).";

			$files[] = $zip_path;
		}
		else {
			$files[] = $filepath;
		}
	}
	// @todo Perchè non abbiamo usato la classe???
	$sent = wi400invioEmail::invioEmail('',$to_array,'',$subject,$body,$files,$SMTP);

	if($sent===false)
		//			$messageContext->addMessage("ERROR", "Errore durante l'invio dell'email");
		echo "Errore durante l'invio dell'email\r\n";
	else
		//			$messageContext->addMessage("SUCCESS", "Email inviata con successo");
		echo "Email inviata con successo\r\n";

	//		stopwatch($start, "EMAIL");
}
else {
	echo "NON NOTIFICARE\r\n";
}

if($zip=="ZIP") {
	unlink($filepath);
}

?>