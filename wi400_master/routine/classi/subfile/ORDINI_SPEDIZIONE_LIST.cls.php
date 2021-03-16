<?php
class ORDINI_SPEDIZIONE_LIST extends wi400CustomSubfile {
	
	private $rtlfor;
	private $des_cli = array();

//-------------------------------------------------------------------	
	public function __construct($parameters) {
		global $db, $connzend;
		$this->rtlfor = new wi400Routine('RTLFOR', $connzend);
	    $this->rtlfor->load_description();		
	}

//-------------------------------------------------------------------	
	public function getArrayCampi() {
		global $db;
		$array = array();
		$array['DEPOSITO']=$db->singleColumns("1", "4", "", "Deposito");
		$array['DATA_SPED']=$db->singleColumns("1", "10", "", "Data spedizione");
		$array['ASSEGNAZIONE']=$db->singleColumns("3", "7", "0", "Assegnazione");
		$array['CAUSALE']=$db->singleColumns("1", "4", "", "Causale");
		$array['CLIENTE']=$db->singleColumns("1", "6", "", "Cliente");
		$array['DES_CLIENTE']=$db->singleColumns("1", "50", "", "Descrizione cliente");		
		$array['COLLI_RIC']=$db->singleColumns("3", "7", "0", "Colli richiesti");
		$array['COLLI_EVA']=$db->singleColumns("3", "7", "0", "Colli evasi");
		$array['STATO']=$db->singleColumns("1", "1", "", "Stato");
		
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
		
		$des_cli = "";
		$data_sped = dateString($campi['RACGSP'],$campi['RACMSP'],$campi['RACASP']);
	
		if(!isset($this->des_cli[$campi['RACCLI']])) {
			$this->rtlfor->prepare();
		    $this->rtlfor->set('NUMRIC',1);
		    $this->rtlfor->set('DATINV', date("Ymd"));
			$this->rtlfor->set('CODFOR',$campi['RACCLI']);
		    $this->rtlfor->call();
		    $row_cli = $this->rtlfor->get('FORN');
		    $this->des_cli[$campi['RACCLI']] = $row_cli['MEBRAG'];
		}
		$writeRow = array(
			$campi['RACCDE'],
			$data_sped,
			$campi['RACNRA'],
			$campi['RACCAU'],
			$campi['RACCLI'],
			$this->des_cli[$campi['RACCLI']],
			$campi['RACCOR'],
			$campi['RACTOC'],
			$campi['RACSTA']
			
		);
		
		return $writeRow;
	}
	
}
?>