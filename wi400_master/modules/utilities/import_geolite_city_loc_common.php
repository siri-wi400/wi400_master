<?php

	$tabella = "FZZ1COUN";
//	echo "TABELLA IMP: $tabella<br>";

	$array_campi = array(
		"CODE",
		"LANGUAGE",
		"CONT",
		"DESCONT",
		"COUNTRY",
		"DESCOUNT",
		"REGION",
		"DESREGION",
		"SUBREG",
		"DESSUB1",
		"CITY",
		"CITYCODE",
		"TIMEZONE",
		"ISEUROPE",
	);
	
	$from = $tabella;
	
	$campiFile = getDs($tabella);
//	echo "CAMPI FILE:<pre>"; print_r($campiFile); echo "</pre>";
	
	$campi_tab = $db->columns($tabella);
//	echo "CAMPI TAB:<pre>"; print_r($campi_tab); echo "</pre>";

//	$ins_prepare = false;		// true: insert righe con prepare, false: insert righe con query diretta

	$tipo_imp_array = array(
		"GEOLITE" => "GeoLite2-City-Locations-en",
		"COMUNI" => "Elenco-comuni-italiani"
	);

//	$csv_as_txt = true;
	
	function get_val_array($val, $array_campi, $csv_as_txt=false) {
//		echo "VAL: "; var_dump($val); echo "<br>";
		
//		echo "ENCODING: ".mb_detect_encoding($val)."<br>";

//		$val = str_replace(array('"'), "", $val);
//		echo "VAL: $val<br>";
		
		$str = $val;
		
		if($csv_as_txt===true) {
			$pos_1 = strpos($str, '"');
			if($pos_1===0) {
				$pos_2 = strpos($str, ',');
				$str_1 = substr($str, 0, $pos_2);
//				echo "STR: $str_1<br>";
				if($str_1!=='"') {
					$str_2 = strrpos($str, '"');
					$str = substr($str, 1, $str_2-1);
					
					$str = str_ireplace('""', '"', $str);
//					echo "<font color='green'>NEW STR:</font> $str<br>";
				}
			}
		}
		
//		echo "ENCODING: ".mb_detect_encoding($str)."<br>";
		
//		$str = prepare_string($str);

//		$str = utf8_encode($str);
		
//		$str = mb_convert_encoding($str, "HTML-ENTITIES", 'UTF-8');

//		echo "<font color='violet'>CLEAN VAL: $str</font><br>";
		
		$str = strtoupper($str);
		$str = fullUpper($str);
//		echo "VAL UPPER: $str<br>";

		$val_array = array();
		
//		$val_array = explode(",", $val);
//		echo "VAL ARRAY:<pre>"; print_r($val_array); echo "</pre>";
	
		$pos_1 = strpos($str, '"');
		while($pos_1!==false) {
			if($pos_1!==0) {
				$split_1 = substr($str, 0, $pos_1-1);
//				echo "SPLIT_1: $split_1<br>";
		
				$split_array = explode(",", $split_1);
				$val_array = array_merge($val_array, $split_array);
			}
				
			$split = substr($str, $pos_1+1);
//			echo "SPLIT: $split<br>";
				
			$pos_2 = strpos($split, '"');
				
			$split_2 = substr($split, 0, $pos_2);
//			echo "SPLIT_2: $split_2<br>";
				
			$val_array[] = $split_2;
				
			$split_3 = substr($split, $pos_2+2);
//			echo "SPLIT_3: $split_3<br>";
				
			$str = $split_3;
			$pos_1 = strpos($str, '"');
		}
		
		if($str!="") {
			$split_array = explode(",", $str);
			$val_array = array_merge($val_array, $split_array);
		}
		
//		echo "<font color='blue'>VAL ARRAY:<pre>"; print_r($val_array); echo "</font></pre>";
		
		$val_array = array_combine($array_campi, $val_array);
//		echo "<font color='orange'>VAL ARRAY:<pre>"; print_r($val_array); echo "</font></pre>";

		if($val_array["COUNTRY"]==="IT") {
			return false;
		}
		
		if($val_array["COUNTRY"]==="IT") {
			if(stripos($val_array["DESSUB1"], "PROVINCE OF")!==false) {
				$new_str = str_ireplace("PROVINCE OF", "PROVINCIA DI", $val_array["DESSUB1"]);
		
//				echo "<font color='orange'>PROVINCE OF:</font> ".$val_array[0]." - $new_str<br>";
		
				$val_array["DESSUB1"] = $new_str;
		
//				echo "<font color='green'>VAL ARRAY:<pre>"; print_r($val_array); echo "</font></pre>";
			}
		}
		
		if($val_array['SUBREG']=="")
			$val_array['SUBREG'] = $val_array['REGION'];
		
		if($val_array['DESSUB1']=="")
			$val_array['DESSUB1'] = $val_array['DESREGION'];
		
		return $val_array;
	}
	
	function insert_val_array($val_array, $campiFile, $from, $error, $ins_prepare) {
		global $db;
		global $messageContext;
		
		static $stmt_ins;
		
//		echo "INS_PREPARE: "; var_dump($ins_prepare); echo "<br>";
		if($ins_prepare===true) {
			if(!isset($stmt_ins)) {
				$stmt_ins = $db->prepare("INSERT", $from, null, array_keys($campiFile));
			}
			
//			$result = write_file_row_prepare($from, $campiFile, $val_array);
				
			$result = $db->execute($stmt_ins, $val_array);
		}
		else if($ins_prepare===false) {
/*
			$campi = array_combine(array_keys($campiFile), $val_array);
//			echo "CAMPI:<pre>"; print_r($campi); echo "</pre>";
				
			$result = write_file_row($from, $campiFile, $campi);
*/
			$sql_ins = "INSERT INTO $from
				(".implode(",", array_keys($campiFile)).")
				VALUES ('".implode("','", $val_array)."')";
//			echo "SQL_INS: $sql_ins<br>";
				
			$result = $db->query($sql_ins);
		}
//		echo "RESULT: "; var_dump($result); echo "<br>";
		
		if(!$result) {
			$error = true;
			$messageContext->addMessage("ERROR","Errore durante l'inserimento della riga con CODE: ".$val_array["CODE"]);
//break;
		}
		
		return $error;
	}
	
	function get_val_array_it($cod_reg, $cod_met, $cod_prov, $prog_comu, $cod_comu, $city, $des_reg, $des_met, $des_prov, $cod_auto, $campiFile, $csv_as_txt=false) {
		if(empty($city))
			return false;
		
		if(empty($cod_met) || $cod_met==="-") {
			$cod_met = "000";
		}
		
		$code = $cod_reg.$cod_met.$cod_prov.$prog_comu.$cod_comu;
		
		if(empty($des_prov) || $des_prov==="-") {
			$des_prov = $des_met;
		}
		
		$val_array = $campiFile;
		
		$val_array["CODE"] = $code;
		$val_array["LANGUAGE"] = "it";
		$val_array["CONT"] = "EU";
		$val_array["DESCONT"] = "Europa";
		$val_array["COUNTRY"] = "IT";
		$val_array["DESCOUNT"] = "Italia";
		$val_array["REGION"] = $cod_reg;
		$val_array["DESREGION"] = $des_reg;
		$val_array["SUBREG"] = $cod_auto;
		$val_array["DESSUB1"] = $des_prov;
		$val_array["CITY"] = $city;
		$val_array["CITYCODE"] = $cod_comu;
		$val_array["TIMEZONE"] = "Europa/Roma";
		$val_array["ISEUROPE"] = "1";
		
		foreach($val_array as $k => $v) {
//			$v = prepare_string($v);
			
			$v = utf8_encode($v);
						
			$v = strtoupper($v);
			$v = fullUpper($v);
//			echo "VAL UPPER: $v<br>";
		
			$val_array[$k] = $v;
		}
		
		return $val_array;
	}