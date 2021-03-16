<?php

class COSTO_CONTO extends wi400CustomSubfile {
	
	public function __construct($parameters){
		global $db;
	}
	
	public function init($parameters){
		global $db;
		
		$array = array();
		$array['CONTO']=$db->singleColumns("1", "10", "", "Conto");
		$array['DES_CONTO']=$db->singleColumns("1", "30", "", "Descrizione");
		$array['COSTO']=$db->singleColumns("3", "14", 2, "Costo");
		$array['CONTO2']=$db->singleColumns("1", "10", "", "Conto2");

		$this->setCols($array);
	}
	
	public function body($campi, $parameters){
		$writeRow = array(
			$campi['CONTO'],
			$campi['DES_CONTO'],
			$campi['COSTO'],
			$campi['CONTO2']
		);

		return $writeRow;
	}

}

?>