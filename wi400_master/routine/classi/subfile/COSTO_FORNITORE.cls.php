<?php

class COSTO_FORNITORE extends wi400CustomSubfile {
	
	private $rtlfdes;
	
	public function __construct($parameters){
		global $db, $connzend;
		
		$this->rtlfdes = new wi400Routine('RTLFDES', $connzend);
		$this->rtlfdes->load_description();
		$this->rtlfdes->prepare();
		$this->rtlfdes->set('DATINV', date("Ymd"));
   		$this->rtlfdes->set('NUMRIC', 1);
	}
	
	public function getArrayCampi() {	
		global $db;
		
		$array = array();
		$array['FORNITORE']=$db->singleColumns("1", "6", "", "Fornitore");
		$array['DES_FORNITORE']=$db->singleColumns("1", "100", "", "Descrizione");
		$array['COSTO']=$db->singleColumns("3", "13", 2, "Costo");
		
		return $array;
	}
	
	public function init($parameters){
		global $db;
		
		$this->setCols($this->getArrayCampi());
	}

	public function body($campi, $parameters){
		global $connzend;
		
		$this->rtlfdes->set('FORNITORE', $campi['FORNITORE']);
		$this->rtlfdes->call();
		
		$writeRow = array(
			$campi['FORNITORE'],
			$this->rtlfdes->get('DESCRIZIONE'),
			$campi['COSTO']
		);

		return $writeRow;
	}
	
}

?>