<?php
/**
 * @name db2as400.class.php Classe per accesso al DB2 AS400
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author Luca Zovi
 * @version 1.01 14/07/2008
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 * 
 * @update 10/08/2017 INIZIO GESTIONE PASSAGGIO DATI IN UTF8 SENZA CODIFICA 
 *    RICHIEDE UNIXODBC 2.3.4/Driver IBM I CLIENT 1.1.7/OS I 7.1 (con PTF)
 *    ? Da valutare cambio parametro Override CCSID per db2
 **/
require_once "db_interface.cls.php";
class DB2AS400 implements WI400_DB {
	private $db_host;
	private $db_user;
	private $db_pwd;
	private $db_name;
	private $link;
	private $lastQuery;
	private $lastError;
	private $debug;
	private $options;
	private $connType;
	private $libl;
	private $in_libl;
	private $log;
	private $callPGM;
	public  $type = "DB2";
    public  $DBAttribute = array(
    		"DB_SUPPORT_PAGINATION"=>False
    );
    public $PRAGMA_RESOLVE_TABLE = False;
    private $XMLServiceLib = 'XMLSERVICE';
    private $plug = 'iPLUG65K';	 // Gestito sul CONF
    // @todo gestione di più plug autoselezionati in base a quello che devo fare?!
	/**
	 * Settaggio parametri del DB
	 *
	 * @param $db_host      Nome DB su AS400. Verificare con WRKRDBDIRE il nome reale su AS400
	 * @param $db_user      Utente
	 * @param $db_pwd       Password
	 * @param $db_schema    Scheam di DB a cui connetteri
	 * @param $connzend     Connessione a zend
	 */
	function set($db_host, $db_user, $db_pwd, $db_name = Null, $connType ='T', $debug = false, $log=false)
	{
		$this->db_host = $db_host;
		$this->db_user = $db_user;
		$this->db_pwd = $db_pwd;
		$this->db_name = $db_name;
		$this->connType= $connType;
		$this->debug = $debug;
		$this->log = $log;
		$this->options =	array(
			'i5_naming'=>DB2_I5_NAMING_ON,
		);
	}
	function setConfigParm($configArray) {
		return True;
	}
	function getOptions($opzione) {
		return $this->options[$opzione];
	}
	/**
	 * Set DB ..
	 *
	 * @param string $db_host Database di connessione da WRKRDBDIRE ..
	 */	
	function setDB($db_host)
	{
		$this->db_host = $db_host;
	}
	/**
	 * Connessione al DB
	 *
	 * @return boolean  True se connessione effettuata, False se la connessione non viene efffettuata
	 */
	function connect($delay=True)
	{
		$msg="Click del ".date("dmYhis"). " No connessione";
		$this->sSq($msg);
		if ($delay=True) return true;
		// connect to DB2AS400
		$this->make_connection();

	}
	/**
	 * Connessione al DB
	 *
	 * @return boolean  True se connessione effettuata, False se la connessione non viene efffettuata
	 */
	function make_connection()
	{
		global $settings, $messageContext, $appBase;
			
		if (isset($settings['xmlservice_lib'])) {
			$this->XMLServiceLib=$settings['xmlservice_lib'];
			$this->plug=$settings['xmlservice_plug'];
		}
		
		if (!isset($this->link))
		{
			$msg="Click del ".date("dmYhis"). " connessione!";
			$this->sSq($msg);
			// @todo Capire perchè i file serializzati hanno i campi in utf sputtanati
			$tempoptions = $this->options;

			if (isset($settings['db_encode_input']) && $settings['db_encode_input']==True) {
				$tempoptions['i5_libl']=utf8_decode($tempoptions['i5_libl']);
			}
			//$tempoptions['i5_libl']=utf8_decode($tempoptions['i5_libl']);
			$tempoptions['i5_commit']=DB2_I5_TXN_NO_COMMIT;
			//$tempoptions['i5_override_ccsid']=1208;
			// connect to DB2AS400
			if ($this->connType=='T') {
				$this->link = db2_connect($this->db_host, "", "", $tempoptions);
			} else {
				$this->link = db2_pconnect($this->db_host, $this->db_user, $this->db_pwd, $tempoptions);
			}

			if ($this->link==False)
			{
				$this->lastError="Errore di connessione al server AS400: " . db2_conn_errormsg(). $this->db_host;
				trigger_error(db2_conn_errormsg());
				die("Errore di Connessione".db2_conn_errormsg());
				if ($this->debug) echo $this->lastError;
				return false;
			}
			// SPEED-UP - Sostituire con ZDT_SPEED2 se si sta usando XMLSERVICE .. non metto parametro perchè non è ancora chiaro come funziona
			// Connessione per richiamo routine
			if (isset($settings['xmlservice'])) {
				if (isset($settings['init_db_routine']) && $settings['init_db_routine']!="") {
					$this->query("CALL $this->db_name".$settings['db_separator'].$settings['init_db_routine']);
				}
				$stmt =  "call $this->XMLServiceLib".$settings['db_separator']."$this->plug(?,?,?,?)";
				$this->callPGM = $this->prepareStatement($stmt, 0, False);
				if ($this->callPGM === false) {
					//$messageContext->addMessage("LOG", "Prepare richiamo programma fallito");
					//if ($this->debug) 
						echo db2_stmt_errormsg();
		            //return false;
				}
				// Attacco la liste delle librerie
				if (isset($this->options['i5_libl'])) {
					if(!isset($settings['delay_library_list'])) {
					$InputXML    ="";
					$InputXML   .= "<?xml version='1.0'?><script>";
					if (isset($settings['base_asp']) && $settings['base_asp']!="" && $settings['base_asp']!="*ARCH"  && $settings['base_asp']!="*P13N") {
						$InputXML   .= "<cmd>SETASPGRP ASPGRP(".$settings['base_asp'].")</cmd>";
					}
					//$InputXML   .= "<cmd>CHGLIBL LIBL(".utf8_encode($this->options['i5_libl']).")</cmd>";
					$InputXML   .= "<cmd>CHGLIBL LIBL(".$this->options['i5_libl'].")</cmd>";
					$InputXML   .= "</script>";
					$OutputXML = callXMLService($InputXML);
					if (!strpos($OutputXML, "+++ success",0)) {
						// @ todo devo andare al login con messaggio
						if (isset($messageContext)) {
							$messageContext->addMessage("ERROR", "Errore Grave. Impossibile caricare il sistema informativo utente:" .$_SESSION['user'].", contattare Assistenza!");
							$_SESSION['user']="";
							session_destroy();
							//die ($appBase."index.php?t=LOGOUT");
							//header("Location: ".$appBase."index.php?t=LOGOUT");
							//exit();
							die("Errore Grave. Impossibile caricare il sistema informativo utente:" .$_SESSION['user'].", contattare Assistenza!");
						} else {
							session_destroy();
							die("Errore Grave. Impossibile caricare il sistema informativo utente:" .$_SESSION['user'].", contattare Assistenza!");
						}
						return false;
					}
				}
				}
			} else {
				$this->query("CALL $this->db_name".$settings['db_separator']."ZDT_SPEED");
			}
			return true;
		}

	}

