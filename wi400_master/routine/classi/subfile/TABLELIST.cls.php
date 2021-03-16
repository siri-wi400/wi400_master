<?php
class TABLELIST extends wi400CustomSubfile {

	//private $tab0069;
	private $rtlfdes;
	//private $agenteArray;
	
	public function __construct($parameters){
		
		global $db;

	}
	public function getArrayCampi() {	
		global $db;
		$array = array();
		$array['TABLENAME']=$db->singleColumns("1", "30", "", "Nome Tabella" );
		$array['TABLEDESC']=$db->singleColumns("1", "50", "", "Descrizione" );

		return $array;
	}		
	public function init($parameters){
		global $db;

		$this->setCols($this->getArrayCampi());
	}
	
	public function body($campi, $parameters){

		$writeRow[] = $campi["TABLE_NAME"];
		$writeRow[] = $campi["TABLE_TEXT"];
	
		return $writeRow;

	}

}
?>