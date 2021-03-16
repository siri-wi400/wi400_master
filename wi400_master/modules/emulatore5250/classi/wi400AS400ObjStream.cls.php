<?php
/**
 * @name wi400AS400ObjStream
 * @desc Gestione della sessione 5250
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author
 * @version 0.01A 01/04/2018
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

class wi400AS400ObjStream {
	
	private $commands=array();
	
	public function __construct() {
		// Nulla da FARE
	}
	public function addCommand($value) {
		$this->commands[]=$value;
	}
	public function getCommands() {
		return $this->commands;
	}
	public function clearCommands() {
		$this->commands=array();
	}
	public function getCommandByType($type, $checkOnly=False) {
		$comandi=array();
		$types = array();
		if (!is_array($type)) {
			$types[]=$type;
		} else {
			$types = $type;
		}
		foreach ($types as $index => $tipcmd) {
			foreach ($this->commands as $key => $value) {
				if ($value->getType()==$tipcmd) {
					if ($checkOnly==False) {
						$comandi[$key]=$value;
					} else {
						return True;
					}
				}
			}
		}
		if (count($comandi)>0) {
			return $comandi;
		} else {
			return False;
		}
	}
}
class wi400AS400ControlCharacter {
	public $resetPending=False;
	public $resetMDT=False;
	public $resetMDTALL=False;
	public $resetNonBypass=False;
	public $nullAll=False;
	public $cursorMoveOnUnlock=True;
	public $resetBlinking=True;
	public $setBlinking=False;
	public $unlockKeyboardAndReset=True;
	public $soundAlarm=False;
	public $messageWaiting=False;
	public $data;
	public function __construct($data) {
		$this->data=$data;
		$binary = sprintf('%08b',  hexdec(substr($data,0,2)));
		$binary2 = sprintf('%08b',  hexdec(substr($data,2,2)));
		// ESAMINO IL PRIMO BYTE
		$stringa=substr($binary,0,3);
		// Nulla da fare
		if ($stringa=="000") {
			//
		}
		if ($stringa=="001") {
			$this->resetPending=True;
		}
		if ($stringa=="010") {
			$this->resetPending=True;
			$this->resetMDT=True;
		}
		if ($stringa=="011") {
			$this->resetPending=True;
			$this->resetMDTALL=True;
		}
		if ($stringa=="100") {
			$this->resetPending=True;
			$this->resetNonBypass=True;
		}
		if ($stringa=="101") {
			$this->resetPending=True;
			$this->resetMDT=True;
			$this->nullAll=True;			
		}
		if ($stringa=="101") {
			$this->resetPending=True;
			$this->resetMDT=True;
			$this->resetNonBypass=True;
		}
		if ($stringa=="111") {
			$this->resetPending=True;
			$this->resetMDTALL=True;
			$this->nullAll=True;
		}
		// ESAMINO IL SECONDO BYTE
		$array = str_split(trim($binary2), 1);
		if ($array[1]=="1") {
			$this->cursorMoveOnUnlock=False;
		}
		if ($array[2]=="1") {
			$this->resetBlinking=False;
		}
		if ($array[3]=="1") {
			$this->setBlinking=True;
		}
		if ($array[4]=="0") {
			$this->unlockKeyboardAndReset=False;
		}
		if ($array[5]=="1") {
			$this->soundAlarm=True;
		}
		if ($array[6]=="1") {
			$this->messageWaiting=False;
		}
		if ($array[7]=="1") {
			$this->messageWaiting=True;
		}
	}
}
class wi400AS400DSC {
	private $fields=array();
	private $clear=False;
	private $error=False;
	private $reset=False;
	private $messageLine=0;
	private $errorMessage="";
	private $commands=array();
	private $controlChar=Null;
	private $cursorOrder=Null;
	private $eraseOrder=Null;
	private $SOHHeader=Null;
	private $read=False;
	private $structured=array();
	private $window=False;
	private $type="";
	private $parameters=array();
	private $coord=array();
	private $coords=array();
	
	public function __construct($type) {
		$this->type=$type;
	}
	public function setClear($clear) {
		$this->clear = $clear;
	}
	public function getWindow() {
		return $this->window;
	}
	public function addParameter($key, $value) {
		$this->parameters[$key]=$value;
	}
	public function getParameter($key) {
		if (isset($parameters[$key])) {
			return $parameters[$key];
		}
		return false;
	}
	public function getClear() {
		return $this->clear;
	}
	public function setType($type) {
		$this->type = $type;
	}
	public function getType() {
		return $this->type;
	}
	public function setCursorOrder($cursorOrder) {
		$this->cursorOrder = $cursorOrder;
	}
	public function getCursorOrder() {
		return $this->cursorOrder;
	}
	public function setEraseOrder($eraseOrder) {
		$this->eraseOrder = $eraseOrder;
	}
	public function getEraseOrder() {
		return $this->eraseOrder;
	}
	
	public function setRead($read) {
		$this->read = $read;
	}
	public function getRead() {
		return $this->read;
	}
	
	public function setSOHHeader($SOHHeader) {
		$this->SOHHeader = $SOHHeader;
	}
	public function getSOHHeader() {
		return $this->SOHHeader;
	}
	public function setControlChar($controlChar) {
		$this->controlChar = $controlChar;
	}
	public function getControlChar() {
		return $this->controlChar;
	}
	public function addField($key, $value) {
		$this->fields[$key]=$value;
		$chiave = $value->getXposition()."-".$value->getYposition();
		$this->coords[$chiave]=$key;
		if ($value->getStructured()==True) {
			$this->structured[$key]=$key;
			// Metto già via che quel comando genera una finestra ...
			$structData = $value->getStructuredData();
			if(isset($structData->struct->type) && $structData->struct->type == 51) {
				$this->window=True;
			}
		}
	}
	public function unsetField($id) {
		unset($this->fields[$id]);
	}
	public function unsetCoords($id) {
		unset($this->coords[$id]);
	}
	/*
	 * @desc Controllo se una nuovo campo si sovrappone al vecchio
	 */
	public function getFieldByXY($x, $y, $size, $returnObj=False, $lenmin=0) {
		$chiave = $x."-".$y;
		$name = "";
		$y_fine = $y+$size;
		// Cicolo su tutti i le field
		foreach ($this->fields as $key => $value) {
			$xx = $value->getXposition();
			$yy = $value->getYposition();
			$len = $value->getLength();
			$tot = $yy+$len;
			// Valuto solamente se sono sulla stessa riga
			if ($len>$lenmin) {
				if ($xx==$x) {
					if (($y>=$yy && $y<=$tot) || ($y_fine>=$yy && $y_fine <= $tot)) {
						if ($returnObj==False) {
							$name= $value->getId();
						} else {
							return $value;
						}
						break;
					}
				}
			}
		}
		return $name;
	}
	public function getStrucuturedFields() {
		return $this->structured;
	}
	public function setFields($fields) {
		$this->fields = $fields;
	}
	public function getFields($field="") {
		if ($field=="") {
			return $this->fields;
		} else {
			if (isset($this->fields[$field])) {
				return $this->fields[$field];
			}
		}
		return False;
	}
	public function getBoxInfo($hex=False) {
		$boxInfo = array("TL"=>0,"TR"=>0,"LL"=>0,"LR"=>0,"ROW"=>0,"COL"=>0);
		$maxx=0;
		$minx=999;
		$maxy=0;
		$miny=999;
		$numRow=0;
		$numCol=0;
		foreach ($this->fields as $key => $value) {
			if ($value->getStructured()==False) {
				$x = $value->getXposition();
				$y = $value->getYposition();
				if ($x<$minx) $minx=$x;
				if ($x>$maxx) $maxx=$x;
				if ($y<$miny) $miny=$y;
				if ($y>$maxy) $maxy=$y;
			}
		}
		$row=($maxx-$minx)+1;
		$col=($maxy-$miny)+1;
		if ($hex==True) {
			// @todo Cambiare con funzione generica
			$maxx=sprintf('%02s',dechex($maxx));
			$maxy=strtoupper(sprintf('%02s',dechex($maxy)));
			$minx=sprintf('%02s',dechex($minx));
			$miny=sprintf('%02s',dechex($miny));
			$row=strtoupper(sprintf('%02s',dechex($row)));
			$col=sprintf('%02s',dechex($col));
			
		}
		return array("TL"=>$minx,"TR"=>$maxx,"LL"=>$miny,"LR"=>$maxy,"ROW"=>$row,"COL"=>$col);
	}
	public function setError($error) {
		$this->error = $error;
	}
	public function getError() {
		return $this->error;
	}
	public function setErrorMessage($errorMessage) {
		$this->errorMessage = $errorMessage;
	}
	public function getErrorMessage() {
		return $this->errorMessage;
	}
		
}
class wi400AS400CursorOrder {
	public $row;
	public $column;
	public $data;
	public function __construct($data) {
		$this->data=$data;
		$this->row=hexdec(substr($data,0,2));
		$this->column=hexdec(substr($data,2,2));
	}
}
class wi400AS400EraseOrder {
	public $row;
	public $column;
	public $fromRow;
	public $fromColumn;
	public $len;
	public $attribute=array();
	public $data;
	public function __construct($data, $fromRow=1, $fromColumn=1) {
		$this->data=$data;
		$this->fromRow=$fromRow;
		$this->fromColumn=$fromColumn;
		$this->row=hexdec(substr($data,0,2));
		$this->column=hexdec(substr($data,2,2));
		$this->len=hexdec(substr($data,4,2));
		$start = 6;
		for ($i=1;$i< $this->len;$i++) {
			$attr = substr($data,$start,2);
			$start = $start+2;
			$this->attribute[] = $attr;
		}
	}
	public function getLength() {
		return $this->len;
	}
}
class wi400AS400_51_CreateWindow {
	public $type="";
	public $len=0;
	public $class="";
	public $cursorRestricted=False;
	public $windowPullDown="";
	public $windowRow=0;
	public $windowColumn=0;
	public $minorStruct=array();
	function __construct($array, $start) {
		// Lunghezza
		$this->len= hexdec($array[$start].$array[$start+1]);
		// Classe
		$this->class = $array[$start+2];
		// Tipo
		$this->type = $array[$start+3];
		// Flag Byte 1
		$binary = sprintf('%08b',  hexdec($array[$start+4]));
		$this->cursorRestricted = substr($binary,0,1);
		$this->windowPullDown = substr($binary,1,1);
		$this->windowRow=hexdec($array[$start+7]);
		$this->windowColumn=hexdec($array[$start+8]);
		$inizio =9;
		// Border Format
		if ($array[$start+$inizio+1]=="01") {
			$mystruct = new wi400AS400WindowBorder($array, $start+$inizio);
			$this->minorStruct['01'] = $mystruct;
			$inizio = $inizio+$mystruct->len;
		}
		if ($array[$start+$inizio+1]=="10") {
			$mystruct = new wi400AS400WindowTitle($array, $start+$inizio);
			$this->minorStruct['10'] = $mystruct;
			$inizio = $inizio+$mystruct->len;
		}
	}	
}
class wi400AS400_54_WriteData {
	public $type="";
	public $len=0;
	public $class="";
	public $writeToEntryField=False;
	public $data="";
	function __construct($array, $start) {
		// Lunghezza
		$this->len= hexdec($array[$start].$array[$start+1]);
		// Classe
		$this->class = $array[$start+2];
		// Tipo
		$this->type = $array[$start+3];
		// Flag
		$binary = sprintf('%08b',  hexdec($array[$start+4]));
		$this->writeToEntryField = substr($binary,0,1);
		// Dati
		$quanti = $this->len - 7;
		$testo = wi400AS400Func::getStringFromArray($array, 7, $quanti);
		//die($testo);
		$this->data = wi400AS400Func::_e2a(wi400AS400Func::hex2str($testo));
	}	
}
class wi400AS400_61_ClearGridLine {
	public $type="";
	public $len=0;
	public $class="";
	public $partition;
	public $startRow=0;
	public $startColumn=0;
	public $width=0;
	public $height=0;
	function __construct($array, $start) {
		// Lunghezza
		$this->len= hexdec($array[$start].$array[$start+1]);
		// Classe
		$this->class = $array[$start+2];
		// Tipo
		$this->type = $array[$start+3];
		// Partition
		$this->partition = $array[$start+4];
		// Start Row
		$this->startRow = $array[$start+5];
		// Start Column
		$this->startColumn = $array[$start+6];
		// Width
		$this->width = $array[$start+7];
		// Height
		$this->height = $array[$start+8];
	}	
}
class wi400AS400_60_DrawEraseGrid {
	public $type="";
	public $len=0;
	public $class="";
	public $partition;
	public $preprocessClear=False;
	public $postprocessClear=False;
	public $color;
	public $lineStyle;
	public $minorStruct=array();
	function __construct($array, $start) {
		// Lunghezza
		$this->len= hexdec($array[$start].$array[$start+1]);
		// Classe
		$this->class = $array[$start+2];
		// Tipo
		$this->type = $array[$start+3];
		// Partition
		$this->partition = $array[$start+4];
		// Flag
		$binary = sprintf('%08b',  hexdec($array[$start+5].$array[$start+6]));
		$this->preprocessClear=substr($binary,0,1);
		$binary = sprintf('%08b',  hexdec($array[$start+7].$array[$start+8]));
		$this->postprocessClear=substr($binary,0,1);
		// Color
		$this->color=$array[$start+9];
		// Line Style
		$this->lineStyle=$array[$start+10];
		if ($this->len>=12) {
			// Ciclo sulle minor Struct
			$inizio = 12;
			for ($i=1;$i<100;$i++) {
				$struct = new wi400MinorStructGridLine($array, $inizio);
				$this->minorStruct[]=$struct;
				$inizio = $inizio + $struct->len;
				if ($inizio>=$this->len) {
					break;
				}
			}
		}
	}
}
class wi400AS400MinorStructGridLine {
	public $len=0;
	public $construct;
	public $type;
	public $startRow;
	public $startColumn;
	public $horizontalDimension;
	public $verticalDimension;
	public $color;
	public $lineStyle;
	public $lineRepeat;
	public $lineInterval; 
	function __construct($array, $start) {
		// Lunghezza
		$this->len= hexdec($array[$start]);
		// Costruttore
		$this->construct=$array[$start+1];
		// Flag
		$binary = sprintf('%08b',  hexdec($array[$start+2]));
		$this->type=substr($binary,0,1);
		$this->startRow = $array[$start+3];
		$this->startColumn = $array[$start+4];
		$this->horizontalDimension = $array[$start+5];
		$this->verticalDimension = $array[$start+6];
		if ($this->len>7) {
			$this->color = $array[$start+7];
		}
		if ($this->len>8) {
			$this->lineStyle = $array[$start+8];
		}
		if ($this->len>9) {
			$this->lineRepeat = $array[$start+9];
		}
		if ($this->len>10) {
			$this->lineInterval = $array[$start+10];
		}
	}
}
class wi400AS400_5F_RemoveGuiAll {
	public $type="";
	public $len=0;
	public $class="";
	public $mapCharacter;
	function __construct($array, $start) {
		// Lunghezza
		$this->len= hexdec($array[$start].$array[$start+1]);
		// Classe
		$this->class = $array[$start+2];
		// Tipo
		$this->type = $array[$start+3];
		// Flag
		$binary = sprintf('%08b',  hexdec($array[$start+4]));
		$this->mapCharacter=substr($binary,0,1);
	}
}
class wi400AS400_5B_RemoveGuiScroll {
	public $type="";
	public $len=0;
	public $class="";
	function __construct($array, $start) {
		// Lunghezza
		$this->len= hexdec($array[$start].$array[$start+1]);
		// Classe
		$this->class = $array[$start+2];
		// Tipo
		$this->type = $array[$start+3];
	}
}
class wi400AS400_58_RemoveGuiSelection {
	public $type="";
	public $len=0;
	public $class="";
	public $start=0;
	function __construct($array, $start) {
		$this->start=$start;
		// Lunghezza
		$this->len= hexdec($array[$start].$array[$start+1]);
		// Classe
		$this->class = $array[$start+2];
		// Tipo
		$this->type = $array[$start+3];
	}
}
class wi400AS400_59_RemoveGuiWindows {
	public $type="";
	public $len=0;
	public $class="";
	public $windowMenuBar=False;
	function __construct($array, $start) {
		// Lunghezza
		$this->len= hexdec($array[$start].$array[$start+1]);
		// Classe
		$this->class = $array[$start+2];
		// Tipo
		$this->type = $array[$start+3];
		// Flag 
		$binary = sprintf('%08b',  hexdec($array[$start+4]));
		$this->windowMenuBar=substr($binary,1,1);
		
	}
}
class wi400AS400_52_MoveCursorWindow {
	public $type="";
	public $len=0;
	public $class="";
	function __construct($array, $start) {
		// Lunghezza
		$this->len= hexdec($array[$start].$array[$start+1]);
		// Classe
		$this->class = $array[$start+2];
		// Tipo
		$this->type = $array[$start+3];
	}	
}
class wi400AS400_55_MouseButton {
	public $type="";
	public $len=0;
	public $class="";
	public $mouserEvent=array();
	function __construct($array, $start) {
		// Lunghezza
		$this->len= hexdec($array[$start].$array[$start+1]);
		// Classe
		$this->class = $array[$start+2];
		// Tipo
		$this->type = $array[$start+3];
		// Minor Strucuteres
		if ($this->len>7) {
			$inizio=7;
			for ($i=7;$i<=$this->len;$i++) {
				$this->mouseEvent[] = new wi400AS400MouseEvent($array[$inizio]);
				$inizio++;
			}
		}
	}
}
class wi400AS400MouseEvent {
	public $bit0;
	public $bit1;
	public $bit2;
	public $bit3;
	function __construct($event) {
		$binary = sprintf('%08b',  hexdec($event));
		$this->bit0 =substr($binary,0,1);
		$this->bit1 =substr($binary,1,1);
		$this->bit2 =substr($binary,2,1);
		$this->bit3 =substr($binary,3,1);
		
	}
}
class wi400AS400_53_DefineScrollBar {
	public $type="";
	public $len=0;
	public $class="";
	public $verticalScroll=False;
	public $cursorMove=False;
	public $fieldMDT=False;
	public $totalRows=0;
	public $sliderPos=0;
	public $rowsOrColumns;
	public $minorStruct;
	function __construct($array, $start) {
		// Lunghezza
		$this->len= hexdec($array[$start].$array[$start+1]);
		// Classe
		$this->class = $array[$start+2];
		// Tipo
		$this->type = $array[$start+3];
		// Mouse Characteristics/Auto-Enter/Field MDT
		$binary = sprintf('%08b',  hexdec($array[$start+4]));
		$this->verticalScrollBar =substr($binary,0,1);
		$this->cursorMove =substr($binary,1,1);
		$this->fieldMDT=substr($binary,7,1);
		// Total Rows
		$this->totalRows=hexdec($array[$start+6].$array[$start+7].$array[$start+8].$array[$start+9]);
		// Slider POS
		$this->sliderPos=hexdec($array[$start+10].$array[$start+11].$array[$start+12].$array[$start+13]);
		// Rows or Clumns
		$this->rowsOrColumns=hexdec($array[$start+14]);
		// Minor Strucuteres
		if ($this->len>15) {
			$struct = new wi400AS400StrucuturedSrollBarIndicator($array, $start+15);
		}
	}
}
class wi400AS400_50_DefineSelection {
	public $type="";
	public $len=0;
	public $class="";
	public $mouseCharacteristics="";
	public $autoEnter="";
	public $autoSelect=False;
	public $fieldMDT=False;
	public $scrollBar=False;
	public $blankAfterNumricSeparator=False;
	public $asteriskIfNoChoice=False;
	public $cursorOnlyInput=False;
	public $fieldAdvance=False;
	public $noCursorKeyToExit=False;
	public $enableChoiceOnKeyboardUnlock=False;
	public $typeSelection="";
	public $guiDevice="";
	public $selectionType="";
	public $mnemonicUnderscore="";
	public $mnemonicSelectionType="";
	public $noMnemonicUnderscore="";
	public $noMnemonicSelectionType="";
	public $textSize=1;
	public $rows=1;
	public $column=1;
	public $paddingChoice=0;
	public $numericSeparatorCharacter="";
	public $countrySpecificChar="";
	public $mousePullDown ="";
	public $totalRows=0;
	public $sliderPos="";
	public $displayStruct=array();
	public $minorStruct=array();
			
