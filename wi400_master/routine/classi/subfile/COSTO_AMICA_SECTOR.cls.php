<?php

class COSTO_AMICA_SECTOR extends wi400CustomSubfile {
	
	public function __construct($parameters){
		global $db;
	}
	
	public function init($parameters){
		global $db;
		
		$array = array();
		$array['AMICA_SECTOR']=$db->singleColumns("1", "5", "", "Amica Sector");
		$array['DES_AM_SEC']=$db->singleColumns("1", "20", "", "Descrizione");
		$array['COSTO']=$db->singleColumns("3", "14", 2, "Costo");

		$this->setCols($array);
	}
	
	public function body($campi, $parameters){
		global $db, $persTable;
		
		$descr = "";
		if(isset($campi["AMICA_SECTOR"]) && !empty($campi["AMICA_SECTOR"])) {
//			$tabelle = new wi400Tabelle("0905", $campi['AMICA_SECTOR'], $db);
			$datiAmSector = $persTable->decodifica('0905', $campi["AMICA_SECTOR"]);		
			$descr = $datiAmSector['DESCRIZIONE'];
		}
		
		$writeRow = array(
			$campi['AMICA_SECTOR'],
			$descr,
			$campi['COSTO']
		);

		return $writeRow;
	}

}

?>