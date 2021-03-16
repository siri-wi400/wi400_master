<?php
global $paramall, $MARK,$CODE, $INTERNALKEY, $CODE_MESSAGE, $OUTPUT_TEMPLATE, $DATASET, $DATASET_DES, $ATTRIBUTES, $ENTITA, $DES_ENTITA, $ATTRIBUTES;

$OUTPUT_TEMPLATE='<?xml version="1.0" encoding="ISO-8859-1" ?> 
<resource entityId="##ENTITA##" entityDesc="##DES_ENTITA##">
<state code="##CODE##" message="##CODE_MESSAGE##" /> 
<datasetList>
<dataset id="##DATASET##" value="##DATASET_DES##">
<attributes>
##ATTRIBUTE##
</attributes>
</dataset>
</datasetList>
</resource>';
$MARK = array('##ENTITA##',
		'##DES_ENTITA##',
		'##CODE##',
		'##CODE_MESSAGE##',
		'##DATASET##',
		'##DATASET_DES##',
		'##ATTRIBUTE##'
		);
$ATTRIBUTES ="";
$DES_ENTITA = "COMMWRS";


/**
 * @desc Controllo se la connessione e stata stabilita precedentemente
 * @param string $id: codice connessione
 * @param string $ip: indirizzo ip relativo alla connessione
 * @return boolean
 */
function checkConnection($id, $ip, $ENTITA, $DATASET, $tipo) {
	global $db;
	
	$sql = "SELECT * FROM FWW1SESS WHERE SESID='$id' and SESIPA='$ip'";
	$result = $db->query($sql);
	$row = $db->fetch_array($result);
	if (!$row) {
		return false;
	}
	if ($row['SESSTA']!='1') {
		return false;
	}
	$timstp = date("Y-m-d-H.i.s.00000");
	// Aggiorno il db con data ultima connessione del client
	$sql = "UPDATE FWW1SESS SET SESTSL='$timstp', SESLSO='$ENTITA-$DATASET-$tipo' WHERE SESID='$id' and SESIPA='$ip'";
	$result = $db->query($sql);	
	return true;
}
/**
 * @desc Compongo l'XML standard di risposta
 * @return string
 */
function componiXML() {
	global $paramall, $AA, $MARK,$CODE, $CODE_MESSAGE, $OUTPUT_TEMPLATE, $DATASET, $DATASET_DES, $ATTRIBUTES, $ENTITA, $DES_ENTITA;
	//$handle = fopen("/www/testluca.txt", "w+");
	//fwrite($handle, $ENTITA. $DES_ENTITA. $CODE. $CODE_MESSAGE. $DATASET. $DATASET_DES. $ATTRIBUTES);
	//fclose($handle);
	if (isset($paramall['jsonEncodeFast']) AND strtoupper($paramall['jsonEncodeFast'])=="TRUE") {
		$AA['code']=$CODE;
		//$AA['message']=$MESSAGE;
		$AA['entita']=$ENTITA;
		$AA['dataset']=$DATASET;
		return $AA;
	} 	elseif (isset($paramall['plainText']) AND strtoupper($paramall['plainText'])=="TRUE") {
		$AA['code']=$CODE;
		//$AA['message']=$MESSAGE;
		$AA['entita']=$ENTITA;
		$AA['dataset']=$DATASET;
		return $AA;
	} else {
		$replace = array($ENTITA, $DES_ENTITA, $CODE, $CODE_MESSAGE, $DATASET, $DATASET_DES, $ATTRIBUTES);
		$xml = str_replace($MARK, $replace, $OUTPUT_TEMPLATE);
		$xml = str_replace("> <", "><", $xml);
		$xml = str_replace(">  <", "><", $xml);
		$xml = str_replace("&", "&amp;", $xml);
		return $xml;
	}
}
/**
 * @desc Compongo la stringa con gli attributi ritornati
 * @param array $AA: array associativo con attributi: Key/Valore
 * @return string
 */
function esplodiDati($AA) {
	$stringa = "";
	foreach ($AA as $key => $value) {
		$stringa .='<attribute id="'.$key.'" value="'.$value.'" />';
	}
	return $stringa;
}
function wrs_logout($id) {
	global $INTERNALKEY, $db;
	$InputXML = '<?xml version="1.0"?>';
	$InternalKey = $INTERNALKEY;
	$ControlKey="*immed";
	$OutputXML = '';
	$callPGM = $db->getCallPGM();
	$db->bind_param ($callPGM, 1, "InternalKey", DB2_PARAM_IN );
	$db->bind_param ($callPGM, 2, "ControlKey", DB2_PARAM_IN );
	$db->bind_param ($callPGM, 3, "InputXML", DB2_PARAM_IN );
	$db->bind_param ($callPGM, 4, "OutputXML", DB2_PARAM_OUT );
	$ret = db2_execute($callPGM);
	$timstp = date("Y-m-d-H.i.s.00000");
	// Aggiorno il db con data ultima connessione del client
	$sql = "UPDATE FWW1SESS SET SESTSL='$timstp', SESSTA='9' WHERE SESID='$id'";
	$result = $db->query($sql);
}
/**
 * Recupero le informazioni sulla configurazione del WS se previste
 * @param unknown $entita
 * @param unknown $segmento
 */
function wrs_getWSInfo($entita, $segmento) {
	global $db;
	$cache_file = wi400File::getCommonFile("wsinfo", "WS_".$entita."-".$segmento.".txt");
	$parametri_array = fileSerialized($cache_file);
	// Se non ci sono i dati li creo e poi li serializzo
	if ($parametri_array ==null) {
		// Reperisco le info
		$parametri_array=array();
		// Recupero i Parametri di testata .. mi conviene fare il giro in testata e ripassare su righe
		$query = "SELECT * FROM FWSDPARM WHERE ASEENT='$entita' AND ASECOD=''";
		$result = $db->query($query);
		while ($row = $db->fetch_array($result)) {
			$parametri_array[$row['ASEPRM']]=$row['ASEVAL'];
		}
		// Recupero i Parametri di dettaglio, sovrascrivo eventualmente quelli di testata 
		$query = "SELECT * FROM FWSDPARM WHERE ASEENT='$entita' AND ASECOD='$segmento'";
		$result = $db->query($query);
		while ($row = $db->fetch_array($result)) {
			if ($row['ASEVAL']!="") {
				$parametri_array[$row['ASEPRM']]=$row['ASEVAL'];
			}
		}
		// Adesso carico i dati di input e OUTPUT
		$query = "SELECT * FROM FWSPINOU WHERE ASEENT='$entita' AND ASECOD='$segmento' ORDER BY ASESEQ ASC";
		$result = $db->query($query);
		while ($row = $db->fetch_array($result)) {
			$parametri_array['PARAMETRI'][$row['ASENAM']]=$row;
		}
		put_serialized_file($cache_file, $parametri_array);
	}
	return $parametri_array;
}
function wrs_write_log($file, $log_rif, $dati) {
	$log_msg = $log_rif;
	if(is_array($dati) && !empty($dati)) {
		$log_msg .= " - ".implode("-", $dati);
	}
	else if(!is_array($dati)){
		$dati = trim($dati);
		if($dati!="")
			$log_msg .= " - ".$dati;
	}
	
	$log_msg .= "\r\n";
	
	$log_handle = fopen($file, "a");
	fwrite($log_handle, $log_msg);
	fclose($log_handle);
}