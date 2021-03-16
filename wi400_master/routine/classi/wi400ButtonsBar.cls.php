<?php

/**
 * @name wi400ButtonsBar 
 * @desc Classe per la creazione di una barra dei bottoni
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author 
 * @version 1.00 09/11/2009
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

class wi400ButtonsBar {

	private $buttons;
	private $width = "100%";

	/**
	 * Costruttore della classe
	 * 
	 */
	public function __construct(){
		$this->buttons = array();
	}

	/**
	 * Aggiunta di un bottone
	 * 
	 * @param wi400InputButton $wi400Button		: l'oggetto bottone da aggiungere
	 */
	public function addButton(wi400InputButton $wi400Button){
		$this->buttons[] = $wi400Button;
	}
	
	public function setWidth($width) {
		$this->width = $width;
	}
	
	public function getWidth() {
		return $this->width;
	}

	/**
	 * Visualizzazione della barra dei bottoni
	 * 
	 */
	public function dispose(){	 
?>
		<table width=<?= $this->width?> cellspacing="0" cellpadding="0">
			<tr>
				<td style="border-top: 1px solid #CCCCCC; background-color: #e0e0e0">
				<?
				foreach ($this->buttons as $wi400Button){
					$wi400Button->dispose(true);
				}
				?>
				</td>
			</tr>
		</table>
<?
	}

}

?>