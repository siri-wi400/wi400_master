<?php
/**
 * DA FARE: 
 * Funzione per reperire i dati extra
 * Array di ordinamento per Sequenza di stampa a Video
 * Array TIPO B
 */
//require_once "giwi400.cls.php";

class giwi400Manager {
	private $id;
	private $display;
	private $file;
	private $libreria;
	private $key;
	private $errors = array();
	
	public function __construct($file, $libreria, $reset=False) {
		$this->file = $file;
		$this->key ="";
		$this->libreria = $libreria;
		$this->loadFormField($reset);
	}
	public function loadFormField($reset) {
		global $db, $wi400GO, $moduli_path;

		// Se non devo fare il reset controllo se è salvato
		//$reset = true;
		if ($reset == False && !isset($_REQUEST['ALBERTO'])) {
			// Controllo se esiste già prima sugli oggetti in cache e poi su file
			$id = "GIWI_DISPLAY_".$this->file."_".$this->libreria;
			/*$display = $wi400GO->getObject($id);
			if (isset($display)) {
				error_log("MANAGER IN GLOBAL OBJECT");
				$this->display = $display;
				return;
			}*/
			//echo $id."__id<br>";
			$trovata = false;
			$display = wi400Session::load(wi400Session::$_TYPE_GENERIC, $id);
			if(isset($display) && is_object($display) && get_class($display)=="giwi400Display") {
				$this->display = $display;
				$trovata = true;
			}else {
				$display = $this->loadCacheGenerica($id);
				if (isset($display) && is_object($display) && get_class($display)=="giwi400Display") {
					//showArray($display);
					$this->display = $display;
					$trovata = true;
				}
			}
			
			if($trovata) {
				$isValid = $this->checkValidCache($id);
				if($isValid) {
					return;
				}
			}
		}
		
		//echo "LOADING MANAGER<BR>";
		//error_log("LOADING MANAGER!");
		// Lettura del database per il caricamento dei campi e dei form
		$sql = "SELECT * FROM ZOT5FILL WHERE OT5FIL='".$this->file."' AND OT5LIB='".$this->libreria."'";
		$result = $db->query($sql);
		$row = $db->fetch_array($result);
		if(!$row) {
			$this->errors[] = 'Non trovata maschera2 '.$this->file.' - '.$this->libreria;
			return;
		}
		$this->key = $row['OT5KEY'];
		$this->display = new giwi400Display($row['OT5ID'],$row['OT5FIL'],$row['OT5LIB'], $row['OT5KEY']);
		// Reperisco i tasti di funzione
		$functionKey = $this->getFunctionKey();
		$this->display->setFunctionKey($functionKey);
		
		$stmt_sql_colonne = $db->singlePrepare("SELECT * FROM ZOT5RECF WHERE OT5KEY=? AND OT5FMT=?");
		
		// @todo
		// Caricamento dei FORM
		$sql = "SELECT * FROM ZOT5RECL WHERE OT5KEY='".$row['OT5KEY']."'";
		$result = $db->query($sql);
		while ($row = $db->fetch_array($result)) {
			$num_tab = 0;
			$form = new giwi400Form($row['OT5FMT'], $row['OT5IDE']);
			$form->setType($row['OT5TYP']);
			$form->setFormatCollegato($row['OT5FMC']);
			$form->setIsWindow($row['OT5WDW']);
			$form->setCategoryKey($row['OT5CAT']);
			// Reperisco i tasti di funzione
			$functionKey = $this->getFunctionKey($row['OT5FMT']);
			$form->setFunctionKey($functionKey);
			
			// Reperisco caratteristiche aggiuntive FORM
			$rs = $db->execute($stmt_sql_colonne, array($row['OT5KEY'], $row['OT5FMT']));
			$colDetail = $db->fetch_array($stmt_sql_colonne);
			if($colDetail) {
				if($colDetail['OT5COL']) {
					$num_colonne = $colDetail['OT5COL'];
					$form->setNumCol($num_colonne);
				}
				$enable_txt = $colDetail['OT5TXT'];
				$form->setEnableTxt($enable_txt);
				$form->setRenderingEngine($colDetail['OT5RDE']);
				if($colDetail['OT5TAB']) {
					$label_tab = explode("|", $colDetail['OT5TAB']);
					$form->setLabelTab($label_tab);
				}
			}
			// Carico la schiera dei legami
			if (!isset($resultlk)) {
				//$sqllk = "SELECT * FROM ZOT5FLDR WHERE OT5KEY='".$row['OT5KEY']."' AND OT5FMT='".$row['OT5FMT']."'";
				$sqllk = "SELECT * FROM ZOT5FLDR WHERE OT5KEY=? AND OT5FMT=?";
				$resultlk = $db->prepareStatement($sqllk);
			}
			$arraylk= array();
			$do = $db->execute($resultlk, array($row['OT5KEY'],$row['OT5FMT']));
			while ($rowlk = $db->fetch_array($resultlk)) {
				$arraylk[$rowlk['OT5FLD']]=$rowlk['OT5FL1'];
			}
			//echo "<br>Array Legami!";
			//showArray($arraylk);
			// Ciclo sui campi del form
			if (!isset($result1)) {
				//$sql1 = "SELECT * FROM ZOT5FLDL WHERE OT5KEY='".$row['OT5KEY']."' AND OT5FMT='".$row['OT5FMT']."' ORDER BY OT5OUB";
				$sql1 = "SELECT * FROM ZOT5FLDL WHERE OT5KEY=? AND OT5FMT=? ORDER BY OT5OUB";
				$result1 = $db->prepareStatement($sql1);
			}
			$do = $db->execute($result1, array($row['OT5KEY'],$row['OT5FMT']));
			$sortArray=array();
			while ($row1 = $db->fetch_array($result1))
			{
				$field = new giwi400Field($row1['OT5FLD']);
				$field->setLen($row1['OT5FLL']);
				$field->setDigits($row1['OT5DIG']);
				$field->setDecimal($row1['OT5DEC']);
				$field->setType($row1['OT5TYP']);
				$field->setDisplayType($row1['OT5TYD']);
				$field->setUse($row1['OT5USE']);
				$field->setEditCode($row1['OT5EDT']);
				$field->setEditMask($row1['OT5WRD']);
				$field->setSeparator($row1['OT5SEP']);
				$field->setDateTimeFormat($row1['OT5DFM']);
				$field->setScambioTastiera($row1['OT5SKY']);
				$field->setInputBuffer($row1['OT5INB']);
				$field->setOutputBuffer($row1['OT5OUB']);
				$field->setSequence($row1['OT5OUB']);
				$field->setDescription($row1['OT5TXT']);
				$field->setRiferimentoDescrizione($row1['OT5RF1']);
				$position = str_pad($row1['OT5ROW'], 3, "0", STR_PAD_LEFT)."-".str_pad($row1['OT5COL'], 3, "0", STR_PAD_LEFT);
				$field->setPosition($position);
				if ($row1['OT5ROW']<='0') {
					$field->setHide(True);
				}
				//$sortArray[$row1['OT5FLD']]=$position;
				// Metto via solo campi non P o * per ottimizzare i cicli di caricamento
				if (substr($row1['OT5FLD'],0,1)!="*" && $row1['OT5TYD']!="P") {
					$sortArray[$row1['OT5FLD']]=($row1['OT5ROW']*100)+$row1['OT5COL'];
				}
				// Riferimenti
				if ($row1['OT5LRI']!="") {
					$rif = new wi400Riferimento($row1['OT5FRI'],$row1['OT5LRI'],$row1['OT5RRI'],$row1['OT5CRI']);
					$field->setRiferimento($rif);
				}
				$where2 = array(
					"OT5KEY='".$row1['OT5KEY']."'",
					"OT5FMT='".$row1['OT5FMT']."'",
					"OT5FLD='".$row1['OT5FLD']."'"
				);
				// Verifico se c'è un campo collegato per la descrizione
				if (isset($arraylk[$row1['OT5FLD']])) {
					//echo "<br>LINKED! ".$row1['OT5FLD'];
					$field->setLinkedField($arraylk[$row1['OT5FLD']]);
				} else {
					// Verifico se sono collegato
					if (in_array($row1['OT5FLD'], $arraylk)) {
						//echo "<br>IS LINKED! ".$row1['OT5FLD'];
						$field->setIsLinked(True);
					}
				}
				// Reperisco gli attributi del campo se presenti
				if (!isset($result2)) {
					//$sql2 = "SELECT * FROM ZOT5FLDA WHERE ".implode(' and ', $where2);
					$sql2 = "SELECT * FROM ZOT5FLDA WHERE OT5KEY=? AND OT5FMT=? AND OT5FLD=?";
					$result2 = $db->prepareStatement($sql2);
				}
				$do = $db->execute($result2, array($row1['OT5KEY'],$row1['OT5FMT'],$row1['OT5FLD']));
				$row2 = $db->fetch_array($result2);
				if ($row2) {
					$field->setAttributeFromArray($row2);
					// Aggiorno la schiera del form sui campi Attributo
					if ($row2['OT5ATRF']!="") {
						$form->addFieldsAttribute($row2['OT5ATRF']);
					}
				}
				// Reperisco gli attributi CLIENT
				if (!isset($result3)) {
					//$sql3 = "SELECT * FROM ZOT5FLDF WHERE ".implode(' and ', $where2);
					$sql3 = "SELECT * FROM ZOT5FLDF WHERE OT5KEY=? AND OT5FMT=? AND OT5FLD=?";
					$result3 = $db->prepareStatement($sql3);
				}
				$do = $db->execute($result3, array($row1['OT5KEY'],$row1['OT5FMT'],$row1['OT5FLD']));
				$row3 = $db->fetch_array($result3);
				if ($row3) {
					// Se specificata Sequenza
					if ($row3['OT5SEQ']!=0) {
						$sortArray[$row1['OT5FLD']]=$row3['OT5SEQ'];
						$field->setSequence($row3['OT5SEQ']);
					}
					// Se descrizione personalizzata
					if ($row3['OT5TXT']!="") {
						$descrizione = $row3['OT5TXT'];
						// Verifico se devo lanciare una funzione
						if (substr($row3['OT5TXT'],0,3)=="#F(") {
							$descrizione = substituteFolderArray($descrizione, $row3);
							$descrizione = applicaFunzioni($descrizione);
						}
						$field->setDescription($descrizione);
					}
					if($row3['OT5TAB'] > $num_tab) $num_tab = $row3['OT5TAB']; 
					//Reperisco i parametri client
					$where2[] = "OT5STA='1'";
					if (!isset($result4)) {
						$sql4 = "SELECT * FROM ZOT5FLDFI WHERE OT5KEY=? AND OT5FMT=? AND OT5FLD=? AND OT5STA='1'";
						$result4 = $db->prepareStatement($sql4);
					}
					//$sql4 = "SELECT * FROM ZOT5FLDFI WHERE ".implode(' and ', $where2)." ORDER BY OT5SEQ";
					$do = $db->execute($result4, array($row1['OT5KEY'],$row1['OT5FMT'],$row1['OT5FLD']));
					$parametri4 = array();
					while($row4 = $db->fetch_array($result4)) {
						if(!isset($parametri4[$row4['OT5FLD1']])) $parametri4[$row4['OT5FLD1']] = array();
						$parametri4[$row4['OT5FLD1']][$row4['OT5PRM']] = $row4['OT5VAL']; 
					}
					$field->setClientAttributeFromArray($row3, $parametri4);
				}
				// Verifico se presenti i parametri per le funzioni
				if (!isset($result5)) {
				    //$sql3 = "SELECT * FROM ZOT5FLDF WHERE ".implode(' and ', $where2);
				    $sql5 = "SELECT * FROM ZOT5FLFP WHERE OT5KEY=? AND OT5FMT=? AND OT5FLD=?";
				    $result5 = $db->prepareStatement($sql5);
				}
				$do = $db->execute($result5, array($row1['OT5KEY'],$row1['OT5FMT'],$row1['OT5FLD']));
				while($row5 = $db->fetch_array($result5)) {
				    $field->addFunctionParm($row5['OT5FUN'], $row5['OT5PRM'], $row5['OT5VAL']);
				}
				$form->addField($row1['OT5FLD'], $field);
			}
			$form->setNumTab($num_tab);
			
			asort($sortArray);
			$form->setSortArray($sortArray);
			// Attacco il form
			$this->display->addForm($row['OT5FMT'], $form);
		}
		$id = "GIWI_DISPLAY_".$this->file."_".$this->libreria;
		// Aggiungo il display alla cache degli oggetti
		//$wi400GO->addObject($id, $this->display, True);
		$this->display->setDataCreazione(getDb2Timestamp());
		
		//Reperisco la data modifica del file giwi400Display
		$filename = $moduli_path.'/giwi400/classi/giwi400Display.cls.php';
		if (file_exists($filename)) {
			$lastTime = date("Y-m-d-H.i.s.000000", filemtime($filename));
			$this->display->setDataModifica($lastTime);
		}else {
			die("Errore! Data modifica del file display non reperita");
		}
		
		// Salvo l'oggetto su FILE
		wi400Session::save(wi400Session::$_TYPE_GENERIC, $id, $this->display);
		
		$this->saveCacheGenerica($id);
	}
	
