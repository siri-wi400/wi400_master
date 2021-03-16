<?php

/**
 * @name wi400Bay 
 * @desc Classe per la generazione di una bay
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author 
 * @version 1.00 08/02/2010
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

class wi400Bay {

	private $resize = 0.7;
	
	private $type; // Tipo di bay: DEFAULT | ROLL | ecc.
	private $rows; // Array di wi400Pallet
	private $altezzaPosto;
	
	private $next;
	private $prev;
	private $action;
	
	/**
	 * Costruttore della classe
	 *
	 * @param string $type	: Tipo della bay da creare
	 */
	public function __construct($type = "DEFAULT"){
		global $actionContext;
		$this->rows = array();
		$this->altezzaPosto = array();
		
		$prev = false;
		$next = false;
	}
	
	/**
	 * Impostazione del tipo della bay
	 *
	 * @param string $type	: Tipo della bay
	 */
	public function setType($type){
		$this->type = $type;
	}
	
	/**
	 * Ritorna il tipo della bay
	 * 
	 * @return array
	 */
	public function getType(){
		return $this->type;
	}
	

	public function setNext($next){
		$this->next = $next;
	}
	
	public function getNext(){
		return $this->next;
	}
	
	public function setPrev($prev){
		$this->prev = $prev;
	}
	
	public function getPrev(){
		return $this->prev;
	}

	/**
	 * Inerimento di un piano intero di pallet in una bay
	 * 
	 * @param array $pallets	: array di oggetti wi400Pallet da inserire nella bay
	 */
	public function addPalletsRow($pallets, $altezzaPosto=123){
		$this->rows[] = $pallets;
		$this->altezzaPosto[] = $altezzaPosto*$this->resize;
	}
	
	/**
	 * Inerimento di un solo pallet in un piano specifico della bay
	 * 
	 * @param wi400Pallet $pallet	: oggetto pallet da inserire in un piano della bay
	 * @param integer $row			: piano in cui inserire il pallet
	 */
	public function addPallet($pallet, $row, $altezzaPosto=123){
		if (!isset($this->rows[$row])) 
			$this->rows[$row] = array();
		$this->rows[$row][] = $pallet;
		
		if (!isset($this->altezzaPosto[$row])){
			$this->altezzaPosto[$row] = $altezzaPosto*$this->resize;
		}else if ($altezzaPosto != 123){
			$this->altezzaPosto[$row] = $altezzaPosto*$this->resize;
		}
	}
	
	
	/**
	 * Visualizzazione della bay
	 * 
	 */
	public function dispose(){
		global $actionContext;
?>
		<table cellspacing="0" cellpadding="0" border="0">
			<tr>
<?
	if ($this->prev){
?>				
	<td width="30" valign="middle"><img style="cursor:pointer" onClick="doSubmit('<?= $actionContext->getAction() ?>&NAVIGATION=PREV','<?= $actionContext->getForm() ?>')" src="themes/common/images/map/leftArrow.gif" hspace="5"></td>
	<td style="background-image: url('themes/common/images/map/rightDisabled.gif');" width="11" valign="top">&nbsp;</td>
							
<?
	}
?>
				<td style="background-image: url('themes/common/images/map/left.gif');" width="9" valign="top">&nbsp;</td>
				<td><table cellspacing="0" cellpadding="0" border="0">
<?
				$rowCounter = 0;
				foreach ($this->rows as $pallets){
?>
					<tr><td align="center" height="<?= $this->altezzaPosto[$rowCounter] ?>"><table  height="<?= $this->altezzaPosto[$rowCounter] ?>" cellspacing="0" cellpadding="0" border="0">
<?
					foreach ($pallets as $palletBay){
						
						$bgcolor = "";
						if (is_array($palletBay)){
							$arrayPallet = $palletBay;
							foreach ($arrayPallet as $pallet){
								if ($pallet->getSelected()) {
									$bgcolor = '#72ff00';
									break;
								}
							}
						}else{
							$arrayPallet = array();
							$arrayPallet[] = $palletBay;
							if ($palletBay->getSelected()) {
								$bgcolor = '#72ff00';
							}
						}
						
						echo "<td valign='bottom' bgcolor='".$bgcolor."'>";
						foreach ($arrayPallet as $pallet){
							echo $pallet->getHtml();
						}
						echo "</td>";
					}
?>
					</tr></table></td></tr>
<? 
					$rowCounter++;
					if ($rowCounter < sizeof($this->rows)) { 
?>
						<tr><td height="17" style="background-image: url('themes/common/images/map/row.gif');"></td></tr>
<? 
					}
				}
?>
				</table></td>
				<td style="background-image: url('themes/common/images/map/right.gif');" width="9" valign="top">&nbsp;</td>
<?
	if ($this->next){
?>				
	<td style="background-image: url('themes/common/images/map/leftDisabled.gif');" width="11" valign="top">&nbsp;</td>
	<td width="30" valign="middle"><img style="cursor:pointer" onClick="doSubmit('<?= $actionContext->getAction() ?>&NAVIGATION=NEXT','<?= $actionContext->getForm() ?>')" src="themes/common/images/map/rightArrow.gif"  hspace="5"></td>							
<?
	}
?>
			</tr>
		</table>
<?
	}

}

?>