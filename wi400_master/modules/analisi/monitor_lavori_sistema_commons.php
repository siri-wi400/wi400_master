<?php

//	$tabella = "LUCALAV/ZMONUSRJ";
	$tabella = "ZMONUSRJ";
	
	$intervalli_array = array(
		"1H" => "1 Ora",
		"1G" => "1 Giorno",
		"1S" => "1 Settimana",
		"1M" => "1 Mese",
		"1A" => "1 Anno"
	);
	
	$int_len = array(
		"1H" => 13,
		"1G" => 10,
		"1M" => 7,
		"1A" => 4
	);
	
	$int_axis_array = array(
		"1H" => "Ore",
		"1G" => "Giorni",
		"1S" => "Settimane",
		"1M" => "Mesi",
		"1A" => "Anni"
	);

	$int_date_array = array(
		"1H" => "TIMESTAMP",
		"1G" => "SHORT_TIMESTAMP",
		"1S" => "WEEK_YEAR",
		"1M" => "TIMESTAMP_MONTH",
		"1A" => ""
	);
	
	$tipo_dati_array = array(
		"MEDIA" => "Medie",
		"PICCO" => "Picchi"
	);
	
	function interpret_data($data, $tipo_int) {
		switch($tipo_int) {
			case "1H":
				$data .= ".00.000000";
				$data = wi400_format_TIMESTAMP($data);
				break;
			case "1G":
				$data = str_replace("-","",$data);
				$data = dateModelToView($data);
				break;
			case "1S":
				$data = sprintf("%02s", trim(substr($data,4)))." ".sprintf("%04s",substr($data,0,4));
				break;
			case "1M":
//				$data = sprintf("%02s", substr($data,5,2))."/".sprintf("%04s",substr($data,0,4));
				$data = ucfirst(nome_mese(substr($data,5,2)))." ".sprintf("%04s",substr($data,0,4));
				break;
		};
		
		return $data;
	}
	
	function get_data_func($tipo_int) {
		global $int_len;
		
		$func = "";
		switch($tipo_int) {
			case "1H":
			case "1G":
			case "1M":
//			case "1A":
				$len = $int_len[$tipo_int];
				$func = "substr(char(montim), 1, $len)";
				break;
			case "1A":
				$func = "year(montim)";
				break;
			case "1S":
				$func = "year(montim)!!week(montim)";
				break;
		}
		
		return $func;
	}

	function get_sql($int_ini, $int_fin, $sel_subsys, $tipo_int, $tipo_dati=array()) {
		global $tabella;
		
		$data = get_data_func($tipo_int);
		
		$campo = "";
		if(in_array("MEDIA",$tipo_dati)) {
			$campo .= ",avg(monnal) as MEDIA";
		}
		if(in_array("PICCO",$tipo_dati)) {
			$campo .= ",max(monnal) as PICCO";
		}
		
		$sql = "SELECT $data as data,monsbs $campo
				FROM $tabella
				WHERE monsbs in ('".implode("', '", $sel_subsys)."') and
					substr(char(montim), 1, 10) between '".substr($int_ini,0,10)."' and '".substr($int_fin,0,10)."' and 
					substr(char(montim), 12, 8) between '".substr($int_ini,11,8)."' and '".substr($int_fin,11,8)."'
				GROUP BY $data,monsbs
				ORDER BY $data,monsbs";
				
		return $sql;
	}

?>