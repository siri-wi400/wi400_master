<?php
require_once 'giwi400Display.cls.php';
require_once 'modules/giwi400/console_giwi400_commons.php';
class giwi400 {
	
	private $id = 0;
	private $testata = 'DSWIHEAD';
	private $datiTestata = array();
	protected $xml;
	private $azione = 'CONSOLE_GIWI400';
	private $attributeColor = array(
		'BLU', 'GREEN', 'PINK', 'RED', 'TOURQUESE', 'WHITE', 'YELLOW'
	);
	private $condition = array();
	protected $manager;
	private $showDatiField = false;
	private $showDisplayButton = true;
	//private $var_condition = array('INH1','INH3','INH2','INH5','INH4','INH7','INH9','INH8','INKB','INKA','INH6','INKD','INKF','INKE','INKH','INKG','INKJ','INKL','INKK','INKN','INKM','INKI','INKQ','INKS','INKR','INKU','INKT','INKW','INKY','INKX','INL1','INL3','INL2','INLR','INKV','INKP','INKC','INL5','INL7','INL6','INMR','INL9','INOB','INOA','INL8','INOD','INOF','INOE','INOV','INU1','INRT','INOG','INU3','INU5','INU4','INU8','INU7','INU6','INU2','INOC','IN03','IN02','IN05','IN07','IN06','IN04','IN1P','IN09','IN11','IN10','IN13','IN15','IN14','IN12','IN08','IN01','INL4','IN17','IN19','IN18','IN21','IN23','IN22','IN20','IN25','IN27','IN26','IN29','IN31','IN30','IN28','IN24','IN33','IN35','IN34','IN37','IN39','IN38','IN36','IN41','IN43','IN42','IN45','IN47','IN46','IN44','IN40','IN49','IN51','IN50','IN53','IN55','IN54','IN52','IN57','IN59','IN58','IN61','IN63','IN62','IN60','IN56','IN48','IN32','IN65','IN67','IN66','IN69','IN71','IN70','IN68','IN73','IN75','IN74','IN77','IN79','IN78','IN76','IN72','IN81','IN83','IN82','IN85','IN87','IN86','IN84','IN89','IN91','IN90','IN93','IN95','IN94','IN97','IN99','IN98','IN96','IN92','IN88','IN80','IN64','IN16');
	private $var_condition = array('IN03','IN02','IN05','IN07','IN06','IN04','IN09','IN11','IN10','IN13','IN15','IN14','IN12','IN08','IN01','IN17','IN19','IN18','IN21','IN23','IN22','IN20','IN25','IN27','IN26','IN29','IN31','IN30','IN28','IN24','IN33','IN35','IN34','IN37','IN39','IN38','IN36','IN41','IN43','IN42','IN45','IN47','IN46','IN44','IN40','IN49','IN51','IN50','IN53','IN55','IN54','IN52','IN57','IN59','IN58','IN61','IN63','IN62','IN60','IN56','IN48','IN32','IN65','IN67','IN66','IN69','IN71','IN70','IN68','IN73','IN75','IN74','IN77','IN79','IN78','IN76','IN72','IN81','IN83','IN82','IN85','IN87','IN86','IN84','IN89','IN91','IN90','IN93','IN95','IN94','IN97','IN99','IN98','IN96','IN92','IN88','IN80','IN64','IN16');
	private $target = '';
	private $dsValues = array();
	private $allProtect = false;
	private $enable_get_field = false;
	private $giwiButton = array();
	private $printTxtButton = false;
	private $printRollUp = false;
	
	public function __construct($xml, $id, $manager) {
		global $messageContext;
		
		$this->setId($id);
		$this->setXml($xml);
		
		$testata = $this->getDatiTestata();
		if(!$manager) {
			$this->manager = new giwi400Manager($testata['I_GIWI_FIL'], $testata['I_GIWI_FLI']);
			$errori = $this->manager->getErrors();
			foreach($errori as $errore) {
				$messageContext->addMessage('ERROR', $errore);
			}
			if($errori) return;
		}else {
			$this->manager = $manager;
		}
		$this->getCoditionValues();
		$_SESSION['GIWI_CURRENT_FILE']=$testata['I_GIWI_FIL'];
		$_SESSION['GIWI_CURRENT_FLIB']=$testata['I_GIWI_FLI'];
		
		//showArray($this->manager);
		
		$form = $this->manager->getDisplay()->getForm($testata['I_GIWI_FRM']);
		
		//echo $testata['I_GIWI_FRM']."___";
		if($form && $form->getIsWindow()) {
			$this->target = 'WINDOW';
		}
		
		$this->enable_get_field = wi400Detail::getDetailValue('CONSOLE_GIWI400_NOME_PROGRAM', 'GIWI400_GET_FIELD');
		
		//Verifico se dovrò stampare una lista o no
		$type = $this->manager->getDisplay()->getForm($testata['I_GIWI_FRM'])->getType();
		if($type == 'SFLCTL') {
			$formatCollegato = $this->manager->getDisplay()->getForm($testata['I_GIWI_FRM'])->getFormatCollegato();
			if($formatCollegato) $this->printRollUp = true;
		}
	}
	
	function setId($id) {
		$this->id = $id;
	}
	
	function getId() {
		return $this->id;
	}
	
	function getManager() {
		return $this->manager;
	}
	
	function setTarget($val) {
		$this->target = $val;
	}
	
	function getTarget() {
		return $this->target;
	}
	
	function setAllProtect($val) {
		$this->allProtect = $val;
	}
	
	function getAllProtect() {
		return $this->allProtect;
	}
	
	function setXml($string_xml) {
		if($string_xml) {
			$xml = new SimpleXMLElement($string_xml);
			
			$this->xml = $xml;
		}
	}
	
	function getXml() {
		return $this->xml;
	}
	
	/**
	 * Ritorna true se sono visibili i customTool per vedere gli attributi del campo
	 *  
	 * @return boolean
	 */
	function getShowDatiField() {
		return $this->showDatiField;
	}
	
	/*
	 * Visualizza o meno il customTool affinco al campo per poter vedere gli attributi impostati
	 * 
	 * @param boolean
	 */
	function setShowDatiField($val) {
		$this->showDatiField = $val;
	}
	
