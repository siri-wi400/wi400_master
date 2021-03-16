<?php
//require_once $moduli_path.'/giwi400/classi/giwi400.cls.php';

class giwi400Sipe extends giwi400 {
	
	function getCustomField($rif, $myField, $obj) {
		if (is_callable('giwi400_get_field')) {
			$myField1 = giwi400_get_field($rif->getField(), $myField->getId(),$obj->getUse());
			//echo "ho un riferimento".var_dump($obj->getRiferimento());
			if (!is_object($myField1)) {
				//
			} else {
				$myField = $myField1;
			}
		}else {
			echo "giwi400_get_field non callable<br>";
		}
		
		return $myField;
	}
	
	function setFiltriAvanzati($miaLista) {
		
		$select = $miaLista->getField();
		$select = str_replace("S_GIWI_RRN, S_GIWI_IND, ", "", $select);
		//showArray($select);
		//$select = explode(" AS ", $select);
		
		//Mi reperisco il valore della select per ogni colonna
		$obj_select = array();
		
		$campo = explode(" AS ", $select);
		//showArray($campo);
		for($i=1; $i < count($campo); $i++) {
			$valore = $campo[$i];
			//echo $valore."<br>";
			if($i != count($campo)-1) {
				$pos = strpos($valore, ',');
				$idCol = substr($valore, 0, $pos);
			}else {
				$idCol = $valore;
			}
			//echo $idCol."___".$i."<br>";
			$val_prec = $campo[$i-1];
			if($i-1 != 0) {
				$pos = strpos($val_prec, ',');
				$val_prec = substr($val_prec, $pos+2);
				//echo $val_prec."_".$idCol."<br>";
			}
			
			$obj_select[$idCol] = $val_prec;
		}
		
		//showArray($obj_select);
		
		$colonne = $miaLista->getCols();
		foreach($colonne as $key => $col) {
			$descCol = $col->getDescription();
			if($col->getShow() && !in_array($descCol, array('S1_OPZION')) && substr($key, -6) != 'SCELTA') {
				//echo $key."__".$col->getDescription()."<br>";
				
				$mioFiltro = new wi400Filter($key, $descCol);
				$mioFiltro->setSqlKey($obj_select[$key]);
				$mioFiltro->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
				//$mioFiltro->setFast(true);
				$miaLista->addFilter($mioFiltro);
			}
		}
		
	}
	