	public function getErrors() {
		return $this->errors;
	}
	
	public function saveDisplay() {
		$id = "GIWI_DISPLAY_".$this->file."_".$this->libreria;
		wi400Session::save(wi400Session::$_TYPE_GENERIC, $id, $this->display);
	}
	
	public function saveCacheGenerica($id) {
		
		$path_file = wi400File::getCommonFile('giwi400', $id.'.txt');
		$file = fopen($path_file, 'w');
		$rs = fwrite($file, serialize($this->display));
		fclose($file);
		
		return $rs;
	}
	
	public function loadCacheGenerica($id) {
		$path_file = wi400File::getCommonFile('giwi400', $id.'.txt');
		$contents = '';
		if(file_exists($path_file)) {
			$contents = file_get_contents($path_file);
		}else {
			return $contents;
		}
		
		return unserialize($contents);
	}
	
	public function checkValidCache($id) {
		global $db, $messageContext, $moduli_path;
		//Lato RPG
		$dataCreazione = $this->display->getDataCreazione();
		//echo $dataCreazione."____dataCreazione<br>";
		$dataModifica = $this->display->getDataModifica();
		//echo $dataModifica."____dataModifica<br>";
		if(!$dataCreazione) error_log('Attenzione! giwi400Display dataCreazione vuota');
		if(!$dataModifica) error_log('Attenzione! giwi400Display dataModifica vuota');
		
		$sql = "SELECT OT5TIM FROM zot5fill WHERE OT5FIL='".$this->file."' and OT5LIB='".$this->libreria."'";
		//showArray($sql);
		$rs = $db->singleQuery($sql);
		$row = $db->fetch_array($rs);
		if($row) {
			if($row['OT5TIM'] > $dataCreazione) {
				return false;
			}
		}else {
			$messageContext->addMessage('ERROR', "Check valid cache rpg non eseguito. ".$this->file." - ".$this->libreria.' maschera non trovata.');
		}
		
		//Lato PHP
		$filename = $moduli_path.'/giwi400/classi/giwi400Display.cls.php';
		if (file_exists($filename)) {
			$lastTime = date("Y-m-d-H.i.s.000000", filemtime($filename));
			if($lastTime) {
				//echo $lastTime."___".$dataModifica."__<br>";
				if($lastTime > $dataModifica) {
					return false;
				}
			}else {
				$messageContext->addMessage('ERROR', "Check valid cache php! Data ultima modifica non trovato");
			}
		}else {
			$messageContext->addMessage('ERROR', "Check valid cache php! File display non trovato");
		}
		
		return true;
	}
	