	function getShowDisplayButton() {
		return $this->showDisplayButton;
	}
	
	function setShowDisplayButton($val) {
		$this->showDisplayButton = $val;
	}
	
	/**
	 * Ritorna true se sono stati stampati i bottoni con le descrizioni
	 * false se non sono stati stampati
	 * @return boolean
	 */
	function getPrintTxtButton() {
		return $this->printTxtButton;
	}
	
	function setPrintTxtButton($val) {
		$this->printTxtButton = $val;
	}
	
	function getPrintRollUp() {
		return $this->printRollUp;
	}
	
	function setPrintRollUp($val) {
		$this->printRollUp = $val;
	}
	
	/**
	 * Reperisco i valori dei campo inputText e i valori degli attributi dal I_GIWI_DSF impostato
	 * 
	 */
	function getDsValues() {
	    global $db, $GIWI_DATI_FIELDS;
		if(empty($this->dsValues)) {
			$testata = $this->getDatiTestata();
			$ds = $testata['I_GIWI_DSF'];
			$tabella = $testata['I_GIWI_DBT'];
			$sql = $this->manager->getSelectSQL($testata['I_GIWI_FRM'], $tabella, False);
			$xmlValues = array();
			if ($sql!="*NOFIELDS") {
				$result = $db->singleQuery($sql);
				$xmlValues = $db->fetch_array($result);
			}
			//echo $sql;
			//$xmlValues = array();
			
			/*if(is_object($this->xml->$ds[0])) {
				foreach($this->xml->$ds[0] as $key => $val) {
					$xmlValues[$key] = $val->__toString();
				}
			}*/
			
			//showArray($xmlValues);
			
			$extraValues = loadExtraData($testata['I_GIWI_FLI'], $testata['I_GIWI_FIL'], $testata['I_GIWI_FRM']);
			if(!is_array($extraValues)) $extraValues = array();
			
			$xmlValues = array_merge($extraValues, $xmlValues);
			
			$this->dsValues = $xmlValues;
		}
		//showArray($this->dsValues);
		$GIWI_DATI_FIELDS = array_merge($GIWI_DATI_FIELDS, $this->dsValues);
		return $this->dsValues;
	}
	
	function getCoditionValues() {
		global $actionContext;
		
		if($actionContext->getForm() == "VIS_MASCHERA") {
			$this->condition = $_SESSION['GIWI_CONDIZIONI'];

		}else {
			
			$string_indicatori = $this->xml->INDICATORI->__toString();
			
			sort($this->var_condition);

			$key_condition = $this->var_condition;
			$value_indicatori = str_split($string_indicatori, 1);
			
			$condition = array_combine($key_condition, $value_indicatori);
			//showArray($condition);
			
			foreach($condition as $key => $val) {
				$this->condition[$key] = $val == '1' ? 'true' : 'false';
			}
			
			//showArray($this->condition);
			
			
			/*$start = false;
			foreach($this->xml as $key => $val) {
				if($key == 'WI4DOLFILE') break;
				if($key == 'WI4DOLCMD') {
					$start = true;
				}else if($start) {
					$this->condition[$key] = $val == '1' ? 'true' : 'false';
					//if($key == 'IN01') $this->condition[$key] = 'true';
					//if($key == 'IN03') $this->condition[$key] = 'true';
				}
			}
			
			foreach($this->var_condition as $condition) {
				if(!isset($this->condition[$condition])) {
					$this->condition[$condition] = 'false';
				}
			}*/
		}
		
		/*$this->condition['IN78'] = 'true';
		$this->condition['IN61'] = 'true';
		$this->condition['IN62'] = 'true';*/
	}
	
	// Reperisco i dati della maschera (dato input/output)
	/*function getDatiMaschera($maschera, $form) {
		global $db;
		
		$dati = array();
		
		$where = array(
			"OT5KEY='".$maschera."_GIWI400'",
			"OT5FMT='$form'"
		);
		
		$sql = "SELECT * FROM ZOT5FLDL WHERE ".implode(" and ", $where);
		$rs = $db->query($sql);
		while($row = $db->fetch_array($rs)) {
			$dati[$row['OT5FLD']] = $row;
		}
		
		return $dati;
	}*/
	
	function getDatiTestata() {
		if(empty($this->datiTestata)) {
			$testata = $this->testata;
			//$prova = (array) $this->xml->$testata;
			$dati = array();
			
			foreach ($this->xml->$testata as $i => $element) {
				//showArray($element);
				foreach($element as $key => $val) {
					//echo "$key: $val <br/>";
					$dati[$key] = (string)$val;
				}
			}
			
			//showArray($dati);
			$this->datiTestata = $dati;
			
			return $dati;
		}else {
			return $this->datiTestata;
		}
	}
	
	function setPressButton($button) {
		$testata = $this->testata;
		
		$this->xml->$testata->FUNCPRS = $button;
	} 
	
	function setDatiDetail() {
		global $db;
		/*$param = array(
			'V1AZIE' => 'ok',
			'V1DATA' => 'ok 2'
		);*/
		
		$x = $this->xml;
		
		$testata = $this->getDatiTestata();
		$ds = $testata['I_GIWI_DSF'];
		
		$form = $testata['I_GIWI_FRM'];
		
		$param = wi400Detail::getDetailValues('GIWI400_PARAM_'.$testata['I_GIWI_FIL'].'_'.$testata['I_GIWI_FRM']);
		
		//$fields = $this->manager->getDisplay()->getForm($form)->getFields();
		// LZ Passo solo i campi di INPUT/OUTPUT
		$fields = $this->manager->getDisplay()->getForm($form)->getFieldsByType("B");
		//echo "ALBERTO";
		//showArray($fields);
		
		//$ds = $db->columns($pgm);
		
		//setto il bottone 
		//$this->setPressButton($button);
		$datiForm = array();
		
		$time = microtime_float();
		foreach($param as $key => $val) {
			//if(isset($this->xml->$ds->$key)) {
				//$this->xml->$ds->$key = $val;
				
				if(isset($fields[$key])) {
					$val = giwi400MaskToRpg($key, $fields[$key], $val);
				}
				
				$datiForm[$key] = $val;
			//}
			
			//echo $val."___valore<br>";
			//$campo = $x->xpath('//'.$key);
			//$campo[0][0] = $val;

			//echo "$key: $val <br/>";
			//$val = (string)$val;
			//showArray($element);
			
		}
		writeDurationProgram($time, $_SESSION['GIWI400_ID'], 'Set valori XML form '.$testata['I_GIWI_FRM']);
		//fil => $file
		//fli => $libreria
		//frm => $form 
		$time = microtime_float();
		$rs = $this->manager->salvataggioFormSuDb($testata['I_GIWI_FLI'], $testata['I_GIWI_FIL'], $testata['I_GIWI_FRM'], $datiForm);
		writeDurationProgram($time, $_SESSION['GIWI400_ID'], 'Salvataggio valori sul DB form '.$testata['I_GIWI_FRM']);
		
	}
	
