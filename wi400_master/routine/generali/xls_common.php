<?php

	// @todo !!! NOTA IMPORTANTE !!!
	
	// RICORDARSI DI AGGIUNGERE TUTTE LE VARIABILI DI STILE TRA LE GLOBAL
	// PERCHE' ALTRIMENTI NON FUNZIONANO GLI INVII DELLE E-MAIL CON XLS DA GENERARE (es. Esportazioni Showroom in Unicomm)
	// IN QUANTO ALTRIMENTI LA FUNZIONE file_by_launch_batch_action() SI PERDE LE VARIABILI NON SPECIFICATE COME GLOBALI
	
	// @todo !!! NOTA IMPORTANTE !!!
/*	
	global $FillBlueStyle, $FillLightBlueStyle, $FillGreenStyle, $FillLightGreenStyle;
	global $FillGreyStyle, $FillOrangeStyle, $FillRedStyle, $FillYellowStyle, $FillWhiteStyle;
	global $FontBlackStyle, $FontBlueStyle, $FontLightBlueStyle, $FontGreenStyle, $FontLightGreenStyle, $FontRedStyle;
	global $BoldStyle, $CenterStyle, $WrapStyle, $CenterWrap, $StringFormat;
	global $CenterBoldStyle, $CenterBoldWrapStyle, $FillNoneStyle;
	global $BorderStyle, $MediumBorderStyle, $OutilneBorderStyle;
	global $LockedFormat, $UnlockedFormat;
	global $error_color_array, $date_formats;
*/
	// PHPExcel
	// @todo Cancellare in giro ripetizioni
	// Estensioni necessarie per il funzionamento delle classi di PHPExcel
	// Example loading an extension based on OS
	if (!extension_loaded('sqlite3')) {
		if(!isset($settings['check_dl']) || $settings['check_dl']===true) {
			if(!(bool)ini_get( "enable_dl" ) || (bool)ini_get("safe_mode")) {
				//die("dl(): Caricamento estensioni dinamico non ammesso. Contattare l'amministratore del server.\n");
			}
		}
	
		checkIfZipLoaded();
	}
	
	// Error reporting
	error_reporting(E_ALL);
	
//	echo "XLS_COMMON<br>";
	
	// PHPExcel
	require_once $routine_path."/excel/PHPExcel.php";
	
	// PHPExcel_IOFactory
	require_once $routine_path.'/excel/PHPExcel/IOFactory.php';
	
	$col_ini = 0;

	// Impostazione dei formati delle celle
	// FILLER
	$FillBlueStyle = array(
		'fill' 	=> array(
			'type' => PHPExcel_Style_Fill::FILL_SOLID,
			'color' => array('argb' => PHPExcel_Style_Color::COLOR_BLUE)
//			'color' => array('rgb' => '6495ED')
		)
	);
	
	$FillLightBlueStyle = array(
		'fill' => array(
			'type' => PHPExcel_Style_Fill::FILL_SOLID,
			'color' => array('rgb' => 'ABCDEF')
		)
	);
	
	$FillGreenStyle = array(
		'fill' 	=> array(
			'type' => PHPExcel_Style_Fill::FILL_SOLID,
			'color' => array('argb' => PHPExcel_Style_Color::COLOR_DARKGREEN)
//			'color'	=> array('rgb' => '228b22')
		)
	);
	
	$FillLightGreenStyle = array(
		'fill' => array(
			'type' => PHPExcel_Style_Fill::FILL_SOLID,
			'color' => array('rgb' => '98FF98')				// FFF8C6
		)
	);
	
	$FillGreyStyle = array(
		'fill' 	=> array(
			'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
			'color'		=> array('rgb' => 'D2D2D2')
		)
	);
	
	$FillOrangeStyle = array(
		'fill' => array(
			'type' => PHPExcel_Style_Fill::FILL_SOLID,
			'color' => array('rgb' => 'ffa500')
		)
	);

	$FillRedStyle = array(
		'fill' => array(
			'type' => PHPExcel_Style_Fill::FILL_SOLID,
			'color' => array('argb' => PHPExcel_Style_Color::COLOR_RED)
		)
	);
	
	$FillYellowStyle = array(
		'fill' => array(
			'type' => PHPExcel_Style_Fill::FILL_SOLID,
			'color' => array('argb' => PHPExcel_Style_Color::COLOR_YELLOW)
		)
	);
	
	$FillWhiteStyle = array(
		'fill' 	=> array(
			'type' => PHPExcel_Style_Fill::FILL_NONE,
			'color' => array('argb' => PHPExcel_Style_Color::COLOR_WHITE)
		)
	);
	
	// FONT BOLD
	$BoldStyle = array(
		'font' => array(
			'bold' => true
		)
	);
