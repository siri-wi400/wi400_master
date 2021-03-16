<?php
/**
 * @desc Classe per scrivere i file di log della mail di wi400
 * @author luca
 *
 * @todo: Aggiornamento dell'errore da lanciare dopo la tentata spedizione
 * @todo: Funzione di controllo formalità dati
 * @todo: Commentare le funzioni
 */
class wi400EmailLog {
	private $tesFile="FPDFCONV";
	private $dstFile="FEMAILDT";
	private $atcFile="FEMAILAL";
	private $usdFile="FEMAILUD";
	private $conFile="FEMAILCT";
	private $destinatari=array();
	private $contenuti=array();
	private $from="";
	private $alias="";
	private $invio="N";
	private $invioMPX="N";
	private $errcod="000";
	private $errdes="";
	private $subject="";
	private $ambiente="";
	private $stato="*";
	private $user="";
	private $job="";
	private $numberJob="";
	private $id;	
	private $allegati=array();
	private $userData = array();
	
	public function setErrore($errcod, $errdes) {
		$this->errcod = $errcod;
		$this->errdes = $errdes;
	}
	public function setStato($stato) {
		$this->stato = $stato;
	}
	public function setAllegato($allegato) {
		$this->allegati[]=$allegato;
	}
	public function setBody($body) {
		$this->contenuti[] = array("TIPO"=>"BODY", "CONTENUTO"=>$body);
	}
	public function setAlias($alias) {
		$this->alias=$alias;
	}
	public function setDestinatario($email, $tipo, $alias="") {
		$this->destinatari[]=array("EMAIL"=>$email, "TIPO"=>$tipo, "ALIAS"=>$alias);
	}
	public function setFrom($from, $alias="") {
		$this->from = $from;
		$this->alias = $alias;
	}
	public function setAmbiente($ambiente) {
		$this->ambiente=$ambiente;
	}
	public function setSubject($subject) {
		$this->subject = $subject;
	}
	public function setInvio($invio) {
		$this->invio = $invio;
	}
	public function setInvioMPX($invioMPX) {
		$this->invioMPX=$invioMPX;
	}
	public function setId($id) {
		$this->id=$id;
	}
	public function setContenuto($tipo, $contenuto) {
		$this->contenuti[] = array("TIPO"=>$tipo, "CONTENUTO"=>$contenuto);
	}
	public function setUserData($tip, $key, $valore) {
		$this->userData[] = array("TIPO"=>$tipo, "KEY"=>$key, "VALORE"=>$valore);
		
	}
	public function __construct($id="", $from="", $alias="") {
		
		$this->id=$id;
		$this->from=$from;
		$this->alias=$alias;
	}
	public function writeLog() {
		
		$this->checkEmail();
		
		$this->writeTestata();
		$this->writeDestinatari();
		$this->writeContenuti();
		$this->writeUserData();
		$this->writeAllegati();
	}
	public function checkEmail() {
		// Al momento niente
	}		
	public function writeTestata() {
		global $db;
		$fieldTes = getDs($this->tesFile);
		$timestamp = date('d/m/Y H:i:s');
		//showArray($fieldTes);
		$stmtTes = $db->prepare("INSERT", $this->tesFile, null, array_keys($fieldTes));
		$fieldTes['ID'] = $this->id;
		$fieldTes['MAIUSR'] = $this->user;
		$fieldTes['MAIJOB'] = $this->job;
		$fieldTes['MAINBR'] = $this->numberJob;
		$fieldTes['MAIEMA'] = $this->invio;
		$fieldTes['MAIMPX'] = $this->invioMPX;
		$fieldTes['MAIFRM'] = $this->from;
		$fieldTes['MAIALI'] = $this->alias;
		$fieldTes['MAISBJ'] = $this->subject;
		$fieldTes['MAISTA'] = $this->stato;
		$fieldTes['MAIAMB'] = "W";
		$fieldTes['MAIERR'] = $this->errcod;
		$fieldTes['MAIDER'] = $this->errdes;
		$fieldTes['MAIINS'] = getDb2Timestamp($timestamp);
		$fieldTes['MAIELA'] = getDb2Timestamp("*INZ");
		$fieldTes['MAIRIS'] = "0";
			
		$result = $db->execute($stmtTes, $fieldTes);
		
	}
	private function writeDestinatari() {
		global $db;
		$file = $this->dstFile;
		$fieldTes = getDs($file);
		$stmtTes = $db->prepare("INSERT", $file, null, array_keys($fieldTes));
		foreach ($this->destinatari as $key => $valore) {
			$fieldTes['ID'] = $this->id;
			$fieldTes['MAITOR'] = $valore['EMAIL'];
			$fieldTes['MAIALI'] = $valore['ALIAS'];
			$fieldTes['MATPTO'] = $valore['TIPO'];
				
			$result = $db->execute($stmtTes, $fieldTes);
		}
	}
	private function writeContenuti() {
		global $db;
		$file = $this->conFile;
		$fieldTes = getDs($file);
		$riga=0;
		$stmtTes = $db->prepare("INSERT", $file, null, array_keys($fieldTes));
		foreach ($this->contenuti as $key => $valore) {
			$riga++;
			$fieldTes['ID'] = $this->id;
			$fieldTes['UCTRIG'] = $riga;
			$fieldTes['UCTTYP'] = $valore['TIPO'];
			$fieldTes['UCTKEY'] = $valore['CONTENUTO'];

			$result = $db->execute($stmtTes, $fieldTes);
		}
	}
	private function writeAllegati() {
		global $db;
		$file = $this->atcFile;
		$fieldTes = getDs($file);
		$stmtTes = $db->prepare("INSERT", $file, null, array_keys($fieldTes));
		foreach ($this->allegati as $key => $valore) {
			$fieldTes['ID'] = $this->id;
			$fieldTes['MAIATC'] = $valore->getAllegato();
			$fieldTes['MAIPAT'] = $valore->getPathConversione();
			$fieldTes['CONV'] = $valore->getConvertire();
			$fieldTes['TPCONV'] = $valore->getTipoConversione();
			$fieldTes['MAIMOD'] = $valore->getModuloConversione();
			$fieldTes['MAIARG'] = $valore->getArgomento();
			$fieldTes['MAINAM'] = $valore->getNomeImposto();
			$fieldTes['MAIOUT'] = $valore->getOutq();
			$fieldTes['MAISTT'] = $valore->getDataStampa();
			$fieldTes['MAISTO'] = $valore->getStampatoPDF();
			//$fieldTest['MAILIB'] = $_SESSION['user'];
			$fieldTes['FILZIP'] = $valore->getZip();
	
			$result = $db->execute($stmtTes, $fieldTes);
		}
	}
	public function aggiornaStato($id, $errore, $errdes, $stato) {
		global $db;
		$sql = "UPDATE FPDFCONV";
		$sql .= " SET MAISTA='" . $stato . "', MAIERR='" . $errore . "'";
		$sql .= ", MAIDER='" . $errdes . "'";
		$sql .= " WHERE ID = '$this->ID'";
		$result = $db->query($sql);
	} 
	private function writeUserData() {
		global $db;
		$file = $this->usdFile;
		$fieldTes = getDs($file);
		$riga=0;
		$stmtTes = $db->prepare("INSERT", $file, null, array_keys($fieldTes));
		foreach ($this->userData as $key => $valore) {
			$riga++;
			$fieldTes['ID'] = $this->id;
			$fieldTes['USNRIG'] = $riga;
			$fieldTes['USNKEY'] = $valore['KEY'];
			$fieldTes['USNVAL'] = $valore['VALORE'];
			$fieldTes['USNTYP'] = $valore['TIPO'];
			
			$result = $db->execute($stmtTes, $fieldTes);
		}
	}
	/**
	 * Setta i parametri ricavandoli da un oggetto PHPMailer
	 * @param unknown $object
	 */
	public static function setByObject($object, $esito) {
		
		$id="XXXXXX";
		$emailLog = new wi400EmailLog($id);
		try {
			// Recupero Allegati
			$dati = $object->getAttachments();
			foreach ($dati as $key => $value) {
				if ($value[0]!="") {
					$allegato = new wi400EmailAllegato($value[0]);
					$emailLog->setAllegato($allegato);
				}
			}
			// Recupero Destinatari Vari
			// TO
			$dati = $object->getToAddresses();
			foreach ($dati as $key => $value) {
				if ($value[0]!="") {
					$emailLog->setDestinatario($value['0'], "TO", $value['0']);
				}
			}
			// CC
			$dati = $object->getCcAddresses();
			foreach ($dati as $key => $value) {
				if ($value[0]!="") {
					$emailLog->setDestinatario($value['0'], "CC", $value['0']);
				}
			}
			// BCC
			$dati = $object->getBccAddresses();
			foreach ($dati as $key => $value) {
				if ($value[0]!="") {
					$emailLog->setDestinatario($value['0'], "BCC", $value['0']);
				}
			}
			// RPYTO
			$dati = $object->getReplyToAddresses();
			foreach ($dati as $key => $value) {
				if ($value[0]!="") {
					$emailLog->setDestinatario($value['0'], "RPYTO", $value['0']);
				}
			}
			$emailLog->setFrom($object->From);
			$emailLog->setAlias($object->FromName);
			$emailLog->setBody($object->Body);
			$emailLog->setSubject($object->Subject);
			$emailLog->setInvio("S");
			$stato = "000";
			$deserr= "";
			if ($esito==False) {
				$stato = "001";
				$deserr= substr($object->ErrorInfo,0,40);
			}
			$emailLog->setErrore($stato, $deserr);
			$emailLog->writeLog();
		} catch (Exception $exc) {
			
		}
	}
}
class wi400EmailAllegato {
	
