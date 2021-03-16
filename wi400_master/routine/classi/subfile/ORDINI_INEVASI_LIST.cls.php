<?php

class ORDINI_INEVASI_LIST extends wi400CustomSubfile {
	
	private $rtlent;
	private $rtlfor;
	private $rtlart;
	
	private $des_dep = array();
	private $des_cli = array();
	private $des_art = array();
	private $des_err = array();
	
	public function __construct($parameters){
		global $db, $connzend;
		
		$this->rtlent = new wi400Routine('RTLENT', $connzend);
	    $this->rtlent->load_description();
	    
	    $this->rtlfor = new wi400Routine('RTLFOR', $connzend);
	    $this->rtlfor->load_description();
	    
	    $this->rtlart = new wi400Routine('RTLART', $connzend);
	    $this->rtlart->load_description();
	}
	
	public function getArrayCampi() {
		global $db;
		
		$array = array();
		$array['DEPOSITO']=$db->singleColumns("1", "4", "", "Deposito");
		$array['DES_DEPOSITO']=$db->singleColumns("1", "50", "", "Descrizione deposito");
		$array['CLIENTE']=$db->singleColumns("1", "6", "", "Cliente");
		$array['DES_CLIENTE']=$db->singleColumns("1", "50", "", "Descrizione cliente");
		$array['ORDINE']=$db->singleColumns("1", "7", "", "Ordine");
		$array['ROLL']=$db->singleColumns("1", "5", "", "Roll");
		$array['BOLLA']=$db->singleColumns("1", "7", "", "Bolla");
		$array['CAUSALE']=$db->singleColumns("1", "4", "", "Causale");
		$array['DATA_SPED']=$db->singleColumns("1", "10", "", "Data spedizione");
		$array['ARTICOLO']=$db->singleColumns("1", "7", "", "Articolo");
		$array['DES_ART']=$db->singleColumns("1", "50", "", "Descrizione articolo");
		$array['ARTICOLO_VAR']=$db->singleColumns("1", "7", "", "Articolo variante");
		$array['DES_ART_VAR']=$db->singleColumns("1", "50", "", "Descrizione art. variante");
		$array['ARTICOLO_SOS']=$db->singleColumns("1", "7", "", "Articolo sostitutivo");
		$array['DES_ART_SOS']=$db->singleColumns("1", "50", "", "Descrizione art. sostitutivo");
		$array['QTA_RIC']=$db->singleColumns("3", "9", 2, "Quantità richiesta");
		$array['QTA_ASS']=$db->singleColumns("3", "9", 2, "Quantità assegnata");
		$array['QTA_EVA']=$db->singleColumns("3", "9", 2, "Quantità evasa");
		$array['ERRORE']=$db->singleColumns("1", "10", "", "Errore");
		$array['DES_ERRORE']=$db->singleColumns("1", "50", "", "Descrizione errore");
	
		return $array;
	}
	
	public function init($parameters){
		global $db;
		
		$this->setCols($this->getArrayCampi());
	}
	
	public function body($campi, $parameters){	
		global $db, $connzend, $persTable;
	
		$dep = $campi['RAQCDD'];
		
		if(!isset($this->des_dep[$dep])) {
			$this->rtlent->prepare();
		    $this->rtlent->set('NUMRIC',1);
		    $this->rtlent->set('DATINV', date("Ymd"));
			$this->rtlent->set('CODICE',$campi['RAQCDD']);
		    $this->rtlent->call();  
		    
		    $this->des_dep[$dep] = $this->rtlent->getDSParm("ENTI", "MAFDSE");
		}
		
		$cli = $campi['RAQCLI'];
		
		if(!isset($this->des_cli[$cli])) {
			$this->rtlfor->prepare();
		    $this->rtlfor->set('NUMRIC',1);
		    $this->rtlfor->set('DATINV', date("Ymd"));
			$this->rtlfor->set('CODFOR',$cli);
		    $this->rtlfor->call();
		    $row_cli = $this->rtlfor->get('FORN');
		    $this->des_cli[$cli] = $row_cli['MEBRAG'];
		}
		
		$data_sped = dateString($campi['RAQGSP'],$campi['RAQMSP'],$campi['RAQASP']);
		
		$art = $campi['RAQCDA'];
		
		if(!isset($this->des_art[$art])) {
		    $this->rtlart->prepare();
		    $this->rtlart->set('NUMRIC',1);
		    $this->rtlart->set('DATINV', date("Ymd"));
		    $this->rtlart->set('ARTICOLO',$art);
		    $this->rtlart->call();
//		    $arti = $this->rtlart->get('ARTI');
		    $this->des_art[$art] = $this->rtlart->get('DART35');
		}
		
		$art_var = $campi['RAQVAR'];
		
		if(!isset($this->des_art[$art_var])) {
		    $this->rtlart->prepare();
		    $this->rtlart->set('NUMRIC',1);
		    $this->rtlart->set('DATINV', date("Ymd"));
		    $this->rtlart->set('ARTICOLO',$art_var);
		    $this->rtlart->call();
//		    $arti = $this->rtlart->get('ARTI');
		    $this->des_art[$art_var] = $this->rtlart->get('DART35');
		}
		
		$art_sos = $campi['RAQSOS'];
		
		if(!isset($this->des_art[$art_sos])) {
		    $this->rtlart->prepare();
		    $this->rtlart->set('NUMRIC',1);
		    $this->rtlart->set('DATINV', date("Ymd"));
		    $this->rtlart->set('ARTICOLO',$art_sos);
		    $this->rtlart->call();
//		    $arti = $this->rtlart->get('ARTI');
		    $this->des_art[$art_sos] = $this->rtlart->get('DART35');
		}
		
		$errore = $campi['RAQERR'];
		
		if(!isset($this->des_err[$errore])) {
			$err_tab = $persTable->decodifica('S016', $errore);
			if($err_tab['FOUND']==True) {
				$this->des_err[$errore] = $err_tab['DESCRIZIONE']; 
			}
		}
		
		$writeRow = array(
			$dep,
			$this->des_dep[$dep],
			$cli,
			$this->des_cli[$cli],
			$campi['RAQNRA'],
			$campi['RAQNRO'],
			$campi['RAQNBL'],
			$campi['RAQCAU'],
			$data_sped,
			$art,
			$this->des_art[$art],
			$art_var,
			$this->des_art[$art_var],
			$art_sos,
			$this->des_art[$art_sos],
			$campi['RAQQRI'],
			$campi['RAQQOR'],
			$campi['RAQQTA'],
			$errore,
			$this->des_err[$errore]
		);
		
		return $writeRow;
	}
	
}

?>