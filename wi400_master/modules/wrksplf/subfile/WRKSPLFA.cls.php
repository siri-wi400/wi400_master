<?php
class WRKSPLFA extends wi400CustomSubfile {

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
		$array['SPOOLNBR']=$db->singleColumns("3", "4", "0", "Numero spool" );
		$array['SPOOLOUTQ']=$db->singleColumns("1", "10", "", "Outq" );
		$array['SPOOLPAGNBR']=$db->singleColumns("3", "4", "0", "Pagine" );
		$array['SPOOLSTA']=$db->singleColumns("1", "10", "", "Stato" );
		$array['SPOOLMODULO']=$db->singleColumns("1", "10" ,"","Modulo");
		$array['SPOOLUSRDATA']=$db->singleColumns("1", "10" ,"","Dati Utente");
		$array['SPOOLDATA']=$db->singleColumns("1", "8","", "Data Creazione Stampa" );
		$array['FATTURA']=$db->singleColumns("1", "10","", "Fattura" );
		return $array;
	}	
	
	public function init($parameters){
		global $connzend, $db;
		
		$this->setCols($this->getArrayCampi());
		// Questo subfile è autoinit e quindi scrivo il codice per reperire e scrivere le informazioni
	    $desc = $parameters["SPOOL_PARAMETER"];
		$this->statusArray = array('ZERO', 'RDY', 'OPN', 'CLO', 'SAV', 'WTR', 'HLD', 'MSGW', 'PND', 'PRT', 'FIN', 'SND', 'DFR');
		$this->spoolList = i5_spool_list($desc, $connzend);
	}
	
	public function body($campi, $parameters){
		global $connzend;

        if($spool_list = i5_spool_list_read($this->spoolList)) {
			$d = $spool_list['DATEOPEN'];
			$data = "20".substr($d,1,2).substr($d,3,2).substr($d,5,2);
			$stat = $this->statusArray[$spool_list['SPLFSTAT']];
			echo "<br>112:".$spool_list['SPLFSTAT'];
			// Reperisco il numero di fattura
			$str = i5_spool_get_data($spool_list['SPLFNAME'],$spool_list['JOBNAME'],$spool_list['USERNAME'],
			$spool_list['JOBNBR'],$spool_list['SPLFNBR']);
			$fattura =  substr($str,70, 10);
		    $writeRow = array( 
		    			$spool_list['SPLFNAME'],
		    			$spool_list['JOBNAME'],
		    			$spool_list['USERNAME'],
		    			$spool_list['JOBNBR'],
		    			$spool_list['SPLFNBR'],
		    			$spool_list['OUTQLIB'],
		    			$spool_list['PAGES'],
		    			$stat,
		    			$spool_list['FORMTYPE'],
		    			$spool_list['USERDATA'],
		          		$data,
		          		$fattura
		    			); 
			
			return $writeRow;
        }
		else {
			return false;
		}

	}
	public function end($subfile){
		i5_spool_list_close($this->spoolList);
	}
}
?>