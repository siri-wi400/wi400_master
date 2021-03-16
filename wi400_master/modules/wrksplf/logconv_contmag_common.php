<?php

	$stampata_array = array(
		"S" => "Solo stampate",
		"N" => "Solo NON stampate"
	);
	
	$mod_conv_abil = array(
		"SCHEDAMAG" => "N",
		"DFTARC" => "N"
	);
	
	$mod_outq_abil = array(
//		"FATTURESAV",
//		"FATTUREPDF"
	);
	
	function get_titolo_chiave_ric($i) {
		$title = "Chiave Ricerca $i";
		$title = "";
		
		$des_array = array(
			1 => "Fattura",
			2 => "Data",
			3 => "Cliente",
			4 => "Partita IVA"	
		);
		
		if(array_key_exists($i, $des_array))
			$title = $des_array[$i];
		
		return $title;
	}
	
	function get_titolo_chiave_user($i) {
//		$title = "Chiave Ricerca $i";
		$title = "";
	
		$des_array = array(
			1 => "Società",
//			2 => "Nazione",
			3 => "Importo"
		);
	
		if(array_key_exists($i, $des_array))
			$title = $des_array[$i];
	
		return $title;
	}