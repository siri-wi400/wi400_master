<?php
	
//	require p13n("/modules/query/query_tool_common_markers_pers.php");
	require p13nPackage("query_tool_common_markers_pers", "querymarker");

	$marker_sep = "|";

	$array_markers = array(
		"STRING" => "Stringa",
		"STRING_NO_QUOTES" => "Stringa senza apici",
		"INTEGER" => "Numero Intero",
		"DOUBLE" => "Numero con Decimali",
		"DATA" => "Data",
		"DB_DATA" => "Data ISO",
		"SELECT" => "Selezioni",
		"SELECT_NO_QUOTES" => "Selezioni senza apici",
		"SESSION[user]" => "Utente attuale",
		"SESSION[cliente]" => "Cliente associato all'utente",
		"SESSION[locale]" => "Locale associato all'utente",
	);
//	echo "LISTA MARKERS:<pre>"; print_r($array_markers); echo "</pre>";

	if(isset($array_markers_pers) && !empty($array_markers_pers)) {
		$array_markers = array_merge($array_markers, $array_markers_pers);
//		echo "LISTA MARKERS:<pre>"; print_r($array_markers); echo "</pre>";
	}
	
	$no_apici_array = array(
		"INTEGER",
		"DOUBLE",
		"FILE",
		"LIBRARY",
		"TABLE",
		"STRING_NO_QUOTES",
		"SELECT_NO_QUOTES"
	);
//	echo "MARKERS SENZA APICI:<pre>"; print_r($no_apici_array); echo "</pre>";

	if(isset($no_apici_array_pers) && !empty($no_apici_array_pers)) {
		$no_apici_array_pers = array_merge($no_apici_array, $no_apici_array_pers);
//		echo "MARKERS SENZA APICI:<pre>"; print_r($no_apici_array); echo "</pre>";
	}

	$val_select_array = array(
		"SELECT",
		"SELECT_NO_QUOTES",
	);
//	echo "MARKERS CON SELEZIONE A TENDINA:<pre>"; print_r($val_select_array); echo "</pre>";
	
	$val_set_array = array(
		"SESSION[user]",
		"SESSION[cliente]",
		"SESSION[locale]",
	);
//	echo "MARKERS CON VALORE GIA' SETTATO:<pre>"; print_r($val_set_array); echo "</pre>";
	
	$hidden_array = array(
		"SESSION[user]",
		"SESSION[cliente]",
		"SESSION[locale]",
	);	
//	echo "MARKERS NASCOSTI:<pre>"; print_r($hidden_array); echo "</pre>";

	$has_params_array = array();
//	echo "MARKERS CON PARAMETRI:<pre>"; print_r($has_params_array); echo "</pre>";
	
	if(isset($has_params_array_pers) && !empty($has_params_array_pers)) {
		$has_params_array = array_merge($has_params_array, $has_params_array_pers);
//		echo "MARKERS CON PARAMETRI:<pre>"; print_r($has_params_array); echo "</pre>";
	}

	$array_options = array();
	foreach($array_markers as $key => $val) {
		$des = $key;
		
		if(in_array($key, $hidden_array)) {
			$des .= " (*NASCOSTO*)";
		}
		else if(in_array($key, $val_select_array)) {
			$des .= " (*SELEZIONE*)";
		}
		else if(in_array($key, $has_params_array)) {
			$des .= " (*PARAMETRI*)";
		}
		
		$array_options[$key] = $val." -> ".$des;
	}
//	echo "ARRAY OPTIONS:<pre>"; print_r($array_options); echo "</pre>";

	if(isset($array_options_pers) && !empty($array_options_pers)) {
		$array_options = array_merge($array_options, $array_options_pers);
//		echo "ARRAY OPTIONS:<pre>"; print_r($array_options); echo "</pre>";
	}
	
	$struttura_marker = array(
		"TIPO",							// OBBLIGATORIO
		"DES",							// OBBLIGATORIO
		"MULTI",
		"UPPER",
		"SELECT_STR",
		"NOT_REQUIRED",
		"NOT_HIDDEN",
		"PARAMS_STR",
			"COND_STR",
			"LEGAME_STR"
	);	
//	echo "STRUTTURA MARKERS:<pre>"; print_r($struttura_marker); echo "</pre>";