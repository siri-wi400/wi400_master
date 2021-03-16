<?php

/**
 * @name wi400Text 
 * @desc Classe per la creazione di un campo di testo
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author 
 * @version 1.00 12/08/2008
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

//require_once $routine_path.'/classi/wi400Input.cls.php';

class wi400Text extends wi400Input {

	private $format;
	private $link;
	private $target;
	private $align;
	private $script;
	private $width = "";
	private $hiddenFields;
	private $lookUp;
	private $description = "";
	    
	/**
	 * Costruttore della classe
	 *
	 * @param string $id	: ID del bottone da creare
	 */
	/**
	 * Enter description here...
	 *
	 * @param string $id			: id del campo
	 * @param string $label			: label del campo
	 * @param unknown_type $value	: valore del campo
	 * @param string $format		: formato del campo
	 * @param string $link			: link legato al valore del campo
	 * @param string $target		: target del link
	 */
    public function __construct($id, $label="", $value="", $format="", $link="", $target=""){
    	global $settings;
    	$this->setId($id);
    	$this->setType("TEXT");
    	
    	$this->setLabel($label);
    	$this->setValue($value);
    	
    	$this->setFormat($format);
    	if ($target=="") {
    		if (isset($settings['link_target_default'])) {
    			$target = $settings['link_target_default'];
    		} else {
    			$target = "_TOP";
    		}
    	}
    	$this->target = strtolower($target);
    	$this->link = $link;
    	
    	$this->align = "";
    	
    	$this->hiddenFields = true;
    }
    
    /**
     * Questa funzione viene usata unicamente in wi400Search con i filtri avanzati
     * per avere l'oggetto poi in ajax_decode_model.php
     * 
     * @param unknown $lookUp
     */
    public function setLookUp($lookUp){
    	if (isset($lookUp) && $lookUp != "" && sizeof($lookUp->getFields())==0){
    		$lookUp->addField($this->getId());
    	}
    	$this->lookUp = $lookUp;
    }
    
    /**
     * Questa funzione viene usata unicamente in wi400Search con i filtri avanzati
     * per avere l'oggetto poi in ajax_decode_model.php
     *
     * @param unknown $lookUp
     */
    public function getLookUp(){
    	return $this->lookUp;
    }
    
    /**
     * Impostazione dell'allineamento del testo del campo
     *
     * @param string $align	: tipo di allineamento del testo (left, right)
     */
	public function setAlign($align){
    	$this->align = $align;
    }
    
    /**
     * Recupero del tipo di allineamento del testo del campo
     *
     * @return unknown
     */
    public function getAlign(){
    	return $this->align;
    }
    /**
     * Impostazione del formato del campo
     *
     * @param string $format	: formato del campo
     */
	public function setFormat($format){
    	$this->format = $format; 
    }
    
    /**
     * Setta la larghezza del contenitore che contiene il testo
     * 
     * Usato per allineare il testo a destra rispetto alla grandezza del contenitore
     * Usato insieme a setAlign
     * 
     * @param integer $value
     */
    public function setWidth($value) {
    	$this->width = $value;
    }
    
    /**
     * Reperisce la larghezza del contenitore che contiene il testo
     * 
     * @return integer
     */
    public function getWidth() {
    	return $this->width;
    }
    
    /**
     * Recupero del formato del campo
     *
     * @return string
     */
	public function getFormat(){
    	return $this->format;
    }
	
    /**
     * Impostazione del link legato al valore del campo
     *
     * @param string $link	: indirizzo legato al valore del campo
     */
	public function setLink($link){
    	$this->link = $link; 
    }
    
    /**
     * Recupero del link legato al valore del campo
     *
     * @return string
     */
    public function getLink(){
    	return $this->link;
    }
    
    /**
     * Impostazione del link legato al valore del campo
     *
     * @param string $link	: indirizzo legato al valore del campo
     */
    public function setScript($script){
    	$this->script = $script;
    }
    
    /**
     * Recupero del link legato al valore del campo
     *
     * @return string
     */
    public function getScript(){
    	return $this->script;
    }
    
    public function setHiddenFields($hide){
    	$this->hiddenFields = $hide;
    }
    
    public function getHiddenFields(){
    	return $this->hiddenFields;
    }

    /**
     * Stringa di descrizione aggiunta successivamente al setValue
     * 
     * @param string $val
     */
    public function setDescription($val) {
    	$this->description = $val;
    }
    
    /**
     * Ritorna la stringa di descrizione aggiunta successivamente al setValue
     *
     * @return string
     */
    public function getDescription() {
    	return $this->description;
    }
    
    /**
     * Visualizzazione del campo di testo
     *
     */
    public function dispose(){
    	global $temaDir;
    	
    	if ($this->format != ""){
    		$this->setValue(wi400List::applyFormat($this->getValue(), $this->format));
			/*if (is_callable("wi400_format_".$this->format,false)){
				$this->setValue(call_user_func("wi400_format_".$this->format, $this->getValue()));
			}
			else{
				echo "<br>Funzione di formattazione wi400_format_".$this->format." non implementata.";
				exit();
			}*/
		}
		
    	// Allineamento testo
		$textStyle = "";
		if ($this->getStyle() != ""){
			$textStyle = "style=\"".$this->getStyle()."\"";
		}
		
		$textClass = "text";
    		if ($this->getStyleClass() != ""){
			$textClass = $this->getStyleClass();
		}
		$toolTip=$this->getToolTip();
		
		
		echo "<td class='".$textClass."'".$textStyle." title=\"".$toolTip."\">";
		
		if ($this->getLink() != "") 
			echo "<a class=rowDetail href='".$this->getLink()."' target='".$this->target."'>";
		
		if($this->getScript()!="")
			echo "<a class=rowDetail href='javascript:".$this->getScript()."'>";
		
		if($this->getWidth()) {
			$align = "left";
			if($this->getAlign()) {
				$align = $this->getAlign();
			}
			echo "<div style='text-align: $align; width: {$this->getWidth()}px;'>";
		}
		echo $this->getValue();
		echo $this->getDescription() ? " - ".$this->description : "";
		if($this->getWidth())
			echo "</div>";
		
		if ($this->getLink() != "" || $this->getScript()!="") 
			echo "</a>";
		// Campi di input nascosti per averlo in request
		if($this->getHiddenFields()===true) {
			echo "<input id='".$this->getId()."' name='".$this->getId()."' type='hidden' value='".strip_tags($this->getValue())."'>";
		}
		
		echo "</td>";
	}
	
	function getHtml() {
		$this->dispose();
	}
   
}
?>