	function __construct($array, $start) {
		// Lunghezza
		$this->len= hexdec($array[$start].$array[$start+1]);
		// Classe
		$this->class = $array[$start+2];
		// Tipo
		$this->type = $array[$start+3];
		// Mouse Characteristics/Auto-Enter/Field MDT
		$binary = sprintf('%08b',  hexdec($array[$start+4]));
		$this->mouseCharacteristics =substr($binary,0,2);
		$this->autoEnter=substr($binary,4,2);
		$this->fieldMDT=substr($binary,7,1);
		// Varie ...
		$binary = sprintf('%08b',  hexdec($array[$start+5]));
		$this->scrollBar=substr($binary,0,1);
		$this->blankAfterNumricSeparator=substr($binary,1,1);
		$this->asteriskIfNoChoice=substr($binary,2,1);
		$this->cursorOnlyInput=substr($binary,3,1);
		$this->fieldAdvance=substr($binary,4,1);
		$this->noCursorKeyToExit=substr($binary,5,1);
		// Varie 2 ..
		$binary = sprintf('%08b',  hexdec($array[$start+6]));
		$this->enableChoiceOnKeyboardUnlock=substr($binary,0,1);
		// Selection Type
		$this->typeSelection=$array[$start+7];
		// Tipo Selezione
		$binary = sprintf('%08b',  hexdec($array[$start+8]));
		$this->guiDevice=substr($binary,0,4);
		$this->selectionType=substr($binary,5,3);
		// Tipo Selezione Menemonic Undescore
		$binary = sprintf('%08b',  hexdec($array[$start+9]));
		$this->mnemonicUnderscore=substr($binary,0,4);
		$this->mnemonicSelectionType=substr($binary,5,3);
		// Tipo Selezione Menemonic Without Underscore
		$binary = sprintf('%08b',  hexdec($array[$start+10]));
		$this->noMnemonicUnderscore=substr($binary,0,4);
		$this->noMnemonicSelectionType=substr($binary,5,3);
		// Text Size
		$this->textSize = hexdec($array[$start+13]);
		// Row
		$this->rows = $array[$start+14];
		// Column
		$this->column = $array[$start+15];
		// Padding choice
		$this->paddingChoice = $array[$start+16];
		// Numeric Separatore
		$this->numericSeparatorCharacter = $array[$start+17];
		// Country Specific Character
		$this->countrySpecificChar= $array[$start+18];
		// Mouse pull Down
		$this->mousePullDown= $array[$start+19];
		$inizio = 20;
		// Parametri facoltativi
		if ($this->scrollBar==True) {
			$this->totalRows= hexdec($array[$start+20].[$start+21]);
			$this->sliderPos = wi400AS400Func::getStringFromArray($array, $start+24,4);
			$inizio=$inizio+8;
		}
		//$this->totalRows = wi400AS400Func::getStringFromArray($array, $start+20,4);
		// Slidere Pos
		
		// Minor Struct in base al tipo di selezione
		/*	X'01'   Menu bar
		 	X'11'   Single choice selection field   OK
		 	X'12'   Multiple choice selection field
		 	X'21'   Single choice selection list 
		 	X'22'   Multiple choice selection list
		 	X'31'   Single choice selection field and a pull-down list
		 	X'32'   Multiple choice selection field and a pull-down list
		 	X'41'   Push buttons OK
		 	X'51'   Push buttons in a pull-down menu*/
		// Controllo se c'è una structured Field display
		if ($array[$start+$inizio+1]=="01") {
			$mystruct = new wi400AS400StrucuturedFiedlDisplay($array, $start+$inizio);
			$this->displayStruct['01'] = $mystruct;
			$inizio = $inizio+$mystruct->len;
		}
		if ($array[$start+$inizio+1]=="02") {
			$mystruct = new wi400AS400StrucuturedChoiceIndicator($array, $start+$inizio);
			$this->displayStruct['02'] = $mystruct;
			$inizio = $inizio+$mystruct->len;
		}
		if ($array[$start+$inizio+1]=="03") {
			$mystruct = new wi400AS400StrucuturedScrollBarIndicator($array, $start+$inizio);
			$this->displayStruct['03'] = $mystruct;
			$inizio = $inizio+$mystruct->len;
		}
		// Viene usata sempre la stessa struttua ...
		//switch ($this->typeSelection) {
		//	case '11':
				  // Reperisco la strutture
				  $partenza=$start+$inizio;
				  for ($i=1;$i<=$this->column;$i++) {
				  	 $selectionStruct = new wi400_Selection_50_11($array, $partenza);
				  	 $this->minorStruct[] = $selectionStruct;
				  	 $partenza=$partenza+$selectionStruct->len;
				  }
		//		  break;
		//	default:
		//		  die("ERRORE PERCHé NON TROVO CHE TIPO SI SELECTED E'".$this->typeSelection);	
		//		  break;
		//}
	}
		
}
class wi400_Selection_50_11 {
	public $len=0;
	public $type="";
	public $choiceState="";
	public $choiceNewRow="";
	public $mnemonicOffset="";
	public $AIDselected="";
	public $numericSelectionChar="";
	public $noCursorAccepted=false;
	public $rollDownAID=false;
	public $rollUpAID=false;
	public $rollLeftAID=false;
	public $rollRightAID=false;
	public $pushButton=false;
	public $right2leftCursor=false;
	public $flagType="";
	public $mnemonicOffsetData="";
	public $AIDData="";
	public $numericCharacter=0;
	public $choiceText="";
	public $displayStruct=Null;
	// Costruttore della classe
	public function __construct($array, $start) {
		// Lunghezza
		$this->len= hexdec($array[$start]);
		// Tipo
		$this->type = $array[$start+1];
		// Flag Byte
		$binary = sprintf('%08b',  hexdec($array[$start+2]));
		$this->choiceState= substr($binary,0,2);
		$this->choiceNewRow= substr($binary,2,1);
		$this->mnemonicOffset= substr($binary,4,1);
		$this->AIDselected= substr($binary,5,1);
		$this->numericSelectionChar= substr($binary,6,2);
		// Flag Byte 2  
		$binary = sprintf('%08b',  hexdec($array[$start+3]));
		$this->noCursorAccepted= substr($binary,0,1);
		$this->rollDownAID= substr($binary,1,1);
		$this->rollUpAID= substr($binary,2,1);
		$this->rollLeftAID= substr($binary,3,1);
		$this->rollRightAID= substr($binary,4,1);
		$this->pushButton= substr($binary,5,1);
		$this->right2leftCursor= substr($binary,7,1);
		// Flag Byte 3 -- a Cosa cavolo mi server
		$binary = sprintf('%08b',  hexdec($array[$start+4]));
		$this->flagType="";
		$dove=5;
		$inizio = $start+5;
		// Tutto quello che c'è dopo dipende dai flag precedentemente impostati
		if ($this->mnemonicOffset==True) {
			$this->mnemonicOffsetData = $array[$inizio];
			$inizio++;
			$dove++;
		}
		if ($this->AIDselected==True) {
			$this->AIDData = $array[$inizio];
			$inizio=$inizio+1;
			$dove++;
		}
		if ($this->numericSelectionChar!="00") {
			$this->numericCharacter = $array[$inizio];
			$inizio++;
			$dove++;
		}
		$quanti = $this->len - $dove;
		$testo = wi400AS400Func::getStringFromArray($array, $inizio, $quanti);
		//die($testo);
		$this->choiceText = wi400AS400Func::_e2a(wi400AS400Func::hex2str($testo));		
	}
}
class wi400AS400WindowBorder {
	public $len;
	public $type;
	public $borderPresentationChar="";
	public $monochrome;
	public $colorBorder;
	public $upperLeftChar="";
	public $topBorderChar="";
	public $upperRightChar="";
	public $leftChar="";
	public $rightBorderChar="";
	public $lowLeftChar="";
	public $bottomChar="";
	public $lowerRightChar="";
	// NON IMPLEMENTATA DEL TUTTO, USIAMO SEMPRE DEVICE VIRTUALI A COLORI
	function __construct($array, $start) {
		$this->len= hexdec($array[$start]);
		$this->type = $array[$start+1];
		// Flags
		$binary = sprintf('%08b',  hexdec($array[$start+2]));
		$this->borderPresentationChar=substr($binary,0,1);
		$this->monchrome=$array[$start+3];
		$this->colorBorder=$array[$start+4];
		$this->upperLeftChar=$array[$start+5];
		$this->topBorderChar=$array[$start+6];
		$this->upperRightChar=$array[$start+7];
		$this->leftChar=$array[$start+8];
		$this->rightBorderChar=$array[$start+9];
		$this->lowLeftChar=$array[$start+10];
		$this->bottomChar=$array[$start+11];
		$this->lowerRightChar=$array[$start+12];
	}
}
class wi400AS400WindowTitle {
	public $len;
	public $type;
	public $orientation="";
	public $titleorfooter="";
	public $monochromeAttribute="";
	public $colorAttributre="";
	public $titleText="";
	// NON IMPLEMENTATA DEL TUTTO, USIAMO SEMPRE DEVICE VIRTUALI A COLORI
	function __construct($array, $start) {
		$this->len= hexdec($array[$start]);
		$this->type = $array[$start+1];
		// Flags
		$binary = sprintf('%08b',  hexdec($array[$start+2]));
		$this->orientation=substr($binary,0,2);
		$this->titleorfooter=substr($binary,2,1);
		$this->monochromeAttirbute=$array[$start+3];
		$this->colorAttirbute=$array[$start+4];
		$quanti = $this->len - 7;
		$testo = wi400AS400Func::getStringFromArray($array, $start+6, $quanti);
		//die($testo);
		$this->titleText = wi400AS400Func::_e2a(wi400AS400Func::hex2str($testo));	
		
	}
}
class wi400AS400StrucuturedFiedlDisplay {
	public $len;
	public $type;
	// NON IMPLEMENTATA DEL TUTTO, USIAMO SEMPRE DEVICE VIRTUALI A COLORI
	function __construct($array, $start) {
		$this->len= hexdec($array[$start]);
		$this->type = $array[$start+1];
	}
}
class wi400AS400StrucuturedSrollBarIndicator {
	public $len;
	public $type;
	// NON IMPLEMENTATA DEL TUTTO, USIAMO SEMPRE DEVICE VIRTUALI A COLORI
	function __construct($array, $start) {
		$this->len= hexdec($array[$start]);
		$this->type = $array[$start+1];
	}
}
class wi400AS400StrucuturedChoiceIndicator {
	public $len;
	public $type;
	// NON IMPLEMENTATA DEL TUTTO, USIAMO SEMPRE DEVICE VIRTUALI A COLORI
	function __construct($array, $start) {
		$this->len= hexdec($array[$start]);
		$this->type = $array[$start+1];
	}
}
class wi400AS400StructeredField {
	public $type="";
	public $len=0;
	public $class="";
	public $struct=Null;
	
