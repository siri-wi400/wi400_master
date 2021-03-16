<?php

class COSTO_PREFATTURA_RIGHE extends wi400CustomSubfile {
	
	public function __construct($parameters){
		global $db;
	}
	
	public function getArrayCampi() {	
		global $db;
		
		$array = array();
		$array['RIGA']=$db->singleColumns("1", "10", "", "Riga");
		$array['CONTO']=$db->singleColumns("1", "10", "", "Conto");
		$array['SEGNO']=$db->singleColumns("1", "2", "", "Segno");
		$array['COSTO']=$db->singleColumns("3", "13", 2, "Costo");
		$array['COD_IVA']=$db->singleColumns("1", "2", "", "Codice IVA");
		$array['IVA']=$db->singleColumns("3", "13", 2, "IVA");
		
		return $array;
	}
	
	public function init($parameters){
		global $db;
		
		$this->setCols($this->getArrayCampi());
	}

	public function body($campi, $parameters){
		global $db;

		$writeRow = array(
			$campi['RIGA'],
			$campi['CONTO'],
			$campi['SEGNO'],
			$campi['COSTO'],
			$campi['COD_IVA'],
			$campi['IVA']
		);

		return $writeRow;
	}
	
}

?>