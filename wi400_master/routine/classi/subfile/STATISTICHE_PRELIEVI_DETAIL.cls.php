<?php 

class STATISTICHE_PRELIEVI_DETAIL extends wi400CustomSubfile {
	
	private $rtlent;
	private $rtlent2;
	private $rtlfor;
	
	private $des_dep;
	private $des_ente = array();
	private $des_cliente = array();
	private $des_orient = array();
	private $des_articolo = array();
	private $des_mis_array = array();
	private $tipo_prel_des_array = array();
	private $des_mis_sosp_array = array();
	
	private $tipo_mov;
	
	private $stmt_pallet;
	
	public function __construct($parameters){
		global $db,$connzend;
		global $moduli_path;
		
		require_once $moduli_path.'/piattaforma/statistiche_prelievi_common.php';
		
		$this->tipo_mov = $parameters["TPMOV"];
		
		$this->tipo_prel_des_array = $tipo_prel_des_array;

		// Routine RTLENT
		$this->rtlent = new wi400Routine('RTLENT', $connzend);
	    $this->rtlent->load_description();

	    $this->rtlent2 = new wi400Routine('RTLENT', $connzend);
	    $this->rtlent2->load_description();
	    
	    // Reperisco il tracciato anagrafico degli interlocutori
		$this->rtlfor = new wi400Routine('RTLFOR', $connzend);
		$this->rtlfor->load_description();
		$this->rtlfor->prepare();
		
		$sql_pallet = "select * from LRSTAAZP where RSTCDE=? and RSTPAL=?";
		$this->stmt_pallet = $db->singlePrepare($sql_pallet);
	}
	
	public function getArrayCampi() {	
		global $db;
		
		$array = array();
		$array['ASSEGNAZIONE']=$db->singleColumns("1", "7", "", "Assegnazione");
		$array['COVER']=$db->singleColumns("1", "5", "", "Cover");
		$array['COLLI_ASS']=$db->singleColumns("3", "9", 0, "Colli assegnati");
		$array['ROLL_ASS']=$db->singleColumns("3", "9", 0, "Roll assegnati");
		$array['COLLI_PREL']=$db->singleColumns("3", "9", 0, "Colli prelevati");
		$array['COLLI_MANC']=$db->singleColumns("3", "9", 0, "Colli mancanti");
		$array['ART_MANC']=$db->singleColumns("3", "9", 0, "Articoli mancanti");
		$array['DATA_PREL_INI']=$db->singleColumns("1", "10", "", "Data inizio prelievo");
		$array['PREL_INI']=$db->singleColumns("1", "50", "", "Inizio prelievo");
		$array['DATA_PREL_FIN']=$db->singleColumns("1", "10", "", "Data fine prelievo");
		$array['PREL_FIN']=$db->singleColumns("1", "50", "", "Fine prelievo");
		$array['CLIENTE']=$db->singleColumns("1", "7", "", "Cliente");
		$array['DES_CLIENTE']=$db->singleColumns("1", "50", "", "Descrizione cliente");
		$array['ARTICOLO']=$db->singleColumns("1", "7", "", "Articolo");
		$array['DES_ARTICOLO']=$db->singleColumns("1", "100", "", "Descrizione Articolo");
		$array['ENTE']=$db->singleColumns("1", "7", "", "Ente");
		$array['DES_ENTE']=$db->singleColumns("1", "50", "", "Descrizione ente");
		$array['ORIENTAMENTO']=$db->singleColumns("1", "7", "", "Orientamento");
		$array['DES_ORIENTAMENTO']=$db->singleColumns("1", "7", "", "Descrizione orientamento");
		$array['TEMPO']=$db->singleColumns("1", "50", "", "Tempo lavorato");
		$array['MEDIA']=$db->singleColumns("1", "50", "", "Media oraria");
		$array['DEPOSITO']=$db->singleColumns("1", "4", "", "Deposito");
		$array['DES_DEPOSITO']=$db->singleColumns("1", "50", "", "Descrizione deposito");
		$array['MISSIONE']=$db->singleColumns("1", "20", "", "Missione");
			$array['PESO_PREL']=$db->singleColumns("3", "15", 3, "Peso Colli prelevati");
			$array['COD_MISSIONE']=$db->singleColumns("1", "4", "", "Codice Missione");
			$array['DES_MISSIONE']=$db->singleColumns("1", "100", "", "Descrizione Missione");
			$array['PALLET']=$db->singleColumns("3", "15", 0, "Pallet");
			$array['TIPO_PREL']=$db->singleColumns("1", "1", "", "Tipo Prelievo");
			$array['DES_TIPO_PREL']=$db->singleColumns("1", "50", "", "Tipo Prelievo");
			$array['MIS_SOSP']=$db->singleColumns("1", "1", "", "Missione Sospesa");
			$array['DES_MIS_SOSP']=$db->singleColumns("1", "100", "", "Descrizione Missione Sospesa");
			$array['RULLIERA']=$db->singleColumns("1", "1", "", "Rulliera");
		$array['PRE_COLLO_RIGA']=$db->singleColumns("3", "20", 2, "Prelievo collo riga");
		$array['ART_PRELEVATI']=$db->singleColumns("3", "20", 2, "Articoli prelevati");

		return $array;
	}
	
