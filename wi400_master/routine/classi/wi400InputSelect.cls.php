<?php

/**
 * @name wi400InputSelect 
 * @desc Classe per la creazione di una drop-down list
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author 
 * @version 1.00 22/07/2008
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

//require_once $routine_path.'/classi/wi400Input.cls.php';

class wi400InputSelect extends wi400Input {

	private $options;
	private $multiple;
	private $firstLabel = "";
	private $size;
	private $changeButtons = false;
	
	private $styles = array();
    
	/**
	 * Costruttore della classe
	 *
	 * @param string $id	: ID della drop-down list da creare
	 */
    public function __construct($id){
    	$this->setId($id);
    	$this->setType("SELECT");
    	$this->setMultiple(false);
    	$this->options = array();
    }
    
    /**
     * Aggiunta di un'opzione
     *
     * @param unknown_type $value	: valore da visualizzare a video
     * @param unknown_type $key		: valore effetivo dell'elemento della lista
     */
    public function addOption($value, $key = null){
    	if ($key === null || trim($key) == ""){		// @todo Togliere la condizione $key=="" ?
    		$key = $value;
    	}
    	$this->options[$key] = $value;
    }
    
    /**
     * Imposzione del tipo di selezione possibile
     *
     * @param boolean $isMultiple	: tipo di selezione posibile (true se multipla, false se singola)
     */
    public function setMultiple($isMultiple){
    	$this->multiple = $isMultiple;
    }
    
    /**
     * Recupero del tipo di selezione possibile
     *
     * @return boolean	Ritorna true se è possibile una selezione multipla, false per una selezione singola
     */
    public function getMultiple(){
    	return $this->multiple;
    } 

	/**
     * Impostazione dell'altezza della lista di selezone multipla
     *
     * @param integer $size	: numero di righe da visualizzare per la finestra di selezione multipla
     */
    public function setSize($size) {
    	$this->size = $size;
    }
    
    /**
     * Recupero dell'altezza della lista di selezione multipla
     *
     * @return integer
     */
    public function getSize() {
    	return $this->size;
    }
    
    /**
     * Impostazione dell'elenco delle opzioni della drop-down list a partire da un array
     *
     * @param array $options	: array di opzioni
     */
    public function setOptions($options){
    	$this->options = $options;
    }
    
    /**
     * Recupero delle opzioni della drop-down list
     *
     * @return array
     */
    public function getOptions(){
    	return $this->options;
    }   
    
    /**
     * Imposta il messaggio presente nella prima selezione (nulla)
     *
     * @param string $firstLabel	: messaggio nella prima selezione (nulla)
     */
    public function setFirstLabel($firstLabel){
    	$this->firstLabel = $firstLabel;
    }
    
    /**
     * Recupera il messaggio presente nella prima selezione (nulla)
     *
     * @return string
     */
    public function getFirstLabel(){
    	return $this->firstLabel;
    }   
    
    function setStyle($style, $key=null, $value=null) {
    	$this->styles[$style][$key] = $value;
	}
    
    function getStyles() {
    	return $this->styles;
    }
    
    function getStyle($id=null,$key=null) {
    	return $this->styles[$id][$key];
    }
	
    /**
     * Settato a true visualizza 2 bottoni affianco al select
     * per scegliere l'opzione precedente o successiva a quella scelta 
     * 
     * @param boolean $val
     */
    function setChangeButtons($val) {
		$this->changeButtons = $val;
    }
    
    /**
     * Ritorna true se il 2 bottoni per cambiare opzione
     * solo visibili altrimenti false
     * 
     * @return boolean
     */
    function getChangeButtons() {
    	return $this->changeButtons;
    }
    
    /**
     * Aggiunge un customTool al campo di testo
     *
     * @param object wi400CustomTool
     */
    public function addCustomTool($tool) {
    	$this->customTools[] = $tool;
    }
    
     
    /**
     * Recupero i tool associati alla classe wi400CustomTool
     *
     * @return array di wi400CustomTool
     */
    public function getCustomTool() {
    	return $this->customTools;
    }
    
    /**
     * Visualizzazione della drop-down list
     *
     */
    public function getHtml(){
//    	echo "ID:".$this->getId()."<br>"; die();
		$id = substr($this->getId(),strrpos($this->getId(),"-")+1);
/*		
    	if($id=="TIPO_PICKING") {
    		echo "STYLES:<pre>"; print_r($this->styles); echo "</pre>";
    		die();
    	}
*/    	
		$outputHtml="";
    	// ONCHANGE
    	$onChange = array();
       	//if ($this->getOnChange() != ""){
    	//	$onChange[] = $this->getOnChange();
    	//}
    	
    	// Mi trovo all'interno di un dettaglio	
    	if ($this->getIdDetail()!="") {
    		$outputHtml .="<td>";  	
    	}
    	
    	if ($this->getName() == ""){
			$this->setName($this->getId());
		}
		
   	
		// Mi trovo all'interno di una lista
		if ($this->getIdList() != ""){
    		$onChange[] = "checkGridRow('".$this->getIdList()."',".$this->getRowNumber().",true)";
		}
		if ($this->getOnChange() != ""){
			$onChange[] = $this->getOnChange();
		}
		if ($this->getCheckUpdate()){
			$onChange[] = "setUpdateStatus('ON')";
		}
		if($this->getChangeButtons()) {
			$onChange[] = "checkDisabledButtonSelect('{$this->getId()}')";
		}
		
		$onChangeFunction = join(";", $onChange);
    	$onChangeFunction = "onChange=\"".$onChangeFunction."\"";
    	
    	$outputHtml.="<select";
		if ($this->getMultiple()){
			$outputHtml .= " multiple='true'";
		}
		if ($this->getDisabled()){
			$outputHtml.=" disabled";
		}
    	if ($this->getSize()) {
			$outputHtml.=" size='".$this->getSize()."'";
		}

		if (!$this->getReadonly()){
			$outputHtml.=" id='".$this->getId()."'";
		}else {
			$outputHtml.=" disabled";
		}
		
		$outputHtml.=" name='".$this->getName();
		if($this->getMultiple()) {
			$outputHtml .= "[]'";
		}else {
			$outputHtml .= "'";
		}
		
		$multi_style = "";
		if($this->getMultiple()) {
			$multi_style = "style='margin-top: 10px; margin-bottom: 10px;'";
		}
		
		$outputHtml.=" class='inputtext' $onChangeFunction $multi_style>";
		if ($this->firstLabel != "") {
			$selected = "";
			if($this->getMultiple()) {
				$values = $this->getValue();
				if (!isset($values[0]) || !$values[0]) {
					$selected = "selected";
				}
			}else {
				if (trim($this->getValue()) === "") {
					$selected = "selected";
				}
			}
//			echo "SEL: $selected<br>";
			
			$outputHtml.="<option value=\"\" $selected >$this->firstLabel</option>";
		}
		
		$valore = trim($this->getValue());
		$valore = (string)$valore;
//		echo "<font color='blue'>VALORE: "; var_dump($valore); echo "</font><br>";
		
//		echo "<font color='green'>FIRST LABEL: ".$this->firstLabel."</font><br>";
//		echo "OPTIONS:<pre>"; print_r($this->options); echo "</pre>";
		
		$valori = $this->getValue();
//		echo "VALUE: "; var_dump($valori); echo "<br>";

		foreach ($this->options as $optionKey => $optionValue) {
//			echo "OPTION KEY: "; var_dump($optionKey); echo "<br>";

			$selected = "";
			if($this->getMultiple()) {
				if(count($valori) >= 1) {
					if(in_array($optionKey, $valori)) 
						$selected = "selected";
				}
			}
			else {
/*				
//				if ($optionKey == $this->getValue())
				if (!($this->firstLabel!="" && trim($this->getValue())==="") && $optionKey == $this->getValue())
//				if (!($this->firstLabel!="" && trim($this->getValue())==="") && $optionKey === $this->getValue())
					$selected = "selected";
*/				
				$optionKey = (string)$optionKey;

				if (!($this->firstLabel!="" && $valore==="") && $optionKey===$valore)
					$selected = "selected";
			}
//			echo "SEL: $selected<br>";
			
			$outputHtml.="<option value=\"$optionKey\" $selected";
			
			if(!empty($this->styles) && 
				array_key_exists($id, $this->styles) &&
				array_key_exists($optionKey, $this->styles[$id])
			) {
				$outputHtml.= $this->styles[$id][$optionKey];
			}
			$outputHtml.= ">".$optionValue."</option>";
		}
		
		$outputHtml.="</select>";
		
		if($this->changeButtons) {
			$pk = $this->getId(); 
			$disabled = "";
			if(count($this->options) == 1) $disabled = "disabled"; 
			$outputHtml .= "&nbsp;&nbsp;<button id='prev_$pk' option='0' $disabled onClick='changeOptionSelect(\"".$pk."\", this)'>&lt;</button>";
			$outputHtml .= "&nbsp;<button id='next_$pk' option='1' $disabled onClick='changeOptionSelect(\"".$pk."\", this)'>&gt;</button>";
			if(!$disabled) {
				$outputHtml .= "<script>jQuery('#$pk').ready(function() { checkDisabledButtonSelect('$pk'); });</script>";
			}
		}

		if ($this->getReadonly()){
			$hiddenField = new wi400InputHidden($this->getId());
			//$hiddenField->setId($this->getId());
			$hiddenField->setName($this->getName());
			$hiddenField->setValue($this->getValue());
			$hiddenField->getHtml();
		}

        if ($this->getIdDetail()!="") {
    		$outputHtml.="</td>";  	
    	}
    	return $outputHtml;
    }
    function dispose() {
		 echo $this->getHtml();
    }   
}
?>