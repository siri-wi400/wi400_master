<?php
/**
 * 
 * @author luca
 * @version 1.1.0
 * @todo 
 * 		* eliminazione messaggio, dovrei riestrarlo e toglierlo dal log
 * 		* TESTING condizione AND/OR e INCLUDI ESCLUDI
 * 		* Segnalare che il messaggio è nuovo -> SE NON E' NOTIFICATO
 * 		* REPERIMENTO MESSAGGI LEGATI AD UNA AZIONE SPECIFICA, ESTRAGGO GIA' TUTTO????!!!!..	
 * 		* NUOVI Settings: Abilitazione Messaggi
 * 						  Abilitazione Notifica Messaggi
 * 						  Timeout Ricerca Nuovi Messaggi X utente
 * 						  Mail from di default per risposta a messaggi
 * 		* TEST * Invio Mail se reply TO con indirizzo e-mail
 * 		* Funzione per esplosione immediata messaggi
 * 		* Unica funzione per valutazione messaggi (eplosione multi utente)	
 * 		* FILTRO SOCIETA UTENTE
 * 		* CUSTOM FILTER, RICHIAMO FUNZIONE CHE MI VALIDA IL MESSAGGIO	
 *		* SIMULAZIONE DIVULGAZIONE MESSAGGIO PER CAPIRE A CHI ARRIVA 
 *
 *	@todo Contare il numero di messaggi che non posso simulare perchè i destinatori sono noti sol al login
 *	@todo Bloccare la divulgazione immediata se ho dei destinatari che sono noti solo al login	  				
 */
class wi400AnnounceMessage {

	private $logFile="ZMSGLOG";
	private $tesFile="ZMSGTES";
	private $dstFile="ZMSGDST";
	private $cttFile="ZMSGCTT";
	private $cthFile="ZMSGCTH";
	private $usrFile="ZMSGUSR";
	private $notFile="ZMSGNOT";
	private $ultimaExt;				
	private $user;
	private $newMessageArray = array();
	private $currentTimestamp;
	private $stmtUser = null;
	private $stmtTitolo = null;
	private $stmtHtml = null;
	private $stmtTes = null;
	private $stmtTesLog = null;
	private $stmtTxt = null;
	private $stmtNot = null;
	private $stmtLog = null;
	private $stmtAllegati = null;
	private $comb = null;
	private $groups = null;

