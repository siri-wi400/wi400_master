<?php

	$tabSettings = "ZTABSETP";
	$tabValori = "ZTABSETV";

	$ambiente = strtoupper(substr($appBase, 1, -1));
	
	function deleteCacheSysParameter() {
		global $settings;
	
		$files = glob($settings['data_path']."COMMON\\usercache\\*"); // get all file names
		foreach($files as $file) {
			if(is_file($file))
				unlink($file);
		}
	}
	
	function getFieldFromParam($parametro) {
		$row = getRowSettings($parametro);
		
		$wi400 = $row['OGGETTO'];
		$flag_exist = false;
		if(class_exists($wi400) || function_exists($wi400)) {
			$flag_exist = true;
		}else {
			$wi400 = "wi400InputText";
			$row['FORMATO'] = "string";
		}
	
		if($row['TIPO'] == "classe" || !$flag_exist) {
			$myField = new $wi400("VALORE");
			if(!in_array($row['FORMATO'], array("string", "array"))) {
				$myField->addValidation($row['FORMATO']);
			}
			if($row['OGGETTO'] == "wi400InputCheckBox" || $row['OGGETTO'] == "wi400InputSwitch") {
				$myField->setUncheckedValue("0");
				$myField->setValue("1");
			}
			$myField->setLabel("Valore");
			$myField->setMaxLength($row['LUNGHEZZA'] ? $row['LUNGHEZZA'] : 50);
			$myField->setSize($row['LUNGHEZZA'] ? $row['LUNGHEZZA'] : 50);
		}else {
			$myField = $wi400("VALORE");
		}
	
		return $myField;
	}
	
	function createVariableSettings() {
		global $db, $tabSettings, $tabValori, $settings, $appBase, $ambiente;
		
		$sql = "select *                                       
				from $tabSettings a inner join $tabValori b on a.parametro=b.parametro
				where b.ambiente='$ambiente' and a.stato='1'
				order by A.PARAMETRO, PGR";
		$rs = $db->query($sql);
		
		$sett = array();
		while($row = $db->fetch_array($rs)) {
			$parametro = $row['PARAMETRO'];
			$valore = $row['VALORE'];
			$formato = $row['FORMATO'];
			if($formato == "array") {
				if(!isset($sett[$parametro])) $sett[$parametro] = array();

				if($row['CHIAVE']) {
					$sett[$parametro][$row['CHIAVE']] = $valore;
				}else {
					$sett[$parametro][] = $valore;
				}
			}else if($formato == "integer") {
				if($row['OGGETTO'] == "wi400InputCheckBox") {
					$sett[$parametro] = $valore ? true : false;
				}else {
					$sett[$parametro] = intval($valore);
				}
			}else {
				$sett[$parametro] = $valore;
			}
		}
		
		//showArray($sett);
		/*$file = file($settings['doc_root'].$appBase."conf/wi400.conf.php");
		
		$file[0] = "";
		$file = implode(" ", $file);
		$file = substr($file, 14);
		eval("\$parameters=".$file);
		
//		$settings = $parameters;
		
		foreach($sett as $chiave => $val) {
			if($settings[$chiave] === $val) {
				
			}else {
				if(is_array($val)) {
					if($settings[$chiave] == $val) {
						
					}else {
						echo $chiave."____no_array<br/>";
					}
				}else {
					echo $chiave."____noooo__".gettype($settings[$chiave])."____".gettype($val)."___".$settings[$chiave]."___".$val."<br/>";
				}
			}
		}*/
	}
	
	function getRowSettings($parametro) {
		global $db, $tabSettings;
		
		$query = "SELECT * FROM $tabSettings WHERE PARAMETRO='$parametro' and STATO='1'";
		$rs = $db->singleQuery($query);
		$row = $db->fetch_array($rs);
		
		return $row;
	}
	
	function getRowValori($parametro) {
		global $db, $tabValori, $ambiente;
	
		$query = "SELECT * FROM $tabValori WHERE AMBIENTE='$ambiente' and PARAMETRO='$parametro'";
		$rs = $db->singleQuery($query);
		$row = $db->fetch_array($rs);
	
		return $row;
	}
	
	function getAllRowValori($parametro) {
		global $db, $tabValori, $ambiente;
		
		static $stmt_all_valori;
		
		if(!isset($stmt_all_valori)) {
			$sql = "SELECT * FROM $tabValori WHERE AMBIENTE='$ambiente' and PARAMETRO=? ORDER BY PGR";
			$stmt_all_valori = $db->prepareStatement($sql);
		}
		
		$rs = $db->execute($stmt_all_valori, array($parametro));
		$dati = array();
		while($row = $db->fetch_array($stmt_all_valori)) {
			$dati[] = $row;
		}
		
		return $dati;
	}
	
	function getValoriInLista($row) {
		
		$html = "";
		//echo $row['PARAMETRO']."___".gettype($row['VALORE'])."<BR>";
		//showArray($row);
		if($row['EXIT'] == "S") {
			if($row['FORMATO'] == "array") {
				$dati = getAllRowValori($row['PARAMETRO']);
				$righe_val = array();
				foreach($dati as $valori) {
					$righe_val[] = $valori['VALORE'];
				}
				$html = implode("<br/>", $righe_val);
			}else {
				$html = $row['VALORE'];
			}
			
		}
		return $html;
	}
	
	function insertParametro($fields) {
		global $db, $tabSettings;
		
		static $stmt_insert_parm;
		
		if(!isset($stmt_insert_parm)) {
			$campi = getDs($tabSettings);
			$stmt_insert_parm =  $db->prepare("INSERT", $tabSettings, null, array_keys($campi));
		}
		
		$rs = $db->execute($stmt_insert_parm, $fields);
		
		return $rs;
	}
	
	function insertValore($valori) {
		global $db, $tabValori;
	
		static $stmt_insert_val;
	
		if(!isset($stmt_insert_val)) {
			$campi = getDs($tabValori);
			$stmt_insert_val =  $db->prepare("INSERT", $tabValori, null, array_keys($campi));
		}
	
		$rs = $db->execute($stmt_insert_val, $valori);
	
		return $rs;
	}
	
	function existParametro($parametro) {
		global $db, $tabSettings;
		
		static $stmt_exit_parm;
		
		if(!isset($stmt_exit_parm)) {
			$sql = "SELECT PARAMETRO FROM $tabSettings WHERE PARAMETRO=?";
			$stmt_exit_parm =  $db->prepareStatement($sql);
		}
		
		$rs = $db->execute($stmt_exit_parm, array($parametro));
		$row = $db->fetch_array($stmt_exit_parm);
		if($row) {
			return true;
		}else {
			return false;
		}
	}
	
	function existValore($parametro, $pgr_nuovo, $pgr_vecchio) {
		global $db, $tabValori, $ambiente;
	
		static $stmt_exit_val;
	
		if(!isset($stmt_exit_val)) {
			$sql = "SELECT VALORE FROM $tabValori WHERE AMBIENTE='$ambiente' and PARAMETRO=? and PGR=? and PRG<>?";
			$stmt_exit_val =  $db->prepareStatement($sql);
		}
	
		$rs = $db->execute($stmt_exit_val, array($parametro, $pgr_nuovo, $pgr_vecchio));
		$row = $db->fetch_array($stmt_exit_val);
		if($row) {
			return true;
		}else {
			return false;
		}
	}
	
	function getWi400ConfParameters() {
		global $settings, $appBase;
		
		$file = file($settings['doc_root'].$appBase."conf/wi400.conf.php");
		//$file = file($settings['doc_root']."/wi400_lzovi/conf/wi400.conf.php");
		
		$file[0] = "";
		$file = implode("", $file);
		$pos = strpos($file, '$settings');
		if($pos !== false) {
			$file = substr($file, $pos);
			eval("\$parameters=".$file);
		}else {
			$parameters = array();
		}
		
		return $parameters;
	}
	