<?php
/**
 * @desc wi400Otm: Classi per gestire la OTM
 * @author luca
 * @todo Gestire la data di scadenza per non passare l'OTM a stato 'A' ma lasciarla aperta incrementando il contatore. Limite Utilizzi OTM?
 */
class wi400Otm {
	private $OtmId;
	private $error = False;
	private $messerr = "";
	private $record=array();
	private $parameter=array();
	private $user ="";
	private $defaultKey ="ec340029d65c7125783d8a8b27b77c8a0fcdc6ff23cf04b576063fd9d1273257";
	
	public function __construct($id=""){
		$this->OtmId = $id;
	}
	/**
	 * @desc isError: Ritorna True se la ricerca dell'OTM è andat in errore
	 * @return boolean $error: True se OTM errata
	 */
	public function isError() {
		return $this->error;
	}
	/**
	 * @desc getOtm() Recupera l'OTM 
	 * @return boolean True se tutto ok.
	 */
	public function getOtm() {
		global $db, $settings;
		$this->record = array();
		// Verifico se esiste la ONE TIME PASSWORD
//		$sql = "select * FROM PHPLIB".$settings['db_separator']."SIR_OTM WHERE OTMID='".$this->OtmId."' AND OTMSTA='1'";
		$sql = "select * FROM SIR_OTM WHERE OTMID='".$this->OtmId."' AND OTMSTA='1'";
		$result = $db->singleQuery($sql);
		$row = $db->fetch_array ($result);
		// se non ho letto nulla vado al LOGIN - OTM non trovata
		if (!$row) {
			$this->error = True;
			$this->messerr = "OTM_SCADUTA: Utilzzare utente e password per il collegamento";
			return false;
		}
		// se trovata la cancello immediatamente oppure aggiorno in base al parametro nei settings
		if ($row['OTMTYP']!="STATIC") {
			if (isset($settings['OTM_delete']) && $settings['OTM_delete']==True) {
	//			$sql = "DELETE FROM PHPLIB".$settings['db_separator']. "SIR_OTM WHERE OTMID='".$_GET['OTM']."'";
				$sql = "DELETE FROM SIR_OTM WHERE OTMID='".$_GET['OTM']."'";				
				$result = $db->query($sql);
				if (!$result) {
					$this->error = True;
					$this->messerr = "OTM Anomala Verificare";
					return false;
				}
			} else {
	//			$sql = "UPDATE PHPLIB".$settings['db_separator']. "SIR_OTM SET OTMSTA='A' WHERE OTMID='".$_GET['OTM']."'";
				$sql = "UPDATE SIR_OTM SET OTMSTA='A' WHERE OTMID='".$_GET['OTM']."'";				
				$result = $db->query($sql);
				if (!$result) {
					$this->error = True;
					$this->messerr = "OTM Anomala Verificare";
					return false;
				}
			}
		} else {
			// Aggiungo parametri contenuti nella REQUEST
			$_SESSION['WI400_OTM_PARAMETERS']=$row['OTMCON'];
			// Controllo se presente una azione da richiamare in automatico
			$dati = explode(";",$_SESSION['WI400_OTM_PARAMETERS']);
			foreach ($dati as $key => $valore) {
				$dati2 = explode("=", $valore);
				$parametri[$dati2[0]]=$dati2[1];
			}
			if (isset($parametri['ACTION'])) {
				$parametri['action']=$parametri['ACTION'];
			}
			// Metto in session il codice OTM per usi FUTURI
			$_SESSION['WI400_OTM_CODE']=$_GET['OTM'];
			if (isset($parametri['ACTION'])) {
				$_GET['t']=$parametri['ACTION'];
			}
			if (isset($parametri['GATEWAY'])) {
				$_GET['g']=$parametri['GATEWAY'];
			}
			// Parametri aggiuntivi OTM
			if (isset($_GET['OTM_PARM'])) {
				$_SESSION['WI400_OTM_PARM']=$_GET['OTM_PARM'];
			}
		}	
		// Controllo il timestamp
		$tm = $row['OTMTIM'];
		$anno = substr($tm,0,4);
		$mese  = substr($tm,5,2);
		$giorno= substr($tm,8,2);
		$ora = substr($tm,11, 2);
		$minuti = substr($tm,14,2);
		$secondi = substr($tm, 17, 2);
		$plusmin = 5;
		$plussec = 0;
		// Calcolo la durata dell'OTM
		if (isset($settings['OTM_val_min']) && $settings['OTM_val_min']!="") {
			$plusmin = $settings['OTM_val_min'];
		}
		if (isset($settings['OTM_val_sec']) && $settings['OTM_val_sec']!="") {
			$plussec = $settings['OTM_val_sec'];
		}
		// Verifico se sul file è stata impostata una data massima di scadenza, quindi no default parametri scadenza
		if ($row['OTMEXP']!= getDb2Timestamp("*INZ")) {
			$tm = $row['OTMEXP'];
			$anno = substr($tm,0,4);
			$mese  = substr($tm,5,2);
			$giorno= substr($tm,8,2);
			$ora = substr($tm,11, 2);
			$minuti = substr($tm,14,2);
			$secondi = substr($tm, 17, 2);			
			$time_unix = mktime($ora,$minuti,$secondi,$mese,$giorno,$anno);
		} else {
			$time_unix = mktime($ora,$minuti+$plusmin,$secondi+$plussec,$mese,$giorno,$anno);
		}
		$adesso = time();
		// se non ho letto nulla vado al LOGIN - OTM non trovata
		if ($time_unix < $adesso) {
			$this->error = True;
			$this->messerr = "OTM Scaduta";
			return false;
		}
		$this->record = $row;
		$this->user = $row['OTMUSR'];
		return True;
		//
	}
	function getUser() {
		return $this->user;
	}
	function parseParameterSerialize() {
		$file ="/tmp/otm_".$this->OtmId.".txt";
		$dati = file_get_contents($file);
		$dati = unserialize($dati);
		$this->record = $dati;
		return $this->parseParameter();
	}
	/**
	 * @desc parseParameter() Estrae i parametri contenuti nella OTM sul file
	 * @return array Parametri trovati:
	 */
	function parseParameter() {
		if ($this->record['OTMTYP']=="TEXT" || $this->record['OTMTYP']=="STATIC") {
			$dati = explode(";", $this->record['OTMCON']);
			foreach ($dati as $key=>$value) {
				$parametri = explode("=", $value);
				$this->parameter[$parametri[0]]=$parametri[1];
			}
			if (isset($this->parameter['ACTION'])) {
				$this->parameter['action']=$this->parameter['ACTION'];
			}
		}
		if ($this->record['OTMTYP']=="XML") {
			$dom = new DomDocument('1.0');
			$xml = trim($this->record['OTMTYP']);
			$dom->loadXML($xml);
			$params = $dom->getElementsByTagName('parametro');
			$i=0;
			foreach ($params as $param){
				$parametri[$params->item($i)->getAttribute('id')]=$params->item($i)->getAttribute('value');
				$i++;
			}
			$this->parameter = $parametri;
		}
		return $this->parameter;
	}
	/**
	 * @desc create_key : Crea una chiave OTM, deve essere passare la private KEy
	 * @param unknown $private_key
	 * @return string
	 */
	function create_key($private_key) {
		//$rand = mcrypt_create_iv(10, MCRYPT_DEV_URANDOM); // da PHP 7.2 è deprecata
		$rand = random_bytes(10);
		$signature = substr(hash_hmac('sha256', $rand, $private_key, true), 0, 10);
		$license = $this->base64url_encode($rand . $signature);
		return $license;
	}
	/**
	 * @desc check_key Controlla se una OTM passata è stata creata con la private Key
	 * @param unknown $license
	 * @param unknown $private_key
	 * @return boolean
	 */
	function check_key($license, $private_key) {
		$tmp = $this->base64url_decode($license);
		$rand = substr($tmp, 0, 10);
		$signature = substr($tmp, 10);
		$test = substr(hash_hmac('sha256', $rand, $private_key, true), 0, 10);
		return $test === $signature;
	}
	/**
	 * @desc getOtmPassw0rd(): Recupera e scrive una OTM password
	 * @param string $user Utente 
	 * @param string $type tipo contenuto
	 * @param string $parm Parametrin in formato XML o TEXT
	 * @param string $scadenza Eventuale data di scadenza dell'OTM (GG/MM/AAAA hh:mm:ss)
	 * @return string|boolean
	 */
	function getOtmPassword($user, $type, $parm, $scadenza="") {
		global $db, $settings;
		
		if (isset($settings['private_key']) && $settings['private_key']!="") {
			$privateKey = $settings['private_key'];
		} else {
			$privateKey = $this->defaultKey;
		}
		// Recupero chiave cifrata
		$key = $this->create_key($privateKey);
		// Scrittura della OTM
		// INSERT
		$timeStamp = getDb2Timestamp();
		$datexp = getDb2Timestamp("*INZ");
		if ($scadenza !="") {
			$datexp = getDb2Timestamp($scadenza);
		}
		$fieldsValue = array("OTMID" => $key, "OTMUSR" => $user,"OTMTIM" => $timeStamp, "OTMSTA"=>"1", "OTMTYP"=>$type, "OTMCON"=>$parm, "OTMEXP"=>$datexp);
		$stmt_ins = $db->prepare("INSERT", "SIR_OTM", null, array_keys($fieldsValue));
		$result = $db->execute($stmt_ins, $fieldsValue);
		if ($result) {
			$this->OtmId = $key;
			// Serializzo per velocizzare il caricamento dei parametri generali
			$file ="/tmp/otm_".$key.".txt";
			file_put_contents($file, serialize($fieldsValue));
			return $key;
		} else {
			return false;
		}
	}
	function verifyOtm($otm) {
		global $settings;
		if (isset($settings['private_key']) && $settings['private_key']!="") {
			$privateKey = $settings['private_key'];
		} else {
			$privateKey = $this->defaultKey;
		}
		$esito = $this->check_key($otm, $privateKey);
		// Controllo presenza chiave nella withe list
		if ($esito == False) {
			$esito = $this->checkWitheList($otm);
		}
		return $esito;
	}
	function base64url_encode($data) {
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}
	/*
	 * Funzione controllo presenza OTM su whitelist
	 */
	public function checkWitheList($otm) {
		global $settings;
		$whitelist = wi400File::getCommonFile("otm", "withelist.dat" );
		if(file_exists($whitelist) && $otm!="") {
			$dati = file_get_contents($whitelist);
			$row = explode("\r\n", $dati);
			if (in_array($otm, $row)) {
				return True;
			}
		}
		return False;
	}
	/**
	 * Questa funzione aggiorna la white list con tutte le statiche
	 */
	public function updateWitheList() {
		global $db, $settings;
		$sql = "select * FROM SIR_OTM WHERE OTMSTA='1' AND OTMTYP='STATIC' AND OTMEXP>='".date("Y-m-d-h.i.s").".00000'";
		$result = $db->query($sql);
		$dati = "";
		while ($row = $db->fetch_array($result)) {
			$dati.= $row['OTMID']."\r\n";
		}
		$whitelist = wi400File::getCommonFile("otm", "withelist.dat" );
		file_put_contents($whitelist, $dati);
	}
	function base64url_decode($data) {
		return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
	}
	
	public function get_defaultKey() {
		return $this->defaultKey;
	}
	
}