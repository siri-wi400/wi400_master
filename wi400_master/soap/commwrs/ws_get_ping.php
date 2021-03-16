<?php 
$start = time();
$AA = array();
require_once 'commwrs_commons.php';
// Non voglio salvare il log
$this->save_XML = False;
// Prendo i dati del segmento che mi interessa
$dati = $param['segmento']['0002'];
$ENTITA = '0100';
$DATASET = '0002';
$DATASET_DES ="PING";
$user = $dati['user'];
$ip = $dati['ip'];
$id="";
if (isset($dati['privateID'])) {
	$id = $dati['privateID'];
}

$AA['ping']="OK";
// time di esecuzione
$end = time();
$tempo = $end-$start;
$AA['tempo']= $tempo;
$ATTRIBUTES = esplodiDati($AA);
$CODE = "0";
$CODE_MESSAGE  ="ELABORAZIONE RIUSCITA";
$XMLPHP = componiXML();
