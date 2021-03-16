<?php
/**
 * @name wi400_5250Session
 * @desc Gestione della sessione 5250
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author
 * @version 1.00 01/04/2018
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

class wi400_5250Session {
	
	private $id_sessione;
	private $connect=False;
	private $jobname="";
	private $jobuser="";
	private $jobnumber="";
	private $user="";
	private $password="";
	private $i=0;
	private $colourDisplay = True;
	private $wtd = array(
			"11" => array("DESC"=>"Set buffer address order", "SIGLA" => "SBA"),
			"13" => array("DESC"=>"Insert cursor order", "SIGLA" => "IC"),
			"14" => array("DESC"=>"Move cursor order", "SIGLA" => "MC"),
			"02" => array("DESC"=>"Repeat to address order", "SIGLA" => "RA"),
			"03" => array("DESC"=>"Erase to address order", "SIGLA" => "EA"),
			"01" => array("DESC"=>"Start of header order", "SIGLA" => "SOH"),
			"10" => array("DESC"=>"Transparent data order", "SIGLA" => "TD"),
			"12" => array("DESC"=>"Write extended attribute order", "SIGLA" => "WEA"),
			"1D" => array("DESC"=>"Start of field order", "SIGLA" => "SF"),
			"15" => array("DESC"=>"Write to Display Structured Field Order", "SIGLA" => "WSDF")
	);
	private $colour = array (
			'20'  => 'Green',
			'21'  => 'Green/Reverse image',
			'22'  => 'White',
			'23'  => 'White/Reverse image',
			'24'  => 'Green/Underscore',
			'25'  => 'Green/Underscore/Reverse image',
			'26'  => 'White/Underscore',
			'27'  => 'Nondisplay',
			'28'  => 'Red',
			'29'  => 'Red/Reverse image',
			'2A'  => 'Red/Blink',
			'2B'  => 'Red/Reverse image/Blink',
			'2C'  => 'Red/Underscore',
			'2D'  => 'Red/Underscore/Reverse image',
			'2E'  => 'Red/Underscore/Blink',
			'2F'  => 'Nondisplay',
			'30'  => 'Turquoise/Column separators',
			'31'  => 'Turquoise/Column separators/Reverse image',
			'32'  => 'Yellow/Column separators',
			'33'  => 'Yellow/Column separators/Reverse image',
			'34'  => 'Turquoise/Underscore',
			'35'  => 'Turquoise/Underscore/Reverse image',
			'36'  => 'Yellow/Underscore',
			'37'  => 'Nondisplay',
			'38'  => 'Pink',
			'39'  => 'Pink/Reverse image',
			'3A'  => 'Blue',
			'3B'  => 'Blue/Reverse image',
			'3C'  => 'Pink/Underscore',
			'3D'  => 'Pink/Underscore/Reverse image',
			'3E'  => 'Blue/Underscore',
			'3F'  => 'Nondisplay'                      
	);
	
	public function __construct($id="", $user="", $password="") {
		// Sessione
		if ($id=="") {
			$id=uniqid("TN_", True);
		}
		$this->id_sessione=$id;
		$this->user=$user;
		$this->password=$password;
	}
	public function openTerminalConnection() {
		global $db;
	}
	public function writeTerminalData($data) {
		global $db;
	}
	public function readTerminalData() {
		global $db;
		return "";
	}
	public function closeTerminalConnection() {
		global $db;
	}
	public function parseDataStream($dataStream) {
		global $routine_path;
		require_once $routine_path."/generali/conversion.php";
		// Estraggo tutte le variabili
		$len = strlen(trim($dataStream));
		$array = str_split(trim($dataStream), 2);
		$totchar = count($array);
		// Inizio a Parsare la stringa
		$i=0;
		//showArray($this->wtd);
		$chiavi = array_keys($this->wtd);
		$do=True;
		while ($do==True) {
			// Verifico se trovo un WTD
			if (isset($array[$i]) && in_array($array[$i], $chiavi)) {
				$comando = $array[$i];
				//echo "<br>Value:".$array[$i];
				//echo "<br>Decim:".hexdec($array[$i]);
				//$binary = sprintf('%16b',  hexdec($array[$i].$array[$i+1]));
				//echo "<br>Binar:".$binary;
				switch ($comando)  {
					// Campo testo o input Field / Discriminante la presenza di 1D
					case "11":
						if ($array[$i+1]!="00") {
							$field = new wi400_5250Field();
							//echo "<br>SONO QUI".$array[$i+1].$array[$i+2].$array[$i+3].$array[$i+4];
							$field->setXposition(hexdec($array[$i+1]));
							$field->setYposition(hexdec($array[$i+2])+1);
							// Verifico se è un campo di INPUT/OUTPUT
							if ($array[$i+3]=="1D") {
								$field->setIO(True);
								$field = $this->getIOField($field, $array, $i+3);
								$i=$this->i -1;
							} else {
								$field->setIO(False);
								$field->setColour($array[$i+3]);
								// Trovo la fine del testo devo trovare un 20 di ripristino o fine carattere
								$testo = "";
								for ($jj=$i+3;$jj++;$jj<=$totchar) {
									$testo .= e2a($this->hex2str($array[$jj]));
									if ($array[$jj+1]=="20" || $array[$jj+1]=="00") {
										break;
									}
								}
								$i=$jj;
								$field->setText($testo);
								// Reperisco l'atributo del campo testo
								// Reperisco il testo
							}
							//$field = $this->getTextField($array, $i);
							$fields[]=$field;
							//echo "<pre>";
							//echo var_dump($field);
						}						
						break;

					// Input di variabile	
					//case "1D":
						//$field = $this->getIOField($array, $i);
						//echo "<pre>";
						//echo var_dump($field);
						//break;
				}
				
			}
			$i++;
			if ($i>count($array)) {
				break;
			}
		}
		/*foreach ($array as $key => $value) {
			// Verifico se trovo un WTD
			//if (in_array($value, $this->wtd)) {
				// Verifico se il secondo Byte è un carattere di controllo
				echo "<br>Value:".$value;
				echo "<br>Decim:".hexdec($value);
				$binary = sprintf('%08b',  hexdec($value)); 
				echo "<br>Binar:".$binary;
			//}
		}*/
		//showArray($array);
		return $fields;
	}
	public function hex2str($hex) {
		return pack('H*', str_replace(array("\r", "\n", ' '), '', $hex));
	}
	public function getIOField($field, $array, $pos) {
			$x = $pos+1;
			//$field = new wi400_5250Field();
			$end=False;
			$findFFW=False;
			$findAttribute=False;
			$findControl=False;
			$findColour=False;
			while ($end==False ){
				// Fine ciclo di lettura del buffer
				if (!isset($array[$x]) || $array[$x]=="") {
					$this->i=$x;
					break;
				}
				// Trasformo in binario quello che trovo dopo
				$binary = sprintf('%08b',  hexdec($array[$x])).sprintf('%08b',  hexdec($array[$x+1]));
				// Attribute Field FFW
				if (substr($binary,0,2)=="01" && $findFFW==False) {
					// Setto gli attributi FFW
					$field->setFFW($binary);
					$x=$x+2;
					//echo "<br>NEXT AFTER FFW:".$array[$x];
					$findFFW=True;
					continue;
				}
				// Field Attribute
				if (substr($binary,0,3)=="001" && $findAttribute==False && $this->colourDisplay==False) {
					//echo "<br>ATTRIBUTE ".$array[$x]. " BINARY ".$binary;
					// Setto gli attributi del campo
					$field->setAttribute(substr($binary,0,8));
					$x=$x+1;
					$findAttribute=True;
					//echo "<br>NEXT AFTER ATTRIBUTE:".$array[$x];
					continue;
				}
				// Control Word
				if (substr($array[$x],0,1)=="8" && $findControl==False) {
					// Setto il control World
					die("CONTROL");
					$findControl=True;
					$x=$x+2;
					continue;
				}
				// Verifo se è un attributo di colore
				if (in_array($array[$x], array_keys($this->colour)) && $findColour==False && $this->colourDisplay==True) {
					$field->setColour($array[$x]);
					$x=$x+1;
					// Immediatamente dopo il colore c'è la lunghezza
					$lunghezza = hexdec($array[$x].$array[$x+1]);
					$field->setLength($lunghezza);
					$x=$x+2;
					$findColor=True;
					continue;
				}
				// Verifico se è la posizione
				if ($array[$x]=="11") {
					//die("<br>SETTO POSIZIONE!".$array[$x+1]. " COL ".$array[$x+2]. " HEX ".hexdec($array[$x+1]));
					//$field->setXposition(hexdec($array[$x+1]));
					//$field->setYposition(hexdec($array[$x+2]));
					// Ultima cosa da fare
					//break;
				}
				// Lunghezza nessuno degli altri casi, sono due byte
				if ($array[$x]=="00") {
					$this->i=$x;
					break;
				}
				//echo "<br>ARRIVO:".$array[$x];
				$this->i=$x;
				break;
			}
			return $field;
			
	}
	public function getTextField($array, $pos) {
		$pos = $pos+1;
		$binary = sprintf('%08b',  hexdec($array[$pos]));
	}
}
class wi400_5250Display {
	private $id_display="";
	private $record = array();
	private $field;
	public function __construct($id_display) {
		global $db;
	}
	public function display($field) {
		global $settings;
		$i=0;
		$first = true;
		$addX = 1;
		$html = '<div class="display">';
		foreach ($field as $key=>$value) {
			
			$x = $value->getXposition()*$addX;
			$y = $value->getYposition()*10;
			
			if($first) {
				$addX = 13.7;
				$first = false;
			}
			
			if ($value->getIO()==False) {
				$html.='<div class="component" style="top:'.$x.'px;left:'.$y.'px;">';
				$html.= str_replace(" ", "&nbsp;", $value->getText());
			} else {
				$html.='<div class="component" style="top:'.($x-2).'px;left:'.$y.'px;">';
				$i++;
				$myField = new wi400InputText("VAR_$i");
				$myField->setLabel("");
				$myField->setValue("");
				$myField->setCleanable(false);
				$myField->setSize(10);
				$myField->setMaxLength(10);
				$html.=$myField->getHtml();
			}
			$html.="</div>";
		}
		$html.="</div>";
		return $html;
	}
	public function waitReply() {
		
	}
}
class wi400_5250Field {
	private $id;
	private $bypass="0";
	private $duplicate="0";
	private $modified="0";
	private $fieldShift="000";
	private $autoEnter="0";
	private $fieldExit="0";
	private $monocase="1";
	private $mandatoryEnter="0";
	private $rightAdjust="000";
	private $xposition=0;
	private $yposition=0;
	private $length=0;
	private $columnSeparator="0";
	private $blinkField="0";
	private $underscore="0";
	private $highIntensity="0";
	private $reverseImage="0";
	private $colour="20";
	private $text="";
	private $IO = False;
	
