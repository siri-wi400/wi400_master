<?php

/**
 * @name wi400TextPanel 
 * @desc Classe per la creazione di un pannello di testo
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author 
 * @version 1.00 19/11/2008
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

class wi400TextPanel extends wi400InputText {
   
	private $height;
	private $overflow;
	private $fontFamily;
	private $preFormatted;
	private $widthParent = false;
	
	
	/**
	 * Costruttore della classe
	 *
	 * @param string $id	: ID dell'area di testo da creare
	 */
    public function __construct($id){
    	$this->setId($id);
    	$this->setType("TEXT_PANEL");
    	$this->setHeight(250);
    	$this->setOverflow("auto");
    	$this->setPreFormatted(true);
    	$this->setFontFamily("'Courier New', Courier, monospace");
    }
    
    
    
    /**
	 * @return the $preFormatted
	 */
	public function getPreFormatted() {
		return $this->preFormatted;
	}

	/**
	 * @param field_type $preFormatted
	 */
	public function setPreFormatted($preFormatted) {
		$this->preFormatted = $preFormatted;
	}

	/**
	 * @return the $fontFamily
	 */
	public function getFontFamily() {
		return $this->fontFamily;
	}

	/**
	 * @param field_type $fontFamily
	 */
	public function setFontFamily($fontFamily) {
		$this->fontFamily = $fontFamily;
	}

	/**
	 * @return the $height
	 */
	public function getHeight() {
		return $this->height;
	}

	/**
	 * @param field_type $height
	 */
	public function setHeight($height) {
		$this->height = $height;
	}
    
    /**
	 * @return the $overflow
	 */
	public function getOverflow() {
		return $this->overflow;
	}

	/**
	 * @param field_type $overflow
	 */
	public function setOverflow($overflow) {
		$this->overflow = $overflow;
	}
	
	/**
	 * Il testo si adatta alla larghezza del contenitore padre
	 * 
	 * @return boolean 
	 */
	public function getWidthParent() {
		return $this->widthParent;
	}
	
	/**
	 * Il testo si adatta alla larghezza del contenitore padre
	 * 
	 * @param boolean
	 */
	public function setWidthParent($bool) {
		$this->widthParent = $bool;
	}

	/**
     * Visualizzazione del campo di testo
     *
     */
    public function dispose(){
    	echo $this->getHtml();
    	
    }
    
     public function getHtml(){
    	global $temaDir;
    	
    	$htmlOutput = "";
    	
    	$preStart = "";
    	$preEnd = "";

    	
		// Mi trovo all'interno di un dettaglio	
    	if ($this->getIdDetail()!="") {
    		$htmlOutput = $htmlOutput."<td>";
    	}
    	
    	
    	$content = $this->getValue();
    	
    	if ($this->getPreFormatted()){
    		$preStart = "<pre>";
    		$preEnd = "</pre>";
    	}else{
    		
    		$content = nl2br($this->getValue());
    	}
    	
    	$testo = "<div class=\"wi400TextPanel\" id=\"".$this->getId()."\" style=\"font-family:".$this->getFontFamily().";width:100%;height:".$this->getHeight()."px; ".($this->getWidthParent() ? "position: absolute;" : "")."overflow:".$this->getOverflow()."\" name=\"".$this->getName()."\">".$preStart.$content.$preEnd."</div>";
    	
    	if($this->getWidthParent()) {
    		$htmlOutput .= "<div style='position: relative; width: 100%; height: ".$this->getHeight()."px;'>
    							$testo
    						</div>";
    	}else {
    		$htmlOutput .= $testo;
    	}
     
		if ($this->getIdDetail()!=""){
			$htmlOutput = $htmlOutput."</td>";
		}
		
		return $htmlOutput;
     
     }
}

?>