	public function getFunctionKey($form="") {
		global $db;
		static $result;
		// Reperisco gli attributi del campo se presenti
		if (!isset($result)) {
			//$sql = "SELECT * FROM ZOT5KEYF WHERE OT5KEY='".$this->key."' AND OT5FMT='$form'";
			$sql = "SELECT * FROM ZOT5KEYF WHERE OT5KEY=? AND OT5FMT=?";
			$result = $db->prepareStatement($sql);
		}
		$do = $db->execute($result, array($this->key, $form));
		$a= $db->fetch_array($result);
		$keys = array();
		// Carico i dati
		if ($a['OT5F1']!="") {
			$atr = new giwi400Attribute("F1");
			$atr->setType($a['OT5F1_T']);
			$atr->setAttr2($a['OT5F1_R']);
			$atr->setCondition($a['OT5F1_I']);
			$keys[] = $atr;
		}
		if ($a['OT5F2']!="") {
			$atr = new giwi400Attribute("F2");
			$atr->setType($a['OT5F2_T']);
			$atr->setAttr2($a['OT5F2_R']);
			$atr->setCondition($a['OT5F2_I']);
			$keys[] = $atr;
		}
		if ($a['OT5F3']!="") {
			$atr = new giwi400Attribute("F3");
			$atr->setType($a['OT5F3_T']);
			$atr->setAttr2($a['OT5F3_R']);
			$atr->setCondition($a['OT5F3_I']);
			//$this->addAttribute($atr);
			$keys[] = $atr;
		}
		if ($a['OT5F4']!="") {
			$atr = new giwi400Attribute("F4");
			$atr->setType($a['OT5F4_T']);
			$atr->setAttr2($a['OT5F4_R']);
			$atr->setCondition($a['OT5F4_I']);
			//$this->addAttribute($atr);
			$keys[] = $atr;
		}
		if ($a['OT5F5']!="") {
			$atr = new giwi400Attribute("F5");
			$atr->setType($a['OT5F5_T']);
			$atr->setAttr2($a['OT5F5_R']);
			$atr->setCondition($a['OT5F5_I']);
			//$this->addAttribute($atr);
			$keys[] = $atr;
		}
		if ($a['OT5F6']!="") {
			$atr = new giwi400Attribute("F6");
			$atr->setType($a['OT5F6_T']);
			$atr->setAttr2($a['OT5F6_R']);
			$atr->setCondition($a['OT5F6_I']);
			//$this->addAttribute($atr);
			$keys[] = $atr;
		}
		if ($a['OT5F7']!="") {
			$atr = new giwi400Attribute("F7");
			$atr->setType($a['OT5F7_T']);
			$atr->setAttr2($a['OT5F7_R']);
			$atr->setCondition($a['OT5F7_I']);
			$keys[] = $atr;
		}
		if ($a['OT5F8']!="") {
			$atr = new giwi400Attribute("F8");
			$atr->setType($a['OT5F8_T']);
			$atr->setAttr2($a['OT5F8_R']);
			$atr->setCondition($a['OT5F8_I']);
			$keys[] = $atr;
		}
		if ($a['OT5F9']!="") {
			$atr = new giwi400Attribute("F9");
			$atr->setType($a['OT5F9_T']);
			$atr->setAttr2($a['OT5F9_R']);
			$atr->setCondition($a['OT5F9_I']);
			$keys[] = $atr;
		}
		if ($a['OT5F10']!="") {
			$atr = new giwi400Attribute("F10");
			$atr->setType($a['OT5F10_T']);
			$atr->setAttr2($a['OT5F10_R']);
			$atr->setCondition($a['OT5F10_I']);
			$keys[] = $atr;
		}
		if ($a['OT5F11']!="") {
			$atr = new giwi400Attribute("F11");
			$atr->setType($a['OT5F11_T']);
			$atr->setAttr2($a['OT5F11_R']);
			$atr->setCondition($a['OT5F11_I']);
			$keys[] = $atr;
		}
		if ($a['OT5F12']!="") {
			$atr = new giwi400Attribute("F12");
			$atr->setType($a['OT5F12_T']);
			$atr->setAttr2($a['OT5F12_R']);
			$atr->setCondition($a['OT5F12_I']);
			$keys[] = $atr;
		}
		if ($a['OT5F13']!="") {
			$atr = new giwi400Attribute("F13");
			$atr->setType($a['OT5F13_T']);
			$atr->setAttr2($a['OT5F13_R']);
			$atr->setCondition($a['OT5F13_I']);
			$keys[] = $atr;
		}
		if ($a['OT5F14']!="") {
			$atr = new giwi400Attribute("F14");
			$atr->setType($a['OT5F14_T']);
			$atr->setAttr2($a['OT5F14_R']);
			$atr->setCondition($a['OT5F14_I']);
			$keys[] = $atr;
		}
		if ($a['OT5F15']!="") {
			$atr = new giwi400Attribute("F15");
			$atr->setType($a['OT5F15_T']);
			$atr->setAttr2($a['OT5F15_R']);
			$atr->setCondition($a['OT5F15_I']);
			$keys[] = $atr;
		}
		if ($a['OT5F16']!="") {
			$atr = new giwi400Attribute("F16");
			$atr->setType($a['OT5F16_T']);
			$atr->setAttr2($a['OT5F16_R']);
			$atr->setCondition($a['OT5F16_I']);
			$keys[] = $atr;
		}
		if ($a['OT5F17']!="") {
			$atr = new giwi400Attribute("F17");
			$atr->setType($a['OT5F17_T']);
			$atr->setAttr2($a['OT5F17_R']);
			$atr->setCondition($a['OT5F17_I']);
			$keys[] = $atr;
		}
		if ($a['OT5F18']!="") {
			$atr = new giwi400Attribute("F18");
			$atr->setType("F18");
			$atr->setCondition($a['OT5F18_I']);
			//$this->addAttribute($atr);
			$keys[] = $atr;
		}
		if ($a['OT5F19']!="") {
			$atr = new giwi400Attribute("F19");
			$atr->setType($a['OT5F19_T']);
			$atr->setAttr2($a['OT5F19_R']);
			$atr->setCondition($a['OT5F19_I']);
			$keys[] = $atr;
		}
		if ($a['OT5F20']!="") {
			$atr = new giwi400Attribute("F20");
			$atr->setType($a['OT5F20_T']);
			$atr->setAttr2($a['OT5F20_R']);
			$atr->setCondition($a['OT5F20_I']);
			$keys[] = $atr;
		}
		if ($a['OT5F21']!="") {
			$atr = new giwi400Attribute("F21");
			$atr->setType($a['OT5F21_T']);
			$atr->setAttr2($a['OT5F21_R']);
			$atr->setCondition($a['OT5F21_I']);
			$keys[] = $atr;
		}
		if ($a['OT5F22']!="") {
			$atr = new giwi400Attribute("F22");
			$atr->setType($a['OT5F22_T']);
			$atr->setAttr2($a['OT5F22_R']);
			$atr->setCondition($a['OT5F22_I']);
			$keys[] = $atr;
		}
		if ($a['OT5F23']!="") {
			$atr = new giwi400Attribute("F23");
			$atr->setType($a['OT5F23_T']);
			$atr->setAttr2($a['OT5F23_R']);
			$atr->setCondition($a['OT5F23_I']);
			$keys[] = $atr;
		}	
		if ($a['OT5F24']!="") {
			$atr = new giwi400Attribute("F24");
			$atr->setType($a['OT5F24_T']);
			$atr->setAttr2($a['OT5F24_R']);
			$atr->setCondition($a['OT5F24_I']);
			$keys[] = $atr;
		}
		if ($a['OT5FRU']!="") {
			$atr = new giwi400Attribute("ROLLUP");
			$atr->setType($a['OT5FRU_T']);
			$atr->setAttr2($a['OT5FRU_R']);
			$atr->setCondition($a['OT5FRU_I']);
			$keys[] = $atr;
		}
		if ($a['OT5FRD']!="") {
			$atr = new giwi400Attribute("ROLLDOWN");
			$atr->setType($a['OT5FRD_T']);
			$atr->setAttr2($a['OT5FRD_R']);
			$atr->setCondition($a['OT5FRD_I']);
			$keys[] = $atr;
		}
		if ($a['OT5FPU']!="") {
			$atr = new giwi400Attribute("PAGEUP");
			$atr->setType($a['OT5FPU_T']);
			$atr->setAttr2($a['OT5FPU_R']);
			$atr->setCondition($a['OT5FPU_I']);
			$keys[] = $atr;
		}
		if ($a['OT5FPD']!="") {
			$atr = new giwi400Attribute("PAGEDOWN");
			$atr->setType($a['OT5FPD_T']);
			$atr->setAttr2($a['OT5FPD_R']);
			$atr->setCondition($a['OT5FPD_I']);
			$keys[] = $atr;
		}
		return $keys;
	}
	public function getDisplay() {
		return $this->display;
	}
	public function loadFromCache($id) {
		
	}
	public function saveToCache() {
		
	}
	/*
	 * Verifica se l'attributo e valido in base alle condizioni passare
	 * True e False devono essere passati come stringa
	 */
	public function evaluateCondition($ind, $conditions) {
		$condizione = False;
		
		$conditions = str_replace(array_keys($ind), array_values($ind), $conditions);
		$conditions = str_replace("N", "!", $conditions);
		$cond1="if (($conditions)){"." return true;"."} else { return false;}";
		//echo $cond1;
		//error_log($cond1);
		if (strpos($cond1, "I!")!==False) {
			//
		} else {
			$condizione = eval($cond1);
		}
		return $condizione;
	}
	public function validAttribute($atr, $indicatori) {
		$condition = $atr->getCondition();
		if($condition) {
			$isValid = $this->evaluateCondition($indicatori, $condition);
			return $isValid;
		}else {
			return true;
		}
	}
	public function evaluateAttribute($obj, $myField, $row, $indicatori, $typeObj="DETAIL") {
		
		$attributeColor = array(
				'BLU', 'GREEN', 'PINK', 'RED', 'TOURQUESE', 'WHITE', 'YELLOW'
		);
		$style = array();
		if($obj->getHasAttribute()) {
			
			$attributi = $obj->getAttributes();
			//showArray($attributi);
			foreach($attributi as $id => $atr) {
				$valid = $this->validAttribute($atr, $indicatori);
				if(in_array($id, $attributeColor) && $valid) {
					$myField->setStyleClass($id.' inputtext');
				}
				if($id == 'PROTETTO' && $valid) {
					$myField->setReadonly(true);
				}
				if($id == 'OBBLIGATORIO' && $valid) {
					//echo "sono obbligatoriooo";
					$myField->addValidation('required');
				}
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
					
					$typeAtr = substr($atr->getType(), 1);
					
					if(isset($row[$typeAtr])) {
						$valore = $row[$typeAtr];
						
						$numero = substr($valore, 3, 2);
						
						$first_char = substr($numero, 0, 1);
						if($first_char == 'A') {
							$myField->setReadonly(true);
							$numero = '2'.substr($numero, 1, 1);
						}else if($first_char == 'B') {
							$myField->setReadonly(true);
							$numero = '3'.substr($numero, 1, 1);
						}
						$class = 'i'.$numero;
						if ($typeObj == "LIST" && ($numero == 27 || $numero ==37)) {
							$class = "wi400_grid_hidden";
						}
						$myField->setStyleClass($class.' inputtext');
					}
				}
			}
			//$myField->setOnChange($onChange);
		}
		return $myField;
	}
	/**
	 * Trasforma la stringa degli indicatori in un array
	 * @param unknown $string
	 */
	function INDStringToArray($string) {
		$condition = str_split($string, 1);
		$indicatori = array();
		$i=0;
		foreach($condition as $key => $val) {
			$i++;
			$chiave = "IN".str_pad($i, 2, "0", STR_PAD_LEFT);
			$indicatori[$chiave] = $val == '1' ? 'true' : 'false';
		}
		return $indicatori;
	}
	public function salvataggioFormSuDb($libreria, $file, $form, $datiForm) {
		global $db, $routine_path;
		
		require_once $routine_path."/generali/conversion.php";
		// Cerco la riga originale
		$tabella = "PHPTEMP/GIWI".$_SESSION['GIWI400_ID'];
		$sql1 = "SELECT S_GIWI_REC FROM $tabella WHERE S_GIWI_RRN=0 AND S_GIWI_LIB='$libreria' AND S_GIWI_FIL='$file' AND S_GIWI_FRM='$form' AND S_GIWI_TIP='F'";;
		$rs1 = $db->query($sql1);
		$row1 = $db->fetch_array($rs1);
		$originale= "";
		$fr=False;
		if ($row1) {
			$fr=True;
			$originale= $row1['S_GIWI_REC'];
		}
		$fields = $this->getDisplay()->getForm($form)->getFieldsByType("B");
		$mystring = "";
		if (empty($fields)) {
			return true;
		}
		//error_log("WRITE FOR $form: COUNT ".count($fields));
		foreach ($fields as $key => $obj) {
			// Attacco il campo
			//if ($obj->getUse()=="B" && substr($key,0,1)!='*') { //&& $obj->getInputBuffer() >"0") {
			if (substr($key,0,1)!='*') { //&& $obj->getInputBuffer() >"0") {
				// Converto in *BLANK i dati esadecimali
				$mystring = "";
				//error_log($key);
				if (array_key_exists($key, $datiForm)) {
					if (substr($datiForm[$key],0,2)=="Hx") {
						// GO next
						//$start += $obj->getLen();
						continue;
						$datiForm[$key]="";
					}
					if ($obj->getDigits()=="0") {
						$mystring = $datiForm[$key];
						$mystring = str_pad($mystring, $obj->getLen(), " ");
					}
					if ($obj->getDigits()!="0") {
						$mystring = doubleViewToModel($datiForm[$key]);
						$mystring = string2Zoned($mystring, $obj->getDigits(), $obj->getDecimal());
					}
				} else {
					// Cosa faccio se non è presente ..
					if ($obj->getDigits()=="0") {
						$mystring .= str_pad("", $obj->getLen(), " ");
					}
					if ($obj->getDigits()!="0") {
						$mystring .= string2Zoned(0, $obj->getDigits(), $obj->getDecimal());
					}
				}
				$originale = mb_substr_replace($originale, $mystring, $obj->getInputBuffer()-1, $obj->getLen());
				//$start += $obj->getLen();
			}
		}
		if ($fr) {
			$sql = "UPDATE $tabella SET S_GIWI_MOD='X', S_GIWI_REC=? WHERE S_GIWI_RRN=0 AND S_GIWI_LIB='$libreria' AND S_GIWI_FIL='$file' AND S_GIWI_FRM='$form' AND S_GIWI_TIP='F'";
			$update_stmt = $db->singlePrepare($sql);
			$result = $db->execute($update_stmt, array($originale));
		} else {
			$sql = "INSERT INTO $tabella VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)";
			$update_ins = $db->singlePrepare($sql);
			$result = $db->execute($update_ins, array($libreria, $file, $form, 0, $originale, "X", "", '', 'F'));
		}
		return true;
	}
	public function getSelectSQL($form, $tabella, $isSubfile=False) {
		global $db;
		
		//$manager = new giwi400Manager($this->file, $this->lib);
		$fieldsSql = $this->getDisplay()->getForm($form)->getFieldsSql();
		$sql ="";
		if ($fieldsSql=="") {
			//error_log("NON TROVATO FIELDSSQL");
			//$fields = $this->getDisplay()->getForm($form)->getFields();
			$fields = $this->getDisplay()->getForm($form)->getFieldsByType("O");
			$fieldsAttribute = $this->getDisplay()->getForm($form)->getFieldsAttribute();
			if ($isSubfile==True) {
				$field = array('S_GIWI_RRN', "S_GIWI_IND");
				$tipo="S";
			} else {
				$field = array();
				$tipo="F";
			}
			foreach ($fields as $key => $obj) {
				// Attacco il campo
				if (substr($key,0,1)!='*' && $obj->getInputBuffer() >"0") {
					$start = $obj->getInputBuffer();
					if ($obj->getType()=="A" || $obj->getType()=="L" || $obj->getType()=="T") {
						$piece = "substr(S_GIWI_REC, $start , ".$obj->getLen().")";
						if (in_array($key, $fieldsAttribute)) {
							$piece = "'Hx\"'!!HEX($piece)!!'\"'";
						}
						$field[] = $piece." AS ".$key;
					}
					if ($obj->getType()=="S") {
						$decimal = "DECIMAL(ZONEDCHAR(SUBSTR(S_GIWI_REC, $start , ".$obj->getLen()."), ".$obj->getDecimal()."), ".$obj->getDigits().", ".$obj->getDecimal().")";
						
						$field[] = "$decimal AS ".$key;
					}
				}
			}
			if (count($field)==0) {
				return "*NOFIELDS";
			}
			$this->field = implode(", ", $field);
			$this->getDisplay()->getForm($form)->setFieldsSql($this->field);
		} else {
			//error_log("TROVATO $fieldsSql");
			$this->field = $fieldsSql;
		}
		//$this->field = implode(", ", $field);
		$this->where = "S_GIWI_FIL='$this->file' AND S_GIWI_FRM='$form' and S_GIWI_TIP='$tipo'";
		
		$sql .= "select ".$this->field;
		$sql .= " FROM $tabella WHERE {$this->where}";
		//$sql .= " SELECT * FROM PI";
		return $sql;
	}
	function __destruct() {
		// Bye Bye
	}
}
class giwi400Display {
	
