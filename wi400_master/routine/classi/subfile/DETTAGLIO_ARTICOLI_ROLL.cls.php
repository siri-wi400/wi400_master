<?php
class DETTAGLIO_ARTICOLI_ROLL extends wi400CustomSubfile {

	private $tipoRoll;
	private $statoRoll;
	private $statoRigaRoll;
	
	private $stmtPallet;
	
	private $rtlart;
	
	private $des_prep = array();
	
	public function __construct($parameters){
		
		global $db, $moduli_path, $connzend;
		
		require_once $moduli_path.'/piattaforma/piattaforma_commons.php';
		
		$this->tipoRoll=$tipoRoll;
		$this->statoRoll=$statoRoll;
		$this->statoRigaRoll=$statoRigaRoll;
		
		$sql = "SELECT * FROM FCAPALET WHERE CAPCDE=? AND CAPCDP=?";
		$this->stmtPallet = $db->singlePrepare($sql);
		
	    $this->rtlart = new wi400Routine('RTLART', $connzend);
		$this->rtlart->load_description();		
		$this->rtlart->prepare();
	}
	
	public function init($parameters){
		global $db;
		
		$array = array();
		$array['RIGA']=$db->singleColumns("3", "5", "0");
		$array['ARTICOLO']=$db->singleColumns("1", "7");
		$array['DESART']=$db->singleColumns("1", "30");
		$array['COLSPE']=$db->singleColumns("3", "5", "0");
		$array['PEZSPE']=$db->singleColumns("3", "9", "2");
		$array['COLPRE']=$db->singleColumns("3", "5", "0");
		$array['PEZPRE']=$db->singleColumns("3", "9", "2");		
		$array['STATO']=$db->singleColumns("1", "15");
		$array['PRELIEVO_RADIO']=$db->singleColumns("1", "15");
		$array['POSTO_PICKING']=$db->singleColumns("1", "15");		
		$array['PALLET']=$db->singleColumns("3", "10", "0");
		$array['POSTO_PICKING_PALLET']=$db->singleColumns("1", "15");
		$array['PREPARATORE']=$db->singleColumns("1", "4");
		$array['DES_PREP']=$db->singleColumns("1", "50");	
		$array['CAUSALE']=$db->singleColumns("1", "4");
		
		$this->setCols($array);
	}
	
	public function body($campi, $parameters){
		global $connzend, $db, $persTable;
		
	    $this->rtlart->prepare();
	    $this->rtlart->set('DATINV', date("Ymd"));
	    $this->rtlart->set('ARTICOLO', $campi['RAGCDA']);
	    $this->rtlart->set('NUMRIC', 1);
	    $this->rtlart->call();
		$art = $this->rtlart->get('ARTI');
	    
	    $prelievo="";
		if ($campi['RAGFL8']!=""){
			$prelievo='PRELEVATO';
		}
		
		// Posto picking pallet
		$postoPallet = "";
		if ($campi['RAGNRP']!=0) {
			$db->execute($this->stmtPallet, array($campi['RAGCDE'], $campi['RAGNRP']));
			$rowPallet = $db->fetch_array($this->stmtPallet);
			$postoPallet = "";
			if ($rowPallet) {
		 	$postoPallet = $rowPallet['CAPZO1']."-".$rowPallet['CAPCO1']."-".$rowPallet['CAPBA1']."-".$rowPallet['CAPCP1'];
			}
		}
		
		if(!isset($this->des_prep[$campi['RAGPKR']])) {
			$prep_array = $persTable->decodifica('0703', $campi['RAGPKR']);
			$this->des_prep[$campi['RAGPKR']] = $prep_array['DESCRIZIONE'];
		}
			
        $writeRow = array(  
       		$campi['RAGRIG'],
			$campi['RAGCDA'],
            $art['MDADSA'],
            $campi['RAGCSP'],
			$campi['RAGPSP'],
            $campi['RAGCPR'],
			$campi['RAGPPR'],
			$campi['RAGSTA']."-".$this->statoRigaRoll[$campi['RAGSTA']],
			$prelievo,
			$campi['RAGZOD']."-".$campi['RAGCOR']."-".$campi['RAGBAY']."-".$campi['RAGCDP'],
			$campi['RAGNRP'],
			$postoPallet,
			$campi['RAGPKR'],
			$this->des_prep[$campi['RAGPKR']],
			$campi['RAGCAR']
		);   	
		
		return $writeRow;
	}
}
?>