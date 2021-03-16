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
class wi400AS400Session {
	
	private $id_sessione;
	private $connect=False;
	private $jobname="";
	private $jobuser="";
	private $jobnumber="";
	private $user="";
	private $lastError="";
	private $PO=Null; //PARSE OBJECT
	private $lastErrorMessage="";
	private $password="";
	private $i=0;
	private $AR=array();
	private $totchar;
	private $colourDisplay = True;
	private $mappaValori = array();
	private $mappaField = array();
	private $curX= 0;
	private $curY= 0;
	private $resolution ="24x80";
	
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
		global $db, $connzend;
		
		$dati = $this->invioDati("OPEN");
	}
	public function writeTerminalData($dati, $options="", $log=True) {
		global $db, $connzend;
		$this->invioDati("WRITE", $dati, $options);
		if ($log==True) {
			$this->writeLog("WRITE", $dati);
		}
		if ($risultato=="0") {
			return True;
		}
		return False;
	}
	public function readTerminalData($options="", $log=True) {
		global $db, $connzend;
		$ds = $this->riceviDati($options);
		$dati = $ds['CREADOP'].$ds['CDATA'];
		if ($log==True) {
			$this->writeLog("READ ", $dati);
		}
		return $dati;
	}
	public function invioDati($oper, $dati="", $options="") {
		global $db, $settings;
		$sendCoda="WI400VIRTS";
		$sendLibr="QGPL";
		// Struttura messaggio
		$MESSAGE = array("DSName"=>"QDATA", "DSParm"=>array(
				array("Name"=>"VTQTYP", "IO"=>I5_INOUT,"Type"=>I5_TYPE_CHAR, "Length"=>"10"),
				array("Name"=>"VTQID", "IO"=>I5_INOUT,"Type"=>I5_TYPE_CHAR, "Length"=>"2"),
				array("Name"=>"VTQHAN", "IO"=>I5_INOUT,"Type"=>I5_TYPE_CHAR, "Length"=>"16"),
				array("Name"=>"VTOID", "IO"=>I5_INOUT,"Type"=>I5_TYPE_CHAR, "Length"=>"40"),
				array("Name"=>"VTOPER", "IO"=>I5_INOUT,"Type"=>I5_TYPE_CHAR, "Length"=>"10"),
				array("Name"=>"FILLER", "IO"=>I5_INOUT,"Type"=>I5_TYPE_CHAR, "Length"=>"22"),
				array("Name"=>"VTCHAR", "IO"=>I5_INOUT,"Type"=>I5_TYPE_CHAR, "Length"=>"3900")));
		$queueSend = dtaq_prepare(trim($sendLibr).'/'.trim($sendCoda), $MESSAGE, "S",-1, False);
		$dati=array(
				"VTQTYP"=>"*CLIENT",
				"VTOID"=>$this->id_sessione,
				"VTOPER"=>$oper,
				"VTCHAR"=>$dati,
				);
		dtaq_send($queueSend,"",$dati);
	}
	public function riceviDati($options) {
		global $db, $settings;
		$CLIENT = array("DSName"=>"QDATA", "DSParm"=>array(
				array("Name"=>"CREADOP", "IO"=>I5_INOUT,"Type"=>I5_TYPE_CHAR, "Length"=>"1"),
				array("Name"=>"CERRORE", "IO"=>I5_INOUT,"Type"=>I5_TYPE_CHAR, "Length"=>"12"),
				array("Name"=>"CFILLER", "IO"=>I5_INOUT,"Type"=>I5_TYPE_CHAR, "Length"=>"87"),
				array("Name"=>"CDATA", "IO"=>I5_INOUT,"Type"=>I5_TYPE_CHAR, "Length"=>"3900")));
		
		$queue = new wi400Routine("QRCVDTAQ");
		$queue->load_description ("QRCVDTAQ_K", $CLIENT, True);
		$queue->prepare ();
		$queue->set('CODA', "WI400VIRTC");
		$queue->set('LIBRERIA', "QGPL");
		$queue->set('LEN', "8000");
		$queue->set('KEYORDER', "EQ");
		$queue->set('KEYLEN', 40);
		$queue->set('WAIT', 2);
		$queue->set('SENDERLEN', 0);
		$queue->set('KEY', $this->id_sessione);
		
		$do = $queue->call();
		$dati = $queue->get('DATI');
		return $dati;
	}
	public function closeTerminalConnection($options="") {
		global $db, $connzend;
		$interfaccia = new wi400Routine('RUNVIRT', $connzend);
		$interfaccia->load_description();
		$interfaccia->prepare(True);
		$interfaccia->set('OPER',"CLOSE");
		$interfaccia->set("INPUTS", $options);
		$interfaccia->set('DATA',"");
		$interfaccia->call();
		// Controllo il risultato
		$risultato = $interfaccia->get('FLAGRIT');
		if ($risultato=="0") {
			return $dati;
		}
		return False;
	}
	public function parseDataStream($stream) {
		global $routine_path;

		require_once $routine_path."/generali/conversion.php";
		// Estraggo tutte le variabili
		$this->PO = new wi400AS400ObjStream();
		$dataStream="";
		// Verifico quante letture ho fatto, al momento lo tratto come un unico STREAM
		$letture = explode("!", $stream);
		//showArray($letture);
		//die();
		foreach ($letture as $letkey => $lettura) {
			if ($lettura!="") {
				$dataStream .=substr(trim($lettura), 1);
			}
		}
		$len = strlen(trim($dataStream));
		$this->AR = str_split(trim($dataStream), 2);
		$this->totchar = count($this->AR);
		// Comincio a ciclare sull'array e verifico cosa c'è dentro
		$this->i=0;
		for (; ; ) {
			if ($this->i >= $this->totchar) {
				break;
			}
			// Verifico se è un comando di ESCAPE x'04'
			if ($this->AR[$this->i]=="04") {
				// Verifico il tipo di comando che mi è stato passato
				$this->i++;
				switch ($this->AR[$this->i]) {
					// Save Screen
					case "02":
						$this->parseDSC02();
						break;
					// Save Partial Screen
					case "03":
						$this->parseDSC03();
						break;
					// Write to Display	
					case "11":
						$this->parseDSC11();
						break;
					// Restore Screen
					case "12":
						$this->parseDSC12();
						break;
					// Restore Partial Screen
					case "13":
						$this->parseDSC13();
						break;
					// Save to Printer
					case "16":
						$this->parseDSC13();
						break;
					// Clear Alternate	
					case "20":
						$this->parseDSC20();
						break;
					// Errore Message
					case "21":
						$this->parseDSC21();
						break;
					// Errore Message to Windows
					case "22":
						$this->parseDSC22();
						break;
					// Roll
					case "23":
						$this->parseDSC23();
						break;
					// Clear Screen
					case "40":
						//$this->PO->setClear(True);
						$this->parseDSC40();
						break;
					// Read Input Fields
					case "42":
						$this->parseDSC42();
						break;
					// Clear Format Table
					case "50":
						$this->parseDSC50();
						break;
					// Read MDT Fields
					case "52":
						$this->parseDSC52();
						break;
					// Read Screen
					case "62":
						$this->parseDSC62();
						break;
					// Read Screen with Extended Attributes
					case "64":
						$this->parseDSC64();
						break;
					// Read Screen to Print
					case "66":
						$this->parseDSC66();
						break;
					// Read Screen to Print with Extended Attributes
					case "68":
						$this->parseDSC68();
						break;
					// Read Screen to Print with Gridlines
					case "6A":
						$this->parseDSC6A();
						break;
					// Read Screen to Print with Gridlines and Extended Attributes
					case "6C":
						$this->parseDSC6C();
						break;
					// Read Immediate
					case "72":
						$this->parseDSC72();
						break;
					// Read MDT Alternate
					case "82":
						$this->parseDSC82();
						break;
					// Read Immediate MDT Alternate
					case "83":
						$this->parseDSC83();
						break;
					// Write Structured Field
					case "F3":
						$this->parseDSCF3();
						break;
					// Write Single Structured Field
					case "F4":
						$this->parseDSCF4();
						break;
					default:
						echo "<br>Comando ".$this->AR[$this->i]." non trovato alla riga $this->i";
						error_log("Comando ".$this->AR[$this->i]." non trovato alla riga $this->i");
						break;
						
				}
			} else {
				// Dati modificati
			}
			$this->i++;
		}

		return $this->PO;
	}
	public function parseDSC13() {
		$dsc13 = new wi400AS400DSC("13");
		//echo "<br>Posizione $this->i"." - ";
		//$numero = $this->i - 70;
		//echo "<br>NUMERO:".$numero;
		//$stringa="";
		//for ($i=0;$i<80;$i++) {
		//	echo "<br>CICLO $i ".($numero+$i);
		//	$stringa.=$this->AR[$numero+$i];
		//}
		//echo "<br>STRINGA:".$stringa;
		//die();
		$this->PO->addCommand($dsc13);
	}
	public function parseDSCF3() {
		$dscF3 = new wi400AS400DSC("F3");
		$this->PO->addCommand($dscF3);
	}
	public function getResolutionRow() {
		$res = explode("x", $this->resolution);
		return $res['0'];
	}
	public function getResolutionCol() {
		$res = explode("x", $this->resolution);
		return $res['1'];
	}
	public function parseDSC20() {
		$dsc20 = new wi400AS400DSC("20");
		$this->i++;
		if ($this->AR[$this->i]=="00") {
			$dsc20->addParameter("SCREEN_SIZE","27x132");
			$this->resolution = "27x132";
		} else {
			$dsc20->addParameter("SCREEN_SIZE","24x80");
			$this->resolution = "24x80";
		}
		$this->PO->addCommand($dsc20);
	}
	public function parseDSC21() {
		$dsc21 = new wi400AS400DSC("21");
		$dsc21->setError(True);
		// Verifico se c'è un Cursor Order
		if ($this->AR[$this->i+1]=="13") {
			$cursorOrder = new wi400AS400CursorOrder($this->AR[$this->i+1].$this->AR[$this->i+2]);
			$this->i = $this->i+2;
			$dsc21->setCursorOrder($cursorOrder);
		}
		// Reperisco il messaggio
		$testo="";
		$start=$this->i;
		for ($jj=$start;$jj++; $jj <= $this->totchar) {
			$this->i=$jj;
			// Gestire Meglio il fine stringa
			if ($this->AR[$jj]=="20") {
				break;
			}
			$testo .= e2a(wi400AS400Func::hex2str($this->AR[$jj]));
		}
		$messaggio = $testo;
		$dsc21->setErrorMessage($messaggio);
		$this->PO->addCommand($dsc21);
	}
	public function parseDSC40() {
		$dsc40 = new wi400AS400DSC("40");
		$dsc40->setClear(True);
		$this->PO->addCommand($dsc40);
	}
	public function parseDSC52() {
		$dsc52 = new wi400AS400DSC("52");
		$dsc52->setRead(True);
		// Reperisco il carattere di controllo
		$byte = $this->AR[$this->i+1].$this->AR[$this->i+2];
		$controlChar = new wi400AS400ControlCharacter($byte);
		$dsc52->setControlChar($controlChar);
		$this->i=$this->i+3;
		$this->PO->addCommand($dsc52);
	}
	/*
	 * @ Parsing del comando WRITO DI DISPLAY
	 */
	public function parseDSC11() {
		// Estraggo il caratteri di controllo Successivi 2 Byte
		$dsc11 = new wi400AS400DSC("11");
		$byte = $this->AR[$this->i+1].$this->AR[$this->i+2];
		$controlChar = new wi400AS400ControlCharacter($byte);
		$dsc11->setControlChar($controlChar);
		$this->i=$this->i+3;
		// Verifico se è l'inizio dell'Header SOH
		//die($this->AR[$this->i]);
		if ($this->AR[$this->i]=="01") {
			$SOH = new wi400AS400SOHHeader($this->AR, $this->i+1);
			$dsc11->setSOHHeader($SOH);
			// Shifto I
			$this->i=$this->i + $SOH->lunghezza+2;
		}
		$chiavi = array_keys(wi400AS400Constant::wtd);
		//echo "<pre>";
		//echo var_dump($this->PO);die();
		$prg=0;
		$do=True;
		// A questo punto comincio ad estrarre i dati INPUT e TESTO
		while ($do==True) {
			// Verifico se trovo un WTD allora ESCO
			if (isset($this->AR[$this->i]) && $this->AR[$this->i]=="04") {
				//echo "<br>SON OQUI!. $this->i";
				$this->i--;
				$do=False;
				break;
			}
			// Fino ciclo dei caratteri ESCO
			if ($this->i > $this->totchar) {
				break;
			}
			//if (isset($this->AR[$this->i]) && in_array($this->AR[$this->i], $chiavi)) {
				$comando = $this->AR[$this->i];
				//echo "<br>COMANDO:".$comando;
				switch ($comando)  {
					// Campo testo o input Field / Discriminante la presenza di 1D
					case "11":
						//if ($this->AR[$this->i+1]!="00") {
							//$prg++;
							//$nameField="PRG_".$prg;
							//$field = new wi400AS400Field($nameField);
							//$field->start=$this->i+1;
							//$field->data=$this->AR[$this->i+1].$this->AR[$this->i+2].$this->AR[$this->i+3];
							$this->i++;
							//$field->setXposition(hexdec($this->AR[$this->i]));
							$this->curX = hexdec($this->AR[$this->i]);
							$this->i++;
							//$field->setYposition(hexdec($this->AR[$this->i])+1);
							$this->curY = hexdec($this->AR[$this->i])+1;
							break;
					// Campo di input		
					case "1D":
							$prg++;
							$nameField="PRG_".$prg;
							$field = new wi400AS400Field($nameField);
							$field->setXposition($this->curX);
							$field->setYposition($this->curY);
							$field->setIO(True);
							$this->i++;
							$field = $this->getIOField($field);
							$dsc11->addField($nameField, $field);
							$this->i--;
							break;
					// Posizionamento Cursore		
					case "13":	
						    break;
					// Repeat Text
					case "02":
							$this->i=$this->i+1;
							$reprow = hexdec($this->AR[$this->i]);
							$this->i=$this->i+1;
							$repcol = hexdec($this->AR[$this->i]);
							//$quanti = $repcol - $field->getYposition();
							$this->i++;
							//echo "<br>".($this->i-2)."<br>";
							//echo "02".$this->AR[$this->i-1].$this->AR[$this->i].$this->AR[$this->i+1].$this->AR[$this->i+2];
							//die();
							$carattere = $this->AR[$this->i];
							$this->insertCharUntil($carattere, $reprow, $repcol);
							//$testo .=str_repeat(e2a(wi400AS400Func::hex2str($carattere)), $quanti);
							//$jj=$jj+3;
							break;
					// Uscita se TROVO 04
					case "04":
						 	die("PARSING ERASE NON FOUND!");
							$uscita = True;
							break;
					// Tutto il resto è testo o attributi colori che scrivo sulla mappa		
					default:
						//echo "<br>".$this->curX. " - ".$this->curY;
						$this->insertChar($this->AR[$this->i]);
						//$this->mappaValori[$this->curX][$this->curY]=e2a(wi400AS400Func::hex2str($this->AR[$this->i]));
						//$this->curY = $this->curY + 1;
						//echo "TESTO:".e2a(wi400AS400Func::hex2str($this->AR[$this->i]));
						break;
				}
			//}
			$this->i++;
			if ($this->i > $this->totchar) {
				break;
			}
		}
		// Recupero il testo
		$this->swapText($dsc11);
		// Salvo il comando
		$this->PO->addCommand($dsc11);
	}
	public function swapText($dsc11) {
		// Recupero Test di tutte le input FIELD
		$fields = $dsc11->getFields();
		$prg=0;
		$testo = "";
		/*foreach ($this->mappaValori as $key => $value) {
			$testo.="($key)";
			foreach ($value as $key2 => $value2) {
				$testo.= "($key2)".$value2;
			}
			$testo .="<br>";
		}
		echo $testo;
		//die($testo);*/
		foreach ($fields as $key => $value) {
			$value->setText($this->getText($value->getXposition(), $value->getYposition(), $value->getLength()));
		}
		// Scrivo tutte le etichette testo rimaneneti
		foreach ($this->mappaValori as $key => $value) {
			$prg++;
			$nameField="TXT_".$prg;
			$field = new wi400AS400Field($nameField);
			//$field->start=$this->i+1;
			//$field->data=$this->AR[$this->i+1].$this->AR[$this->i+2].$this->AR[$this->i+3];
			$field->setXposition($key);
			$first = true;
			$colonna=0;
			foreach ($value as $key2 => $value2) {
				if ($first && substr($value2,0,3)=="ATR") {
					$field->setColour(substr($value2,4,2));
					continue;
				}
				if ($first==True) {
					$colonna=$key2;
					$field->setYposition($key2);
					$first=False;
					// Se supero la lunghezza massima delle colonne aggiungo un BR
				}
				// Attributo di chiusura dovrebbe essere sempre 020 oppure non c'è dato a video
				$step = $key2-$colonna;
				if (substr($value2,0,3)=="ATR" || $step>1) {
					$dsc11->addField($nameField, $field);
					$prg++;
					$nameField="TXT_".$prg;
					$field = new wi400AS400Field($nameField);
					$field = new wi400AS400Field($nameField);
					$field->setColour(substr($value2,4,2));
					$field->setXposition($key);
					$first=True;
					continue;
				}
				$colonna=$key2;
				$field->setText($field->getText().$value2);
			}
			$dsc11->addField($nameField, $field);
		}
	}
	public function getText($row, $colonna, $lunghezza) {
		$colonne=$this->getResolutionCol();
		$righe=$this->getResolutionRow();
		$testo="";
		$numero=0;
		for ($i=0;$i<1000;$i++) {
			if (isset($this->mappaValori[$row][$colonna])) {
				$valore = $this->mappaValori[$row][$colonna];
				if (substr($valore,0,4)=="ATR:") $valore = "";
				$testo .= $valore;
				unset($this->mappaValori[$row][$colonna]);
				$numero++;
				$colonna++;
			} else {
				break;
			}
			// Se ho preso tutto il testo
			if ($lunghezza == $numero) {
				break;
			}
			if ($colonna>$colonne) {
				$colonna=1;
				$row++;
			}
		}
		return $testo;
	}
	public function insertChar($carattere) {
		//$colonne=80;
		//$righe=24;
		$colonne=$this->getResolutionCol();
		$righe=$this->getResolutionRow();
		static $key_colour;
		if (!isset($key_colour)) {
			$key_colour = array_keys(wi400AS400Constant::colour);
		}
		// Se + un attributo colore lo scrivo così come è con ATR
		if (in_array($carattere, $key_colour)) {
			$this->mappaValori[$this->curX][$this->curY]="ATR:".$carattere;
		} else {
			if ($carattere == '00') $carattere = '40';
			$this->mappaValori[$this->curX][$this->curY]=e2a(wi400AS400Func::hex2str($this->AR[$this->i]));
		}
		//$this->curY = $this->curY + 1;
		// Verifico se salto RIGA
		if ($this->curY>$colonne) {
			$this->curY=1;
			$this->curX++;
		}
		$this->curY = $this->curY + 1;
	}
	public function insertCharUntil($carattere, $row, $colonna) {
		// Calcolo eventuale salto riga e Y
		//echo "<br>".$carattere. " ".$row. " ".$colonna;
		for ($i=0;$i<1000;$i++) {
			$this->insertChar($carattere);
			if ($this->curX==$row && $this->curY>=$colonna) {
				//die("SONO ARRIVATO".$i);
				break;
			}
		}
	}
	public function getIOField($field) {
			//$field = new wi400_5250Field();
			$end=False;
			$findFFW=False;
			$findAttribute=False;
			$findControl=False;
			$findColour=False;
			$value="";
			$ciclo=0;
			$testo="";
			$binary="";
			$controlPosition=0;
			$key_colour = array_keys(wi400AS400Constant::colour);
			$testo="";
			$binary = sprintf('%08b',  hexdec($this->AR[$this->i])).sprintf('%08b',  hexdec($this->AR[$this->i+1]));
			// Attribute Field FFW
			if (substr($binary,0,2)=="01" && $findFFW==False) {
				$field->setFFW($binary);
				$this->i=$this->i+2;
				//echo "<br>NEXT AFTER FFW:".$array[$x];
				$findFFW=True;
				$controlPosition=$this->i;
			}
			// Field Attribute
			$binary = sprintf('%08b',  hexdec($this->AR[$this->i])).sprintf('%08b',  hexdec($this->AR[$this->i+1]));
			if (substr($binary,0,3)=="001" && $findAttribute==False && $this->colourDisplay==False) {
				//echo "<br>ATTRIBUTE ".$array[$x]. " BINARY ".$binary;
				// Setto gli attributi del campo
				$field->setAttribute(substr($binary,0,8));
				$x=$x+1;
				$findAttribute=True;
				//echo "<br>NEXT AFTER ATTRIBUTE:".$array[$x];
			}
			// Control Word
			if (substr($this->AR[$this->i],0,1)=="8" && $findControl==False && $findFFW==True && $controlPosition==$this->i) {
				// Deve essere immediatamente successivo a FFW
				// Setto il control World
				//die("CONTROL". $array[$x]);
				$findControl=True;
				$this->i=$this->i+2;
			}
			// Verifo se è un attributo di colore
			if (in_array($this->AR[$this->i], $key_colour) && $findColour==False && $this->colourDisplay==True) {
				$field->setColour($this->AR[$this->i]);
				$this->i=$this->i+1;
				// Immediatamente dopo il colore c'è la lunghezza
				$lunghezza = hexdec($this->AR[$this->i].$this->AR[$this->i+1]);
				$field->setLength($lunghezza);
				$this->i=$this->i+2;
				$findColour=True;
			}
			return $field;
	}
	public function writeLog($operazione, $data) {
		global $settings;
		$filename = wi400File::getUserFile("sessioni5250", $this->id_sessione."_log.dat");
		$handle = fopen($filename, "a");
		$dati = date("Y-m-d h:i:s"). " $operazione:$data\r\n";
		fwrite($handle, $dati);
		fclose($handle);
	}
	public function getTextField($array, $pos) {
		$pos = $pos+1;
		$binary = sprintf('%08b',  hexdec($array[$pos]));
	}
}