<?php
require_once 'modules/giwi400/console_giwi400_commons.php';

class giwi400Cmd {
	
	private $cmdXml;
	private $current_libreria = '';
	private $current_file = '';
	private $current_form = '';
	private $indicatore_bottone = '';
	private $errors = array();
	private $array_giwi400 = array();
	
	public function __construct($xml) {
		$this->setXml($xml);
		
		//$_SESSION['GIWI400_PARAM'] = 0;
	}
	
	function setXml($string_xml) {
		if($string_xml) {
			$xml = new SimpleXMLElement($string_xml);
				
			$this->cmdXml = $xml;
		}
	}
	
	function getXml() {
		return $this->cmdXml;
	}
	
	function setIndicatoreButtone($val) {
		$this->indicatore_bottone = $val;
	}
	
	function getIndicatoreButtone() {
		return $this->indicatore_bottone;
	}
	
	function setCurrentForm($val) {
		$this->current_form = $val;
	}
	
	function getIdFile() {
		return $this->current_libreria."_".$this->current_file."_".$this->current_form;
	}
	
	function getCurrentLibreria() {
		return $this->current_libreria;
	}
	
	function getCurrentFile() {
		return $this->current_file;
	}
	
	function getCurrentForm() {
		return $this->current_form;
	}
	
	function getErrors() {
		return $this->errors;
	}
	
	function getFiles() {
		$files = array();
		
		foreach($this->cmdXml->CMDS->CMD as $cmd) {
			$files[] = $cmd->FILE->__toString();
		}
		
		return $files;
	}
	
	function checkErrori() {
		global $messageContext;
		
		if(isset($this->cmdXml->MESSAGES)) {
			foreach($this->cmdXml->MESSAGES->MESSAGE as $msg) {
				$testo = $msg->TEXT->__toString();
				$type = $msg->TYPE->__toString();
				$field = $msg->FIELD->__toString();
				
				$messageContext->addMessage($type, $testo, $field);
			}
		}
	}
	
	function setDatiDetailAndFile() {
		//global $firephp;
		
		$files = $this->getFiles();
		
		$manager = '';
		
		//$time = microtime_float();
		foreach($files as $i => $file) {
			$time = microtime_float();
			$string_xml = file_get_contents($file, FILE_USE_INCLUDE_PATH);
			writeDurationProgram($time, $_SESSION['GIWI400_ID'], 'file_get_contents '.$file);
			
			$time = microtime_float();
			writeDurationProgram($time, $_SESSION['GIWI400_ID'], 'getDetailValues GIWI400_PARAM_'.$i);
			
			$useClass = getUseClass();
			
			$giwi400 = new $useClass($string_xml, $i, $manager);
			$giwi400->setDatiDetail();
			$rs = $giwi400->createFileXml($file);
			
			if($i == 0) $manager = $giwi400->getManager();
		}
		//writeDurationProgram($time, $_SESSION['GIWI400_ID'], 'TOT Ciclo settaggio valori vari form');
		
		$time = microtime_float();
		//Set indicatori
		$indicatori = '';
		for($i=1; $i<=99; $i++) {
			
			$val = '0';
			if($i == intval($this->indicatore_bottone)) {
				$val = '1';
			}
			
			$indicatori .= $val;
		}
		//$firephp->fb('INDICATORI '.$this->indicatore_bottone.': '.$indicatori);
		
		$this->cmdXml->DSWIOUTP->O_GIWI_IND = $indicatori;
		$this->cmdXml->DSWIOUTP->O_GIWI_PRS = $_REQUEST['GIWI_BUTTON'];
		$this->cmdXml->DSWIOUTP->O_GIWI_STA = 0;
		writeDurationProgram($time, $_SESSION['GIWI400_ID'], 'Settaggio indicatori CMD');
	}
	
	function createCmdFileXml($file) {
		$time = microtime_float();
		$text = $this->cmdXml->asXML();
		
		//tolgo l'istruzione <?xml version="1.0 che trovo all'inizio
		$pos = strpos($text, "<VIDEO");
		if ($pos !== false) {
			//$text = substr($text, $pos);
			//showArray(htmlentities($text));
			//$rs1 = file_put_contents(substr($file, 0, -4).'_alberto_'.uniqid().'.xml', $text);
			$rs = file_put_contents($file, $text);
			
		}else {
			$rs = false;
		}
		
		writeDurationProgram($time, $_SESSION['GIWI400_ID'], 'Creazione file xml CMD');
		
		return $rs;
	}
	