	function createFileXml($file) {
		$time = microtime_float();
		$text = $this->xml->asXML();
		
		
		//tolgo l'istruzione <?xml version="1.0 che trovo all'inizio
		$pos = strpos($text, "<display");
		if ($pos !== false) {
			//$text = substr($text, $pos);
			//showArray(htmlentities($text));
			
			$rs = file_put_contents($file, $text);
		}else {
			$rs = false;
		}
		
		writeDurationProgram($time, $_SESSION['GIWI400_ID'], 'CreateFileXml '.$file);
		
		return $rs;
	}
	
	function getHtml() {
		//showArray($this->xml);
		
	}
	
	function setFormatCol($idField, $field, &$col) {
		$format = 'STRING';
		
		if($field->getType() == 'S') { //numero
			$decimali = $field->getDecimal();
			if($decimali > 0) {
				$col->setFormat('DOUBLE_'.$decimali);
				$col->setAlign('RIGHT');
				//echo $idField."__decimali__".$decimali;
				//$valCol = '$row["'.$idField.'"]';
				//$col->setDefaultValue('EVAL:'.$valCol.' > 0 ? '.$valCol.'/'.(pow(10, $decimali)).' : "0"');
			}
		}
		
		if(in_array($field->getEditMask(), array("'  /  /  '", "'  /  /    '"))) {
			//echo $idField."___<br>";
			//echo $field->getEditMask()."__mask<br>";
			
			$col->setDefaultValue('EVAL:evalListaFormatingDate("'.$idField.'", "'.$field->getEditMask().'", $row["'.$idField.'"])');
			
			/*$date = DateTime::createFromFormat('dmy', $val);
			$val = $date->format('d/m/Y');*/
			//showArray($obj);
		}
		return $format;
	}
	