	private $id;
	private $file;
	private $libreria;
	private $key;
	private $function_key = array();
	private $form = array();
	private $dataModifica = '';
	private $dataCreazione = '';
	
	/**
	 * Costruttore della classe
	 */
	public function __construct($id, $file, $libreria, $key) {
		$this->id = $id;
		$this->file = $file;
		$this->libreria = $libreria;
		$this->key = $key;
	}
	public function addForm($id, $form) {
		$this->form[$id] = $form;
	}
	public function setFunctionKey($function_key) {
		$this->function_key = $function_key;
	}
	public function getFunctionKey() {
		return $this->function_key;
	}
	public function getForm($id) {
		return $this->form[$id];
	}
	
	/*
	 * Valorizzata con il timestamp di modifica del sorgente
	 */
	public function getDataModifica() {
		return $this->dataModifica;
	}
	
	public function setDataModifica($data) {
		$this->dataModifica = $data;
	}
	
	/*
	 * valorizzata con il timestamp di creazione dell’oggetto
	 */
	public function getDataCreazione() {
		return $this->dataCreazione;
	}
	
	public function setDataCreazione($data) {
		$this->dataCreazione = $data;
	}
}
class giwi400Form {
	private $id;
	private $name;
	private $fields = array();
	private $fieldsB = array();
	private $fieldsO = array();
	private $function_key = array();
	private $type;
	private $sortArray;
	private $enableTxt;
	private $renderingEngine ='';
	private $categoryKey;
	private $fieldsDs;
	private $formatCollegato = '';
	private $isWindow = false;
	private $fieldsAttribute = array();
	private $numTab = 0;
	private $numCol = 2;
	private $fieldsSql;
	private $labelTab = array();

