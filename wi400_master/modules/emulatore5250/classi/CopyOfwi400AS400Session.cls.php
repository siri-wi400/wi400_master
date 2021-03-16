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
		$interfaccia = new wi400Routine('RUNVIRT', $connzend);
		$interfaccia->load_description();
		$interfaccia->prepare(True);
		$interfaccia->set('OPER',"OPEN");
		$interfaccia->set('DATA',"");
		$interfaccia->call();
		// Controllo il risultato
		$risultato = $interfaccia->get('FLAGRIT');
		if ($risultato=="0") {
			return True;
		}
		return False;
	}
	public function writeTerminalData($dati, $options="", $log=True) {
		global $db, $connzend;
		$interfaccia = new wi400Routine('RUNVIRT', $connzend);
		$interfaccia->load_description();
		$interfaccia->prepare(True);
		$interfaccia->set('OPER',"WRITE");
		$interfaccia->set("INPUTS", $options);
		$interfaccia->set('DATA',$dati);
		$interfaccia->call();
		// Controllo il risultato
		$risultato = $interfaccia->get('FLAGRIT');
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
		$dati=False;
		$interfaccia = new wi400Routine('RUNVIRT', $connzend);
		$interfaccia->load_description();
		$interfaccia->prepare(True);
		$interfaccia->set('OPER',"READ");
		$interfaccia->set("INPUTS", $options);
		$interfaccia->set('DATA',"");
		$interfaccia->call();
		// Controllo il risultato
		$risultato = $interfaccia->get('FLAGRIT');
		if ($risultato=="0") {
			$dati = $interfaccia->get('DATA');
		}
		if ($log==True) {
			$this->writeLog("READ ", $dati);
		}
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
	public function parseDSC20() {
		$dsc20 = new wi400AS400DSC("20");
		$this->i++;
		if ($this->AR[$this->i]=="00") {
			$dsc20->addParameter("SCREEN_SIZE","27x132");
		} else {
			$dsc20->addParameter("SCREEN_SIZE","24x80");
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
			if (isset($this->AR[$this->i]) && in_array($this->AR[$this->i], $chiavi)) {
				$comando = $this->AR[$this->i];
				switch ($comando)  {
					// Campo testo o input Field / Discriminante la presenza di 1D
					case "11":
						if ($this->AR[$this->i+1]!="00") {
							$prg++;
							$nameField="PRG_".$prg;
							$field = new wi400AS400Field($nameField);
							$field->start=$this->i+1;
							$field->data=$this->AR[$this->i+1].$this->AR[$this->i+2].$this->AR[$this->i+3];
							$this->i++;
							$field->setXposition(hexdec($this->AR[$this->i]));
							$this->i++;
							$field->setYposition(hexdec($this->AR[$this->i])+1);
							$this->i++;
							// Verifico se è un campo di INPUT/OUTPUT
							if ($this->AR[$this->i]=="1D") {
								$field->setIO(True);
								$field = $this->getIOField($field, $this->AR, $this->i);
								$this->i=$this->i -1;
							} else {
								$field->setIO(False);
								$field->setColour($this->AR[$this->i]);
								// Verifico se si tratta di un REPEAT ORDER
								$this->i++;
								/*if ($this->AR[$this->i]=="02") {
									// Devo scrivere un carattere in una posizione determinata
									$newfield = $dsc11->getFieldByXY($field->getXposition(), $field->getYposition());
									// se esiste vuol dire che posso metterci un valore
									if ($newfield!==False) {
										$field = $newfield;
										$this->i++;
										$reprow = hexdec($this->AR[$this->i]);
										$this->i++;
										$repcol = hexdec($this->AR[$this->i]);
										$this->i++;
										$quanti = $repcol - $field->getYposition();
										$carattere = $this->AR[$this->i];
										if ($carattere == '00') $carattere = '40';
										$field->setText(str_repeat(e2a(wi400AS400Func::hex2str($carattere)), $quanti));
									} else {
										$this->i++;
										continue;
									}
									break;
								}*/
								// Trovo la fine del testo devo trovare un 20 di ripristino o fine carattere
								$testo = "";
								$this->i--;
								//die("SONO QUI!!!". $this->i." - ".$this->totchar);
								for ($jj=$this->i;$jj++; $jj <= $this->totchar) {
									// Verifico se è finita la stringa
									if ($this->AR[$jj]=="11" || $this->AR[$jj]=="04") {
										//echo "<br>SONO QUI $jj";
										$jj--;
										//echo "<br>TESTO:".$testo;
										break;
									}
									if ($jj >= $this->totchar) {
										//die($jj. " - ".$this->totchar);
										break;
									}
									// Se '00' converto in spazio
									if ($this->AR[$jj]=="00") {
										$this->AR[$jj]="40";
									}
									if ($this->AR[$jj]=="20") {
										continue;
									}
									// Se trovo un '02' Significa che devo correre con un carattere fino a
									if ($this->AR[$jj]=="02") {
										$reprow = hexdec($this->AR[$jj+1]);
										$repcol = hexdec($this->AR[$jj+2]);
										$quanti = $repcol - $field->getYposition();
										$carattere = $this->AR[$jj+3];
										if ($carattere == '00') $carattere = '40';
										$testo .=str_repeat(e2a(wi400AS400Func::hex2str($carattere)), $quanti);
										$jj=$jj+3;
									} else {
										$testo .= e2a(wi400AS400Func::hex2str($this->AR[$jj]));
									}
									//echo "<br>$testo";
									// Verifico se è finita la stringa
									if ($this->AR[$jj+1]=="11" || $this->AR[$jj+1]=="04") {
										//echo "SONO QUI!!";
										//if ($array[$jj+1]=="20") {
										break;
									}
								}
								$this->i=$jj;
								//echo "<br>NEXT READ:".$this->AR[$this->i];
								$mydati = str_split($testo, 132);
								$testo = implode("<br>",$mydati);
								$field->setText($testo);
							}
							$dsc11->addField($nameField, $field);
						}
						break;
				}
			}
			$this->i++;
			if ($this->i > $this->totchar) {
				break;
			}
		}
		$this->PO->addCommand($dsc11);
	}
	public function getIOField($field, $array, $pos) {
			$x = $pos+1;
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
			while ($end==False ){
				// Fine ciclo di lettura del buffer
				if (!isset($array[$x]) || $array[$x]=="") {
					$this->i=$x;
					break;
				}
				// Trasformo in binario quello che trovo dopo
				if (isset($array[$x+1])) {
					$binary = sprintf('%08b',  hexdec($array[$x])).sprintf('%08b',  hexdec($array[$x+1]));
					// Attribute Field FFW
					if (substr($binary,0,2)=="01" && $findFFW==False) {
						// Setto gli attributi FFW
						$field->setFFW($binary);
						$x=$x+2;
						//echo "<br>NEXT AFTER FFW:".$array[$x];
						$findFFW=True;
						$controlPosition=$x;
						continue;
					}
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
				if (substr($array[$x],0,1)=="8" && $findControl==False && $findFFW==True && $controlPosition==$x) {
					// Deve essere immediatamente successivo a FFW
					// Setto il control World
					//die("CONTROL". $array[$x]);
					$findControl=True;
					$x=$x+2;
					continue;
				}
				// Verifo se è un attributo di colore
				if (in_array($array[$x], $key_colour) && $findColour==False && $this->colourDisplay==True) {
					$field->setColour($array[$x]);
					$x=$x+1;
					// Immediatamente dopo il colore c'è la lunghezza
					$lunghezza = hexdec($array[$x].$array[$x+1]);
					$field->setLength($lunghezza);
					$x=$x+2;
					$findColour=True;
					continue;
				}
				// Verifico se è la posizione
				if ($array[$x]=="11") {
					// Torno indietro di 11
					$this->i--;
					break;
					//die("<br>SETTO POSIZIONE!".$array[$x+1]. " COL ".$array[$x+2]. " HEX ".hexdec($array[$x+1]));
					//$field->setXposition(hexdec($array[$x+1]));
					//$field->setYposition(hexdec($array[$x+2]));
					// Ultima cosa da fare
					//break;
				}
				// Nessuno degli altri caso comincia il testo
				$ciclo++;
				if ($ciclo>$lunghezza) {
					//echo "<br>TESTO:".$testo;
					$field->setText($testo);
					break;
				}
				//echo "<br>TEST:".$testo. " - ".$this->AR[$x];
				if ($array[$x]=="00") {
					$testo .=" ";
				} else {
					$testo .= e2a(wi400AS400Func::hex2str($this->AR[$x]));
				}
				// @todo Devo settare il valore
				//echo "<br>ARRIVO:".$array[$x];
				$this->i++;
				$x++;
				continue;
				//break;
			}
			$field->setText($testo);
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