	function getFormatInput($idField, $val, $obj) {
		
		$onChange = '';
		$descrizione = $obj->getDescription();
		$descrizione = str_replace(":", "", $descrizione);
		
		$hidden = false;
		if ($obj->getHide()==False || $obj->getPosition()!="000-000") {
			$myField = new wi400InputText($idField);
			$myField->setLabel($descrizione ? $descrizione : $idField);
		} else {
			$hidden = True;
			$myField = new wi400InputHidden($idField);
			$myField->setDispose(false);
		}
		
		if($obj->getType() == 'S' && !$hidden) { //numero
			$size = $obj->getDigits();
			
			$mask = "0123456789";
			if($obj->getDecimal() > 0) {
				$mask = "0123456789,";
				//$myField->addValidation("double");
				$myField->setDecimals($obj->getDecimal());
				//error_log($val." ".gettype($val));
				/*if(gettype($val) == 'string') {
					$val = 0;
				}else {*/
					$val = number_format($val, $obj->getDecimal(), ',', '.');
				//}
			}else {
				if($val == '') $val = 0;
				$val = number_format($val, 0, '', '');
			}
			$myField->setMask($mask);
		}else {
			$size = $obj->getLen();
		}
		
		if($obj->getUse() == 'O') {
			//echo "sono_in_readonly__".$idField."<br>";
			$myField->setReadonly(true);
		}
		
		if (!$hidden) {
			$myField->setSize($size);
			$myField->setMaxLength($size);
		}
		if (!$hidden) {
			if ($obj->getRiferimento()!=Null) {
				$rif = $obj->getRiferimento();
				if($this->enable_get_field) {
					$myField = $this->getCustomField($rif, $myField, $obj);
				}
			}
		}
		
		if($obj->getHasAttribute()) {
			
			$attributi = $obj->getAttributes();
			//showArray($attributi);
			foreach($attributi as $id => $atr) {
				$valid = $this->validAttribute($atr);
				if(in_array($id, $this->attributeColor) && $valid) {
					//echo $idField."___<br>";
					//showArray($atr);
					$myField->setStyleClass($id.' inputtext');
				}
				if($id == 'PROTETTO' && $valid) {
					$myField->setReadonly(true);
				}
				if($id == 'OBBLIGATORIO' && $valid) {
					//echo "sono obbligatoriooo";
					$myField->addValidation('required');
				}
				//TEST validation yav
				/*if(in_array($idField, array('V1AZIE', 'V1DATA'))) {
					//$myField->setMask('01234');
					//$myField->addValidation('required');
					$myField->setAutoFocus(true);
					
					//echo "<script>rules['".$idField."'] = '".$idField."|custom|alberto(\"".$idField."\")';</script>";
				}*/
				if($id == 'NO_DISPLAY' && $valid) {
					$myField = new wi400InputHidden($idField);
					$myField->setDispose(false);
					continue;
					/*echo "<input type='hidden' name='$idField' value='$val' />";
					return false;*/
				}
				if($id == 'ALL_DESTRA_SPAZI' && $valid) {
					$onChange .= "string_pad(this, ' ', false);";
				}
				if($id == 'ALL_DESTRA_ZERI' && $valid) {
					$onChange .= "string_pad(this, '0', false);";
				}
				if($id == 'DESTRA_SINISTRA' && $valid) {
					
				}
				if($id == 'POSIZIONAMENTO' && $valid) {
					$myField->setAutoFocus(true);
				}
				if($myField->getType() == 'INPUT_TEXT') {
					if($id == 'MINUSCOLI' && $valid) {
						$myField->setCase('LOWER');
					}else {
						$myField->setCase('UPPER');
					}
				}
				if($id == 'CAMPO_ATTRIBUTO' && $valid) {
					
					$xmlValues = $this->getDsValues();
					$typeAtr = substr($atr->getType(), 1);
					
					if(isset($xmlValues[$typeAtr])) {
						$valore = $xmlValues[$typeAtr];
						
						$numero = substr($valore, 3, 2);
						
						$first_char = substr($numero, 0, 1);
						if($first_char == 'A') {
							$myField->setReadonly(true);
							$numero = '2'.substr($numero, 1, 1);
						}else if($first_char == 'B') {
							$myField->setReadonly(true);
							$numero = '3'.substr($numero, 1, 1);
						}
							 
						//echo $valore."____valore<br>";
						//showArray($atr->getValues());
						//echo $idField."__".$numero."___numerooo<br>";
						$myField->setStyleClass('i'.$numero.' inputtext');
					}
				}
				/*if($id == 'OBBLIGATORIO' && $valid) {
					
				}
				if($id == 'OBBLIGATORIO' && $valid) {
					
				}
				if($id == 'OBBLIGATORIO' && $valid) {
					
				}
				if($id == 'OBBLIGATORIO' && $valid) {
					
				}
				if($id == 'OBBLIGATORIO' && $valid) {
					
				}
				if($id == 'OBBLIGATORIO' && $valid) {
					
				}
				if($id == 'OBBLIGATORIO' && $valid) {
					
				}*/
				
			}
			
			$myField->setOnChange($onChange);
		}
		//Gestione clientAttributi
		foreach($obj->getClientAttributes() as $type => $clientAtt) {
			$parametri = $clientAtt->getParametri();
			
			if($type == 'DECODING') {
				$decodeParameters = array(
					'TYPE' => $clientAtt->getValore(),
				);
				foreach($parametri as $key => $valore) {
					if($val === 'true') $valore = true;
					if($val === 'false') $valore = true;
					
					$decodeParameters[$key] = $valore;
				}
				$myField->setDecode($decodeParameters);
			}
			if($type == 'LOOKUP') {
				$myLookUp = new wi400LookUp($clientAtt->getValore());
				$myLookUp->addField($idField);
				$myLookUp->setParameters($parametri);
				$myField->setLookUp($myLookUp);
			}
			
			if($type == 'HIDE_LABEL' && $clientAtt->getValore() == 'S') {
				$myField->setLabel("");				
			}
			if($type == 'OUTPUT_PHP') {
				$funzione = $clientAtt->getValore();
				if (is_callable($funzione)) {
					$myField = call_user_func($funzione, $idField, $myField, $val, $obj, 'OUTPUT_PHP');
				}
			}
			if($type == 'VISUALIZZAZIONE') {
				$valore = $clientAtt->getValore();
				if($valore == 'N') {
					$myField = new wi400InputHidden($idField);
					$myField->setValue($val);
					$myField->setDispose(false);
					continue;
					//echo "<input type='hidden' name='$idField' value='$val' />";
					//return false;
				}
				if($valore == 'P') $myField->setReadonly(true);
			}
		}
		
		//echo $obj->getEditCode()."___editCode__".$idField."_<br>";
		
		//if($obj->getEditCode() != '' || $obj->getType() == 'L') {
			$val = giwi400EditCode($myField, $obj, $val);
		//}
		
		$myField->setValue($val);
		// Ulteriori attributi dalla classe
		// Cx_PROGPG, Cx_USERPG, Cx_OPZION, Cx_INTEST
		$myField = $this->customFieldAttribute($myField, $idField, $val);
		
		return $myField;
	}
	
	function validAttribute($atr) {
		$condition = $atr->getCondition();
		if($condition) {
			//showArray($condition);
			//showArray($this->condition);
			$isValid = $this->manager->evaluateCondition($this->condition, $condition);
			return $isValid;
		}else {
			return true;
		}
	}
	
	function getCustomField($rif, $myField, $obj) {
		echo "giwi400 CustomField<br>";
		return $myField;
	}
	
	function finalizeList($miaLista, $formatCollegato) {
		// Verifico se è un lista
		// Se lista verifico se esistono delle opzioni
		// Attacco le opzioni sul campo opzioni della lista
		return $miaLista;
	}

	function customFieldAttribute($myField, $idField, $val) {
		
		return $myField;
	}
	
	function checkErrori() {	
		global $messageContext;
		
		$testata = $this->getDatiTestata();
		
		if($testata['I_GIWI_ERR']) {
			$errori = explode("|", $testata['I_GIWI_ERR']);
			foreach($errori as $err) {
				list($codice, $desc) = explode(':', $err);
				
				if($codice == '*GEN') {
					$messageContext->addMessage("ERROR", $desc);
				}else {
					$messageContext->addMessage("ERROR", $desc, $codice);
				}
			}
		}
	}
	
	function getCategoryKeyForm() {
		$testata = $this->getDatiTestata();
		$form = $this->manager->getDisplay()->getForm($testata['I_GIWI_FRM']);
		$category_key = $form->getCategoryKey();
		
		//echo $category_key."___categoryKey<br>";
		
		return $category_key;
	}
	
	function getFormTxt($detail) {
		return $detail;
	}
	
	function getTxtDescription($file, $libreria, $form) {
		global $db;
		
		$sql = "SELECT OT5TRD, OT5TXT, OT5ROW, OT5COL FROM ZOT5TXTL WHERE OT5KEY=? AND OT5FMT=?";
		$stmt_txt_description = $db->prepareStatement($sql);
		$rs = $db->execute($stmt_txt_description, array($file.'_'.$libreria, $form));
		while($row = $db->fetch_array($stmt_txt_description)) {
			$dati[$row['OT5TRD']] = $row;
		}
		
		return $dati;
	}
	
