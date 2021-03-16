<?php

class MONITOR_PROCESSI extends wi400CustomSubfile {
	
	private $stmt_job_active;
	
	public function __construct($parameters) {
		global $db;
		
		$sql = "SELECT V_JOB_STATUS, V_ACTIVE_JOB_STATUS                      
 				FROM TABLE(QSYS2.GET_JOB_INFO(?)) A";
		$this->stmt_job_active = $db->prepareStatement($sql); 
	}
	
	public function getArrayCampi() {	
		global $db;
		
		$array = array();
		$array['PROSID'] = $db->singleColumns('1', '40', '', 'Id sessione');
		$array['PROPID'] = $db->singleColumns('1', '20', '', 'Id php');
		$array['PROJOA'] = $db->singleColumns('1', '30', '', 'Id AS400');
		$array['PROJAD'] = $db->singleColumns('1', '30', '', 'Id DB');
		$array['PROSTA'] = $db->singleColumns('1', '1', '', 'Stato processo');
		$array['PROTIF'] = $db->singleColumns('1', '30', '', 'First click');
		$array['PROTIL'] = $db->singleColumns('1', '30', '', 'Last click');
		$array['PROTIE'] = $db->singleColumns('1', '30', '', 'End click');
		$array['PROUSR'] = $db->singleColumns('1', '40', '', 'Utente');
		$array['PROAZI'] = $db->singleColumns('1', '60', '', 'Azione');
		$array['PROFRM'] = $db->singleColumns('1', '60', '', 'Form');
		$array['PROURL'] = $db->singleColumns('1', '40', '', 'App base');
		$array['ATV_AS400'] = $db->singleColumns('1', '20', '', 'Lavoro attivo AS400');
		$array['STATUS_AS400'] = $db->singleColumns('1', '20', '', 'Stato lavoro AS400');
		$array['ATV_DB'] = $db->singleColumns('1', '20', '', 'Lavoro attivo DB');
		$array['STATUS_DB'] = $db->singleColumns('1', '20', '', 'Stato lavoro DB');
		//$array['PROFRM'] = $db->singleColumns('1', '60', '', 'Form');
		
	
		return $array;
	}
	
	public function init($parameters){
		global $db, $connzend;
		
		$this->setCols($this->getArrayCampi());
	}
	
	public function start($subfile) {
		global $db, $connzend;
		
		
	}
	
	public function body($campi, $parameters) {
		global $db;
		
		$writeRow = $this->getColsInz();
		foreach ($writeRow as $key => $value) {
			if (isset($campi[$key])) {
				$writeRow[$key]=$campi[$key];
			}
		}
		
		$rs = $db->execute($this->stmt_job_active, array($campi['PROJOA']));
		$row = $db->fetch_array($this->stmt_job_active);
		
		$writeRow['ATV_AS400'] = $row['V_JOB_STATUS'];
		$writeRow['STATUS_AS400'] = $row['V_ACTIVE_JOB_STATUS'];
		
		$rs = $db->execute($this->stmt_job_active, array($campi['PROJAD']));
		$row = $db->fetch_array($this->stmt_job_active);
		
		$writeRow['ATV_DB'] = $row['V_JOB_STATUS'];
		$writeRow['STATUS_DB'] = $row['V_ACTIVE_JOB_STATUS'];
		
		return $writeRow;
	}
	
}

?>