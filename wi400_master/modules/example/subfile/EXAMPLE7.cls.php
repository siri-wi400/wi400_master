<?php

class EXAMPLE7 extends wi400CustomSubfile {
	
	private $stmt;
	// Costruttore della classe
	public function __construct($parameters){

	}
	// Funzione per recupero descrizione dei campi del subfile
	public function getArrayCampi() {	
        global $db;
        
		$array = array();
		$array['UTENTE']=$db->singleColumns("1", "10", "", "Utente");
		$array['MENU']=$db->singleColumns("1", "20", "", "Menu");
		$array['DESCRIZIONE']=$db->singleColumns("1", "30", "", "Descrizione");

		return $array;
	}
	// Inizializzazione del subfile	
	public function init($parameters){
		global $db;

		$this->setCols($this->getArrayCampi());
		$sql = 'SELECT DESCRIZIONE FROM FMNUSIRI WHERE MENU=?';
		$this->stmt = $db->singlePrepare($sql, 1);		
	}
	// Body -- richiamato ad ogni esecuzione del fetch della query
	public function body($campi, $parameters){
		global $db;
		
		$do = $db->execute($this->stmt, array($campi['MENU']));
		$row = $db->fetch_array($this->stmt);

		$descrizione = $row['DESCRIZIONE'];
		
		$writeRow = array(
			$campi['USER_NAME'],
			$campi['MENU'],
			$descrizione
		);

		return $writeRow;

	}

}

?>