<?php
class MOVIMENTI_ARTICOLO extends wi400CustomSubfile {

	public function __construct($parameters){
		
		global $db;

	}
	
	public function init($parameters){
		
		global $db;
		
		$array = array();
		$array['CAUSALE']=$db->singleColumns("1", "4");
		$array['DESCAU']=$db->singleColumns("1", "30");
		$array['COLLI']=$db->singleColumns("3", "4", "0" );
		
		$this->setCols($array);
	}
	
	public function body($campi, $parameters){

		global $persTable;
		 
		$datiCausale = $persTable->decodifica('0114', $campi['CAACAU']);
		$causaleDesc = $datiCausale['DESCRIZIONE']; 
		$writeRow = array(
			$campi['CAACAU'],
			$causaleDesc,
			$campi['CAAQTP']
		);

		return $writeRow;

	}
}
?>