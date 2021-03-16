<?php

	$template_path =  $base_path."/package/siad/templatefield";

	function deleteCacheSysParameter() {
		global $settings;
		
		$files = glob($settings['data_path']."COMMON\\usercache\\*"); // get all file names
		foreach($files as $file) {
			if(is_file($file))
				unlink($file);
		}
	}
	
	function getFieldFromParam($parametro, $row = false) {
		global $db;
		//require_once 'op_resi_vuoti_commons.php';
		
		if($parametro) {
			$query = "SELECT * FROM ZTABTABE WHERE ELEMENTO='$parametro'";
			$rs = $db->singleQuery($query);
			$row = $db->fetch_array($rs);
		}
		
		$wi400 = $row['VALORE1'];
		$flag_exist = false;
		if(class_exists($wi400) || function_exists($wi400)) { 
			$flag_exist = true;
		}else {
			$wi400 = "wi400InputText";
			$row['VALORE2'] = "string";
		}

		if($row['ELEMENTO2'] == "classe" || !$flag_exist) {
			try {
				$myField = new $wi400("VALORE");
				if($row['VALORE2'] != "string") {
					$myField->addValidation($row['VALORE2']);
				}
				if($row['VALORE1'] == "wi400InputCheckBox" || $row['VALORE1'] == "wi400InputSwitch") {
					//$myField->setUncheckedValue("0");
					$myField->setValue("1");
					$myField->setChecked(false);
					if(isset($row['DEFAULT'])) {
						if($row['DEFAULT']) {
							$myField->setChecked(true);
						}
					}
				}
				$myField->setLabel("Valore");
				$size = $row['VALORE3'] ? $row['VALORE3'] : 50;
				$myField->setSize($size);
				$myField->setMaxLength($size);
			}catch (Exception $e) {
				$myField = new wi400InputText("VALORE");
			}
		}else {
			$myField = new $wi400("VALORE");
		}
		
		return $myField;
	}
	
	function getDefinizioneTabella($societa, $tabella) {
		global $db;
		
		$sql = "SELECT * FROM ZTABTABE WHERE SOCIETA='$societa' and TABELLA='$tabella' AND TIPO='D'";
		$rs = $db->singleQuery($sql);
		$row = $db->fetch_array($rs);
		
		return $row;
	}