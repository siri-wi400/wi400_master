<?php

class MONITOR_EMAIL_DEST_LIST extends wi400CustomSubfile {
	
	public function __construct($parameters) {
		global $db, $connzend, $moduli_path, $persTable;
	}
	
	public function getArrayCampi() {
		global $db;
	
		$array = array();
	
		$array['ID']=$db->singleColumns("1", "10", "", "ID");
		$array['MAITOR']=$db->singleColumns("1", "64", "", "E-mail destinatario");
		$array['MAIALI']=$db->singleColumns("1", "50", "", "Alias destinatario");		
		$array['MATPTO']=$db->singleColumns("1", "5", "", "Tipo destinatario");
				
		return $array;
	}
	
	public function init($parameters){
		global $db, $settings;
	
		$this->setCols($this->getArrayCampi());
	}
	
	public function body($campi, $parameters) {
		global $db, $connzend, $persTable;
		
		$writeRow = array(
			$campi['ID'],
			$campi['MAITOR'],
			$campi['MAIALI'],
			$campi['MATPTO']
		);
		
		if(!empty($writeRow))
			return $writeRow;
		else
			return false;
	}
	
}