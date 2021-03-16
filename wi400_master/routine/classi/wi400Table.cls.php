<?php

/**
 * @name wi400Table 
 * @desc Classe per la creazione di una tabella
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author 
 * @version 1.00 25/05/2009
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

class wi400Table extends wi400Input {
	
	private $cols;
	private $rows;
	private $keys;
	private $selectable;
	private $hasInput;
	private $extraTables;
	private $show_header;
	private $use_div;
	private $fixed_width;
	private $sub_grid_type;			// "table", "rows"
	
	 /**
	 * Costruttore della classe
	 *
	 * @param string $id	: ID della tabella da creare
	 */
 	public function __construct($id){
    	$this->setId($id);
    	$this->setType("TABLE");
    	
    	$this->keys = array ();
    	$this->cols = array();
    	$this->rows = array();
    	$this->selectable = false;
    	$this->extraTables = array();
    	$this->show_header = true;
    	$this->use_div = true;
    	$this->fixed_width = false;
    	$this->sub_grid_type = 'table';
    }
    
    /**
     * Impostazione della selezionabilità delle righe della tabella
     *
     * @param boolean $selectable	: se true compaiono dei radio buttons per selezionare le righe
     */
    public function setSelectable($selectable){
    	$this->selectable = $selectable;
    }
    
    /**
     * Recupero dello stato di selezionabilità della tabella
     *
     * @return boolean	Ritorna true se le righe della tabella sono selezionabili
     */
	public function getSelectable(){
    	return $this->selectable;
    }
    
    /**
     * Aggiunta di una colonna
     *
     * @param object wi400Column $col	: colonna da aggiungere
     */
	public function addCol($col){
		if (!is_object($col)){
			echo "ERRORE: wi400Table->addCol richiede un oggetto di tipo wi400Column in input";
			exit();
		}
		$this->cols[] = $col;
	}
	
	/**
	 * Aggiunta di un array di colonne
	 *
	 * @param array $cols	: array di object wi400Column da aggiungere alla tabella
	 */
	public function setCols($cols){
		$this->cols = $cols;
	}
	
	public function getCols() {
		return $this->cols;
	}
	
	/**
	 * Aggiunta di una riga alla tabella
	 *
	 * @param array $row	: array dei valori della riga
	 * @param string $key	: id della riga
	 */
	public function addRow($row, $key = "", $extraTable = false){
		$this->rows[] = $row;
		$this->keys[] = $key;
		$this->extraTables[] = $extraTable;
	}
	
	/**
	 * Aggiunta di un array di righe
	 *
	 * @param array $rows	: array di righe da aggiungere alla tabella
	 */
	public function setRows($rows){
		$this->rows = $rows;
	}
	
	public function getRows() {
		return $this->rows;
	}
	
	public function setExtraTables($extraTable) {
		$this->extraTables = $extraTable;
	}
	
	public function getExtraTables() {
		return $this->extraTables;
	}
	
	public function setShowHeader($show) {
		$this->show_header = $show;
	}
	
	public function getShowHeader() {
		return $this->show_header;
	}
	
	public function setUseDiv($use) {
		$this->use_div = $use;
	}
	
	public function getUseDiv() {
		return $this->use_div;
	}
	
	public function getHasInput() {
		return $this->hasInput;
	}
	
	public function setFixedWidth($fixed) {
		$this->fixed_width = $fixed;
	}
	
	public function getFixedWidth() {
		return $this->fixed_width;
	}
	
	public function setSubGridType($type) {
		$this->sub_grid_type = $type;
	}
	
	public function getSubGridType() {
		return $this->sub_grid_type;
	}
	
	 /**
     * Recupero del codice html da utilizzare per visualizzare la tabella
     *
     * @return string
     */