	function __construct($array, $start) {
		// Lunghezza primi 2 byte
		$this->len= hexdec($array[$start].$array[$start+1]);
		$this->class = $array[$start+2];
		$this->type = $array[$start+3];
		switch ($this->type) {
			case "50": // OK
				$this->struct = new wi400AS400_50_DefineSelection($array, $start);
				break;
			case "51": //OK
				$this->struct = new wi400AS400_51_CreateWindow($array, $start);
				break;
			case "52":
				$this->struct = new wi400AS400_52_MoveCursorWindow($array, $start);
				break;
			case "53": //OK
				$this->struct = new wi400AS400_53_DefineScrollBar($array, $start);
				break;
			case "54":
				$this->struct = new wi400AS400_54_WriteData($array, $start);
				break;
			case "55":
				$this->struct = new wi400AS400_55_MouseButton($array, $start);
				break;
			case "58":
				$this->struct = new wi400AS400_58_RemoveGuiSelection($array, $start);
				break;
			case "59":
				$this->struct = new wi400AS400_59_RemoveGuiWindows($array, $start);
				break;
			case "5B":
				$this->struct = new wi400AS400_5B_RemoveGuiScroll($array, $start);
				break;
			case "5F":
				$this->struct = new wi400AS400_5F_RemoveGuiAll($array, $start);
				break;
			case "60":
				$this->struct = new wi400AS400_60_DrawEraseGrid($array, $start);
				break;
			case "61":
				$this->struct = new wi400AS400_61_ClearGridLine($array, $start);
				break;
		}
	}
}
class wi400AS400SOHHeader {
	public $rightToLeft=False;
	public $automaticScreenReverse = False;
	public $cursorAllowInInput = False;
	public $firstField = 0;
	public $errorRow=0;
	public $returnAllField=False;
	public $pfKeysAllow = array();
	public $data="";
	
