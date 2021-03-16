<?php

class BATCHJOB_SCHEDULER extends wi400CustomSubfile {
	
	private $des_frequenza = array();
	private $des_stato = array();

	public function __construct($parameters){
		global $connzend, $moduli_path;
		
		require $moduli_path."/batch/batch_commons.php";
		
		$this->des_frequenza = $frequenza_array;
		$this->des_stato = $statoBatchScheduled;
	}
	
	public function getArrayCampi() {	
		global $db;
		$array = array();
		$array['ID']=$db->singleColumns("3", "10", 0, "Progressivo lavoro");
		$array['NOME']=$db->singleColumns("1", "10", "", "Nome lavoro");
		$array['DES_LAVORO']=$db->singleColumns("1", "100", "", "Descrizione lavoro");
		$array['XML_PATH']=$db->singleColumns("1", "100", "", "Path XML");
		$array['XML_FILE']=$db->singleColumns("1", "100", "", "File XML");
		$array['FREQUENZA']=$db->singleColumns("1", "10", "", "Frequenza");
		$array['DES_FREQUENZA']=$db->singleColumns("1", "100", "", "Descrizione frequenza");
		$array['INTERVALLO']=$db->singleColumns("3", "6", 0, "Intervallo");
		$array['FIRING']=$db->singleColumns("3", "20", 0, "Prossima esecuzione");
		$array['NUMERO_ESECUZIONI']=$db->singleColumns("3", "6", 0, "Numero esecuzioni");
		$array['LAST_FIRING']=$db->singleColumns("3", "20", 0, "Ultima esecuzione");
		$array['STATO']=$db->singleColumns("1", "1", "", "Stato");
		$array['DES_STATO']=$db->singleColumns("1", "100", "", "Descrizione stato");
	
		return $array;
	}	
	
	public function init($parameters){
		global $connzend, $db;
		
		$this->setCols($this->getArrayCampi());
	}
	
	public function body($campi, $parameters){
		global $connzend;
		
		$xml_path = dirname($campi['XML']);
		$xml_file = basename($campi['XML']);
		
	    $writeRow = array( 
			$campi['ID'],
			$campi['NOME'],
			$campi['DES_LAVORO'],
			$xml_path,
			$xml_file,
			$campi['FREQUENZA'],
			$this->des_frequenza[$campi['FREQUENZA']],
			$campi['INTERVALLO'],
			$campi['FIRING'],
			$campi['NUMERO_ESECUZIONI'],
			$campi['LAST_FIRING'],
			$campi['STATO'],
			$this->des_stato[$campi['STATO']]
		); 
			
		return $writeRow;
	}
	
}

?>