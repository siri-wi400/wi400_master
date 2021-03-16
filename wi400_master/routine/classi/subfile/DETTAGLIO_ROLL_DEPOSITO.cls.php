<?php
class DETTAGLIO_ROLL_DEPOSITO extends wi400CustomSubfile {

	private $tipoRoll;
	private $statoRoll;
	
//	private $tab0707;
	private $rtlfor;
	private $rtlent;
	
	private $des_pre = array();
	private $des_cliente = array();
	private $des_ente = array();
	private $des_orient = array();
	
	public function __construct($parameters){
		global $db, $connzend, $moduli_path;
		
		require_once $moduli_path.'/piattaforma/piattaforma_commons.php';
		
		$this->tipoRoll = $tipoRoll;
		$this->statoRoll = $statoRoll;
		
//		$this->tab0707 = new wi400Tabelle ( "0707", Null, $db );
//		$this->tab0707->prepareStmt();	
		
		$this->rtlfor = new wi400Routine('RTLFOR', $connzend);
	    $this->rtlfor->load_description();
		
		$this->rtlent = new wi400Routine('RTLENT', $connzend);
	    $this->rtlent->load_description();
	}
	
	public function init($parameters){
		global $db;
		
		$array = array();
		$array['NUMROL']=$db->singleColumns("3", "4", "0");
		$array['TIPROL']=$db->singleColumns("1", "30");
		$array['COVER']=$db->singleColumns("3", "10", "0" );
		$array['ORDINE']=$db->singleColumns("3", "7", "0" );
		$array['DATA_SPEDIZIONE']=$db->singleColumns("3", "8", "0");
		$array['VIAGGIO']=$db->singleColumns("1", "7");
		$array['TOTCOL']=$db->singleColumns("3", "5", "0" );
		$array['STATO']=$db->singleColumns("1", "40");
		$array['CLIENTE']=$db->singleColumns("1", "6");
		$array['DES_CLI']=$db->singleColumns("1", "30");
		$array['ENTE']=$db->singleColumns("1", "4");
		$array['DES_ENTE']=$db->singleColumns("1", "30");
		$array['ORIENTAMENTO']=$db->singleColumns("1", "7", "", "Orientamento");
		$array['DES_ORIENTAMENTO']=$db->singleColumns("1", "50", "", "Descrizione orientamento");
		$array['PRELEVATORE']=$db->singleColumns("1", "4");
		$array['DESPRE']=$db->singleColumns("1", "20");
		$array['RAGGRUPPAMENTO']=$db->singleColumns("1", "15");
		$array['CAUSALE']=$db->singleColumns("1", "4");		
		
		$this->setCols($array);
	}
	
	public function body($campi, $parameters){
		global $connzend, $db, $persTable;
/*
		$descli = "";
		$codice = "";		
		if ($campi['RAFCD1']=='9000') {
			$this->rtlfdes->prepare();
		    $this->rtlfdes->set('NUMRIC', 1);
		    $this->rtlfdes->set('FORNITORE', $campi['RAFCLI']);
		    $this->rtlfdes->set('DATINV', date("Ymd"));
		    $this->rtlfdes->call();
		    $descli = $this->rtlfdes->get('DESCRIZIONE');
		    $cliente= $campi['RAFCLI'];
		} else {
	       $this->rtlent->prepare();
	       $this->rtlent->set('NUMRIC',1);
	       $this->rtlent->set('DATINV', date("Ymd"));
	       $this->rtlent->set('CODICE',$campi['RAFCD1']);
	       $this->rtlent->call();
	       $descli = $this->rtlent->getDSParm('ENTI', 'MAFDSE');
           $cliente= $campi['RAFCD1'];			
		}
*/
		if(!isset($this->des_cliente[$campi['RAFCLI']])) {
			$this->rtlfor->prepare();
		    $this->rtlfor->set('NUMRIC',1);
		    $this->rtlfor->set('DATINV', date("Ymd"));
			$this->rtlfor->set('CODFOR',$campi['RAFCLI']);
		    $this->rtlfor->call();
		    $row_cli = $this->rtlfor->get('FORN');
		    $this->des_cliente[$campi['RAFCLI']] = $row_cli['MEBRAG'];
		}
		
		if(!isset($this->des_ente[$campi['RAFCD1']])) {
			$this->rtlent->prepare();
			$this->rtlent->set('NUMRIC',1);
			$this->rtlent->set('DATINV', date("Ymd"));
			$this->rtlent->set('CODICE',$campi['RAFCD1']);
			$this->rtlent->call();
			$this->des_ente[$campi['RAFCD1']] = $this->rtlent->getDSParm('ENTI', 'MAFDSE');
		}
		
//		$this->tab0707->decodifica($campi['RAFPRE']);
//		$despre =$this->tab0707->getDescrizione();

		if(!isset($this->des_pre[$campi['RAFPRE']])) {
			$prel_array = $persTable->decodifica('0707', $campi['RAFPRE']);
			$this->des_pre[$campi['RAFPRE']] = $prel_array['DESCRIZIONE'];
		}
		
		// Descrizione orientamento
		if(!isset($this->des_orient[$campi['RAFCD1'].$campi['RAFCDO']])) {
			$orient_tab = $persTable->decodifica('0168', $campi['RAFCD1'].$campi['RAFCDO']);
			if($orient_tab['FOUND']==True) {
				$this->des_orient[$campi['RAFCD1'].$campi['RAFCDO']] = $orient_tab['DESCRIZIONE']; 
			}
		}
		
//		$des_orient = "";
//		if(isset($this->des_orient[$campi['RAFCD1'].$campi['RAFCDO']]))
//			$des_orient = $this->des_orient[$campi['RAFCD1'].$campi['RAFCDO']];
		
        $writeRow = array(  
			$campi['RAFNRO'],
            $campi['RAFTRO']."-".$this->tipoRoll[$campi['RAFTRO']], 			
			$campi['RAFULA'],
			$campi['RAFNRA'],
			dateString($campi['RAFGSP'],$campi['RAFMSP'],$campi['RAFASP']),
			$campi['RAFVIA'],
			$campi['RAFTOC'],
            $campi['RAFSTA']."-".$this->statoRoll[$campi['RAFSTA']], 			
			$campi['RAFCLI'],
			$this->des_cliente[$campi['RAFCLI']],
			$campi['RAFCD1'],
			$this->des_ente[$campi['RAFCD1']],
			$campi['RAFCDO'],
			$this->des_orient[$campi['RAFCD1'].$campi['RAFCDO']],
			$campi['RAFPRE'],
//			$despre,
			$this->des_pre[$campi['RAFPRE']],
			$campi['RAFRGG'],
			$campi['RAFCAU']
		);
		
		return $writeRow;
	}
	
}

?>