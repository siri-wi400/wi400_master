<?php

class COSTO_SAP extends wi400CustomSubfile {
	
	public function __construct($parameters){
		global $db;
	}
	
	public function getArrayCampi() {	
		global $db;
		
		$array = array();
		$array['SAP']=$db->singleColumns("1", "7", "", "Codice SAP");
		$array['COSTO']=$db->singleColumns("3", "20", 2, "Costo");
		
		return $array;
	}
	
	public function init($parameters){
		global $db;
		
		$this->setCols($this->getArrayCampi());
	}

	public function body($campi, $parameters){
		global $connzend;

		$writeRow = array(
			$campi['SAP'],
			$campi['COSTO']
		);

		return $writeRow;
	}
	
}

?>