	public function __construct($id, $name) {
		$this->id = $id;
		$this->name = $name;
	}
	public function getFieldsAttribute() {
		return $this->fieldsAttribute;
	}
	public function addFieldsAttribute($fieldAttribute) {
		$this->fieldsAttribute[] = str_replace("&", "",$fieldAttribute);
	}
	/**
	 * Tipologia del form WINDOW, SUBFILE, MESSAGE
	 */
	public function setType($type) {
		$this->type = $type;
	}
	
	public function getType() {
		return $this->type;
	}
	public function setRenderingEngine($renderingEngine) {
		$this->renderingEngine = $renderingEngine;
	}
	
	public function getRenderingEngine() {
		return $this->renderingEngine;
	}
	public function setFieldsSql($fieldsSql) {
		$this->fieldsSql=$fieldsSql;
	}
	public function getFieldsSql() {
		return $this->fieldsSql;
	}
	public function setCategoryKey($categoryKey) {
		$dati = explode('|', $categoryKey);
		$category = array();
		foreach ($dati as $key => $dato) {
			$pos = strpos($dato, "(");
			if ($pos==0) {
				$category[$dato]=$dato;
			} else {
				$key = substr($dato,0,$pos);
				$end = strpos($dato, ")", $pos+1);
				$category[$key]=substr($dato,$pos+1,($end-($pos+1)));
			}
		}
		$this->categoryKey= $category;
	}
	
	public function getCategoryKey($key="") {
		if ($key=="") {
			return $this->categoryKey;
		} else {
			return $this->categoryKey[$key];
		}
	}
	public function setSortArray($sortArray) {
		$this->sortArray = $sortArray;
	}
	
	public function getSortArray() {
		return $this->sortArray;
	}
	public function setFieldsDs($fieldsDs) {
		$this->fieldsDs = $fieldsDs;
	}
	public function getFieldsDs() {
		return $this->fieldsDs;
	}
	/**
	 * Funzione di validazione automatica del form
	 * @param unknown $form
	 */
	public function validateForm() {
		foreach ($this->fields as $key => $value) {
			
		}
	}
	public function setFunctionKey($function_key) {
		$this->function_key = $function_key;
	}
	public function getFunctionKey() {
		return $this->function_key;
	}
	public function addField($id, $field) {
		$this->fields[$id]=$field;
		if ($field->getUse()=="B" && $field->getDisplayType()!="H") {
			$this->fieldsB[$id]=$field;
		}
		if ($field->getSequence()<>0) {
			$this->fieldsO[$id]=$field;
		}
	}
	public function getFieldsByType($type="B") {
		if ($type=="B") {
			return $this->fieldsB;
		}
		if ($type=="O") {
			return $this->fieldsO;
		}
			
	}
	public function getFields($sorted=False) {
		if (!$sorted) {
			return $this->fields;
		} else  {
			$fields = array();
			$sortArray = $this->getSortArray();
			foreach ($sortArray as $key => $value) {
				$fields[$key]= $this->fields[$key];
			}
			return $fields;
		}
	}
	public function getField($field) {
		return $this->fields[$field];
	}
	public function setIsWindow($val) {
		$this->isWindow = $val;
	}
	
	public function getIsWindow() {
		return $this->isWindow == 'S' ? true : false;
	}
	
	public function setFormatCollegato($val) {
		$this->formatCollegato = $val;
	}
	
	public function getFormatCollegato() {
		return $this->formatCollegato;
	}
	
	public function setNumTab($val) {
		$this->numTab = $val;
	}
	
