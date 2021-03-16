<?php

/**
 * @name wi400InputRadio 
 * @desc Classe per la creazione di un radio button
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author 
 * @version 1.01 09/11/2009
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

//require_once $routine_path.'/classi/wi400Input.cls.php';

class wi400InputRadio extends wi400Input {

    private $size	   = "";
    private $maxLength = "";
	private $checked   = "";
    
	/**
	 * Costruttore della classe
	 *
	 * @param unknown_type $id
	 */
    public function __construct($id){
    	$this->setId($id);
    	$this->setType("SELECT_RADIO");
    }
    
    public function setSize($size){
    	$this->size = $size;
    }
    
    public function getSize(){
    	return $this->size;
    }
    
    /**
     * Impostazione dello stato di selezione del radio button
     *
     * @param boolean $checked	: true se il radio button è selezionato, false altrimenti
     */
    public function setChecked($checked){
    	$this->checked = $checked;
    }
    
    /**
     * Recupero dello stato di selezione del radio button
     *
     * @return boolean	Ritorna true se il radio button è selezionato, false altrimenti
     */
    public function getChecked(){
    	return $this->checked;
    }
    
    public function setMaxLength($maxLength){
    	$this->maxLength = $maxLength;
    }
    
    public function getMaxLength(){
    	return $this->maxLength;
    }
    
    /**
     * Recupero del codice html da utilizzare per visualizzare il radio button
     *
     * @return unknown
     */
    public function getHtml(){
    	$htmlString = "";
    	$checkedString = "";
    	if ($this->getChecked()){
    		$checkedString = "checked";
    	}
    	
    	$onClick = array();
    	if ($this->getOnClick() != ""){
    		$onClick[] = $this->getOnClick();
    	}
        if ($this->getCheckUpdate()){
    		$onClick[] = "setUpdateStatus('ON')";
    	}
    	$onClickFunction = "";
    	if (sizeof($onClick)>0){
    		$onClickFunction = join(";", $onClick);
    		$onClickFunction = "onClick=\"".$onClickFunction."\"";
    	}
    	
    	if ($this->getName() == ""){
    		$this->setName($this->getId());
    	}
    	$toolTip=$this->getToolTip();
    	$htmlString = '<input type="radio" '.$checkedString.' '.$onClickFunction.' id="'.$this->getId().'" name="'.$this->getName().'" class="inputtext" value="'.$this->getValue().'" title="'.$toolTip.'">';

        if ($this->getType() == "SELECT_RADIO" && $this->getLabel() != ""){
    		$htmlString = $htmlString."<label class=text for='".$this->getId()."'>".$this->getLabel()."</label>";
    	}
    	
    	return $htmlString;
    	
    }
    
    /**
     * Visualizzazione del radio button
     *
     */
    public function dispose(){
    	echo $this->getHtml();
    }
}
?>