<?php
class WRKSPLF extends wi400CustomSubfile {

	private $spoolList;
	private $statusArray;
	
	public function __construct($parameters){
		
		global $connzend;

	}
	public function getArrayCampi() {	
		global $db;
		$array = array();
		$array['SPOOLNAME']=$db->singleColumns("1", "10", "", "Nome Spool" );
		$array['SPOOLJOB']=$db->singleColumns("1", "10", "", "Job Name" );
		$array['SPOOLUSER']=$db->singleColumns("1", "10", "", "Utente" );
		$array['SPOOLNUMBER']=$db->singleColumns("1", "6", "", "Numero lavoro" );
		$array['SPOOLNBR']=$db->singleColumns("3", "6", "0", "Numero spool" );
		$array['SPOOLOUTQ']=$db->singleColumns("1", "10", "", "Spool Outq" );
		$array['OUTQNAME']=$db->singleColumns("1", "10","", "Outq" );
		$array['SPOOLPAGNBR']=$db->singleColumns("3", "6", "0", "Pagine" );
		$array['SPOOLSTA']=$db->singleColumns("1", "10", "", "Stato" );
		$array['SPOOLMODULO']=$db->singleColumns("1", "10" ,"","Modulo");
		$array['SPOOLUSRDATA']=$db->singleColumns("1", "10" ,"","Dati Utente");
		$array['SPOOLDATA']=$db->singleColumns("1", "8","", "Data Creazione" );
		$array['OUTSTATUS']=$db->singleColumns("1", "8","", "Stato OUTQ" );
		$array['OUTWRITEJ']=$db->singleColumns("1", "28","", "Writer JOB" );
		$array['OUTJOBSTS']=$db->singleColumns("1", "4","", "Stato Writer" );
		$array['OUTTYPE']=$db->singleColumns("1", "7","", "Tipo Writer" );
		
		return $array;
	}	
	
	public function init($parameters){
		global $connzend, $db, $routine_path;
		
		$this->setCols($this->getArrayCampi());
		// Questo subfile è autoinit e quindi scrivo il codice per reperire e scrivere le informazioni
	    $desc = $parameters["SPOOL_PARAMETER"];
	    require_once $routine_path."/os400/wi400Os400Spool.cls.php";
	    $this->spoolList = new wi400Os400Spool($desc['username'], '*ALL', '*ALL', $desc['outq']);

	    $this->spoolList->getList();	    	    
		$this->statusArray = array('ZERO', 'RDY', 'OPN', 'CLO', 'SAV', 'WTR', 'HLD', 'MSGW', 'PND', 'PRT', 'FIN', 'SND', 'DFR');
		//$this->spoolList = i5_spool_list($desc, $connzend);
	}
	
	public function body($campi, $parameters){
		global $connzend,$db;
		
        //if($spool_list = i5_spool_list_read($this->spoolList)) {
        if($spool_list = $this->spoolList->getEntry()) {
			$d = $spool_list['DATEOPEN'];
			// Prepare per recupero info aggiuntive CODA
			$sql ="SELECT * FROM QSYS2.OUTPUT_QUEUE_INFO
			where
			 OUTPUT_QUEUE_NAME = '".$spool_list['OUTQNAME']."'
			AND
			 OUTPUT_QUEUE_LIBRARY_NAME = '".$spool_list['OUTQLIB']."'";
			$stmt = $db->singleQuery($sql);
			$outqData= $db->fetch_array($stmt);
			//showArray($outqData);die();
			$data = "20".substr($d,1,2).substr($d,3,2).substr($d,5,2);
			$stat = $this->statusArray[trim($spool_list['SPLFSTAT'])];
		    $writeRow = array( 
		    			$spool_list['SPLFNAME'],
		    			$spool_list['JOBNAME'],
		    			$spool_list['USERNAME'],
		    			$spool_list['JOBNBR'],
		    			$spool_list['SPLFNBR'],
		    			$spool_list['OUTQLIB'],
		    			$spool_list['OUTQNAME'],
		    			$spool_list['PAGES'],
		    			$stat,
		    			$spool_list['FORMTYPE'],
		    			$spool_list['USERDATA'],
		          		$data,
			    		$outqData['OUTPUT_QUEUE_STATUS'],
			    		$outqData['WRITER_JOB_NAME'],
			    		$outqData['WRITER_JOB_STATUS'],
			    		$outqData['WRITER_TYPE']
		    			); 
			
			return $writeRow;
        }
		else {
			return false;
		}

	}
	public function end($subfile){
		//i5_spool_list_close($this->spoolList);
	}
}
?>