<?php

/**
 * @name wi400RoutineXMLMulti.class.php
* @desc Classe per richiamo multiplo di routine con XMLSERVICE
* @copyright S.I.R.I. Informatica s.r.l.
* @author Luca Zovi
* @version 1.01 03/01/2017
* @link www.siri.informatica.it
* @info info@siri-informatica.it
*
* Da fare. Ritorno nome DS utilizzata dalla routine getDSName, getDSNumField
*
*/


class wi400RoutineXMLMulti {
	private $routine = array();
	/**
	 * @desc Costruttore della classe: al momento nessun parametro
	 */
	function __construct() {
		// Niente per il momento
	}
	/**
	 * @desc addRoutine: Aggiunge una routine per il richiamo multiplo
	 * @param unknown $routine
	 */
	function addRoutine($routine, $name="") {
		$key = $routine->RPGProgram;
		if ($name!="") {
			$key=$name;
		}
		$this->routine[$key]=$routine;
	}
	/**
	 * @desc call: richiamo multiplo di tutte le routine
	 * @param string $reset
	 * @return boolean
	 */	
	function call($reset=False) {
		global $db, $messagContext;
		$InputXML="";
		$first= True;
		foreach ($this->routine as $key => $obj) {
			if ($reset) {
				$obj->reset();
			}
			$obj->ClearLastErr();
			$OutputXML  = "";
			$newxml = $obj->getInputXML(False);
			// Tolgo il TAG XML per i blocchi XML successivi al primo
			if ($first==False) {
				$newxml = str_replace("<?xml version='1.0'?>","",$newxml);
			}	
			$first=False;
			$InputXML .=$newxml;
		}
		$OutputXML = callXMLService($InputXML, Null , Null , $db->getCallPGM());
		if(!$OutputXML ) {
			//echo "bad execute: " . "\$db->lastErrorMsg()".$db->getCallPGM().$this->RPGProgram.$OutputXML;
			developer_debug("Errore function call: outputXML è vuoto!");
			return false;
		}
		// Inserisco l'XML nelle routine corrispondenti
		$xmlout = explode("<script><pgm name=", $OutputXML);
		$i=1;
		foreach ($this->routine as $key => $obj) {
			$obj->OutputXML = "<?xml version='1.0'?><script><pgm name=\"".$xmlout[$i];
			$i++;
			if (strpos($obj->OutputXML, "<errnoxml>",0)) {
				if (isset($messageContext)) {
					error_log("Errore richiamo routine ".$obj->RPGProgram);
				}
			}	
		}
		//$this->internalParse($OutputXML);
		return True;
	}
	function get($name) {
		return $this->routine[$name];
	}
	function clear() {
		unset($this->routine);
	}
}