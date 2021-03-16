<?php

class ORDINI_INEVASI_DETAIL extends wi400CustomSubfile {

	private $rtlart;

	private $des_art = array();
	
	public function __construct($parameters){
		global $db, $connzend;
		
		$this->rtlart = new wi400Routine('RTLART', $connzend);
	    $this->rtlart->load_description();
	}
	
	public function getArrayCampi() {	
		global $db;
		
		$array = array();
		$array['ARTICOLO']=$db->singleColumns("1", "7", "", "Articolo");
		$array['DES_ART']=$db->singleColumns("1", "50", "", "Descrizione articolo");
		$array['QTA']=$db->singleColumns("3", "9", 2, "Quantità");
		
		return $array;
	}
	
	public function init($parameters){
		global $db;
		
		$this->setCols($this->getArrayCampi());
	}
	
	public function body($campi, $parameters){	
		global $db, $connzend, $persTable;
		
		$art = $campi['RABCDA'];
		
		if(!isset($this->des_art[$art])) {
			$this->rtlart->prepare();
		    $this->rtlart->set('NUMRIC',1);
		    $this->rtlart->set('DATINV', date("Ymd"));
		    $this->rtlart->set('ARTICOLO',$art);
		    $this->rtlart->call();
		    $arti = $this->rtlart->get('ARTI');
		    $this->des_art[$art] = $this->rtlart->get('DART35');;
		}
		
		$writeRow = array(
			$art,
			$this->des_art[$art],
			$campi['RABQTA'],
		);
		
		return $writeRow;
	}
	
}

?>