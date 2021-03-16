<?php
class wi400Os400Spool  {
	
	private $user;
	private $formType;
	private $userDta;	
	private $entry;
	private $outq;
	private $previousEntry;
	private $userSpace = "SPOOLLIST QTEMP";
	private $userSpaceSpool = "SPOOLDATA QTEMP";
	private $datiTesta;
	private $formato;
	private $rtv_spc;
	private $offset;
	private $length;
	private $num;
	
	function __construct($user = '*ALL', $formType='*ALL', $userDta = '*ALL', $outQ="*ALL", $formato='SPLF0300') {
	   $this->user = $user;
	   $this->formType = $formType;
	   $this->userDta = $userDta;
	   $this->formato = $formato;	
	   $this->outQ = $outQ;   
	   $previousEntry = -1;

	}
	function getList() {
		global $connzend, $routine_path;
		
	    require_once $routine_path."/os400/APIFunction.php";
		// Creazione User Space!!
		$usr_spc = new wi400Routine('QUSCRTUS', $connzend);
	    $usr_spc->load_description();
		$usr_spc->prepare();
		$usr_spc->set('USERSPACE',$this->userSpace);
	    $usr_spc->set('INITSIZE', 1000);
	    $usr_spc->set('PUBAUT',"*ALL");
	    $usr_spc->set('REPLACE',"*YES");
	    $usr_spc->set('DESC',"USER SPACE CREATA DA PHP");
	    $usr_spc->call(True);
	    // Richiamo routine per lista degli spool
		$job_lsg = new wi400Routine('QUSLSPL', $connzend);
	    $job_lsg->load_description();
		$job_lsg->prepare();
		$job_lsg->set('USERSPACE',$this->userSpace);
	    $job_lsg->set('FORMAT', $this->formato);
	    $job_lsg->set('UTENTE',$this->user);
	    $job_lsg->set('FORMTYPE',$this->formType);
	    $job_lsg->set('USRDTA',$this->userDta);
	    $job_lsg->set('OUTQPA',$this->outQ);
	    $job_lsg->set('STATUS',"*ACTIVE");
	    $job_lsg->call(True); 
	    if ($job_lsg->getDSParm("API_ERR", "ERRBYTE")!="0") {
	    	die("Errore Reperimento DTAQ contattare Assistenza");
	    }
	    //die("ciao!");
	    // Recupero dati di testata
		$this->rtv_spc = new wi400Routine('QUSRTVUS', $connzend);
	    $tracciato = getApiDS("QUSRTVUS", "HEADER");	
	    $this->rtv_spc->load_description(null, $tracciato, True);
		$do = $this->rtv_spc->prepare();
		$this->rtv_spc->set('USERSPACE',$this->userSpace);
	    $this->rtv_spc->set('OFFSET', 1);
	    $this->rtv_spc->set('SIZE',300);
	    $do = $this->rtv_spc->call(True);
	    $dati = $this->rtv_spc->get('DATI');
	    $this->datiTestata = $dati;
	    // Resetto dati di testata per le prossime letture
	    $tracciato = getApiDS("QUSLSPL", $this->formato);
		$this->rtv_spc = new wi400Routine('QUSRTVUS', $connzend);	    
	    $this->rtv_spc->load_description(null, $tracciato, True);	    
		$do = $this->rtv_spc->prepare(True);
		$this->offset =  $dati['LDSECOFFSET']+1;
		$this->length =  $dati['ENTRYSIZE'];
		$this->num    =  $dati['ENTRYNUMBER']; 
	}
	/**
	 * @Desc Ritorna La successiva riga reperita
	 * @return $array Entry Array con i dati di una riga lista spool
	 */	
	function getEntry() {
		$currentEntry = $this->previousEntry + 1;
		if ($currentEntry > $this->num) {
		    return false;
		} else {
			$this->previousEntry++;	
			$dati = $this->readUserSpace();
			$dati['STATUS_DECODED']=$this->spoolStatus($dati['SPLFSTAT']); 	
		    return $dati;
		}
	}
	private function readUserSpace() {
        global $connzend;
		$this->rtv_spc->set('USERSPACE',$this->userSpace);
	    $this->rtv_spc->set('OFFSET', $this->offset);
	    $this->rtv_spc->set('SIZE',$this->length);
	    $do = $this->rtv_spc->call(True);
	    $this->offset += $this->length;
	    return $this->rtv_spc->get('DATI');
	}
	
