<?php

	require_once "batch_commons.php";

	require_once $routine_path."/classi/wi400Batch.cls.php";
	
	$timestamp_unix = mktime(date('H'),date('i'),0,date('m'),date('d'),date('Y'));
	
	$sql = "select * from ZSCHDJOB where STATO='1' and FIRING<=$timestamp_unix AND STATO <>'0'";
	$handle = fopen("laststart.log", "w+");
	fwrite($handle, date("H:i:s d/m/Y", $timestamp_unix));
	fclose($handle);
//	echo "SQL: $sql<br>";
	
	$result = $db->query($sql);
	while($row = $db->fetch_array($result)) {
//		echo "<b>ID: ".$row['ID']."</b><br>";
		
//		echo "TIME: ".wi400_format_UNIX_TIMESTAMP($timestamp_unix);
//		echo " >= FIRING: ".wi400_format_UNIX_TIMESTAMP($row['FIRING']);
//		echo "<br>";
		
		$XML_path = $row['XML'];
		
//		echo "XML PATH: $XML_path<br>";
		
		if(!file_exists($XML_path))
			continue;
		$dom_xml = load_XML_file($XML_path);
		
		// Estrazione dei dati di interesse dalla response XML
		$params = array();
		$params = parse_XML_file($dom_xml);
			
		//throw_soap_fault($params);		
//	echo "PARAMS: "; print_r($params); echo "<br>";
		$server = getServerAddress(True);
		$url = "http://".$server.$appBase;
		$batch = new wi400Batch($_SESSION['user'], "*JOBD", "JOBSCD", $url);
		if(!isset($params['action']) || empty($params['action']))
			continue;
		$batch->setAction($params['action'],$params['form']);
			
		foreach($params as $key => $val) {
			$batch->addParameter($key, $val);
		}
		
		// prossima esecuzione
		if($row['FREQUENZA']=="*ONETIME") {
			$firing_unix = 0;
			$stato = "E";
		}
		else if(in_array($row['FREQUENZA'],array("*DAILY","*WEEKLY"))) {
			$date_exe = mktime(date('H',$row['FIRING']),date('i',$row['FIRING']),0,date('m'),date('d'),date('Y'));
			$firing_unix = $date_exe+$row['INTERVALLO'];
			$stato = "1";
		}
		else {
			$firing_unix = $timestamp_unix + $row['INTERVALLO'];
			$stato = '1';
		}
		
//		echo "FIRING: $firing_unix - DATA: ".date("d/m/Y H:i",$firing_unix)."<br>";
		
		$num_ex = $row['NUMERO_ESECUZIONI']+1;
		
		// Aggiornamento dei dati del lavoro
		$keys = array("ID"=>$row['ID']);
		$fields = array("FIRING","NUMERO_ESECUZIONI","LAST_FIRING","STATO");
		$campi = array($firing_unix,$num_ex,$timestamp_unix,$stato);
		
		$stmt = $db->prepare("UPDATE", "ZSCHDJOB", $keys, $fields);
		$update_res = $db->execute($stmt, $campi);
		
		$result_batch = $batch->call($connzend);
		
		if($messageContext->getSeverity()=="SUCCESS")
			$messageContext->addMessage("SUCCESS", "Lanciato il lavoro ".$row['ID']);
		else
			$messageContext->addMessage("ERROR", "Errore nel lancio del lavoro ".$row['ID']);
	}
	
	if(!in_array($messageContext->getSeverity(),array("SUCCESS","ERROR")))
		$messageContext->addMessage("ALERT", "Non è stato lanciato nessun lavoro");
		
?>