//	$BoldStyle['font'] = array('bold' => true);
	
	// FONT COLOR
	$FontBlackStyle = array(
		'font' => array(
			'color' => array('argb' => PHPExcel_Style_Color::COLOR_BLACK)
		)
	);
	
	$FontBlueStyle = array(
		'font' => array(
			'color' => array('argb' => PHPExcel_Style_Color::COLOR_BLUE)
		)
	);
//	$FontBlueStyle['font']['color'] = array('argb' => PHPExcel_Style_Color::COLOR_BLUE);

	$FontLightBlueStyle = array(
		'font' => array(
			'type' => PHPExcel_Style_Fill::FILL_SOLID,
			'color' => array('rgb' => 'ABCDEF')
		)
	);
	
	$FontGreenStyle = array(
		'font' => array(
			'color' => array('argb' => PHPExcel_Style_Color::COLOR_DARKGREEN)
		)
	);
//	$FontGreenStyle['font']['color'] = array('argb' => PHPExcel_Style_Color::COLOR_DARKGREEN);

	$FontLightGreenStyle = array(
		'font' => array(
			'type' => PHPExcel_Style_Fill::FILL_SOLID,
			'color' => array('rgb' => '98FF98')				// FFF8C6
		)
	);

	$FontRedStyle = array(
		'font' => array(
			'color' => array('argb' => PHPExcel_Style_Color::COLOR_RED)
		)
	);
//	$FontRedStyle['font']['color'] = array('argb' => PHPExcel_Style_Color::COLOR_RED);
	
	// ALIGN
	$CenterStyle = array(
		'alignment' => array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
		)
	);
/*
	 $CenterBoldStyle = array(
 		'alignment' => array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
 		),
 		'font' => array(
			'bold' => true
 		)
	 );
*/
	// ORIENTATION
	$VerticalStyle = array(
		'alignment' => array(
			'rotation' => 90
		)
	);
	
	// WRAP
	$WrapStyle = array(
		'alignment' => array(
//			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
//			'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			'wrap' => true
		)
	);
