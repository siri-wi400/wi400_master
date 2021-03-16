<?php

	$tabella = "ZWIDETPA";
	$tabGestioneParametri = "ZTABTABE";

	// 0 => abilitato, 1=> abilita default value, 2 => default value
	$conf_all = array(
		'*all_lista' => array('Reload'=> array(true, 0, ""), 
							'Filtri'=> array(true, 0, ""),
							'Tree'=> array(true, 0, ""),
							'Esportazione'=> array(true, 0, ""),
							'Sql'=> array(true, 0, ""),
							'Configura lista'=> array(true, 0, ""),
							'Numero righe'=> array(true, 0, 10),
							'Filtro testata'=> array(true, 0, ""),
							'Scroll testata'=> array(true, 0, ""),
							'Nascondi lista'=> array(0, 1, "CLOSE")
		),
		'*all_detail' => array('Detail saved' => array(0, 0, ""), 
								'Only detail'=> array(0, 0, ""),
								'Nascondi detail'=> array(0, 1, "CLOSE")),
	);

	function creazioneAllGenerale($setting_all, $tipo) {
		global $db, $conf_all;
		
		static $stmt_all_list, $stmt_all_detail, $stmt_exits_all_generico;
		
		$widazi = "*ALL_DETAIL";
		if($tipo == "L")
			$widazi = "*ALL_LISTA";
		
		$file = "ZWIDETPA";
			
		$fieldInsert = getDs($file);
		$fieldInsert['WIDAZI'] = $widazi;
		$fieldInsert['WIDDOL'] = $tipo;
		$fieldInsert['WIDID'] = "*ALL";
		$fieldInsert['WIDDED'] = $tipo == "L" ? "Tutte le liste" : "Tutti i detail";
		$fieldInsert['WIDKEY'] = "*ALL";
		$fieldInsert['WIDABI'] = 1;
		$fieldInsert['WIDHID'] = 0;
		$fieldInsert['WIDDFT'] = 0;
		$fieldInsert['WIDDFV'] = "";
		$fieldInsert['WIDTYP'] = "TOOL";
		$fieldInsert['WIDSTA'] = 1;
		
		if(!isset($stmt_all_list) || !isset($stmt_all_detail)) {
			if($tipo == "L") {
				$stmt_all_list = $db->prepare("INSERT", $file, null, array_keys($fieldInsert));
			}else {
				$stmt_all_detail =  $db->prepare("INSERT", $file, null, array_keys($fieldInsert));
			}
		}
		
		$query = "SELECT * FROM ZWIDETPA WHERE WIDAZI='$widazi' AND WIDDOL='$tipo' AND WIDREQ=?";
		$stmt_exits_all_generico = $db->prepareStatement($query);
		
		foreach($conf_all[$setting_all] as $ele => $val) {
			$rs = $db->execute($stmt_exits_all_generico, array($ele));
			if(!$row = $db->fetch_array($stmt_exits_all_generico)) {
				$fieldInsert['WIDREQ'] = $ele;
				$fieldInsert['WIDABI'] = $val[0];
				$fieldInsert['WIDDFT'] = $val[1];
				$fieldInsert['WIDDFV'] = $val[2];
				
				if($tipo == "L") {
					$db->execute($stmt_all_list, $fieldInsert);
				}else {
					$db->execute($stmt_all_detail, $fieldInsert);
				}
			}
		}
	}
	
	function elimina_cache() {
		global $settings;
		
		$dir = $settings['data_path']."COMMON/checkFieldEnabled/";
		
		if(is_dir($dir)) {
			delete_dir_files($dir);
		}
	}
	
	function getKeyValue($azione, $tipo, $id, $key, $fieldId, $select = "*") {
		global $db;
		
		$where = array("WIDAZI='$azione'", "WIDDOL='$tipo'", "WIDID='$id'", "WIDKEY='$key'", "WIDREQ='$fieldId'");
		$rs = $db->singleQuery("SELECT $select FROM ZWIDETPA WHERE ".implode(" and ", $where));
		$row = $db->fetch_array($rs);
		
		//showArray($row);
		
		return $row;
	}
	
	function getOrdinamentoCol($azione, $id, $key) {
		global $db, $tabella;
		
		$sql = "SELECT WIDREQ, WIDSEQ FROM $tabella 
				WHERE WIDAZI='$azione' and WIDDOL='L' and WIDID='$id' and WIDKEY='$key' and WIDTYP='COLUMN'
				ORDER BY WIDSEQ";
		$rs = $db->query($sql);
		$dati = array();
		while($row = $db->fetch_array($rs)) {
			$dati[$row['WIDREQ']] = $row['WIDSEQ'];
		}
		
		return $dati;
	}
	
	function nascostoReadOnly($row) {
		//if($row["WIDTYP"])
		if($row["WIDDOL"] == 'L' ) {
			$hide = $row['WIDTYP'] == "COLUMN" ? false : true;
		}else {
			$hide = false;
		}
		
		return $hide;
	}
	
	function functionUpdateRow(wi400List $wi400List, $request) {
		global $db;
	
		$chiave = $request['LIST_KEY'];
		$dati = explode("|", $chiave);
		
		$row = $wi400List->getCurrentRow();
		$abilita_default = $row['WIDDFT'];
		
		$tipo = $request['WIDDOL_INFO'] == 'Lista' ? 'L' : 'D';
		
		$where = array("WIDAZI" => $request['WIDAZI_INFO'], "WIDDOL" => $tipo, "WIDID" => $request['WIDID_INFO'], "WIDKEY" => $request['WIDKEY_INFO'], "WIDREQ" => $dati[0]);
		
		$stmt_update_abil = $db->prepare("UPDATE", "ZWIDETPA", $where, array("WIDDFT"));
		$rs = $db->execute($stmt_update_abil, array($abilita_default));
		
		elimina_cache();
	
		return $wi400List;
	}
	
	function functionFormattazioneInput(wi400List $wi400List, $inputField, $row) {
		
		require_once 'manutenzione_parametri_commons.php';
		
		$arr_id = explode("-", $inputField->getId());
		$colonna = $arr_id[2];
		
		$myField = getFieldFromParam(false, $row);
		$myField->setId($inputField->getId());
		
		$inputField = $myField;
		
		/*if($row['VALORE1'] == 'wi400InputCheckBox') {
			$myField = new wi400InputCheckbox($inputField->getId());
			$myField->setValue("1");
			$myField->setChecked(false);
			if(isset($row['DEFAULT']) && $row['DEFAULT']) {
				$myField->setChecked(true);
			}
			
			$inputField = $myField;
		}*/
		
		return $inputField;
	}
	
	function getValueParam($row) {
		
		if($row['WIDDFT']) {
			return $row['WIDDFV'];
		}
		
		return $row['DEFAULT'];
	}
	
	function checkValoreDefault($row) {
		if($row['WIDDFT']) {
			return 'N';
		}
		
		return 'S';
	}
	
	function getTracciatoParamAzione($azione, $utente, $parametro) {
		global $db, $tabella, $tabGestioneParametri;
		
		$sql = "SELECT * 
				FROM $tabGestioneParametri left join $tabella on TABELLA=WIDAZI AND WIDDOL='P' AND WIDKEY='$utente' AND ELEMENTO=WIDREQ  
				WHERE TABELLA='$azione' AND ELEMENTO='$parametro'
				ORDER BY WIDSEQ";

		$rs = $db->query($sql);
		$dati = array();
		while($row = $db->fetch_array($rs)) {
			$dati[] = $row;
		}
		
		return $dati;
	}
	
	/*function getTracciatoGestioneParametro($azione, $parametro) {
		global $db, $tabGestioneParametri;
		
		$sql = "SELECT * FROM $tabGestioneParametri WHERE TABELLA='$azione' AND ELEMENTO='$parametro'";
		$rs = $db->singleQuery($sql);
		$row = $db->fetch_array($rs);
		
		
		return $row;
	}*/
	
	function getParamConfigurati() {
		global $db, $tabGestioneParametri;
		
		$sql = "SELECT * FROM ".$tabGestioneParametri." WHERE TABELLA='PARMAZI'";
		$rs = $db->query($sql);
		
		$param = array();
		while($row = $db->fetch_array($rs)) {
			$param[] = $row;
		}
		
		return $param;
	}
	
	function insertParametro($widazi, $widkey, $nome, $valore, $sequenza = '0') {
		global $db, $tabella, $messageContext;
		
		static $stmt_insert_new_param;
		
		$fields_file = array(
			"WIDAZI" => $widazi,
			"WIDDOL" => "P",
			"WIDID" => "",
			"WIDKEY" => $widkey,
			"WIDREQ" => $nome,
			"WIDABI" => "1",
			"WIDDFT" => '1',
			"WIDDFV" => $valore,
			"WIDSEQ" => $sequenza,
			"WIDSTA" => "1"
		);
		
		if(!isset($stmt_insert_new_param)) {
			$stmt_insert_new_param = $db->prepare("INSERT", $tabella, null, array_keys($fields_file));
		}
		
		$result = $db->execute($stmt_insert_new_param, $fields_file);
		if(!$result) {
			$messageContext->addMessage("ERROR", "Errore creazione nuovo parametro nel gruppo $widkey!");
		}
		
		return $result;
	}
	
	function deleteParametro($widazi, $widkey, $nome) {
		global $db, $tabella, $messageContext;
		
		$where = array(
			"WIDAZI='$widazi'",
			"WIDDOL='P'",
			"WIDID=''",
			"WIDKEY='$widkey'",
			"WIDREQ='$nome'"
		);
		
		$sql = "DELETE FROM $tabella WHERE ".implode(" AND ", $where);
		$rs = $db->query($sql);
		if(!$rs) {
			$messageContext->addMessage("ERROR", "Errore update parametro!");
		}
		
		return $rs;
	}