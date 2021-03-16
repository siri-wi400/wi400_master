<?php

class LAVORI_ATTIVI_LIST extends wi400CustomSubfile {
	
	private $job_attuale;
	private $ID_job;
	private $form;
	private $stmt_log;
	
	//private $stmt_des_ute;
	
	public function __construct($parameters){
		global $db, $connzend;
		
		$this->job_attuale = $parameters['JOB_ATTUALE'];
		$this->ID_job = $parameters['ID_JOB'];
		$this->form = $parameters['FORM'];
		
		// Query prepare
		$sql_log = "select * from ZSLOG where ZJOB=? AND ZUSR=? AND ZNBR=?";
		$this->stmt_log = $db->singlePrepare($sql_log);
		//$sql_des_ute = "select * from AASSTP3000/JPROFADF where NMPRAD=?";
		//$this->stmt_des_ute = $db->singlePrepare($sql_des_ute);		
	}
	
	public function getArrayCampi() {	
		global $db;
		
		$array = array();
		$array['JOBNAME']=$db->singleColumns("1", "20", "", "Nome Lavoro");
	    $array['USERNAME']=$db->singleColumns("1", "20", "", "Nome Utente");	
		$array['JOBNUM']=$db->singleColumns("1", "6", "", "Numero lavoro");
		$array['USER_ID']=$db->singleColumns("1", "10","", "ID Utente");
		$array['USER_DES']=$db->singleColumns("1", "50", "", "Descrizione Utente");
	    $array['IP']=$db->singleColumns("1", "20", "", "Indirizzo IP");
	    $array['SESSION']=$db->singleColumns("1", "26", "", "Sessione PHP");
	    
		return $array;
	}
	
	public function init($parameters){
		global $db, $connzend;
		
		$this->setCols($this->getArrayCampi());
	}
	
	public function start($subfile){
		global $db, $connzend, $settings, $routine_path;

	    require_once $routine_path."/os400/wi400Os400Job.cls.php";
	
		if($this->job_attuale===true) {
		
		    //$list = new wi400Os400Job("*");
		    $list = new wi400Os400Job('*');
		    $list->getList();
			$this->get_job_list($subfile, $list);		    		
			
		}
		else {
			$job_filter = explode(",",$settings['active_jobs']);
			$sbs_filter = explode(",",$settings['active_jobs_subsys']);
			//foreach($job_filter as $jobName) {
		    	$list = new wi400Os400Job('*ALL', "*ALL", '*ALL', '*ACTIVE' , 'JOBL0200');
			    $list->getList();					
				$this->get_job_list($subfile, $list, $sbs_filter);
				
			//}
		}
	}
	
	public function body($campi, $parameters) {
		return false;
	}
	
	private function get_job_list($subfile, $list, $sbs_filter=array()) {
		global $db, $connzend, $moduli_path, $architettura;
		
		require_once $moduli_path."/analisi/job_log_commons.php";
		
		if($list->getEntryNum() <= 0)
			return false;
				
		$ret = true;
		while($ret){
			$ret = $list->getEntry();
				
					
			if(is_bool($ret) && $ret===true)
				die("Errore reperimento dati");
			
			if(empty($ret))
				break;
			// Carico a video solo i lavori dei sottosistemi che mi interessano
			if (!in_array(trim(substr($ret['RETDATI_2']['R_DATA'],0 ,10)), $sbs_filter) && count($sbs_filter)>0) {
				continue;
			}	
			$dati = array();

			$result_log = $db->execute($this->stmt_log, array($ret['JOBNAME'],$ret['JOBUSER'],$ret['JOBNBR']));			
			//$user_id = $dati['USER_ID'];
			//$ip = $dati['IP'];
			$row_log = $db->fetch_array($this->stmt_log);
			$user_id = $row_log['ZSUTE'];
			$ip = $row_log['ZSIP'];
			$session = substr($row_log['ZFRE'],0,26);
			if($this->job_attuale!="") {
				$user_id = $_SESSION['user'];
				$ip = $_SESSION['IP'];
			}			
			//$result_des_ute = $db->execute($this->stmt_des_ute, array($user_id));
			$dati = $architettura->translate_user_info($architettura->retrive_user_info($user_id));
			
			//$des_ute = "";
	    	//if($array_des_ute = $db->fetch_array($this->stmt_des_ute))
	    	//	$des_ute = $array_des_ute['DSPRAD'];
			
			$writeRow = array(
				$ret['JOBNAME'],
				$ret['JOBUSER'],
				$ret['JOBNBR'],
				$user_id,
				$dati['DESCRIZIONE'],
				$ip,
				$session
			);
			
//			echo "ROW:<pre>"; print_r($writeRow); echo "</pre>";
				
			// Inserimento della riga nel subfile
			$subfile->write($writeRow);
		}
	}
	
}

?>