	public function __construct($id="") {
		$this->id =$id;
	}
	public function setIO($io) {
		$this->IO=$io;
	}
	public function getIO() {
		return $this->IO;
	}
	public function setId($id) {
		$this->id=$id;
	}
	public function getId() {
		return $this->id;
	}
	public function setBypass($bypass) {
		$this->bypass=$bypass;
	}
	public function getBypass() {
		return $this->bypass;
	}
	public function setDuplicate($duplicate) {
		$this->duplicate=$duplicate;
	}
	public function getDuplicate() {
		return $this->duplicate;
	}
	public function setModified($modified) {
		$this->modified=$modified;
	}
	public function getModified() {
		return $this->modified;
	}
	public function setFieldShift($fieldShift) {
		$this->fieldShift=$fieldShift;
	}
	public function getFieldShift() {
		return $this->fieldShift;
	}
	public function setFieldExit($fieldExit) {
		$this->fieldExit=$fieldExit;
	}
	public function getFieldExit() {
		return $this->fieldExit;
	}
	public function setAutoEnter($autoEnter) {
		$this->autoEnter=$autoEnter;
	}
	public function getAutoEnter() {
		return $this->autoEnter;
	}
	public function setMonocase($monocase) {
		$this->monocase=$monocase;
	}
	public function getMonocase() {
		return $this->monocase;
	}
	public function setMandatoryEnter($mandatoryEnter) {
		$this->mandatoryEnter=$mandatoryEnter;
	}
	public function getMandatoryEnter() {
		return $this->mandatoryEnter;
	}
	public function setRightAdjust($rightAdjust) {
		$this->rightAdjust=$rightAdjust;
	}
	public function getRightAdjust() {
		return $this->rightAdjust;
	}
	public function setXposition($xposition) {
		$this->xposition=$xposition;
	}
	public function getXposition() {
		return $this->xposition;
	}
	public function setYposition($yposition) {
		$this->yposition=$yposition;
	}
	public function getYposition() {
		return $this->yposition;
	}
	public function setLength($length) {
		$this->length=$length;
	}
	public function getLength() {
		return $this->length;
	}
	public function setColumnSeparator($columnSeparator) {
		$this->columnSeparator=$columnSeparator;
	}
	public function getColumnSeparator() {
		return $this->columnSeparator;
	}
	public function setBlinkField($blinkField) {
		$this->blinkField=$blinkField;
	}
	public function getBlinkField() {
		return $this->blinkField;
	}
	public function setUnderscore($underscore) {
		$this->underscore=$underscore;
	}
	public function getUnderscore() {
		return $this->underscore;
	}
	public function setHighIntensity($highIntensity) {
		$this->highIntensity=$highIntensity;
	}
	public function getHighIntensity() {
		return $this->highIntensity;
	}
	public function setReverseImage($reverseImage) {
		$this->reverseImage=$reverseImage;
	}
	public function getReverseImage() {
		return $this->reverseImage;
	}
	public function setColour($colour) {
		$this->colour=$colour;
	}
	public function getColour() {
		return $this->colour;
	}
	public function setText($text) {
		$this->text=$text;
	}
	public function getText() {
		return $this->text;
	}
	public function setFFW($ffw) {
		if (substr($ffw,0,2)=="01") {
			$this->setBypass(substr($ffw,2,1));
			$this->setDuplicate(substr($ffw,3,1));
			$this->setModified(substr($ffw,4,1));
			$this->setFieldShift(substr($ffw,5,3));
			$this->setAutoEnter(substr($ffw,8,1));
			$this->setFieldExit(substr($ffw,9,1));
			$this->setMonocase(substr($ffw,10,1));
			$this->setMandatoryEnter(substr($ffw,12,1));
			$this->setRightAdjust(substr($ffw,13,3));
		}
	}
	public function setAttribute($attr) {
		if (substr($attr,0,3)=="001") {
			$this->setColumnSeparator(substr($attr,3,1));
			$this->setBlinkField(substr($attr,4,1));
			$this->setUnderscore(substr($attr,5,1));
			$this->setHighIntensity(substr($attr,6,1));
			$this->setReverseImage(substr($attr,7,1));
		}
	}
}