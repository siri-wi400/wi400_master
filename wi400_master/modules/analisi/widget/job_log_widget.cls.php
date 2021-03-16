<?php
class JOB_LOG_WIDGET extends wi400Widget {
	private $result = "SUCCESS";
	
	function __construct($progressivo) {
		$this->progressivo = $progressivo;
		$this->parameters['TITLE'] = "LAVORI ATTIVI";
		$this->parameters['ONCLICK'] = true;
		//$this->parameters['RELOAD'] = true;
		$this->parameters['INTERVAL'] = 3000;
	}
	
	public function getHtmlBody() {
		global $db, $settigs;
		
		//$this->removeColor = true;
		$dati = $this->parameters['BODY'];
		$html = "Lavori attivi: ".$dati[0]."<br/>
				Lavori in errore: ".$dati[1];
		//$this->removeColor = true;
		
		return $html;
	}
	
	function run() {
		global $db, $settings;
		
		$sottosistemi = $settings['active_jobs_subsys'];
		//LAVORI ATTIVI
		$sql_attivi = "SELECT COUNT(*) NUM FROM TABLE (QSYS2.ACTIVE_JOB_INFO('NO', '$sottosistemi', '','')) AS X";
		$result = $db->query($sql_attivi);
		$row_attivi = $db->fetch_array($result);
		//LAVORI IN ERRORE
		$sql_errore = "SELECT COUNT(*) NUM FROM TABLE (QSYS2.ACTIVE_JOB_INFO('NO', '$sottosistemi', '','')) AS X  WHERE X.JOB_STATUS='MSGW'";
		$result = $db->query($sql_errore);
		$row_errore = $db->fetch_array($result);
		
		if($row_attivi && $row_errore) {
			$this->parameters['TITLE'] = "LAVORI ATTIVI";
			$this->parameters['BODY'] = array($row_attivi['NUM'], $row_errore['NUM']);
			if($row_errore['NUM'] == 1) {
				$this->result = "ERROR";
			}else {
				$this->removeColor = true;
			}
		}else {
			$this->result = "ERROR";
		}
	
		return $this->result;
	}
}
