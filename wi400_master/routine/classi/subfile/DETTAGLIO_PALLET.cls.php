<?php
class DETTAGLIO_PALLET extends wi400CustomSubfile {
	
	private $rtlfor;
	private $rtlart;
	private $rtripda;
	
	private $stmt_lotto;
	
	private $des_forn = array();
	private $des_art = array();
	
	private $stato_pallet = array();
	
	public function __construct($parameters){
		global $db, $connzend, $moduli_path;
		
		require $moduli_path."/piattaforma/copertura_commons.php";
		
		$this->stato_pallet = $statoPallet;
		
		$sql_lotto = "select * from FKTLOTTI where KTLDEP=? and KTLPAL=?";
		$this->stmt_lotto = $db->singlePrepare($sql_lotto);
		
		$this->rtlfor = new wi400Routine('RTLFOR', $connzend);
	    $this->rtlfor->load_description();
	    
	    $this->rtlart = new wi400Routine('RTLART', $connzend);
	    $this->rtlart->load_description();
	    
	    $this->rtripda = new wi400Routine('RTRIPDA', $connzend);
	    $this->rtripda->load_description();
	}
	public function getArrayCampi() {
		global $db;
		
		$array = array();
		$array['ARTICOLO']=$db->singleColumns("1", "7", "", "Articolo");
		$array['DES_ART']=$db->singleColumns("1", "50","","Descrizione articolo");
		$array['LOTTO']=$db->singleColumns("1", "30", "", "Lotto");
		$array['PALLET']=$db->singleColumns("3", "10", "0", "Pallet" );
		$array['OPERATORE']=$db->singleColumns("1", "4", "", "Operatore");
		$array['DES_OPERATORE']=$db->singleColumns("1", "30", "", "Descrizione operatore");
		$array['FLAP']=$db->singleColumns("3", "10", "0", "Flap" );
		$array['COLDIS']=$db->singleColumns("3", "4", "0", "Colli Disp." );
		$array['COLGIA']=$db->singleColumns("3", "4", "0", "Colli Giac." );
		$array['PEZDIS']=$db->singleColumns("3", "6", "2", "Pezzi Disp." );
		$array['PEZGIA']=$db->singleColumns("3", "6", "2", "Pezzi Giac." );
		$array['DATA_SCADENZA']=$db->singleColumns("1", "10", "", "Data Scadenza" );
		$array['FORZATURA']=$db->singleColumns("1", "10", "", "Forzatura" );
		$array['STATO']=$db->singleColumns("1", "30", "", "Stato Pallet");
		$array['FORNITORE']=$db->singleColumns("1", "6", "", "Fornitore");
		$array['DES_FORN']=$db->singleColumns("1", "50", "", "Descrizione fornitore");
		$array['CARICO']=$db->singleColumns("1", "10", "", "Carico");
		$array['DATA_ARRIVO']=$db->singleColumns("1", "10", "", "Data arrivo");
		$array['PARTITA']=$db->singleColumns("1", "10", "", "Partita");
		$array['POSTO_ARRIVO']=$db->singleColumns("1", "20", "", "Posto arrivo");
		
		return $array;
		
	}
	public function init($parameters){
		global $db;
		
		$this->setCols($this->getArrayCampi());
	}
	
	public function body($campi, $parameters){
	    global $db, $connzend;
/*	    
		$rtdspal = new wi400Routine('RTDSPAL', $connzend);
	    $rtdspal ->load_description();
	    $rtdspal ->prepare();
	    $rtdspal ->set('STATO',$campi['CAPSTA']);
	    $rtdspal ->call();
*/	    
		if(!isset($this->des_forn[$campi['CAPINT']])) {
			$this->rtlfor->prepare();
		    $this->rtlfor->set('NUMRIC',1);
		    $this->rtlfor->set('DATINV', date("Ymd"));
			$this->rtlfor->set('CODFOR',$campi['CAPINT']);
		    $this->rtlfor->call();
		    $row_cli = $this->rtlfor->get('FORN');
		    $this->des_forn[$campi['CAPINT']] = $row_cli['MEBRAG'];
		}
		
		if(!isset($this->des_art[$campi['CAPCDA']])) {
		    $this->rtlart->prepare();
		    $this->rtlart->set('NUMRIC',1);
		    $this->rtlart->set('DATINV', date("Ymd"));
		    $this->rtlart->set('ARTICOLO',$campi['CAPCDA']);
		    $this->rtlart->call();
		    $arti = $this->rtlart->get('ARTI');
		    $this->des_art[$campi['CAPCDA']] = $arti['MDADSA'];
		}
	    
	    $result = $db->execute($this->stmt_lotto, array($campi['CAPCDE'],str_pad($campi['CAPCDP'], 10, "0", STR_PAD_LEFT)));
	    $row_lotto = $db->fetch_array($this->stmt_lotto);
	    
	    $data_arrivo = dateString($campi['CAPGAR'],$campi['CAPMAR'],$campi['CAPAAR']);
	    
	    $posto_arrivo = implode("-",array($campi['CAPZO2'],$campi['CAPCO2'],$campi['CAPBA2'],$campi['CAPCP2']));
	    
	    $this->rtripda->prepare();
	    $this->rtripda->set('DEPOSITO',$campi['CAPCDE']);
	    $this->rtripda->set('PALLET',str_pad($campi['CAPCDP'], 10, "0", STR_PAD_LEFT));
	    $this->rtripda->set('ARTICOLO',$campi['CAPCDA']);
	    $this->rtripda->set('ZONA',$campi['CAPZO1']);
	    $this->rtripda->set('CORRIDOIO',$campi['CAPCO1']);
	    $this->rtripda->set('BAY',$campi['CAPBA1']);
	    $this->rtripda->set('POSTO',$campi['CAPCP1']);
	    $this->rtripda->call();
	    $op =$this->rtripda->get('RISORSA');
	    $des_op = $this->rtripda->get('DESRIS');

		$writeRow = array(  
			$campi['CAPCDA'],
			$this->des_art[$campi['CAPCDA']],
			$row_lotto['KTLNLF'],
			$campi['CAPCDP'],
			$op,
			$des_op,
			$campi['CAPFL1'],
			$campi['CAPQDC'],
			$campi['CAPQRC'],
			$campi['CAPQDP'],
			$campi['CAPQRP'],
			dateString($campi['CAPGSC'] ,$campi['CAPMSC'],$campi['CAPASC']),
			$campi['CAPSCF'],
//			$rtdspal->get('DESLUNGA'),
			$this->stato_pallet[$campi['CAPSTA']],
			$campi['CAPINT'],
			$this->des_forn[$campi['CAPINT']],
			$campi['CAPNOR'],
			$data_arrivo,
			$campi['CAPNPA'],
			$posto_arrivo
		);   	
			
		return $writeRow;

	}
}
?>