	function putTn5250Text(&$datiTxt, $testo, $row, $col, $rigaVuota) {
		if(!isset($datiTxt[$row])) {
			$datiTxt[$row] = $rigaVuota;
		}
		
		$len = strlen($testo);
		$datiTxt[$row] = substr($datiTxt[$row], 0, $col).$testo.substr($datiTxt[$row], $col+$len);
	}
	
	function getTn5250($form='', $isList = false) {
		global $db;
		
		$rigaVuota = "";
		for($i = 0; $i < 132; $i++) {
			$rigaVuota .= ' ';
		}
		//echo strlen($rigaVuota)."___lunghezzaVuota<br>";
		$datiTxt = array();
		
		$testata = $this->getDatiTestata();
		if(!$form) {
			$form = $testata['I_GIWI_FRM'];
		}
		$mixData = $this->getDsValues();
		
		$txt = '';
		
		if($isList) {
			$tabella = $testata['I_GIWI_DBT'];
			
			$giwisubfile = new giwiSubfile($form, $testata['I_GIWI_FIL'], $testata['I_GIWI_FLI'], $tabella, $this->manager);
			$sqlLista = $giwisubfile->getSelectSQL();
			
			$rs = $db->singleQuery($sqlLista);
			$riga = $db->fetch_array($rs);
			if(!empty($riga)) {
				$mixData = array_merge($mixData, $riga);
			}
			
			//showArray($sqlLista);
			/*echo "parti_lista<br>";
			showArray($sqlField);
			showArray($sqlFrom);
			showArray($sqlWhere);*/
			//die("alberto");
		}
		
		$fields = $this->manager->getDisplay()->getForm($form)->getFields(True);
		
		$descTxt = $this->getTxtDescription($testata['I_GIWI_FIL'],$testata['I_GIWI_FLI'], $form);
		//showArray($descTxt);
		//echo "sono_quiiiii<br>";
		//showArray($fields);
		foreach ($fields as $idField => $field) {
			
			$arr_id = explode("_", $idField);
			if(isset($arr_id[1]) && in_array($arr_id[1], array('OPZION', 'INTEST'))) {
				continue;
			}
			
			$val = '';
			if(strpos($idField, '*') === false && isset($mixData[$idField])) {
				$val = $mixData[$idField];
			}
			//$myField = $this->getFormatInput($key, $val, $field);
			$rifDesc = $field->getRiferimentoDescrizione();
			//echo $idField."__".$rifDesc;
			
			//$descrizione = $field->getDescription();
			//$descrizione = str_replace(":", "", $descrizione);
			if ($field->getHide()==False || $field->getPosition()!="000-000") {
				//$myField = new wi400InputText($idField);
				//$myField->setLabel($descrizione ? $descrizione : $idField);
				
				//showArray($field);
				$descrizione = '';
				$rowDesc = 0;
				$colDesc = 0;
				if(isset($descTxt[$rifDesc])) {
					$descrizione = $descTxt[$rifDesc]['OT5TXT'];
					$rowDesc = $descTxt[$rifDesc]['OT5ROW'];
					$colDesc = $descTxt[$rifDesc]['OT5COL'];
					
					$this->putTn5250Text($datiTxt, $descrizione, +$rowDesc, +$colDesc, $rigaVuota);
				}
				
				if($idField == 'C2_SCCLAC') {
					//showArray($field);
				}
				if($field->getType() == 'S') {
					if($field->getDecimal() > 0) {
						$val = number_format($val, $field->getDecimal(), ',', '.');
					}else {
						if($val == '') $val = 0;
						$val = number_format($val, 0, '', '');
					}
				}else if($val && in_array($field->getEditMask(), array("'  /  /  '", "'  /  /    '"))) {
					if(strpos($val, '/') === false) {
						$format = 'dmy';
						if(strlen(''.$val) == 8) $format = 'dmY';
						
						$returnFormat = 'd/m/y';
						if($field->getEditMask() == "'  /  /    '") {
							$returnFormat = 'd/m/Y';
						} 
							
						$date = DateTime::createFromFormat($format, $val);
						$val = $date->format($returnFormat);
					}
				}
				
				list($row, $col) = explode("-", $field->getPosition());
				
				//echo "__".$descrizione."_".$val."_".$row."__".$col;
				
				$this->putTn5250Text($datiTxt, $val, +$row, +$col, $rigaVuota);
			}
			
			//echo "<br>";
		}
		
		//showArray($datiTxt);
		
		$txt = implode("<br>", $datiTxt);
		$txt = str_replace(" ", "&nbsp;", $txt); 
		
		return $txt;
	}
	