	public function getNumTab() {
		return $this->numTab;
	}
	
	public function setNumCol($val) {
		$this->numCol = $val;
	}
	
	public function getNumCol() {
		return $this->numCol;
	}
	public function setEnableTxt($enableTxt) {
		$this->enableTxt = $enableTxt;
	}
	
	public function getEnableTxt() {
		return $this->enableTxt;
	}
	
	public function setLabelTab($labelTab) {
		$this->labelTab = $labelTab;
	}
	
	public function getLabelTab() {
		return $this->labelTab;
	}
}
class giwi400Field {
	private $id;
	private $hasAttribute;
	private $hasCondition;
	private $len;
	private $digits;
	private $inputBuffer;
	private $outputBuffer;
	private $decimal;
	private $type;
	private $displayType;
	private $use;
	private $editCode;
	private $editMask;
	private $linkedField;
	private $isLinked;
	private $scambioTastiera;
	private $position;
	private $attributes = array();
	private $clientAttributes = array();
	private $description = "";
	private $riferimentoDescrizione = '';
	private $hide = False;
	private $riferimento = Null;
	private $separator="";
	private $dateTimeFormat="";
	private $sequence=0;
	private $functionParmArray=array();
	
	public function __construct($id) {
		$this->id = $id;		
	}
	public function setHide($hide) {
		$this->hide = $hide;
	}
	public function getHide() {
		return $this->hide;
	}
	public function setEditCode($editCode) {
		$this->editCode = $editCode;
	}
	public function getEditCode() {
		return $this->editCode;
	}
	public function setLinkedField($linkedField) {
		$this->linkedField = $linkedField;
	}
	public function getLinkedField() {
		return $this->linkedField;
	}
	public function setSequence($sequence) {
		$this->sequence = $sequence;
	}
	public function getSequence() {
		return $this->sequence;
	}
	public function setIsLinked($isLinked) {
		$this->isLinked = $isLinked;
	}
	public function getIsLinked() {
		return $this->isLinked;
	}
	public function setScambioTastiera($scambioTastiera) {
		$this->scambioTastiera = $scambioTastiera;
	}
	public function getScambioTastiera() {
		return $this->scambioTastiera;
	}
	public function setEditMask($editMask) {
		$this->editMask = $editMask;
	}
	public function getEditMask() {
		return $this->editMask;
	}
	public function addAttribute($atr) {
		$this->attributes[$atr->getId()] = $atr;
	}
	public function getAttributes() {
		return $this->attributes;
	}
	public function addClientAttribute($atr) {
		$this->clientAttributes[$atr->getId()] = $atr;
	}
	public function getClientAttributes($key="") {
		if ($key=="") {
			return $this->clientAttributes;
		} else {
			if (isset($this->clientAttributes[$key])) {
				return $this->clientAttributes[$key];
			}
		}
	}
	public function setLen($len) {
		$this->len = $len;
	}
	public function getLen() {
		return $this->len;
	}
	public function setDescription($description) {
		$this->description = $description;
	}
	public function setRiferimento($riferimento) {
		$this->riferimento = $riferimento;
	}
	public function getRiferimento() {
		return $this->riferimento;
	}
	public function getDescription() {
		return $this->description;
	}
	
	public function setRiferimentoDescrizione($rif) {
		$this->riferimentoDescrizione = $rif;
	}
	
	public function getRiferimentoDescrizione() {
		return $this->riferimentoDescrizione;
	}
	
	public function setDecimal($decimal) {
		$this->decimal = $decimal;
	}
	public function getDecimal() {
		return $this->decimal;
	}
	
	public function setDigits($digits) {
		$this->digits = $digits;
	}
	public function getDigits() {
		return $this->digits;
	}
	public function setType($type) {
		$this->type = $type;
	}
	public function getType() {
		return $this->type;
	}
	public function setDisplayType($displayType) {
		$this->displayType = $displayType;
	}
	public function getDisplayType() {
		return $this->displayType;
	}
	public function setSeparator($separator) {
		$this->separator = $separator;
	}
	public function getSeparator() {
		return $this->separator;
	}
	public function setDateTimeFormat($dateTimeFormat) {
		$this->dateTimeFormat= $dateTimeFormat;
	}
	public function getDateTimeFormat() {
		return $this->dateTimeFormat;
	}
	public function setUse($type) {
		$this->use = $type;
	}
	public function getUse() {
		return $this->use;
	}
	public function setPosition($position) {
		$this->position = $position;
	}
	public function getPosition() {
		return $this->position;
	}
	public function setHasAttribute($hasAttribute) {
		$this->hasAttribute = $hasAttribute;
	}
	public function getHasAttribute() {
		return $this->hasAttribute;
	}
	public function setInputBuffer($inputBuffer) {
		$this->inputBuffer = $inputBuffer;
	}
	public function getInputBuffer() {
		return $this->inputBuffer;
	}
	public function setOutputBuffer($outputBuffer) {
		$this->outputBuffer = $outputBuffer;
	}
	public function getOutputBuffer() {
		return $this->outputBuffer;
	}
	public function getFunctionParm($key="") {
	    if ($key=="") {
	       return $this->functionParmArray;
	    } else {
	       return $this->functionParmArray[$key];
	    }
	}
	public function addFunctionParm($function, $parm, $value) {
	    $this->functionParmArray[$function][$parm]=$value;
	}
	
