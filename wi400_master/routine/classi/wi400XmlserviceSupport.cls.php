<?php

/**
 * @name wi400XmlserviceSupport
 * @desc Support WI400 a XMLSERVICE
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author 
 * @version 1.00 13/06/2015
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

class wi400XmlserviceSupport {
	
	private $db;
	private $CONTROLKEY;
	private $INTERNALKEY;
	private $jobName;
	private $XMLHEADER = "<?xml version=\"1.0\" ?>";
	private $timeout;
	private $enableCtdata;
	
	public function __construct(){
		$getAttach = "";
	}
	/**
	 * @desc Set della controlkey
	 * @param string $controlKey
	 */
	public function setControlKey($controlKey) {
		$this->CONTROLKEY = $controlKey;
	}
	/**
	 * @desc Get della controlkey
	 * @param string $controlKey
	 */
	public function getControlKey() {
		return $this->CONTROLKEY;
	}
	/**
	 * @desc Set della internalkey
	 * @param string $internalkey
	 */
	public function setInternalKey($internalKey) {
		$this->INTERNALKEY = $internalKey;
	}
	/**
	 * @desc Get della internalkey
	 * @param string $internalKey
	 */
	public function getInternalKey() {
		return $this->INTERNALKEY;
	}
	/**
	 * @desc Set del timeout
	 * @param integer $timeout
	 */
	public function setTimeout($timeout) {
		$this->timeout = $timeout;
	}
	/**
	 * @desc Get del timeout
	 * @param integer $timeout
	 */
	public function getTimeout() {
		return $this->timeout;
	}
	/**
	 * @desc  Richiamo di un programma, wrapper delle vecchie funzioni i5
	 * @param string $cmd Comando da eseguire
	 * @param array $arrayInput Array con i parametri di input
	 * @param array $arrayOutput Array con i parametri da valorizzare come variabili
	 */
	public function callXMLCmd($cmd, $arrayInput=null, $arrayOutput=null) {
		global $messageContext;
	
		$xml = $this->XMLHEADER;
		$xml.="<script>";
		$comando = $cmd;
		$tagcmd = '<cmd>';
		$returnVar = False;
		// Parametri di input
		if (isset($arrayInput) && count($arrayInput)>0) {
			foreach ($arrayInput as $key=>$value) {
				$comando .= " $key($value)";
			}
			$tagcmd = "<cmd>";
		}
		// Parametri di ritorno e trasformo tutte le kye in upper altrimenti si incasina
		$arrayNew = array();
		if (isset($arrayOutput) && count($arrayOutput)>0) {
			foreach ($arrayOutput as $key=>$value) {
				$comando .= " ".strtoupper($key)."(?)";
				$arrayNew[strtoupper($key)]=$value;
			}
			$tagcmd = "<cmd exec='rexx'>";
			$returnVar = True;
		}
		if ($tagcmd =='') {
			$tagcmd = '<cmd>';
		}
		$arrayOutput = $arrayNew;
		$xml .=$tagcmd.strtoupper($comando)."</cmd></script>";
		$OutputXML = $this->callXMLService($xml);
		// Controllo il risultato
		if (!$OutputXML) {
			if (isset($messageContext)) {
				$messageContext->addMessage("LOG","Errore esecuzione comando ".$comando);
			}
			return false;
		}
	
		// Verifico se contiene il messaggio di SUCCESS else .. errore
		if (!strpos($OutputXML, "+++ success",0)) {
			if (isset($messageContext)) {
				$messageContext->addMessage("LOG","Errore esecuzione comando ".$comando);
			}
			return false;
		}
		// Se ci sono parametri di ritorno li carico come global parsando i dati
		if ($returnVar) {
			$dom = new DomDocument('1.0');
			$dom->loadXML($OutputXML);
			$params = $dom->getElementsByTagName('data');
			$i=0;
			foreach ($params as $p) {
				if (isset($arrayOutput[strtoupper($params->item($i)->getAttribute('desc'))])) {
					$myVar  = $arrayOutput[strtoupper($params->item($i)->getAttribute('desc'))];
					global ${$myVar};
					${$myVar} = $params->item($i)->nodeValue;
				}
				$i++;
			}
		}
	
		return true;
	}
	public function callXMLService($XInputXML, $XInternalKey=null, $XControlKey=null, $stmt=null) {
		global $settings;
		if (!isset($stmt)) {
			$stmt = $db->getCallPGM();
		}
		if (!isset($XInternalKey)) {
			$XInternalKey = $this->INTERNALKEY;
		}
		if (!isset($XControlKey)) {
			$XControlKey = $this->CONTROLKEY;
		}
		/*echo "<br>CONTROL KEY:".$CONTROLKEY;
		 echo "<br>CONTROL KEY pass:".$XControlKey;
		echo "<br>CONTROL KEY pass:".$INTERNALKEY;
		echo "<br>CONTROL KEY pass:".$XInternalKey;*/
		/*$handle = fopen("/www/ciccio", "a+");
		 fwrite($handle, $XInputXML);
		fclose($handle);*/
	
		$InputXML = utf8_decode($XInputXML);
		$InternalKey = $XInternalKey;
		$ControlKey = $XControlKey;
		//$ControlKey = '*here';
		$OutputXML="";
		if ($settings['xmlservice_driver']=="DB") {
			$db->bind_param($stmt, 1, "InternalKey", DB2_PARAM_IN );
			$db->bind_param($stmt, 2, "ControlKey", DB2_PARAM_IN );
			$db->bind_param($stmt, 3, "InputXML", DB2_PARAM_IN );
			$db->bind_param($stmt, 4, "OutputXML", DB2_PARAM_OUT );
			// Per le prossime chiamate ignoro i Flag per aumentare le perfomance, tanto i lavori sono già aperti
			//$OutputXML = i_xmlservice($InputXML, $ControlKey, $InternalKey);
			//$CONTROLKEY = '*ignore';
			$result = db2_execute($stmt);
			if (!$result) {
				return false;
			}
		} else if($settings['xmlservice_driver']=="MEMORY") {
			//$OutputXML = i_xmlservice($InputXML, $ControlKey, $InternalKey, 819, 280);
			$OutputXML = i_xmlservice($InputXML, $ControlKey, $InternalKey);
		} else if($settings['xmlservice_driver']=="DB_DIRECT") {
			$OutputXML = db2_ibmi_xmlservice($db->getLink(),$InputXML,65000);
		} else if($settings['xmlservice_driver']=="ODBC") {
			$result = $db->execute($stmt, array($InternalKey, $ControlKey, $InputXML));
			if (!$result) {
				return false;
			}
			$OutputXML;
			$row = $db->fetch_array($stmt);
			$OutputXML.=$row['OUT151'];
		} else if($settings['xmlservice_driver']=="PDO") {
			$result = $stmt->execute(array($InternalKey, $ControlKey, $InputXML));
			if (!$result) {
				return false;
			}
			$OutputXML="";
			$row = $stmt->fetchAll(PDO::FETCH_NUM);
			//print_r($row);
			foreach ($row as $key => $value) {
				$OutputXML.=$value[0];
			}
		}
		return utf8_encode(trim($OutputXML));
	}
	function xmlservice_logout() {
		global $settings;
	
		$InputXML = $this->XMLHEADER;
		$InternalKey = $this->INTERNALKEY;
		$ControlKey="*immed";
		$callPGM = $db->getCallPGM();
	
		if($settings['xmlservice_driver']!="PDO") {
			$OutputXML = '';
			$db->bind_param ($callPGM, 1, "InternalKey", DB2_PARAM_IN );
			$db->bind_param ($callPGM, 2, "ControlKey", DB2_PARAM_IN );
			$db->bind_param ($callPGM, 3, "InputXML", DB2_PARAM_IN );
			$db->bind_param ($callPGM, 4, "OutputXML", DB2_PARAM_OUT );
			$ret = db2_execute($callPGM);
		} else {
			$result = $callPGM->execute(array($InternalKey, $ControlKey, $InputXML));
			if (!$result) {
				return false;
			}
			//$row = $stmt->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT);
			/*
			 $OutputXML="";
			$row = $stmt->fetchAll(PDO::FETCH_NUM);
			//print_r($row);
			foreach ($row as $key => $value) {
			$OutputXML.=$value[0];
			}*/
		}
	}
	
}