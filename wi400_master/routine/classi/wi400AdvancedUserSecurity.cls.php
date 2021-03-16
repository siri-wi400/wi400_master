<?php
/**
 * @name wi400AdvancedUserSecurity
 * @desc Classe per la sicurezza avanzata degli utenti
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author 
 * @version 1.01 08/09/2017
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */
class wi400AdvancedUserSecurity {
	private $user;
	private $block;
	private $num_errori;
	private $timeout = 1800;
	private $param = False;
	private $foundParam=False;
	private $secFile="SIR_USESE";
	private $set = False;
	private $array_complex = array(
		"Nessuna" => "*NONE",
		"Debole" => "*WEAK",
		"Media" => "*MEDIUM",
		"Forte" => "*STRONG",
		"Complessa" => "*COMPLEX",
		"Default Sistema" => "*SYSVAL",	
	);
	private $desc_complex = array(
			"*NONE" => "Nessuna (almeno 1 carattere)",
			"*WEAK" => "Deobole (almeno 6 caratteri)",
			"*MEDIUM" => "Media (caratteri e numeri)",
			"*STRONG" =>"Forte (caratteri, numeri e caratteri speciali)",
			"*COMPLEX" => "Complessa (caratteri, numeri, maiuscole e caratteri speciali)"
	);
	public function getDescComplex($complex) {
		return $this->desc_complex[$complex];
	}
	public function getComplexArray() {
		return $this->array_complex;
	}
	public function __construct($user) {
		$this->user = $user;
		$this->getSecuritySettings();
	}
	public function setUser($user) {
		$this->user = $user;
	}
	public function getUser() {
		return $this->user;
	}
	public function setTimeout($timeout) {
		$this->timeout = $timeout;
	}
	public function getTimeout() {
		return $this->timeout;
	}
	/**
	 * @desc Controllo se un utente è stato bloccato
	 * @param unknown $userName
	 * @return boolean
	 */
	public function checkUserBlocked() {
		global $settings;
		$fileBlock = wi400File::getCommonFile("blocked", strtoupper($this->user).".dat" );
		if(file_exists($fileBlock)) {
			// Verifico se è un blocco amministrativo e quindi no reset
			$dati = file_get_contents($fileBlock);
			if (substr($dati,0, 3)=="AMM") {
				return "AMM";
			}
			// Controllo se il file è più vecchio di 30 minuti, in questo caso lo cancello
			if (time()-filemtime($fileBlock) > $this->timeout) {
				unlink($fileBlock);
			} else {
				return True;
			}
		}
		return False;
	}
	public function setUserBlocked($amm="") {
		global $settings;
		$fileBlock = wi400File::getCommonFile("blocked", strtoupper($this->user).".dat" );
		file_put_contents($fileBlock, $amm.":BLOCCATO IL ".date("d-m-Y h:i:s"));
	}
	public function setUserUnBlocked() {
		global $settings;
		$fileBlock = wi400File::getCommonFile("blocked", strtoupper($this->user).".dat" );
		unlink($fileBlock);
	}
	public function setUserData($secData) {
		global $db;
		// Verifico se Insert o Update
		$sql = "SELECT * FROM $this->secFile WHERE USER_NAME=?";
		$stmt = $db->singlePrepare($sql);
		$result = $db->execute($stmt, array($this->user));
		$row = $db->fetch_array($stmt);
		if (!$row) {
				$sec = getDs($this->secFile);
				$sec = array_merge($sec, $secData);
				$timestamp = date('d/m/Y H:i:s');
				$stmtTes = $db->prepare("INSERT", $this->secFile, null, array_keys($sec));
				$sec['USER_NAME']=$this->user;
				$sec['LSTCHP']=date("d-m-Y");
				$sec['LOGERR']=0;
				$sec['STATO']="1";
				$result = $db->execute($stmtTes, $sec);
		} else {
			// Devo aggiornare il record
			$field = $secData;
			$key = array("USER_NAME"=>$this->user);
			$stmt = $db->prepare('UPDATE', $this->secFile, $key, array_keys($field));
			$campi = $secData;
			$result = $db->execute($stmt, $campi);
		}
	}
	public function getUserViewSecurityParam() {
		global $db, $settings;
		$this->param = True;
		$sec = array();
		$this->foundParam=False;
		// Cerco sul file
		$sql = "SELECT * FROM $this->secFile WHERE USER_NAME=?";
		$stmt = $db->singlePrepare($sql);
		$result = $db->execute($stmt, array($this->user));
		$sec = $db->fetch_array($stmt);
		if (!$sec) {
			$sec['USER_NAME']=$this->user;
			$sec['DURATAP']= "*SYSVAL";
			$sec['SCADE_NEXT']="N";
			$sec['COMPLEX']="*SYSVAL";
			$sec['MAXTENTA']="*SYSVAL";
			$sec['ABIRIP']="*SYSVAL";
			$sec['ABILOG']="*SYSVAL";
			$sec['ABICHP']="*SYSVAL";
			$sec['NUMERR']=0;
			$sec['LSTCHP']="-----";
			$sec['LOGERR']=0;
			$sec['STATO']="1";
		}
		return $sec;
	}
	/**
	 * @desc: Reperisco i parametri di sicurezza legati all'utente
	 */
	public function getSecurityParam() {
		global $db, $settings;
		$this->param = True;
		$sec = array();
		$this->foundParam=False;
		// Cerco sul file
		$sql = "SELECT * FROM $this->secFile WHERE USER_NAME=?";
		$stmt = $db->singlePrepare($sql);
		$result = $db->execute($stmt, array($this->user));
		$sec = $db->fetch_array($stmt);

		if ($sec) {
			$this->foundParam=True;
			if ($sec['DURATAP']=="*SYSVAL") {
				$sec['DURATAP']= $this->set['sec_advanced_durata_password'];
			}
			if ($sec['COMPLEX']=="*SYSVAL") {
				$sec['COMPLEX']= $this->set['sec_advanced_complex'];
			}
			if ($sec['MAXTENTA']=="*SYSVAL") {
				$sec['MAXTENTA']= $this->set['sec_advanced_maxtentativi'];
			}
			if ($sec['ABIRIP']=="*SYSVAL") {
				$sec['ABIRIP']= $this->set['sec_advanced_ripristino'];
			}
			if ($sec['ABILOG']=="*SYSVAL") {
				$sec['ABILOG']= $this->set['sec_advanced_log'];
			}
			if ($sec['ABICHP']=="*SYSVAL") {
				$sec['ABICHP']= $this->set['sec_advanced_cambio_password'];
			}
		} else {
			// Se non trovato imposto quelli fissati da sistema
			$sec['DURATAP']= $this->set['sec_advanced_durata_password'];
			$sec['SCADE_NEXT']="N";
			$sec['COMPLEX']=$this->set['sec_advanced_complex'];
			$sec['MAXTENTA']=$this->set['sec_advanced_maxtentativi'];
			$sec['ABIRIP']=$this->set['sec_advanced_ripristino'];
			$sec['ABILOG']=$this->set['sec_advanced_log'];
			$sec['ABICHP']=$this->set['sec_advanced_cambio_password'];
			$sec['NUMERR']=0;
			$sec['LSTCHP']=date("d-m-Y");
			$sec['LOGERR']=0;
		}
		// Adesso che ho i parametri cerco di capire alcune condizioni
		$sec['CAMBIO_PASSWORD']="N";
		// Cambio password NEXT LOGIN
		if ($sec['SCADE_NEXT']=="S") {
			$sec['CAMBIO_PASSWORD']='S';
		}
		// Durata e scadenza
		if ($sec['DURATAP']!="*EVER") {
			$diffe = date_diff($sec['LSTCHP'], date("d-m-Y"));
			if ($diffe > $sec['DURATAP']) {
				$sec['CAMBIO_PASSWORD']=='S';
			}
		}
		$this->param= $sec;
		return $sec;
	}
	public function resetUserError($errori) {
		global $db;
		// @todo cosa fa questa roba ...
		//if ($param==False) {
			$this->getSecurityParam();
		//}
		if ($this->foundParam==False) {
			// Devo Scrivere se devo aggiornare gli errori
			if ($errori!=0) {
				$sec = getDs($this->secFile);
				$timestamp = date('d/m/Y H:i:s');
				$stmtTes = $db->prepare("INSERT", $this->secFile, null, array_keys($sec));
				$sec['USER_NAME']=$this->user;
				//$sec['DURATAP']= $this->set['sec_advanced_durata_password'];
				$sec['DURATAP']= "*SYSVAL";
				$sec['SCADE_NEXT']="N";
				$sec['COMPLEX']="*SYSVAL";
				$sec['MAXTENTA']="*SYSVAL";
				$sec['ABIRIP']="*SYSVAL";
				$sec['ABILOG']="*SYSVAL";
				$sec['ABICHP']="*SYSVAL";
				$sec['NUMERR']=$errori;
				$sec['LSTCHP']=date("d-m-Y");
				$sec['LOGERR']=0;
				$sec['STATO']="1";
				$result = $db->execute($stmtTes, $sec);
			}
		} else {
			// Devo aggiornare il record
			$field = array("NUMERR");
			$key = array("USER_NAME"=>$this->user);
			$stmt = $db->prepare('UPDATE', $this->secFile, $key, $field);
			$campi = array(trim($errori));
			$result = $db->execute($stmt, $campi);
		}
	}
	public function resetChangePasswordLogin() {
		global $db, $settings;
		$field = array("SCADE_NEXT");
		$key = array("USER_NAME"=>$this->user);
		$stmt = $db->prepare('UPDATE', $this->secFile, $key, $field);
		$campi = array("N");
		$result = $db->execute($stmt, $campi);
		
	}
	/**
	 * @desc Cerca i parametri di sicurezza, se non trovati li imposta a DEFAULT
	 */
	public function getSecuritySettings() {
		global $settings;
		$set = array();
		// Durata Password
		if (!isset($settings['sec_advanced_durata_password'])) {
			$set['sec_advanced_durata_password']="*EVER";
		} else {
			$set['sec_advanced_durata_password']=$settings['sec_advanced_durata_password'];
		}
		// Complessita Password
		if (!isset($settings['sec_advanced_complex'])) {
			$set['sec_advanced_complex']="*STRONG";
		} else {
			$set['sec_advanced_complex']=$settings['sec_advanced_complex'];
		}
		// Massimo tentativi
		if (!isset($settings['sec_advanced_maxtentativi'])) {
			$set['sec_advanced_maxtentativi']="9999999";
		} else {
			$set['sec_advanced_maxtentativi']=$settings['sec_advanced_maxtentativi'];
		}
		// Ripristino Password
		if (!isset($settings['sec_advanced_ripristino'])) {
			$set['sec_advanced_ripristino']="N";
		} else {
			$set['sec_advanced_ripristino']=$settings['sec_advanced_ripristino'];
		}
		// Abilita LOG
		if (!isset($settings['sec_advanced_log'])) {
			$set['sec_advanced_log']="N";
		} else {
			$set['sec_advanced_log']=$settings['sec_advanced_log'];
		}
		// Abilita Cambio Password
		if (!isset($settings['sec_advanced_cambio_password'])) {
			$set['sec_advanced_cambio_password']="N";
		} else {
			$set['sec_advanced_cambio_password']=$settings['sec_advanced_cambio_password'];
		}
		$this->set = $set;
	}
}