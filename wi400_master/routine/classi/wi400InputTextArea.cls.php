<?php

/**
 * @name wi400InputTextArea 
 * @desc Classe per la creazione di un'area di testo
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author 
 * @version 1.00 19/11/2008
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

class wi400InputTextArea extends wi400InputText {
   
	private $rows = 1;
	private $wrap = false;
	
	/**
	 * Costruttore della classe
	 *
	 * @param string $id	: ID dell'area di testo da creare
	 */
    public function __construct($id){
    	$this->setId($id);
    	$this->setType("TEXT_AREA");
    }
    
	/**
	 * Impostazione del numero di righe dell'area di testo (altezza)
	 *
	 * @param integer $rows	: numero di righe
	 */
    public function setRows($rows){
    	$this->rows = $rows;
    }
    
    /**
     * Recupero del numero di righe che compongono l'area di testo (altezza)
     *
     * @return integer
     */
    public function getRows(){
    	return $this->rows;
    }
    
    /**
     * Se impostato a true il testo inviato via form avrà "\n" se risulta troppo lungo
     *
     * @param boolean
     */
    public function setWrap($val){
    	$this->wrap = $val;
    }
    
    /**
     * Ritorna l'attributo wrap
     *
     * @return boolean
     */
    public function getWrap(){
    	return $this->wrap;
    }
    
}

?>