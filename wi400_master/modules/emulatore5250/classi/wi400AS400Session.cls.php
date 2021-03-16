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
	private $password="";
	private $device="";
	private $pgm="";
	private $lastError="";
	private $PO=Null; //PARSE OBJECT
	private $lastErrorMessage="";
	private $i=0;
	private $AR=array();
	private $totchar;
	private $colourDisplay = True;
	private $mappaValori = array();
	private $mappaField = array();
	private $mappaAttributi = array();
	private $curX= 0;
	private $curY= 0;
	private $resolution ="24x80";
	private $commType="SERVER";
	private $sDTAQ="WI400VIRTS";
	private $sLIBQ="QGPL";
	private $cDTAQ="WI400VIRTC";
	private $cLIBQ="QGPL";
	private $key_colour=array();
	private $boxCoords=array("TL"=>0,"TR"=>0,"LL"=>0,"LR"=>0);
	private $timeout=10;
	private $max_cicli=8;
	private $applicationParm="";
	private $macroScript="";
	
	public function __construct($id="", $user="", $password="", $device="", $initpgm="", $initlib="") {
		// Sessione
		if ($id=="") {
			$id=uniqid("TN_", True);
		}
		$this->id_sessione=$id;
		$this->user=$user;
		$this->password=$password;
		$this->initpgm=$initpgm;
		$this->initlib=$initlib;
		$this->key_colour = array_keys(wi400AS400Constant::colour);
	}
	public function setMacroScript($macroScript) {
		$this->macroScript=$macroScript;
	}
	public function getMacroScript() {
		return $this->macroScript;
	}
	public function setUser($user) {
		$this->user=$user;
	}
	public function getId() {
		return $this->id_sessione;
	}
	public function setPassword($password) {
		$this->password=$password;
	}
	public function setDevice($device) {
		$this->device=$device;
	}
	public function setInitPgm($initPgm) {
		$this->initpgm=$initPgm;
	}
	public function setInitLib($initLib) {
		$this->initlib=$initLib;
	}
	public function setApplicationParm($applicationParm) {
		$this->applicationParm = $applicationParm;
	}
	public function getApplicationParm() {
		return $this->applicationParm;
	}
	public function executeMacro($display) {
		$macro = $this->getMacroScript();
		eval("$macro");
		// Esecuzione con una eval del codice PHP, implementare ricerca TEST e così via
	}
	public function connect() {
		$extraParm="";
		$sepa="";
		if ($this->user!="") {
			$extraParm.=$sepa."USER=$this->user";
			$sepa=";";
		}
		if ($this->password!="") {
			$extraParm.=$sepa."PASSWORD=$this->password";
			$sepa=";";
		}
		if ($this->device!="") {
			$extraParm.=$sepa."DEVICE=$this->device";
			$sepa=";";
		}
		if ($this->initpgm!="") {
			$extraParm.=$sepa."INITPGM=$this->initpgm";
			$sepa=";";
		}
		if ($this->initlib!="") {
			$extraParm.=$sepa."INITLIB=$this->initlib";
			$sepa=";";
		}
		$this->openTerminalConnection($extraParm);
	}
	public function sendSysReq($req="03") {
		$queueSend = dtaq_prepare(trim($this->sLIBQ).'/'.trim($this->sDTAQ), wi400AS400Constant::QRCVMSG, "S",-1, False);
		$dati=array(
				"VTQTYP"=>"*CLIENT",
				"VTOID"=>$this->id_sessione,
				"VTOPER"=>"RQS",
				"VTCHAR"=>$req);
		dtaq_send($queueSend,"",$dati);
	}
	public function sendATTN($req="03") {
		$queueSend = dtaq_prepare(trim($this->sLIBQ).'/'.trim($this->sDTAQ), wi400AS400Constant::QRCVMSG, "S",-1, False);
		$dati=array(
				"VTQTYP"=>"*CLIENT",
				"VTOID"=>$this->id_sessione,
				"VTOPER"=>"ATTN",
				"VTCHAR"=>"");
		dtaq_send($queueSend,"",$dati);
	}
	public function openTerminalConnection($extraParm="") {
		global $db, $connzend;
		if ($this->commType=="CLIENT") {
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
		if ($this->commType=="SERVER") {
			// @todo controllare se il server è ATTIVO
			$queueSend = dtaq_prepare(trim($this->sLIBQ).'/'.trim($this->sDTAQ), wi400AS400Constant::QRCVMSG, "S",-1, False);
			$dati=array(
					"VTQTYP"=>"*CLIENT",
					"VTOID"=>$this->id_sessione,
					"VTOPER"=>"OPEN",
					"VTCHAR"=>$extraParm);
			dtaq_send($queueSend,"",$dati);
			// Non posso sapere la risposta subito, di default va bene
			return True;
		}
	}
	private function writeTextLog() {
		$filename = wi400File::getLogFile("sessioni5250", $this->id_display.".dat");
	}
	/**
	 * Gestione colloquio con 5250 per visualizzazione della maschera.
	 * @param string $ajax
	 */
	public function manage5250($ajax=False) {
		global $db, $firephp;
		
		//$Sessione5250 = new wi400AS400Session($session_id);
		if ($ajax==False) {
			$display = new wi400AS400Display($this->id_sessione, True);
		}
		if ($ajax==True) {
			// Carico il Display
			$display= wi400AS400Func::loadDisplay($this->id_sessione);
			//$session_id = $_GET['SESSION_ID'];
			$function_key = $_GET['FUNCTION_KEY'];
			$colonna = $_GET['COL'];
			$riga = $_GET['ROW'];
			$continue = True;
			if(isset($_GET['CMD_SYSTEM'])) {
				// chiamo l'api di sistema per la richiesta
				$this->sendSysReq();
				$continue=False;
			}
			if ($function_key=="ATTN") {
				$this->sendATTN();
				$continue=False;
			}
			if ($continue==True) {
				// @TODO Capire cosa si aspetta il video .. tutto o solo i campi modificati
				// Prendo la posizione del cursore
				if($display->getIsWindow()) {
					$hex = $display->getPosWindowHex();
				}else {
					$hex = "0101"; // Default posizione cursore
				}
				// Reperisco il campo selezionato da campo selezionato se passato
				if ($_REQUEST['CAMPO_SELEZIONATO']!="") {
					$newhex = $display->getFocusedField($_REQUEST['CAMPO_SELEZIONATO'], $_REQUEST['CARATTERI']);
					if ($newhex!="") {
						$hex = $newhex;
					}
				} else {
					// Controllo se è passata la posizione del cursore
					$hex=wi400AS400Func::num2hex($riga).wi400AS400Func::num2hex($colonna);
				}
				// @todo da implementare
				$hex .=$function_key;
				// Se ho premuto il tasto ATTN non c'è la posizione del cursore ma solo l'esadecimale
				//echo "<br>HEX FUNCTION KEY".$hex;
				$modifystring = $display->getModifiedString($_REQUEST);
				$hex .=$modifystring;
				// Gestione selezione CHOICE
				if ($function_key=="CHOICE") {
					$hex = $display->getFocusedField($_REQUEST['CAMPO_SELEZIONATO'], 0);
					//$hex=wi400AS400Func::num2hex($riga).wi400AS400Func::num2hex($colonna);
					$hex = $hex."F111".$hex."0020";
				}
				/*if ($function_key=="ATTN") {
					$hex = "C900010001";
				}*/
				// @todo Controllo che il tasto di funzione sia tra quelli previsti
				$dati = $this->writeTerminalData($hex);
			}
		} else {
			// Nuova Connessione
			$this->connect();
		}
		$display = $this->dialogWith5250($display);
		return $display;
	}
	public function dialogWith5250($display) {
		global $firephp;
		// @verifico i dati di ritorno per comunicare eventuali errori
		// Leggo un prima sequenza di dati per tornare già una risposta del 5250 interrogato, aspetto finche no ho risposta
		$timeout =$this->timeout;
		$stream="";
		$streamAll="";
		$sepa="";
		$max_cicli=$this->max_cicli;
		$window=False;
		$i=0;
		$infodat="";
		
		//$file = fopen("/www/zendsvr/htdocs/WI400_pasin/zElimina.txt", "a");
		
		do {
			$dati = $this->readTerminalData($timeout);
			$stream = "3".$dati['CDATA'];
			$obj = $this->parseDataStream($stream);
			if ($dati['CREADOP']=="400") {
				$hex ="0412";
				$dati = $this->writeTerminalData($hex);
				// Elaboro eventuali dati arrivati e poi resetto lo STREAM
				$obj = $this->parseDataStream($streamAll);
				$display->setStreamObj($obj, $window);
				$display->executeCommand();
				$stream="";
				$streamAll = "";
				$sepa="";
				$display->saveScreen();
				$i++;
				continue;
			}
			// Verifico se mi è arrivato un comando di QUERY
			if ($obj->getCommandByType("F3")) {
				$hex ="0000880044D9708006000302000000000000000000000000000000000001F3F1F7F9F0F0F20101000000701201F40000007B3100000FC800000000000000000000000000000000";
				$dati = $this->writeTerminalData($hex);
				$stream="";
				$streamAll="";
				$sepa="";
				continue;
			}
			if ($obj->getCommandByType("62")) {
				$hex ="0462";
				$dati = $this->writeTerminalData($hex);
				$window=true;
				$stream="";
				// Pulisco anche tutto lo stream precedentemente salvato per la conversione
				$streamAll="";
				$sepa="";
				continue;
			}
			if ($obj->getCommandByType("12") && $dati['CREADOP']=="500") {
				$display->restoreScreen();
				//showArray($display->getFields());
				$stream="";
				//break;
				//continue;
			}
			if ($obj->getCommandByType("52") && $dati['CREADOP']=="100") {
				$hex ="0412";
				$dati = $this->writeTerminalData($hex);
				$stream="";
				$sepa="";
				//die("SONO IN QUESTA SITUAZIONE!!");
				break;
				//continue;
			}
			if ($obj->getCommandByType("02")) {
				$hex ="0402";
				$dati = $this->writeTerminalData($hex);
				// Il salva schermo deve essere comunicato al DISPLAY
				$display->saveScreen();
				$stream="";
				$sepa="";
				continue;
			}
			$streamAll .= $sepa."3".$dati['CDATA'];
			if ($dati['CJOBDET']!="") {
				$infodat = $dati['CJOBDET'];
			}
			$sepa="!";
			$i++;
			if ($i>$max_cicli) {
				break;
			}
		} while ($dati['CREADOP']!="300");
		// Invio il nuovo pannello se c'è qualcosa di letto
		if ($streamAll!="") {
			
			/*fwrite($file, ($window ? "true" : "false")."\r\n");
			fwrite($file, $streamAll."\r\n\n");
			fclose($file);*/
			$obj = $this->parseDataStream($streamAll);
			// Se è cambiata la risoluzione del display devo pulire tutto!
			$colDis = $display->getResolutionCol();
			if ($colDis!="" && $colDis !=$this->getResolutionCol()) {
				$display->clearScreen();
			}
			$display->setStreamObj($obj, $window);
			$display->executeCommand();
			$resolution = $this->getResolutionRow()."x".$this->getResolutionCol();
			$display->setResolution($resolution);
			if ($infodat!="") {
				//$info = new w400JobInfo($infodat);
				$display->setInfo($infodat);
			}
			//$html = $display->display();
		}
		return $display;
	}
	public function writeTerminalData($data, $options="", $log=True) {
		global $db, $connzend;
		if ($this->commType=="CLIENT") {
			$interfaccia = new wi400Routine('RUNVIRT', $connzend);
			$interfaccia->load_description();
			$interfaccia->prepare(True);
			$interfaccia->set('OPER',"WRITE");
			$interfaccia->set("INPUTS", $options);
			$interfaccia->set('DATA',$data);
			$interfaccia->call();
			// Controllo il risultato
			$risultato = $interfaccia->get('FLAGRIT');
			if ($log==True) {
				$this->writeLog("WRITE", $data);
			}
			if ($risultato=="0") {
				return True;
			}
			return False;
		}
		if ($this->commType=="SERVER") {
			$queueSend = dtaq_prepare(trim($this->sLIBQ).'/'.trim($this->sDTAQ), wi400AS400Constant::QRCVMSG, "S",-1, False);
			$dati=array(
					"VTQTYP"=>"*CLIENT",
					"VTOID"=>$this->id_sessione,
					"VTOPER"=>"WRITE",
					"VTCHAR"=>$data);
			dtaq_send($queueSend,"",$dati);
		}
	}
	public function readTerminalData($wait="0", $options="", $log=True) {
		global $db, $connzend;
		if ($this->commType=="CLIENT") {
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
		if ($this->commType=="SERVER") {
			$dati=False;
			$queue = new wi400Routine("QRCVDTAQ");
			$queue->load_description ("QRCVDTAQ_K", wi400AS400Constant::QSNDMSG, True);
			$queue->prepare ();
			$queue->set('CODA', $this->cDTAQ);
			$queue->set('LIBRERIA', $this->cLIBQ);
			$queue->set('LEN', "10000");
			$queue->set('KEYORDER', "EQ");
			$queue->set('KEYLEN', 40);
			$queue->set('WAIT', $wait);
			$queue->set('SENDERLEN', 0);
			$queue->set('KEY', $this->id_sessione);
			$do = $queue->call();

			if ($do)
				$dati = $queue->get('DATI');
			}
			// Taglio i dati a seconda della lunghezza
			$dati['CDATA'] = substr($dati['CDATA'],0, $dati['CLEN']*2);
			return $dati;
	}
	public function closeTerminalConnection($options="") {
		global $db, $connzend;
		if ($this->commType=="CLIENT") {
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
		if ($this->commType=="SERVER") {
			$queueSend = dtaq_prepare(trim($this->sLIBQ).'/'.trim($this->sDTAQ), wi400AS400Constant::QRCVMSG, "S",-1, False);
			$dati=array(
					"VTQTYP"=>"*CLIENT",
					"VTOID"=>$this->id_sessione,
					"VTOPER"=>"CLOSE",
					"VTCHAR"=>"");
			dtaq_send($queueSend,"",$dati);
		}
	}
	public function estraiCurrentsubfile() {
		global $db, $connzend;
		// Scrittura del file per il passaggio dei parametri
		$id = getSequence("EXTSUB");
		$row = $this->getConnectionRecord();
		// prepare dello statement
		// Scrittura record di innesco
		$dati = getDS("ZEXTCAPM");
		$stmtDoc = $db->prepare("INSERT", "ZEXTCAPM", null, array_keys($dati));
		// Cancello un eventuale record presente
		$sql = "DELETE FROM ZEXTCAPM WHERE PGMID='".$id."'";
		$db->query($sql);
		// Scrittura
		$timeStamp = getDb2Timestamp();
		$dati['PGMID']=$id;
		$dati['PGMSTS']="*";
		$dati['PGMTIN']=getDb2Timestamp();
		$dati['PGMJOB']=$row['SESDEV'];
		$dati['PGMUSR']=$row['SESUSR'];
		$dati['PGMNBR']=$row['SESNBR'];
		$dati['PGMDFI']=$row['SESVFI'];
		$dati['PGMDFR']=$row['SESVFM'];
		$dati['PGMFIL']= wi400File::getUserFile('tmp', "EXT_SUBFILE_".$id.".csv");
		
		$result = $db->execute($stmtDoc, $dati);
		// Richiamo programma
		$interfaccia = new wi400Routine('ZCALLEXT', $connzend);
		$interfaccia->load_description();
		$interfaccia->prepare(True);
		$interfaccia->set('ID',$id);
		$interfaccia->call();
		
		return $id;
	}
	public function getConnectionStatus() {
		$stato = "";
		$row = $this->getConnectionRecord();
		if ($row) {
			$stato = $row['SESSST'];
		}
		return $stato;
	}
	public function getConnectionRecord() {
		global $db;
		static $stmt;
		if (!$stmt) {
			$sql = "SELECT * FROM ZOPNSESS WHERE SESSID=?";
			$stmt = $db->singlePrepare($sql);
		}
		$result = $db->execute($stmt, array($this->id_sessione));
		if ($result) {
			$row = $db->fetch_array($stmt);
			return $row;
		}
		return false;
	}
	public function parseDataStream($stream) {
		global $routine_path;

		require_once $routine_path."/generali/conversion.php";
		// Estraggo tutte le variabili
		$this->PO = new wi400AS400ObjStream();
		$dataStream="";
		$this->mappaValori=array();
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
				//echo "<br>COMANDO:".$this->AR[$this->i]. "CARATTERE".$this->i;
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
					// Write to Display	OK
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
					// Errore Message OK
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
						//echo "<br>Comando ".$this->AR[$this->i]." non trovato alla riga $this->i";
						error_log("Comando ".$this->AR[$this->i]." non trovato alla riga $this->i ".wi400AS400Func::getStringFromArray($this->AR,$this->i+-100, 110));
						break;
						
				}
			} else {
				// Dati modificati
			}
			$this->i++;
		}
		// Ciclo sui campi testo
		// Splitto i campi che sono troppo lunghi per essere contenuti in una riga
		$first=True;
		
		$commandByType = $this->PO->getCommandByType("11");
		/*showArray($commandByType);
		ksort($this->mappaValori);
		foreach($this->mappaValori as $arr_valori) {
			ksort($arr_valori);
			echo implode("", $arr_valori)."<br/>";
		}*/
		
		if(1==1) {
		for($i = 1; $i < count($commandByType); $i++) {
			if(isset($commandByType[$i-1])) {
				$commandPrev = $commandByType[$i-1];
				$command = $commandByType[$i];
				
				$fieldsPrev = $commandPrev->getFields();
				if(count($fieldsPrev) > 0) {
					//echo "command $i ha fields<br/>";
					
					$erase = $command->getEraseOrder();
					/*echo "erase__".$i."<br/>";
					showArray($erase);*/
					
					if (in_array("FF", $erase->attribute)) {
						//error_log("ERASE:!!!:/");
						foreach ($fieldsPrev as $key => $value) {
							$myx = $value->getXposition();
							$myy = $value->getYposition();
							//error_log("ERASE:!!!:/ $myx - $myy ".$erase->row." / ".$erase->fromColumn);
							if ($myx<=$erase->row && $myx>=$erase->fromRow) {
								if ($myy<=$erase->column && $myy>=$erase->fromColumn) {
									//error_log("ERASE UNSET:!!!:/ $myx - $myy");
									//echo "unsetto la field $key - $myx $myy - ".$value->getLength()."<br/>";
									$commandPrev->unsetField($key);
									$commandPrev->unsetCoords("$myx-$myy");
									if(isset($this->mappaValoriIO[$myx]) && isset($this->mappaValoriIO[$myx][$myy])) {
										for($y = $myy; $y< $myy+$value->getLength(); $y++) {
											unset($this->mappaValoriIO[$myx][$y]);
										}
										//Se la riga è vuota la cancello
										if(count($this->mappaValoriIO[$myx]) == 0) {
											//echo "mappIO_".$myx."_vuotooo<br/>";
											unset($this->mappaValoriIO[$myx]);
										}
									}
								}
							}
						}
						//$command->setEraseORder(Null);
					}
				}else {
					//echo "command ".($i-1)." non ha fields<br/>";
				}
			}
		}
		}
		
		//showArray($this->mappaValori);
		// Ogni comando deve avere le sue etichette @DA RIVEDERE 
		//$commandByType = $this->PO->getCommandByType("11");
		if (1==1) {
		if(is_array($commandByType)) {
			foreach ($commandByType as $key => $value) {
				// Il testo va fatto solo una volta ...
				$this->splitField($value);
				$this->swapText($value);
				// Creo le etichette di testo solo sul comando iniziale
				if ($first==True) {
					$this->createTxtElement($value);
					$first=False;
				}
			}
		}
		}
		return $this->PO;
	}
	public function parseDSC02() {
		$dsc02 = new wi400AS400DSC("02");
		$this->PO->addCommand($dsc02);
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
	public function parseDSC03() {
		$dsc03 = new wi400AS400DSC("03");
		$this->i=$this->i+5;
		$this->PO->addCommand($dsc03);
	}
	public function parseDSC12() {
		$dsc12 = new wi400AS400DSC("12");
		$this->PO->addCommand($dsc12);
	}
	public function parseDSC62() {
		$dsc62 = new wi400AS400DSC("62");
		$this->i=$this->i+5;
		$this->PO->addCommand($dsc62);
	}
	public function parseDSC22() {
		$dsc22 = new wi400AS400DSC("22");
		$dsc22->setError(True);
		// Reperisco il messaggio
		$testo="";
		$dsc22->addParameter("ROW",hexdec($this->AR[$this->i]));
		$this->i++;
		$dsc22->addParameter("COL",hexdec($this->AR[$this->i]));
		// Gestire come Field ma che contengono errori
		$start=$this->i;
		for ($jj=$start;$jj++; $jj <= $this->totchar) {
			$this->i=$jj;
			// Gestire Meglio il fine stringa
			if ($this->AR[$jj]=="20") {
				break;
			}
			// Se ci sono errori potrei andare i loop
			if ($this->i>$this->totchar) {
				break;
			}
			$testo .= $this->_e2a(wi400AS400Func::hex2str($this->AR[$jj]));
		}
		$messaggio = $testo;
		$dsc22->setErrorMessage($messaggio);
		$this->PO->addCommand($dsc22);
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
		/*if ($this->AR[$this->i+1]=="13") {
			$cursorOrder = new wi400AS400CursorOrder($this->AR[$this->i+1].$this->AR[$this->i+2]);
			$this->i = $this->i+2;
			$dsc21->setCursorOrder($cursorOrder);
		}*/
		// Reperisco il messaggio
		$testo="";
		$start=$this->i;
		for ($jj=$start;$jj++; $jj <= $this->totchar) {
			$this->i=$jj;
			// Gestire Meglio il fine stringa
			//if ($this->AR[$jj]=="20") {
			if ($this->AR[$jj]=="04") {
				//$this->i--;
				break;
			}
			// Verifico se ho un Cursor Order
			if ($this->AR[$this->i]=="13") {
				$cursorOrder = new wi400AS400CursorOrder($this->AR[$this->i+1].$this->AR[$this->i+2]);
				$this->i = $this->i+2;
				$dsc21->setCursorOrder($cursorOrder);
			}
			if (in_array($this->AR[$jj], $this->key_colour)) {
				continue;
			}
			// Se ci sono errori potrei andare i loop
			if ($this->i>$this->totchar) {
				break;
			}
			$testo .= $this->_e2a(wi400AS400Func::hex2str($this->AR[$jj]));
		}
		$messaggio = $testo;
		$dsc21->setErrorMessage($messaggio);
		$this->PO->addCommand($dsc21);
	}
	public function parseDSC40() {
		$dsc40 = new wi400AS400DSC("40");
		$dsc40->setClear(True);
		$this->PO->clearCommands();
		$this->PO->addCommand($dsc40);
		$this->mappaValori=array();
		$this->mappaValoriIO=array();
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
	 * @ Parsing del comando WRITE DI DISPLAY
	 */
	public function parseDSC11() {
		// Estraggo il caratteri di controllo Successivi 2 Byte
		$dsc11 = new wi400AS400DSC("11");
		$byte = $this->AR[$this->i+1].$this->AR[$this->i+2];
		$controlChar = new wi400AS400ControlCharacter($byte);
		$dsc11->setControlChar($controlChar);
		$this->i=$this->i+3;
		//$this->mappaValori=array();
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
		$struct=0;
		$do=True;
		// A questo punto comincio ad estrarre i dati INPUT e TESTO
		while ($do==True) {
			// Fine ciclo
			if (!isset($this->AR[$this->i])) break;
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
				//echo "<br>COMANDO:".$comando. "CARATTERE".$this->i;
				switch ($comando)  {
					// Transparent Data Order
					case "10":
							//error_log("Comando ".$this->AR[$this->i]." non trovato alla riga $this->i");
							die("11 10 non implementato");
							$this->i++;
							$len = hexdec($this->AR[$this->i].$this->AR[$this->i+1]);
							$this->i++;
							$this->i++;
							$dati = wi400AS400Func::getStringFromArray($this->AR, $this->i,$len-3);
							$this->i=$this->i+($len-3);
							break;
					// Campo testo o input Field / Discriminante la presenza di 1D
					case "11":
							$this->i++;
							//$field->setXposition(hexdec($this->AR[$this->i]));
							$this->curX = hexdec($this->AR[$this->i]);
							//echo "<br>X=".$this->curX;
							$this->i++;
							//$field->setYposition(hexdec($this->AR[$this->i])+1);
							// Aggiungo 1 perchè la prima posizione è un attributo ?!
							$this->curY = hexdec($this->AR[$this->i]);
							//echo "<br>Y=".$this->curY;
							// Il sistema può passare colonna zero per il primo campo seguito da 2D
							if ($this->curY==0) $this->curY=1;
							break;
					// Extended Attribute
					case "12":
							error_log("Comando ".$this->AR[$this->i]." non trovato alla riga $this->i");
							die("11 12 non implementato");
							break;
					// Campo di input		
					case "1D":
							$prg++;
							$nameField="VAR_".$prg;
							$field = new wi400AS400Field($nameField);
							//$this->curY++;
							// Verifico se esiste già, seignifica che gli devo andare sopra
							// .....
							$field->setXposition($this->curX);
							$field->setYposition($this->curY+1);
							$mydata = array_slice($this->AR, $this->i-20, 40);
							$field->data = implode("", $mydata);
							$field->setIO(True);
							$this->i++;
							$field = $this->getIOField($field);
							// verifico se in testata c'è il reset del lock altrimenti la proteggo
							// @todo la regola dovrebbe essere che se non era settato nulla do per scontato
							// che la tastiera sia sbloccata, ma è da verificare il comportamento
							//if (($controlChar->unlockKeyboardAndReset!="1" && $controlChar->data!="0000") || $controlChar->cursorMoveOnUnlock!="1") {
							/*if (($controlChar->unlockKeyboardAndReset!="1" && $controlChar->data!="0000")) {
								$field->setBypass("1");
							}*/
							if (($controlChar->data =="0010")) {
								$field->setBypass("1");
								// Ricordo che il campo è stato forzato a protetto
								$field->setForceByPass(True);
							}
							$dsc11->addField($nameField, $field);
							$this->i--;
							// Se poi c'è del testo è un carattere più avanti 
							$this->insertChar($field->getColour(), $this->curX, $this->curY);
							// Scrivo la mappa dei campi di I/O
							$this->writeMappaIO($field);
							//$this->curY++;
							break;
					// Posizionamento Cursore		
					case "13":
							$cursorOrder = new wi400AS400CursorOrder($this->AR[$this->i+1].$this->AR[$this->i+2]);
							$this->i = $this->i+2;
							$dsc11->setCursorOrder($cursorOrder);
						    break;
					// Move Cursor
					case "14":
							$cursorOrder = new wi400AS400CursorOrder($this->AR[$this->i+1].$this->AR[$this->i+2]);
							$this->i = $this->i+2;
							$dsc11->setCursorOrder($cursorOrder);
							break;
					// Strucutured FIELD	
					case "15":
							$structuredField = new wi400AS400StructeredField($this->AR, $this->i+1);
							$struct++;
							$nameField="STRUCT_".$struct;
							$field = new wi400AS400Field($nameField);
							$field->setStructuredData($structuredField);
							$field->setStructured(True);
							$field->setIO(True);
							$field->setXposition($this->curX);
							$field->setYposition($this->curY+1);
							$dsc11->addField($nameField, $field);
							$field->start=$this->i;
							//showArray($structuredField);
							//$dati = wi400AS400Func::getStringFromArray($this->AR, $this->i,50);
							//echo "<br>DATI:".$dati;
							//die();
							$this->i=$this->i+$structuredField->len;
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
							//echo "<br>INSERT CHAR UNTIL:".$this->curX." - ".$this->curY." - ".$carattere." - ".$reprow." - ".$repcol;
							$this->insertCharUntil($carattere, $reprow, $repcol);
							//$testo .=str_repeat(e2a(wi400AS400Func::hex2str($carattere)), $quanti);
							//$jj=$jj+3;
							break;
					// Erase Order
					case "03":
						    //echo "<br>ERASE ORDER!";
						    $stringa = wi400AS400Func::getStringFromArray($this->AR,$this->i+1, 10);
							$eraseOrder = new wi400AS400EraseOrder($stringa, $this->curX, $this->curY);
							//showArray($eraseOrder);
							$len = $eraseOrder->getLength();
							$this->i = $this->i+2+$len;
							$dsc11->setEraseOrder($eraseOrder);
							break;
					// Uscita se TROVO 04
					case "04":
							$uscita = True;
							break;
					// Tutto il resto è testo o attributi colori che scrivo sulla mappa		
					default:
							//echo "<br>Insert:".$this->curX. " - ".$this->curY. " - ".$this->i. " - ".$this->AR[$this->i];
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
 		// Splitto i campi che sono troppo lunghi per essere contenuti in una riga
		//$this->splitField($dsc11);
		// Recupero il testo
		//$this->swapText($dsc11);
		// Controllo se ci sono dei comandi di cancellazione ...
		// Se si cancello tutti i campi presenti nell'OBJECT $dsc11
		// Recupero tutti i camandi di cancellazione presenti
		/*$erase = $dsc11->getEraseOrder();
		if ($dsc11->getEraseOrder()!=Null) {
			// Per ogni comando di cancellazione rimuovo le field
			$fields = $dsc11->getFields();
			$fromR = $erase->fromRow;
			$fromC = $erase->fromColumn;
			$toR = $erase->row;
			$toC = $erase->column;
			//echo "<br>Condizioni:".$fromR."-".$fromC."-".$toR."-".$toC;
			//showArray($fields);
			foreach ($fields as $key => $value) {
				$x = $value->getXposition();
				$y = $value->getYposition();
				//echo "<br>CAMPO:".$value->getId();
				//echo "<br>$x - $y";
				if (($x>=$fromR && $x<=$toR) && ($y>=$fromC && $y<=$toC)) {
					//echo "<br>DELETE!! ".$value->getId();
					$dsc11->unsetField($value->getId());
				}
			}
			
		}*/
		// FINE CANCELLAZIONE
		$this->PO->addCommand($dsc11);
	}
	public function _e2a($dati) {
		return wi400AS400Func::_e2a($dati);
	}
	public function writeMappaIO($field) {
		$c = $this->getResolutionCol();
		$x = $field->getXposition();
		$y = $field->getYposition();
		$l = $field->getLength();
		$row=$x;
		$colonna=$y;
		for ($i=1;$i<=$l;$i++) {
			$this->mappaValoriIO[$row][$colonna]="IO";
			$colonna++;
			if ($colonna>$c) {
				$colonna=1;
				$row++;
			}
		}
	}
	public function swapText($dsc11) {
		// Recupero Test di tutte le input FIELD

		$fields = $dsc11->getFields();
		//showArray($fields);die();
		$prg=0;
		ksort($this->mappaValori);
		/*$testo = "";
		foreach ($this->mappaValori as $key => $value) {
			$testo.="($key)";
			foreach ($value as $key2 => $value2) {
				$testo.= "($key2)".$value2;
			}
			$testo .="<br>";
		}
		echo $testo;
		//die($testo);*/
		
		foreach ($fields as $key => $value) {
			$testo = $this->getText($value->getXposition(), $value->getYposition(), $value->getLength());
			$value->setText($testo);
			if ($testo=="") {
				$value->setVacum(True);
			}
			// Controllo se il carattere prima dell'inizio del campo è un attributo
			$xx= $value->getXposition();
			$yy= $value->getYposition();
			$yy--;
			if ($yy==0) {
				$xx--;
				$yy=$this->getResolutionCol();
			}
			if (isset($this->mappaValori[$xx][$yy]) && substr($this->mappaValori[$xx][$yy],0,3)=="ATR") {
				$value->setColour(substr($this->mappaValori[$xx][$yy],4,2));
				//echo "<br>UNSET :".$xx. " - ".$yy;
				//unset($this->mappaValori[$xx][$yy]);
			} else {
				// Se è un campo splittato riporto l'attributo dal suo capostipite
				if ($value->getFieldSplit()==True) {
					$origine = $value->getFieldSplitFirst();
					//die("ORIGINE:".$origine);
					$campo = $dsc11->getFields($origine);
					$value->setColour($campo->getColour());
				}
			}
		}
	}
	private function createTxtElement($dsc11) {
		// Scrivo tutte le etichette testo rimaneneti
		$currentAttribute="";
		//ksort($this->mappaValori);
		$prg=0;
		/*$testo = "";
		foreach ($this->mappaValori as $key => $value) {
			$testo.="($key)";
			foreach ($value as $key2 => $value2) {
				$testo.= "($key2)".$value2;
			}
			$testo .="<br>";
		}
		echo $testo;die();*/
		
		//showArray($this->mappaValori);
		$ih=0;
		foreach ($this->mappaValori as $key => $value) {
			$prg++;
			$nameField="TXT_".$prg;
			$field = new wi400AS400Field($nameField);
			$field->setXposition($key);
			$first = true;
			$colonna=0;
			$len=0;
			// Ordino l'array ---> Già fatto sopra
			ksort($value);
			$ih++;
			
			foreach ($value as $key2 => $value2) {
				//echo "CURRENT ATTRIBUTE: $value2 ->>>".$currentAttribute."<br>";
				//showArray($value);
				if (!isset($this->mappaValoriIO[$key][$key2])) {
					if (substr($value2,0,3)=="ATR") {
						$currentAttribute=substr($value2,4,2);
					}
					if ($first && substr($value2,0,3)=="ATR") {
						$field->setColour(substr($value2,4,2));
						//$currentAttribute=substr($value2,4,2);
						continue;
					}
					if ($first==True) {
						$colonna=$key2;
						$field->setYposition($key2);
						$field->setColour($currentAttribute);
						$first=False;
						// Se supero la lunghezza massima delle colonne aggiungo un BR
					}
					// Attributo di chiusura dovrebbe essere sempre 020 oppure non c'è dato a video
					$step = $key2-$colonna;
					if (substr($value2,0,3)=="ATR") {
						// Comunque l'attriuto è un carattere in più del testo
						if ($len>0) {
							//$len++;
							$field->setText($field->getText()." ");
						}
						$field->setLength($len);
						$dsc11->addField($nameField, $field);
						$prg++;
						$nameField="TXT_".$prg;
						$len=0;
						$field = new wi400AS400Field($nameField);
						//$field = new wi400AS400Field($nameField);
						if (substr($value2,0,3)=="ATR") {
							$currentAttribute=substr($value2,4,2);
						}
						//$field->setColour(substr($value2,4,2));
						$field->setColour($currentAttribute);
						$field->setXposition($key);
						$first=True;
						continue;
					}
					$colonna=$key2;
					$len++;
					//unset($this->mappaValori[$key][$key2]);
					$field->setText($field->getText().$value2);
				}
			}
			if ($ih>3) {
				//showArray($dsc11->getFields());
				//die("ON FKLDSAFJ");
			}
			$field->setLength($len);
			if ($field->getYposition()==0) $field->setYposition($key2);
			// Butto via tutti i campi a 0 lunghezza!
			$dsc11->addField($nameField, $field);
		}
	}
	public function splitField($dsc11) {
		$colonne=$this->getResolutionCol();
		// Dovrei avere solo campi di input ma + meglio controllare
		$myf = $dsc11->getFields();
		foreach ($myf as $key => $value) {
			if ($value->getIO()==True) {
				$inizio = $value->getYposition();
				$riga = $value->getXposition();
				$lunghezza = $value->getLength();
				if ($inizio+$lunghezza > ($colonne+1)) {
					$residuo=$lunghezza;
					$name = $value->getId();
					$prg2=0;
					$newcol=1;
					$prg2++;
					// Split della prima field
					$value->setFieldSplit(True);
					$value->setFieldSplitFirst($name);
					$newlunghezza = ($colonne-$inizio)+1;
					$residuo = $residuo - $newlunghezza;
					$value->setLength($newlunghezza);
					// Nuova Field a capo
					$idSplit = $name."_".$prg2;
					$value->setNextFieldSplit($idSplit);
					//$newField = clone $value;
					// Verifico se ci sta in una riga
					while ($residuo>0)  {
						$newField = clone $value;
						$newField->setXposition($riga+$prg2);
						$newField->setYposition($newcol);
						$newField->setId($idSplit);
						// @todo gestire split che parte da una colonna precisa
						if ($residuo <= $colonne) {
							$newlunghezza = $residuo;
							$newField->setFieldSplitLast(True);
							$newField->setNextFieldSplit("");
							$residuo=0;
						} else {
							$newlunghezza = $colonne;
							$residuo = $residuo - $colonne;
							//valued->setNextFieldSplit($idSplit);
							$inizio=1;
						}
						$newField->setLength($newlunghezza);
						$dsc11->addField($idSplit, $newField);
						$prg2++;
						$idSplit = $name."_".$prg2;
					} 
				}
			}
		}
	}
	public function getText($row, $colonna, $lunghezza) {
		//echo "<br> $row $colonna $lunghezza";
		$colonne=$this->getResolutionCol();
		$righe=$this->getResolutionRow();
		$testo="";
		$numero=1;
		for ($i=0;$i<1000;$i++) {
			if (isset($this->mappaValori[$row][$colonna])) {
				//echo "<br>PROVA:".$this->mappaValori[$row][$colonna];
				$valore = $this->mappaValori[$row][$colonna];
				if (substr($valore,0,4)=="ATR:") $valore = "";
				$testo .= $valore;
				//if (substr($valore,0,4)!="ATR:") {
				//	unset($this->mappaValori[$row][$colonna]);
				//}
				$numero++;
				$colonna++;
			} else {
				break;
			}
			// Se ho preso tutto il testo
			if ($numero > $lunghezza) {
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
		//static $key_colour;
		//if (!isset($key_colour)) {
		//	$key_colour = array_keys(wi400AS400Constant::colour);
		//}
		// Se + un attributo colore lo scrivo così come è con ATR
		if (in_array($carattere, $this->key_colour)) {
			//echo "<br>CARATTERE:".$this->curX."-".$this->curY."=".$carattere;
			$this->mappaValori[$this->curX][$this->curY]="ATR:".$carattere;
			$this->mappaAttributi[$this->curX][$this->curY]="ATR:".$carattere;
		} else {
			if ($carattere == '00') $carattere = '40';
			$this->mappaValori[$this->curX][$this->curY]=$this->_e2a(wi400AS400Func::hex2str($carattere));
			//echo "<br>INSERISCO CARATTERE:".$this->curX." - ".$this->curY;
		}
		//$this->curY = $this->curY + 1;
		// Verifico se salto RIGA
		if ($this->curY>=$colonne) {
			$this->curY=1;
			$this->curX++;
		} else {
			$this->curY = $this->curY + 1;
		}
	}
	public function insertCharUntil($carattere, $row, $colonna) {
		// Calcolo eventuale salto riga e Y
		// Ciclo per 3654, massimo numero di caratteri per schermo 27*132
		for ($i=0;$i<3654;$i++) {
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
				$field->setControl($this->AR[$this->i].$this->AR[$this->i+1]);
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