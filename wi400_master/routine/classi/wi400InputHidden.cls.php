<?php

/**
 * @name wi400InputHidden 
 * @desc Classe per la creazione di un campo hidden
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author 
 * @version 1.00 24/07/2008
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

//require_once $routine_path.'/classi/wi400Input.cls.php';

class wi400InputHidden extends wi400Input {
	
	private $dispose = true;
	
	/**
	 * Costruttore della classe
	 *
	 * @param string $id	: ID del campo da creare
	 */
    public function __construct($id){
    	$this->setId($id);
    	$this->setType("HIDDEN");
    }
    
    /**
     /* Indica se l'oggetto devi essere stampa nel dettaglio o no
     * @see wi400Input::addValidation()
     */
    public function setDispose($val) {
    	$this->dispose = $val;
    }
    
    public function getDispose() {
    	return $this->dispose;
    }
/*
    public function setValue($value=""){
    	if (!is_array($value)) {
    		$this->value = $value;
    	}
    }
*/    
    /**
     * Recupero del codice html che costituisce il campo hidden
     *
     * @return unknown
     */
    public function getHtml(){
    	if ($this->getName() == ""){
			$this->setName($this->getId());
		}
		
		$val = str_replace('"', '&quot;', $this->getValue());
		
    	$html = '<input type="hidden" id="'.$this->getId().'" name="'.$this->getName().'" value="'.$val.'" '.$this->getDisabled().'>';
    	
    	return $html;
    }
    
    /**
     * Inserimento del codice html del campo hidden all'interno del resto del codice
     *
     */
    public function dispose(){
		echo $this->getHtml();
    }
}
?>