<?php

	$classi_conv_array = get_classi_conv();

	$orientamento_array = array(
		"P" => _t("PORTRAIT"),
		"L" => _t("LANDSCAPE"),
		"A" => _t("AUTOMATICO")
	);
	
	$si_no_array = array(
		"S" => _t("SI"),
		"N" => _t("NO")
	);
	
	$font_array = array(
		"*DEFAULT" => "*DEFAULT",
		"courier" => "Courier",
		"helvetica" => "Helvetica",
		"times" => "Times New Roman",
		"symbol" => "Symbol",
		"E8_50" => "Ean 8"
	);
	
	$formato_pagina_array = array(
		"A5" => "Formato A5",
		"A4" => "Formato A4",
		"A3" => "Formato A3",
		"Letter" => "Lettera",
		"Legal" => "Legale"
	);
	
	$um_array = array(
		"mm" => "Millimetri",
		"cm" => "Centimetri",
		"pt" => "Punti",
		"in" => "Pollici"
	);
	
	$action_icons_array = array(
		"DETTAGLIO",
		"ELIMINA",
		"COPIA"
	);
	
	$des_icons_array = array(
		"DETTAGLIO" => "Dettaglio",
		"ELIMINA" => "Elimina",
		"COPIA" => "Copia"
	);
	
	$type_icons_array = array(
		"DETTAGLIO" => "SEARCH",
		"ELIMINA" => "BIN",
		"COPIA" => "COPY"
	);
	
	function get_modello($modello) {
		global $db;
		
		static $stmt_mod;
		
		if(!isset($stmt_mod)) {
			$sql = "select * from SIR_MODULI where MODNAM=?";
//			echo "SQL: $sql<br>";

			$stmt_mod = $db->singlePrepare($sql, 0, true);
		}
		
		$res_mod = $db->execute($stmt_mod, array($modello));
		
		$row_mod = $db->fetch_array($stmt_mod);
		
		return $row_mod;
	}
	
	// Scansione della directory routine/classi/pers e creazione delle opzioni basate sui file personalizzati presenti
	function get_classi_conv() {
		global $base_path, $settings;
	
		$path = $base_path."/package/".$settings['package'].'/persconv';
		$dir = opendir("$path");
		 
		$modelli = array();

		$modelli["*DEFAULT"] = "*DEFAULT";
		
		while($file = readdir($dir)) {
			if(is_file("$path/$file") && strncmp($file,"wi400SpoolCvt_",14)==0) {
				$fileName = basename($file, ".cls.php");
				
				if(strpos($fileName, ".cls.php")!==false)
					continue;
				
				$model = substr($fileName,14);
				$modelli[$model] = $model;
			}
		}
		
		ksort($modelli);
		
		return $modelli;
	}