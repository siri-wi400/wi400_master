<?php
class ORDINI_SPEDIZIONE_DETAIL extends wi400CustomSubfile {
	
	private $rtlart;
	
//	private $des_dep = array();
//	private $des_cli = array();
	
//-------------------------------------------------------------------
	public function __construct($parameters){
		global $db, $connzend;
		
	    $this->rtlart = new wi400Routine('RTLART', $connzend);
	    $this->rtlart->load_description();
	}
	
//-------------------------------------------------------------------	
	public function getArrayCampi() {	
		global $db;
		
		$array = array();
		$array['ARTICOLO']=$db->singleColumns("1", "7", "", "Articolo");
		$array['DES_ART']=$db->singleColumns("1", "50", "", "Descrizione articolo");
		$array['QTA']=$db->singleColumns("3", "9", 2, "Quantità");
		$array['RIGA']=$db->singleColumns("3", "3", 0, "Riga");
		$array['CAUS']=$db->singleColumns("1", "4", "", "Causale riga");
		
		return $array;
	}

//-------------------------------------------------------------------	
	public function init($parameters){
		global $db;
		
		$this->setCols($this->getArrayCampi());
	}

//-------------------------------------------------------------------	
	public function body($campi, $parameters){	
		global $db, $connzend, $persTable;

		$des_art = "";

		if(!isset($this->des_art[$campi['RADCDA']])) {
		    $this->rtlart->prepare();
		    $this->rtlart->set('NUMRIC',1);
		    $this->rtlart->set('DATINV', date("Ymd"));
		    $this->rtlart->set('ARTICOLO',$campi['RADCDA']);
		    $this->rtlart->call();
		    $arti = $this->rtlart->get('ARTI');
		    $this->des_art[$campi['RADCDA']] = $arti['MDADSA'];
		}
		
		$writeRow = array(
			$campi['RADCDA'],
			$this->des_art[$campi['RADCDA']],
			$campi['RADCLS'],
			$campi['RADNRI'],
			$campi['RADCAU']
		);
		
		return $writeRow;
	}
}

?>