	public function __construct($user=""){
		// Reperisco le informazioni sull'ultimo reperimento info utente
		if ($user=="") {
			$this->user = $_SESSION['user'];
			$this->getDatiUser($this->user);
		} else {
			$this->user = $user;
			$this->getDatiUser($user);
		} 
		$this->currentTimestamp = getDb2Timestamp();
		$this->ultimaExt = $this->getUltimaExt();
			
    }
    /**
     * @desc setUser: Setto l'utente per le ricerche dei messaggi
     * @param unknown $user
     */
	public function setUser($user) {
    	$this->user = $user;
    	//$this->dati = $this->getDatiUser($user);
    	$this->getDatiUser($user);
    	//echo "<br>$user".var_dump($this->dati);
    	// Ricerco la data di estrazione per L'utente
    	$this->ultimaExt = $this->getUltimaExt();
    }
    /**
     * @desc getDatiUser: reperisco le informazioni legate all'utente
     * @param unknown $user
     */
    private function getDatiUser($user) {
    	// Reperimento Gruppi
    	if ($user==$_SESSION['user']) {
    		$this->dati['GROUPS']=$_SESSION['WI400_GROUPS'];
    		if (isset($_SESSION['interlocutore'])) {
    			$this->dati['INTERLOCUTORE']=$_SESSION['interlocutore'];
    		} else {
    			$this->dati['INTERLOCUTORE']="";
    		}
    		$this->dati['LOCALE']=$_SESSION['locale'];
    	} else {
    		$dati = rtvUserInfo($user);
    		//echo "<br>PRIMA:".$dati['WI400_GROUPS'];
    		$this->dati['GROUPS']=explode ( ";", $dati["WI400_GROUPS"]);
    		$this->dati['INTERLOCUTORE']="";
    		$this->dati['LOCALE']="";
    		//echo "<br>GRUPPI $user:".showArray($this->dati['GROUPS']);
    	}
    }
    /**
     * @desc getUltimaExt: Reperisce l'ultima data di estrazione messaggi per l'utente 
     */
    public function getUltimaExt() {
    	global $db;
    	$ultimaExt = "";
    	if (!isset($this->stmtUser)) {
	    	$queryusr = "SELECT USREXT FROM ZMSGUSR WHERE USRUSR = ?";
	    	$this->stmtUser = $db->singlePrepare($queryusr);
    	}
    	$resultusr = $db->execute($this->stmtUser, array("USRUSR"=>$this->user));
    	$row = $db->fetch_array($this->stmtUser);
    	if ($row) {
    		$ultimaExt = $row['USREXT'];
    	} else {
    		$ultimaExt = getDb2Timestamp("*INZ");
    	}
    	return $ultimaExt;
    }
    /**
     * @desc getMessages: Reperisce i messaggi presenti
     * @param $tipo string: Tipo messaggio *HOME, *ACTION, *LOGOUT
     * @param $stati array: Stati da includere nella selezione, se non passati include tutti gli stati
     */
    public function getMessages($tipo=array(), $stati=array(), $azione=null, $id=null, $mess_scaduti=false, $area="") {
		global $db;
		$messagesId = array();
		$strstati= "";
		$strtipo = "";
		$periodo = "";
		$sarea = "";
		
		if (count($tipo)==0) $tipo[]="*HOME";
		$strtipo = implode("','",$tipo);
		
		if (count($stati)> 0) {
			$strstati = " AND B.LOGSTA IN('".implode("','",$stati)."')";
		}
		$strid = "";
		if (isset($id)) {
			$strid = " AND A.TESID = '$id' ";
		}
		$sqlazione = "";
		if (isset($azione)) {
			$sqlazione = " AND A.TESAZI='$azione'";
		}
		
		if (!$mess_scaduti) {
			$periodo = " AND A.TESPUB < '".$this->currentTimestamp."' AND A.TESSCA > '".$this->currentTimestamp."'";
		}
		
		if($area!="") {
			$sarea = " AND A.TESARE = '$area'";
		}
	
		// Carico tutti i messaggi che mi sono stati notificati e che non sono scaduti
		$query = "SELECT * FROM ZMSGTES A, ZMSGLOG B WHERE A.TESID=B.LOGID AND A.TESVIS IN('$strtipo') AND LOGUSR='".$this->user."' AND A.TESSTA='1'";
		$query .= $periodo.$strstati.$sqlazione.$strid.$sarea;  
		$query .= " ORDER BY A.TESPRY DESC, A.TMSINS DESC";
		
		$result = $db->query($query);
		while ($row = $db->fetch_array($result)) {
				// Deocodifico l'intestazione del messaggio
				$titolo = $this->getTitoloMessaggio($row['TESID']);
				$row['TITOLO']=$titolo;
				$messagesId[$row['TESID']]=$row;
		}
		return $messagesId;
    }
    /**
     * @desc simulaMessageId: Simula invio messaggio e ritorna destinatari trovati
     * @param string $id: ID Messaggio
     * @return array : Numero messaggi divulgati e utenti
     */
    public function simulaMessageId($id) {
		return $this->divulgaMessageId($id, True);    	
    }
    /**
     * @desc divulgaMessageId: Divulga immediatamente un messaggio
     * @param string $id: ID Messaggio
     * @param boolean $simula: Se viene effettuata solo una simulazione
     * @return array : Numero messaggi divulgati e utenti
     */
    public function divulgaMessageId($id, $simula=False) {
    	global $db, $users_table;
    	$destinatari = array();
    	$totaleDivulgati = 0;
    	$userDivulgati = array();
    	$stato = "";
    	
    	if ($simula!=True) {
    		$stato = " AND A.TESSTA='1'";
    	}
    	// Se non sono in simulazione non posso divulgare immediatamente messaggi con destinatari noti solo al login
    	// Carico tutti i messaggi che non sono ancora stati notificati e che non sono di tipo privato (X Singolo Utente, già nati come notificati)
    	$query = "SELECT * FROM ZMSGTES A, ZMSGDST B WHERE A.TESID=B.DSTID $stato AND B.DSTSTA='1' AND A.TESID = '".$id."'
    	AND A.TESSCA > '".$this->currentTimestamp."' ORDER BY B.DSTSEQ";
    	
		//echo $query;
		$result = $db->query($query);
		while ($row = $db->fetch_array($result)) {
			$destinatari[]=$row;
		}
		
		//echo "<pre>";
		//print_r($destinatari);
		//echo "</pre>";
		// Ciclo su tutti gli utenti di WI400 che non hanno già avuto il messaggio
		$sql = "select USER_NAME from " .$users_table." WHERE USER_NAME NOT IN (SELECT LOGUSR FROM ZMSGLOG WHERE LOGID='".$id."')";
		$result = $db->query($sql);
		while ($utenti = $db->fetch_array($result)) {
			//echo "<br>UTENTI:".$utenti['USER_NAME'];
			// Verifico se è un messaggio per l'utente ma non aggiorno il suo log
			$this->setUser($utenti['USER_NAME']);
			//echo var_dump($this->dati);
			$newMessageCount = $this->parseDestinatari($destinatari, false, $simula);
			if ($newMessageCount>0) {
				$totaleDivulgati++;
				$userDivulgati[]=$utenti['USER_NAME'];
				
				$query = "SELECT TESTNO, TESFMT FROM ZMSGTES WHERE TESID = '$id'";
				
				$mail_to = "";
				$result2 = $db->query($query);
				if ($ris = $db->fetch_array($result2)) {
					if($ris['TESTNO'] == "E-MAIL") {
						$dest_mail = getUserMail($utenti['USER_NAME']);
						
//						echo "destinatario: ".$dest_mail."<br/>";
						if($dest_mail) {
							
							$subject = $this->getTitoloMessaggio($id);
							$allegati = $this->getAllegati($id);
							
							foreach($allegati as $i => $valore) {
								$allegati[$i] = $valore['ATCATC'];
							}
							
							if($ris['TESFMT'] == "TXT") {
								$body = $this->getTxtContent($id);
							}else {
								$body = $this->getHtmlContent($id);
							}
							
							$isHtml = false;
							if(is_array($body)) {
								$body = implode("\n", $body);
							}else {
								$isHtml = true;
							}
						
							wi400invioEmail::invioEmail('', array($dest_mail), '', $subject, $body, $allegati, array(), $isHtml);
						}
					}
				}
			}
		}
		$ritorno = array("DIVULGATI"=>$totaleDivulgati, "UTENTI"=>$userDivulgati);
		return $ritorno;
    }
    /**
     * @desc getNewMessageAction: Verifico se c'è qualche messaggio legato all'azione che sto per eseguire
     */
    public function getNewMessageAction($action) {
    	global $db;
    	$destinatari = array();
    	// Carico tutti i messaggi che non sono ancora stati notificati e che non sono di tipo privato (X Singolo Utente, già nati come notificati)
    	//$query = "SELECT * FROM ZMSGTES A, ZMSGDST B WHERE A.TESID=B.DSTID AND A.TESSTA = '1' AND B.DSTSTA='1' AND A.TMSMOD > '".$this->ultimaExt."'
    	$query = "SELECT * FROM ZMSGTES A, ZMSGDST B WHERE A.TESID=B.DSTID AND A.TESSTA = '1' AND B.DSTSTA='1' 
    	AND A.TESSCA > '".$this->currentTimestamp."' AND A.TESPUB < '".$this->currentTimestamp."' AND A.TESVIS IN('*ACTION') AND A.TESAZI='$action' AND A.TESPRV<>'S' 
    	AND A.TESID NOT IN (SELECT LOGID FROM ZMSGLOG WHERE LOGID=A.TESID AND LOGUSR='".$this->user."') ORDER BY A.TESID, B.DSTSEQ";
    	///echo $query;
    	$result = $db->query($query);
    	while ($row = $db->fetch_array($result)) {
    		$destinatari[]=$row;
    	}
    	$newMessageCount = $this->parseDestinatari($destinatari, false);
    	return $newMessageCount;
    }
    /**
     * @desc getNewMessageHome: Reperisco se per l'utente ci sono nuovi messaggi da notificare sulla HOME
     */
    public function getNewMessageHome() {
    	global $db;
    	$destinatari = array();
    	// Carico tutti i messaggi che non sono ancora stati notificati e che non sono di tipo privato (X Singolo Utente, già nati come notificati)
    	$query = "SELECT * FROM ZMSGTES A, ZMSGDST B WHERE A.TESID=B.DSTID AND A.TESSTA = '1' AND B.DSTSTA='1' AND A.TMSMOD > '".$this->ultimaExt."' 
    	AND A.TESSCA > '".$this->currentTimestamp."' AND A.TESVIS IN('*HOME') AND A.TESPRV<>'S' 
    	AND A.TESID NOT IN (SELECT LOGID FROM ZMSGLOG WHERE LOGID=A.TESID AND LOGUSR='".$this->user."') ORDER BY A.TESID, B.DSTSEQ";
    	//echo $query;
    	$result = $db->query($query);
    	while ($row = $db->fetch_array($result)) {
    		$destinatari[]=$row;
    	}
    	$newMessageCount = $this->parseDestinatari($destinatari);
    	return $newMessageCount;
    }
    /**
     * @desc parseDestinari: verifica se l'utente è destinatario del messaggio
     * @param unknown $result
     * @param boolean $updateLog: Se salvare il log di estrazione false per single id
     * @param boolean $simula: True se si tratta di una simulazoine per capire a chi va il messaggio
     */
    private function parseDestinatari($arrayDestinatari, $updateLog = True, $simula=False) {
    	$oldId="";
    	$forMe= False;
    	$newMessageCount = 0;
    	$oldcond = "";
    	$this->comb ="";
    	$string = "";
    	foreach ($arrayDestinatari as $key => $row) {
    		$currentId =$row['TESID'];
    		if ($currentId!=$oldId && $oldId!="") {
    			$forMe = eval("if (($string)){"."return true;"."}");
    			//echo "<br>TEST:".$string;
    			// Segno che il messaggio mi interessa sul log
    			if ($forMe==True) {
    				$this->updateLogMessage($oldId);
    				$newMessageCount++;
    				//echo "<br>STRINGA:(".$string.")";
    			}
    			$forMe = False;
    			$oldcond ="";
    			$this->comb ="";
    			$string = "";
    		}
    		$oldId=$currentId;
    		// Selezione dei messaggi in base al destinatario di tipo utente
    		//if ($row['DSTTYP']=="*USER" && ($row['DSTDST']==$this->user || $row['DSTDST']=="*ALL")) {
    		if ($row['DSTTYP']=="*USER") {
    			//echo "<br>Il messaggio è anche per ME!!!!(*USER)";
    			$cond = "0";
    			if ($row['DSTDST']==$this->user || $row['DSTDST']=="*ALL") {
    				$cond = "1";
    				//$forMe = True;
    			}
    			$string = $this->getCompareString($cond, $row, $string, $oldcond);
    			$oldcond=$this->comb;
    		}
    		// Selezione dei messaggi in base al gruppo
    		//if ($row['DSTTYP']=="*GRUPPO" && (in_array($row['DSTDST'], $_SESSION['WI400_GROUPS']) || $row['DSTDST']=="*ALL")) {
    		if ($row['DSTTYP']=="*GRUPPO") {
    			//echo "<br>Valuto il gruppo!!".$this->user.var_dump($this->dati['GROUPS']);
    			$cond="0";
    			//print_r($this->dati);
    			if (in_array($row['DSTDST'], $this->dati['GROUPS']) || $row['DSTDST']=="*ALL") {
    				//echo "<br>Il messaggio è anche per ME!!!! (*GRUPPO)";
    				$cond = "1";
    				//$forMe = True;
    			}
    			$string = $this->getCompareString($cond, $row, $string, $oldcond);
    			$oldcond=$this->comb;
    		}
    		// Tipo interlocutore
    		if ($row['DSTTYP']=="*INT") {
    			//echo "<br>Valuto il gruppo!!".$this->user.var_dump($this->dati['GROUPS']);
    			$cond="0";
    			//print_r($this->dati);
    		    if ($row['DSTDST']==$this->dati['INTERLOCUTORE'] || $row['DSTDST']=="*ALL") {
    				$cond = "1";
    				//$forMe = True;
    			}
    			$string = $this->getCompareString($cond, $row, $string, $oldcond);
    			$oldcond=$this->comb;
    		}
    		// Locale collegato a utente
    		if ($row['DSTTYP']=="*ENTE") {
    			//echo "<br>Valuto il gruppo!!".$this->user.var_dump($this->dati['GROUPS']);
    			$cond="0";
    			//print_r($this->dati);
    			if ($row['DSTDST']==$this->dati['LOCALE'] || $row['DSTDST']=="*ALL") {
    				$cond = "1";
    				//$forMe = True;
    			}
    			$string = $this->getCompareString($cond, $row, $string, $oldcond);
    			$oldcond=$this->comb;
    		}
    	}
    	// Scrivo un eventuale ultimo record
    	if ($string!="") {
    		$forMe = eval("if (($string)){"."return true;"."}");
    	}
    	// Contatore di messaggi
    	if ($forMe==True) {
    		$newMessageCount++;
    	}
    	//echo "<br>TEST2:".$string;
    	if ($forMe==True && $simula == False) {
    		//echo "<br>TEST:".$string;
    		$this->updateLogMessage($oldId);
    	}
    	// Aggiorno il log di estrazione informazioni per l'utente solo se non sto divulgando un singolo ID
    	if ($updateLog==True && $simula==False) {
    		$this->updateUsrLog();
    	}
    	if ($newMessageCount>0 && $simula==False) {
    		$this->notificaMsg();
    	}
    	return $newMessageCount;
    }
    public function getCompareString($cond, $row, $string, $oldcond) {
    	$not="";
    	
    	if ($row['DSTIOE'] =='E') $not = "!";
    	$comb = " && ";
    	if ($row['DSTOOA'] =='OR') $comb = " || ";
    	if ($comb!=$oldcond) {
    		if ($oldcond=="") {
    			$string .= $not.$cond;
    		} elseif($oldcond!="" && $row['DSTOOA']=='OR') {
    			$string .= ")".$comb."(".$not.$cond;
    		} else {
    			$string .= $comb.$not.$cond;
    		}
    	} else {
    		$string .= $comb.$not.$cond;
    	}
    	$this->comb = $comb;
    	return $string;
    }
    /**
     * *desc getTitoloMessaggio: Reperisce il titolo del messaggio
     * @param string $id: Id del messaggio per reperimento titolo
     */
    public function getTitoloMessaggio($id) {
    	global $db;
    	if (!isset($this->stmtTitolo)) {
    		$querytitolo = "SELECT CTTTXT FROM ZMSGCTT WHERE CTTID = ? AND CTTSTA='1' AND CTTRIG=?";
    		$this->stmtTitolo = $db->singlePrepare($querytitolo);
    	}
    	$resulttitolo = $db->execute($this->stmtTitolo, array("CTTID"=>$id, "CTTRIG"=>0));
    	$row = $db->fetch_array($this->stmtTitolo);
    	if (isset($row['CTTTXT'])) {
    		return $row['CTTTXT'];
    	} else {
    		return "";
    	}
    }
    /**
     * *desc getAllegati: Reperisce allegati presenti sul messaggio
     * @param string $id: Id del messaggio per reperimento allegati
     */
    public function getAllegati($id) {
    	global $db;
    	$allegati = array();
    	if (!isset($this->stmtAllegati)) {
    		$queryallegati = "SELECT * FROM ZMSGATC WHERE ATCID = ? AND ATCSTA='1'";
    		$this->stmtAllegati = $db->prepareStatement($queryallegati);
    	}
    	$resultallegati = $db->execute($this->stmtAllegati, array("ATCID"=>$id));
    	
    	while ($row = $db->fetch_array($this->stmtAllegati)) {
			$allegati[]= $row;
    	}
    	return $allegati;
    }
    /**
     * *desc getHtmlContent: Reperisce HTML messaggio
     * @param string $id: Id del messaggio per reperimento HTML
     */
    public function getHtmlContent($id) {
    	global $db;
    	if (!isset($this->stmtHtml)) {
    		$queryhtml = "SELECT CTHHTM FROM ZMSGCTH WHERE CTHID = ? AND CTHSTA='1'";
    		$this->stmtHtml = $db->singlePrepare($queryhtml);
    	}
    	$resulttitolo = $db->execute($this->stmtHtml, array("CTHID"=>$id));
    	$row = $db->fetch_array($this->stmtHtml);
    	if (isset($row['CTHHTM'])) {
    		return $row['CTHHTM'];
    	} else {
    		return "";
    	}
    }
    /**
     * *desc getTxtContent: Reperisce il messaggio di tipo testo
     * @param string $id: Id del messaggio per reperimento testo
     */
    public function getTxtContent($id) {
    	global $db;
    	$text = array();
    	if (!isset($this->stmtTxt)) {
    		$querytxt = "SELECT CTTTXT FROM ZMSGCTT WHERE CTTID = ? AND CTTRIG>0";
    		$this->stmtTxt = $db->singlePrepare($querytxt);
    	}
    	$resulttitolo = $db->execute($this->stmtTxt, array("CTTID"=>$id));
    	while ($row = $db->fetch_array($this->stmtTxt)) {
    		//$valore_riga = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $row['CTTTXT']);
			$valore_riga = str_replace(" ", "&nbsp;", $row['CTTTXT']);
			$text[]= $valore_riga;
    	}
    	return $text;
    }	
    
