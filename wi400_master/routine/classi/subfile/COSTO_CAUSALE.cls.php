<?php

class COSTO_CAUSALE extends wi400CustomSubfile {
	
	public function __construct($parameters){
		global $db;
	}
	
	public function init($parameters){
		global $db;
		
		$array = array();
		$array['CAUSALE']=$db->singleColumns("1", "4", "", "Causale");
		$array['DES_CAUS']=$db->singleColumns("1", "30", "", "Descrizione");
		$array['COSTO']=$db->singleColumns("3", "9", 2, "Costo");

		$this->setCols($array);
	}
	
	public function body($campi, $parameters){
		$writeRow = array(
			$campi['CAUSALE'],
			$campi['DES_CAUSALE'],
			$campi['COSTO']
		);
		
		return $writeRow;
	}
	
}

?>