	function getEntryNum() {
	    return $this->num;
	}
	private function spoolStatus($status) {
		$stato = array('1'=>"RDY",
		      '2'=>"OPN",
		      '3'=>"CLO",
		      '4'=>"SAV",
		      '5'=>"WTR",
		      '6'=>"HLD",
		      '7'=>"MSGW",
		      '8'=>"PND",
		      '9'=>"PRT",
		      '10'=>"FIN",
		      '11'=>"SND",
		      '12'=>"DFR");
		return $stato[$status];
	}
	static function getAttribute($jobQual='*', $spoolName="", $spoolNbr = -1, $formato='SPLA0100') {
        global $connzend, $routine_path;
	    require_once $routine_path."/os400/APIFunction.php";        
	    // Richiamo routine per lista degli spool
	    $tracciato = getApiDS("QUSRSPLA", $formato);	
		$job_lsg = new wi400Routine('QUSRSPLA', $connzend);
	    $job_lsg->load_description(null, $tracciato, True);
		$job_lsg->prepare();
		$job_lsg->set('JOBQUAL',$jobQual);
	    $job_lsg->set('FORMAT', $formato);
	    $job_lsg->set('SPOOLNAME', $spoolName);
	    $job_lsg->set('SPOOLNBR', $spoolNbr);
	    $job_lsg->set('SIZE', 1577);
	    $job_lsg->call(True);
	    return $job_lsg->get('DATI');		
	}
	static function getData($jobQual='*', $spoolName="", $spoolNbr = '*LAST', $formato='*PRTCTL') {
		global $routine_path, $connzend, $db,$settings;
		
		// Crazione Tabella
		$file = "SPOOLDATA_".session_id();
		$array = array();
		$array['SPOOLDATA']   = $db->singleColumns("1", "202" );
		$db->createTable($file, $settings['db_temp'], $array, True);
		// Recupero il nome da 10 per usare comandi di sistema
		$ID = $db->getSystemTableName($file, $settings['db_temp']);		
        // 
		$jobnumber = substr($jobQual, 20, 6);
		$jobuser = substr($jobQual, 10, 10);
		$jobname = substr($jobQual, 0, 10);
	    //$do =executeCommand("CRTPF",array("FILE"=>$file, "RCDLEN"=>200, "SIZE"=>"*NOMAX"), array(), $connzend);

		// Copia spool su file
	    //echo "<br>".$jobnumber."/".$jobuser."/".$jobname;
	    $separator = $settings['db_separator'];
	    $separator = "/";
		$do =executeCommand("CPYSPLF", array("FILE"=>$spoolName, "TOFILE"=>$settings['db_temp'].$separator.$ID,
			"JOB"=>trim($jobnumber)."/".trim($jobuser)."/".trim($jobname),"SPLNBR"=>$spoolNbr, "CTLCHAR"=>"*PRTCTL"), array(),$connzend);
		// Lettura dei dati
		$sql = "SELECT * FROM ".$settings['db_temp'].$settings['db_separator'].$ID;
		$result = $db->query($sql, False , 0);
		$dati = array();
		$posizione = 99999;
		$firstPage = True;
		while ($row = $db->fetch_array($result, null, false)) {
			        //$riga = e2a($row['SPOOLDATA']);
			        $riga = $row['SPOOLDATA'];
			        switch ($formato) {
			        	case "*PRTCTL":
			        		$dati[]=$riga;
			        		break;
			        	case "*HTML":
			        		$skip = substr($riga, 0 ,3);
			        		$space = substr($riga, 3, 1);
			        		$print = substr($riga, 4);
			        		$br = 0;
			        		//echo "<br>".$posizione;
        	        		//echo "<br>Skip ".$skip;
        	        		//echo "<br>Space ".$space;
			        		if ($skip != "   ") { 
			        		    if($skip < $posizione) {
				        			$posizione = $skip;
				        			if ($firstPage) {
						        			//$dati[]= '****** INIZIO PAGINA ********';
						        			$firstPage = False;
				        			} else {
						        			$dati[]= '<FONT COLOR="#de0021"><--------------------------------------- Salto Pagina ----------------------------------------></FONT>';				        				
				        			}
				        			$br = $skip;
			        		    } else {
			        		    	$br = $skip - $posizione;
			        		    }
			        		    $posizione = $skip;
			        		    //echo "<br>di qua!";
			        		}
			        		if ($space != " ") {
			        			//if (!is_numeric($space)) echo "<br>non numerico:".$space."-->";
			        			$br +=$space;
			        			$posizione = $posizione + $space;
			        			// Controllo se mettere strong
			        			if ($space == '0') {
			        				$newprint = "";
			        				for ($j=0; $j<strlen($print); $j++) {
			        					$printchar = substr($print, $j, 1);
			        					//echo "<br>".$printchar."->".$oldprint;
			        					if (($printchar == substr($oldprint, $j, 1)) && $printchar !="") {
				        					$newprint .= "<strong>".$printchar."</strong>";
				        				} else if (substr($oldprint, $j, 1) == "_"){
				        					$newprint .= "<u>".$printchar."</u>";
				        				}else{
				        					$newprint .= $printchar;
				        				}
			        				}
			        				$dati[count($dati)-1]=$newprint;
			        				break;
			        			}
			        		}
			        		if ($br > 1) {
				        		for ($i=1; $i<=$br; $i++) {
					        		$dati[]="";
				        		}
				        	}
			        		$dati[]=$print;
			        		
			        		$oldprint = $print;
			        		break;
			        		
			        }
		}
		// Cancellazione tabella temporanea.
		$db->freeResult($result);
		$sql = "DROP TABLE ".$settings['db_temp'].$settings['db_separator'].$ID;
		$db->query($sql);
		// Ritorno dei dati
		return $dati;
	}
	/**
	 * @desc Recupera gli attributi di una coda di stampa
	 * @param string $outqQual: Identificativo coda   oggetto   libreria
	 * @param unknown_type $formato
	 * @return string
	 */
	static function getOutqInfo($outqQual, $formato='OUTQ0100') {
		
        global $connzend, $routine_path;
        require_once $routine_path."/os400/APIFunction.php"; 
	    // Richiamo routine per lista degli spool
	    $tracciato = getApiDS("QSPROUTQ", $formato);
		$job_lsg = new wi400Routine('QSPROUTQ', $connzend);
	    $job_lsg->load_description(null, $tracciato, True);
		$job_lsg->prepare();
		$job_lsg->set('OUTQQUAL',$outqQual);
	    $job_lsg->set('FORMAT', $formato);
	    $job_lsg->set('SIZEDATA', 1000);
	    $job_lsg->call(True);
	    
	    return $job_lsg->get('DATI');
		
	}	
}