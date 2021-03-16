<?php
class wi400Os400Job  {
	
	private $username;
	private $job;
	private $nbr;
	private $jobqual;
	private $status;
	private $entry;
	private $previousEntry;
	private $userSpace = "JOBLIST   QTEMP";
	private $datiTesta;
	private $formato;
	private $rtv_spc;
	private $offset;
	private $length;
	private $num;
	private $block_read =30; // Minimo Blocco di dati da leggere 
	private $current_block_read = -1;
	private $array_fetch = array();
	/**
	 * Costruttore della classe
	 * @desc Classe per reperire una lista di Job su AS400
	 *
	 * @param string $job 			: Nome lavoro (Default *ALL) oppure *CURRENT jobname pr * questo lavoro
	 * @param string $username 		: Utente Lavoro (Default *ALL) oppure *CURRENT
	 * @param string $nbr			: Numero lavoro (Default *ALL) ammesso <nome*>
	 * @param string $status		: Stato lavoro (Default *ACTIVE) oppure *JOBQ,*OUTQ, *ALL
	 * @param string $formato 	 	: Formato API
	 */	
	function __construct($job = '*ALL', $username="*ALL", $nbr='*ALL', $status='*ACTIVE' ,$formato='JOBL0100') {
	   $this->username = $username;
	   $this->job = $job;
	   $this->nbr = $nbr;
	   $this->jobqual = str_pad($job, 10).str_pad($username, 10).str_pad($nbr, 6);
	   // Se richiesto lavoro attuale sbianco il resto 
	   if ($job =='*') {
	        $this->jobqual = '*';
	   }
	   $this->status = $status;
	   $this->formato = $formato;	
	   $previousEntry = -1;

	}
	/**
	 * @Desc Carico i dati per la successiva getEntry
	 */	
	function getList() {
		global $connzend, $routine_path;
		
	    require_once $routine_path."/os400/APIFunction.php";
	    $this->array_fetch = array();
	    $this->current_block_read = -1;
	    $this->block_read = 30;
		// Creazione User Space!!
		$usr_spc = new wi400Routine('QUSCRTUS', $connzend);
	    $usr_spc->load_description();
		$usr_spc->prepare();
		$usr_spc->set('USERSPACE',$this->userSpace);
	    $usr_spc->set('INITSIZE', 1000);
	    $usr_spc->set('PUBAUT',"*ALL");
	    $usr_spc->set('REPLACE',"*YES");
	    $usr_spc->set('DESC',"USER SPACE LISTA JOB");
	    $usr_spc->call(True);
	    // Richiamo routine per lista degli spool
		$job_lsg = new wi400Routine('QUSLJOB', $connzend);
		if ($this->formato=="JOBL0200") {
	    	$job_lsg->load_description('QUSLJOB_EXT', null, True);
		} else  {
			$job_lsg->load_description('QUSLJOB' ,null, True);
		}
		$job_lsg->prepare();
		$job_lsg->set('USERSPACE',$this->userSpace);
	    $job_lsg->set('FORMAT', $this->formato);
	    $job_lsg->set('STATUS',$this->status);
	    $job_lsg->set('JOBQUAL',$this->jobqual);
	    if ($this->formato=="JOBL0200") {
		    $job_lsg->set('JOBTYPE',"*");
		    $job_lsg->set('KEYNUMBER',"2");
		    $job_lsg->set("KEY_FIELD", array('KEYCODE_1'=>"101", 'KEYCODE_2'=>"1906"));	
	    }    
	    $job_lsg->call(True);
	    // Recupero dati di testata
		$this->rtv_spc = new wi400Routine('QUSRTVUS', $connzend);
	    $tracciato = getApiDS("QUSRTVUS", "HEADER");	
	    $this->rtv_spc->load_description(null, $tracciato, True);
		$do = $this->rtv_spc->prepare(True);
		$this->rtv_spc->set('USERSPACE',$this->userSpace);
	    $this->rtv_spc->set('OFFSET', 1);
	    $this->rtv_spc->set('SIZE',300);
	    $do = $this->rtv_spc->call(True);
	    $dati = $this->rtv_spc->get('DATI');
	    // Resetto dati di testata per le prossime letture
	    $this->num    =  $dati['ENTRYNUMBER'];
	    // Se il numero di entry e minore del blocco di lettura lo imposto come blocco di lettura
	    if ($this->num <= $this->block_read) $this->block_read = $this->num;
	    $tracciato = getApiDS("QUSLJOB", $this->formato);
		$this->rtv_spc = new wi400Routine('QUSRTVUS', $connzend);	    
	    $this->rtv_spc->load_description(null, $tracciato, True,  $this->block_read);	    
		$do = $this->rtv_spc->prepare(True);
		$this->offset =  $dati['LDSECOFFSET']+1;
		$this->length =  $dati['ENTRYSIZE']* $this->block_read;
		$this->num    =  $dati['ENTRYNUMBER']; 
	}
	/**
	 * @Desc Ritorna La successiva riga reperita
	 * @param  bool   Extend: Recupera le informazioni dettagliate del lavoro
	 * @return $array Entry Array con i dati di una riga lista spool
	 */	
	function getEntry($extend=False) {
		$currentEntry = $this->previousEntry + 1;
		if ($currentEntry > $this->num) {
			return false;
		} else {
			$this->previousEntry++;	
			$dati = $this->readUserSpace();
			if ($extend == True) {
				if ($dati['JOBNAME']=="") {
					echo "<br>Questo ".$this->previousEntry;
				}
				$key = str_pad($dati['JOBNAME'], 10).str_pad($dati['JOBUSER'], 10).str_pad($dati['JOBNBR'], 6);
				$dati2 = $this->getJobA($key);
				$dati = array_merge($dati, $dati2);
			}
		    return $dati;
		}
	}
	private function readUserSpace() {
        global $connzend;
        // @todo Ultimo giro leggere solo quelle che effettivamente mancano all'appello        
        // Controllo se leggere un ulteriore blocco di record dalla user space
        if ($this->current_block_read == -1 or $this->current_block_read >= $this->block_read) {
			$this->rtv_spc->set('USERSPACE',$this->userSpace);
		    $this->rtv_spc->set('OFFSET', $this->offset);
		    $this->rtv_spc->set('SIZE',$this->length);
		    $do = $this->rtv_spc->call(True);
		    $this->array_fetch=array();
		    $this->offset += $this->length;
		    if ($this->num==1) {
		    	$this->array_fetch[] = $this->rtv_spc->get('DATI');
		    } else {
		    	$this->array_fetch = $this->rtv_spc->get('DATI', 2);
		    }	
		    $this->current_block_read = 0;
        }
		$this->current_block_read++;
        return $this->array_fetch[$this->current_block_read-1];

	}
	
