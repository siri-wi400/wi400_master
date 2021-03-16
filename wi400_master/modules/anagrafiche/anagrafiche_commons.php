<?php
function decodeWriteFlag($flag) {
	$msg=array();
    switch($flag) {
    	case 0: $msg['sev'] = "SUCCESS"; $msg['msg'] = "record letto e bloccato/scritto/aggiornato"; break;
    	case 1: $msg['sev'] = "ERROR"; $msg['msg'] = "tentativo di scrivere una chiave doppia"; break;
    	case 2: $msg['sev'] = "ERROR"; $msg['msg'] = "record non letto e bloccato/scritto/aggiornato"; break;
    	case 3: $msg['sev'] = "ERROR"; $msg['msg'] = "record gia' bloccato da un altro programma"; break;
    }
    return $msg;
}
/**
 * Imposta FMDAANAR: valorizza il tracciato passato per la scrittura
 * 
 * @param $articoli array(): Tracciato articoli, potrebbe già essere parzialmente valorizzato
 * @param $dataRiferimento AAAAMMGG: Data riferimento per scrittura record
 * @param $dati array(): array per valorizzazione tracciato. Se non passato viene usato il $_POST
 * @param $context string: contesto di valorizzazione dei dati. 
 */
function impostaFMDAANAR($articolo, $dataRiferimento=null, $dati=null, $context='DEFAULT') {

     $valori=array();

     // Se non ho passato l'array con i dati da scrivere utilizzo il post
     if ($dati!=null) {
       $valori = $dati;
     } else {
 		$valori = $_POST;    
     }
	 // Valorizzo i campi modificati a video con i campi del tracciato di output
	 foreach($articolo as $key => $tracciato) {
			if(isset($valori[$key])) {
				$articolo[$key] = $valori[$key];
			}
		}
	 // Valorizzo la data di riferimento
	 $dataRecord = "";
     // Se non ho passato l'array con i dati da scrivere utilizzo il post
     if ($dataRiferimento!=null) {
       $dataRecord = $dataRiferimento;
     } else {
 	   $dataRecord = date("Ymd");    
     }
	 $articolo['MDAAVA']= substr($dataRecord,0,4);
	 $articolo['MDAMVA']= substr($dataRecord,4,2);
	 $articolo['MDAGVA']= substr($dataRecord,6,2);
	 // Impostazione default
	 $micro = substr(date("u"),0,3);
	 $articolo['MDAHMO']= date("His").$micro;
	 $articolo['MDADMO']= date("dmy");
	 $articolo['MDAUSR']= "000";
	 $articolo['MDAPID']= "000";
	 $articolo['MDAPGM']= "PHPANA";
	 // Altri dati
	 if ($articolo['MDASTA']=='') $articolo['MDASTA']='1';
	 $articolo['MDAST1']='1';
	 $articolo['MDAST2']='1';	 
	 
	 return $articolo;
}
/**
 * Imposta FMDCFISC: valorizza il tracciato passato per la scrittura
 * 
 * @param $articoli array(): Tracciato dati fiscali, potrebbe già essere parzialmente valorizzato
 * @param $dataRiferimento AAAAMMGG: Data riferimento per scrittura record
 * @param $dati array(): array per valorizzazione tracciato. Se non passato viene usato il $_POST
 * @param $context string: contesto di valorizzazione dei dati. 
 */
