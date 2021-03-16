<?php

	function detail_markers($idDetailMarker, $query_libera) {
		global $array_campi;
		global $array_markers, $array_options;
//		global $hidden_array, $val_select_array;

		$detailAction = new wi400Detail($idDetailMarker, false);
		$detailAction->setTitle("Markers");
		
		// Campo
		if($query_libera===false) {
			$mySelect = new wi400InputSelect('IN_CAMPO');
			$mySelect->setLabel("In campo");
			$mySelect->addValidation('required');
			$mySelect->setFirstLabel("Seleziona...");
			$mySelect->setOptions($array_campi);
//			$mySelect->setValue($in_campo);
			$detailAction->addField($mySelect);
		}
		
		// Tipo di Marker
		$mySelect = new wi400InputSelect('TIPO_MARKER');
		$mySelect->setLabel("Tipo Marker");
		$mySelect->addValidation('required');
		$mySelect->setFirstLabel("Seleziona...");
//		$mySelect->setOptions($array_markers);		
		$mySelect->setOptions($array_options);
//		echo "OPTIONS:<pre>"; print_r($mySelect->getOptions()); echo "</pre>";
//		$mySelect->setValue($tipo_marker);
		$detailAction->addField($mySelect);
		
		// Descrizione Marker
		$myField = new wi400InputText('DES_MARKER');
		$myField->setLabel('Descrizione Marker');
		$myField->setSize(50);
		$myField->setMaxLength(50);
//		$myField->setCase("UPPER");
//		$myField->setValue($des_marker);
		$detailAction->addField($myField);
		
		$myField = new wi400InputSwitch("MULTI");
		$myField->setLabel("Valori Multipli");
		$myField->setOnLabel(_t('LABEL_YES'));
		$myField->setOffLabel(_t('LABEL_NO'));
//		$myField->setChecked($check_multi);
		$myField->setValue(1);
		$detailAction->addField($myField);
		
		$myField = new wi400InputSwitch("UPPER");
		$myField->setLabel("Solo Maiuscole");
		$myField->setOnLabel(_t('LABEL_YES'));
		$myField->setOffLabel(_t('LABEL_NO'));
//		$myField->setChecked($check_upper);
		$myField->setValue(1);
		$detailAction->addField($myField);
		
		$myField = new wi400InputText('SELECT_STR');
		$myField->setLabel('Selezione di valori');
		$myField->setSize(50);
//		$myField->setMaxLength(50);
//		$myField->setCase("UPPER");
		$myField->setInfo("Inserire i valori in formato <VALORE1>=<DESCRIZIONE1>;<VALORE2>=<DESCRIZIONE2>");
//		$myField->setValue($select_str);
		$detailAction->addField($myField);
		
		// Campo vuoto ammesso => In questo caso il valore nella query sarà '' (in caso di stringa)
		$myField = new wi400InputSwitch("NOT_REQUIRED");
		$myField->setLabel("Campo Vuoto Ammesso");
		$myField->setOnLabel(_t('LABEL_YES'));
		$myField->setOffLabel(_t('LABEL_NO'));
//		$myField->setChecked($check_not_required);
		$myField->setValue(1);
		$detailAction->addField($myField);
		
		// Mostra campo nascosto => In questo caso i campi dell'array $hidden_array non verranno nascosti
		$myField = new wi400InputSwitch("NOT_HIDDEN");
		$myField->setLabel("Mostra campo nascosto");
		$myField->setOnLabel(_t('LABEL_YES'));
		$myField->setOffLabel(_t('LABEL_NO'));
//		$myField->setChecked($check_not_hidden);
		$myField->setValue(1);
		$detailAction->addField($myField);
		
		// Parametri (per lookup, decode)
		$myField = new wi400InputText('PARAMS_STR');
		$myField->setLabel('Parametri');
		$myField->setSize(50);
//		$myField->setMaxLength(50);
//		$myField->setCase("UPPER");
		$myField->setInfo("Inserire i valori in formato <PARAM1>:<VALORE1>;<VALORE2>|<PARAM2>:<VALORE3>;<VALORE4>");
//		$myField->setValue($params_str);
		$detailAction->addField($myField);
		
		if($query_libera===false) {
			$myField = new wi400InputText('COND_STR');
			$myField->setLabel('Condizione');
			$myField->setSize(50);
//			$myField->setMaxLength(50);
//			$myField->setCase("UPPER");
			$myField->setInfo("Inserire la condizione da legare al marker utilizzando $$ per indicare dove va il valore");
//			$myField->setValue($cond_str);
			$detailAction->addField($myField);
/*			
			$myField = new wi400InputText('LEGAME_STR');
			$myField->setLabel('Legame condizione');
			$myField->setSize(50);
//			$myField->setMaxLength(50);
//			$myField->setCase("UPPER");
			$myField->setInfo("Inserire il legame da applicare alla condizione");
//			$myField->setValue($legame_str);
			$detailAction->addField($myField);
*/			
			$mySelect = new wi400InputSelect('LEGAME_STR');
			$mySelect->setLabel('Legame condizione');
			$mySelect->setFirstLabel("Seleziona...");
//			$mySelect->setOptions($array_legami);
			$mySelect->addOption("and", "and");
			$mySelect->addOption("or", "or");
			$mySelect->setInfo("Inserire il legame da applicare alla condizione");
//			$mySelect->setValue($legame_str);
			$detailAction->addField($mySelect);
		}
		
		$detailAction->dispose();
		
		return $detailAction;
	}
	
	function check_markers($string, $markers) {
		global $array_markers;
		global $hidden_array, $hidden_markers;
	
//		echo "STRING: $string<br>";
	
		$pos_m = strpos($string, "##");
		if($pos_m!==false) {
			$pos_f = strpos($string, "##", $pos_m+2);
	
			$len = $pos_f-($pos_m+2);
//			echo "POS M: $pos_m - POS F: $pos_f - LEN: $len<br>";
	
			$mark = substr($string, $pos_m+2, $len);
//			echo "MARK: $mark<br>";
	
			$parts = get_marker_parts($mark);
	
			if(array_key_exists($parts['TIPO'], $array_markers)) {
				if(!in_array($mark, $markers)) {
					$markers[] = $mark;
						
					if(in_array($parts['TIPO'], $hidden_array) && $parts['NOT_HIDDEN']=="") {
						$hidden_markers++;
					}
				}
			}
	
			$string = substr($string, $pos_f+2);
	
			$markers = check_markers($string, $markers);
		}
	
		return $markers;
	}
	
	function get_marker_values($markers, $idDetailMarkers, $hidden_markers) {
		$marker_values = array();
		
		foreach($markers as $mark) {
			$field_id = get_marker_field_id($mark);
//			echo "FIELD ID: $field_id<br>";
		
			if(!is_null(wi400Detail::getDetailValue($idDetailMarkers, $field_id))) {
				$marker_values[$mark] = wi400Detail::getDetailValue($idDetailMarkers, $field_id);
			}
			else {
				$parts = get_marker_parts($mark);
				$tipo = $parts['TIPO'];
		
				$val = replace_val_set_markers($tipo);
		
				if($val!==false) {
					$marker_values[$mark] = $val;
				}
			}
		}
		
//		echo "<font color='green'>MARKER VALUES:</font><pre>"; print_r($marker_values); echo "</pre>";
		
		if(count($markers)>$hidden_markers && count($marker_values)==$hidden_markers) {
			$marker_values = array();
		}
			
//		echo "<font color='green'>MARKER VALUES:</font><pre>"; print_r($marker_values); echo "</pre>";

		return $marker_values;
	}
	
	function get_marker_field_id($marker) {
		global $marker_sep;
/*
		$field_id = str_replace($marker_sep, "_", $marker);
		$field_id = str_replace(" ", "_", $field_id);
*/
		$marker_parts = get_marker_parts($marker);
		$field_id = $marker_parts["TIPO"]."_".$marker_parts["DES"];
		$field_id = str_replace(array("."), "", $field_id);
		$field_id = str_replace(array(" ", "'", "[", "]"), "_", $field_id);
	
//		echo "FIELD ID: $field_id<br>";
	
		return $field_id;
	}
	
	function get_marker_code($idDetailMarker) {
		global $messageContext;
		global $struttura_marker, $marker_sep;
		
		$marker_parts = get_marker_code_parts($idDetailMarker);
		
		$marker = "";
		if(!empty($struttura_marker)) {
			foreach($struttura_marker as $part) {
				if($marker=="") {
					$marker .= "##";
				}
				else {
					$marker .= $marker_sep;	
				}
				
				$val = "";
				if(isset($marker_parts[$part])) {
					$val = $marker_parts[$part];
				}
				else {
					$messageContext->addMessage("ERROR", "Manca l'elemento $part della struttura del marker");
				}
				
				$marker .= $val;
			}
			
			$marker .= "##";
		}
//		echo "MARKER: $marker<br>";
		
		return $marker;
	}
	
	function get_marker_code_parts($idDetailMarker) {
		global $array_markers;
		
		// Tipo Marker - OBBLIGATORIO
		$tipo_marker = wi400Detail::getDetailValue($idDetailMarker, "TIPO_MARKER");
		
		// Descrizione Marker - OBBIGATORIO
		$des_marker = wi400Detail::getDetailValue($idDetailMarker, "DES_MARKER");
		if($des_marker=="")
			$des_marker = $array_markers[$tipo_marker];
		
//		echo "IN CAMPO: $in_campo<br>";
//		echo "TIPO MARKER: $tipo_marker - DES MARKER: $des_marker<br>";
		
		// Valori Multipli
		$check_multi = get_switch_bool_value($idDetailMarker, "MULTI");
//		echo "CHECK MULTI: $check_multi<br>";
		
		$multi = "";
		if($check_multi==true)
			$multi = "S";
//		echo "MULTI: $multi<br>";
		
		// Solo Maiuscole
		$check_upper = get_switch_bool_value($idDetailMarker, "UPPER");
//		echo "CHECK UPPER: $check_upper<br>";
		
		$upper = "";
		if($check_upper==true)
			$upper = "S";
//		echo "UPPER: $upper<br>";
		
		// Selezione di valori
		$select_str = wi400Detail::getDetailValue($idDetailMarker, "SELECT_STR");
//		echo "SELECT: $select_str<br>";
		
		// Campo Vuoto Ammesso
		$check_not_required = get_switch_bool_value($idDetailMarker, "NOT_REQUIRED");
//		echo "CHECK NOT REQUIRED: $check_not_required<br>";
		
		$not_required = "";
		if($check_not_required==true)
			$not_required = "S";
//		echo "NOT REQUIRED: $not_required<br>";
		
		// Mostra campo nascosto
		$check_not_hidden = get_switch_bool_value($idDetailMarker, "NOT_HIDDEN");
//		echo "CHECK NOT HIDDEN: $check_not_hidden<br>";
		
		$not_hidden = "";
		if($check_not_hidden==true)
			$not_hidden = "S";
//		echo "NOT HIDDEN: $not_hidden<br>";
		
		// Parametri
		$params_str = wi400Detail::getDetailValue($idDetailMarker, "PARAMS_STR");
//		echo "PARAMS: $params_str<br>";

		// Condizione
		$cond_str = wi400Detail::getDetailValue($idDetailMarker, "COND_STR");
//		echo "CONDIZIONE: $cond_str<br>";

		// Legame
		$legame_str = wi400Detail::getDetailValue($idDetailMarker, "LEGAME_STR");
//		echo "LEGAME: $legame_str<br>";

		$marker_parts = array(
			"TIPO" => $tipo_marker,							// OBBLIGATORIO
			"DES" => $des_marker,							// OBBLIGATORIO
			"MULTI" => $multi,
			"UPPER" => $upper,
			"SELECT_STR" => trim($select_str),
			"NOT_REQUIRED" => $not_required,
			"NOT_HIDDEN" => $not_hidden,
			"PARAMS_STR" => trim($params_str),
				"COND_STR" => trim($cond_str),
				"LEGAME_STR" => trim($legame_str),
		);
		
		return $marker_parts;
	}
	
	function get_marker_parts($marker) {
		global $marker_sep, $struttura_marker;
		global $array_markers, $val_set_array;
		global $readonly;
		
		$parts = explode($marker_sep, $marker);
//		echo "PARTS:<pre>"; print_r($parts); echo "</pre>";

		foreach($struttura_marker as $key => $campo) {
			$pos = array_search($campo, $struttura_marker);
/*
			if(!array_key_exists($pos, $parts))
				continue;
			
			$val = $parts[$pos];
*/				
			$val = "";
			if(array_key_exists($pos, $parts))
				$val = $parts[$pos];
//			echo "VAL: $val<br>";
			
			$marker_parts[$campo] = $val;
		}
		
//		if($marker_parts['DES']=="" && in_array($marker_parts['TIPO'], $val_set_array)) {
		if($marker_parts['DES']=="" && array_key_exists($marker_parts['TIPO'], $array_markers)) {
			$marker_parts['DES'] = $array_markers[$marker_parts['TIPO']];
		}
		
		if($readonly==true) {
			$marker_parts['NOT_HIDDEN'] = "";
		}
		
//		echo "MARKER PARTS:<pre>"; print_r($marker_parts); echo "</pre>";
		
		return $marker_parts;
	}
	
	function replace_markers($string, $marker_values) {
		global $no_apici_array;
		global $query_libera;
	
		$marker_legame = array();
		$i = 0;
		foreach($marker_values as $mark => $value) {
			$parts = get_marker_parts($mark);
	
			$tipo = $parts['TIPO'];
				
			if(is_array($value)) {
				if(in_array($tipo, $no_apici_array)) {
					$value = implode(", ", $value);
				}
				else {
					$value = "'".implode("', '", $value)."'";
				}
			}
			else {
				if($tipo=="DATA") {
					$value = dateViewToModel($value);
				}
				else if($tipo=="DB_DATA") {
					$value = dateViewToModel($value);
					$value = "'".dateToDBdate($value)."'";
				}
				else if(!in_array($tipo, $no_apici_array)) {
					$value = "'".$value."'";
				}
/*				
				else {
					$not_required = $parts['NOT_REQUIRED'];
						if($value=="" && $not_required!="") {
						$value = 0;
					}
				}
*/				
			}
			
			if($query_libera===false) {
				if(!empty($parts['COND_STR'])) {
					$cond_str = $parts['COND_STR'];
//					echo "COND STR: $cond_str<br>";
					
					if($parts['NOT_REQUIRED']=="S" && $value=="''") {
						$cond_str = "";
					}
					else {
						$cond_str = str_replace("$$", $value, $cond_str);
					}
//					echo "COND STR - REPLACED: $cond_str<br>";
					
					$value = $cond_str;
//					echo "MARKER  VALUE: $value<br>";

					if(!empty($parts['LEGAME_STR'])) {
						$legame_str = $parts['LEGAME_STR'];
//						echo "LEGAME STR: $legame_str<br>";
					
						$marker_legame[] = array(
							"COND" => $value,
							"LEGAME" => $legame_str
						);
						
						$string = str_replace("##".$mark."##", "**".$i, $string);
						
						$i++;
					}
				}
			}
				
			$string = str_replace("##".$mark."##", $value, $string);
//			echo "STRING: $string<br>";
		}
		
		if(!empty($marker_legame)) {
			while(strpos($string, "**")!==false) {			
				$pos = strpos($string, "**");
				
				$part_1 = substr($string, 0, $pos);
//				echo "PART 1: $part_1<br>";
				
				$legame = substr($string, $pos+2, 1);
//				echo "LEGAME: $legame<br>";
				
				$part_2 = substr($string, $pos+3);
//				echo "PART 2: $part_2<br>";
				
				$string = "";
				if(trim($part_1)=="") {
					$string = $marker_legame[$legame]['COND'];
					$string .= $part_2;
				}
				else {
					$string = $part_1;
					
					if($marker_legame[$legame]['COND']!="") {
						if(substr(trim($part_1), -1)!="(") {
							$string .= $marker_legame[$legame]['LEGAME']." ";
						}
						
						$string .= $marker_legame[$legame]['COND'];
					}
					
					if(substr(trim($part_2), 0, 2)!="**" &&
						!in_array(substr(trim($part_2), 0, 1), array("", ")")) && 
						substr(trim($part_1), -1)!="("
					) {
						$string .= " ".$marker_legame[$legame]['LEGAME'];
					}
					
					$string .= $part_2;
				}
				
//				echo "<font color='red'>STRING LEGAME:</font> $string<br>";
			}
			
//			echo "STRING: $string<br>";
		}
			
		return $string;
	}
	
	function replace_val_set_markers($tipo) {
		global $val_set_array;
		
		if(in_array($tipo, $val_set_array)) {
			$arg = substr($tipo, strlen("SESSION_"), -1);
			$val = $_SESSION["$arg"];
//			echo "SESSION ARG: $arg - VALUE: $val<br>";
			
			return $val;
		}
		
		return false;
	}
	
	function replace_comments($string) {
//		echo "STRING: $string<br>";
		
		$com_i = "<!--";
		$com_f = "--!>";
	
		$pos_m = strpos($string, $com_i);
		if($pos_m!==false) {
			$pos_f = strpos($string, $com_f, $pos_m);
	
			$len = ($pos_f+strlen($com_f))-$pos_m;
//			echo "POS M: $pos_m - POS F: $pos_f - LEN: $len<br>";
	
			$comment = substr($string, $pos_m, $len);
//			echo "COMMENT: $comment<br>";
	
			$string = str_replace($comment, "", $string);
	
			$string = replace_comments($string);
			$string = trim($string);
		}
//		echo "STRING: $string<br>";
	
		return $string;
	}