	function checkTargetWindow() {
		$files = $this->getFiles();
		
		$current_form = '';
		$manager = '';
		
		$cont_windows = 0;
		
		foreach($files as $i => $file) {
			$string_xml = file_get_contents($file, FILE_USE_INCLUDE_PATH);
				
			if($string_xml) {
				$useClass = getUseClass();
				
				$giwi400 = new $useClass($string_xml, $i, $manager);
				$target = $giwi400->getTarget();

				if($i == 0) {
					$datiTestata = $giwi400->getDatiTestata();
					$current_form = $datiTestata['I_GIWI_FRM'];
					$this->current_libreria = $datiTestata['I_GIWI_FLI'];
					$this->current_file = $datiTestata['I_GIWI_FIL'];
					$this->current_form = $current_form;
					
					$manager = $giwi400->getManager();
				}
				
				if($giwi400->getManager()->getErrors()) continue;
				
				if($target == 'WINDOW') {
					/*if($_SESSION['GIWI400_CURRENT_FORM'] == $current_form) {
						return false;
					}else {
						return true;
					}*/
					
					$cont_windows++;
					
					//return true;
				}
			}
		}
		
		if($cont_windows == count($files)) return true;
		
		return false;
	}
	
	function getDetailTitolo($id, $datiTestata) {
	
		$detail = new wi400Detail($id);
		$detail->setColsNum(2);
	
		$myField = new wi400Text('PROGRAMMA', 'Programma', $datiTestata['I_GIWI_PGM']);
		$myField->setDescription('qualcosa');
		$detail->addField($myField);
	
		$myField = new wi400Text('ID_LAVORO', 'Id lavoro', $_SESSION['GIWI400_ID']);
		$detail->addField($myField);
	
		$myField = new wi400Text('FILE_VIDEO', 'File video', 'File video');
		$detail->addField($myField);
	
		$myField = new wi400Text('FORM', 'Form', implode("<br>", $datiTestata['NAME_FORMS']));
		$detail->addField($myField);
	
	
		return $detail;
	}
	
	function getNameFormFromFiles($files) {
		$forms = array();
		foreach ($files as $file) {
			$file = explode("/", $file);
			$file = array_pop($file);
			$name_form = substr($file, 6, -4);
			$forms[] = $name_form;
		}
		
		return $forms;
	}
	
	function display() {
		
		$files = $this->getFiles();
		
		$name_forms = $this->getNameFormFromFiles($files);
		
		$printRollUp = false;
		$manager = '';
		
		foreach($files as $i => $file) {
			$string_xml = file_get_contents($file, FILE_USE_INCLUDE_PATH);
			
			if($string_xml) {
				$useClass = getUseClass();
				//echo $useClass."___useClass2<br>";
				
				$giwi400 = new $useClass($string_xml, $i, $manager);
				if($giwi400->getManager()->getErrors()) continue;
				
				$giwi400->checkErrori();
				
				if($i == 0) {
					if(count($files) > 1) {
						$giwi400->setShowDisplayButton(false);
					}
					
					$datiTestata = $giwi400->getDatiTestata();
					$datiTestata['NAME_FORMS'] = $name_forms;
					
					if(isset($_SESSION['GIWI400_SHOW_TESTATA']) && $_SESSION['GIWI400_SHOW_TESTATA']) {
						$detailTitolo = $this->getDetailTitolo('GIWI400_TITOLO', $datiTestata);
						$detailTitolo->dispose();
						
						echo "<br>";
					}
					
					$manager = $giwi400->getManager();
				}
				
				if($giwi400->getPrintRollUp()) $printRollUp = true;
				
				$category_key = $giwi400->getCategoryKeyForm();
				if(in_array("PROTECT", $category_key)) {
					foreach($this->array_giwi400 as $pre_giwi400) {
						$pre_giwi400->setAllProtect(true);
					}
				}
				
				$this->array_giwi400[] = $giwi400;
				//$giwi400->display();
				
				//echo "<br>";
			}
		}
		
		$datiTestata = $giwi400->getDatiTestata();
		$_SESSION['GIWI400_CURRENT_FORM'] = $datiTestata['I_GIWI_FRM'];
		
		$noDisplayButton = false;
		foreach($this->array_giwi400 as $i => $giwi400) {
			if($noDisplayButton) {
				$giwi400->setShowDisplayButton(false);
			}else if($printRollUp) {
				$giwi400->setPrintRollUp(true);
			}
			//$testata = $giwi400->getDatiTestata();
			//showArray($testata['I_GIWI_FRM']);
			
			$rs = $giwi400->display();
			
			if($giwi400->getPrintTxtButton()) {
				$noDisplayButton = true;
			}
			
			if($rs) echo "<br>";
		}
		
		//echo "<script>rules = Object.values(rules);</script>";
		
		//ECHO "<BR><BR><BR><BR>FILE XML";
		//showArray($this->cmdXml);
	}
}