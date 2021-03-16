<?php

class TRACCIABILITA_PALLET extends wi400CustomSubfile {
	
	private $rtlfdes;
	private $rtlent;
	
	private $stmt_palet;
	
	private $des_cli = array();
	private $des_for = array();
	private $des_dep = array();
	private $des_neg = array();
		
	public function __construct($parameters){
		global $db, $connzend;
		
		$this->rtlfdes = new wi400Routine('RTLFOR', $connzend);
		$this->rtlfdes->load_description();
//		$this->rtlfdes->prepare();
//		$this->rtlfdes->set('DATINV', date("Ymd"));
//		$this->rtlfdes->set('NUMRIC', 1);	
		
		$this->rtlent = new wi400Routine('RTLENT', $connzend);
	    $this->rtlent->load_description();
//	    $this->rtlent->prepare();
//	    $this->rtlent->set('NUMRIC',1);
//	    $this->rtlent->set('DATINV', date("Ymd"));

		$sql_palet = "SELECT * FROM FCAPALET WHERE CAPCDE=? AND CAPCDA=? AND CAPCDP=?";
		$this->stmt_palet = $db->singlePrepare($sql_palet);
	}
	
	public function getArrayCampi() {	
		global $db;
		
		$array = array();
		$array['LOTTO']=$db->singleColumns("1", "20", "", "Lotto");
		$array['PALLET']=$db->singleColumns("1", "10", "", "Pallet");
		$array['DEPOSITO']=$db->singleColumns("1", "4", "", "Deposito" );
		$array['DES_DEPOSITO']=$db->singleColumns("1", "50", "", "Descrizione deposito");
		$array['DATA_PREL_INI']=$db->singleColumns("1", "30", "", "Data inizio prelievo");
		$array['DATA_PREL_FIN']=$db->singleColumns("1", "30", "", "Data fine prelievo");
		$array['ORDINE']=$db->singleColumns("1", "7", "", "Ordine");
		$array['FORNITORE']=$db->singleColumns("1", "6", "", "Fornitore" );
		$array['DES_FORN']=$db->singleColumns("1", "50", "", "Descrizione fornitore");
		$array['BOLLA_FORN']=$db->singleColumns("1", "20", "", "Bolla del fornitore" );
		$array['CLIENTE']=$db->singleColumns("1", "6", "", "Cliente" );
		$array['DES_CLI']=$db->singleColumns("1", "50", "", "Descrizione cliente");
		$array['NEGOZIO']=$db->singleColumns("1", "4", "", "Codice negozio" );
		$array['DES_NEGOZIO']=$db->singleColumns("1", "50", "", "Descrizione negozio");
		$array['QTA_PREL']=$db->singleColumns("3", "9", 2, "Qta prelevata");
		$array['QTA_PAL']=$db->singleColumns("3", "9", 2, "Qta pallet");
		$array['CAUSALE']=$db->singleColumns("1", "4", "", "Causale" );
		$array['ROLL']=$db->singleColumns("1", "5", "", "Roll" );
		$array['COVER']=$db->singleColumns("1", "5", "", "Cover" );
		$array['TIPO_ROLL']=$db->singleColumns("1", "1", "", "Tipo Roll" );
		$array['RIGA_ROLL']=$db->singleColumns("1", "5", "", "Riga Roll" );
//		$array['ZONA']=$db->singleColumns("1", "2", "", "Zona");
//		$array['CORRIDOIO']=$db->singleColumns("1", "2", "", "Corridoio");
//		$array['BAY']=$db->singleColumns("1", "3", "", "Bay");
//		$array['POST']=$db->singleColumns("1", "2", "", "Post");
		$array['POSTO']=$db->singleColumns("1", "21", "", "Posto");
		$array['NVIAG']=$db->singleColumns("1", "7", "", "Viaggio");
		$array['DATA_SCADENZA']=$db->singleColumns("1", "10", "", "Data scadenza");

		return $array;
	}
	
	public function init($parameters){
		global $db;
		
		$this->setCols($this->getArrayCampi());
	}

