<?php
/**
 * @name wi400DBSupport
 * @desc Classe con funzioni di supporto al DB
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author
 * @version 1.01 03/03/2010
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */
class wi400DBSupport {
	private $warning; // Array con Warning sui controlli
	private $error; // Errori sui controlli
	private $ds; // Ds/Tabella
	private $structure; //Struttura della tabella letta da DS
	private $tracciato; // Tracciato Inizializzato
	private $field; // Campi da controllare passati
	private $normalize; // Normalizzare il tracciato correggendo Warning
	private $isErrore = False;
	private $isWarning = False;
	/**
	 * @desc Ritorna la struttura dati reperita
	 * @return the $structure
	 */
	public function getStructure() {
		return $this->structure;
	}
	/**
	 * @return the $isErrore
	 */
	public function getIsErrore() {
		return $this->isErrore;
	}
	/**
	 * @return the $isWarning
	 */
	public function getIsWarning() {
		return $this->isWarning;
	}
	/**
	 * @return the $normalize
	 */
	public function getNormalize() {
		return $this->normalize;
	}

	/**
	 * @param string $normalize
	 */
	public function setNormalize($normalize) {
		if ($normalize=="") $this->setErrore("Valorizzare Normalize");
		$this->normalize = $normalize;
	}
	private function setErrore($errore) {
		$this->error[]=$errore;
		$this->isErrore = True;
	}
	public function getErrore() {
		showArray($this->error);
	}
	private function setWarning($warning) {
		$this->error[]=$warning;
		$this->isWarning = True;
	}
	public function getWarning() {
		showArray($this->warning);
	}
	public function getTracciato() {
//		showArray($this->tracciato);
		return $this->tracciato;
	}
//	public function getTNormalize() {
//		showArray($this->normalize);
//	}
	/**
	 * Costruttore della classe
	 * @param string $ds: Nome DS Da controllare
	 * @param array $field: Campi da controllare
	 */
	function __construct($ds, $field, $normalize=True) {
		global $db;
		$this->ds = $ds;
		$this->structure = $db->columns($ds);
//		showArray($this->structure);
		$this->field = $field;
		$this->normalize = $normalize;
		}
	
	function check() {
		global $db;
		$this->warning = array();
		$this->error = array();
		$this->tracciato = array();
		foreach ($this->field as $key=>$value) {
			// Controlli su campi passati
//			showArray($this->structure);
			if (isset($this->structure[$key])) {
				// Verifico se il dato è congruende con il contenuto
				// Estraggo la struttura dei dati	
				$campi = $this->structure[$key];
				$type = $campi['DATA_TYPE'];
				$type_string = $campi['DATA_TYPE_STRING'];
				$num_scale = $campi['NUM_SCALE'];
				$lenght_precision = $campi['LENGTH_PRECISION'];
				$video_lenght = $campi['VIDEO_LENGTH'];
					// Se di tipo Stringa - char'1'/varchar'12' - controllo la lunghezza
					if ($type == '1' || $type == '12'){
					if ($lenght_precision < strlen($this->field[$key])){
					$this->isWarning=True;
					$this->warning[] = array("FIELD"=>$key, $this->field[$key]." Lunghezza campo errata");}
					// Normalizzo
					$this->tracciato[$key] = substr($this->field[$key],strlen(intval($this->field[$key]))-$lenght_precision,$lenght_precision);
					}
					// Se di tipo Numerico - e campo nullo warning
					if ($type == '3' && $this->field[$key] == ''){
						$this->isWarning=True;
						$this->warning[] = array("FIELD"=>$key, $this->field[$key]." Campo numerico mancante");
						$this->tracciato[$key] = "0";
					}
					// Se di tipo Numerico - numeric'3' - controllo la lunghezza Interi e decimali
					// Interi
					if ($type == '3'){
						if ($lenght_precision < strlen(intval($this->field[$key]))){
							$this->isWarning=True;
						$this->warning[] = array("FIELD"=>$key, $this->field[$key]." Lunghezza interi errata");}
						// Normalizzo
						$this->tracciato[$key] = substr($this->field[$key],strlen(intval($this->field[$key]))-$lenght_precision,$lenght_precision);
						// Decimali su solo interi
						if ($this->field[$key] - intval($this->field[$key]) > 0 && $num_scale == 0){
							$decimal = strlen(substr($this->field[$key],stripos($this->field[$key],".")+1));
							if ($num_scale < $decimal){
								$this->isWarning=True;
								$this->warning[] = array("FIELD"=>$key, $this->field[$key]." Lunghezza decimali errata");
//								// Normalizzo
//								$this->tracciato[$key] = substr($this->field[$key],strlen(intval($this->field[$key]))-$lenght_precision,$lenght_precision);
								}
						}
					//	}
					}
					// Decimali
					if ($type == '3' && $num_scale > 0){
						$decimal = strlen(substr($this->field[$key],stripos($this->field[$key],".")+1));
					if ($num_scale < $decimal){
						$this->isWarning=True;
					$this->warning[] = array("FIELD"=>$key, $this->field[$key]." Lunghezza decimali errata");}
					// Normalizzo
					$this->tracciato[$key] = substr($this->field[$key],0,stripos($this->field[$key],".")+1+$num_scale);
					// Se interi oltre il limite
					if (strlen(intval($this->field[$key])) > $lenght_precision){
//					$this->tracciato[$key] = substr($this->field[$key],strlen(intval($this->field[$key]))-$lenght_precision,$lenght_precision+1).substr($this->field[$key],stripos($this->field[$key],".")+1,$num_scale);}
					$this->tracciato[$key] = substr($this->field[$key],strlen(intval($this->field[$key]))-$lenght_precision,$lenght_precision+1+$num_scale);}
						
					}
					// Se di tipo Data - datetime2'88'
					if ($type == '88'){
						$this->tracciato[$key] = $this->field[$key];
						// Se data non inserita propongo
						if 	($this->field[$key] == ""){
							$this->isWarning=True;
							$this->warning[] = array("FIELD"=>$key, $this->field[$key]." Data mancante");
							$this->tracciato[$key] = "2001-01-01 00:00:00.0000000";
						}
					if	(checkDateFormat($this->field[$key],"TIMESTAMP")===false){
						$this->isErrore=True;
						$this->error[] = array("FIELD"=>$key, $this->field[$key]." Data errata");
						$this->tracciato[$key] = $this->field[$key];}
					}
					// Se di tipo Data - date'8'
					if ($type == '8'){
						$this->tracciato[$key] = $this->field[$key];
						// Se data non inserita propongo
						if 	($this->field[$key] == ""){
							$this->isWarning=True;
							$this->warning[] = array("FIELD"=>$key, $this->field[$key]." Data mancante");
							$this->tracciato[$key] = "2001-01-01";
						}
						if	(checkDateFormat($this->field[$key])===false){
							$this->isErrore=True;
							$this->error[] = array("FIELD"=>$key, $this->field[$key]." Data errata");
							$this->tracciato[$key] = $this->field[$key];}
					}
			}else {
			$this->isErrore=True;
			$this->error[] = array("FIELD"=>$key, $this->field[$key]." Campo non presente");
			}
		}
		
	}
}
?>