	public function updateUsrLog() {
		global $db;
    	if (!isset($this->stmtUser)) {
	    	$queryusr = "SELECT USREXT FROM ZMSGUSR WHERE USRUSR = ?";
	    	$this->stmtUser = $db->singlePrepare($queryusr);
    	}
    	$resultusr = $db->execute($this->stmtUser, array("USRUSR"=>$this->user));
    	$row = $db->fetch_array($this->stmtUser);
		if ($row) {
			$keyUpdt = array("USRUSR" => $this->user);
			$fieldsValue = array('USREXT' => $this->currentTimestamp, 'TMSMOD' =>$this->currentTimestamp);
			$stmt_updt = $db->prepare("UPDATE", "ZMSGUSR", $keyUpdt, array_keys($fieldsValue));
			$res_updt = $db->execute($stmt_updt, $fieldsValue);
		} else {
			$usrFile=$this->usrFile;
			$fieldUsr = getDS($usrFile);
			$stmtUsr = $db->prepare("INSERT", $usrFile, null, array_keys($fieldUsr));
			// Scrittura Testata
			$fieldUsr['USRUSR']= $this->user;
			$fieldUsr['USREXT']= $this->currentTimestamp;
			$fieldUsr['TMSMOD']= $this->currentTimestamp;
			$fieldUsr['TMSINS']= $this->currentTimestamp;
			$fieldUsr['USRMOD']= $this->user;
			$fieldUsr['USRINS']= $this->user;
			$result = $db->execute($stmtUsr, $fieldUsr);
		}
	}
	public function getTestata($id) {
		global $db;
		if (!isset($this->stmtTes)) {
			$querytes = "SELECT * FROM ZMSGTES WHERE TESID = ?";
			$this->stmtTes = $db->singlePrepare($querytes);
		}
		$resultusr = $db->execute($this->stmtTes, array("TESID"=>$id));
		return $db->fetch_array($this->stmtTes);
	}
	public function getTestataWithLog($id) {
		global $db;
		if (!isset($this->stmtTesLog)) {
			$querytes = "SELECT * FROM ZMSGTES, ZMSGLOG WHERE TESID=LOGID AND TESID = ? AND LOGUSR= ?";
			$this->stmtTesLog = $db->singlePrepare($querytes);
		}
		$resultusr = $db->execute($this->stmtTesLog, array("TESID"=>$id, "LOGUSR" => $_SESSION['user']));
		return $db->fetch_array($this->stmtTesLog);
	}
	public function getExtraParm($id) {
		global $db;
		$righe = array();
		$querytes = "SELECT * FROM ZMSGPRM WHERE PRMID = '$id'";
		$stmt = $db->query($querytes);

		while($row = $db->fetch_array($stmt)) {
			$righe[$row['PRMPRM']] = $row['PRMVAL'];
		}
		return $righe;
	}
	public function notificaMsg() {
		global $db;
		if (!isset($this->stmtNot)) {
			$querynot = "SELECT NOTUSR FROM ZMSGNOT WHERE NOTUSR = ?";
			$this->stmtNot = $db->singlePrepare($querynot);
		}
		$resultusr = $db->execute($this->stmtNot, array("NOTUSR"=>$this->user));
		$row = $db->fetch_array($this->stmtNot);
		if ($row) {
			$keyUpdt = array("NOTUSR" => $this->user);
			$fieldsValue = array('NOTTIM' => $this->currentTimestamp, 'NOTSTA' =>"1");
			$stmt_updt = $db->prepare("UPDATE", "ZMSGNOT", $keyUpdt, array_keys($fieldsValue));
			$res_updt = $db->execute($stmt_updt, $fieldsValue);
		} else {
			$usrFile=$this->notFile;
			$fieldUsr = getDS($usrFile);
			$stmtUsr = $db->prepare("INSERT", $usrFile, null, array_keys($fieldUsr));
			// Scrittura Testata
			$fieldUsr['NOTUSR']= $this->user;
			$fieldUsr['NOTTIM']= $this->currentTimestamp;
			$fieldUsr['NOTSTA']= "1";
			$result = $db->execute($stmtUsr, $fieldUsr);
		}
	}
	/**
	 * @desc updateLogStatus: Aggiornamento dei vari stati del messaggio
	 * @param string $id: Id del messaggio da aggiornare sul log
	 */
	public function updateLogStatus($id) {
		// lato client o fare fare alla classe con metodo statico 
	}
	/**
	 * @desc updateLogNotifica: Aggiorno log con notifica messaggio letto
	 * @param string $id: Id del messaggio da aggiornare sul log
	 */
	public function updateLogNotifica($id) {
		global $db;		
		// lato client o fare fare alla classe con metodo statico
		$keyUpdt = array("LOGID"=> $id, "LOGUSR" => $this->user);
		$fieldsValue = array('LOGNOT' => $this->currentTimestamp, 'TMSMOD' =>$this->currentTimestamp);
		$stmt_updt = $db->prepare("UPDATE", "ZMSGLOG", $keyUpdt, array_keys($fieldsValue));
		$res_updt = $db->execute($stmt_updt, $fieldsValue);
		$this->updateTotaliMessaggio($id, "NOTIFICA");
		
		$testata = $this->getTestata($id);
		if($testata['TESCLE'] == 'N' && $testata['TESRPY'] != 'S') {
			$this->updateLogLetto($id);
		}
	}
	/**
	 * @desc updateLogLetto: Aggiorno log con lettura messaggio
	 * @param string $id: Id del messaggio da aggiornare sul log
	 */
	public function updateLogLetto($id) {
		global $db;
		// lato client o fare fare alla classe con metodo statico
		$keyUpdt = array("LOGID"=> $id, "LOGUSR" => $this->user);
		$fieldsValue = array('LOGLET' => $this->currentTimestamp, 'TMSMOD' =>$this->currentTimestamp);
		// Verifico se posso cambiarlo di stato per segnare che non lo voglio più fare comparire in lista
		$row = $this->getTestata($id);
		if ($row['TESEVR']!="S") {
			$fieldsValue['LOGSTA']="2";
		}
		$stmt_updt = $db->prepare("UPDATE", "ZMSGLOG", $keyUpdt, array_keys($fieldsValue));
		$res_updt = $db->execute($stmt_updt, $fieldsValue);
		// Aggiorno contatore globale dei messaggi Letti sull'ID Principale.
		$this->updateTotaliMessaggio($id, "LETTO");
	}
	/**
	 * @desc updateLogReply: Aggiorno log con risposta a messaggio
	 * @param string $id: Id del messaggio da aggiornare sul log
	 */
	public function updateLogRisposta($id, $risposta) {
		global $db;		
		// lato client o fare fare alla classe con metodo statico
		$tes = $this->getTestata($id);
		
		// Se il messaggio prevede risposte
		if ($tes['TESRPY']=="S") {
			$keyUpdt = array("LOGID"=> $id, "LOGUSR" => $this->user);
			$fieldsValue = array("LOGRIS"=>"S" ,"LOGRPT"=>$risposta, 'LOGLET' => $this->currentTimestamp, 'TMSMOD' =>$this->currentTimestamp);
			$stmt_updt = $db->prepare("UPDATE", "ZMSGLOG", $keyUpdt, array_keys($fieldsValue));
			$res_updt = $db->execute($stmt_updt, $fieldsValue);
			// Verifico se devo spedire una mail
			if ($tes["TESTO"]!=""){
				$succ = wi400invioEmail::invioEmail("", array($tes['TESTO']), array(), "RIPOSTA A MESSAGGIO ".$this->getTitoloMessaggio($id), $risposta);
			}
			$this->updateTotaliMessaggio($id, "RISPOSTA");
		}
	}
	/**
	 * @desc updateTotaliMessaggio: Aggiorna i totali della testata del messaggio
	 * @param string $id: Id del messaggio da aggiornare sul log
	 */
	public function updateTotaliMessaggio($id, $what) {
		global $db;
		// lato client o fare fare alla classe con metodo statico
		switch ($what) {
			case "LETTO":
				$cosa="TESTOL";
				break;
			case "NOTIFICA":
				$cosa="TESTOV";
				break;
			case "RISPOSTA":
				$cosa="TESTOR";
				break;
			default:
				return false;
				break;
		}
		$queryupdate = "UPDATE ZMSGTES SET $cosa=$cosa+1 WHERE TESID='$id'";
		$db->query($queryupdate);
	}
	public function updateLogMessage($id) {
		global $db;
		if (!isset($this->stmtLog)) {
			$querylog = "SELECT LOGID FROM ZMSGLOG WHERE LOGUSR = ? AND LOGID=?";
			$this->stmtLog = $db->singlePrepare($querylog);
		}
		// Verifico se esiste
		$resultlog = $db->execute($this->stmtLog, array("LOGUSR"=>$this->user, "LOGID"=>$id));
		$row = $db->fetch_array($this->stmtLog);
		if (isset($row)) {
			$logFile=$this->logFile;
			$fieldLog = getDS($logFile);
			$timeInz = getDb2Timestamp("*INZ");
			$stmtLog = $db->prepare("INSERT", $logFile, null, array_keys($fieldLog));
			// Scrittura Testata
			$fieldLog['LOGUSR']= $this->user;
			$fieldLog['LOGID']= $id;
			$fieldLog['LOGSTA']= "1";
			$fieldLog['LOGRIS']= "";
			$fieldLog['LOGRPT']= "";
			$fieldLog['LOGNOT'] = $timeInz;
			$fieldLog['LOGLET'] = $timeInz;
			$fieldLog['LOGRPY'] = $timeInz;
			$fieldLog['TMSMOD']= $this->currentTimestamp;
			$fieldLog['TMSINS']= $this->currentTimestamp;
			$fieldLog['USRMOD']= $this->user;
			$fieldLog['USRINS']= $this->user;
			$result = $db->execute($stmtLog, $fieldLog);
		}
	}
	/**
	 * getMessageId: Recupero ID progressivo per Messaggio
	 * @return string $id:Nuovo numeratore Messaggi
	 */
	public function getMessageId() {
		global $db;
		
		$id = "MSG_".getSysSequence("MESSAGES");
		return $id;
	}
}
/**
 * 
 * @author luca
 *
 */
