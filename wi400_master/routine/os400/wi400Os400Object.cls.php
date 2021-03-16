<?php
class wi400Os400Object  {
	
	private $library;
	private $type;
	private $object;
	private $objandlib;
	private $entry;
	private $previousEntry;
	private $userSpace = "OBJECTLISTQGPL";
	private $userSpaceF = "QGPL/OBJECTLIST";
	private $datiTesta;
	private $formato;
	private $rtv_spc;
	private $offset;
	private $length;
	private $num;
	private $tracciato;
	private $parm_out;
	private $user_space_libl;
	private $user_space_name;

	/**
	 * Costruttore della classe
	 * @desc Classe per reperire una lista di oggetti di AS400 
	 *
	 * @param string $type 			: Tipo oggetto OS400 (Default *ALL)	 *
	 * @param string $library 		: Libreria Oggetto (Default *ALL)
	 * @param string $object		: Nome oggetto (Default *ALL) ammesso <nome*>
	 * @param string $formato 	 	: Formato API
	 * @param boolean $debug		:
	 */	
	function __construct($type="*ALL", $library = '*ALL', $object='*ALL', $formato='OBJL0200') {
	   $this->library = $library;
	   $this->object = $object;
	   $this->objandlib = str_pad($object, 10).str_pad($library, 10); 
	   $this->type = $type;
	   $this->formato = $formato;	
	   $previousEntry = -1;
	   $name = "U".strtoupper(substr(uniqid(),4,9));
	   $this->userSpace = $name."PHPTEMP";
	   $this->userSpaceF = "PHPTEMP/".$name; 
	   $this->user_space_libl="PHPTEMP";
	   $this->user_space_name = $name;
	}
	/**
	 * @Desc Carico i dati per la successiva getEntry
	 */	
	function getList() {
		global $connzend, $routine_path, $settings;
		
	    require_once $routine_path."/os400/APIFunction.php";
		// Creazione User Space!!
		$usr_spc = new wi400Routine('QUSCRTUS', $connzend);
	    $usr_spc->load_description();
		$usr_spc->prepare();
		$usr_spc->set('USERSPACE',$this->userSpace);
	    $usr_spc->set('INITSIZE', 1000);
	    $usr_spc->set('PUBAUT',"*ALL");
	    $usr_spc->set('REPLACE',"*YES");
	    $usr_spc->set('DESC',"USER SPACE LISTA SPOOL");
	    $usr_spc->call(True);
	    // Richiamo routine per lista degli spool
		$job_lsg = new wi400Routine('QUSLOBJ', $connzend);
	    $job_lsg->load_description();
		$job_lsg->prepare();
		$job_lsg->set('USERSPACE',$this->userSpace);
	    $job_lsg->set('FORMAT', $this->formato);
	    $job_lsg->set('OBJTYPE',$this->type);
	    $job_lsg->set('OBJANDLIB',$this->objandlib);
	    $job_lsg->call(True);
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
	    $tracciato = getApiDS("QUSLOBJ", $this->formato);
	    $this->tracciato = $tracciato;
	    $this->rtv_spc = userspace_prepare($this->userSpaceF, $tracciato, $connzend, 'R', 1, True);
		/*$this->rtv_spc = new wi400Routine('QUSRTVUS', $connzend);	    
	    $this->rtv_spc->load_description(null, $tracciato, True);	    
		$do = $this->rtv_spc->prepare(True);*/
		$this->offset =  $dati['LDSECOFFSET']+1;
		$this->length =  $dati['ENTRYSIZE'];
		$this->num    =  $dati['ENTRYNUMBER']; 
		foreach($this->tracciato as $key)
        	{
        		$parm_out[$key['Name']] = $key['Name'];
        	}
		$this->parm_out = $parm_out;
	}
	/**
	 * @Desc Ritorna La successiva riga reperita
	 * @return $array Entry Array con i dati di una riga lista spool
	 */	
	function getEntry() {
		$currentEntry = $this->previousEntry + 1;
		if ($currentEntry > $this->num) {
		    if (is_object($this->rtv_spc)) {
		    	$this->rtv_spc->__destruct();
	            	$ret = executeCommand('DLTUSRSPC USRSPC('.$this->user_space_libl.'/'.$this->user_space_name.')');
		    }
		    return false;
		} else {
			$this->previousEntry++;	
			$dati = $this->readUserSpace();
		    return $dati;
		}
	}
	private function readUserSpace() {
        global $connzend;

        $ret = userspace_get($this->rtv_spc, $this->parm_out, $this->offset);
		/*$this->rtv_spc->set('USERSPACE',$this->userSpace);
	    $this->rtv_spc->set('OFFSET', $this->offset);
	    $this->rtv_spc->set('SIZE',$this->length);
	    $do = $this->rtv_spc->call(True);
	    $this->offset += $this->length;*/
	    $this->offset +=  $this->length;
        return $ret;
	    //return $this->rtv_spc->get('DATI');
	}
	
	function getEntryNum() {
	    return $this->num;
	}
}