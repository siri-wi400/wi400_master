<?php

class TRACCIABILITA_PRESENZE extends wi400CustomSubfile {
	
	private $rtlfdes;
	private $rtlent;
	private $rtdspal;
		
	private $stmt_palet;
	private $stmt_tpepp;
	private $stmt_bolla;
	
	public function __construct($parameters){
		global $db, $connzend;
		
		$this->rtlfdes = new wi400Routine('RTLFOR', $connzend);
		$this->rtlfdes->load_description();
		$this->rtlfdes->prepare();
		$this->rtlfdes->set('DATINV', date("Ymd"));
		$this->rtlfdes->set('NUMRIC', 1);
		
		$this->rtlent = new wi400Routine('RTLENT', $connzend);
	    $this->rtlent->load_description();
	    $this->rtlent->prepare();
	    $this->rtlent->set('NUMRIC',1);
	    $this->rtlent->set('DATINV', date("Ymd"));
	    
	    $this->rtdspal = new wi400Routine('RTDSPAL', $connzend);
	    $this->rtdspal->load_description();
	    $this->rtdspal->prepare();
	    
	    $sql_palet = "SELECT * FROM FCAPALET WHERE CAPCDE=? AND CAPCDA=? AND CAPCDP=?";
		$this->stmt_palet = $db->singlePrepare($sql_palet);
		
		$sql_tpepp = "SELECT * FROM FCATPEPP WHERE CATCDE=? AND CATCDA=? AND CATPAL=?";
		$this->stmt_tpepp = $db->singlePrepare($sql_tpepp);
		
		$sql_bolla = "select * from FCADBAMR where CADNRO=? and CADAEC=? and CADMEC=? and CADGEC=?";
		$this->stmt_bolla = $db->singlePrepare($sql_bolla);
	}
	
	public function getArrayCampi() {	
		global $db;
		
		$array = array();
		$array['LOTTO']=$db->singleColumns("1", "20", "", "Lotto");
//		$array['PALLET']=$db->singleColumns("1", "10", "", "Pallet");
		$array['PALLET']=$db->singleColumns("3", "7", 0, "Pallet");
		$array['BOLLA_CARICO']=$db->singleColumns("1", "20", "", "Bolla di carico" );
		$array['DATA_CARICO']=$db->singleColumns("1", "10", "", "Data carico");
		$array['PRES_INI']=$db->singleColumns("1", "30", "", "Presenza iniziale");
		$array['PRES_FIN']=$db->singleColumns("1", "30", "", "Presenza finale");
		$array['DES_PALLET']=$db->singleColumns("1", "60", "", "Descrizione pallet");
		$array['STATO']=$db->singleColumns("1", "10", "", "Stato");
		$array['DEPOSITO']=$db->singleColumns("1", "4", "", "Deposito");
		$array['DES_DEPOSITO']=$db->singleColumns("1", "30", "", "Descrizione deposito");
		$array['ORDINE_FORN']=$db->singleColumns("1", "7", "", "Ordine fornitore");
		$array['DATA_ORDINE_EM']=$db->singleColumns("1", "10", "", "Data emissione ordine");
		$array['FORNITORE']=$db->singleColumns("1", "6", "", "Fornitore" );
		$array['DES_FORN']=$db->singleColumns("1", "30", "", "Descrizione fornitore");
		$array['BOLLA_FORN']=$db->singleColumns("1", "20", "", "Bolla del fornitore" );
		$array['DATA_BOLLA_CONS']=$db->singleColumns("1", "10", "", "Data consenga bolla");
		$array['DATA_SCADENZA']=$db->singleColumns("1", "10", "", "Data scadenza");
		$array['FORZATURA']=$db->singleColumns("1", "10", "", "Forzatura");
		$array['CAUSALE']=$db->singleColumns("1", "4", "", "Causale");
		$array['POSTO']=$db->singleColumns("1", "21", "", "Posto");
		$array['ORA_INI']=$db->singleColumns("1", "30", "", "Data presenza iniziale");
		$array['ORA_FIN']=$db->singleColumns("1", "30", "", "Data presenza finale");
		$array['QTA_INI_COL']=$db->singleColumns("3", "9", 0, "Qta iniziale colli");
		$array['QTA_INI_PEZ']=$db->singleColumns("3", "9", 2, "Qta iniziale pezzi");
		$array['QTA_RES_COL']=$db->singleColumns("3", "9", 0, "Qta residua colli");
		$array['QTA_RES_PEZ']=$db->singleColumns("3", "9", 2, "Qta residua pezzi");
		$array['QTA_DISP_COL']=$db->singleColumns("3", "9", 0, "Qta disponibile colli");
		$array['QTA_DISP_PEZ']=$db->singleColumns("3", "9", 2, "Qta disponibile pezzi");
		$array['PEZZ']=$db->singleColumns("3", "9", 0, "Pezzatura");
		
		return $array;
	}
	