	public function __construct($array, $i) {
		$this->lunghezza = $array[$i];
		$this->data =$array[$i];
		if ($this->lunghezza >= 1) {
			$i++;
			$binary = sprintf('%08b',  hexdec($array[$i]));
			$this->data .=$array[$i];
			if (substr($binary,0,1)=="1") {
				$this->rightToLeft=True;
			}
			if (substr($binary,0,2)=="1") {
				$this->automaticScreenReverse=True;
			}
			if (substr($binary,0,3)=="1") {
				$this->cursorAllowInInput=True;
			}
		}
		if ($this->lunghezza>= 3) {
			$this->data .=$array[$i+1];
			$i=$i+2;
			$this->data .=$array[$i];
			$this->firstField =hexdec($array[$i]);
		}
		if ($this->lunghezza>= 4) {
			$i=$i+1;
			$this->data .=$array[$i];
			$this->errorRow =hexdec($array[$i]);
		}
		if ($this->lunghezza<=6) {
			$this->returnAllField=True;
		}
		if ($this->lunghezza== 7) {
			$this->data .=$array[$i+1].$array[$i+2].$array[$i+3];
			// Setto i PF Abilitati
			$binary = sprintf('%08b',hexdec($array[$i+1])).sprintf('%08b',hexdec($array[$i+2])).sprintf('%08b',hexdec($array[$i+3]));
			$this->pfKeysAllow = array_reverse(str_split($binary, 1));
		}
	}
}