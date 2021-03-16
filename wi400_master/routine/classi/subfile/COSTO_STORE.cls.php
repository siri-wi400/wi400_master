<?php

class COSTO_STORE extends wi400CustomSubfile {
	
	private $rtlent;
	
	public function __construct($parameters){
		global $db, $connzend;
		
		$this->rtlent = new wi400Routine('RTLENT', $connzend);
	    $this->rtlent->load_description();
	    /*$this->rtlent->prepare();
	    $this->rtlent->set('NUMRIC',1);
	    $this->rtlent->set('DATINV', date("Ymd"));*/
	}
	
	public function getArrayCampi() {	
		global $db;
		
		$array = array();
		$array['STORE']=$db->singleColumns("1", "4", "", "Store");
		$array['DES_STORE']=$db->singleColumns("1", "100", "", "Descrizione");
		$array['COSTO']=$db->singleColumns("3", "13", 2, "Costo");
		
		return $array;
	}
	
	public function init($parameters){
		global $db;
		
		$this->setCols($this->getArrayCampi());
	}

	public function body($campi, $parameters){
		global $connzend;

	    $this->rtlent->prepare();
	    $this->rtlent->set('NUMRIC',1);
	    $this->rtlent->set('DATINV', date("Ymd"));
		$this->rtlent->set('CODICE', $campi['STORE']);
		$this->rtlent->call();
		
		$ente = $this->rtlent->get('ENTI');

		$writeRow = array(
			$campi['STORE'],
			$ente['MAFDSE'],
			$campi['COSTO']
		);

		return $writeRow;
	}
	
}

?>