	private $allegato;
	private $convertire="N";
	private $pathConversione="";
	private $tipoConversione="";
	private $moduloConversione="";
	private $argomento="";
	private $nomeImposto="";
	private $outq="";
	private $stampatoPDF="N";
	private $dataStampa="";
	private $zip="";
	
	/**
	 * @return the $allegato
	 */
	public function getAllegato() {
		return $this->allegato;
	}

	/**
	 * @return the $convertire
	 */
	public function getConvertire() {
		return $this->convertire;
	}

	/**
	 * @return the $pathConversione
	 */
	public function getPathConversione() {
		return $this->pathConversione;
	}

	/**
	 * @return the $tipoConversione
	 */
	public function getTipoConversione() {
		return $this->tipoConversione;
	}

	/**
	 * @return the $moduloConversione
	 */
	public function getModuloConversione() {
		return $this->moduloConversione;
	}

	/**
	 * @return the $argomento
	 */
	public function getArgomento() {
		return $this->argomento;
	}

	/**
	 * @return the $nomeImposto
	 */
	public function getNomeImposto() {
		return $this->nomeImposto;
	}

	/**
	 * @return the $outq
	 */
	public function getOutq() {
		return $this->outq;
	}

	/**
	 * @return the $stampatoPDF
	 */
	public function getStampatoPDF() {
		return $this->stampatoPDF;
	}