	public function body($campi, $parameters){	
		global $db, $connzend;

		if(empty($this->des_cli[$campi['WRKCLI']])) {
			$this->rtlfdes->prepare();
			$this->rtlfdes->set('DATINV', date("Ymd"));
			$this->rtlfdes->set('NUMRIC', 1);
			$this->rtlfdes->set('CODFOR', $campi['WRKCLI']);
			$this->rtlfdes->call();
			$this->des_cli[$campi['WRKCLI']] = $this->rtlfdes->getDSParm('FORN', 'MEBRAG');
		}
		
		if(empty($this->des_for[$campi['WRKFOR']])) {
			$this->rtlfdes->prepare();
			$this->rtlfdes->set('DATINV', date("Ymd"));
			$this->rtlfdes->set('NUMRIC', 1);
			$this->rtlfdes->set('CODFOR', $campi['WRKFOR']);
			$this->rtlfdes->call();
			$this->des_for[$campi['WRKFOR']] = $this->rtlfdes->getDSParm('FORN', 'MEBRAG');
		}
	
		if(empty($this->des_dep[$campi['WRKCDE']])) {
			$this->rtlent->prepare();
		    $this->rtlent->set('NUMRIC',1);
		    $this->rtlent->set('DATINV', date("Ymd"));
			$this->rtlent->set('CODICE',$campi['WRKCDE']);
		    $this->rtlent->call();
		    $this->des_dep[$campi['WRKCDE']] = $this->rtlent->getDSParm("ENTI", "MAFDSE");
		}
	    
		if(empty($this->des_neg[$campi['WRKCD1']])) {
		    $this->rtlent->prepare();
		    $this->rtlent->set('NUMRIC',1);
		    $this->rtlent->set('DATINV', date("Ymd"));
			$this->rtlent->set('CODICE',$campi['WRKCD1']);
		    $this->rtlent->call();
		    $this->des_neg[$campi['WRKCD1']] = $this->rtlent->getDSParm("ENTI", "MAFDSE");
		}
		
/*	    
	    $cliente = $campi['WRKCLI'];	    
	    // Verifico se sto lavorando per cliente o fornitore
	    if ($campi['WRKCLI']==""){
	    	$this->rtlent->prepare();
		    $this->rtlent->set('NUMRIC',1);
		    $this->rtlent->set('DATINV', date("Ymd"));
			$this->rtlent->set('CODICE',$campi['WRKCD1']);
		    $this->rtlent->call();
		    $des_cli = $this->rtlent->getDSParm("ENTI", "MAFDSE");
		    $cliente = $campi['WRKCD1'];	    	
	    }
*/
		
		$res_palet = $db->execute($this->stmt_palet, array($campi['KTLDEP'],$campi['KTLCDA'],$campi['KTLPAL']));
	    $array_palet = $db->fetch_array($this->stmt_palet);
	    
	    $data_scadenza = dateString($array_palet['CAPGSC'], $array_palet['CAPMSC'], $array_palet['CAPASC']);
	    
	    $posto = "";
	    if(trim($campi['WRKZOD'])!="")
	    	$posto = $campi['WRKZOD']."-".$campi['WRKCOR']."-".$campi['WRKBAY']."-".$campi['WRKCDP'];
  
		$writeRow = array(
			$campi['WRKNLF'],
			$campi['WRKPAL'],
			$campi['WRKCDE'],
			$this->des_dep[$campi['WRKCDE']],
			$campi['WRKTIP'],
			$campi['WRKTFP'],
			$campi['WRKNRA'],
			$campi['WRKFOR'],
			$this->des_for[$campi['WRKFOR']],
			$campi['WRKNBL'],
			$campi['WRKCLI'],
			$this->des_cli[$campi['WRKCLI']],
			$campi['WRKCD1'],
			$this->des_neg[$campi['WRKCD1']],
			$campi['WRKQTA'],
			$campi['WRKQT1'],
			$campi['WRKCAU'],
			$campi['WRKNRO'],
			$campi['WRKCOV'],
			$campi['WRKTRO'],
			$campi['WRKRIG'],
//			$campi['WRKZOD'],
//			$campi['WRKCOR'],
//			$campi['WRKBAY'],
//			$campi['WRKCDP'],
			$posto,
			$campi['WRKVIA'],
			$data_scadenza
		);

		return $writeRow;
	}
	
}

?>