	/**
	 * SET DB Schema
	 *
	 * @return boolean  True se lo schema viene settato, False se lo schema non viene settato o trovato
	 */
	function setSchema($schema=Null)
	{
		$this->make_connection();
		if (isset($schema)) {
			$this->db_name = $schema;
		}

		if ($this->db_name !="")
		{
			$query="SET CURRENT SCHEMA='".$this->db_name."'";
			if ($this->link != null){
				$do = $this->exec( $query);
				if ($do==False)
				{
					$this->lastError="Schema DB $this->db_name non selezionato";
					//if ($this->debug) echo $this->lastError;
					return false;
				}
			}
		}
		return True;
	}
	function exec($query, $option=null) {
		global $settings;
		if (isset($settings['db_encode_input']) && $settings['db_encode_input']==True) {
			$query= utf8_decode($query);
		}	
		if (isset($option)) {
			return db2_exec($this->link, $query, $option);
		} else {
			return db2_exec($this->link, $query);
		}
	}
	/**
	 * Esegue una query sul server. La libreria del file viene reperita in modo dinamico richiamando un programma sul server
	 * AS400 che restituisce la libreria in cui è presente il file sul sistema informativo precaricato. Il cursore viene impostato
	 * di default di tipo scrollabile per permettere la successiva paginazione.
	 *
	 * @param $sql    Statement SQL da eseguire
	 * @return $result  Ritorna un resultset se la query viene eseguita correttamente, False se errore
	 */
	function query($sql, $scrollable=True, $optimize=10, $startFrom=null)
	{
		global $settings;
		
		if ($optimize>0) {
			$optimize++;
		}
		
		$drop=False;
		$this->make_connection();
		// Automaticamente se nella query ho un file in QTEMP dirotto su XMLSERVICE
		// Se si tratta di una SELECT aggiungiamo l'optimize per velocizzare l'apertura
		if ((strtoupper(substr($sql, 0, 6))=="SELECT") && $optimize>0){
			if ($this->getDBAttribute("DB_SUPPORT_PAGINATION")==True && $startFrom>0) {
				$sql .= " OFFSET $startFrom ROWS";
			}
			$sql .= " OPTIMIZE FOR $optimize ROWS";
		} elseif ((strtoupper(substr($sql, 0, 4))=="DROP")) {
			$drop = True;
		}
		if ($settings['ccsid']=='280') {
			$sql = str_replace("!!", "||", $sql);
		}
//		echo "DB2AS400 - SQL: ".strtoupper($sql)."<br>";
		$pos_where = strpos(strtoupper($sql), "WHERE");
//		echo "DB2AS400 - POS WHERE: $pos_where<br>";
		if($pos_where!==false)
			$sql_tmp = substr($sql, 0, $pos_where);
		else
			$sql_tmp = $sql;
//		echo "DB2AS400 - SQL TMP: ".strtoupper($sql_tmp)."<br>";		
//		if (strpos(strtoupper($sql), "QTEMP")!==false) {
		$sql = $this->resolveTable($sql);
		if (strpos(strtoupper($sql_tmp), "QTEMP")!==false) {
			$this->lastQuery = $sql;
			return queryQTEMP($sql, $scrollable, $optimize);
		}
		if ($scrollable) {
			$option = array('cursor' => DB2_SCROLLABLE);
			$result = $this->exec( $sql, $option);
		} else {
			$result = $this->exec( $sql);
		}

		if (!$result && $drop===False)
		{
			$this->lastError="Query Error: " .db2_stmt_errormsg()." ".$sql;
			global $messageContext;
			if (isset($messageContext)) {
				$messageContext->addMessage("ERROR",db2_stmt_errormsg());
				$messageContext->addMessage("LOG",db2_stmt_errormsg(). "-". $sql);
			}
			if ($this->debug) echo $this->lastError." query:".$sql;
			return false;
		}
		$this->lastQuery = $sql;
		$this->sSq($sql);
		return $result;
	}

