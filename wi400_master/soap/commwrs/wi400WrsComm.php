<?php
require '../template/wi400Ws.php';
require '../template/wi400WsTemplate.php';
class wi400WrsComm extends wi400WsSiriAtg{
	
	public function wrsComm($consumer, $contest, $xml, $regole) {
		// Reimposto i parametri globali con le configurazioni particolari di questa versione
		
		$this->defaultRoutine = "ws_wrscomm";
		$this->timeout = "600";
		//$handle = fopen("/home/wi400/xml.txt");
		//fwrite($handle, $xml);
		//fclose($handle);
		// Verifico se esiste un file esterno di configurazione particolare che sovrascrive gli standard
		if (file_exists(dirname( __FILE__ ).'/parameters.ini')) {
			$myparm = parse_ini_file(dirname( __FILE__ ).'/parameters.ini');
			if (isset($myparm['save_log'])) {
				$this->save_LOG = $myparm[$save_log];
			}
			if (isset($myparm['save_xml'])) {
				$this->save_XML = $myparm['save_xml'];
			}
			if (isset($myparm['save_path'])) {
				$this->save_path = $myparm['save_path'];
			}
			if (isset($myparm['server_down'])) {
				$this->server_down = $myparm['server_down'];
			}	
		}
		return $this->getP($consumer, $contest, $xml, $regole);
	}
	protected function getP($consumer, $contest, $xml, $regole, $dest=False) {
		$this->returnxml = $this->get($consumer, $contest, $xml, $regole, $dest);
		return $this->returnxml;
	}
	public function wrsPing($consumer, $contest, $xml, $regole) {
		$pingReply='<?xml version="1.0" encoding="UTF-8"?>
		  <state code="0" message=""/>';
		return $pingReply;
	}
	/*protected function getKeyString($param) {
		$key = "";
		$sepa = ";";
		if (isset($param['id'])) {
			$key = trim($param['id']);
		}
		// Compongo la chiave per la routine
		for ($i=0;$i<$param['keyCount'];$i++) {
			$key .= $sepa.trim($param['id'.$i]);
			if (str_pad($param['key'.$i], 10)=='*INTNETADR') {
				$key .= "-".$_SERVER['REMOTE_ADDR'];
			} else {
				$key .= "-".trim($param['key'.$i]);
			}
			$sepa = ";";
		}
		return $key;
	}*/
}
// Start WEB service
$server = new SoapServer("wi400WrsComm.wsdl", array('soap_version' => SOAP_1_2));
$server->setClass("wi400WrsComm");
/*$handle = fopen("/www/zendsvr/post_data.txt", "w+");
fwrite($handle, file_get_contents('php://input'));
fclose($handle);*/
//$server->setPersistence(SOAP_PERSISTENCE_REQUEST);   
if (strpos(file_get_contents('php://input'), "!DOCTYPE")!==False) { 
   $server->fault("500", "Invalid Soap Envelope");         
} else {                                                   
   $server->handle();                                      
}                                                          
