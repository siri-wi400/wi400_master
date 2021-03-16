<?php

	// Stati Elaborazione Batch
	$statoBatchArray = array(
		"1" => _t('IN ATTESA DI ESECUZIONE'),
		"2" => _t('IN ESECUZIONE'),
		"E" => _t('COMPLETATO CON ERRORI'),
		"9" => _t('COMPLETATO')
	);

	$statoBatchColor = array(
		"1" => "wi400_grid_gray",
		"2" => "wi400_grid_blu",
		"E" => "wi400_grid_red",
		"9" => "wi400_grid_green"
	);

	$statoBatchScheduled = array(
		"1" => "Valido",
		"0" => "Sospeso",
		"E" => "Eseguito"
	);
	
	$statoBatchScheduledColor = array(
		"0" => "wi400_font_red",
		"1" => "wi400_font_green",
		"E" => "wi400_font_blue"
	);
	
	$frequenza_array = array(
		"*DAILY" => "Giornaliera",
		"*WEEKLY" => "Settimanale",
		"*ONETIME" => "Un solo lancio",
		"*TIME" => "Dopo un certo intervallo di tempo"
	);
	
	$readOnlyArray = array(
		"*DAILY" => true,
		"*WEEKLY" => true,
		"*ONETIME" => true,
		"*TIME" => false
	);
	
	$des_status = array(
		"*ACTIVE" => "ATTIVO",
		"*OUTQ" => "TERMINATO",
		"ERRORE" => "ERRORE",
		"SYSTEM" => "SCHEDULATORE SISTEMA"	
	);
	
	$col_status = array(
		"*ACTIVE" => "green",
		"*OUTQ" => "red",
		"ERRORE" => "red",
		"SYSTEM" => "blue"
	);
	
	function parse_XML_file($dom) {
		$array = array();
	
		// Cerco se c'è il tag parametro 
		$params = $dom->getElementsByTagName('parametro'); // Find Sections
		
		if(!isset($params)) 
			return false;
		
		foreach($params as $key => $param) {
			$array[$params->item($key)->getAttribute('id')] = $params->item($key)->getAttribute('value');
		}
		
		return $array;
	}
	
	function create_XML_file($id_array, $value_array) {
		$dom = new DomDocument('1.0');
		
		// Non si possono avere più di un elemento di 1° livello
		// Creazione del tag 'parametri' di 1° livello
		$parametri = $dom->appendChild($dom->createElement('parametri'));
		// Creazione dei tag 'parametro' di 2° livello con attributi
		
		foreach($id_array as $key => $id_val) {
			$parametro = $parametri->appendChild($dom->createElement('parametro'));
			
			$field_name = $dom->createAttribute('id'); 
			$parametro->appendChild($field_name);
			$name = $dom->createTextNode($id_val);
			$field_name->appendChild($name);
			
			$field_name = $dom->createAttribute('value'); 
			$parametro->appendChild($field_name);
			$name = $dom->createTextNode($value_array[$key]);
			$field_name->appendChild($name);
		}
		
		$dom->formatOutput = true;
		$XML_code =  $dom->saveXML();
		
		return $XML_code;
	}
	
	function start_batch_scheduler() {
		global $settings;
		
//		$result = executeCommand('SBMJOB CMD(CALL PGM(PHPLIB/ZBATCHSCD)) JOB(PHPSCDE) JOBQ('.$settings['jobq'].') SYSLIBL(*CURRENT) CURLIB(*CURRENT) INLLIBL(PHPLIB ZENDCORE)');
		$result = executeCommand('SBMJOB CMD(CALL PGM('.$settings['db_name'].'/ZBATCHSCD)) JOB(PHPSCDE) JOBQ('.$settings['jobq'].') SYSLIBL(*CURRENT) CURLIB(*CURRENT) INLLIBL(PHPLIB ZENDCORE)');
		
		return $result;
	}
	
	function stop_batch_scheduler($parameter) {
		$job_name = trim(substr($parameter,0,10));
		$user = trim(substr($parameter,10,10));
		$job_num = trim(substr($parameter,20));
//		echo "JOB NAME: $job_name - USER: $user - JOB NUM: $job_num<br>";
		
		$result = executeCommand('ENDJOB JOB('.$job_num.'/'.$user.'/'.$job_name.') OPTION(*IMMED)');
		
		return $result;
	}
	
	function freeze_batch_scheduler($parameter) {
		$job_name = trim(substr($parameter,0,10));
		$user = trim(substr($parameter,10,10));
		$job_num = trim(substr($parameter,20));
//		echo "JOB NAME: $job_name - USER: $user - JOB NUM: $job_num<br>";
		
		$result = executeCommand('HLDJOB JOB('.$job_num.'/'.$user.'/'.$job_name.')');
		
		return $result;
	}
	
	function release_batch_scheduler($parameter) {
		$job_name = trim(substr($parameter,0,10));
		$user = trim(substr($parameter,10,10));
		$job_num = trim(substr($parameter,20));
//		echo "JOB NAME: $job_name - USER: $user - JOB NUM: $job_num<br>";
		
		$result = executeCommand('RLSJOB JOB('.$job_num.'/'.$user.'/'.$job_name.')');
		
		return $result;
	}
	
	function update_stato_batch_scheduler($ID, $stato, $tabella="ZSCHDJOB") {
		global $db;
		
		// Impostazione della condizione WHERE
		$keys = array("ID"=>$ID);
		$fields = array("STATO");
		$campi = array($stato);

		$stmt = $db->prepare("UPDATE", $tabella, $keys, $fields);
		$result = $db->execute($stmt, $campi);
		
		return $result;
	}
	
?>