	/**
	 * Esegue una query sul server per ottenere un singolo risultato. La single query è più veloce inquanto
	 * aggiunge la clausula FETCH FIRST ROW ONLY e quindi reperisce solo una riga senza scorrere tutto il file
	 * La libreria del file viene reperita in modo dinamico richiamando un programma sul server
	 * AS400 che restituisce la libreria in cui p presente il file sul sistema informativo precaricato. Il cursore viene impostato
	 * di default di tipo scrollabile per permettere la successiva paginazione.
	 *
	 * @param $sql    Statement SQL da eseguire
	 *
	 * @return $result  Ritorna un resultset se la query viene eseguita correttamente, False se errore
	 */
	function singleQuery($sql)
	{
		global $settings;
		
		$sql .= " FETCH FIRST ROW ONLY OPTIMIZE FOR 1 ROWS WITH UR";
		$this->make_connection();
		if ($settings['ccsid']=='280') {
			$sql = str_replace("!!", "||", $sql);
		}
//		echo "SQL: $sql<br>"; die();		
//		echo "DB2AS400 - SQL: ".strtoupper($sql)."<br>";
		$pos_where = strpos(strtoupper($sql), "WHERE");
//		echo "DB2AS400 - POS WHERE: $pos_where<br>";
		if($pos_where!==false)
			$sql_tmp = substr($sql, 0, $pos_where);
		else
			$sql_tmp = $sql;
//		echo "DB2AS400 - SQL TMP: ".strtoupper($sql_tmp)."<br>";
//		if (strpos(strtoupper($sql), "QTEMP")!==false) {
		$sql = $this->resolveTable($sql);
		if (strpos(strtoupper($sql_tmp), "QTEMP")!==false) {
			$this->lastQuery = $sql;
			return queryQTEMP($sql, False, 1);
		}		
		$result = $this->exec( $sql); // Cursore normale per lettura, array('cursor' => DB2_SCROLLABLE));
		if (!$result)
		{
			$this->lastError="Query Error: " .db2_stmt_errormsg()." ".$sql;
			
			
			global $messageContext;
			if (isset($messageContext)) {
				$messageContext->addMessage("ERROR",db2_stmt_errormsg());
				$messageContext->addMessage("LOG",db2_stmt_errormsg());
			}
			if ($this->debug) echo $this->lastError." query:".$sql;
			return false;
		}
		$this->lastQuery = $sql;
		$this->sSq($sql);
		return $result;
	}
	function esegui($sql)
	{
		$this->make_connection();
		$this->exec($sql);
	}
	/**
	 * Preparazione all'esecuzione di una query di aggiornamento/inserimento dati su DB
	 *
	 * @param $file     String File da aggiornare
	 * @param $key      Array  Array di chiavi da utilizzare per l'aggiornamento
	 * @param $field    Array  Array di campi che devono essere aggiornati
	 *
	 * @return $stmt    Ritorna il prepare dello statement
	 */
	function prepare($operazione, $file, $key=null, $field=null, $numRows=1)
	{
		global $settings;
		$where ="";
		$values = "";
		$id ="";
		$filagg ="";
		$filagg=$file;
		$this->make_connection();
		if ($operazione == "COUNTER"){
			// Custom prepare

			$sql = "SELECT COUNT(*) AS COUNTER FROM ". $filagg ." WHERE ".$key;

		}else{

			// Preparazione della chiave
			if (isset($key)){
				if (count($key)>0)
				{
					$where = ' WHERE ';
					$and = ' ';
					foreach ($key as $chiave=>$values)
					{

						if ($values != "?") $values = "'".$values."'";
						$where.=$and.$chiave."=".$values;
						$and = ' AND ';
					}
				}
			}

			// Preparazione dei values
			if (count($field)>0){
				if ($operazione =="INSERT"){
					// Se sto costruendo una INSERT
					$values= "(";
					$id = ' (';
					$and = '';
					foreach ($field as $chiave)
					{
						$id.=$and.$chiave;
						$values.=$and.'?';
						$and = ',';
					}
					$values.=')';
					$id.=')';
					// Controllo se il prepare statement deve essere eseguito per N righe
					if ($numRows > 1)
					{
						$and ="";
						$stringa="";
						for ($i=0;$i<$numRows;$i++)
						{
							$stringa .=$and.$values;
							$and =",";
						}
						$values = $stringa;
					}
					$sql = "$operazione INTO $filagg $id VALUES $values";
				} else if ($operazione =="UPDATE"){
					// Se sto costruendo una UPDATE
					$values= ' SET ';
					$and = '';
					foreach ($field as $chiave)
					{
						$values.=$and.$chiave."=?";
						$and = ',';
					}

					$sql = "$operazione $filagg $values $where";
				} else if ($operazione =="SELECT") {
					// Se sto costruendo una SELECT
					$campi = "*";
					$and ="";
					if (isset($field))
					{
						if (count($field)>0)
						{
							$campi = "";
							foreach ($field as $chiave)
							{
								$campi.=$and.$chiave;
								$and = ',';
							}
						}
					}

					$sql = "$operazione $campi FROM $filagg $where";

				}
			}
			if ($operazione =="DELETE"){
				// Cancellazione
				$where = "";
				if(isset($key) && !empty($key)) {
					$where = ' WHERE ';
					$and = ' ';
					foreach ($key as $chiave=>$values)
					{
						$where.=$and.$values."=?";
						$and = ' AND ';
					}
				}
				$sql = "$operazione FROM $filagg $where";
			}
		}
		if (isset($settings['db_encode_input']) && $settings['db_encode_input']==True) {
			$sql= utf8_decode($sql);
		}	
		$stmt = db2_prepare($this->link, $sql);
		if (!$stmt)
		{
			$this->lastError="Prepare Error: " .db2_stmt_errormsg()." ".$sql;
			if ($this->debug) echo $this->lastError." query:".$sql;
			return false;
		}else{
			$this->lastQuery = $sql;
		}
		$this->sSq($sql);
		return $stmt;
			
	}
	function singlePrepare($sql, $optimize = 1, $only=False)
	{
		global $settings;
		$this->make_connection();
		if ($settings['ccsid']=='280') {
			$sql = str_replace("!!", "||", $sql);
		}			
		if ($optimize > 0 AND $only==False){
			$sql.= " OPTIMIZE FOR ".$optimize." ROW";
		} elseif ($only == True){
			$sql .= " FETCH FIRST ROW ONLY WITH NC";
		}
		if (isset($settings['db_encode_input']) && $settings['db_encode_input']==True) {
			$sql= utf8_decode($sql);
		}	
		$stmt = db2_prepare($this->link, $sql);
		$this->sSq($sql);
		return $stmt;
			
	}