class wi400AnnounceMessageSet {
	private $logFile="ZMSGLOG";
	private $tesFile="ZMSGTES";
	private $dstFile="ZMSGDST";
	private $cttFile="ZMSGCTT";
	private $cthFile="ZMSGCTH";
	private $atcFile="ZMSGATC";
	private $usrFile="ZMSGUSR";
	private $notFile="ZMSGNOT";
	private $prmFile="ZMSGPRM";
	private $destinatari = array();
	private $allegati = array();
	private $extraParm = array();
	private $id="";
	private $gruppo=""; // Vedi Gruppi
	private $titolo="";
	private $formato="TXT"; //TXT o HTML
	private $check_formato = array("TXT", "HTML");
	private $tipo="INFO"; // INFO WARNING ERROR
	private $check_tipo = array("INFO", "WARNING", "ERROR", "PRODOTTO", "SEGRETERIA");
	private $tipoNotifica="*NONE"; // E-MAIL SMS
	private $check_tipoNotifica = array("*NONE", "E-MAIL", "SMS");
	private $confermaLettura='N'; // S N
	private $check_son = array("S", "N");
	private $richiedeRisposta='N'; // S N
	private $tipoRisposta=""; // TXT o SI-NO
	private $check_tipoRisposta = array("", "TXT", "SI-NO");
	private $inviaRispostaA=""; // email
	private $tipoVisualizzazione="*HOME"; // *HOME *ACTION
	private $check_tipoVisualizzazione = array("*HOME", "*ACTION");
	private $azione="";
	private $divulgazione="*IMMED"; // *IMMED *LOGIN
	private $check_divulgazione = array("*IMMED", "*LOGIN");
	private $dataPubblicazione;
	private $dataScadenza;
	private $priorita="1";
	private $sempreVisibile="N"; // S N
	private $contenuto="";
	private $html="";
	private $area="";
	private $messaggioPrivato="N";
	private $check_andor = array("AND", "OR");
	private $check_ioe = array("I", "E");
	private $check_tipo_dest = array("*USER", "*GRUPPO", "*INT", "*ENTE");
	