	public function init($parameters){
		global $db;
		
		$this->setCols($this->getArrayCampi());
	}
	
	public function body($campi, $parameters){
		global $db, $persTable;
		
		$data_ini = dateString($campi['RSTGCO'],$campi['RSTMCO'],$campi['RSTACO']);
		$data_fin = dateString($campi['RSTGTE'],$campi['RSTMTE'],$campi['RSTATE']);
/*		
		$tempo = "";
		$tempo = mills_to_time($campi['TEMPO']);
*/
		$tempo = $campi['TEMPO'];
		
		$media = 0;	
		if($campi['TEMPO']!=0) {
			// secondi totali
			$st = floor($campi['TEMPO']/1000000);
			// colli al secondo
			$ms = $campi['RSTCOL']/$st;
			// colli all'ora
			$media = $ms*(60*60);
		}
		
		// Deposito
		$cod_dep = substr($campi['RSTRIF'],0,4);
		
		if(empty($this->des_dep)) {
			$this->rtlent->prepare();
		    $this->rtlent->set('NUMRIC',1);
		    $this->rtlent->set('DATINV', date("Ymd"));
			$this->rtlent->set('CODICE',$cod_dep);
		    $this->rtlent->call();
		    $this->des_dep = $this->rtlent->getDSParm("ENTI", "MAFDSE");
		}
		
		if(!isset($this->des_ente[$campi['RSTCD1']])) {
		    $this->rtlent2->prepare();
		    $this->rtlent2->set('NUMRIC',1);
		    $this->rtlent2->set('DATINV', date("Ymd"));
		    $this->rtlent2->set('CODICE',$campi['RSTCD1']);
		    $this->rtlent2->call();
		    $this->des_ente[$campi['RSTCD1']] = $this->rtlent2->getDSParm("ENTI", "MAFDSE");
		}
	    
	    // Cliente
	    
		if(!isset($this->des_cliente[$campi['RSTCDF']])) {
/*			
		    $this->rtlfor->set('NUMRIC',1);
			$this->rtlfor->set('DATINV', $_SESSION['data_validita']);
			$this->rtlfor->set('CODFOR',str_pad($campi['RSTCDF'], 7, "0", STR_PAD_LEFT));
			$this->rtlfor->call();
			$cliente_array = $this->rtlfor->get('FORN');
			$this->des_cliente[$campi['RSTCDF']] = $cliente_array['MEBRAG'];
*/
			$this->des_cliente[$campi['RSTCDF']] = get_campo_fornitore($campi['RSTCDF'], $_SESSION['data_validita'], "MEBRAG");
		}
		
		// Articolo
		if(!isset($this->des_articolo[$campi['RSTCDA']])) {
			$articoloObj = get_campo_articolo($campi['RSTCDA'], $_SESSION['data_validita']);
//			echo "ART:".$campi['RSTCDA']."_ANA:"; print_r($articoloObj); echo "</pre>";
			
			$this->des_articolo[$campi['RSTCDA']] = $articoloObj["DART35"];
		}
		
		// Descrizione orientamento
		$des_orient = "";
		if (isset($campi['RSTCDO'])) {
		if(!isset($this->des_orient[$campi['RSTCD1'].$campi['RSTCDO']])) {
			$orient_tab = $persTable->decodifica('0168', $campi['RSTCD1'].$campi['RSTCDO']);
			if($orient_tab['FOUND']==True) {
				$this->des_orient[$campi['RSTCD1'].$campi['RSTCDO']] = $orient_tab['DESCRIZIONE']; 
			}
		}
		

		if(isset($this->des_orient[$campi['RSTCD1'].$campi['RSTCDO']]))
			$des_orient = $this->des_orient[$campi['RSTCD1'].$campi['RSTCDO']];
		}
		
		$cover = "";
		$assegnazione = "";
//		echo "ART:".$campi['RSTCDA']."_CLA:".$campi['RSTCLA']."_";
		if($campi['RSTCLA']=='02') {
			$pallet = substr($campi['RSTRIF'],4,10);
//			echo "DEP:".$cod_dep."_PALLET:$pallet<br>";
/*			
			$result_pallet = $db->execute($this->stmt_pallet, array($cod_dep,$pallet));
			if($row_pallet = $db->fetch_array($this->stmt_pallet)) {
				$cod_dep = substr($row_pallet['RSTRIF'],0,4);
				$assegnazione = substr($row_pallet['RSTRIF'],4,7);
//				echo "_ASS_RIF:".$assegnazione."<br>";
				$cover = substr($row_pallet['RSTRIF'],11,5);
			}
			else {
*/				$assegnazione = $campi['RSTNAC'];
//				echo "_ASS_NAC:".$assegnazione."<br>";
//			}
//			echo "_ASS:".$assegnazione."<br>";
		}
//		else if($campi['RSTCLA']=='01') {
		else if(in_array($campi['RSTCLA'], array("01", "07"))) {
			$assegnazione = substr($campi['RSTRIF'],4,7);
			$cover = substr($campi['RSTRIF'],11,5);
		}
		
		$orientamento  ="";
		if (isset($campi['RSTCDO'])) {
			$orientamento = $campi['RSTCDO'];
		}
		
		if(!array_key_exists($campi['RSTCOD'], $this->des_mis_array)) {
			$tracciato0702 = array();
			$tab0702 = $persTable->decodifica('0702', $campi['RSTCOD']);
			if($tab0702['FOUND'] == True) {
				$tracciato0702 = $tab0702['TABELLA'];
				$this->des_mis_array[$campi['RSTCOD']] = $tracciato0702['T702DS'];
			}
		}
		
		$des_mis = $this->des_mis_array[$campi['RSTCOD']];
		
		if(!array_key_exists($campi['RSTFL5'], $this->des_mis_sosp_array)) {
			$tab0704 = $persTable->decodifica('0704', str_pad($campi['RSTFL5'], 4));
			if($tab0704['FOUND'] == True) {
				$tracciato0704 = $tab0704['TABELLA'];
				$this->des_mis_sosp_array[$campi['RSTFL5']] = $tracciato0704['T704DE'];
			}
		}
		$des_mis_sosp = '';
		if(isset($this->des_mis_sosp_array[$campi['RSTFL5']])) {
			$des_mis_sosp = $this->des_mis_sosp_array[$campi['RSTFL5']];
		}
		$des_tipo_prel = '';
		if(isset($this->tipo_prel_des_array[$campi['RSTJ02']])) {
			$des_tipo_prel = $this->des_mis_sosp_array[$campi['RSTFL5']];
		}
		
		$writeRow = array(
			$assegnazione,
			$cover,
			$campi['COLLI_ASS'],
			$campi['RSTNPR'],
			$campi['RSTCOL'],
			$campi['RSTCNP'],
			$campi['RSTANP'],
			$data_ini,
//			$campi['RSTHIN'],
			sprintf("%09s", $campi['RSTHIN']),
			$data_fin,
//			$campi['RSTHFI'],
			sprintf("%09s", $campi['RSTHFI']),
			$campi['RSTCDF'],
			$this->des_cliente[$campi['RSTCDF']],
				$campi['RSTCDA'],
				$this->des_articolo[$campi['RSTCDA']],
			$campi['RSTCD1'],
			$this->des_ente[$campi['RSTCD1']],
			$orientamento,
			$des_orient,
			$tempo,
			$media,
			$cod_dep,
			$this->des_dep,
			$campi['RSTRIF'],
				$campi['RSTND3'],
				$campi['RSTCOD'],
				$des_mis,
				$campi['RSTPAL'],
				$campi['RSTJ02'],
				$des_tipo_prel,
				$campi['RSTFL5'],
				$des_mis_sosp,
				$campi['RSTJ03'],
			$campi['PRELIEVO_COLLO_RIGA'],
			$campi['ART_PRELEVATI']
		);

		return $writeRow;
	}
	
	public function end($subfile) {
		global $db;
		
		$totali = $subfile->getTotals();
		
		$sql = "SELECT sum(COLLI_PREL) SUM_COLLI_PREL, SUM(ART_PRELEVATI) SUM_ART_PRELEVATI FROM ".$subfile->getTable();
		$result = $db->query($sql);
		$row_sum = $db->fetch_array($result);
		
		$totali['PRE_COLLO_RIGA'] = $row_sum['SUM_COLLI_PREL']/$row_sum['SUM_ART_PRELEVATI'];
		//showArray($totali);
		
		$subfile->setTotals($totali);
		
		$subfile->setFinalized(True);
		wi400Session::save(wi400Session::$_TYPE_SUBFILE, $subfile->getIdTable(), $subfile);
	}
	
}

?>