	public function setClientAttributeFromArray($a, $parametri) {
		$this->setHasAttribute(True);
		if ($a['OT5DEC']!="") {
			$atr = new giwi400ClientAttribute("DECODING");
			$atr->setType("DECODING");
			$atr->setValore($a['OT5DEC']);
			if(isset($parametri['DECODING'])) {
				$atr->setParametri($parametri['DECODING']);
			}
			$this->addClientAttribute($atr);
		}
		if ($a['OT5LOK']!="") {
			$atr = new giwi400ClientAttribute("LOOKUP");
			$atr->setType("LOOKUP");
			$atr->setValore($a['OT5LOK']);
			if(isset($parametri['LOOKUP'])) {
				$atr->setParametri($parametri['LOOKUP']);
			}
			$this->addClientAttribute($atr);
		}
		if ($a['OT5ABI']!="") {
			$atr = new giwi400ClientAttribute("ABILITAZIONE");
			$atr->setType("ABILITAZIONE");
			$atr->setValore($a['OT5ABI']);
			if(isset($parametri['OT5ABI'])) {
				$atr->setParametri($parametri['OT5ABI']);
			}
			$this->addClientAttribute($atr);
		}
		if ($a['OT5OTH']!="") {
			$atr = new giwi400ClientAttribute("CUSTOM");
			$atr->setType("CUSTOM");
			$atr->setValore($a['OT5OTH']);
			if(isset($parametri['CUSTOM'])) {
				$atr->setParametri($parametri['CUSTOM']);
			}
			$this->addClientAttribute($atr);
		}
		if ($a['OT5STY']!="") {
			$atr = new giwi400ClientAttribute("STYLE");
			$atr->setType("STYLE");
			$atr->setValore($a['OT5STY']);
			if(isset($parametri['STYLE'])) {
				$atr->setParametri($parametri['STYLE']);
			}
			$this->addClientAttribute($atr);
		}
		if ($a['OT5VIS']!="") {
			$atr = new giwi400ClientAttribute("VISUALIZZAZIONE");
			$atr->setType("VISUALIZZAZIONE");
			$atr->setValore($a['OT5VIS']);
			if(isset($parametri['VISUALIZZAZIONE'])) {
				$atr->setParametri($parametri['VISUALIZZAZIONE']);
			}
			$this->addClientAttribute($atr);
		}
		if ($a['OT5TAB']!='') {
			$atr = new giwi400ClientAttribute("TAB");
			$atr->setType("TAB");
			$atr->setValore($a['OT5TAB']);
			$this->addClientAttribute($atr);
		} 
		// Hide Label
		if ($a['OT5HLA']!='') {
			$atr = new giwi400ClientAttribute("HIDE_LABEL");
			$atr->setType("HIDE_LABEL");
			$atr->setValore($a['OT5HLA']);
			$this->addClientAttribute($atr);
		}
		// Funzione OUTPUT PHP
		if ($a['OT5FOP']!='') {
			$atr = new giwi400ClientAttribute("OUTPUT_PHP");
			$atr->setType("OUTPUT_PHP");
			$atr->setValore($a['OT5FOP']);
			$this->addClientAttribute($atr);
			if(isset($parametri['OT5FOP'])) {
			    $atr->setParametri($parametri['OT5FOP']);
			}
		}
		// Funzione OUTPUT PHP
		if ($a['OT5FOR']!='') {
			$atr = new giwi400ClientAttribute("OUTPUT_RPG");
			$atr->setType("OUTPUT_RPG");
			$atr->setValore($a['OT5FOR']);
			$this->addClientAttribute($atr);
			if(isset($parametri['OT5FOR'])) {
			    $atr->setParametri($parametri['OT5FOR']);
			}
		}
	}
	public function setAttributeFromArray($a) {
		$this->setHasAttribute(True);
		//$this->hasCondition($true);
		// Array degli attributi letti dal file
		// CAMPO ATTRIBUTO
		if ($a['OT5ATRF']!="") {
			$atr = new giwi400Attribute("CAMPO_ATTRIBUTO");
			$atr->setType($a['OT5ATRF']);
			$atr->setCondition($a['OT5ATRF_I']);
			$this->addAttribute($atr);
		}
		if ($a['OT5HI']!="") {
			$atr = new giwi400Attribute("ALTA_INTENSITA");
			$atr->setType("ALTA_INTENSITA");
			$atr->setCondition($a['OT5HI_I']);
			$this->addAttribute($atr);
		}
		if ($a['OT5RI']!="") {
			$atr = new giwi400Attribute("INVERSIONE");
			$atr->setType("INVERSIONE");
			$atr->setCondition($a['OT5RI_I']);
			$this->addAttribute($atr);
		}
		if ($a['OT5CS']!="") {
			$atr = new giwi400Attribute("SEPARATORI");
			$atr->setType("SEPARATORI");
			$atr->setCondition($a['OT5CS_I']);
			$this->addAttribute($atr);
		}
		if ($a['OT5BL']!="") {
			$atr = new giwi400Attribute("LAMPEGGIANTE");
			$atr->setType("LAMPEGGIANTE");
			$atr->setCondition($a['OT5BL_I']);
			$this->addAttribute($atr);
		}
		if ($a['OT5ND']!="") {
			$atr = new giwi400Attribute("NO_DISPLAY");
			$atr->setType("NO_DISPLAY");
			$atr->setCondition($a['OT5ND_I']);
			$this->addAttribute($atr);
		}
		if ($a['OT5UL']!="") {
			$atr = new giwi400Attribute("SOTTOLINEATO");
			$atr->setType("SOTTOLINEATO");
			$atr->setCondition($a['OT5UL_I']);
			$this->addAttribute($atr);
		}
		if ($a['OT5PC']!="") {
			$atr = new giwi400Attribute("POSIZIONAMENTO");
			$atr->setType("POSIZIONAMENTO");
			$atr->setCondition($a['OT5PC_I']);
			$this->addAttribute($atr);
		}
		if ($a['OT5MDT']!="") {
			$atr = new giwi400Attribute("CONTRASSEGNO");
			$atr->setType("CONTRASSEGNO");
			$atr->setCondition($a['OT5MDT_I']);
			$this->addAttribute($atr);
		}
		if ($a['OT5PR']!="") {
			$atr = new giwi400Attribute("PROTETTO");
			$atr->setType("PROTETTO");
			$atr->setCondition($a['OT5PR_I']);
			$this->addAttribute($atr);
		}
		if ($a['OT5OID']!="") {
			$atr = new giwi400Attribute("SCHEDA_MAGNETICA");
			$atr->setType("SCHEDA_MAGNETICA");
			$this->addAttribute($atr);
		}
		if ($a['OT5SP']!="") {
			$atr = new giwi400Attribute("PENNA_OTTICA");
			$atr->setType("PENNA_OTTICA");
			$this->addAttribute($atr);
		}
		if ($a['OT5BLU']!="") {
			$atr = new giwi400Attribute("BLU");
			$atr->setType("BLU");
			$atr->setCondition($a['OT5BLU_I']);
			$this->addAttribute($atr);
		}
		if ($a['OT5GRN']!="") {
			$atr = new giwi400Attribute("GREEN");
			$atr->setType("GREEN");
			$atr->setCondition($a['OT5GRN_I']);
			$this->addAttribute($atr);
		}
		if ($a['OT5PNK']!="") {
			$atr = new giwi400Attribute("PINK");
			$atr->setType("PINK");
			$atr->setCondition($a['OT5PNK_I']);
			$this->addAttribute($atr);
		}
		if ($a['OT5RED']!="") {
			$atr = new giwi400Attribute("RED");
			$atr->setType("RED");
			$atr->setCondition($a['OT5RED_I']);
			$this->addAttribute($atr);
		}
		if ($a['OT5TRQ']!="") {
			$atr = new giwi400Attribute("TOURQUESE");
			$atr->setType("TOURQUESE");
			$atr->setCondition($a['OT5TRQ_I']);
			$this->addAttribute($atr);
		}
		if ($a['OT5WHT']!="") {
			$atr = new giwi400Attribute("WHITE");
			$atr->setType("WHITE");
			$atr->setCondition($a['OT5WHT_I']);
			$this->addAttribute($atr);
		}
		if ($a['OT5YLW']!="") {
			$atr = new giwi400Attribute("YELLOW");
			$atr->setType("YELLOW");
			$atr->setCondition($a['OT5YLW_I']);
			$this->addAttribute($atr);
		}
		if ($a['OT5ME']!="") {
			$atr = new giwi400Attribute("OBBLIGATORIO");
			$atr->setType("OBBLIGATORIO");
			$this->addAttribute($atr);
		}
		if ($a['OT5ER']!="") {
			$atr = new giwi400Attribute("AVANZAMENTO");
			$atr->setType("AVANZAMENTO");
			$this->addAttribute($atr);
		}
		if ($a['OT5MF']!="") {
			$atr = new giwi400Attribute("RIEMPIMENTO");
			$atr->setType("RIEMPIMENTO");
			$this->addAttribute($atr);
		}
		if ($a['OT5FE']!="") {
			$atr = new giwi400Attribute("USCITA_CAMPO");
			$atr->setType("USCITA_CAMPO");
			$this->addAttribute($atr);
		}
		if ($a['OT5RB']!="") {
			$atr = new giwi400Attribute("ALL_DESTRA_SPAZI");
			$atr->setType("ALL_DESTRA_SPAZI");
			$this->addAttribute($atr);
		}
		if ($a['OT5RZ']!="") {
			$atr = new giwi400Attribute("ALL_DESTRA_ZERI");
			$atr->setType("ALL_DESTRA_ZERI");
			$this->addAttribute($atr);
		}
		if ($a['OT5RL']!="") {
			$atr = new giwi400Attribute("DESTRA_SINISTRA");
			$atr->setType("DESTRA_SINISTRA");
			$this->addAttribute($atr);
		}
		if ($a['OT5LC']!="") {
			$atr = new giwi400Attribute("MINUSCOLI");
			$atr->setType("MINUSCOLI");
			$this->addAttribute($atr);
		}
		if ($a['OT5KEYB']!="") {
			$atr = new giwi400Attribute("SCAMBIO_TASTIERA");
			$atr->setType("SCAMBIO_TASTIERA");
			$this->addAttribute($atr);
		}
		if ($a['OT5RANGE']!="") {
			$atr = new giwi400Attribute("RANGE");
			$atr->setType("RANGE");
			$atr->setValues($a['OT5RANGE']);
			$this->addAttribute($atr);
		}
		if ($a['OT5COMP']!="") {
			$atr = new giwi400Attribute("COMP");
			$atr->setType("COMP");
			$atr->setValues($a['OT5COMP']);
			$this->addAttribute($atr);
		}
		if ($a['OT5VALUE']!="") {
			$atr = new giwi400Attribute("VALUES");
			$atr->setType("VALUES");
			$atr->setValues($a['OT5VALUE']);
			$this->addAttribute($atr);
		}
		if ($a['OT5CHANGE']!="") {
			$atr = new giwi400Attribute("CHANGE");
			$atr->setType("CHANGE");
			$atr->setValues($a['OT5CHANGE']);
			$this->addAttribute($atr);
		}
		if ($a['OT5DUP']!="") {
			$atr = new giwi400Attribute("CHANGE");
			$atr->setType("CHANGE");
			$atr->setValues($a['OT5DUP']);
			$this->addAttribute($atr);
		}
		if ($a['OT5MSGID']!="") {
			$atr = new giwi400Attribute("MESSAGGIO");
			$atr->setType("MASSAGGIO");
			$atr->setValues($a['OT5MSGID']."-".$a['OT5MSGIDF']."-".$a['OT5MSGIDL']);
			$this->addAttribute($atr);
		}
	}
}
class giwi400Attribute {
	private $id;
	private $type;
	private $condition;
	private $values;
	private $attribute2;
	public function __construct($id) {
		$this->id = $id;
	}
	public function setAttr2($attr2) {
		$this->attribute2=$attr2;
	}
	public function getAttr2() {
		return $this->attribute2;
	}
	public function setType($type) {
		$this->type = $type;
	}
	public function getType() {
		return $this->type;
	}
	public function setCondition($condition) {
		$this->condition = $condition;
	}
	public function getCondition() {
		return $this->condition;
	}
	public function setValues($values) {
		$this->values = $values;
	}
	public function getValues() {
		return $this->values;
	}
	public function getId() {
		return $this->id;
	}	
}
class giwi400ClientAttribute {
	private $id;
	private $type;
	private $valore;
	private $parametri = array();
	