	/**
	 * @return the $messaggioPrivato
	 */
	public function getMessaggioPrivato() {
		return $this->messaggioPrivato;
	}

	/**
	 * @param field_type $messaggioPrivato
	 */
	public function setMessaggioPrivato($messaggioPrivato) {
		$this->messaggioPrivato = $messaggioPrivato;
	}

	/**
	 * @return the $html
	 */
	public function getHtml() {
		return $this->html;
	}

	/**
	 * @param field_type $html
	 */
	public function setHtml($html) {
		$this->html = $html;
	}

	/**
	 * @return the $contenuto
	 */
	public function getContenuto() {
		return $this->contenuto;
	}
	/**
	 * set Extra Parm
	 */
	public function setExtraParm($parm, $valore) {
		$this->extraParm[$parm]=$valore;
	}
	/**
	 * get Extra Parm
	 */
	public function getExtraParm($key="") {
		if ($key!="") {
			return $this->extraParm[$key];
		} else {
			return $this->extraParm;
		}
	}
	/**
	 * @param field_type $contenuto
	 */
	public function setContenuto($contenuto) {
		if (strlen(trim($contenuto))>80) {
			throw new exception("Lunghezza riga contenuto maggiore di 80 caratteri", 8);
		}
		$this->contenuto[] = $contenuto;
	}

	/**
	 * @return the $destinatari
	 */
	public function getDestinatari() {
		return $this->destinatari;
	}

	/**
	 * @return the $id
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return the $gruppo
	 */
	public function getGruppo() {
		return $this->gruppo;
	}

	/**
	 * @return the $titolo
	 */
	public function getTitolo() {
		return $this->titolo;
	}

	/**
	 * @return the $formato
	 */
	public function getFormato() {
		return $this->formato;
	}

	/**
	 * @return the $tipo
	 */
	public function getTipo() {
		return $this->tipo;
	}

	/**
	 * @return the $tipoNotifica
	 */
	public function getTipoNotifica() {
		return $this->tipoNotifica;
	}

	/**
	 * @return the $confermaLettura
	 */
	public function getConfermaLettura() {
		return $this->confermaLettura;
	}

	/**
	 * @return the $richiedeRisposta
	 */
	public function getRichiedeRisposta() {
		return $this->richiedeRisposta;
	}

	/**
	 * @return the $tipoRisposta
	 */
	public function getTipoRisposta() {
		return $this->tipoRisposta;
	}

	/**
	 * @return the $inviaRispostaA
	 */
	public function getInviaRispostaA() {
		return $this->inviaRispostaA;
	}

