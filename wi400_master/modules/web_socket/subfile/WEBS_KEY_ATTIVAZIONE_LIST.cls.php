<?php
class WEBS_KEY_ATTIVAZIONE_LIST extends wi400CustomSubfile {
	
	private $data_ini;
	private $data_fin;
	private $deposito;
	private $fornitore;
	private $cliente;
	private $qta_conf;
	private $societa_mm;
	private $societa_pm;
	private $vettore;
	private $tipo_saldo="MOV";
	
	
	private $totals = array();
	
	private $azione;
	
	public function __construct($parameters){
		global $db, $connzend, $moduli_path;
		
		$this->data_ini = $parameters['DATA_INI'];
		$this->data_fin = $parameters['DATA_FIN'];
		$this->deposito = $parameters['DEPOSITO'];
		$this->fornitore = $parameters['FORNITORE'];
		$this->societa = $parameters['SOCIETA'];
		$this->sito = $parameters['SITO'];
		$this->cliente = $parameters['CLIENTE'];
		$this->qta_conf = $parameters['QTA_CONF'];
		$this->societa = $parameters['SOCIETA'];
		$this->societa_mm = $parameters['SOCIETA_MM'];
		$this->societa_pm = $parameters['SOCIETA_PM'];
		$this->vettore = $parameters['VETTORE'];
		$this->tipo_saldo = $parameters['TIPO_SALDO'];
		$this->azione = $parameters['AZIONE'];
		
		// Query per trovare la descrizione dell'articolo (invece di usare la routine RTLART)
	    $sql_art = "SELECT DESCRIZIONE FROM TRI_ANAART WHERE ACCESSORIO='1' AND STATO='1' AND ARTICOLO=?";
	    $this->stmt_art = $db->singlePrepare($sql_art,0,true);
	}
	
	public function getArrayCampi() {	
		global $db;
		
		$array = array();
		$array['ARTICOLO']=$db->singleColumns("1", "7", "", "Articolo");
		$array['DES_ART']=$db->singleColumns("1", "100", "", "Descrizione articolo");
		$array['SPEDITO']=$db->singleColumns("3", "15", 0, "Spedito");
		$array['RESO']=$db->singleColumns("3", "15", 0, "Reso");
		$array['SALDO']=$db->singleColumns("3", "15", 0, "Saldo");
	
		return $array;
	}
	
	public function init($parameters){
		global $db, $connzend;
		
		$this->setCols($this->getArrayCampi());
	}
	
	public function start($subfile){
		global $db, $connzend, $settings, $moduli_path, $actionContext, $tesFile, $docFile, $artFile;
		
		require_once $moduli_path.'/'.rtvModuloAzione($this->azione).'/op_resi_vuoti_commons.php';
		// Prepare della query di inserimento
		$fields = array_keys($this->getArrayCampi());
		$stmt_ins = $db->prepare("INSERT", $subfile->getTable(), null, $fields);
		if($this->fornitore!="" || $this->fornitore===true) {
			if($this->fornitore===true)
				$this->fornitore = "";
			$caus_sped = "0811";
			$caus_reso = "0006";
			//calcola il saldo per la causale spedizione
			$qta = array();
			$qta = calcolaSaldo($caus_sped, $this->fornitore, $this->societa, $this->sito, $this->deposito, $this->data_ini, $this->data_fin,"",
			$this->societa_pm, $this->societa_mm, $this->vettore, $this->tipo_saldo);	
			//showArray($qta);			
			$qta1 = array();
			foreach($qta as $key => $value){
				$qta1[$key] = array($caus_sped => $value["QTA"]);
			}	
			//calcola il saldo per la causale reso		
			$qta = array();
			$qta = calcolaSaldo($caus_reso, $this->fornitore, $this->societa, $this->sito, $this->deposito, $this->data_ini, $this->data_fin,"",
			$this->societa_pm, $this->societa_mm, $this->vettore, $this->tipo_saldo);
			//showArray($qta);"<br> $caus_sped->>>>";die();				
			$qta2 = array();
			foreach($qta as $key => $value){
				$qta2[$key] = array($caus_reso => $value["QTA"]);
			}
		}
//		else if($this->cliente!="" || $this->cliente===true) {
		else if((is_array($this->cliente) && !empty($this->cliente)) || $this->cliente===true || $this->cliente!="") {
			if($this->cliente===true)
				$this->cliente = "";
//			echo "CLI:"; var_dump($this->cliente); echo "</pre>";
			
			$caus_sped = "9001";
			$caus_reso = "9024";
			
			//calcola il saldo per la causale spedizione
			$qta = array();
			$qta = calcolaSaldoClienti($caus_sped, $this->cliente, $this->societa, $this->sito, $this->deposito, $this->data_ini, $this->data_fin
					,$this->qta_conf,"",$this->societa_pm, $this->societa_mm, $this->vettore, $this->tipo_saldo);			
			$qta1 = array();
			foreach($qta as $key => $value){
				$qta1[$key] = array($caus_sped => $value['QTA']);
			}		
				
			//calcola il saldo per la causale reso		
			$qta = array();
			$qta = calcolaSaldoClienti($caus_reso, $this->cliente, $this->societa, $this->sito, $this->deposito, $this->data_ini, $this->data_fin
					, $this->qta_conf,"", $this->societa_pm, $this->societa_mm, $this->vettore, $this->tipo_saldo);			
			$qta2 = array();
			foreach($qta as $key => $value){
				$qta2[$key] = array($caus_reso => $value['QTA']);
			}
		}
//		echo "SPEDITO:<pre>"; print_r($qta1); echo "</pre>";
//		echo "RESO:<pre>"; print_r($qta2); echo "</pre>";	
		
		//combina array spedito/reso per articolo
		$qtaTot = array();
		foreach($qta1 as $key => $value){
			$qtaTot[$key] = array($caus_sped => $value[$caus_sped], $caus_reso => 0);
		}
			
		foreach($qta2 as $key => $value){	
			if (!array_key_exists($key, $qtaTot)){			
				$qtaTot[$key] = array($caus_sped => 0, $caus_reso => $value[$caus_reso]);			
			} else {			
			    $qtaTot[$key][$caus_reso] = $value[$caus_reso];
			}
		}
		
		//ordinamento array		
		ksort($qtaTot);
		
		$this->totals["SPEDITO"] = 0;
		$this->totals["RESO"] = 0;
		$this->totals["SALDO"] = 0;
		
		foreach($qtaTot as $key => $value){
			$writeRow = array();
			$result_art = $db->execute($this->stmt_art, array($key));
  			$art_array = $db->fetch_array($this->stmt_art);
			$des_art = "";
			$des_art = $art_array['DESCRIZIONE'];
				
			$spedito = $value[$caus_sped];	
			$reso = $value[$caus_reso];
			$saldo = $value[$caus_sped] - $value[$caus_reso];
			
			$this->totals["SPEDITO"] += $spedito;
			$this->totals["RESO"] += $reso;
			$this->totals["SALDO"] += $saldo;
				
			$writeRow = array(
				"ARTICOLO" => $key,
				"DES_ART" => $des_art,
				"SPEDITO" => $spedito,
				"RESO" => $reso,
				"SALDO" => $saldo
			);
			
//			echo "ROW:<pre>"; print_r($writeRow); echo "</pre>";
			
			if(!empty($writeRow)) {
				// Inserimento della riga nel subfile
				$db->execute($stmt_ins, $writeRow);
			}
		}
	}
	
	public function body($campi, $parameters) {
		return false;
	}
	
	public function end($subfile){
		$subfile->setTotals($this->totals);
	}
	
}

?>