<?php 

class UTENTI_SISTEMA extends wi400CustomSubfile {
	
	private $stmt_des_ute;
	private $stmt_area_fun;
	
	private $des_utente = array();
	private $des_area_fun = array();
	
	public function __construct($parameters){
		global $db, $connzend, $moduli_path;
		
		$sql_des_ute = "select * from AASSTP3000/JPROFADF where NMPRAD=?";
		$this->stmt_des_ute = $db->singlePrepare($sql_des_ute);
		
		$sql_des_area_fun = "select AFDESC from SIRIUTENZE/FAREAFUN where AFAREA=?";
		$this->stmt_area_fun = $db->singlePrepare($sql_des_area_fun);
	}
	
	public function getArrayCampi() {	
		global $db;
		$array = array();
		$array['USERID']=$db->singleColumns("1", "10", "", "ID Utente");
		$array['DESCRIZIONE']=$db->singleColumns("1", "100", "", "Descrizione utente");
		$array['EMAIL']=$db->singleColumns("1", "100", "", "Email");
		$array['AREA_FUN']=$db->singleColumns("1", "10", "", "Area funzionale");
		$array['DES_AREA_FUN']=$db->singleColumns("1", "100", "", "Descrizione area funzionale");
		$array['DES_USER']=$db->singleColumns("1", "30", "", "Descrizione utente");
		$array['ENTE']=$db->singleColumns("1", "4", "", "Ente");
		$array['NAZIONE']=$db->singleColumns("1", "4", "", "Nazione");
		$array['STATO']=$db->singleColumns("1", "1", "", "Stato");
		$array['LAST_COL']=$db->singleColumns("1", "30", "", "Ultimo collegamento");
	
		return $array;
	}	
	
	public function init($parameters){
		global $db, $connzend;
		
		$this->setCols($this->getArrayCampi());
	}
	
	public function body($campi, $parameters){
		global $db, $connzend;
		
		if(!in_array($campi['UPUPRF'],$this->des_utente)) {
			$res_des_ute = $db->execute($this->stmt_des_ute, array($campi['UPUPRF']));
			if($row_des_ute = $db->fetch_array($this->stmt_des_ute))
				$this->des_utente[$campi['UPUPRF']] = $row_des_ute['DSPRAD'];
			else
				$this->des_utente[$campi['UPUPRF']] = '';
		}
		
		if(!in_array($campi['USARFU'],$this->des_area_fun)) {
			$res_area_fun = $db->execute($this->stmt_area_fun, array($campi['USARFU']));
			if($row_area_fun = $db->fetch_array($this->stmt_area_fun))
				$this->des_area_fun[$campi['USARFU']] = $row_area_fun['AFDESC'];
			else
				$this->des_area_fun[$campi['USARFU']] = '';
		}
		
		$des_area_fun = "";
		if(!empty($campi['USARFU']))
			$des_area_fun = $this->des_area_fun[$campi['USARFU']];
		
		$last_col = date6to8_rev($campi['UPPSOD']).$campi['UPPSOT'];
		
		$writeRow = array(
			$campi['UPUPRF'],
			$this->des_utente[$campi['UPUPRF']],
			$campi['USEMAI'],
			$campi['USARFU'],
			$des_area_fun,
			$campi['USDESC'],
			$campi['USENTE'],
			$campi['USNAZI'],
			$campi['USSTAT'],
			$last_col
		);
		
		return $writeRow;
	}
	
}

?>