	function getEntryNum() {
	    return $this->num;
	}
	static function getJobA($jobQual='*', $formato='JOBI0200') {
        global $connzend;
	    // Richiamo routine per lista degli spool
	    $tracciato = getApiDS("QUSRJOBI", $formato);	
		$job_lsg = new wi400Routine('QUSRJOBI', $connzend);
	    $job_lsg->load_description(null, $tracciato, True);
		$job_lsg->prepare();
		$job_lsg->set('JOBQUAL',$jobQual);
	    $job_lsg->set('FORMAT', $formato);
	    $job_lsg->set('SIZE', 225);
	    $job_lsg->call(True);
	    
	    return $job_lsg->get('DATI');
		
	}
	static function getJobLogMsg($jobQual='*', $fetch_mode="*NEXT", $key='') {
		global $connzend;
		// Richiamo routine per lista degli spool
		$job_lsgm = new wi400Routine('zgetmsgl', $connzend);
		$job_lsgm->load_description();
		$job_lsgm->prepare();
		$job_lsgm->set('JOBQUAL',$jobQual);
		$job_lsgm->set('FETCH_MODE', $fetch_mode);
		$job_lsgm->set('KEY', $key);
		$job_lsgm->call();
		$dati = array();
		$dati['MESSAGE']=$job_lsgm->get('MESSAGE');
		$dati['KEY']=$job_lsgm->get('KEY');	
		return $dati;
	
	}
	static function getJobLog($jobQual='*') {
		global $routine_path, $connzend, $db,$settings;
		
		require_once $routine_path."/generali/conversion.php";
		
		$jobnumber = substr($jobQual, 20, 6);
		$jobuser = substr($jobQual, 10, 10);
		$jobname = substr($jobQual, 0, 10);
		$file = "JL".substr(session_id(),0,8);
		if ($jobname = '*') {
			$key = '*';
		} else {
			$key = trim($jobnumber)."/".trim($jobuser)."/".trim($jobname);
		}
	    //$do =executeCommand("CRTPF",array("FILE"=>$file, "RCDLEN"=>200, "SIZE"=>"*NOMAX"), array(), $connzend);
		// Copia spool su file
	    //echo "<br>".$jobnumber."/".$jobuser."/".$jobname;
		$do =executeCommand("DSPJOBLOG", array("JOB"=>$key,
		'OUTPUT'=>'*OUTFILE', 
		"OUTFILE"=>$settings['db_temp']."/".$file), array(),$connzend);
		$sql = "SELECT * FROM ".$settings['db_temp'].$settings['db_separator'].$file;
		$result = $db->query($sql, False , 0);
		$dati = array();
		while ($row = $db->fetch_array($result)) {
			//$dati = $row;
			if (isset($settings['xmlservice'])) {
			     $dati[]= e2a(trim($row['QMHMDT']));	
			} else {
				 $dati[]= trim($row['QMHMDT']);
			}	
		}	
		// Cancellazione Tabella
		$db->freeResult($result);
		$sql = "DROP TABLE ".$settings['db_temp'].$settings['db_separator'].$file;
		$db->query($sql);
        // Ritorno i dati del joblog
 		return $dati;
	}
}