	function getDetailForm($id = 'GIWI400_PARAM', $form = '') {
		global $isFromHistory, $settings;
		
		$x = $this->xml;
		$testata = $this->getDatiTestata();
		
		$detail = new wi400Detail($id, True);
		$mixData = $this->getDsValues();
		$advanced = wi400Detail::getDetailValue('CONSOLE_GIWI400_NOME_PROGRAM', 'GIWI400_AFR');
		$advanced = "1";
		//echo var_dump($advanced);
		if($this->allProtect) $detail->setReadOnly(true);
		
		if(!$form) $form = $testata['I_GIWI_FRM'];
		
		//Set numero colonne detail
		$num_colonne = $this->manager->getDisplay()->getForm($form)->getNumCol();
		//echo $num_colonne."__num_colonne<br>";
		$detail->setColsNum($num_colonne);
		
		$num_tab = $this->manager->getDisplay()->getForm($form)->getNumTab();
		//echo $num_tab."___num_tab<br>";
		$label_tab = $this->manager->getDisplay()->getForm($form)->getLabelTab();
		//showArray($label_tab);
		
		if($num_tab > 1) {
			for($i = 1; $i <= $num_tab; $i++) {
				$desc_tab = 'tab_'.$i;
				if($label_tab && isset($label_tab[$i-1])) $desc_tab = $label_tab[$i-1];   
				$detail->addTab('tab_'.$i, $desc_tab, $num_colonne);
			}
		}
		
		//$mixData = $this->getDSValues();
		$fields = $this->manager->getDisplay()->getForm($form)->getFields(True);
		foreach ($fields as $key => $field) {
			if(strpos($key, '*') === false) { //fix warning xpath non vuole l'asterisco
				//valore field
				if (isset($mixData[$key])) {
					$val = $mixData[$key];
				} else {
					$val = '';
				}
				/*$search = $x->xpath('//'.$key);
				if(isset($search[0])) {
					$val = $search[0]->__toString();
				}else {
					$val = '';
				}*/
			}else {
				$val = '';
			}
			
			$tab_id = 'tab_1';

			$attributi = $field->getClientAttributes();
			if($attributi && isset($attributi['TAB'])) {
				//echo $attributi['TAB']->getValore()."__tab_num<br>";
				$tab_value = $attributi['TAB']->getValore();
				if($tab_value > 1) $tab_id = 'tab_'.$tab_value;
			}
			
			if($key == 'F2_DTORDI') {
				//showArray($field);
			}
			$myField = $this->getFormatInput($key, $val, $field);
			// Gestione riconoscimento Avanzato Campi
			if ($advanced=="1") {
				if ($field->getLinkedField()!="" && isset($mixData[$field->getLinkedField()])) {
					$myField->setDescription($mixData[$field->getLinkedField()]);
				}
				if ($field->getIsLinked() == True) {
					$myField = new wi400InputHidden($key);
					$myField->setDispose(false);
					$myField->setValue($val);
				}
			}
			if(!is_bool($myField)) {
				if(isset($attributi['CUSTOM']) && $attributi['CUSTOM']->getValore() == 'QUESTION_MARK') {
					//showArray($settings);
					if($myField->getType() != 'HIDDEN' && !$myField->getReadonly()) {
						$customTool = new wi400CustomTool();
						$customTool->setIco("themes/".$settings['temaDefault']."/images/lookup.png");
						$customTool->setToolTip("Lookup");
						$customTool->setScript("openLookupGiwi400('".$key."')");
						$myField->addCustomTool($customTool);
					}
				}
				//if($this->showDatiField && get_class($myField)!="wi400InputHidden" && get_class($myField)!="wi400InputCheckbox" && get_class($myField)!="wi400Text") {
				if(isset($_SESSION['GIWI400_CUSTOM_TOOL_FIELD']) && get_class($myField)!="wi400InputHidden" && get_class($myField)!="wi400InputCheckbox" && get_class($myField)!="wi400Text") {
					$customTool = new wi400CustomTool('DATI_GIWI400', 'SHOW_DATI_FIELD');
					$customTool->setIco("themes/".$settings['temaDefault']."/images/grid/config.gif");
					$customTool->setToolTip("Mostra dati campo");
					$customTool->setStyle("width: 12px;");
					$customTool->addParameter('I_GIWI_FIL', $testata['I_GIWI_FIL']);
					$customTool->addParameter('I_GIWI_FLI', $testata['I_GIWI_FLI']);
					$customTool->addParameter('I_GIWI_FRM', $testata['I_GIWI_FRM']);
					$customTool->addParameter('CAMPO', $key);
					$myField->addCustomTool($customTool);
				}else {
					//echo "custommm non <br>";
				}
				if (get_class($myField)=="wi400Image") {
					$detail->addImage($myField, $num_tab > 1 ? $tab_id : null);	
				} else {
					//echo $myField->getId()."_".$myField->getType()."<br>";
					$detail->addField($myField, $num_tab > 1 ? $tab_id : null);
				}
			}
		}
		
		//echo $this->manager->getDisplay()->getForm($form)->getEnableTxt()."__enableText_".$form."<br>";
		if($this->manager->getDisplay()->getForm($form)->getEnableTxt() == 'S') {
			$detail = $this->getFormTxt($detail);
		}
		
		return $detail;
	}
	
