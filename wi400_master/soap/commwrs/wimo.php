<?php
global $dati, $paramall, $AA, $errnum, $errori, $connzend, $INTERNALKEY, $routine_path,$base_path, $db;

$start = time();
$AA = array();
$errnum = 0;
set_time_limit(0);

require_once 'commwrs_commons.php';
//require_once '../../routine/generali/formatting.php';

$timestamp = date("Y-m-d-H.i.s.00000");
$micro = substr(date("u"),0,3);
$timestamp2 = date("YmdHis").$micro;
// Prendo i dati del segmento che mi interessa
$dati = $param['segmento']['0001'];
$paramall = $param;

$ENTITA = '0050';
$DATASET = '0001';
$DATASET_DES ="WRS_GIACENZA";
$id = $param['privateID'];
$tipo = $dati['tipo'];
$save_data =  $settings['doc_root']."upload/commwrs/log/";

$connzend = $this->conn;

// @todo sistemare While ...

if (!file_exists($save_data)) {
	wi400_mkdir($save_data, 0777, True);
}

$filename = $save_data.$id.".log";
$data_rif = date("Ymd");

while(True) {
	if ($tipo == "TIPO_VENDUTO") {
		$AA['ciao'] = "ok";
	}
	break;
}

$ATTRIBUTES = esplodiDati($AA);
$CODE = "0";
$CODE_MESSAGE  ="ELABORAZIONE RIUSCITA";
$XMLPHP = componiXML();


