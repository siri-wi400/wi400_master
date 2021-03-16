<?php

	$gerarchie_classiche = array(
		"1" => "Rifornibilità/Costi/Prezzi",
		"3" => "Proprietà",
		"4" => "Contingentamento"
	);
	
	function get_ger_elem($sheet, $ger_array, $livello, $codice, $riga) {
		$key = "l".$livello;
		$val = "vals".$livello;
		
		if($codice!="")
			$sel_array = $ger_array[$livello][$codice];
		else
			$sel_array = $ger_array[$livello];
		
		if(!isset($sel_array))
			return;
		
		foreach($sel_array as $key => $val) {
			$col = 2*($livello-1);
			
			$sheet->setCellValueByColumnAndRow($col,$riga, $key);
			$sheet->setCellValueByColumnAndRow($col+1,$riga++, $val);
			
			if($livello<count($ger_array) && isset($ger_array[$livello+1][$key]))
				$riga = get_ger_elem($sheet, $ger_array, $livello+1, $key, $riga);
		}
		
		return $riga;
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