	function prepareStatement($sql, $optimize = 0, $only=False){
		global $messageContext, $settings;
		
		$this->make_connection();
		if ($optimize > 0 AND $only==False){
			$sql.= " OPTIMIZE FOR ".$optimize." ROW";
		} elseif ($only == True){
			$sql .= " FETCH FIRST ROW ONLY WITH NC";
		}
		
		if ($settings['ccsid']=='280') {
			$sql = str_replace("!!", "||", $sql);
		}
		if (isset($settings['db_encode_input']) && $settings['db_encode_input']==True) {
			$sql= utf8_decode($sql);
		}
		$stmt = db2_prepare($this->link, $sql);
		if (!$stmt) {
			if (isset($messageContext)) {
				$messageContext->addMessage("LOG",db2_stmt_errormsg());
			}
		}
		$this->sSq($sql);
		return $stmt;
	}
	/**
	 * Esegue il bind dei parametri di un prepare statemente
	 *
	 * @param $stmt    Risorsa ritornata da una prepare
	 * @param $id      ID del parametro
	 * @param $key     Chiave del parametro
	 * @param $value   Valroe del parametro
	 *
	 * @return Risultato   True eseguito, False errore
	 */	
	function bind_param($stmt, $id, $key, $type=DB2_PARAM_IN) {
		
		global $messageContext;
		
		$ret = db2_bind_param ($stmt, $id, $key, $type);		
		if (!$ret){			
			$this->lastError="Execute prepare Error: " .db2_stmt_errormsg();
			$messageContext->addMessage("LOG",db2_stmt_errormsg());
			if ($this->debug) {
				echo $this->lastError;
			}
			return false;
		}
	}
	/**
	 * Inserisce o aggiorna dati in seguito ad una prepare
	 *
	 * @param $stmt    Risorsa ritornata da una prepare
	 * @param $array   Array contenente i campi da aggiornare
	 *
	 * @return Risultato   True eseguito, False errore
	 */
	function execute($stmt, $campi = array(), $utfDecode=False)
	{
		global $messageContext, $settings;
		//$this->sSq('values'.implode(";", $campi));
		if (isset($settings['db_encode_input']) && $settings['db_encode_input']==True) {
			$utfDecode=True;
		}
		if ($utfDecode) {
			foreach ($campi as $key => $value) {
				if (isset($value)) {
					$campi[$key]=utf8_decode($value);
				}
			}
		}
		$result = db2_execute($stmt, $campi);
		if (!$result)
		{
			$this->lastError="Execute prepare Error: " .db2_stmt_errormsg();
			if (isset($messageContext)) {
 				$messageContext->addMessage("LOG",db2_stmt_errormsg());
			}
			if ($this->debug) {
				echo $this->lastError;
				print_r($campi);
			}
			return false;
		}

		return $result;
	}
	/**
	 * Legge le righe din un cursore SQL
	 *
	 * @param $result    Resultset di una query eseguita
	 * @param $row_number[optional]  Numero di riga da leggere
	 *
	 * @return array     Array con i campi del record letto
	 */
	function fetch_array($result, $row_number = Null, $trim=True)
	{	
		global $settings, $messageContext;
		// create an array called $row
		if (is_string($result)) {
			return fetchQTEMP($result, $row_number, $trim);
		}
		if ($row_number) $this->currentRecord = $row_number;
		if (get_resource_type($result)=="DB2 Statement") {
			//$row = db2_fetch_both($result, $row_number);
			$row = db2_fetch_assoc($result, $row_number);
			if (!$row) {
				//echo db2_stmt_errormsg();
				$errore = db2_stmt_error();
				if ($errore != "") {
					if (isset($messageContext)) {
 						$messageContext->addMessage("LOG",db2_stmt_errormsg());
 						$messageContext->addMessage("ERROR",db2_stmt_errormsg());
					}
				}
				//
			} 
			// No altrimenti mi tolgo i campi con *BLANK davanti!! Vedi conversione spool file
			// In aggiunta trasformo tutto in utf8 per non avre problemi
	        if($row && $trim)
			{
				$row2 = array();
				foreach (array_keys($row) as $chiave)
				{
					//$row[$chiave]=rtrim(utf8_encode($row[$chiave]));
					//$row2[utf8_encode($chiave)]=rtrim(utf8_encode($row[$chiave]));
					if (isset($settings['db_encode_output']) && $settings['db_encode_output']==True) {
						$row2[utf8_encode($chiave)]=rtrim(utf8_encode($row[$chiave]));
					} else {
						$row2[$chiave]=rtrim($row[$chiave]);
						//echo "<br>".$chiave." CODING:".mb_detect_encoding($str). " - ".$row[$chiave];
					}
				}
				$row = $row2;
			}
			return $row;
		} else {
			developer_debug("Fetch_array con result vuoto o nulla");
		}
	}

	/*
	 function num_rows($result)
	 {
		return $this->getrownumber($this->lastQuery, $result);

		}
		*/
	function num_rows($result, $caso=1)
	{
//		echo "LAST:".$this->lastQuery."<br>";		
		return $this->getrownumber($this->lastQuery, $result, $caso);

	}
	
	function get_last_query() {
		return $this->lastQuery;
	}

	function freeResult($result)
	{

		db2_free_result($result);

	}
	function freestmt($stmt)
	{

		db2_free_stmt($stmt);

	}

	/**
	 * Cerca di reperire il numero relativo di record dell'ultimo record inserito nel DB
	 *
	 * @return $integer   Numero relativo di record
	 */
	function insert_id()
	{
		$last_id = 0;

		return $last_id;
	}
	/**
	 * Restituisce il numero campi presenti in un result set
	 *
	 * @param $result    Result set di un query.
	 *
	 * @return $integer   Numero campi
	 */
	function num_fields($result)
	{
		$result = db2_num_fields($result);
		return $result;
	}

	/**
	 * Restituisce il numero di righe selezionate dall'ultimo SQL eseguito. Nel caso di operazione UPDATE o DELETE
	 * esiste una specifica funzione del DB2. Nel caso di SELECT occorre rieseguire lo statement modificandolo con un
	 * COUNT(*). Attenzione al tempo di esecuzione di tale operazione
	 *
	 * @param $sql    Statement SQL di cui si vuole reperire il numero di righe
	 * @param optional $result    Result set di un query. Nel caso si voglia reperire il numero di righe di una UPDATE o DELETE
	 *
	 * @return $integer   Numero di righe
	 */
	//	function getrownumber($sql, $result = Null)
	function getrownumber($sql, $result = Null, $caso=1) {
//		echo "<b>SQL_ORIG:</b>$sql<br>";
		$mystring = strtoupper($sql);
		// Verifico il tipo di query passata
		$find = strpos($mystring, "ELECT");
		if ($find >0) {
//			$dadove = strpos($mystring, "FROM");
//			$group  = strpos($mystring, "GROUP BY");
//			$order  = strpos($mystring, "ORDER BY");
			// Elimino eventuale presenza di OFFSET;
			$findoff = strpos($mystring, "OFFSET ");
			if ($findoff > 0) {
				$findoffend = strpos($mystring, "ROWS", $findoff);
				$mystring = substr($mystring,0,$findoff-1).substr($mystring, $findoffend+4);
			}
							
			$dadove = strnpos($mystring, "FROM", $caso);
			$group  = strnpos($mystring, "GROUP BY");
			$order  = strnpos($mystring, "ORDER BY");
			$optimize = strnpos($mystring, "OPTIMIZE FOR");
				
			$len = strlen($mystring);
/*			
			if ($group > 0){
				$len = $group - $dadove;
			} else{
				if ($order > 0)
				$len = $order - $dadove;
			}
*/
			if ($order > 0)
				$len = $order - $dadove;
			else if ($optimize > 0)
				$len = $optimize - $dadove;	
				
//			$querycount = "SELECT COUNT(*) AS COUNTER ".substr($mystring , $dadove, $len);
			$querycount = "SELECT COUNT(*) AS COUNTER ".substr($sql , $dadove, $len);
			
			if($group > 0) {
				$querycount = "select count(*) AS COUNTER from (".$querycount.") as temp";
			}			
//			echo "QUERY_COUNT:$querycount<br>";

			// LZ sostituisco != con <>
			$find = strpos(strtoupper($querycount), "!=");
			if ($find >0) {
				$querycount = substr($querycount, 0,$find)."<>".substr($querycount, $find+2) ;
			}				
//			echo "<b>QUERY_FIND:</b>$querycount<br>";
				
			$this->sSq("ROW NMBER");
			//$totale = db2_exec($this->link, $querycount);
			//$totrec = db2_fetch_array($totale);
            $totale = $this->query($querycount);
            $totrec = $this->fetch_array($totale, Null, False);
            if (isset($totrec)) {
            	return $totrec['COUNTER'];
            }
            return 0;
            //
			if (db2_free_stmt($totale))
			return $totrec[0];
			else
			return 0;
		}
		else
		return db2_num_rows($result);
	}

