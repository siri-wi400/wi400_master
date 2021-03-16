<?php
/**
 * @name db2as400.class.php Classe per accesso al DB2 AS400
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author Luca Zovi
 * @version 1.01 14/07/2008
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 **/
class DB2_ODBC {
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
	public  $type = "ODBC";
    private $XMLServiceLib = 'XMLSERVICE';
    private $plug = 'iPLUGR65K';	 // Gestito sul CONF
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
		/*$this->options =	array(
			'i5_naming'=>odbc_I5_NAMING_ON,
		);*/
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
			// connect to DB2AS400
			$this->link = odbc_pconnect("AS400", $this->db_user, $this->db_pwd);
			/*if ($this->connType=='T') {
				$this->link = odbc_connect($this->db_host, "", "", $this->options);
			} else {
				$this->link = odbc_pconnect($this->db_host, $this->db_user, $this->db_pwd, $this->options);
			}*/

			if ($this->link==False)
			{
				$this->lastError="Errore di connessione al server AS400: " . odbc_errormsg(). $this->db_host;
				trigger_error(odbc_errormsg());
				die("Errore di Connessione".odbc_errormsg());
				if ($this->debug) echo $this->lastError;
				return false;
			}
//			$this->setSchema("PHPLIB");
			$this->setSchema($this->db_name);
			// SPEED-UP - Sostituire con ZDT_SPEED2 se si sta usando XMLSERVICE .. non metto parametro perchè non è ancora chiaro come funziona
			// Connessione per richiamo routine
			if (isset($settings['xmlservice'])) {
				if (isset($settings['init_db_routine']) && $settings['init_db_routine']!="") {
					$this->query("CALL $this->db_name".$settings['db_separator'].$settings['init_db_routine']);
				}
				$stmt =  "call $this->XMLServiceLib".$settings['db_separator']."$this->plug(?, ?, ?)";
				$this->callPGM = $this->prepareStatement($stmt, 0, False);
				if ($this->callPGM === false) {
					//$messageContext->addMessage("LOG", "Prepare richiamo programma fallito");
					//if ($this->debug) 
						echo odbc_errormsg();
		            //return false;
				}
				// Attacco la liste delle librerie
				if (isset($this->options['i5_libl'])) {
					$InputXML    ="";
					$InputXML   .= "<?xml version='1.0'?><script>";
					if (isset($settings['base_asp']) && $settings['base_asp']!="" && $settings['base_asp']!="*ARCH"  && $settings['base_asp']!="*P13N") {
						$InputXML   .= "<cmd>SETASPGRP ASPGRP(".$settings['base_asp'].")</cmd>";
					}
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
		if (isset($option)) {
			return odbc_exec($this->link, utf8_decode($query));
		} else {
			return odbc_exec($this->link, utf8_decode($query));
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
	function query($sql, $scrollable=True, $optimize=10)
	{
		global $settings;
		$drop=False;
		$this->make_connection();
		// Automaticamente se nella query ho un file in QTEMP dirotto su XMLSERVICE
		// Se si tratta di una SELECT aggiungiamo l'optimize per velocizzare l'apertura
		if ((strtoupper(substr($sql, 0, 6))=="SELECT") && $optimize>0){
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
			//$option = array('cursor' => odbc_SCROLLABLE);
			$result = $this->exec( $sql);
		} else {
			$result = $this->exec( $sql);
		}

		if (!$result && $drop===False)
		{
			$this->lastError="Query Error: " .odbc_errormsg()." ".$sql;
			global $messageContext;
			if (isset($messageContext)) {
				$messageContext->addMessage("ERROR",odbc_errormsg());
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
		
		$sql .= " FETCH FIRST ROW ONLY WITH NC";
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
		$result = $this->exec( $sql); // Cursore normale per lettura, array('cursor' => odbc_SCROLLABLE));
		if (!$result)
		{
			$this->lastError="Query Error: " .odbc_errormsg()." ".$sql;
			
			
			global $messageContext;
			if (isset($messageContext)) {
				$messageContext->addMessage("ERROR",odbc_errormsg());
				$messageContext->addMessage("LOG",odbc_errormsg());
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

		$stmt = odbc_prepare($this->link, utf8_decode($sql));
		if (!$stmt)
		{
			$this->lastError="Prepare Error: " .odbc_errormsg()." ".$sql;
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
		$stmt = odbc_prepare($this->link, utf8_decode($sql));
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

		$stmt = odbc_prepare($this->link, utf8_decode($sql));
		if (!$stmt) {
			if (isset($messageContext)) {
				$messageContext->addMessage("LOG",odbc_errormsg());
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
	function bind_param($stmt, $id, $key, $type=odbc_PARAM_IN) {
		
		global $messageContext;
		
		$ret = odbc_bind_param ($stmt, $id, $key, $type);		
		if (!$ret){			
			$this->lastError="Execute prepare Error: " .odbc_errormsg();
			$messageContext->addMessage("LOG",odbc_errormsg());
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
	function execute($stmt, $campi = array(), $utfDecode=True)
	{
		global $messageContext;
		//$this->sSq('values'.implode(";", $campi));
			$campi2 = array();
		if ($utfDecode) {
			foreach ($campi as $key => $value) {
				if (isset($value)) {
					$campi2[]=utf8_decode($value);
				} else {
					$campi2[] = '';
				}
			}
		}
		$result = odbc_execute($stmt, $campi);
		if (!$result)
		{
			$this->lastError="Execute prepare Error: " .odbc_errormsg();
			echo odbc_errormsg($this->link);
			if (isset($messageContext)) {
 				$messageContext->addMessage("LOG",odbc_errormsg());
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
		// create an array called $row
		if (is_string($result)) {
			return fetchQTEMP($result, $row_number, $trim);
		}
		if ($row_number) $this->currentRecord = $row_number;

		//$row = odbc_fetch_both($result, $row_number);
		$row = odbc_fetch_array($result, $row_number);
		
		// No altrimenti mi tolgo i campi con *BLANK davanti!! Vedi conversione spool file
		// In aggiunta trasformo tutto in utf8 per non avre problemi
        if($row && $trim)
		{
			foreach (array_keys($row) as $chiave)
			{
				$row[$chiave]=rtrim(utf8_encode($row[$chiave]));
			}
		}
		return $row;
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

		odbc_free_result($result);

	}
	function freestmt($stmt)
	{

		odbc_free_stmt($stmt);

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
		$result = odbc_num_fields($result);
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
			//$totale = odbc_exec($this->link, $querycount);
			//$totrec = odbc_fetch_array($totale);
            $totale = $this->query($querycount);
            $totrec = $this->fetch_array($totale, Null, False);
            if (isset($totrec)) {
            	return $totrec['COUNTER'];
            }
            return 0;
            //
			if (odbc_free_stmt($totale))
			return $totrec[0];
			else
			return 0;
		}
		else
		return odbc_num_rows($result);
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
		$dati = array();
		$dati['NAME']= odbc_field_name($result, $field);
		$dati['DISPLAY_SIZE'] = odbc_field_display_size($result, $field);
		$dati['TIPO'] = odbc_field_type($result, $field);
		$dati['WIDTH'] = odbc_field_width($result, $field);
		$dati['SIZE'] = odbc_field_precision($result, $field);
		$dati['SCALE'] = odbc_field_scale($result, $field);
		return $dati;

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
			if (isset($settings['xmlservice'])) {
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
			//$dft = " NOT NULL WITH DEFAULT";
			// Imposto le colonne da creare
			foreach($array as $key=>$element)
			{
				$campo = $key;
				if ($element['DATA_TYPE'] =='1') $campo.= " CHAR(".$element['BUFFER_LENGTH'].")";
				if ($element['DATA_TYPE'] =='12') $campo.= " VARCHAR(".$element['BUFFER_LENGTH'] .")";
				if ($element['DATA_TYPE'] =='2') $campo.= " DEC(".$element['LENGTH_PRECISION'].", ".$element['NUM_SCALE'].")";
				if ($element['DATA_TYPE'] =='3') $campo.= " DEC(".$element['LENGTH_PRECISION'].", ".$element['NUM_SCALE'].")";
				//$campo .=$dft;
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
			odbc_free_stmt($stmt);
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
			odbc_close($this->link);
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
			
			$stmt = odbc_prepare($this->getLink(), $sql);
			if (!$stmt) {
				//echo $this->link->errorCode();
				$error = odbc_errormsg();
				$errorCode = trim(substr($error, 22, 8));
				$errorCode = odbc_error();
				if ($trace) {
					echo "<br>".odbc_errormsg();
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
}
?>
