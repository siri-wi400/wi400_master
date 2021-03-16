<?php

class wi400UserSpaceFast {
	
    private $handle;
    private $descrittore;
    private $userspcae;
    private $position; 
    private $libl;
    private $len;
    private $io;
        
	public function __construct($userspace, $libl, $desc, $io='r') {
		global $settings;
	    $this->descrittore = $desc;
	    $this->userspace = $userspace;
	    $this->libl = $libl; 
	    $this->io = strtolower($io);
	    $asp= "";
	    if (isset($settings['base_asp']) && $settings['base_asp']!="") {
	    	$asp = "/".trim($settings['db_host']);
	    }
	    $this->handle = fopen("$asp/qsys.lib/".strtolower($libl).".lib/".strtolower($userspace).".usrspc", "$this->io");
	    //$this->handle = fopen("/qsys.lib/phpdemo.lib/fmafenti.file/fmafenti.mbr", "$this->io");
	    $this->len = DSlen($desc);
	    $this->position = 0;
	}
    
	public function get($start = 0) {
		// Se inizio da uno o comunque proseguo la lettura da dove ero arrivato sono già posizionato
		if ($start == $this->position) {
			$dati = fread($this->handle, $this->len);
			$this->position = $this->position + $this->len;
		// Altrimenti devo leggere tutto il contenuto fino al punto di arrivo	
		} else {
			fseek($this->handle, $start);
			$dati = fread($this->handle, $this->len);
			$this->position =  $start + $this->len;
		}
		return $array = string2DS($dati, $this->descrittore);
		
	}
	public function put($string, $start = 0) {
		// Se inizio da uno o comunque proseguo la lettura da dove ero arrivato sono già posizionato
		if ($start == $this->position) {
			$dati = fwrite($this->handle, $string, $this->len);
			$this->position = $this->position + $thislen;
			// Altrimenti devo leggere tutto il contenuto fino al punto di arrivo
		} else {
			fseek($this->handle, $start);
			$dati = fwrite($this->handle, $string, $this->len);
			$this->position =  $start + $this->len;
		}
		return $array = string2DS($dati, $desc1);
	
	}
	public function __destruct() {
			@fclose ($this->handle);
	}
  
}

?>