function impostaFMDCFISC($articolo, $dataRiferimento=null, $dati=null, $context='DEFAULT') {

     $valori=array();

     // Se non ho passato l'array con i dati da scrivere utilizzo il post
     if ($dati!=null) {
       $valori = $dati;
     } else {
 		$valori = $_POST;    
     }
	 // Valorizzo i campi modificati a video con i campi del tracciato di output
	 foreach($articolo as $key => $tracciato) {
			if(isset($valori[$key])) {
				$articolo[$key] = $valori[$key];
			}
		}
	 // Valorizzo la data di riferimento
	 $dataRecord = "";
     // Se non ho passato l'array con i dati da scrivere utilizzo il post
     if ($dataRiferimento!=null) {
       $dataRecord = $dataRiferimento;
     } else {
 	   $dataRecord = date("Ymd");    
     }
	 $articolo['MDCAVA']= substr($dataRecord,0,4);
	 $articolo['MDCMVA']= substr($dataRecord,4,2);
	 $articolo['MDCGVA']= substr($dataRecord,6,2);
	 // Impostazione default
	 $micro = substr(date("u"),0,3);
	 $articolo['MDCHMO']= date("His").$micro;
	 $articolo['MDCDMO']= date("dmy");
	 $articolo['MDCUSR']= "000";
	 $articolo['MDCPID']= "000";
	 $articolo['MDCPGM']= "PHPANA";
	 // Altri dati
	 if ($articolo['MDCSTA']=='') $articolo['MDCSTA']='1';
	 $articolo['MDCST1']='1';
	 $articolo['MDCST2']='1';
	 
	 return $articolo;
}
function impostaFMEBINTL($interlocutori, $dataRiferimento=null, $dati=null, $context='DEFAULT') {

	$valori=array();

	// Se non ho passato l'array con i dati da scrivere utilizzo il post
	if ($dati!=null) {
		$valori = $dati;
//		echo "DATI:<pre>"; print_r($dati); echo "</pre>";
	} else {
		$valori = $_POST;
	}
	// Valorizzo i campi modificati a video con i campi del tracciato di output
	foreach($interlocutori as $key => $tracciato) {
		if(isset($valori[$key])) {
			$interlocutori[$key] = $valori[$key];
		}
	}
//	echo "INTERLOCUTORI:<pre>"; print_r($interlocutori); echo "</pre>";
	// Valorizzo la data di riferimento
	$dataRecord = "";
	// Se non ho passato l'array con i dati da scrivere utilizzo il post
	if ($dataRiferimento!=null) {
		$dataRecord = $dataRiferimento;
	} else {
		$dataRecord = date("Ymd");
	}
	$interlocutori['MEBAVA']= substr($dataRecord,0,4);
	$interlocutori['MEBMVA']= substr($dataRecord,4,2);
	$interlocutori['MEBGVA']= substr($dataRecord,6,2);
	// Impostazione default
	$micro = substr(date("u"),0,3);
	$interlocutori['MEBHMO']= date("His").$micro;
	$interlocutori['MEBDMO']= date("dmy");
	$interlocutori['MEBUSR']= "000";
	$interlocutori['MEBPID']= "000";
	$interlocutori['MEBPGM']= "PHPANA";
	// Altri dati
	if ($interlocutori['MEBSTA']=='') $interlocutori['MEBSTA']='1';
	$interlocutori['MEBST1']='1';
	$interlocutori['MEBST2']='1';

	return $interlocutori;
}
/**
 * @desc getIdScheda(): Recupero dell'ID della scheda articoli dalla sua descrizione
 * @param unknown $scheda
 * @param unknown $argomento
 * @return Ambigous <multitype:, boolean, unknown>
 */
function getIdScheda($scheda, $argomento) {
	global $db;
	$sqlFlds = "SELECT FLD_TYPE FROM ZFLDTYPE WHERE UPPER(FLD_DESC)='".strtoupper(sanitize_sql_string($scheda))."' AND OBJ_TYPE='".sanitize_sql_string($argomento)."'";
//	echo "SQL ID SCHEDA: $sqlFlds<br>";
	
	$resultItem = $db->singleQuery($sqlFlds);
	$row = $db->fetch_array($resultItem);
	return $row['FLD_TYPE'];
}
/**
 * @desc getHtmlItemScheda(): Recupero l'html di una scheda passato il codice id dell'elemtno e l'id della scheda
 * @param unknown $scheda
 * @param unknown $argomento
 * @return Ambigous <multitype:, boolean, unknown>
 */
function getHtmlItemScheda($scheda, $item) {
	global $db;
    $sqlFlds = "SELECT FLD_HTML FROM ZOBJFLD WHERE digits(FLD_TYPE) = '".$scheda."' AND
								OBJ_CODE = '".sanitize_sql_string($item)."'";
//	echo "SQL HTML ITEM SCHEDA: $sqlFlds<br>";
	$resultItem = $db->singleQuery($sqlFlds);
    $row = $db->fetch_array($resultItem);
	return $row['FLD_HTML'];
}
/**
 * @desc setIdScehdaByName(): Recupera il futuro numeratore della scheda e lo scrive sul file
 */
function setIdSchedaByName($item) {
	$sqlLastFolder = "SELECT MAX(FLD_TYPE) as MAX_ORDER from ZOBJFLD";
	$result = $db->singleQuery($sqlLastFolder);
	$fldOrder = 1;
	if ($result){
		$fldResult = $db->fetch_array($result);
		$fldOrder = $fldResult["MAX_ORDER"];
	}
	$fldOrder = $fldOrder + 1;
	$fieldsName = array("FLD_TYPE", "OBJ_CODE","FLD_HTML");
	$stmtinsert = $db->prepare("INSERT", "ZOBJFLD", null, $fieldsName);
	$fieldsValue = array($fldOrder, $item, "");
	$result = $db->execute($stmtinsert, $fieldsValue);
	
}
/**
 * saveHtmlItemScheda($scheda, $item): Salvataggio di una scheda
 * @param unknown $scheda
 * @param unknown $item
 */
function saveHtmlItemScheda($scheda, $item, $html) {
	global $db;
	
	// Cancellazione vecchie schede
	$fieldsName = array("FLD_TYPE", "OBJ_CODE");
	$stmtdelete = $db->prepare("DELETE", "ZOBJFLD", $fieldsName, null);
	$fieldsValue = array($scheda, $item);
	$result = $db->execute($stmtdelete, $fieldsValue);
	// Inserimento della scheda	
		
	$fieldsName = array("FLD_TYPE", "OBJ_CODE","FLD_HTML");
	$stmtinsert = $db->prepare("INSERT", "ZOBJFLD", null, $fieldsName);
	$fieldsValue = array($scheda, $item, $html);
	$result = $db->execute($stmtinsert, $fieldsValue);
}
?>