	public function init($parameters){
		global $db;
		
		$this->setCols($this->getArrayCampi());
	}

	public function body($campi, $parameters){	
		global $db, $connzend;

		$this->rtlfdes->set('CODFOR', $campi['KTLFOR']);
		$this->rtlfdes->call();
		
		$this->rtlent->set('CODICE',$campi['KTLDEP']);
	    $this->rtlent->call();
	    	    
	    $res_tpepp = $db->execute($this->stmt_tpepp, array($campi['KTLDEP'],$campi['KTLCDA'],$campi['KTLPAL']));
	    $array_tpepp = $db->fetch_array($this->stmt_tpepp);
	    
	    $res_palet = $db->execute($this->stmt_palet, array($campi['KTLDEP'],$campi['KTLCDA'],$campi['KTLPAL']));
	    $array_palet = $db->fetch_array($this->stmt_palet);

	    $des_pallet = "";
	    $pres_ini = "";
	    $pres_fin = "";
		if(!empty($array_tpepp)) {
	    	if(empty($array_tpepp['CATTMI']) || (strncmp($array_tpepp['CATTMI'],"1985",4)==0)) {
	    		$pres_ini = "Assente";
	    	}
	    	else {
	    		$pres_ini = $array_tpepp['CATTMI'];
	    	}
	
	    	if(empty($array_tpepp['CATTMF']) || (strncmp($array_tpepp['CATTMF'],"2999",4)==0)) {
	    		if ($array_palet['CAPSTA']=='A') {
					$pres_fin = "Annullato";
				}
				else {
					$pres_fin = "Disponibile";
				}
	    	}
	    	else {
	    		$pres_fin = $array_tpepp['CATTMF'];
	    	}
	    }
	    
		if(empty($array_palet)) {
    		$des_pallet = "Pallet non movimentato";
    	}
    	else {
			$this->rtdspal->set('STATO', $array_palet['CAPSTA']);
			$this->rtdspal->call();
			
			$des_pallet = $this->rtdspal->get('DESLUNGA');
//			echo "STATO:".$array_palet['CAPSTA']."_DES:$des_pallet<br>";
	    }
	    
		$data_carico = dateString($campi['KTLGVA'], $campi['KTLMVA'], $campi['KTLAVA']);
		$data_scadenza = dateString($array_palet['CAPGSC'], $array_palet['CAPMSC'], $array_palet['CAPASC']);

		switch($array_palet['CAPSTA']) {
			case "1":
			case "3":
				$zona = $array_palet['CAPZO2'];
				$corridoio = $array_palet['CAPCO2'];
				$bay = $array_palet['CAPBA2'];
				$post = $array_palet['CAPCP2'];
				break;
			case "7":
				$zona = $array_palet['CAPZO2'];
				$corridoio = $array_palet['CAPCO2'];
				$bay = $array_palet['CAPBA2'];
				$post = $array_palet['CAPCP2'];
				break;
			default:
				$zona = $array_palet['CAPZO1'];
				$corridoio = $array_palet['CAPCO1'];
				$bay = $array_palet['CAPBA1'];
				$post = $array_palet['CAPCP1'];
				break;
		}
		
		$res_bolla = $db->execute($this->stmt_bolla, array($campi['KTLK01'],$campi['KTLAVA'],$campi['KTLMVA'],$campi['KTLGVA']));
	    $array_bolla = $db->fetch_array($this->stmt_bolla);
	    
	    $data_ordine_em = dateString($array_bolla['CADGEM'],$array_bolla['CADMEM'],$array_bolla['CADAEM']);
	    $data_bolla_cons = dateString($array_bolla['CADGEB'],$array_bolla['CADMEB'],$array_bolla['CADAEB']);
	    
	    $posto = "";
	    if($zona!="")
	    	$posto = $zona."-".$corridoio."-".$bay."-".$post;
		
		$writeRow = array(
			$campi['KTLNLF'],
			substr($campi['KTLPAL'],3),
			$array_bolla['CADNRC'],
			$data_carico,
			$pres_ini,
			$pres_fin,
			$des_pallet,
			$array_palet['CAPSTA'],
			$campi['KTLDEP'],
			$this->rtlent->getDSParm("ENTI", "MAFDSE"),
			$campi['KTLK01'],
			$data_ordine_em,
			$campi['KTLFOR'],
			$this->rtlfdes->getDSParm('FORN', 'MEBRAG'),
			$campi['KTLBAM'],
			$data_bolla_cons,
			$data_scadenza,
			$array_palet['CAPSCF'],
			$campi['KTLCAU'],
			$posto,
			$array_tpepp['CATTMI'],
			$array_tpepp['CATTMF'],
			$array_palet['CAPQTC'],
			$array_palet['CAPQTP'],
			$array_palet['CAPQRC'],
			$array_palet['CAPQRP'],
			$array_palet['CAPQDC'],
			$array_palet['CAPQDP'],
			$array_palet['CAPPEZ']
		);

		return $writeRow;
	}
	
}

?>