	public function __construct($id) {
		$this->id = $id;
		
		
	}
	public function setType($type) {
		$this->type = $type;
	}
	public function getType() {
		return $this->type;
	}
	public function setValore($valore) {
		$this->valore = $valore;
	}
	public function getValore() {
		return $this->valore;
	}
	public function getParametri() {
		return $this->parametri;
	}
	public function setParametri($parametri) {
		$this->parametri = $parametri;
	}

	public function getId() {
		return $this->id;
	}
}
class wi400FunctionKeys {
	private $keys = array();
	public function __construct($key, $condition) {
		$this->key = $key;
		$this->condition = $condition;
	}
}
class wi400Riferimento {
	private $file;
	private $libre;
	private $format;
	private $field;
	
	public function __construct($file, $libre, $format, $field) {
		$this->file = $file;
		$this->libre = $libre;
		$this->format = $format;
		$this->field = $field;
	}
	public function getFile() {
		return $this->file;
	}
	public function getLibre() {
		return $this->libre;
	}
	public function getFormat() {
		return $this->format;
	}
	public function getField() {
		return $this->field;
	}
	
}

/**
 * @author luca
 *
 */
class giwiSubfile {
	
	private $sql;
	
	private $field;
	private $tabella;
	private $where;
	private $manager;
	
	private $form;
	private $file;
	private $lib;
	//private $objXml;
	private $cleanSession = false;
	
	public function __construct($form="", $file="", $lib="", $tabella="", $manager="") {
		$this->tabella = $tabella;
		$this->file = $file;
		$this->lib = $lib;
		$this->form = $form;
		$this->manager = $manager;
		
		$filename = $_SESSION['GIWI400_ID'].$form."_".$file."_".$lib;
		$file_xml_subfile = '/www/giwi400/session/'.$filename.".xml";
		//showArray($file_xml_subfile);
		if(file_exists($file_xml_subfile)) {
			$xml = file_get_contents($file_xml_subfile, FILE_USE_INCLUDE_PATH);
			//echo "<label>XML FILE_CONTENT</label>";
			//showArray(htmlentities($xml));
			
			$objXml = new SimpleXMLElement($xml);
			if(isset($objXml->DSWIHEAD->I_GIWI_TIP)) {
				$i_giwi_tip = $objXml->DSWIHEAD->I_GIWI_TIP->__toString();
				$this->cleanSession = $i_giwi_tip == 'I' ? true : false;
			}
		}
		
	}
	/**
	 * Questa funzione restituisce la stringa formattata del record SUBFILE
	 */
	public function formatSubfileRecord() {
		
	}
	
	function setCleanSession($val) {
		$this->cleanSession = $val;
	}
	
	function getCleanSession() {
		return $this->cleanSession;
	}
	
	public function getSelectSQL() {
		global $db;
		
		//$manager = new giwi400Manager($this->file, $this->lib);
		$fieldsSql = $this->manager->getDisplay()->getForm($this->form)->getFieldsSql();
		$sql ="";
		if ($fieldsSql=="") {
			//error_log("NON TROVATO FIELDSSQL");
			$fields = $this->manager->getDisplay()->getForm($this->form)->getFields();
			$fieldsAttribute = $this->manager->getDisplay()->getForm($this->form)->getFieldsAttribute();
			$field = array('S_GIWI_RRN', "S_GIWI_IND");
			//while ($row1 = $db->fetch_array($result1)) {
			//$DSfields = getDSFields($this->file, $this->lib, $this->form);
			foreach ($fields as $key => $obj) {
				// Attacco il campo
				//if (substr($key,0,1)!='*' && in_array($key, $DSfields)) { //&& $obj->getInputBuffer() >"0") {
				if (substr($key,0,1)!='*' && $obj->getInputBuffer() >"0") {
					$start = $obj->getInputBuffer();
					if ($obj->getType()=="A" || $obj->getType()=="L" || $obj->getType()=="T") {
						$piece = "substr(S_GIWI_REC, $start , ".$obj->getLen().")";
						if (in_array($key, $fieldsAttribute)) {
							$piece = "'Hx\"'!!HEX($piece)!!'\"'";
						}
						$field[] = $piece." AS ".$key;
					}
					if ($obj->getType()=="S") {
						$decimal = "DECIMAL(ZONEDCHAR(SUBSTR(S_GIWI_REC, $start , ".$obj->getLen()."), ".$obj->getDecimal()."), ".$obj->getDigits().", ".$obj->getDecimal().")";
						
						$field[] = "$decimal AS ".$key;
					}
				}
			}
			$this->field = implode(", ", $field);
			$this->manager->getDisplay()->getForm($this->form)->setFieldsSql($this->field);
		} else {
			//error_log("TROVATO $fieldsSql");
			$this->field = $fieldsSql;
		}
		//$this->field = implode(", ", $field);
		$this->where = "S_GIWI_FIL='$this->file' AND S_GIWI_FRM='$this->form' and S_GIWI_TIP='S'";
		
		$sql .= "select ".$this->field;
		$sql .= " FROM $this->tabella WHERE {$this->where}";
		//$sql .= " SELECT * FROM PI";
		return $sql;
	}
	
	public function getField() {
		return $this->field;
	}
	
	public function getFrom() {
		return $this->tabella;
	}
	
	public function getWhere() {
		return $this->where;
	}
}
