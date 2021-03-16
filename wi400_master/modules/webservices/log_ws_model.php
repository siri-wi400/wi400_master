<?php
	
	$azione = $actionContext->getAction();
	
	$history->addCurrent();
	
	$entita = wi400Detail::getDetailValue($azione."_DET", 'ENTITA');
	$data_ric_ini = wi400Detail::getDetailValue($azione."_DET", 'DATA_RIC_INI');
	$ora_ini = wi400Detail::getDetailValue($azione."_DET", 'ORA_INI');
	$data_ric_fin = wi400Detail::getDetailValue($azione."_DET", 'DATA_RIC_FIN');
	$ora_fin = wi400Detail::getDetailValue($azione."_DET", 'ORA_FIN');
	$errCheck = wi400Detail::getDetailValue($azione."_DET", 'ERROR_WS');
	
	if($actionContext->getForm()=="DEFAULT") {
		$actionContext->setLabel("Parametri");
	}
	else if($actionContext->getForm()=="DETAIL") {
		$actionContext->setLabel("Dettaglio log");
		$key = getListKeyArray($azione."_LIST");
		$id = $key['LOGID'];
		
		$sql = "SELECT * FROM ZWEBSLOG WHERE LOGID='".$id."'";
		$result = $db->singleQuery($sql);
		$row = $db->fetch_array($result);
		
		$segmenti = explode(";", $row['LOGSEG']);
		//showArray($segmenti);
		
		$sql = "SELECT asedco FROM fasegmen WHERE aseent=? and asecod=?";
		$stmt = $db->prepareStatement($sql);
		$seg_con_desc = array();
		foreach($segmenti as $chiave => $valore) {
			$rs = $db->execute($stmt, array($row['LOGENT'], $valore));
			$jobArray = $db->fetch_array($stmt);
			$seg_con_desc[$valore] = $jobArray['ASEDCO'];
		}
		//showArray($seg_con_desc);
		
		$xml_in = "";
		if(file_exists($row['LOGXIN'])) {
			$xml_in = file_get_contents($row['LOGXIN']);
			//$xml_in = str_replace('"', "&quot;", $xml_in);
			$doc = new DomDocument('1.0');
			$doc->loadXML($xml_in, LIBXML_NOENT);
			$doc->preserveWhiteSpace = false;
			$doc->normalize();
			$doc->formatOutput = true;
			$xml_in = $doc->saveXML();
			//echo "<input type='text' name='ciao' value=\"".$xml_in."\" style='width: 400px;'><br/><br/>";
		}
		
		$xml_out = "";
		if(file_exists($row['LOGXOU'])) {
			$xml_out = file_get_contents($row['LOGXOU']);
			/*if($xml_out == "") {
				$xml_out = "is_emty"
			}*/
			$xml_out = str_replace('"', "&quot;", $xml_out);
			//echo "<input type='text' name='ciao' value=\"".$xml_out."\" style='width: 400px;'><br/><br/>";
		}
		$log_out = "";
		if(file_exists($row['LOGADL'])) {
			$log_out = file_get_contents($row['LOGADL']);
			$log_out = str_replace('"', "&quot;", $log_out);
		}
	}