//	$WrapStyle['alignment']['wrap'] = true;
	
	$CenterWrap = array();
	$CenterWrap['alignment'] = array_merge($CenterStyle['alignment'], $WrapStyle['alignment']);
	
	// FORMAT
	$StringFormat = array(
		'numberformat' => array(
			'code' => PHPExcel_Style_NumberFormat::FORMAT_TEXT
		)
	);
	
	// BORDER
	$BorderStyle = array(
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN
			),
		)
	);
	
	$MediumBorderStyle = array(
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_MEDIUM
			),
		)
	);
	
	$OutilneBorderStyle = array(
		'borders' => array(
			'outline' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN,
//				'color' => array('argb' => 'FFFF0000'),
			),
		)	
	);
	
	$MediumOutilneBorderStyle = array(
		'borders' => array(
			'outline' => array(
				'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
//				'color' => array('argb' => 'FFFF0000'),
			),
		)
	);
	
	// PROTEZIONE
	$LockedFormat = array(
		'protection' => array(
			'locked' => PHPExcel_Style_Protection::PROTECTION_PROTECTED
		)
	);
	
	$UnlockedFormat = array(
		'protection' => array(
			'locked' => PHPExcel_Style_Protection::PROTECTION_UNPROTECTED
		)
	);
	
	// MIXED STYLES
	$CenterBoldStyle = array_merge($CenterStyle, $BoldStyle);
	$CenterBoldWrapStyle = array_merge($CenterWrap, $BoldStyle);
	$FillNoneStyle = array_merge($FillWhiteStyle, $FontBlackStyle);
	
	$error_color_array = array(
		"W" => "Yellow",
		"F" => "Red",
		"I" => "Blue",
		"R" => "Grey"
	);
	
	$date_formats = array(
		"yyyy-mm-dd",
		"yy-mm-dd",
		'yy/mm/dd;@',
		"mm-dd-yy",
		"m/d/yy",
		"d-m-y",
		"dd/mm/yy",
		"d/m/y",
	);
	
	function setRangeStyle($sheet, $col_ini, $row_ini, $col_fin, $row_fin, $style=null, $bold=false) {
		$range_ini = PHPExcel_Cell::stringFromColumnIndex($col_ini).$row_ini;
		$range_fin = PHPExcel_Cell::stringFromColumnIndex($col_fin).$row_fin;
	
		if(!empty($style))
			$sheet->getStyle($range_ini.":".$range_fin)->applyFromArray($style);
	
		if($bold===true)
			$sheet->getStyle($range_ini.":".$range_fin)->getFont()->setBold($bold);
	
		return $sheet;
	}
	
	function stampaErrore($sheet, $logFile, $i, $j, $header, $valore, $messaggio, $error="F", $onlyLog=false, $simple_msg=false) {
		global $num_errori, $num_warning, $num_info, $rigaErrore;
	
		$msg_mio = "";
		if ($error=='F') {
			$num_errori++;
			$msg_mio = "Errore $num_errori";
			$rigaErrore = True;
		}
		elseif ($error=='W') {
			$num_warning++;
			$msg_mio = "Warning $num_warning";
		}
		elseif ($error=='I') {
			$msg_mio = "Info $num_info";
			$num_info ++;
		}
	
		// @todo Segnalazione errore su foglio EXCEL
	
		$colonna = PHPExcel_Cell::stringFromColumnIndex($j);
	
		if($header=="") {
			$params = array($msg_mio, $messaggio, $colonna, $i, $valore);
			$dati = _t('LOG_IMPORT_MSG_GEN', $params);
		}
		else {
/*			
			$params = array($msg_mio, $messaggio, $header, $colonna, $i, $valore);
			$dati = _t('LOG_IMPORT_MSG', $params);
*/			
			$params = array($msg_mio, $header, $messaggio, $colonna, $i, $valore);
			$dati = _t('LOG_IMPORT_MSG_CMP', $params);
		}
//		echo "PARAMS:<pre>"; print_r($params); echo "</pre>";
//		echo "MSG: $dati<br>";
	
		write_file_txt($logFile, $dati);
//		return;
	
		if($onlyLog==false) {
			if($header=="" || $simple_msg===true) {
				$sheet = write_error_on_xls_file($sheet, $i, $j, $messaggio, $error);
			}
			else {
				$params = array($valore, $header, $messaggio);
				$msg = _t('LOG_IMPORT_MSG_XLS', $params);

				$sheet = write_error_on_xls_file($sheet, $i, $j, $msg, $error);
			}
		}
	
		return $sheet;
	}
	
	function stampaErroreRiga($sheet, $logFile, $riga, $max_cols, $messaggio, $onlyLog=false) {
		global $num_err_sys, $FillOrangeStyle;
	
		$num_err_sys++;
	
		$msg_mio = "Errore di sistema $num_err_sys";
	
		$params = array($msg_mio, $messaggio, $riga);
//		echo "PARAMS:<pre>"; print_r($params); echo "</pre>";
	
		$dati = _t('LOG_IMPORT_MSG_RIGA', $params);
//		echo "$dati<br>";
	
		write_file_txt($logFile, $dati);
	
		if($onlyLog==false) {
//			echo "RIGA: $riga - MAX COLS: $max_cols - MSG: $messaggio<br>";
			setRangeStyle($sheet, 0, $riga, $max_cols-1, $riga, $FillOrangeStyle);
			
			$sheet->getCommentByColumnAndRow(0, $riga)->getText()->createTextRun($messaggio);
			
			$sheet->getCommentByColumnAndRow(0, $riga)->setWidth("250px");
			$sheet->getCommentByColumnAndRow(0, $riga)->setHeight("150px");
		}
	}
	
	function write_error_on_xls_file($sheet, $i, $j, $messaggio, $error) {
		global $error_color_array, $FillRedStyle, $FillYellowStyle, $FillBlueStyle, $FillGreyStyle;
		
		if(empty($sheet))
			return;
		
//		echo "<font color='green'>WRITE ERROR IN FILE EXCEL</font> - MSG: $messaggio - COL: $j - RIGA: $i<br>";
			
		$messaggio = prepare_string($messaggio);
			
		$style = "Fill".$error_color_array[$error]."Style";
// 		echo "STYLE: $style<br>";
		
		$color_cella = $sheet->getStyleByColumnAndRow($j,$i)->getFill()->getStartColor()->getARGB();
		$val = $sheet->getCellByColumnAndRow($j,$i)->getValue();
		if($color_cella != PHPExcel_Style_Color::COLOR_RED) {
			$sheet->getStyleByColumnAndRow($j,$i)->applyFromArray($$style);
		}
		$sheet->getCommentByColumnAndRow($j,$i)->getText()->createTextRun($messaggio."\r\n");
		
		$sheet->getCommentByColumnAndRow($j, $i)->setWidth("250px");
		$sheet->getCommentByColumnAndRow($j, $i)->setHeight("150px");
			
		if($error=="I") {
			$dati = explode(" ", $messaggio);
			$string = $dati[0];
	
			$sheet->setCellValueExplicitByColumnAndRow($j,$i, $string);
		}
	
		return $sheet;
	}
	
	function search_sheet_xls($objPHPExcel, $foglio="") {
		if($foglio=="") {
//			echo "CURRENT SHEET<br>";
			$sheet = $objPHPExcel->getActiveSheet();
				
			// @todo Scoprire cosa fa di preciso la funzione se venisse usata così come qui sotto? Valutare se è meglio?
			/**
			* Create array from worksheet
			*
			* @param mixed $nullValue Value returned in the array entry if a cell doesn't exist
			* @param boolean $calculateFormulas Should formulas be calculated?
			* @param boolean $formatData  Should formatting be applied to cell values?
			* @param boolean $returnCellRef False - Return a simple array of rows and columns indexed by number counting from zero
			*                               True - Return rows and columns indexed by their actual row and column IDs
			* @return array
			*/
//			$sheet = $objPHPExcel->getActiveSheet()->toArray(null, true, true, false);
				
			if(!is_object($sheet)) {
				$sheet = $objPHPExcel->getSheet(0);
			}
			
			return $sheet;
		}
		else {
			$num_sheets = count($objPHPExcel->getAllSheets());
//			echo "NUM SHEETS: $num_sheets<br>";

//			echo "FOGLIO: $foglio<br>";
	
			for($s=0; $s<$num_sheets; $s++) {
				$sheet = $objPHPExcel->getSheet($s);
//				echo "TITLE: ".$sheet->getTitle()."<br>";
				if(strncmp($sheet->getTitle(), $foglio, strlen($foglio))==0) {
//					echo "<font color='red'>TITLE FOUND:</font> ".$sheet->getTitle()."<br>";
					return $sheet;
				}
			}
		}
	
		return false;
	}
	
	function check_data_by_format($data) {
		$types_data = array(
			"DATE_STRING",
			"SHORT_DATE",
			"DATE"
		);
		
		$date = "";		
		$check_data = false;
		
//		echo "<font color='red'>DATA - $data - ";
		
		if($data!="") {
			foreach($types_data as $tipo) {
				$check_data = checkDateFormat($data, $tipo);
				
				if($check_data===false)
					continue;
				
				if($tipo=="SHORT_DATE") {
//					echo "SHORT_DATE";
					$date = date6to8(str_replace("/", "", $data));
				}
				else if($tipo=="DATE") {
//					echo "DATE";
					$date = dateViewToModel($data);
				}
				else if($tipo=="DATE_STRING") {
//					echo "DATE_STRING";
					$date = $data;
				}
				
//				echo " - $date";

				break;
			}
		}
		
		if($check_data===false) {
			$date = false;
//			echo "FALSE";
		}
		
//		echo "</font><br>";
		
		return $date;
	}
	
	// Controllo del formato della data
	function check_data_val($data, $format="", $check_prev=false) {
//		echo "CHECK DATA VAL - DATA: $data - FORMAT: $format - CHECK PREV: $check_prev<br>";
		
		$check_prev = true;
		
		if($data!="") {
			if($check_prev===true) {
//				echo "<font color='green'>CHECK PREV</font><br>";
				$date = check_data_by_format($data);
				
				if($date!==false)
					return $date;
			}
			
			$has_format = true;
			if(isset($format) && $format!="") {
//				echo "<font color='orange'>CHECK FORMAT</font><br>";
				switch($format) {
					case "yyyy-mm-dd":
					case "yy-mm-dd":
					case 'yy/mm/dd;@':
						$y = 0;
						$m = 1;
						$d = 2;
						break;
					case "mm-dd-yy":
					case "m/d/yy":
						$y = 2;
						$m = 0;
						$d = 1;
						break;
					case "d-m-y":
					case "dd/mm/yy":
					case "d/m/y":
						$y = 2;
						$m = 1;
						$d = 0;
						break;
					default:
						$has_format = false;
						break;
				}
			}
//			echo "D: $d - M: $m - Y: $y - SEP: $sep<br>";
//			echo "HAS FORMAT: $has_format<br>";

			if($has_format===true) {	
				$data_pz = array();
				if(strpos("/", $data)===false)
					$data_pz = explode("-",$data);
				else
					$data_pz = explode("/",$data);
//				echo "DATA PZ:<pre>"; print_r($data_pz); echo "</pre>";
	
				if(count($data_pz)==3) {
					$len = 2;
					if(strlen($data_pz[$d])>2)
						$len = $data_pz[$d];
					$dd = sprintf("%0".$len."s", $data_pz[$d]);
		
					$len = 2;
					if(strlen($data_pz[$m])>2)
						$len = $data_pz[$m];
					$mm = sprintf("%0".$len."s", $data_pz[$m]);
		
					$yy = $data_pz[$y];
					$data = $dd."/".$mm."/".$yy;
//					echo "<font color='blue'>DATA FORMAT: $data</font><br>";
				}
			}
		}
/*	
		if($data=="" ||
			(checkDateFormat($data, "SHORT_DATE")===false && checkDateFormat($data, "DATE")===false)
		) {
//			echo "<font color='red'>FALSE</font><br>";
			return false;
		}
		else {
//			echo "<font color='red'>";
			if(checkDateFormat($data, "SHORT_DATE")===true) {
//				echo "SHORT_DATE";
				$date = date6to8(str_replace("/","",$data));
			}
			else if(checkDateFormat($data, "DATE")===true) {
//				echo "DATE";
				$date = dateViewToModel($data);
			}
			else if(checkDateFormat($data, "DATE_STRING")===true) {
//				echo "STRING DATE";
				$date = $data;
			}
//			echo " - $date</font><br>";
	
			return $date;
		}
*/
		$date = check_data_by_format($data);
		
		return $date;
	}
	
	class ChunkReadFilter implements PHPExcel_Reader_IReadFilter {
		private $_startRow = 0;
		private $_endRow = 0;
	
		//  Set the list of rows that we want to read
		public function setRows($startRow, $chunkSize) {
			$this->_startRow = $startRow;
			$this->_endRow = $startRow + $chunkSize;
		}
/*
		public function readCell($column, $row, $worksheetName = '') {
			//  Only read the heading row, and the rows that are configured in $this->_startRow and $this->_endRow
			if (($row == 1) || ($row >= $this->_startRow && $row < $this->_endRow)) {
				return true;
			}
		 
			return false;
		}
*/
		public function readCell($column, $row, $worksheetName = '') {
			//  Only read the rows that are configured in $this->_startRow and $this->_endRow
			if($row >= $this->_startRow && $row < $this->_endRow) {
				return true;
			}
	
			return false;
		}
	}
	
	function trans_coord($col, $row, $block_col=false, $block_row=false) {
		$col_block = "";
		if($block_col===true)
			$col_block = "$";
			
		$row_block = "";
		if($block_row===true)
			$row_block = "$";
			
		$coord = $col_block.PHPExcel_Cell::stringFromColumnIndex($col).$row_block.$row;
	
		return $coord;
	}