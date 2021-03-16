<?php

/**
 * @name wi400Routine.class.php 
 * @desc Classe per accesso alle routine applicativo SIAD
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author Luca Zovi
 * @version 1.01 14/07/2008   
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 * 
 * Da fare. Ritorno nome DS utilizzata dalla routine getDSName, getDSNumField
 * 
 */
 
class wi400Routine {
	
	private $prog;
	private $RPGProgram;
	private $parmout;
	private $parminp;
	private $desc;
	private $connzend;
	private $LastErr;
	private $autoreset;
	private $path;
	private $pathDefault;
	private $debug;
	private $package;
	private $dsCount;
	private $parmaaa;
	private $parmbbb;
	private $isPrepared = False;
	
	/**
	 * Costruttore della classe
	 *
	 * @param string $name			: Nome della routine da richiamare
	 * @param string $connzend		: ID della connessione a Zend
	 * @param boolean $autoreset	: Se Impostato a True resetta l'environment ad ogni richiamo successivo
	 * @param boolean $debug		: 
	 */
	function __construct($name, $connzend, $autoreset=True, $debug=True, $package='settings') {
		global $settings;
		
		$this->parminp = array();
		$this->parmout = array();
		$this->RPGProgram = $name;
		$this->connzend = $connzend;
		$this->autoreset = $autoreset;
		if ($package=='settings') {
			  $package = $settings['package'];
		}
		$this->package = $package;
		$this->path = 'base/package/'.$package.'/routine/';
		$this->pathDefault = 'base/package/default/routine/';
		
		$this->debug = $debug;
	}
	
	/**
	 * Funzione di caricamento del prototipo di chiamata. Vine cercato un script contenente le informazioni
	 * con il nome siad_<nome routine>.php
	 * 
	 * ATTENZIONE!: Tutti i descrittori esterni devono essere in minuscolo altrimenti non vengono inclusi.
	 * 
	 * @param string $description	: 
	 *
	 * @return boolean 	 Esito della chiamata: True eseguita con successo, False non eseguita per errori
	 */
	function load_description($description=null, $tracciato=null, $reset=False) {
		global $messageContext;
		// Controllo se è già in sessione
		$file = '';
		if ($reset == True) unset($_SESSION[$this->RPGProgram]);		
		if (isset($_SESSION[$this->RPGProgram])) {
			$this->desc = $_SESSION[$this->RPGProgram];
		} 
		else {
			// Cerco il descrittore, prima su package e se non trovato su package
			if (isset($description)){
				$file = p13n($this->path.$this->package."_".strtolower($description).".php");
				if (!$file) {
					$file = p13n($this->pathDefault.strtolower($description).".php");
				}
			} 
			else {
				$file = p13n($this->path.$this->package."_".strtolower($this->RPGProgram).".php");
				if (!$file) {
					$file = p13n($this->pathDefault.strtolower($this->RPGProgram).".php");
				}
			}
			if ($file) {
				include $file;
			} else {
				$export_description = '';
				if (isset($messageContext)) {
					$messageContext->addMessage("LOG","Program not Found:".$this->RPGProgram);
				} else {
					echo "<br>Program not Found:".$this->RPGProgram;
				}
			}
			$this->desc = $export_description;
			$_SESSION[$this->RPGProgram] = $export_description;
		}
		return true;
	}
	
	/**
	 * Preparazione del prototipo di chiamata
	 * La funzione prepara la chiamata e imposta gli arrai di input e di output costruendoli automaticamente
	 * dall'array del descrittore caricato con load_Description()
	 * 
	 * @return boolean	Esito della chiamata: True eseguita con successo, False non eseguita per errori
	 */
	function prepare($reset=False) {
		global $settings, $messageContext;
		
		if ($reset) {
			$this->isPrepared=False;
		}
		if (!$this->isPrepared) {
		$this->prog = i5_program_prepare("*LIBL".$settings['i5_sep'].$this->RPGProgram , $this->desc, $this->connzend);
		if ($this->prog === false) {
			$this->LastErr= i5_errno()." ".i5_errormsg();
			$messageContext->addMessage("LOG", $this->LastErr);
			if ($this->debug) 
				echo $this->LastErr;
            return false;
		}
		else {
			// Preparazione parametri di INPUT e di OUTPUT
			$this->parminp = $this->input_param($this->desc);
			$this->isPrepared=True;
			//$this->parmout = $this->output_param($this->desc);
		}
		return true;
		}
		return true;
	}
	
	/**
	 * Pulizia ultimo errore verificatosi all'interno della classe
	 * 
	 */
	function ClearLastErr() {
		$this->lastErr= null;
	}
	/**
	 * Chiamata effettiva del programma
	 * 
	 * @return boolean	Esito della chiamata: True eseguita con successo, False non eseguita per errori
	 */
	function call() {
        $this->reset();
		$this->ClearLastErr();
		//$prog = @i5_program_call($this->prog, $this->parminp, $this->parmout);
		$prog = i5_program_call($this->prog, $this->parminp, $this->parmout);	
		if ($prog === false) {
			if (trim(i5_errno()) !== "13262") {
				$this->LastErr= i5_errno()." ".i5_errormsg()." ".$this->RPGProgram;
						print_r(i5_error());
				if ($this->debug) 
					echo $this->LastErr;
				return false;
            }
			if (trim(i5_errno()) === "13262") 
				return True;
		} 
		// Valorizzo il tracciato di ritorno con i valori dei campi tornati
		foreach ($this->parmout as $key => $value) {
			if (isset($this->dsCount[$key]) && $this->dsCount[$key] > 1) {
					$newArray = $this->parmout[$key] = $$value;
				    for ($i=0; $i<$this->dsCount[$key]; $i++) {
				    	$this->parmout[$key][$i] = $newArray[$i];
					}	
			} else {
				$this->parmout[$key] = $$value;
			}	
		}
		return True;	
	}
	
