<?php

class BATCHJOB extends wi400CustomSubfile {
	
	private $zchkjob;

	public function __construct($parameters){
		global $connzend;
		
		$this->zchkjob = new wi400Routine('ZCHKJOB', $connzend);
	    $this->zchkjob->load_description();
	    
	    $this->path = $parameters['PATH'];
	}
	
	public function getArrayCampi() {	
		global $db;
		$array = array();
		$array['ID']=$db->singleColumns("1", "20", "", _t('JOB_ID'));
		$array['SESSIONE']=$db->singleColumns("1", "40", "", _t('SESSION'));
		$array['UTENTE']=$db->singleColumns("1", "40", "", _t('USER'));
		$array['NOME_LAVORO']=$db->singleColumns("1", "30", "", _t('JOB'));
		$array['DES_LAVORO']=$db->singleColumns("1", "100", "", _t('JOB_DES'));
		$array['TIMESUB']=$db->singleColumns("1", "26", "", _t('DATA_ORA_INS'));
		$array['TIMESTART']=$db->singleColumns("1", "26", "", _t('DATA_ORA_INI'));
		$array['TIMECOMPLETE']=$db->singleColumns("1", "26", "", _t('DATA_ORA_FIN'));				
		$array['STATO']=$db->singleColumns("1", "1", "", _t('STATO'));
		$array['STATO_BATCH']=$db->singleColumns("1", "10", "", _t('STATO_BATCH'));
		$array['FILECOL']=$db->singleColumns("1", "1", "", _t('FILES'));
		
		return $array;
	}	
	
	public function init($parameters){
		global $connzend, $db;
		
		$this->setCols($this->getArrayCampi());
	}
	
	public function body($campi, $parameters){
		global $connzend;
		
		$stato_batch = "";
		if($campi['STATO']=='2') {
			$job = str_pad($campi['ASJOB'],10).str_pad($campi['ASUSER'],10).str_pad($campi['ASJOBN'],6);
			
			$this->zchkjob->prepare();
			$this->zchkjob->set('JOB',$job);
		    $this->zchkjob->call();
		    
		    $stato_batch = $this->zchkjob->get('STATUS');
		}
		
		$stato = $campi['STATO'];
		
		$file_flag = "";
		if ($stato!="1") {
			$has_file = checkFilePresence($this->path.$campi["ID"], array($campi["ID"].".out", $campi["ID"].".txt"));
			if($has_file) {
				$stato = "9";
				$file_flag = "X";
			}
		}

	    $writeRow = array( 
			$campi['ID'],
			$campi['SESSIONE'],
			$campi['UTENTE'],
			$campi['NOME_LAVORO'],
			$campi['DES_LAVORO'],
			$campi['TIMESUB'],
			$campi['TIMESTART'],
			$campi['TIMECOMPLETE'],		
			$stato,
			$stato_batch,
    		$file_flag
		); 
			
		return $writeRow;
	}
	
}
?>