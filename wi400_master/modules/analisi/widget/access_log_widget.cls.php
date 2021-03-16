<?php
class ACCESS_LOG_WIDGET extends wi400Widget {
	private $result = "SUCCESS";
	
	function __construct($progressivo) {
		$this->progressivo = $progressivo;
		$this->parameters['TITLE'] = "ACCESSI";
		//$this->parameters['INTERVAL'] = "NONE";
		$this->parameters['ONCLICK'] = true;
		$this->parameters['RELOAD'] = true;
		
		$this->userParameters = array(
			"MAGAZZINO",
			"SOCIETA"
		);
	}
	
	public function getDetailParam($progressivo, $user) {
		global $db;
		$sql_get_param = "SELECT * FROM ZWIDGPRM WHERE WIDUSR='$user' and WIDAZI='ACCESS_LOG' and WIDPRG=".$progressivo;
		$rs = $db->query($sql_get_param);
		$valori = array();
		while($row = $db->fetch_array($rs)) {
			if(!isset($row['WIDKEY'])) $valori[$row['WIDKEY']] = array();
			$valori[$row['WIDKEY']][] = $row['WIDVAL'];
		}
		
		$param = $this->userParameters;
		$detail = new wi400Detail("PARAM_WIDGET", true);
		
		$myField = new wi400InputText($param[0]);
		$myField->setLabel("Magazzino");
		$myField->setValue($valori[$param[0]][0]);
		$detail->addField($myField);
		
		$myField = new wi400InputText($param[1]);
		$myField->setLabel("Societa");
		$myField->setValue($valori[$param[1]][0]);
		$detail->addField($myField);
		
		return $detail;
	}
	
	public function saveParams($progressivo, $user) {
		global $db;
		
		$result = true;
		$file = "ZWIDGPRM";
		$fields = getDS($file);
		$stmt_param = $db->prepare("INSERT", $file, null, array_keys($fields));
		
		$values = wi400Detail::getDetailValues("PARAM_WIDGET");
		
		foreach($this->userParameters as $param) {
			$val = $values[$param];
			if(!is_array($val)) $val = array($val);
			
			foreach($val as $riga => $ele) {
				$timestamp = getDb2Timestamp();
				$fields['WIDUSR'] = $user;
				$fields['WIDAZI'] = "ACCESS_LOG";
				$fields['WIDPRG'] = $progressivo;
				$fields['WIDKEY'] = $param;
				$fields['WIDVAL'] = $ele;
				$fields['WIDRIG'] = $riga;
				$fields['WIDSTA'] = '1';
				$fields['USRINS'] = $_SESSION['user'];
				$fields['TMSINS'] = $timestamp;
				$fields['USRMOD'] = $_SESSION['user'];
				$fields['TMSMOD'] = $timestamp;
				
				$rs = $db->execute($stmt_param, $fields);
				if(!$rs) $result = false;
			}
		}
		
		return $result;
	}

	
	public function getHtmlBody() {
		$dati = $this->parameters['BODY'];
		$html = "Accessi giornalieri: ".$dati[0];
		$this->removeColor = true;
		return $html;
	}
	
	function run() {
		global $db;
		$sql ="select count(*) as COUNT                        
				from ZSLOG                                      
				where ztime >='".date('Y-m-d')."-00.00.00.000000' and  
				      ztime <'".date('Y-m-d')."-23.59.59.000000'";
		$result = $db->query($sql);
		$row = $db->fetch_array($result);
		if($row) {
			$this->parameters['TITLE'] = "ACCESSI";
			$this->parameters['BODY'] = array($row['COUNT']);
		}else {
			$this->result = "ERROR";
		}
	
		return $this->result;
	}
}