	function getListForm($id = 'GIWI400_TABLE', $formatCollegato="", $formatControl="") {
	    global $GIWI_DATI_FIELDS;
	    $testata = $this->getDatiTestata();
		
		$tabella = $testata['I_GIWI_DBT'];
		$this->printRollUp = true;
		
		$fields = $this->manager->getDisplay()->getForm($formatCollegato)->getFields();
		$form = $this->manager->getDisplay()->getForm($formatControl);
		$sflpag = $form->getCategoryKey("SFLPAG");
		//echo "PAGINE:".$sflpag;
		
		$giwisubfile = new giwiSubfile($formatCollegato, $testata['I_GIWI_FIL'], $testata['I_GIWI_FLI'], $tabella, $this->manager);
		$sqlLista = $giwisubfile->getSelectSQL();
		$sqlField = $giwisubfile->getField();
		$sqlFrom = $giwisubfile->getFrom();
		$sqlWhere = $giwisubfile->getWhere();
		
		//
		
		//showArray($sqlLista);
		//die("alberto");
		
		$cleanSession = $giwisubfile->getCleanSession();
		//echo $cleanSession ? 'SI_CLEAN' : 'NO_CLEAN';echo "<br>";
		//if($testata['I_GIWI_TIP'] == 'I') $cleanSession = true;
		//$cleanSession = false;
		//echo $cleanSession ? 'trueee' : 'falseee'; echo "<br>";
		//$cleanSession = true;
		$miaLista = new wi400List($id, $cleanSession);
		
		//echo $_SESSION['LAST_FOCUSED_FIELD']." LAST_FOCUS<br>";
		$miaLista->setRefreshFocus(true);
		
		//$miaLista->setFrom($tabella);
		//$miaLista->setQuery($sqlLista);
		//$miaLista->setAutoFilter(false);
		
		$miaLista->setField($sqlField);
		$miaLista->setFrom($sqlFrom);
		$miaLista->setwhere($sqlWhere);
		
		$miaLista->setIncludeFile('giwi400', 'console_giwi400_commons.php');
		
		$miaLista->setAutoUpdateList(true);
		$miaLista->setCallBackFunction("formatting", "functionFormattazioneRiga");
		$miaLista->setCallBackFunction('validation', 'functionValidationValori');
		$miaLista->setCallBackFunction('validationRow', 'functionValidationValori');
		$miaLista->setCallBackFunction("updateRow", "functionUpdateValori");
		$miaLista->setCallBackFunction("inputCell", "functionFormattazioneInput");
		
		//Gestione bottoni cambio pagina
		//$miaLista->setCallBackFunction("buttonChangePage", "functionButtonChangePage");
			
		//$cols = getColumnListFromTable($tabella);
		/*$cols = array(
			new wi400Column('V3SCEL', 'Codice'),
			new wi400Column('V3PRO', 'Codice'),
			new wi400Column('V3DESP', 'Descrizione'),
		);*/
		
		$cols = array();
		
		//showArray($fields);
		
		foreach($fields as $key => $field) {
			
			$input = $this->getFormatInput($key, '', $field);
			$descrizione = $field->getDescription();
			if ($descrizione =="") {
			    $descrizione = $key;
			}
			// La descrizione potrebbe essere soggetta a funzioni
			$descrizione = substituteFolderArray($descrizione, $GIWI_DATI_FIELDS);
			$descrizione = applicaFunzioni($descrizione);

			$col = new wi400Column($key, $descrizione);
			$this->setFormatCol($key, $field, $col);
			// Nascondo le colonne Hidden
			if ($field->getHide()==True) {
				$col->setShow(False);
			}
			if(!is_bool($input)) {
				if($input->getReadonly()) {
					$input->setReadonly(false);
					$col->setReadonly(true);
				}
				//showArray($input);
				$col->setInput($input);
			}
			
			$col->setStyle("CALLBACK:");
			//$col->setReadonly('CALLBACK:');
			$col->setAutoUpdateBackGround(true);
			// Salto * e colonne Program to System
			if (substr($key, 0 ,1)=="*" || $field->getDisplayType()=="P") {
				// LZ queste colonne non devono proprio comparire
				continue;
				$col->setShow(False);
			}
			if ($field->getHide()==False || $field->getPosition()!="000-000") {
				// Nulla da fare
			} else {	
				$col->setShow(False);
			}
			
			/*if($col->getShow()) {
				echo $key."_".$descrizione."<br>";
			}*/
				
			$cols[] = $col;
		}
		
		//showArray($cols);
		
		$miaLista->setCols($cols);
			
		/*foreach(array_keys($cols) as $id_col) {
			$miaLista->addKey($id_col);
		}*/
		
		$miaLista->addKey('S_GIWI_RRN');
		if (isset($sflpag)) {
			if (is_numeric($sflpag)) {
				$miaLista->setMaxPageRows($sflpag);
			}
		}
		
		$this->finalizeList($miaLista, $formatCollegato);
			
		//$miaLista->dispose();
		return $miaLista;
		
	}
	
	function display($returnDetail = false) {
		global $db;
		
		$testata = $this->getDatiTestata();
		
		//echo $testata['I_GIWI_FRM']."__target__".$this->target."_<br>";
		//echo $this->target."___<br>";
		if(!isset($_REQUEST['CURRENT_ACTION']) || $_REQUEST['CURRENT_ACTION'] != 'DATI_GIWI400') {
		
			if(isset($_REQUEST['GIWI400_WINDOW']) && $this->target != 'WINDOW') {
				//echo "non stampo ".$testata['I_GIWI_FRM']."<br>";
				
				return false;
			}else if(!isset($_REQUEST['GIWI400_WINDOW']) && $this->target == 'WINDOW') {
				//echo "non stampo2  ".$testata['I_GIWI_FRM']."<br>";
				
				echo '<script>
							openWindow(_APP_BASE + APP_SCRIPT + "?t=CONSOLE_GIWI400&f=DEFAULT&DECORATION=lookUp&GIWI400_WINDOW=si", "giwi400", undefined, undefined, true, false);
						</script>';
					
				return false;
			}
		
		}
		
		//$_SESSION['GIWI400_PARAM']++;
		
		//$this->manager = new giwi400Manager($testata['I_GIWI_FIL'], $testata['I_GIWI_FLI']);
		//$a = 'DSWIHEAD';
		
		//echo $head['I_GIWI_PGM']."___pgm";
		
		//showArray($testata);
		$type = $this->manager->getDisplay()->getForm($testata['I_GIWI_FRM'])->getType();
		$rendering = $this->manager->getDisplay()->getForm($testata['I_GIWI_FRM'])->getRenderingEngine();
		
		$idDetail = 'GIWI400_PARAM_'.$testata['I_GIWI_FIL'].'_'.$testata['I_GIWI_FRM'];
		
		if($type != 'SFLCTL') {
			
			if($rendering == 'TN5250') {
				$txtRendering = $this->getTn5250();
				echo '<div style="font-family: monospace;">'.$txtRendering."</div>";
			}else {
				$detail = $this->getDetailForm($idDetail);
				
				echo "<input type='hidden' name='GIWI400_FORM[]' value='".$testata['I_GIWI_FRM']."'/>";
				
				$detail->dispose();
			}
			
		}else if($type == 'SFLCTL') { //subfile
			
			if($rendering == 'TN5250') {
				$txtRendering = $this->getTn5250();
				echo '<div style="font-family: monospace;">'.$txtRendering."</div>";
			}else {
				$detail = $this->getDetailForm($idDetail);
				$detail->dispose();
			}
			
			echo "<br>";
			
			$formatCollegato = $this->manager->getDisplay()->getForm($testata['I_GIWI_FRM'])->getFormatCollegato();
			
			if($formatCollegato) {
				//echo $formatCollegato."___formatCollegato<br>";

				$rendering = $this->manager->getDisplay()->getForm($formatCollegato)->getRenderingEngine();
				
				if($rendering == 'TN5250') {
					$txtRendering = $this->getTn5250($formatCollegato, true);
					echo '<div style="font-family: monospace;">'.$txtRendering."</div>";
				}else {
					//$prova = $this->manager->getDisplay()->getForm($formatCollegato);
					//showArray($prova);
					$idLista = 'GIWI400_TABLE_'.$testata['I_GIWI_FLI']."_".$testata['I_GIWI_FIL']."_".$testata['I_GIWI_FRM'];
					//echo $idLista."__idLista";
					$miaLista = $this->getListForm($idLista, $formatCollegato, $testata['I_GIWI_FRM']);
					
					$miaLista->dispose();
	
					echo "<script>jQuery(document).ajaxStop(function() { REFRESH_FOCUS = false; });</script>";
				}
			}
		}
		
		if($this->showDisplayButton) {
			$this->displayButton();
		}
		
		$this->displayInfo();
		
		return true;
		//showArray($this->manager->getDisplay()->getForm($testata['I_GIWI_FRM']));
		
		
		/*$fields = $manager->getDisplay()->getForm($testata['I_GIWI_FRM'])->getFields();
		showArray($fields['V1AZIE']->getAttributes());*/
		
		//echo $this->getHtml();
	}
	