	/**
	 * Reperisce il tipo di campo passato come parametro
	 *
	 * @param $result result set
	 * @param $field  string nome del campo
	 *
	 * @return $integer   Numero relativo di record
	 */
	function getField($result, $field)
	{
		global $settings;
		$dati = array();
		if (isset($settings['db_encode_output']) && $settings['db_encode_output']==True) {
			$name=utf8_encode(db2_field_name($result, $field));
		} else {
			$name = db2_field_name($result, $field);
		}
		$dati['NAME']= $name;
		$dati['DISPLAY_SIZE'] = db2_field_display_size($result, $field);
		$dati['TIPO'] = db2_field_type($result, $field);
		$dati['WIDTH'] = db2_field_width($result, $field);
		$dati['SIZE'] = db2_field_precision($result, $field);
		$dati['SCALE'] = db2_field_scale($result, $field);
		return $dati;

	}
	/**
	 * @desc rtvLibre : Recupera libreria tabella con SQL *NEW 11/08/2015 Al momento più lento di rtvLibre con routine
	 * @param string $tabella
	 */
	function rtvLibre ($tabella) {
		
		global $settings;
		static $statement;
		if (isset ( $_SESSION ['array_librerie'][$tabella])) {
			return $_SESSION ['array_librerie'][$tabella];
		} else {
			if (!isset($statement)) {
				$library = explode(" " ,trim($this->libl)); 
				$orderlist=" ORDER BY CASE TABLE_SCHEMA ";
				$sql = "SELECT TABLE_SCHEMA FROM QSYS2".$settings['db_separator']."SYSTABLES WHERE TABLE_NAME=? AND TABLE_SCHEMA IN ('".implode("','",$library)."')";
				$i=0; 
				foreach ($library as $key => $value) {
					$i++;
					$orderlist .= " WHEN '".trim($value)."' THEN $i ";
				}
				$orderlist .=" END";
				$sql = $sql.$orderlist;
				$statement = $this->singlePrepare($sql, 0 , True);
			}
			$do = $this->execute($statement, array($tabella));
			if ($do) {
				$libreria = $this->fetch_array($statement);
				//print_r($libreria);
			}
			$_SESSION ['array_librerie'][$tabella]=$libreria['TABLE_SCHEMA'];
			return $libreria['TABLE_SCHEMA'];
		}
	}
	/**
	 * Restituisce un array con i campi di una tabella passata come parametro
	 * Ogni elemento dell'array contiene un sottoarray con le caratteristiche del campo
	 *
	 * @param $tabella  string Tabella di cui recuperare i dati
	 *
	 * @return $array   Campi della tabella
	 */
	function columns($tabella, $column="", $only=False, $commento="", $libre=null)
	{
		global $connzend, $settings;
		static $stmtMulti;
 
		$field = array();
		$campi = array();
		if ($libre==null){
			$libre = rtvLibre($tabella, $connzend);
		}
		// Controllo se il file p stato serializzato in precedenza per recuperare il suo descrittore
		$filename = wi400File::getCommonFile( "serialize", "DB_" . $tabella . "_".$libre.".dat" );
		$desc = fileSerialized ( $filename );
		if (!$desc){	
			$this->make_connection();		
			// Prepare delle query per ottimizzare le prestazioni
			if (!isset($stmtMulti)){
					$sql = "SELECT  
					COLUMN_HEADING, COLUMN_NAME, COLUMN_TEXT,
                    DATA_TYPE, NUMERIC_SCALE, LENGTH, COLUMN_DEFAULT, IS_NULLABLE, 
                    DATETIME_PRECISION, CCSID 					
					FROM QSYS2".$settings['db_separator']."SYSCOLUMNS WHERE TABLE_NAME = ?	AND TABLE_SCHEMA = ? ORDER BY ORDINAL_POSITION";
					$stmtMulti = $this->prepareStatement ($sql);
				}
			$result = $this->execute ($stmtMulti, array (strtoupper ( $tabella ), $libre));
			$stmt=$stmtMulti;
			while ($row = $this->fetch_array($stmt))
			{
				// A seconda del tipo di dato imposto la lunghezza a video
				$dataType = $this->convertDataTypeString($row['DATA_TYPE']);
				$descrizione=$commento;
				if ($descrizione=="") $descrizione = $row['COLUMN_TEXT'];
				if ($descrizione=="") $descrizione = $row['COLUMN_HEADING'];
				if ($descrizione=="") $descrizione = $row['COLUMN_NAME'];
				// Valorizzazione array
				$field[$row['COLUMN_NAME']]=array(
	    		'DATA_TYPE'=>$dataType,
	    		'DATA_TYPE_STRING'=>$row['DATA_TYPE'],
	      		'NUM_SCALE'=>$row['NUMERIC_SCALE'],
	    		'LENGTH_PRECISION'=>$row['LENGTH'],
	    		'VIDEO_LENGTH'=>$row['LENGTH'],
	    		'BUFFER_LENGTH'=>$row['LENGTH'],
	    		'REMARKS'=>$descrizione,
				'HEADING'=>$row['COLUMN_HEADING'],
	    		'COLUMN_DEFAULT'=>$row['COLUMN_DEFAULT'],
	    		'IS_NULLABLE'=>$row['IS_NULLABLE'],
	    		'DATETIME_CODE'=>$row['DATETIME_PRECISION'],
				'CCSID' => $row['CCSID']
				);
			}
			put_serialized_file($filename, $field);
		} else {
			    $field = $desc;
		}	
		// Se ho richiesto una singola colonna ritorno l'array della colonna
		if ($column!="")
		{
			return $field[$column];
		}
		if (!$only) {
			return $field;
		} else {
			return array_keys($field);
		}
	}
	// Inizializzazione valori di default
	function inzDsValue($dataType) {
		global $db;
		$valore ="";
		switch($dataType) {
			case "INTEGER":
				$valore = 0;
				break;
			case "DECIMAL":
				$valore = 0;
				break;
			case "NUMERIC":
				$valore = 0;
				break;
			case "FLOAT":
				$valore = 0;
				break;
			case "TIMESTAMP":
				$valore = $db->getTimestamp("*INZ");
				break;
			case "TIMESTMP":
				$valore = $db->getTimestamp("*INZ");
				break;
			case "DATE";
			$valore = "0001-01-01";
			break;
		}
		return $valore;
	}
	function convertDataTypeString($string, $what='TO_NUMERIC') {

		static $array = array("CHAR"=>"1","DECIMAL"=>"2","NUMERIC"=>"3","DATE"=>"8", "TIME"=>"9", "VARCHAR"=>"12","INTEGER"=>"19", "TIMESTMP"=>"88");
	  
		if ($what=='TO_NUMERIC') {
			return $array[$string];
		} else {
			return array_search($string, $array);
		}
	}
	// Imposta le caratteristiche di una campo dai valori passati
	function singleColumns($type="1", $lunghezza, $decimali=null , $commento="")
	{
		$decimal_precision = $decimali;
		$data_type_string = $this->convertDataTypeString($type, $what='TO_STRING');
		
		return array(
    		'DATA_TYPE'=>$type,
    		'DATA_TYPE_STRING'=>$data_type_string,
	      	'NUM_SCALE'=>$decimal_precision,
    		'LENGTH_PRECISION'=>$lunghezza,
    		'VIDEO_LENGTH'=>$lunghezza,
    		'BUFFER_LENGTH'=>$lunghezza,
    		'REMARKS'=>$commento,
    		'COLUMN_DEF'=>"",
    		'NULLABLE'=>"N",
    		'DATETIME_CODE'=>""
    		);
	}
	// Aggiungo lista librerie per connessione SQL
	function add_to_librarylist($libraries, $forceCall=False)
	{
		global $settings;

		// Prima cosa setto l'ASP di sistema
		//$sql = "CALL QGPL".$settings['db_separator']."ZDT_ASP('SIRI1')";
		//$this->esegui($sql);
			
		$sys_lib = array();
		$sys_lib = explode(";",$settings['db_lib_list']);
		$sys_inf = array();
		foreach ($sys_lib as $valore)
		{
			$sys_inf[]=$valore;
		}
		if (is_array($libraries)){
			foreach ($libraries as $valore){
				//$sys_inf[]=$valore;
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

		// Attacco la QGPL
		if (array_search('QGPL', $sys_inf) === False){
			$sys_inf[]='QGPL';
		}
		// Attacco le librerie per le store procedure
		$stringa ="";
		$in_libl ="";
		$prefix =" ";
		foreach ($sys_inf as $elemento)
		{
			$stringa .= " ".trim($elemento);
			$in_libl .= $prefix."'".trim($elemento)."'";
			$prefix = ",";
			//$this->esegui($sql);
		}
		$this->libl = $stringa;
		$this->in_libl = $in_libl;
		$this->options['i5_libl']=$stringa;
		if ($forceCall) {
			$this->make_connection();
			$sql = "CALL QGPL".$settings['db_separator']."ZDT_LIBR('".trim($stringa)."')";
			$this->esegui($sql);
			// Se p attivo il supporto XML SERVICE attacco le librerie
			if (isset($settings['xmlservice']) && !isset($settings['delay_library_list'])) {
					$InputXML   = "<?xml version='1.0'?><script>";
					$InputXML   .= "<cmd>CHGLIBL LIBL(".$stringa.")</cmd>";
					$InputXML   .= "</script>";
					callXMLService($InputXML);
			}
		}
	}
	// Creazione di una tabella
	public function createTable($table, $libre="", $array, $checkExist = false, $not_null=false)
	{
		global $settings;

		if ($checkExist && $this->ifExist($table,$libre)){
			return;
		}else{

			$this->make_connection();
			$tableName= $table;
			$found = False;
			if ($libre!="") $tableName =$libre.$settings['db_separator'].$table;
			$sql = "CREATE TABLE ".$tableName." (";
			$sqlDescription = "LABEL ON ".$tableName." (";
			$virgola ="";
			$descrizioni = array();
			$dft = "";
			if($not_null===true)
				$dft = " NOT NULL WITH DEFAULT";
			// Imposto le colonne da creare
			foreach($array as $key=>$element)
			{
				$campo = $key;
				if ($element['DATA_TYPE'] =='1') $campo.= " CHAR(".$element['BUFFER_LENGTH'].")";
				if ($element['DATA_TYPE'] =='12') $campo.= " VARCHAR(".$element['BUFFER_LENGTH'] .")";
				if ($element['DATA_TYPE'] =='2') $campo.= " DEC(".$element['LENGTH_PRECISION'].", ".$element['NUM_SCALE'].")";
				if ($element['DATA_TYPE'] =='3') $campo.= " DEC(".$element['LENGTH_PRECISION'].", ".$element['NUM_SCALE'].")";
				$campo .=$dft;
				$sql.=$virgola.$campo;
				//$sql.=$virgola.$campo. " NOT NULL WITH DEFAULT";
				$virgola =",";
				if (isset($element['REMARKS']) && $element['REMARKS']!="") {
					$descrizioni[$key]=$element['REMARKS'];
					$found=True;
				}
			}
			$sql.=")";
			// Creo la tabella
			$stmt= $this->query($sql);
			db2_free_stmt($stmt);
			// Scrivo le eventuali descrizioni sulla tabella
			$virgola="";
			if ($found) {
				foreach($descrizioni as $key=>$element) {
					$sqlDescription .=$virgola. $key. " TEXT IS '$element'";
					$virgola = ",";
				}
				$sqlDescription.=")";
				$stmt= $this->query($sqlDescription);
					
			}
		}
	}
	// Controllo se esiste una tabella
	public function ifExist($table, $libre=null)
	{
		global $settings;
		$this->make_connection();
		if (isset($libre) AND $libre!="") {
			$sql = "select TABLE_SCHEMA from QSYS2".$settings['db_separator']."SYSTABLES where TABLE_SCHEMA ='$libre' and TABLE_NAME='".strtoupper($table)."'";
		} else {
			$sql = "select TABLE_SCHEMA from QSYS2".$settings['db_separator']."SYSTABLES where TABLE_SCHEMA IN(".$this->in_libl. ") and TABLE_NAME='".strtoupper($table)."'";
		}
		$result = $this->singleQuery($sql);
		$row = $this->fetch_array($result);
		if (isset($row['TABLE_SCHEMA']) AND ($row['TABLE_SCHEMA']!="")) {
			return true;
		}else{
			return False;	
		}
	}
	/**
	 * @desc Recupero il nome da 10 usato dal sistema per gli oggetti 
	 * @param string $file: Nome tabella (> 10)
	 * @param string $libre: Libreria
	 * @return string Codice da 10
	 */
	public function getSystemTableName($table, $libre) {
		global $settings;

		$this->make_connection();
		$sql = "select SYSTEM_TABLE_NAME FROM QSYS2".$settings['db_separator']."SYSTABLES where TABLE_SCHEMA ='$libre' and TABLE_NAME='".strtoupper($table)."'";
		$result = $this->singleQuery($sql, False);
		$row = $this->fetch_array($result);
		if ($row) return $row['SYSTEM_TABLE_NAME'];
		return "";		
	}
	// Controllo se esiste una tabella
	public function getTableDescription($table)
	{
		global $settings;

		$this->make_connection();
		$sql = "select TABLE_TEXT FROM QSYS2".$settings['db_separator']."SYSTABLES where TABLE_SCHEMA IN(".$this->in_libl. ") and TABLE_NAME='".strtoupper($table)."'";
		$result = $this->singleQuery($sql, False);
		$row = $this->fetch_array($result);
		if ($row) return $row['TABLE_TEXT'];
		return "";
	}
	
	/*Distruzione di tutte le tabelle temporanee create a livello di sessione
	 / @param $ID  string ID sessione in chiusura
	 */
	public function destroyTable($ID)
	{
		global $settings;
		$this->make_connection();
		
		$tmpDir = wi400File::getSessionFile($ID);
		if (file_exists($tmpDir)){
			$handler = opendir($tmpDir);
			while ($sessionFile = readdir($handler)) {
		        if (!is_dir($sessionFile) && strripos($sessionFile, ".".wi400Session::$_TYPE_SUBFILE) > 0){
					$subFile = wi400Session::loadFile($ID, $sessionFile);
		        	$subFile->drop();
		        }
		    }
		    closedir($handler);
		}
	}
	public function clearPHPTEMP ($session_id) {
		global $settings;
	
		$sql = "select TABLE_SCHEMA, TABLE_NAME from QSYS2".$settings['db_separator']."SYSTABLES where TABLE_SCHEMA ='".$settings['db_temp']."' and TABLE_NAME like'%".strtoupper($session_id)."%'";
		$result = $this->query($sql, False, 0);
		if ($result) {
			while ($row = $this->fetch_array($result)) {
				$sql1 = "DROP TABLE ".trim($row['TABLE_SCHEMA']).$settings['db_separator'].trim($row['TABLE_NAME']);
				$this->query($sql1);
			}
		}
	}
	/*
	 * Distruzione di tutte le tabelle temporanee create a livello di sessione
	 / @param $ID  string ID sessione in chiusura
	 */
	public function deleteTable($ID){
		global $settings;
		$this->make_connection();
		$tmpDir = wi400File::getSessionFile($ID);
		if (file_exists($tmpDir)){
			$handler = opendir($tmpDir);
			while ($sessionFile = readdir($handler)) {
				 if (!is_dir($sessionFile) && strripos($sessionFile, ".".wi400Session::$_TYPE_SUBFILE) > 0){
					$subFile = wi400Session::loadFile($ID, $sessionFile);
					$subFile->delete();
				 }
			}
			 closedir($handler);
		}
	}

	function sSq($sql)
	{
		global $settings;
		
		static $handle;
//		if ($this->debug) {
		if ($this->log) {
			$user ="NONE";
			if (isset($_SESSION['user']) && $_SESSION['user']!="") {
				$user = $_SESSION['user'];
			}
			$sessionId = session_id();
			if ($sessionId =="") $sessionId = "batch";
			$file = $settings['log_sql'].$user."_".$sessionId.".txt";
			if (!isset($handle)) {
					$handle = fopen($file, "a");				
			}
			fwrite($handle, date("[Y-m-d_H:i:s.u]").$sql."\r\n");
			//fclose($handle);
		}
	}
	// Distruttore della classe
	function __destruct()
	{
		if (!isset($this->link) && is_resource($this->link))
		{
			db2_close($this->link);
		}
	}
	function getLink() {
		return $this->link;
	}
	function getCallPGM() {
		if (!isset($this->callPGM)) {
			$this->make_connection();
		}
		return $this->callPGM;
	}	
	function field_display_size($resultSet ,$key ) {
		$size = db2_field_display_size ($resultSet ,$key );
		return $size;
	}
	function getColumnListFromTable($table, $libre) {
		
		global $settings;
		
		$sql = "SELECT COLUMN_NAME, LENGTH , DATA_TYPE, NUMERIC_SCALE, NUMERIC_PRECISION, COLUMN_TEXT FROM   
		QSYS2".$settings['db_separator']."SYSCOLUMNS WHERE TABLE_NAME = ? 
		AND TABLE_SCHEMA = ? ORDER BY ORDINAL_POSITION";
		$stmt = $this->prepareStatement ( $sql );
		$result = $this->execute ( $stmt, array (strtoupper ( $table ), $libre ) );
		$desc1 = array ();
		// Ciclo di costruzione e caricamento del descrittore della DS da utilizzare	
		while ( $info = $this->fetch_array ( $stmt ) ) {
			if ($info['COLUMN_NAME']!='NREL') {
			$description = $info ['COLUMN_TEXT'];
			if ($description=="") $description =  $info ['COLUMN_NAME'];	
			$mycol = new wi400Column ( $info ['COLUMN_NAME'], $description );
			// Campi Packed
			if ($info ['DATA_TYPE'] == 'DECIMAL' or $info ['DATA_TYPE'] == 'NUMERIC') {
				$len = $info ['NUMERIC_PRECISION'];
				$dec = $info ['NUMERIC_SCALE'];
				$mycol->setAlign ( 'right' );
				$mycol->setFormat (getNumericFormatoByDecimal($dec));			
			} // ID DATATYPE
			$desc1 [$info ['COLUMN_NAME']] = $mycol;
			}
		}
		// Ritorno il descrittore recuperato dalla routine
		return $desc1;
	}
		
	function getInfoDB() {
		global $settings;
		$query ="SELECT ZGETJOBA() AS JOBA FROM SYSIBM".$settings['db_separator']."SYSDUMMY1";
		$result = $this->singleQuery($query);
		$row= $this->fetch_array($result);
		return $row['JOBA'];
	}
	function resolveTable($sql, $trace = False) {
		global $connzend, $settings;
		if (isset($settings['auto_resolve_table'])) {
		$oriSql = $sql;
		$prova = True;
		$i=0;
		do  {
			if ($i>5) {
				break;
			}
			
			$stmt = db2_prepare($this->getLink(), $sql, array('deferred_prepare'=>DB2_DEFERRED_PREPARE_OFF));
			if (!$stmt) {
				//echo $this->link->errorCode();
				$error = db2_stmt_errormsg();
				$errorCode = trim(substr($error, 22, 8));
				if ($trace) {
					echo "<br>".db2_stmt_errormsg();
				}
				$pos = strpos($error, "SQL0204N");
				if ($pos!==False) {
					$find = strpos($error,'.');
					if ($find !==False) {
						$file = substr($error, $find+1, strpos($error, '"',$find+1)-$find-1);
						$fileArray[] = $file;
						$substArray[] = rtvLibre($file, $connzend).$settings['db_separator'].$file;
						if ($trace) {
							echo "<br>".rtvLibre($file, $connzend). " - ".$file;
							echo "<br>Prima:".$sql;
						}
						// Aggiunto StringToUpper perchp il controllo ritorna la tabella MAIUSCOLA
						$sql = str_replace($fileArray, $substArray, strtoupper($oriSql));
						if ($trace) {
							echo "<br>Dopo:".$sql;
						}
					}
				} else {
					$prova = False;
				}
			} else {
				$prova = False;
			}
			$i++;
		} while ($prova);
		//echo "<br>SQL:$sql";
		}
		return $sql;
	}
	function escapeSpecialKey($mixed) {
		//$query = str_replace("FILE", '"FILE"', $query);
		return $mixed;
	}
	function checkKeyField($field, $create =False) {
		$result = $field;
		if ($create===True) {
			if ($field =="FILE") $result ='"FILE"';
		} else {
			if ($field =="FILE") $result ='"FILE"';
		}
		return $result;
	}
	function getDBAttribute($attribute) {
		if (isset($this->DBAttribute[$attribute])) {
			return $this->DBAttribute[$attribute];
		} else {
			return null;
		}
	}
	function setDBAttribute($attribute, $valore) {
		$this->DBAttribute[$attribute]=$valore;
	}
	function getCurrentOpenFile($filter="") {
		global $settings, $db;
		// Funzione generica anche per PDO
		$dati = getCurrentOpenFile_AS400($filter="");
		return $dati;
	}
	function getTimestamp($data=Null, $short=Null) {
		global $settings;
		return getDb2Timestamp_AS400($data, $short);
	}
	function getTime($time) {
		global $settings;
		if ($time=="*INZ") {
			return "00:00:00.000000";
		} else {
			$unix = strtotime($time);
			return date("H:i:s.000000", $unix);
		}
	}
	function getSequence($fileNumeratore, $numeratore) {
		global $settings, $connzend;
		if ($fileNumeratore=="ZCNUMERI") {
			return getSequence_OS400($numeratore);
		} elseif ($fileNumeratore=="ZSYSNUME") {
			return getSysSequence_OS400($numeratore);
		}
	}
	static function create_descriptor($file, $connzend, $libre = Null, $desc = False) {
		global $db, $settings;
	
		static $stmt;
	
		if ($desc) {
			$name = 'COLUMN_TEXT';
		} else {
			$name = 'COLUMN_NAME';
		}
		// Verifico se ho già creato il descrittore del file e se esiste se per caso ha la data del giorno
		$putfile = False;
		// Se non mi è stata passata la libreria la cerco
		if (! isset ( $libre )) {
			$libre = rtvLibre ( $file, $connzend );
		}
		$filename = wi400File::getCommonFile ( "serialize", $libre . "_" . $file . ".dat" );
		$desc = fileSerialized ( $filename );
		if ($desc != null) {
			return $desc;
		}
		// Se arrivo qui devo ricaricre il descrittore e quindi apro il file per la scrittura
		// Accedo alla tabella su AS400 per recuperarne la struttura
		if (! isset ( $stmt )) {
			$sql = "SELECT COLUMN_NAME, LENGTH , DATA_TYPE, COALESCE(NUMERIC_SCALE, 0) AS NUMERIC_SCALE, COALESCE(NUMERIC_PRECISION, 0) AS NUMERIC_PRECISION, COALESCE(COLUMN_TEXT,'') AS COLUMN_TEXT FROM
	QSYS2".$settings['db_separator']."SYSCOLUMNS2 WHERE TABLE_NAME = ?
	AND TABLE_SCHEMA = ? ORDER BY ORDINAL_POSITION";
			$stmt = $db->prepareStatement ( $sql );
		}
		$result = $db->execute ( $stmt, array ($file, $libre ) );
		// Verifico se ho trovato qualcosa
		if (! $result) {
			return false;
		}
		$desc1 = array ();
		// Ciclo di costruzione e caricamento del descrittore della DS da utilizzare
		while ( $info = $db->fetch_array ( $stmt ) ) {
			// Campi Packed
			if ($info ['DATA_TYPE'] == 'DECIMAL') {
				$len = $info ['NUMERIC_PRECISION'];
				$dec = $info ['NUMERIC_SCALE'];
				$desc1 [$info[$name]] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_PACKED, "Length" => "$len.$dec" );
			}
			// Campi alfanumerici
			if ($info ['DATA_TYPE'] == 'CHAR') {
				$len = $info ['LENGTH'];
				$desc1 [$info[$name]] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_CHAR, "Length" => "$len" );
	
			}
			// Zoned
			if ($info ['DATA_TYPE'] == 'NUMERIC') {
				$len = $info ['NUMERIC_PRECISION'];
				$dec = $info ['NUMERIC_SCALE'];
				$desc1 [$info[$name]] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_ZONED, "Length" => "$len.$dec" );
			}
			// Integer
			if ($info ['DATA_TYPE'] == 'INTEGER') {
				$len = $info ['NUMERIC_PRECISION'];
				$dec = $info ['NUMERIC_SCALE'];
				$desc1 [$info[$name]] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_INT, "Length" => "$len.$dec" );
			}
			// Time
			if ($info ['DATA_TYPE'] == 'TIME') {
				$len = $info ['NUMERIC_PRECISION'];
				$dec = $info ['NUMERIC_SCALE'];
				$desc1 [$info[$name]] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_CHAR, "Length" => "8" );
			}
			// Date
			if ($info ['DATA_TYPE'] == 'DATE') {
				$len = $info ['NUMERIC_PRECISION'];
				$dec = $info ['NUMERIC_SCALE'];
				$desc1 [$info[$name]] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_CHAR, "Length" => "10" );
			}
			// TimeStamp
			if ($info ['DATA_TYPE'] == 'TIMESTMP') {
				$len = $info ['NUMERIC_PRECISION'];
				$dec = $info ['NUMERIC_SCALE'];
				$desc1 [$info[$name]] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_CHAR, "Length" => "26" );
			}
			// TimeStamp
			if ($info ['DATA_TYPE'] == 'FLOAT') {
				$len = $info ['NUMERIC_PRECISION'];
				$dec = $info ['NUMERIC_SCALE'];
				$desc1 [$info[$name]] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_FLOAT, "Length" => "26" );
			}
	
		}
		//db2_free_result($result);
	
		put_serialized_file($filename, $desc1);
		// Ritorno il descrittore recuperato dalla routine
		return $desc1;
	}
	
}
?>