	/**
	 * @return the $dataStampa
	 */
	public function getDataStampa() {
		return $this->dataStampa;
	}

	/**
	 * @return the $zip
	 */
	public function getZip() {
		return $this->zip;
	}

	/**
	 * @param field_type $allegato
	 */
	public function setAllegato($allegato) {
		$this->allegato = $allegato;
	}

	/**
	 * @param string $convertire
	 */
	public function setConvertire($convertire) {
		$this->convertire = $convertire;
	}

	/**
	 * @param string $pathConversione
	 */
	public function setPathConversione($pathConversione) {
		$this->pathConversione = $pathConversione;
	}

	/**
	 * @param string $tipoConversione
	 */
	public function setTipoConversione($tipoConversione) {
		$this->tipoConversione = $tipoConversione;
	}

	/**
	 * @param string $moduloConversione
	 */
	public function setModuloConversione($moduloConversione) {
		$this->moduloConversione = $moduloConversione;
	}

	/**
	 * @param string $argomento
	 */
	public function setArgomento($argomento) {
		$this->argomento = $argomento;
	}

	/**
	 * @param string $nomeImposto
	 */
	public function setNomeImposto($nomeImposto) {
		$this->nomeImposto = $nomeImposto;
	}

	/**
	 * @param string $outq
	 */
	public function setOutq($outq) {
		$this->outq = $outq;
	}

	/**
	 * @param string $stampatoPDF
	 */
	public function setStampatoPDF($stampatoPDF) {
		$this->stampatoPDF = $stampatoPDF;
	}

	/**
	 * @param string $dataStampa
	 */
	public function setDataStampa($dataStampa) {
		$this->dataStampa = $dataStampa;
	}

	/**
	 * @param field_type $zip
	 */
	public function setZip($zip) {
		$this->zip = $zip;
	}

	public function __construct($allegato, $convertire="N", $pathConversione="", $tipoConversione="") {

		$this->allegato=$allegato;
		$this->convertire=$convertire;
		$this->pathConversione=$pathConversione;
		$this->tipoConversione=$tipoConversione;
		$this->dataStampa = getDb2Timestamp("*INZ");
		
	}
	
	
}