	/**
	 * @return the $tipoVisualizzazione
	 */
	public function getTipoVisualizzazione() {
		return $this->tipoVisualizzazione;
	}

	/**
	 * @return the $azione
	 */
	public function getAzione() {
		return $this->azione;
	}

	/**
	 * @return the $divulgazione
	 */
	public function getDivulgazione() {
		return $this->divulgazione;
	}

	/**
	 * @return the $dataPubblicazione
	 */
	public function getDataPubblicazione() {
		return $this->dataPubblicazione;
	}

	/**
	 * @return the $dataScadenza
	 */
	public function getDataScadenza() {
		return $this->dataScadenza;
	}

	/**
	 * @return the $priorita
	 */
	public function getPriorita() {
		return $this->priorita;
	}

	/**
	 * @return the $sempreVisibile
	 */
	public function getSempreVisibile() {
		return $this->sempreVisibile;
	}

	/**
	 * @desc setDestinatario: Inserimento di un destinatario
	 * @param integer $seq: Sequenza del destinatario
	 * @param string $tipo: Dipo Destinatario *USER *GRUPPO *INT *ENT
	 * @param string $destinatario: Codice del destinatario
	 * @param string $ioe: Includi o Escludi I o E
	 * @param string $andor: Se And o Or A o O
	 */
	public function setDestinatario($seq, $tipo, $destinatario, $ioe="I", $andor='OR') {
		if (!in_array($tipo, $this->check_tipo_dest)) {
			throw new exception("Tipo destinatario deve essere:".implode(" ", $this->check_tipo_dest), 10);
		}
		if (!in_array($ioe, $this->check_ioe)) {
			throw new exception("Includi/Escludi dev essere:".implode(" ", $this->check_ioe), 10);
		}
		if (!in_array($andor, $this->check_andor)) {
			throw new exception("And/Or dev essere:".implode(" ", $this->check_andor), 10);
		}
		$this->destinatari[$seq] = array("TIPO"=>$tipo, "DESTINATARIO"=>$destinatario, "IOE"=>$ioe, "ANDOR" => $andor);
	}
	/**
	 * 
	 * @param array: $allegati File allegati CHIAVE => percorso File
	 */
	public function setAllegati($allegati) {
		$this->allegati = $allegati;
	}
	
	/**
	 * 
	 * @param string $key: Chiave Allegato
	 * @param string $allegato: Percorso dell'allegato su IBM i
	 */
	public function setAllegato($allegato) {
		$this->allegati[] = $allegato;
	}
	public function setArea($area) {
		$this->area = $area;
	}
	public function getArea() {
		return $this->area;
	}
	/**
	 * @return the $allegati
	 */
	public function getAllegati() {
		return $this->allegati;
	}
	
	public function saveAllegato($file) {
//	public static function saveAllegato($id, $file) {
		global $db;
	
		$timestamp = date('d/m/Y H:i:s');
	
		$fieldTes = getDs("ZMSGATC");
	
		$stmtTes = $db->prepare("INSERT", "ZMSGATC", null, array_keys($fieldTes));
	
		$fieldTes['ATCID'] = $this->id;
		$fieldTes['ATCLNG'] = 'ITA';
		$fieldTes['ATCATC'] = $file;
		$fieldTes['ATCSTA'] = '1';
		$fieldTes['USRMOD'] = $_SESSION['user'];
		$fieldTes['TMSMOD'] = getDb2Timestamp($timestamp);
		$fieldTes['USRINS'] = $_SESSION['user'];
		$fieldTes['TMSINS'] = getDb2Timestamp($timestamp);
	
		$result = $db->execute($stmtTes, $fieldTes);
		
		return $result;
	}
	