	function getGiwiButton() {
		global $actionContext;
		
		if(!$this->giwiButton) {
			$arr_button = array();
			
			$current_form = $actionContext->getForm();
			
			$target = '';
			if($current_form == 'SYSTEM_BUTTON') {
				$target = 'wi400top.';
			}
			
			$manager = $this->manager;
			
			$testata = $this->getDatiTestata();
			
			//showArray($manager);
			$buttonManager = $manager->getDisplay()->getFunctionKey();
			//showArray($buttonManager);
			//showArray($manager->getDisplay());
			$buttonDisplay = $manager->getDisplay()->getForm($testata['I_GIWI_FRM'])->getFunctionKey();
			//showArray($buttonDisplay);
			
			$allButton = array_merge($buttonManager, $buttonDisplay);
			
			/*showArray($buttonManager);
			 showArray($buttonDisplay);
			showArray($allButton);*/
			//echo "<br/>";
			
			$buttonAjax = 1;
			
			$label = 'ENTER';
			$myButton = new wi400InputButton($label);
			$myButton->setLabel($label);
			$myButton->setButtonClass("ENTER_CLASS");
			if($current_form == 'VIS_MASCHERA') {
				$myButton->setScript("console.log('ENTER')");
			}else {
				if(!$buttonAjax) {
					$myButton->setAction($this->azione);
					$myButton->setForm('WRITE_FILE&GIWI_BUTTON='.$label.'&GIWI_ISTABLE='.$testata['I_GIWI_TAB']);
				}else {
					$myButton->setScript($target."submitPressButton('$label', '', true)");
				}
			}
			$myButton->setValidation(true);//se non è attivo buttonAjax
			$arr_button[$label] = $myButton;
			//$myButton->dispose();
			//setKeyAction("ENTER", "ENTER_CLASS");
			
			//showArray($allButton);
			foreach($allButton as $button) {
					
				$id = $button->getId();
				$label = $id;
				$attr2 = $button->getAttr2();
				$validation = $button->getType();
					
				$myButton = new wi400InputButton($id);
				$myButton->setLabel($label);
				if($current_form == 'VIS_MASCHERA') {
					$parametri = array(
							"I_GIWI_FIL=".$testata['I_GIWI_FIL'], //I_GIWI_FRM
							"I_GIWI_FLI=".$testata['I_GIWI_FLI'], //I_GIWI_FIL
							"I_GIWI_FRM=".$testata['I_GIWI_FRM'], // I_GIWI_FLI
							"CAMPO=".$label
					);
					$myButton->setAction('DATI_GIWI400');
					$myButton->setForm('SHOW_DATI_FIELD&IS_BUTTON=si&'.implode("&", $parametri));
					$myButton->setTarget('WINDOW', 600, 400);
				}else {
					if(!$buttonAjax) {
						$myButton->setAction($this->azione);
						$myButton->setForm('WRITE_FILE&GIWI_BUTTON='.$label.'&GIWI_ISTABLE='.$testata['I_GIWI_TAB']);
					}else {
						$myButton->setScript($target."submitPressButton('$label', '$attr2', ".($validation == 'F' ? 'true' : 'false').")");
					}
				}
				if($validation == 'F') $myButton->setValidation(true);//se non è attivo buttonAjax
				
				$arr_button[$id] = $myButton;
			}
			
			$this->giwiButton = $arr_button;
		}else {
			$arr_button = $this->giwiButton;
			//echo 'cache_button';
		}
		
		return $arr_button; 
	}
	
	function displayButton($altK = false) {
		echo "<br/>";
		
		$allButton = $this->getGiwiButton();
		
		if(!$altK) {
			$allButton = $this->getGiwiButton();
			foreach($allButton as $id => $button) {
				$button->dispose();
					
				if($id == 'ENTER') {
					setKeyAction("ENTER", "ENTER_CLASS");
				}
					
				echo "&nbsp;";
			}
		}else {
			echo "<table border='1' cellspacing='1'><tr>";
			//foreach($allButton as $id => $button) {
			for($i = 1; $i <= 24; $i++) {
				if($i == 13) echo "</tr><tr>";
				
				echo "<td style='width: 49px;'>";
				if(isset($allButton['F'.$i]) && $i != 10) {
					$allButton['F'.$i]->dispose();
				}
				echo "</td>";
				//if(in_array($id, array('ENTER', 'ROLLUP'))) continue;
			}
			echo "</tr></table><br>";
			
			$allButton['ENTER']->dispose();
			if(isset($allButton['ROLLUP'])) {
				echo "&nbsp";
				$allButton['ROLLUP']->dispose();
			}
			
			echo "&nbsp;";echo "&nbsp;";
			
			/*$button = new wi400InputButton('INFO_SCHERMATA');
			$button->setLabel('Prova');
			$button->setButtonClass('detail-button fa fa-info-circle');
			//$button->set
			$button->dispose();*/
			echo "<button onClick='openWindow(_APP_BASE + \"index.php?t=\"+CURRENT_ACTION.value+\"&f=INFO_MASCHERA&DECORATION=lookup\", \"infoMaschera\", 800, 600);' class='fa fa-info-circle' style='color: #0043ff; float: right;'></button>";
		}
	}
	
	function displayInfo() {
		$testata = $this->getDatiTestata();
		
		$campi = array('I_GIWI_FIL', 'I_GIWI_FLI', 'I_GIWI_FRM');
		foreach($campi as $name) {
			$myField = new wi400InputHidden($name);
			$myField->setValue($testata[$name]);
			$myField->dispose();
		}
	}
}