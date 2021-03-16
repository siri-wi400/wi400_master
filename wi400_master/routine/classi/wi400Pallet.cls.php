<?php

/**
 * @name wi400Pallet 
 * @desc Classe per la creazione di un pallet
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author 
 * @version 1.00 26/01/2010
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

class wi400Pallet {

	private $resize = 0.7;
	private $defaultWidth = 80;
	
	private $charge;   // EMPTY | HALF | FULL | ERROR

	private $selected;
	private $moving; // IN | OUT_D : abbassamento | OUT_M : spostamento | OUT_S : Spedizione | L : bloccato
	
	private $onClick;
	private $title;
	private $altezza;
	private $zoccolo;
	
	private $chargeImages = array(
		"EMPTY" => "E",
		"HALF"  => "H",
		"FULL"  => "F",
		"ERROR"  => "F_E",
		"MULO" => "M"
	);
							
	private $movingDescription = array(
		"IN" => "In Carico",
		"OUT_D"  => "In Abbassamento",
		"OUT_M"  => "In Movimento",
		"OUT_S"  => "In Spedizione",
		"L" => "Bloccato"
	);
	
	/**
	 * Costruttore della classe
	 *
	 * @param string $charge	: stato del pallet (EMPTY | HALF | FULL | ERROR)
	 * @param string $moving	: movimento del pallet 
	 * 							(IN | OUT_D : abbassamento | OUT_M : spostamento | OUT_S : Spedizione | L : bloccato)
	 * @param unknown_type $altezza
	 * @param unknown_type $zoccolo
	 */
	public function __construct($charge = "EMPTY", $moving = "", $altezza=148, $zoccolo=14){
		$this->charge = $charge;
		$this->moving = $moving;
		$this->altezza = $altezza*$this->resize;
		$this->zoccolo = $zoccolo*$this->resize;
	}
	
	/**
	 * Impostazione della selezione di un pallet
	 *
	 * @param boolean $selected	: se true, allora il pallet viene indicato come selezionato con uno sfondo verde
	 */
	public function setSelected($selected){
		$this->selected = $selected;	
	}
	
	/**
	 * Recupero lo stato di selezione del pallet
	 *
	 * @return boolean	Ritorna true se il pallet è selezionato
	 */
	public function getSelected(){
		return $this->selected;
	}
	
	/**
	 * Impostazione dell'operazione da eseguire nel caso in cui si cliccki sul pallet
	 *
	 * @param string $onClick	: operazione da eseguire
	 */
	public function setOnClick($onClick){
		$this->onClick = $onClick;
	}
	
	/**
	 * Recupero dell'operazione da eseguire quando si cliccka sul pallet
	 *
	 * @return string
	 */
	public function getOnClick(){
		return $this->onClick;
	}
	
	/**
	 * Impostazione del titolo (label) che compare quando si passa sopra al pallet con il mouse
	 *
	 * @param string $title	: titolo (label) da mostrare
	 */
	public function setTitle($title) {
		$this->title = $title;
	}
	
	/**
	 * Recupero del titolo (label) del pallet
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}
	
	/**
     * Recupero del codice html da utilizzare per visualizzare il pallet
     *
     * @return string
     */
	public function getHtml(){
		$imgBox = $this->chargeImages[$this->charge];
		$html = "";
		
		if ($this->moving != ""){
			$html .= "<div style='position:absolute'><img alt='".$this->movingDescription[$this->moving]."' width='".$this->defaultWidth."' height='".($this->altezza)."' onClick='".$this->onClick."' style='cursor:pointer' src='themes/common/images/map/".$this->moving.".gif'></div>";
		}
		
		$width = $this->defaultWidth;
		if ($imgBox == "M"){
			$width= 160;
			$this->altezza = 190;
		}
		
		$html .=  "<table border='0' cellpadding='1' cellspacing='0'>";
		$html .= "<tr><td title='".$this->title."'><img onClick='".$this->onClick."' style='cursor:pointer' src='themes/common/images/map/".$imgBox.".gif' WIDTH='".$width."' height='".$this->altezza."'></td></tr>";
		if ($this->charge!="MULO") {
			$html .= "<tr><td><img src='themes/common/images/map/P.gif' WIDTH='".$this->defaultWidth."' height='".$this->zoccolo."'></td></tr></table>";
		} else {
			$html .= "<tr><td><img src='themes/common/images/map/spacer.gif'></td></tr></table>";
		}

		return $html;
	}
	
	/**
	 * Visualizzazione del pallet
	 *
	 */
	public function dispose(){
		echo $this->getHtml();
	}
	
}

?>