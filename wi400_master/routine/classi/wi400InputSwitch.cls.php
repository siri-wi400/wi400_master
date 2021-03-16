<?php

/**
 * @name wi400InputSwitch 
 * @desc Classe per la creazione di uno switch button
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author 
 * @version 1.00 06/06/2011
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

require_once $routine_path.'/classi/wi400InputCheckBox.cls.php';

class wi400InputSwitch extends wi400InputCheckbox {

	private $onLabel;
	private $offLabel;
	private $description = "";
	private $customTools = array();
	
	public function setOnLabel($onLabel){
    	$this->onLabel = $onLabel;
    }
    
    public function getOnLabel(){
    	return $this->onLabel;
    } 
    
    public function setOffLabel($offLabel){
    	$this->offLabel = $offLabel;
    }
    
    public function getOffLabel(){
    	return $this->offLabel;
    } 
    
    public function setDescription($description){
    	$this->description = $description;
    }
    
    public function getDescription(){
    	return $this->description;
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
	 * Costruttore della classe
	 *
	 * @param string $id	: ID del checkbox da creare
	 */
    public function __construct($id){
    	global $jsArray;
    	$this->setId($id);
    	$this->setType("CHECKBOX");
    	$this->onLabel = "ON";
    	$this->offLabel = "OFF";
    	$jsArray[] = "base/js/wi400-switch.js";
    }
    
	/**
     * Visualizzazione del checkbox
     *
     */
    public function getHtml(){
    	
    	$outHtml = "";
    	
    	$checkedString = "";
    	$disabledString = "";
    	$value = "off";
    	$onButton = $this->getOnLabel();
    	$offButton = $this->getOffLabel();
    	
    	if ($this->getChecked()){
    		$checkedString = "checked";
    		$value = "on";
    	}
    	
    	$onClick = array();
    	
    	if ($this->getOnClick() != ""){
    		$onClick[] = $this->getOnClick();
    	}
    	

        if ($this->getCheckUpdate()){
    		$onClick[] = "setUpdateStatus('ON')";
    	}
    	
    	if ($this->getIdList() != ""){
    		$onClick[] = "checkGridRow('".$this->getIdList()."',".$this->getRowNumber().",true)";
    	}
    	
    	if ($this->getName() == ""){
    		$this->setName($this->getId());
    	}
    	
    	if($this->getDisabled() == true) {
    		$disabledString = 'disabled="disabled"';
    	}	
    	
    	// ONCLICK
    	$onClickFunction = "";
    	if (sizeof($onClick) > 0){
	    	$onClickFunction = "onClick='".join(";", $onClick)."'";
    	}
    	
		$outHtml .= '<span class="checkSwitch '.$value.'" '.$onClickFunction.'>';
		$outHtml .= '<input class="checkSwitch" value="'.$this->getValue().'" type="checkbox" '.$checkedString.' name="'.$this->getName().'" id="'.$this->getId().'" '.$disabledString.'/>';
		$outHtml .= '<div class="checkSwitchInner"><div class="checkSwitchOn">'.$onButton.'</div><div class="checkSwitchHandle"></div><div class="checkSwitchOff">'.$offButton.'</div></div>';
		if($disabledString) {
			$outHtml .= '<div style=" position: absolute; width: 100%; height: 100%; left: 0px; top: 0px; background: rgba(192,192,192, 0.4);"></div>';
		}
		$outHtml .= '</span>';
		$outHtml .= '<script>var '.$this->getId().'_LABELS = ["'.$this->onLabel.'","'.$this->offLabel.'"];</script>';

    	
    	return $outHtml;
    }
}


