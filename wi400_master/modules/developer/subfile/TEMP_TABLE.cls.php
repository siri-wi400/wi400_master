<?php
class TEMP_TABLE extends wi400CustomSubfile {
	
	public function __construct($parameters){
		
		global $db;

	}
	public function getArrayCampi() {	
		global $db;
		$array = array();
		$array['TABLENAME']=$db->singleColumns("1", "80", "", "Nome Tabella" );
		$array['SYSNAME']=$db->singleColumns("1", "10", "", "Nome Sistema" );
		$array['TABLEDESC']=$db->singleColumns("1", "50", "", "Descrizione" );
		$array['DATA_CREAZIONE']=$db->singleColumns("1", "10", "", "Data Creazione" );
		$array['ULTIMO_UTILIZZO']=$db->singleColumns("1", "10", "", "Ultimo Utilizzo" );

		return $array;
	}		
	public function init($parameters){
		global $db;

		$this->setCols($this->getArrayCampi());
	}
	
	public function body($campi, $parameters){

		$writeRow = $this->getColsInz();
		$writeRow['TABLENAME'] = $campi["TABLE_NAME"];
		$writeRow['SYSNAME'] = $campi["SYSTEM_TABLE_NAME"];
		$writeRow['TABLEDESC'] = $campi["TABLE_TEXT"];
	
		return $writeRow;

	}

}
?>