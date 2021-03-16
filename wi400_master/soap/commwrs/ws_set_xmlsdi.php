<?php 

global $moduli_path, $routine_path;

require_once $moduli_path."/siri_fattura_elettronica/siri_fattura_elettronica.php";

ini_set("memory_limit","10000M");
ini_set("post_max_size ","200M");
ini_set("upload_max_filesize","200M");
set_time_limit(0);
ini_set('default_socket_timeout', 5000);

$start = time();
$AA = array();

require_once 'commwrs_commons.php';

// Prendo i dati del segmento che mi interessa
$dati = $param['segmento']['0010'];
$ENTITA = '0800';
$DATASET = '0010';
$DATASET_DES ="USER_INFO";
$idUnivoco = $dati['idUnivocoXML'];
$dataRicezione = $dati['dataRicezioneXML'];
$idSocieta = $dati['idSocieta'];
$PDFGlobe = "";
if(isset($dati['PDFGlobe']) && !empty($dati['PDFGlobe']))
	$PDFGlobe = base64_decode($dati['PDFGlobe']);
$processXML = True;
//$id = $dati['privateID'];

$data_rif = date("Ymd");
		
// Salvo quello che ho ricevuto e scrivo il primo record di log
while (true) {
//	$db_log = "FXMLSDZ";
//	$db_logD = "FXMLSDZD";
	$field_log = getDS($db_log);
	$idprg = getSequence("WS_XML_SDI");
	$xml = base64_decode($dati['XML']);
	
	$path_allegato = wi400File::getCommonFile("xml_globe", $idprg.".xml");
	
	file_put_contents($path_allegato, $xml);
	
	$stmtD = $db->prepare("INSERT", $db_log, null, array_keys($field_log));
	
	if ($dataRicezione=="") {
		$dataRicezione = getDb2Timestamp("*INZ");
	}
	else {
		$dataRicezione = str_replace(":", ".", $dataRicezione);
	}
	
	$field_log['ZPRG']=$idprg;
	$field_log['ZEXTKEY']=$idUnivoco;
	$field_log['ZEXTDAT']=$dataRicezione;
	$field_log['ZORIGIN']="WS_GLOBE";
	$field_log['ZLGSTA']="*";
	$field_log['ZTIMINS']=getDb2Timestamp();
	$field_log['ZSOCGLOBE'] = $idSocieta;
	$field_log['ZPATH']=$path_allegato;
	
	$result = $db->execute($stmtD, $field_log);
	if ($processXML==True) {
		// Istanzio la classe di elaborazione XML
//		require_once $moduli_path."/siri_fattura_elettronica/siri_fattura_elettronica.php";
		require_once $moduli_path."/siri_fattura_elettronica/siri_fattura_elettronica_functions.php";
		
		$ei = new Wi400FatturaElettronica();
		
		$result = $ei->load($xml);
		
		// Verifico se ci sono stati errori
		if ($result===false) {
			$sql = "UPDATE $db_log SET ZERROR='0001' WHERE ZPRG = '$idprg'";
			$db->query($sql);
			
			$CODE = "0001";
			$CODE_MESSAGE = "XML SDI NON VALIDO";
			$XMLPHP = componiXML();
			
			break;
		}
		
		// Inizio L'elaborazione
//		$obj = $ei->getFatturaElettronica();	
		preg_match('/encoding="(.*?)"/', $xml, $match);
		if (count($match)==0) {
			$xml = '<?xml version="1.0" encoding="UTF-8"?>'.$xml;
		} else {
			if (strtoupper($match[1])!="UTF-8") {
				// Sostituzione stringa
				$xml= preg_replace('/(<\?xml[^?]+?)'.trim($match[1]).'/i', '$1UTF-8', $xml);
				$xml = utf8_encode($xml);
			}
		}
		
		$obj = simplexml_load_string($xml);
		$obj = $ei->getFatturaElettronica2($obj);
		
		$wc = new wi400EiToDBSiri($ei, $xml);
		$wc->setIdEsterno($idUnivoco);
		$wc->setDataEsterna($dataRicezione);
		$wc->setIdSocietaEsterna($idSocieta);
		$wc->setPDFEsterno($PDFGlobe);
		
		$do = $wc->write();
		
		if ($do===False) {
			$sql = "UPDATE $db_log SET ZERROR='0002' WHERE ZPRG = '$idprg'";
			$db->query($sql);
			
			$CODE = "0002";
			$CODE_MESSAGE = "ERRORI DI IMPORT XML";
			$XMLPHP = componiXML();
			
			break;
		}
		
		$id_import = $wc->getKey();
		
		$path_esterno = $wc->get_path_pdf_esterno();
		
		// Aggiorno file con data Elaborazione XML 
		$sql = "UPDATE $db_log SET ZLGSTA='1', ZTIMELAX='".getDb2Timestamp()."', ZKEY=$id_import, ZPDFXML='$path_esterno' WHERE ZPRG = '$idprg'";
		$db->query($sql);
		
		// Scrivo Dettaglio Chiavi Elaborate
		$ids = $wc->getKeys();
		$field_det = getDS($db_logD);
		$stmtDet = $db->prepare("INSERT", $db_logD, null, array_keys($field_det));
		foreach ($ids as $key => $value) {
			$field_det['ZPRG']=$idprg;
			$field_det['ZKEY']=$value;
			$field_det['ZEXTKEY']=$idUnivoco;
			$field_det['ZORIGIN']="WS_GLOBE";
			$field_det['ZLGSTA']="1";
			$result = $db->execute($stmtDet, $field_det);
		}
		
		// Lancio Elaborazione Flusso RPG - Controllo riscontro tra XML ed EDI
		$res_ris = check_riscontro_xml_fattura_elettronica($id_import);
	}
	// Riporto il private id
	//$AA['privateID']= $this->privateId;
	$ATTRIBUTES = esplodiDati($AA);
	$CODE = "0";
	$CODE_MESSAGE  ="ELABORAZIONE RIUSCITA";
	$XMLPHP = componiXML();
	break;
}