	/**
	 * Settaggio parametri di input (NO DS)
	 *
	 * @param string $var	: Nome della variabile
	 * @param string $val	: Valore da settare
	 */
	function set($var, $val) {	
		if (isset($this->parminp[$var])) {
			$this->parminp[$var] = $val;
			return true;	
		} else {
			return false;
		}
	}
	
	/**
	 * Settaggio parametri di input per DS
	 *
	 * @param string $ds	: Nome della DS
	 * @param string $var	: Nome della variabile
	 * @param string $val	: Valore da settare
	 */
	function setDSParm($ds, $var, $val) {
		if (isset($this->parminp[$ds][$var])) {
			$this->parminp[$ds][$var] = $val; 
			return true;
		} else {
			return false;
		}	
	}
	
	function setDSParm_array($ds,$array) {
		$this->parminp[$ds] = $array;
	}
	
	/**
	 * Recupero valore dai parametri di output (NO DS)
	 * 
	 * @param string $var	: Nome della variabile
	 * 
	 * @return string	Contenuto della variabile
	 */	
	function get($var, $occurence=1) {
    	if (isset($this->dsCount[$var]) && $this->dsCount[$var]!=0) {
    	    if ($occurence == 0) {
    	        return $this->parmout[$var];
    	    } else {
    			return $this->parmout[$var][$occurence-1];
    	    }		
    	} else {
    		return $this->parmout[$var];
    	}
	}
	
	/**
	 * Recupero valore dai parametri di output di una DS
	 *
	 * @param string $ds	: Nome del parametro DS
	 * @param string $var	: Nome della variabile
	 * 
	 * @return string	Contenuto del campo all'interno della DS
	 */
    function getDSParm($ds, $var, $occurence=1) {
    	if ($this->dsCount[$ds]!=0) {
    		return $this->parmout[$ds][$occurence-1][$var];
    	} else {
    		return $this->parmout[$ds][$var];    		
    	}	
	}
	
	/**
	 * Recupero valore dai parametri di output di una DS
	 *
	 * @param string $ds	: Nome del parametro DS
	 * @param string $var	: Nome della variabile
	 * 
	 * @return string	Contenuto del campo all'interno della DS
	 */
    function getOutDSParm($ds, $var) {
		return $this->parminp[$ds][$var];
	}
	
	/**
	 * Recupero valore array di una DS
	 *
	 * @param string $ds	: Nome del parametro DS
	 * 
	 * @return array	Contenuto del campo all'interno della DS
	 */
	
    function getOutDS($ds) {
		return $this->parminp[$ds];
	}
	
	/**
	 * Inizializzazione DS di input	
	 * 
	 * @param string $ds	: Nome del parametro DS
	 */	
    function clearDS($ds) {
		
		return $this->parminp[$ds];
	}		
		
	/**
	* Recupero l'ultimo errore
	* 
	*/
	function getLastErr() {
		return $this->LastErr;
	}
	
	/**
	* Resetto l'environment della classe. Necessario per chiamate ricorsive. Da capire ????
	* 
	*/
	function reset() {
		$this->parmout = array();
		$this->parmout = $this->output_param($this->desc);
	}
	
	/**
	* Distruttore della classe
	* 
	*/	
	function __destruct() {
		@i5_program_close($this->prog);
	}
	
	/**
	 * Costruisce l'array dei parametri di output copiandoli dal descrittore passato come parametro
	 *
	 * @param string $description	: Descrittore prototipo parametri
	 * 
	 * @return array	Array di output
	 */	
	function output_param($description)	{

	    if (isset($this->parmaaa)) {
        	return $this->parmaaa;
        }		
		$parmaaa = array();
		foreach($description as $key) {
			if(isset($key['Name'])) {
				$parmaaa[$key['Name']]=$key['Name'];
			}
			else {
				$parmaaa[$key['DSName']]=$key['DSName'];
			}
		}
		$this->parmaaa=$parmaaa;		
		return $parmaaa;
	}
	
	/**
	 * Costruisce l'array dei parametri di input copiandoli dal descrittore passato come parametro
	 *
	 * @param string $description	: Descrittore prototipo parametri
	 * 
	 * @return array	Array di input
	 */		
	function input_param($description) {
		
        if (isset($this->parmbbb)) {
        	return $this->parmbbb;
        }
		$parmbbb = array();
		foreach($description as $key) {
			if(isset($key['Name'])) {
				if ($key['Type']=="0") 			
					$parmbbb[$key['Name']]="";
				else 
					$parmbbb[$key['Name']]="0";
			}
			else {
			 	$dsparm = array();
			 	$this->dsCount[$key['DSName']]= 0;
			 	foreach($key['DSParm'] as $key1) {
					if ($key1['Type']=="0") 
						$dsparm[$key1['Name']]="";
					else 
						$dsparm[$key1['Name']]='0'; 
				}
				if (isset($key['Count']) && $key['Count']>1) {
					        $newArray = array();
			 				$this->dsCount[$key['DSName']]= $key['Count'];					        
							for ($i=0; $i<$key['Count']; $i++) {
								$newArray[$i]=$dsparm;
							}	
							$dsparm = $newArray;
				}
				$parmbbb[$key['DSName']]=$dsparm;
			 }
		}  
		$this->parmbbb=$parmbbb;
		return $parmbbb;
	}
	
}

?>
