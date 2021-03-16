<?php

/**
 * @name wi400Connect
 * @desc Classe per connessione a I5COMD
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author Luca Zovi
 * @version 1.01 25/01/2010
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

class i5_connect {
	
	private $conn;
	private $ip_server;
	private $user;
	private $pass;
	private $options = null;
	private $persistent;
	private $LastErr;
	private $debug;
	private $private;
	private $conId;
	
	/**
	 * Costruttore della classe
	 * Il costruttore setta i parametri per il collegamento all'AS400
	 *
	 * @param string $ip_server 	: Inidirizzo IP di collegamento (default 127.0.0.1)
	 * @param string $user			: Utente di collegamenteo (default NOBODY)
	 * @param string $pass 			: Password di collegamento (default NODBODY)
	 * @param string $persistente 	: Indicare se creare una connessione persistente con P (default). Qualsiasi altro carattere per connessione normale
	 * @param boolean $debug		:
	 */
	public function __construct($ip_server = '127.0.0.1', $user="NOBODY", $pass="NOBODY", $persistent='P', $debug=False) {
		if($user == "" OR $pass == "")
			throw new Exception("Collegamento con utente di default");

		$this->ip_server = $ip_server;
		$this->user = $user;
		$this->pass = $pass;
		$this->persistent = $persistent;
		$this->debug = $debug;
		$this->private = false;
		$this->options = array();

	}
	
	/**
	 * La funzione permette di settare i parametri di collegamento
	 *
	 * @param string $job_name 			: job name (machine name by default)
	 * @param string $sql_naming		: to use doted (.) or slashed (/) notation in SQL requests
	 * @param string $decimal_point 	: to use dot or comma as decimal separator
	 * @param string $code_page_file	: to use specific code page
	 * @param string $init_lib 			: specifies initial library
	 * @param string $alias 			: ALIAS delle connessione
	 * @param string $timeout 			: Tempo di attesa prima di chiusura connessione se non usata
	 * @param boolean $private 			: 
	 * @param string $privateId			: 
	 */
	public function set_options($job_name = null, $sql_naming = null, $decimal_point = null, $code_page_file = null, $init_lib = null, $alias = null, $timeout=null, $private=False, $privateId=0) {
		$options = array(
			I5_OPTIONS_JOBNAME => $job_name,
			I5_OPTIONS_SQLNAMING    => $sql_naming,
			I5_OPTIONS_DECIMALPOINT => $decimal_point,
			I5_OPTIONS_CODEPAGEFILE => $code_page_file,
			I5_OPTIONS_INITLIBL     => $init_lib,
			//I5_OPTIONS_ALIAS        => $alias
		);
		
		// Parametro aggiunto dalla versione 2.6.1
		$options[I5_OPTIONS_IDLE_TIMEOUT] = "$timeout";
		if ($private ==True) {
			$this->conId = 0;
			if ($privateId!=0) {
				$this->conId = $privateId;
				//echo "<br>Trovata in sessione:".$_SESSION['connectionID'];
			}
			$options[I5_OPTIONS_PRIVATE_CONNECTION] = $this->conId;
			$this->private = true;
		}
		//$this->options = $options;
		foreach ($options as $key=>$value) {
			if (isset($options[$key])) {
				$this->options[$key] = $value;
			}
		}
	}
	
	/**
	 * Effettua la connessione
	 *
	 * @return resource/boolean   Ritorna resource se la connessione viene effettuata, False se ci sono stati dei problemi
	 */
	public function connect(){
        global $settings;
		// @todo se xmlservice no connessione
		if (isset($settings['i5_toolkit'])) { 
		if (isset($this->options)) {
			if ($this->persistent=='P'){
				$this->conn = i5_pconnect($this->ip_server, $this->user, $this->pass, $this->options);
				// Nel caso di connessione privata verifico se è andata a buon fine al limite la rilancio
				if ($this->private == True){
					if (is_bool($this->conn) && $this->conn == FALSE){
						$errorTab = i5_error();
						if (
							($errorTab['cat'] == 6 && ($errorTab['num'] == -12 || $errorTab['num'] == -1)) || 
							($errorTab['cat'] == 9 && ($errorTab['num'] == 285)) || 
							($errorTab['cat'] == 6 && ($errorTab['num'] == -17))
						){
							$this->conId = 0;
							$this->options[I5_OPTIONS_PRIVATE_CONNECTION] = $this->conId;
							$this->connect();
						} 
						else {
							print_r($errorTab);
							exit();
						}		
					} 
					else {
						//$ret = i5_get_property(I5_PRIVATE_CONNECTION, $this->conn);
						//echo "Connessione andata a buon fine".$ret;
						if ($this->conId == 0){
							//Session varaible was 0: Get connection ID and store it in session variable.
							$ret = i5_get_property(I5_PRIVATE_CONNECTION, $this->conn);
							if (is_bool($ret) && $ret == FALSE){
								$errorTab = i5_error();
								print_r($errorTab);
							} 
							else {
								// Connection ID is stored in session variable
								//echo "<br>Primo giro. Salvata in sessione:".$ret;
								$this->conId = $ret;
								//$_SESSION['connectionID'] = $ret;
							}
						} 
						else {
							    $ret = i5_get_property(I5_PRIVATE_CONNECTION, $this->conn);
								$this->conId = $ret;							    
							    //die("non devo passarci mai!!!");
						}
					}
				}
			} 
			else {
				$this->conn = i5_connect($this->ip_server, $this->user, $this->pass, $this->options);
			}		
		} 
		else {
			if ($this->persistent=='P'){
				$this->conn = i5_pconnect($this->ip_server, $this->user, $this->pass);
			} 
			else {
				$this->conn = i5_connect($this->ip_server, $this->user, $this->pass);
			}
		}
		
		if ($this->conn === false) {
			$this->LastErr = i5_errno()." ".i5_errormsg();
			if ($this->debug) 
				echo $this->getLastErr();
			return false;
		}
		return $this->conn;
		} else {
			return true;
		}
	}
	
	/**
	 * Recupero l'ultimo errore
	 * 
	 * @return resource
	 */
	function getLastErr(){
		return $this->LastErr;
	}
	
	/**
	 * Ritorna la connessione effettuata
	 *
	 * @return resource
	 */
	function returm_conn(){
		return $this->conn;
	}
	
	/**
	 * Ritorna l'ID privato della connessione
	 *
	 * @return privateId
	 */
	function getPrivateId() {
		return $this->conId;
	}	
	
	/**
	 * Effettua la disconnessione dal server
	 *
	 * @return boolean   True se effettuata correttamente, False se ci sono stati dei problemi.
	 */
	function disconnect(){
		if ($this->persistent!='P') {
			$close = i5_close($this->conn);
		}
		else{
			if ($this->private == True){
				$close = i5_pclose($this->conn);
			}
		}

		if ($close === false) {
			$this->LastErr = i5_errno()." ".i5_errormsg();
			if ($this->debug) 
				echo $this->LastError;
			return false;
		}
		return true;
	}
	
	/**
	 * Distruttore della funzione. Solo se la connessione non è persistente
	 * 
	 */
	function __destruct() {
		if (is_resource($this->conn) AND ($this->persistent!='P')) {
			i5_close($this->conn);
		}
	}
	/**
	 * Imposto la lista delle librerie. In aggiunta alle librerie passate vengono aggiunte quelle della configurazione base
	 *
	 * @param array $libraries Array con la lista delle librerie da ipostare
	 * @param resource $connessione  specificare una connessione. Se non passata usa quella di default 
	 */
	function add_to_librarylist($libraries,$connessione=Null) {
		global $settings;
        if (!isset($settings['i5_toolkit'])) {
        	return true;
        }
		$curlibl = "";
		$sys_lib = array();
		$sys_lib = explode(";",$settings['db_lib_list']);
		$sys_inf = array();
		foreach ($sys_lib as $valore){
			if (array_search($valore, $sys_inf) === False){
				$sys_inf[]=$valore;
			}
		}
		if (is_array($libraries)){
			foreach ($libraries as $valore){
				if (array_search($valore, $sys_inf) === False){
					$sys_inf[]=$valore;
				}
			}
		}
		// Aggiungo le librerie in POST
		$post_lib = array();
		if (isset($settings['db_post_lib_list'])){
			$post_lib = explode(";",$settings['db_post_lib_list']);
		}
		foreach ($post_lib as $valore){
			if (array_search($valore, $sys_inf) === False){
				$sys_inf[]=$valore;
			}
		}
		
		$curlibl .= " " . implode(" ",$sys_inf);
		// Verifico se sono diverse rispetto a quelle attuali, nel caso le applico 
		/*$ret = executeCommand("rtvjoba",array(),array("usrlibl" => "userlib"));
		$mylib="";
		if (isset($userlib)) {
		$mylib=str_replace(" ", "", $userlib);
		}
		$newlib=str_replace(" ", "", $curlibl);
		if ($mylib !== $newlib) {*/
            //$do = executeCommand("CALL QGPL/ZDT_ASP2");
            //echo "<br>di qua!";		
			$do =i5_command("chglibl",array("libl"=>$curlibl),array());
		//}
	}
  
}

?>