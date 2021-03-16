<?php

/**
 * @name wi400InputCheckbox 
 * @desc Classe per la creazione di un checkbox
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author 
 * @version 1.00 22/07/2008
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

//require_once $routine_path.'/classi/wi400Input.cls.php';

class wi400InputCheckbox extends wi400Input {

    private $size	   = "";
    private $maxLength = "";
	private $checked   = "";
    private $uncheckedValue = null;
    private $userApplicationValue = "";
	/**
	 * Costruttore della classe
	 *
	 * @param string $id	: ID del checkbox da creare
	 */
    public function __construct($id){
    	$this->setId($id);
    	$this->setType("CHECKBOX");
    }
    
    public function setSize($size){
    	$this->size = $size;
    }
    
    public function getSize(){
    	return $this->size;
    }
    
    /**
     * Impostazione del valore del campo da memorizzare in sessione per poter essere riutilizzato anche
     * in altre azioni in cui ci sia un campo con lo stesso ID
     *
     * @param unknown_type $userApplicationValue
     */
    public function setUserApplicationValue($userApplicationValue){
    	$this->userApplicationValue = $userApplicationValue;
    }
    
    public function getUserApplicationValue(){
    	return $this->userApplicationValue;
    }
    
    /**
	 * @return the $uncheckedValue
	 */
	public function getUncheckedValue() {
		return $this->uncheckedValue;
	}

	/**
	 * @param field_type $uncheckedValue
	 */
	public function setUncheckedValue($uncheckedValue) {
		$this->uncheckedValue = $uncheckedValue;
	}

	/**
     * Impostazione dello stato di selezione del checkbox
     *
     * @param boolean $checked	: true se il checkbox è selezionato, false altrimenti
     */
    public function setChecked($checked){
    	$this->checked = $checked;
    }
    
    /**
     * Recupero dello stato di selezione del checkbox
     *
     * @return boolean	Ritorna true se il checkbox è selezionato, false altrimenti
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
    
     public function dispose(){
     	echo $this->getHtml();
     }
    
    /**
     * Visualizzazione del checkbox
     *
     */
    public function getHtml(){
    	
    	$outHtml = "";
    	$readonly = "";
    	$id = $this->getId();
        if ($this->getReadonly()){
    		$readonly = "DISABLED";
    		//$id = $id."_DISABLED";
    	}
    	
    	$checkedString = "";
    	if ($this->getChecked()){
    		$checkedString = "checked";
    	}
    	
    	$onClick = array();
    	
    	//if ($this->getOnClick() != ""){
    	//	$onClick[] = $this->getOnClick();
    	//}
    	

        if ($this->getCheckUpdate()){
    		$onClick[] = "setUpdateStatus('ON')";
    	}
    	
    	if ($this->getIdList() != ""){
    		$onClick[] = "checkGridRow('".$this->getIdList()."',".$this->getRowNumber().",true)";
    	}
    	
    	if ($this->getName() == ""){
    		$this->setName($this->getId());
    	}
    	
    	if ($this->getUncheckedValue() != null){
    		$onClick[] = "updateCheckboxValue(this)";
    	}
    	if ($this->getOnClick() != ""){
    		$onClick[] = $this->getOnClick();
    	}
    	// ONCLICK
    	$onClickFunction = "";
    	if (sizeof($onClick) > 0){
	    	$onClickFunction = join(";", $onClick);
	    	$onClickFunction = "onClick=\"".$onClickFunction."\"";
    	}
    	
    	$onChangeFunction = "";
    	if ($this->getOnChange()) {
    		$onChangeFunction = "onChange=\"".$this->getOnChange()."\"";
    	}
    	
        // Mi trovo all'interno di un dettaglio	
    	if ($this->getIdDetail()!="") {
    		$outHtml = $outHtml."<td>";  	
    	}
    	$toolTip=$this->getToolTip();
		$outHtml = $outHtml.'<input type="checkBox" '.$readonly.' '.$checkedString.' '.$onClickFunction.' '.$onChangeFunction.' id="'.$id.'" name="'.$id.'" class="inputtext" value="'.$this->getValue().'" title="'.$toolTip.'">';
		
		if ($this->getUncheckedValue() != null){
			$disabledString = "";
			if ($this->getChecked()){
	    		$disabledString = "disabled";
	    	}
			$outHtml = $outHtml.'<input type="hidden" value="'.$this->getUncheckedValue().'" id="'.$this->getId().'_HIDDEN" name="'.$this->getName().'" '.$disabledString.'>';
		}
		/*if ($this->getReadonly()){
			$outHtml = $outHtml.'<input type="hidden" value="'.$this->getValue().'" id="'.$this->getId().'" name="'.$this->getName().'">';
		}*/
        if ($this->getType() == "SELECT_CHECKBOX" && $this->getLabel() != ""){
    		$outHtml = $outHtml."<label class=text for='".$this->getId()."'>".$this->getLabel()."</label>";
    	}
        // Mi trovo all'interno di un dettaglio	
    	if ($this->getIdDetail()!="") {
    		$outHtml = $outHtml."</td>";  	
    	}
    	
    	return $outHtml;
    }
    
}

?>