	function finalizeList($miaLista, $formatCollegato) {
	    global $GIWI_DATI_FIELDS;
		$this->setFiltriAvanzati($miaLista);
		
		//$x = $this->xml;
		//showArray($x);
		$testata = $this->getDatiTestata();
		$form = $testata['I_GIWI_FRM'];
		
		//$prova = $this->manager->getDisplay()->getForm($form)->getFields();
		$fields = $this->manager->getDisplay()->getForm($form)->getFields();
		$dati = $this->getDsValues();
		//showArray($dati);
		
		$num_control = str_replace('CONTROL', '', $form);
		//echo $num_control."__num_control<br>";
		
		//$file_path = $_SESSION['GIWI400_ID'].$testata
		
		$key = 'C'.$num_control."_OPZION";
		$val = '';
		if(isset($fields[$key])) {
			/*$search = $x->xpath('//'.$key);
			if(isset($search[0])) {
				$val = $search[0]->__toString();
			}*/
			if (isset($dati[$key])) {
				$val = $dati[$key];
			}
		} else {
			// Provo a cercarli nei testi
			$datitxt = getStringaButtoniDesc($form,$testata['I_GIWI_FIL'],$testata['I_GIWI_FLI'], true);
			//echo "sono qui:".$form.$testata['I_GIWI_FIL'].$testata['I_GIWI_FLI'];
			//showArray($datitxt);
			foreach ($datitxt as $key => $value) {
				if (strpos($value, "Opzioni:")!==False) {
					$val = $value;
				}
			}
		}
		//showArray($GIWI_DATI_FIELDS);
		//showArray($testata);
		//echo $val."___value<br>";
		
		
		$opzioni = explode(" ", $val);
		unset($opzioni[0]);
		
		$arr_opzioni = array();
		foreach($opzioni as $opt) {
			$opt = trim($opt);
			if(is_numeric(substr($opt, 0, 2)) || is_numeric(substr($opt, 0, 1))) {
				$arr_opzioni[] = $opt;
			}else {
				$arr_opzioni[count($arr_opzioni)-1] .= ' '.$opt;
			}
		}
		//showArray($arr_opzioni);
		
		$id = 'S'.$num_control.'_OPZION';
		$col_opzion = $miaLista->getCol($id);
		// Se non trovato provo se esiste un campo scelta
		if (!$col_opzion) {
			$id = 'S'.$num_control.'_SCELTA';
			$col_opzion = $miaLista->getCol($id);
		}
		if($col_opzion) {
			if ($val=="") {
				$arr_opzioni[] ="1=Selezione";
			}
			$mySelect = new wi400InputSelect($id);
			$mySelect->setFirstLabel(' ');
			foreach($arr_opzioni as $label) {
				$chiave = intval(substr($label, 0, 2));
				$mySelect->addOption($label, $chiave);
				
				$action = new wi400ListAction();
				$action->setLabel($label);
				//$action->setAction('CONSOLE_GIWI400');
				//$action->setForm('AZIONI_DI_LISTA');
				$action->setScript("callUpdateListRow('$chiave')");
				$action->setSelection('SINGLE');
				$miaLista->addAction($action);
			}
			
			$col_opzion->setInput($mySelect);
			
?>
			<script type="text/javascript">
				function callUpdateListRow(valore) {
					var id = '<?=$id?>';
					var rigaSel = jQuery('.wi400-grid-row_selected,.wi400-grid-row_over_selected').attr('id');
					if(rigaSel) {
						rigaSel = rigaSel.split('-');
						//console.log(rigaSel);
						var idOption = rigaSel[0]+'-'+rigaSel[1]+'-'+id;
						var select = jQuery('#'+idOption);
						if(select) {
							select.val(valore).change();
							setTimeout(function() {
								jQuery('#ENTER').click();
							}, 1000);
						}else {
							console.log('Non ho trovato il campo select '+idOption);
						}
					}
				}
			</script>
<?php 	
			
		}
		
		//showArray($fields);
		
		
		
		// Verifico se è un lista
		// Se lista verifico se esistono delle opzioni
		// Attacco le opzioni sul campo opzioni della lista
		return $miaLista;
	}
	
	function getFormTxt($detail) {
		$testata = $this->getDatiTestata();
		$form = $testata['I_GIWI_FRM'];
		
		$stringa = getStringaButtoniDesc($testata['I_GIWI_FRM'], $testata['I_GIWI_FIL'], $testata['I_GIWI_FLI']);
		$dati = explode(' ', $stringa);
		
		//Ci sono bottoni che nella descrizione hanno lo spazio tra una parola e l'altra
		$arr_dati = array();
		foreach($dati as $d) {
			$d = trim($d);
			if(substr($d, 0, 1) == 'F') {
				$arr_dati[] = $d;
			}else {
				$arr_dati[count($arr_dati)-1] .= ' '.$d;
			}
		}
		
		//showArray($arr_dati);

		$button_stringa = array();
		foreach($arr_dati as $label) {
			list($id, $desc) = explode('=', $label);
			
			$button_stringa[$id] = $label;
		}
		
		//showArray($button_stringa);
		
		$arr_button = $this->getGiwiButton();
		
		foreach($arr_button as $id => $myButton) {
			if($id == 'ENTER') {
				$detail->addButton($myButton);
				
				setKeyAction("ENTER", "ENTER_CLASS");
			}else if(isset($button_stringa[$id])) {
				$myButton->setLabel($button_stringa[$id]);
				$detail->addButton($myButton);
				
			}else if($id == 'ROLLUP' && $this->getPrintRollUp()) {
				$detail->addButton($myButton);
			}
		}
		
		if(count($arr_button) > 0 ) {
			$this->setPrintTxtButton(true);
			$this->setShowDisplayButton(false);
		}
		
		return $detail;
	}
	
	function customFieldAttribute($myField, $idField, $val) {
		$dati = explode("_", $idField);
		if((substr($dati[0], 0, 1) == 'C' || substr($dati[0], 0, 1) == 'F') && in_array($dati[1], array("PROGPG", "USERPG", "OPZION", "INTEST"))) {
		//if(substr($dati[0], 0, 1) == 'C' && in_array($dati[1], array("PROGPG2", "USERPG2", "OPZION2", "INTEST2"))) {
			$myField = new wi400InputHidden($idField);
			$myField->setDispose(false);
			$myField->setValue($val);
		}
		
		return $myField;
	}
}