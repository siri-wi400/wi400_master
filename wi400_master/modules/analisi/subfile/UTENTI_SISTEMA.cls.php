<?php 

class UTENTI_SISTEMA extends wi400CustomSubfile {
	
	private $rtlent;
	
	private $des_ente = array();
	private $des_country = array();
	private $des_op_cl = array();
	
	public function __construct($parameters){
		global $db, $connzend, $moduli_path;
		
		require_once $moduli_path.'/analisi/utenti_sistema_commons.php';
		
		$this->des_op_cl = $des_op_cl;
		
		// Routine RTLENT
		$this->rtlent = new wi400Routine('RTLENT', $connzend);
		$this->rtlent->load_description();
		
		// Query descrizione nazione
		$sql_naz = "select * from FCOUNTRY";
		$result_naz = $db->query($sql_naz,false,0);
		while($row_naz = $db->fetch_array($result_naz)) {
			$this->des_country[$row_naz['COCOUN']] = $row_naz['CODESC'];
		}
	}
	
	public function getArrayCampi() {	
		global $db;
		$array = array();
		$array['USERID']=$db->singleColumns("1", "10", "", "ID Utente");
		$array['DESCRIZIONE']=$db->singleColumns("1", "100", "", "Descrizione utente");
		$array['EMAIL']=$db->singleColumns("1", "100", "", "Email");
		$array['AREA_FUN']=$db->singleColumns("1", "10", "", "Area funzionale");
		$array['DES_AREA_FUN']=$db->singleColumns("1", "100", "", "Descrizione area funzionale");
		$array['DES_USER']=$db->singleColumns("1", "30", "", "Descrizione utente");
		$array['ENTE']=$db->singleColumns("1", "4", "", "Ente");
		$array['DES_ENTE']=$db->singleColumns("1", "50", "", "Descrizione Ente");
		$array['NAZIONE']=$db->singleColumns("1", "4", "", "Nazione");
		$array['DES_NAZIONE']=$db->singleColumns("1", "30", "", "Descrizione Nazione");
		$array['STATO']=$db->singleColumns("1", "1", "", "Stato");
		$array['LAST_COL']=$db->singleColumns("1", "30", "", "Ultimo collegamento");
		$array['VALIDITA']=$db->singleColumns("1", "10", "", "Validità");
		$array['OP_CL']=$db->singleColumns("1", "1", "", "Aperto/Chiuso");
		$array['DES_OP_CL']=$db->singleColumns("1", "10", "", "Descrizione Aperto/Chiuso");
		$array['TIMESTAMP_CREAZIONE']=$db->singleColumns("1", "20", "", "Data creazione");
	
		return $array;
	}	
	
	public function init($parameters){
		global $db, $connzend;
		
		$this->setCols($this->getArrayCampi());
	}
	
	public function body($campi, $parameters){
		global $db, $connzend;
		
		// Descrizione ente
		if(!array_key_exists($campi['ESTENT'],$this->des_ente)) {
			$this->rtlent->prepare();
		    $this->rtlent->set('NUMRIC',1);
		    $this->rtlent->set('DATINV', date("Ymd"));
			$this->rtlent->set('CODICE', $campi['ESTENT']);
		    $this->rtlent->call();
		    
			$this->des_ente[$campi['ESTENT']] = "";
			if($row_enti = $this->rtlent->get('ENTI'))
	    		$this->des_ente[$campi['ESTENT']] = $row_enti['MAFDSE'];
		}
		$des_ente = $this->des_ente[$campi['ESTENT']];

		// Data ultimo collegamento
		$data = $campi['ESTDUL'];
		$time = "00:00:00";
		if(!empty($campi['ESTOUL']))
			$time = $campi['ESTOUL'];
		$unix_time = time_to_unix_timestamp($data." ".$time);
		$last_col = "";
		if($unix_time!=0)
			$last_col = date("YmdHis", $unix_time);
		
		// Descrizione nazione
		$des_naz = "";
		if(array_key_exists($campi['ESTNAZ'],$this->des_country)) {
			$des_naz = $this->des_country[$campi['ESTNAZ']];
		}
		
		// Descrizione aperto/chiuso
		$des_op_cl = "";
		if(array_key_exists($campi['ESTSTO'],$this->des_op_cl)) {
			$des_op_cl = $this->des_op_cl[$campi['ESTSTO']];
		}
		
		if($campi['ESTDCR']=="")
			$data_creazione = "00000000";
		else
			$data_creazione = dateViewToModel($campi['ESTDCR']);
		
		if($campi['ESTOCR']=="")
			$data_creazione .= "000000";
		else
			$data_creazione .= timeViewToModel($campi['ESTOCR'],6);
		
		$writeRow = array(
			$campi['ESTUTE'],
			$campi['ESTDES'],
			$campi['ESTEMA'],
			$campi['ESTFUN'],
			$campi['ESTDFU'],
			$campi['ESTDPR'],
			$campi['ESTENT'],
			$des_ente,
			$campi['ESTNAZ'],
			$des_naz,
			$campi['ESTSTA'],
			$last_col,
			$campi['ESTABI'],
			$campi['ESTSTO'],
			$des_op_cl,
			$data_creazione
		);
//		echo "CAMPI:<pre>"; print_r($campi); echo "</pre>";
//		echo "ROW:<pre>"; print_r($writeRow); echo "</pre>";
		
		return $writeRow;
	}
	
}

?>