/*	
	@todo: LARGHEZZA SOTTOTABELLA SFALSATA: 
		table.fixed { table-layout:fixed; width:90px; word-break:break-all; }
		table.fixed { table-layout:fixed; width:20px; overflow:hidden; word-wrap:break-word; }
		style="display: none;"
*/
	public function getHtml(){
		$outHtml = "";

		$tableWidth = 0;
		$colWidthTot = 0;
		foreach ($this->cols as $col){
			if ($col->getWidth() != ""){
				$tableWidth += $col->getWidth();
				$colWidthTot += $col->getWidth();
			}
		}
		
		if ($this->getSelectable()){
			$tableWidth += 5;
		}
		
//		echo "EXTRA:<pre>"; print_r($this->extraTables); echo "</pre>";
		if (isset($this->extraTables[0]) && $this->extraTables[0]!==false){
			$tableWidth += 5;
		}
		
		if(empty($tableWidth))
			$tableWidth = "100%";
		
		if ($tableWidth > 0){
			if(!(stripos($tableWidth,"%") > 0 || stripos($tableWidth,"px") > 0)){
				$tableWidth .= "px";
			}
				
		}
//		echo "TAB ".substr($this->getId(), 40)." - WIDTH: $tableWidth<br>";
		
		if($this->getUseDiv()===true)
			$outHtml = $outHtml.'<div class="work-area" style="display: table">';
		
		$outHtml = $outHtml."<table width='".$tableWidth."' cellpadding='0' cellspacing='0' class='wi400-grid ".$this->getStyleClass()."'>";

		if($this->getShowHeader()) {
		$outHtml = $outHtml."<tr class='wi400-grid-header' style='height:20px'>";
		
		if ($this->getSelectable()){
			$outHtml = $outHtml."<td class='wi400-grid-header-first-cell' width='5'>&nbsp;</td>";
		}
		
		if (!empty($this->extraTables) && $this->extraTables[0]!==false){
			$outHtml = $outHtml."<td class='wi400-grid-header-first-cell' width='5'>&nbsp;</td>";
		}
		
		foreach ($this->cols as $col){
			$colWidth = "";
			$colValue = "";
			$fixed_style = "";
			if ($col->getWidth() != ""){
				$colWidth = "width='".$col->getWidth()."'";
				if($this->fixed_width===true) {
					$fixed_style = "wi400-col-fixed-width";
				}
			}
			$colValue = $col->getDescription();
			if ($colValue == ""){
				$colValue = $col->getKey();
			}	
			$outHtml = $outHtml."<td class='wi400-grid-header-cell $fixed_style' ".$colWidth.">".$colValue."</td>";	
		}
		$outHtml = $outHtml."</tr>";
		}
/*		
		$rowCounter = 0;
		
		foreach ($this->rows as $row){		
			$outHtml = $outHtml."<tr style='height:30px' class='wi400-grid-row'>"; 
			$colCounter = 0;
			
			if ($this->getSelectable()){
				$selectRadio = new wi400InputRadio($this->getId());
				if ($this->keys[$rowCounter] == $this->getValue()){
					$selectRadio->setChecked(true);
				}
				$selectRadio->setValue($this->keys[$rowCounter]);
				$outHtml = $outHtml."<td class='wi400-grid-row-cell' width='5'>".$selectRadio->getHtml()."</td>";
			}
			
			$extraTable = false;
			if(isset($this->extraTables[$rowCounter]))
				$extraTable = $this->extraTables[$rowCounter];
			if ($extraTable!==false){
				if($extraTable!==true)
					$sub_rows = $extraTable->getRows();
//				echo "SUB ROW:<pre>"; print_r($sub_tab->getRows()); echo "</pre>";
				
				$outHtml = $outHtml."<td class='wi400-grid-row-cell' width='5'>";
				if(!empty($sub_rows)) {
					$outHtml = $outHtml."<img id='".$extraTable->getId()."-".$rowCounter."-img' onclick='openExtraRowDetail(\"".$extraTable->getId()."\",".$rowCounter.")' style='cursor:pointer' src='themes/common/images/grid/expand.png'>";
				}
				$outHtml = $outHtml."</td>";
			}
			
			foreach ($row as $val){
				$cellStyle = "";
				
				if(is_array($val)) {
					$value = $val['VALUE'];
					$cellStyle = $val['STYLE'];
				}else if (strpos($val, "EVAL:")===0){
					$evalValue = substr($val,5).";";
					eval('$rowTableValue='.$evalValue);
					$value = $rowTableValue;
				}else{
					$value = $val;
				}

				$colAlign = "";
				if (isset($this->cols[$colCounter])){
					$col = $this->cols[$colCounter];
					if ($col->getAlign() != ""){
						$colAlign = "align='".$col->getAlign()."'";
					}
					
					if ($col->getFormat() != ""){
						$value = wi400List::applyFormat($value, $col->getFormat());
					}
					
				}
				
				$colStyle = "";
				if ($col->getStyle() != ""){
					$defaultStyle = $col->getStyle();
				
					if (is_array($defaultStyle)>0){
						$condition = false;
						foreach($defaultStyle as $rowCondition){
							$evalValue = substr($rowCondition[0],5).";";
							eval('$condition='.$evalValue.';');
							if ($condition){
								$colStyle = $rowCondition[1];
								break;
							}
						}
					}else {
						$colStyle = $col->getStyle();
					}
				}
				
				$colWidth = "";
				$fixed_style = "";
				if ($col->getWidth() != ""){
					$colWidth = "width='".$col->getWidth()."'";
					if($this->fixed_width===true) {
						$fixed_style = "wi400-col-fixed-width";
					}
				}
				
				$inputField = false;
				if ($col->getInput() != null){
					$this->hasInput = true;
					// Colonna con input
					
					$inputField = $col->getInput();
					$inputFieldId = $this->getId()."_".$this->keys[$rowCounter]."_".$col->getKey();
					
					$inputField->setId($inputFieldId);
					$inputField->setName($inputFieldId);

					if ($inputField->getType()=='CHECKBOX'){
						$inputField->setChecked($value == $inputField->getValue());
					}else{
						$inputField->setValue($value);	
					}
					
					if ($this->getIdList() != null){
						// Autoselezione riga lista
						$inputField->setRowNumber($this->getRowNumber());
						$inputField->setIdList($this->getIdList());
					}
				}
				
				if ($value == "") $value = "&nbsp;";
				$outHtml = $outHtml."<td class='wi400-grid-row-cell ".$colStyle." ".$cellStyle." ".$fixed_style."' ".$colAlign." $colWidth >";
				
				// incremento contatore colonne
				$colCounter++;
				// Ci sono campi input ed è l'ultima colonna aggiungo campo indicazioni chiave
				if ($this->hasInput && $colCounter == sizeof($row)){
					$hiddenRowKey = new wi400InputHidden($this->getId()."[]");
					$hiddenRowKey->setValue($this->keys[$rowCounter]);
					$outHtml = $outHtml.$hiddenRowKey->getHtml();
				}
				
				if ($inputField) $outHtml = $outHtml.$inputField->getHtml(); else $outHtml = $outHtml.$value;
				$outHtml = $outHtml."</td>";
				
			}
			$outHtml = $outHtml."</tr>";
			if ($extraTable!==false && $extraTable!==true){
				$outHtml = $outHtml."<tr id='".$extraTable->getId()."-".$rowCounter."' style='display:none'>";
					$outHtml = $outHtml."<td style='background-color:#ededed' width='5'></td>";
					$outHtml = $outHtml."<td colspan='".sizeof($this->cols)."' width='$colWidthTot' style='$fixed_style'>";
						$outHtml = $outHtml.$extraTable->getHtml();
					$outHtml = $outHtml."</td>";
				$outHtml = $outHtml."</tr>";
			}
			$rowCounter++;
		}
*/
		$outHtml = $this->print_rows($outHtml, $this);
		
		$outHtml = $outHtml."</table>";
		
		if($this->getUseDiv()===true)
			$outHtml = $outHtml.'</div>';
		
		return $outHtml;
	}
	
	private function print_rows($outHtml, $grid, $hide=false) {
		$rowCounter = 0;
		
		foreach ($grid->rows as $row){
			$row_class = "wi400-grid-row";
			$row_style = "style='height:30px'";
			if($hide===true) {
				$row_class = "hide-table-row";
				$row_style = "style='display:none'";
			}
//			echo "ROW CLASS: $row_class - STYLE: $row_style<br>";
			$outHtml = $outHtml."<tr $row_style class='$row_class'>";
			$colCounter = 0;
		
			if ($grid->getSelectable()){
				$selectRadio = new wi400InputRadio($grid->getId());
				if ($grid->keys[$rowCounter] == $grid->getValue()){
					$selectRadio->setChecked(true);
				}
				$selectRadio->setValue($grid->keys[$rowCounter]);
				$outHtml = $outHtml."<td class='wi400-grid-row-cell' width='5'>".$selectRadio->getHtml()."</td>";
			}
			
//			echo "EXTRA TABLES:<pre>"; print_r($grid->extraTables); echo "</pre>";

			$extraTable = false;
			if(isset($grid->extraTables[$rowCounter]))
				$extraTable = $grid->extraTables[$rowCounter];
//			echo "EXTRA TABLE:<pre>"; print_r($extraTable); echo "</pre>";

			if ($extraTable!==false){
				if($extraTable!==true)
					$sub_rows = $extraTable->getRows();
//				echo "SUB ROW:<pre>"; print_r($sub_rows); echo "</pre>";
		
				$outHtml = $outHtml."<td class='wi400-grid-row-cell' width='5'>";
				if(!empty($sub_rows)) {
					if($this->sub_grid_type=="table")
						$outHtml = $outHtml."<img id='".$extraTable->getId()."-".$rowCounter."-img' onclick='openExtraRowDetail(\"".$extraTable->getId()."\",".$rowCounter.")' style='cursor:pointer' src='themes/common/images/grid/expand.png'>";
					else if($this->sub_grid_type=="rows")
					$outHtml = $outHtml."<img id='".$extraTable->getId()."-".$rowCounter."-img' onclick='hide_table_rows(\"".$extraTable->getId()."\",".$rowCounter.")' style='cursor:pointer' src='themes/common/images/grid/expand.png'>";
				}
				$outHtml = $outHtml."</td>";
			}
			
			if($hide===true) {
				$outHtml = $outHtml."<td class='wi400-grid-row-cell' width='5'>";
			}
		
			foreach ($row as $val){
				$cellStyle = "";
		
				if(is_array($val)) {
					$value = $val['VALUE'];
					$cellStyle = $val['STYLE'];
				}else if (strpos($val, "EVAL:")===0){
					$evalValue = substr($val,5).";";
					eval('$rowTableValue='.$evalValue);
					$value = $rowTableValue;
				}else{
					$value = $val;
				}
				// Patch per vecchie versioni con colonna array Index e non associativo
				if (!isset($grid->cols[$colCounter])) {
					$grid->cols = array_values($grid->cols);
				}
				$colAlign = "";
				if (isset($grid->cols[$colCounter])){
					$col = $grid->cols[$colCounter];
					if ($col->getAlign() != ""){
						$colAlign = "align='".$col->getAlign()."'";
					}
		
					if ($col->getFormat() != ""){
						// @todo: Capire se sono già formattati in partenza ...
						$value = wi400List::applyFormat($value, $col->getFormat());
					}
		
				}
		
				$colStyle = "";
				if ($col->getStyle() != ""){
					$defaultStyle = $col->getStyle();
		
					if (is_array($defaultStyle)>0){
						$condition = false;
						foreach($defaultStyle as $rowCondition){
							$evalValue = substr($rowCondition[0],5).";";
							eval('$condition='.$evalValue.';');
							if ($condition){
								$colStyle = $rowCondition[1];
								break;
							}
						}
					}else {
						$colStyle = $col->getStyle();
					}
				}
		
				$colWidth = "";
				$fixed_style = "";
				if ($col->getWidth() != ""){
					$colWidth = "width='".$col->getWidth()."'";
					if($grid->fixed_width===true) {
						$fixed_style = "wi400-col-fixed-width";
					}
				}
		
				$inputField = false;
				if ($col->getInput() != null){
					$grid->hasInput = true;
					// Colonna con input
		
					$inputField = $col->getInput();
					$inputFieldId = $grid->getId()."_".$grid->keys[$rowCounter]."_".$col->getKey();
		
					$inputField->setId($inputFieldId);
					$inputField->setName($inputFieldId);
		
					if ($inputField->getType()=='CHECKBOX'){
						$inputField->setChecked($value == $inputField->getValue());
					}else{
						$inputField->setValue($value);
					}
		
					if ($grid->getIdList() != null){
						// Autoselezione riga lista
						$inputField->setRowNumber($grid->getRowNumber());
						$inputField->setIdList($grid->getIdList());
					}
				}
		
				if ($value == "") $value = "&nbsp;";
				$outHtml = $outHtml."<td class='wi400-grid-row-cell ".$colStyle." ".$cellStyle." ".$fixed_style."' ".$colAlign." $colWidth >";
		
				// incremento contatore colonne
				$colCounter++;
				// Ci sono campi input ed è l'ultima colonna aggiungo campo indicazioni chiave
				if ($grid->hasInput && $colCounter == sizeof($row)){
					$hiddenRowKey = new wi400InputHidden($grid->getId()."[]");
					$hiddenRowKey->setValue($grid->keys[$rowCounter]);
					$outHtml = $outHtml.$hiddenRowKey->getHtml();
				}
		
				if ($inputField) $outHtml = $outHtml.$inputField->getHtml(); else $outHtml = $outHtml.$value;
				$outHtml = $outHtml."</td>";
		
			}
			$outHtml = $outHtml."</tr>";
			
			if ($extraTable!==false && $extraTable!==true){
				if($this->sub_grid_type=="table") {				
					$outHtml = $outHtml."<tr id='".$extraTable->getId()."-".$rowCounter."' style='display:none'>";
					$outHtml = $outHtml."<td style='background-color:#ededed' width='5'></td>";
					$outHtml = $outHtml."<td colspan='".sizeof($this->cols)."' width='$colWidthTot' style='$fixed_style'>";
					$outHtml = $outHtml.$extraTable->getHtml();
					$outHtml = $outHtml."</td>";
					$outHtml = $outHtml."</tr>";
				}
				else if($this->sub_grid_type=="rows") {
					$outHtml = $this->print_rows($outHtml, $extraTable, true);
				}
			}
			$rowCounter++;
		}
		
		return $outHtml;
	}
	
	/**
	 * Visualizzazione della tabella
	 *
	 */
	public function dispose(){
		echo $this->getHtml();
	}
	
}
?>