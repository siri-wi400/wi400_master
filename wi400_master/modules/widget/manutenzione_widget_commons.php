<?php

	function getWidgetObj($azione, $progressivo) {
		global $moduli_path, $messageContext;
		
		$widget = $azione;
		$row = rtvAzione($widget);
		
		if(is_bool($row)) {
			$messageContext->addMessage("WARNING", "Azione del widget: $widget non abilitata per questo utente.");
			return "AZIONE NON ABILITATA";
		}
		
		//$path_file = $moduli_path."/".$row['MODULO']."/widget/".$widget."_widget.cls.php";
		$path_file = p13n("modules/".$row['MODULO']."/widget/".$widget."_widget.cls.php");
		if(file_exists($path_file)) {
			require_once $moduli_path."/".$row['MODULO']."/widget/".$widget."_widget.cls.php";
		}else {
			$messageContext->addMessage("ERROR", "File widget $widget non trovato!");
			return "NON ESISTE";
		}
		$classe = strtoupper($widget."_WIDGET");
		$object = new $classe($progressivo);
		
		return $object;
	}

	function check_param($riga) {
		global $db;
		
		static $stmt_check_param;
		
		if (!isset($stmt_utente)) {
			$sql = "SELECT COUNT(*) CONT FROM ZWIDGPRM WHERE WIDUSR='{$riga['WIDUSR']}' and WIDAZI='{$riga['WIDAZI']}' and WIDPRG=".$riga['WIDPRG']." and WIDRIG=0";
			$stmt_check_param = $db->prepareStatement($sql);
		}
		$object = getWidgetObj($riga["WIDAZI"], $riga['WIDPRG']);
		if(!is_object($object)) return 'N';
		$parametri = $object->getUserParameters();
		$rs = $db->execute($stmt_check_param, array());
		$cont_param = $db->fetch_array($stmt_check_param);
		
		if(empty($parametri) || count($parametri) == $cont_param['CONT']) {
			return "S";
		}else {
			return "N";
		}
	}
	
	$users_table = $settings['table_prefix']."USERS";