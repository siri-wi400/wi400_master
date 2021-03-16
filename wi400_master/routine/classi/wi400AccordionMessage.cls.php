<?php

class wi400AccordionMessage {

	private $id;
	private $header;
	private $type;
	private $color_header;
	private $body;
	private $formato_testo;
	private $risposta;
	private $rightIcon = "";
	private $allegati = array();
	private $log_not;
	private $target;

	/**
	 * Costruttore della classe
	 *
	*/
	public function __construct($key) {
		$this->id = $key;
	}

	public function getId() {
		return $this->id;
	}
	
	public function setHeader($header) {
		$this->header = $header;
	}
	
	public function getHeader() {
		return $this->header;
	}
	
	public function setColorHeader($color) {
		$this->color_header = $color;
	}
	
	public function getColorHeader() {
		return $this->color_header;
	}
	
	public function setBody($body) {
		$this->body = $body;
	}
	
	public function getBody() {
		return $this->body;
	}
	
	public function setFormatoTesto($format) {
		$this->formato_testo = $format;
	}
	
	public function getFormatoTesto() {
		return $this->formato_testo;
	}
	
	public function setRisposta($val) {
		$this->risposta = $val;
	}

	public function getRisposta() {
		return $this->risposta;
	}
	
	/**
	 * Setta l'icona posizionata a destra del messaggio. Per info guardate http://astronautweb.co/snippet/font-awesome/
	 * es. "fa-music" per l'icona della musica
	 * 
	 * @param string $val
	 */
	public function setRightIcon($val) {
		$this->rightIcon = $val;
	}
	
	/**
	 * Ritorna l'icona posizionata a destra del messaggio
	 * 
	 * @return string
	 */
	public function getRightIcon() {
		return $this->rightIcon;
	} 
	
	public function setAllegati($val) {
		$this->allegati = $val;
	}
	
	public function getAllegati($val) {
		return $this->allegati;
	}
	
	public function setType($tipo) {
		$this->type = $tipo;
		
		switch($this->type) {
			case "INFO": $this->color_header = "rgb(46, 181, 221);"; break;
			case "WARNING": $this->color_header = "rgb(236, 255, 63);"; break;
			case "ERROR": $this->color_header = "rgb(255, 87, 87);"; break;
			case "PRODOTTO": $this->color_header = "rgb(0, 204, 0);"; break;
			case "SEGRETERIA": $this->color_header = "rgb(250, 186, 25);"; break;
		}
	}
	
	public function getType() {
		return $this->type;
	}
	
	public function setLogNot($time) {
		$this->log_not = $time;
	}
	
	public function getLogNot() {
		return $this->log_not;
	}
}

?>