<?php

class MONITOR_EMAIL_LIST extends wi400CustomSubfile {
	
	private $stmt_user;
	private $des_user_array = array();
	
	public function __construct($parameters) {
		global $db, $connzend, $moduli_path, $persTable, $settings;
		
		$sql_user = "select DSPRAD from ".$settings['lib_architect']."/JPROFADF	where NMPRAD=?";
		$this->stmt_user = $db->singlePrepare($sql_user);
	}
	
	public function getArrayCampi() {
		global $db;
	
		$array = array();
	
		$array['ID']=$db->singleColumns("1", "10", "", "ID");
		$array['MAIUSR']=$db->singleColumns("1", "10", "", "Utente");
		$array['DES_USR']=$db->singleColumns("1", "100", "", "Descrizione Utente");		
		$array['MAIJOB']=$db->singleColumns("1", "10", "", "Job");
		$array['MAINBR']=$db->singleColumns("1", "6", "", "Numero Job");
		$array['MAIEMA']=$db->singleColumns("1", "1", "", "Invio e-mail");
		$array['MAIMPX']=$db->singleColumns("1", "1", "", "Invio MPX");
		$array['MAIFRM']=$db->singleColumns("1", "64", "", "E-mail mittente");
		$array['MAIALI']=$db->singleColumns("1", "50", "", "Alias mittente");
		$array['MAISBJ']=$db->singleColumns("1", "60", "", "Oggetto");
		$array['MAISTA']=$db->singleColumns("1", "1", "", "Stato del record");
		$array['MAIAMB']=$db->singleColumns("1", "1", "", "Ambiente generazione");
		$array['MAIWDW']=$db->singleColumns("1", "1", "", "Window");
		$array['MAILIB']=$db->singleColumns("1", "64", "", "Campo libero per usi futuri");
		$array['MAIRIS']=$db->singleColumns("3", "4", 0, "Numero Rispedizioni");
		$array['MAIERR']=$db->singleColumns("1", "3", "", "Codice errore");
		$array['MAIDER']=$db->singleColumns("1", "40", "", "Messaggio di errore");
		$array['MAIINS']=$db->singleColumns("1", "30", "", "Data inserimento");
		$array['MAIELA']=$db->singleColumns("1", "30", "", "Data elaborazione");
				
		return $array;
	}
	
	public function init($parameters){
		global $db, $settings;
	
		$this->setCols($this->getArrayCampi());
	}
	
	public function body($campi, $parameters) {
		global $db, $connzend, $persTable;
		
		$user = $campi['MAIUSR'];
		
		$des_usr = "";
		if(!array_key_exists($user, $this->des_user_array)) {
			$result_user = $db->execute($this->stmt_user, array($user));
			if($row_user = $db->fetch_array($this->stmt_user))
				$this->des_user_array[$user] = $row_user['DSPRAD'];
			else
				$this->des_user_array[$user] = "";
		}
		$des_usr = $this->des_user_array[$user];
		
		$email = "N";
		if($campi['MAIEMA']=="S")
			$email = "S";
		
		$mpx = "N";
		if($campi['MAIMPX']=="S")
			$mpx = "S";
		
		$writeRow = array(
			$campi['ID'],
			$campi['MAIUSR'],
			$des_usr,
			$campi['MAIJOB'],
			$campi['MAINBR'],
			$email,
			$mpx,
			$campi['MAIFRM'],
			$campi['MAIALI'],
			$campi['MAISBJ'],
			$campi['MAISTA'],
			$campi['MAIAMB'],
			$campi['MAIWDW'],
			$campi['MAILIB'],
			$campi['MAIRIS'],
			$campi['MAIERR'],
			$campi['MAIDER'],
			$campi['MAIINS'],
			$campi['MAIELA']
		);
		
		if(!empty($writeRow)) 
			return $writeRow;
		else 
			return false;
	}
	
}