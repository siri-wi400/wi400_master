<?php

class MANAGER_DATA_AREA_LIST extends wi400CustomSubfile {
	
	public function __construct($parameters) {
		global $db, $connzend, $persTable, $moduli_path, $routine_path;
		
		require_once $routine_path."/os400/wi400Os400Object.cls.php";
	}
	
	public function getArrayCampi() {
		global $db;
	
		$array = array();
		
		$array['DTANAM']=$db->singleColumns("1", "10", "", "Nome Data Area");
		$array['DTALIB']=$db->singleColumns("1", "10", "", "Libreria Data Area");
			$array['DTANAM_DES']=$db->singleColumns("1", "100", "", "Descrizione Data Area");
		$array['DTADS']=$db->singleColumns("1", "10", "", "Nome DS campi Data Area");
		$array['DTADSL']=$db->singleColumns("1", "10", "", "Libreria DS campi Data Area");
			$array['DTADS_DES']=$db->singleColumns("1", "100", "", "Descrizione DS campi Data Area");
				
		return $array;
	}
	
	public function init($parameters){
		global $db, $settings;
	
		$this->setCols($this->getArrayCampi());
	}
	
	public function body($campi, $parameters) {
		global $db, $connzend, $persTable;
		
		// Descrizione Data Area
		$list = new wi400Os400Object("*DTAARA", $campi['DTALIB'], $campi['DTANAM']);
		$list->getList();

		$des_dta = "";
		while ($obj_read = $list->getEntry()) {
//			echo "DATI:<pre>"; print_r($obj_read); echo "</pre>";
			$des_dta = $obj_read['DESCRIP'];
		}
		
		// Descrizione DS campi Data Area
		$list = new wi400Os400Object("*FILE", $campi['DTADSL'], $campi['DTADS']);
		$list->getList();
		
		$des_ds = "";
		while ($obj_read = $list->getEntry()) {
//			echo "DATI:<pre>"; print_r($obj_read); echo "</pre>";
			$des_ds = $obj_read['DESCRIP'];
		}
		
		$writeRow = array(
			$campi['DTANAM'],
			$campi['DTALIB'],
				$des_dta,
			$campi['DTADS'],
			$campi['DTADSL'],
				$des_ds
		);
		
		if(!empty($writeRow))
			return $writeRow;
		else
			return false;
	}
	
}