	public function deleteAllegato($file) {
		global $db;
		global $data_path;
		
		$sql = "DELETE FROM ZMSGATC WHERE ATCATC='{$file}'";
		$rs = $db->query($sql);
		
		if($rs) {
			$folder = $data_path."messages/".$this->id;
				
			$nome = explode("/", $file);
			$nome = $nome[count($nome)-1];
				
			$file_path = $folder."/".$nome;
			$result = unlink($file_path);
				
			// 0 => . 1 => .. quindi 2 se vuota
			if(count(scandir($folder)) == 2) {
				rmdir($folder);
			}
			
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * @param string $id
	 */
	public function setId($id) {
		$this->id = $id;
	}

	/**
	 * @param field_type $gruppo
	 */
	public function setGruppo($gruppo) {
		$this->gruppo = $gruppo;
	}

	/**
	 * @desc setTitolo: Setta il titolo del messaggio
	 * @param field_type $titolo
	 */
	public function setTitolo($titolo) {
		$this->titolo = $titolo;
	}

	/**
	 * @desc setFormato: Setta il formato del messaggio TXT o HTML
	 * @param field_type $formato Formato del messaggio TXT o HTML
	 */
	public function setFormato($formato) {
		$this->formato = $formato;
	}

	/**
	 * @desc setTipo: Setta il tipo di messaggio
	 * @param field_type $tipo: ERROR, WARNING, INFO
	 */
	public function setTipo($tipo) {
		$this->tipo = $tipo;
	}

	/**
	 * @desc setTipoNotifica: imposta il tipo di notifica che deve arrivare all'utente se prevista
	 * @param field_type $tipoNotifica: E-MAIL o SMS o vuoto
	 */
	public function setTipoNotifica($tipoNotifica) {
		$this->tipoNotifica = $tipoNotifica;
	}

	/**
	 * @desc setConfermaLettura: Setta se l'utente devo confermare l'avvenuta lettura
	 * @param field_type $confermaLettura S o N
	 */
	public function setConfermaLettura($confermaLettura) {
		$this->confermaLettura = $confermaLettura;
	}

	/**
	 * @desc setRichiedeRisposta: Se il messaggio richiede una risposta da parte dell'utente
	 * @param field_type $richiedeRisposta S o N
	 */
	public function setRichiedeRisposta($richiedeRisposta) {
		$this->richiedeRisposta = $richiedeRisposta;
	}

	/**
	 * @desc setTipoRisposta: Se previsto il tipo di risposta che deve dare l'utente
	 * @param field_type $tipoRisposta TXT o SI-NO
	 */
	public function setTipoRisposta($tipoRisposta) {
		$this->tipoRisposta = $tipoRisposta;
	}

	/**
	 * @desc setInviaRispostaA: e-mail a cui inviare la risposta
	 * @param field_type $inviaRispostaA e-mail a cui inviare la risposta del messaggio
	 */
	public function setInviaRispostaA($inviaRispostaA) {
		$this->inviaRispostaA = $inviaRispostaA;
	}

	/**
	 * @desc setTipoVisualizzazione: Se visualizzare il messaggio in HOME oppure legato ad una azione
	 * @param field_type $tipoVisualizzazione *HOME *ACTION
	 */
	public function setTipoVisualizzazione($tipoVisualizzazione) {
		$this->tipoVisualizzazione = $tipoVisualizzazione;
	}

	/**
	 * @param field_type $azione
	 */
	public function setAzione($azione) {
		$this->azione = $azione;
	}

	/**
	 * @desc setDivulgazione: Tipo divulgazione messaggio, *IMMED immediata oppure al momento del LOGIN
	 * @param field_type $divulgazione *IMMED *LOGIN
	 */
	public function setDivulgazione($divulgazione) {
		$this->divulgazione = $divulgazione;
	}

	/**
	 * @param field_type $dataPubblicazione GG/MM/AAAA
	 */
	public function setDataPubblicazione($dataPubblicazione) {
		$this->dataPubblicazione = $dataPubblicazione;
	}

	/**
	 * @param field_type $dataScadenza GG/MM/AAAA
	 */
	public function setDataScadenza($dataScadenza) {
		$this->dataScadenza = $dataScadenza;
	}

	/**
	 * @param field_type $priorita
	 */
	public function setPriorita($priorita) {
		$this->priorita = $priorita;
	}

	/**
	 * @desc setSempreVisibile: Il messaggio sarà sempre visibile anche dopo la conferma di lettura
	 * @param field_type $sempreVisibile S o N
	 */
	public function setSempreVisibile($sempreVisibile) {
		$this->sempreVisibile = $sempreVisibile;
	}

	/**
	 * @desc Costrutture della classe, passo come parametro il nuovo ID del messaggio da reperire
	 * @param unknown $id
	 */
	public function __construct($id) {
		$this->id = $id;
	}
	private function writeTestata() {
		global $db;
		$fieldTes = getDs($this->tesFile);
		$timestamp = date('d/m/Y H:i:s');
		$fieldTes['TESID'] = $this->id;
		$fieldTes['TESGRP'] = $this->gruppo;
		$fieldTes['TESFMT'] = $this->formato;
		$fieldTes['TESTYP'] = $this->tipo;
		$fieldTes['TESTNO'] = $this->tipoNotifica;
		$fieldTes['TESCLE'] = $this->confermaLettura;
		$fieldTes['TESRPY'] = $this->richiedeRisposta;
		$fieldTes['TESVIS'] = $this->tipoVisualizzazione;
		$fieldTes['TESPRV'] = $this->messaggioPrivato;
		$fieldTes['TESTO'] = $this->inviaRispostaA;
		$fieldTes['TESRPYT'] = $this->tipoRisposta;
		$fieldTes['TESAZI'] = $this->azione;
		$fieldTes['TESDIV'] =  $this->divulgazione;
		$fieldTes['TESPUB'] = getDb2Timestamp($this->dataPubblicazione);
		$fieldTes['TESSCA'] = getDb2Timestamp($this->dataScadenza);
		$fieldTes['TESPRY'] = $this->priorita;
		$fieldTes['TESARE'] = $this->area;
		$fieldTes['TESEVR'] = $this->sempreVisibile;
		$fieldTes['USRMOD'] = $_SESSION['user'];
		$fieldTes['TMSMOD'] = getDb2Timestamp($timestamp);
		$fieldTes['TESUSR'] = $_SESSION['user'];
		$fieldTes['TESSTA'] = '0';
		$fieldTes['TESTOL'] = 0;
		$fieldTes['TESTOV'] = 0;
		$fieldTes['TESTOR'] = 0;
		$fieldTes['USRINS'] = $_SESSION['user'];
		$fieldTes['TMSINS'] = getDb2Timestamp($timestamp);
		
		$stmtTes = $db->prepare("INSERT", $this->tesFile, null, array_keys($fieldTes));
		$result = $db->execute($stmtTes, $fieldTes);
		// Scrittura del titolo, prima RIGA Messaggio
		$sql_header = "INSERT INTO $this->cttFile VALUES('{$this->id}', 'ITA', 0, '{$this->titolo}', '1', '{$_SESSION['user']}', '".getDb2Timestamp($timestamp)."', '{$_SESSION['user']}', '".getDb2Timestamp($timestamp)."')";
		$rs = $db->query($sql_header);
		
		
		return $result;
	}
	private function writeAllegati() {
			global $db;
			$fieldTes = getDs($this->atcFile);
			$timestamp = date('d/m/Y H:i:s');
			//showArray($fieldTes);
			$stmtTes = $db->prepare("INSERT", $this->atcFile, null, array_keys($fieldTes));
			foreach ($this->allegati as $key => $valore) {
				$fieldTes['ATCID'] = $this->id;
				$fieldTes['ATCLNG'] = 'ITA';
				$fieldTes['ATCATC'] = $valore;
				$fieldTes['ATCSTA'] = '1';
				$fieldTes['USRMOD'] = $_SESSION['user'];
				$fieldTes['TMSMOD'] = getDb2Timestamp($timestamp);
				$fieldTes['USRINS'] = $_SESSION['user'];
				$fieldTes['TMSINS'] = getDb2Timestamp($timestamp);
			
				$result = $db->execute($stmtTes, $fieldTes);
			}
	}
	private function writeDestinatari() {
		global $db;
		$file = $this->dstFile;
		$fieldTes = getDs($file);
		$timestamp = date('d/m/Y H:i:s');
		//showArray($fieldTes);
		
		//showArray($_REQUEST);
		$stmtTes = $db->prepare("INSERT", $file, null, array_keys($fieldTes));
		foreach ($this->destinatari as $key => $valore) {		
			$fieldTes['DSTID'] = $this->id;
			$fieldTes['DSTTYP'] = $valore['TIPO'];
			$fieldTes['DSTDST'] = $valore['DESTINATARIO'];
			$fieldTes['DSTIOE'] = $valore['IOE'];
			$fieldTes['DSTOOA'] = $valore['ANDOR'];
			$fieldTes['DSTSEQ'] = $key;
			$fieldTes['DSTSTA'] = '1';
			$fieldTes['USRMOD'] = $_SESSION['user'];
			$fieldTes['TMSMOD'] = getDb2Timestamp($timestamp);
			$fieldTes['USRINS'] = $_SESSION['user'];
			$fieldTes['TMSINS'] = getDb2Timestamp($timestamp);
			
			$result = $db->execute($stmtTes, $fieldTes);
		}
	}
	private function writeExtraParm() {
		global $db;
		$file = $this->prmFile;
		$fieldTes = getDs($file);
		$timestamp = date('d/m/Y H:i:s');
		//showArray($fieldTes);
		
		//showArray($_REQUEST);
		$stmtTes = $db->prepare("INSERT", $file, null, array_keys($fieldTes));
		foreach ($this->extraParm as $key => $valore) {
			$fieldTes['PRMID'] = $this->id;
			$fieldTes['PRMPRM'] = $key;
			$fieldTes['PRMVAL'] = $valore;
			$fieldTes['USRMOD'] = $_SESSION['user'];
			$fieldTes['TMSMOD'] = getDb2Timestamp($timestamp);
			$fieldTes['USRINS'] = $_SESSION['user'];
			$fieldTes['TMSINS'] = getDb2Timestamp($timestamp);
			
			$result = $db->execute($stmtTes, $fieldTes);
		}
	}
	private function writeContenuti() {
		global $db;
		$timestamp = date('d/m/Y H:i:s');
		
		$error = array();
		//showArray($key);
		//showArray($_REQUEST);
		
		if($this->formato == "TXT") {
			$tesFile = $this->cttFile;
			$fieldTes = getDs($tesFile);
			$stmtTes = $db->prepare("INSERT", $tesFile, null, array_keys($fieldTes));
			foreach($this->contenuto as $chiave => $riga) {
				$fieldTes['CTTID'] = $this->id;
				$fieldTes['CTTLNG'] = 'ITA';
				$fieldTes['CTTRIG'] = $chiave+1;
				$fieldTes['CTTTXT'] = $riga;
				$fieldTes['CTTSTA'] = '1';
				$fieldTes['USRMOD'] = $_SESSION['user'];
				$fieldTes['TMSMOD'] = getDb2Timestamp($timestamp);
				$fieldTes['USRINS'] = $_SESSION['user'];
				$fieldTes['TMSINS'] = getDb2Timestamp($timestamp);
		
				$result = $db->execute($stmtTes, $fieldTes);
				if(!$result && !in_array($error, "Errore inserimento nuovo contenuto")) {
					$error[] = "Errore inserimento nuovo contenuto";
				}
			}
		}else {
			$tesFile = $this->cthFile;
			$fieldTes = getDs($tesFile);
			$fieldTes['CTHHTM'] = "";
			//showArray($fieldTes);
			$stmtTes = $db->prepare("INSERT", $tesFile, null, array_keys($fieldTes));
	
			$fieldTes['CTHID'] = $this->id;
			$fieldTes['CTHLNG'] = 'ITA';
			$fieldTes['CTHHTM'] = $this->html;
			$fieldTes['CTHSTA'] = '1';
			$fieldTes['USRMOD'] = $_SESSION['user'];
			$fieldTes['TMSMOD'] = getDb2Timestamp($timestamp);
			$fieldTes['USRINS'] = $_SESSION['user'];
			$fieldTes['TMSINS'] = getDb2Timestamp($timestamp);
	
			$result = $db->execute($stmtTes, $fieldTes);
			if(!$result)  {
				$error[] = "Errore inserimento del contenuto";
			}
	}
	}
	public function pubblicaMessaggio() {
		global $db;
		$file = $this->tesFile;
		$field = array(	"TESSTA" => '1',
				"USRMOD" => $_SESSION['user'],
				"TMSMOD" => getDb2Timestamp(date('d/m/Y H:i:s')));
		$key = array("TESID" => $this->id);
			
		$stmtTes = $db->prepare("UPDATE", $file, $key, array_keys($field));
		$result = $db->execute($stmtTes, $field);
			
		if($result)  {
		
			if($this->divulgazione == "*IMMED") {
				$announce = new wi400announceMessage();
				$rs = $announce->divulgaMessageId($this->id);
			}
		}else {
			
		}
	}
	/**
	 * @desc: Controlla la formalità dei dati inseriti prima della scrittura del messaggio ed impsota eventuali Default mancati
	 */
	public function checkMessaggio() {
		$errore = False;
		$errdes ="";
		$errcod = 0;
		// Manca Azione su Tipo visualizzazione *ACTION
		if($this->tipoVisualizzazione == "*ACTION" && $this->azione=="") {
			$errdes = "Hai settato visualizzazione a '*ACTION'! Campo azione vuoto!";
			$errcod = 1;
			$errore=True;
		}
		// Verifico se inseriti Destinatario
		if(count($this->destinatari)==0) {
			$errdes = "Inserire almeno un destinatario per il messaggio!";
			$errcod = 2;
			$errore=True;
		}
		// Messaggio privato deve essere divulgato immediatamente
		if ($this->messaggioPrivato=="S" && $this->divulgazione !="*IMMED") {
			$errdes = "Il campo divulgazione deve essere *IMMED con messaggio privato!";
			$errcod = 3;
			$errore=True;
		}
		// Verifico se inserito il contenuto del messaggio
		if ($this->formato=="TXT") {
			if (count($this->contenuto)==0) {
				$errdes = "Manca il contenuto TXT del messaggio!";
				$errcod = 4;
				$errore=True;				
			}
		}
		if ($this->formato=="HTML") {
			if ($this->html=="") {
				$errdes = "Manca il contenuto HTML del messaggio!";
				$errcod = 5;
				$errore=True;
			}
		}
		// Verifico data pubblicazione
		if(!check_periodo($this->dataPubblicazione, $this->dataScadenza)) {
			$errdes = "Errore data pubblicazione maggiore della data di scadenza!";
			$errcod = 6;
			$errore=True;
		}
		// Verifico se destinatari *INT o *ENTE e divulgazione *IMMED
		if ($this->divulgazione =='*IMMED') {
			foreach ($this->destinatari as $key => $valori) {
				if ($valori['TIPO']=="*INT" || $valori['TIPO']=="*ENTE") {
					$errdes = "Divulgazione non pu&ograve; essere *IMMED con destinatari *INT o *ENTE!";
					$errcod = 7;
					$errore=True;
					break;
				}
			}
		}
		// Manca titolo
		if($this->titolo=="") {
			$errdes = "Manca il titolo del messaggio!";
			$errcod = 8;
			$errore=True;
		}
		// Controllo se tutti i valori sono congruenti
		if (!in_array($this->formato, $this->check_formato)) {
			$errdes = "Formato messaggio deve essere:".implode(" ", $this->check_formato);
			$errcod = 10;
			$errore=True;
		}
		if (!in_array($this->divulgazione, $this->check_divulgazione)) {
			$errdes = "Divulgazione messaggio deve essere:".implode(" ", $this->check_divulgazione);
			$errcod = 10;
			$errore=True;
		}
		if (!in_array($this->tipoVisualizzazione, $this->check_tipoVisualizzazione)) {
			$errdes = "Tipo visualizzazione messaggio deve essere:".implode(" ", $this->check_tipoVisualizzazione);
			$errcod = 10;
			$errore=True;
		}
		if (!in_array($this->tipo, $this->check_tipo)) {
			$errdes = "Tipo messaggio deve essere:".implode(" ", $this->check_tipo);
			$errcod = 10;
			$errore=True;
		}
		if (!in_array($this->tipoNotifica, $this->check_tipoNotifica)) {
			$errdes = "Tipo notifica messaggio deve essere:".implode(" ", $this->check_tipoNotifica);
			$errcod = 10;
			$errore=True;
		}
		if (!in_array($this->tipoRisposta, $this->check_tipoRisposta)) {
			$errdes = "Tipo risposta messaggio deve essere:".implode(" ", $this->check_tipoRisposta);
			$errcod = 10;
			$errore=True;
		}
		if (!in_array($this->tipoRisposta, $this->check_tipoRisposta)) {
			$errdes = "Tipo risposta messaggio deve essere:".implode(" ", $this->check_tipoRisposta);
			$errcod = 10;
			$errore=True;
		}
		if (!in_array($this->confermaLettura, $this->check_son)) {
			$errdes = "Conferma lettura del messaggio deve essere:".implode(" ", $this->check_son);
			$errcod = 10;
			$errore=True;
		}
		if (!in_array($this->richiedeRisposta, $this->check_son)) {
			$errdes = "Richiede risposta messaggio deve essere:".implode(" ", $this->check_son);
			$errcod = 10;
			$errore=True;
		}
		if (!in_array($this->sempreVisibile, $this->check_son)) {
			$errdes = "Sempre visibile deve essere:".implode(" ", $this->check_son);
			$errcod = 10;
			$errore=True;
		}
		if (!in_array($this->messaggioPrivato, $this->check_son)) {
			$errdes = "Messaggio privato deve essere:".implode(" ", $this->check_son);
			$errcod = 10;
			$errore=True;
		}
		
		if ($errore==True) {
			throw new Exception($errdes, $errcod);
		}
		return true;
		
	}
	public function writeMessaggio() {
		
		$do = $this->checkMessaggio();
		
		if ($do) {
			$this->writeTestata();
			$this->writeAllegati();
			$this->writeContenuti();
			$this->writeDestinatari();
			$this->writeExtraParm();
		}
	}
}
?>