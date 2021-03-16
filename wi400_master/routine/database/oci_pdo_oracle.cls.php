<?php
/**
 * @name generic_pdo.class.php Classe per accesso al DB tramite driver PDO
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author Luca Zovi
 * @version 1.01 03/09/2015
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 **/
if (!isset($settings['i5_toolkit']) && !defined("I5_IN")) {
	define ("I5_IN", 1);
	define ("I5_OUT", 2);
	define ("I5_INOUT", 3);
	define ("I5_TYPE_CHAR", 0);
	define('I5_TYPE_LONG', 2);
	define('I5_TYPE_INT', 2);
	define('I5_TYPE_FLOAT', 3);
	define('I5_TYPE_DOUBLE', 4);
	define('I5_TYPE_BIN', 5);
	define ("I5_TYPE_PACKED", 6);
	define ('I5_TYPE_BYTE', 5);
	define ("I5_TYPE_ZONED", 7);
	define('I5_TYPE_DATE', 8);
	define('I5_TYPE_TIME', 9);
	define('I5_TYPE_TIMESTP', 10);
	define ("I5_TYPE_STRUCT", 256);
	define('I5_NAME', 'name');
	define('I5_INITSIZE', 'initSize');
	define('I5_DESCRIPTION', 'description');
	define('I5_INIT_VALUE', 'initvalue');
	define('I5_AUTHORITY', 'authority');
	define('I5_LIBNAME', 'libName');
	define ('DB2_PARAM_IN' ,0);
	define ('DB2_PARAM_OUT' ,1);
}
require_once "db_interface_pdo.cls.php";
class OCI_PDO_ORACLE implements WI400_DB_PDO{
	private $db_host;
	private $db_user;	
	private $db_pwd;
	private $db_name;
	private $link;
	private $lastQuery;
	public  $lastError;
	private $debug;
	private $options;
	private $connType;
	private $libl;
	private $in_libl;
	private $log;
	public  $callPGM;
	public  $type = "GENERIC_PDO";
	public  $schema_info = "INFORMATION_SCHEMA";
	public  $schema_tables = "TABLES";
	public  $schema_columns = "COLUMNS";
	public  $DBAttribute = array(
			"DB_SUPPORT_PAGINATION"=>false
			);
    private $metaData = array();	
    private $fromSpecial = array("FILE", "KEY", "VIEW");
    private $toSpecial = array('"FILE"', '"KEY"', '"VIEW"');
    
    // @todo gestione di pi� plug autoselezionati in base a quello che devo fare?!
	/**
	 * Settaggio parametri del DB
	 *
	 * @param $db_host      Nome DB su AS400. Verificare con WRKRDBDIRE il nome reale su AS400
	 * @param $db_user      Utente
	 * @param $db_pwd       Password
	 * @param $db_schema    Scheam di DB a cui connetteri
	 * @param $connzend     Connessione a zend
	 */
	function set($db_host, $db_user, $db_pwd, $db_name = Null, $connType ='T', $debug = true, $log=false)
	{
		$this->db_host = $db_host;
		$this->db_user = $db_user;
		$this->db_pwd = $db_pwd;
		$this->db_name = $db_name;
		$this->connType= $connType;
		$this->debug = $debug;
		$this->log = $log;
		$this->option = array();

	}
	function getOptions($opzione) {
		return $this->options[$opzione];
	}
	function setConfigParm($configArray) {
		foreach ($configArray as $key => $value) {
			switch ($key) {
				case "DB_SCHEMA_INFO":
					$this->schema_info = $value;
					break;
				case "DB_SCHEMA_TABLE":
					$this->schema_table = $value;
					break;
				case "DB_SCHEMA_COLUMNS":
					$this->schema_columns = $value;
					break;
				default:
					$this->DBAttribute[$key] = $value;	
					break;	
			}	
		}
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
		global $settings, $messageContext;

		if (!isset($this->link))
		{
			$msg="Click del ".date("dmYhis"). " connessione!";
			$this->sSq($msg);
			// @TODO GESTIRE CONNESSIONE PERMANENTE O TEMPORANEA
			try {
				// IBM i Access for Linux ODBC Driver
			//$this->link = new PDO("odbc:DSN=AS400", "WI400", "WI400");
			//$this->link = new PDO("odbc:DRIVER={iSeries Access ODBC Driver};SYSTEM=192.168.2.5;UID=EDP2V;PWD=FORMERCURY;BlockSizeKB=128;PREFETCH=1");
			// @todo non usare DEBUG
			$this->link = oci_connect($this->db_user,$this->db_pwd,$settings['pdo_connection_string']);
			} catch (Exception $e) {
			    echo($e->getMessage());
				$this->lastError="Errore di connessione al server: " . $e->getMessage(). $this->db_host;
				die("Errore di Connessione".$e->getMessage());
				if ($this->debug) echo $this->lastError;
				return false;			  
			}		
			// connect to DB2AS400

			/*if ($this->connType=='T') {
				if (count($this->option)>0) {
					$this->link = db2_connect($this->db_host, "", "", $this->options);
				} else {
					$this->link = db2_connect($this->db_host, "", "");
				}
			} else {
				$this->link = db2_pconnect($this->db_host, $this->db_user, $this->db_pwd, $this->options);
			}*/			
			$this->setSchema($this->db_name);
//			$this->link->setAttribute(PDO::ATTR_AUTOCOMMIT,1);
			//$query = "SET IMPLICIT_TRANSACTIONS OFF";
//			$this->link->query($query)
			;
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
			//$query="SET CURRENT SCHEMA='".$this->db_name."'";
			$query="alter session set current_schema = ".$this->db_name;
			if ($this->link != null){
				$do = $this->exec($query);
			//-- I seguenti parametri devono essere settati	per ogni connessione
			//	 per la formattazione corretta
				// inserisco il formato della data che verr� utilizzato
//				$query="ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY-MM-DD HH24:MI:SS'";
				$query="ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY-MM-DD'";
				$do = $this->exec($query);
				// inserisco il formato timestamp che verr� utilizzato
				$query="ALTER SESSION SET NLS_TIMESTAMP_FORMAT = 'YYYY-MM-DD HH24:MI:SS.FF'";
				$do = $this->exec($query);
				// inserisco il formato timestamp con time zone che verr� utilizzato
//				$query="alter session set nls_timestamp_tz_format='YYYY-MM-DD HH24:MI:SS.FF'";
//				$do = $this->link->query($query);
				// Decimali 
				// Decimal separator "."  - group separator "," 
				$query="ALTER SESSION SET NLS_NUMERIC_CHARACTERS = '.,'";
				$do = $this->exec($query);
				// Setto ottimizzazione per il caricamento di tutte le righe
				// all_rows				
				// The optimizer uses a cost-based approach for all SQL statements in the session and optimizes with a goal of best throughput (minimum resource use to complete the entire statement).
				// ORACLE accetta eventualmente FIRST_ROWS_n dove n=1,10,100,1000 e FIRST_ROWS
				$query = "alter session set OPTIMIZER_MODE = ALL_ROWS";
				$do = $this->exec($query);
			//--
				if ($do==False)
				{
					$this->lastError="Schema DB $this->db_name non selezionato";
					echo "errore set SCHEMA!";
					print_r(oci_error($do));
					//if ($this->debug) echo $this->lastError;
					return false;
				}
			}
		}
		return True;
	}
	
	function exec($query, $option=null) {
			$stmt = oci_parse($this->link, $query);
			$do = oci_execute($stmt);
/*			if ($do) {return $stmt;}
			else {return false;}*/
			return $stmt;
	}
	/**
	 * Esegue una query sul server. La libreria del file viene reperita in modo dinamico richiamando un programma sul server
	 * che restituisce la libreria in cui è presente il file sul sistema informativo precaricato. Il cursore viene impostato
	 * di default di tipo scrollabile per permettere la successiva paginazione.
	 *
	 * @param $sql    Statement SQL da eseguire
	 * @return $result  Ritorna un resultset se la query viene eseguita correttamente, False se errore
	 */
/*	function query($sql, $scrollable=True, $optimize=10, $startFrom=null)
	{
		global $settings;
		$this->make_connection();
		// Se si tratta di una SELECT aggiungiamo l'optimize per velocizzare l'apertura
		if ((strtoupper(substr($sql, 0, 6))=="SELECT") && $optimize>0 && isset($startFrom)){
//			$sql .= " FIRST_ROWS_".$optimize;
			// Reperisco il file from e metto "a" per la query di paginazione - FROM $order2 a
			$pos = strpos($sql,"FROM")+5;
			$order2 = substr($sql,$pos,strpos(substr($sql, $pos), ' '));
	//		$sql = str_replace($order2, order2." A", $sql);
			$sql = str_replace($order2, $order2." A", $sql);
			// Order By
			$pos = strpos($sql,"ORDER BY");
			$sql = str_replace("ORDER BY " ,"ORDER BY A.", $sql);
			// Se group by lo elimino
			$posg = strpos($sql,"GROUP");
			if ($posg){
			$group = substr($sql,$posg,$pos-$posg);
			$sql = str_replace($group, "", $sql);}
			// Verifico se c'è un order by
			//echo "sono passato di qua!!";	
			if ($pos!==False) {
				$order = substr($sql,$pos);
				$pos = strpos($sql,"FROM")+5;
				$order2 = substr($sql,$pos,strpos(substr($sql, $pos), "ORDER BY"));
//				$order2 = substr(strtoupper($sql),$pos,strpos(substr($sql, $pos), ' '));
				//echo "<br>anche di qua!!";
				// da oracle 12c viene implementata la funzione di paginazione con offset e fetch next
//				$sql .=" OFFSET $startFrom ROWS FETCH NEXT $optimize ROWS ONLY";;
				$sql ="SELECT * FROM(SELECT A.*,
              ROW_NUMBER() OVER ($order) rn
         FROM $order2
      ) FIRST_ROWS_$optimize 
 WHERE rn >= $startFrom+1 and rn <= $startFrom + $optimize";
			}
		}
		if (strtoupper($sql, 0, 6)=="INSERT"){
			//$sql = utf8_decode($sql);
		}
		if ($settings['ccsid']=='280') {
			$sql = str_replace("!!", "||", $sql);
		}	
		//Normalizza query
		$sql = normalizza($sql);
		//OFFSET 5 ROWS FETCH NEXT 5 ROWS ONLY
		//
		//$sql = $this->escapeSpecialKey($sql);
		//$sql .= " WITH NC"; 
		$sql = $this->resolveTable($sql);
		if ($scrollable) {
//			$result = $this->link->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$result = $this->link->prepare($sql);
			if ($result) {
				$result->execute();
			}
		} else {
			$result = $this->link->query($sql);
		}
		if (!$result)
		{
			$this->lastError="Query Error: " .$this->link->errorCode()." ".$sql;
			global $messageContext;
			$error = $this->link->errorInfo();
			if (isset($messageContext)) {
				$messageContext->addMessage("ERROR",$error[2]);
			}
			if ($this->debug) echo $this->lastError." query:".$sql;
			return false;
		}
		$this->lastQuery = $sql;
		$this->sSq($sql);
		return $result;
	}*/
	function query($sql, $scrollable=True, $optimize=10, $startFrom=0)
	{
		global $settings;
		$this->make_connection();
		// Se si tratta di una SELECT aggiungiamo l'optimize per velocizzare l'apertura
/*		if ((strtoupper(substr($sql, 0, 6))=="SELECT") && $optimize>0){
//			$sql .= " OPTIMIZE FOR $optimize ROWS";
			// Reperisco il file from tabella
//			$pos = strpos(strtoupper($sql),"FROM")+5;
//			$order2 = substr(($sql),$pos,strpos(substr($sql, $pos), ' '));
//			$sql = str_replace("WHERE","WHERE ROWNUM >= $startFrom AND ", $sql);
//			$sql .= " FIRST_ROWS_".$optimize;
		}*/
		if (strtoupper(substr($sql, 0, 6))=="INSERT"){
			$sql = utf8_decode($sql);
		}
		if ($settings['ccsid']=='280') {
			$sql = str_replace("!!", "||", $sql);
		}
		//$sql .= " WITH NC";
		$sql = $this->resolveTable($sql);
		//Normalizza query
		$sql = normalizza($sql);
		if ($scrollable) {
			$stmt = oci_parse($this->link,$sql);
			if ($stmt) {
				$do = oci_execute($stmt);
				$result = $stmt;
			}
		} else {
			$result = $this->exec($sql);
		}
		if (!$result)
		{
			$this->lastError="Query Error: " .oci_error($result)." ".$sql;
			global $messageContext;
			$error = oci_error($result);
			if (isset($messageContext)) {
				$messageContext->addMessage("ERROR",$error[2]);
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
	 * AS400 che restituisce la libreria in cui è presente il file sul sistema informativo precaricato. Il cursore viene impostato
	 * di default di tipo scrollabile per permettere la successiva paginazione.
	 *
	 * @param $sql    Statement SQL da eseguire
	 *
	 * @return $result  Ritorna un resultset se la query viene eseguita correttamente, False se errore
	 */
	function singleQuery($sql)
	{
		global $settings;
		
		//$sql .= " FETCH FIRST ROW ONLY WITH NC";
		$this->make_connection();
		if ($settings['ccsid']=='280') {
			$sql = str_replace("!!", "||", $sql);
		}
		if (strtoupper(substr($sql, 0, 6))=="SELECT" && strtoupper(substr($sql, 0, 12))!="SELECT COUNT"){
			// Seleziona una riga
	     	$sql = str_replace("WHERE", "WHERE ROWNUM <=1 AND ", $sql);
		}
		if (strtoupper(substr($sql, 0, 6))=="INSERT"){
			//$sql = utf8_decode($sql);
		}
		//Normalizza query
		$sql = normalizza($sql);
		
		$sql = $this->resolveTable($sql);
		$result = $this->exec($sql); // Cursore normale per lettura, array('cursor' => DB2_SCROLLABLE));
		if (!$result)
		{
			$this->lastError="Query Error: " .oci_error($result)." ".$sql;
			global $messageContext;
			$info = oci_error($result);
			if (isset($messageContext)) {
				$messageContext->addMessage("ERROR",$info[2]);
				$messageContext->addMessage("LOG",$info[2]);
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
		$sql = $this->resolveTable($sql);
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
					$idx=0;
					foreach ($field as $chiave)
					{
						$chiave = $this->checkKeyField($chiave);
						$id.=$and.$chiave;
						$values.=$and." ?";
						//$values.=$and." :$chiave";
						$and = ',';
						$idx++;
					}
					$values.=')';
					$id.=')';
					// Controllo se il prepare statement deve essere eseguito per N righe
/*					The Oracle Way
					insert multiple records in oracle.
					INSERT ALL
					INTO item (title) VALUES ('title5')
					INTO item (title) VALUES ('title6')
					INTO item (title) VALUES ('title7')
					SELECT * FROM dual;*/
					if ($numRows > 1)
					{
						$operazione = "INSERT ALL";
						$and ="";
						$stringa="";
						$idx=0;
						$sepa = "";
						for ($i=0;$i<$numRows;$i++)
						{
							/*$stringa .=$and.$values;
							$and =",";*/
							$stringa .=$sepa."(";
							$and ="";
							foreach ($field as $chiave)
							{
								$stringa.=$and." ?";
								//$values.=$and." :$chiave";
								$and = ',';
								$idx++;
							}
							$stringa.=")";
							$sepa =" INTO $filagg $id VALUES ";
						}
						$values = $stringa." SELECT * FROM DUAL";
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

		if (!isset($sql)) {
			echo "<br>FILE:$file OPERAZIONE:$operazione<br><pre>";
			print_r($field);
			print_r($key);
			//die();
		}
		$sql = normalizza($sql);
		$sql = $this->resolveTable($sql);
		$stmt = oci_parse($this->link,$sql);
		if (!$stmt)
		{
			echo $sql;
			print_r(oci_error($stmt));
			echo "<br>$operazione $file";
			print_r($field);
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
			//$sql.= " OPTIMIZE FOR ".$optimize." ROW";
//			$sql = str_replace($tabella, $tabella." FIRST_ROWS_".$optimize." ", $sql);
		// Reperisco il file from tabella
//		$pos = strpos(strtoupper($sql),"FROM")+5;
//		$order2 = substr(($sql),$pos,strpos(substr($sql, $pos), ' '));
//		$sql = str_replace($order2, $order2." FIRST_ROWS_".$optimize." ", $sql);
		} elseif ($only == True){
			if (strtoupper(substr($sql, 0, 6))=="SELECT" && strtoupper(substr($sql, 0, 12))!="SELECT COUNT"){
//		 		$sql = "SELECT TOP(1) ".substr($sql, 6);
				// Seleziona una riga
				$sql = str_replace("WHERE", "WHERE ROWNUM <=1 AND ", $sql);
			}
		}
		//Normalizza query
		$sql = normalizza($sql);
		
		$sql = $this->resolveTable($sql);
		$stmt = oci_parse($this->link,$sql);
		//$stmt = db2_prepare($this->link, $sql);
		$this->sSq($sql);
		return $stmt;
			
	}
    
	function prepareStatement($sql, $optimize = 0, $only=False){
		global $messageContext;
		
		$this->make_connection();
		
		$sql = $this->resolveTable($sql);
		if ($optimize > 0 AND $only==False){
			//$sql.= " OPTIMIZE FOR ".$optimize." ROW";
//			$sql = str_replace($tabella, $tabella." FIRST_ROWS_".$optimize." ", $sql);
			// Reperisco il file from tabella
//			$pos = strpos(strtoupper($sql),"FROM")+5;
//			$order2 = substr(($sql),$pos,strpos(substr($sql, $pos), ' '));
		} elseif ($only == True){
			if (strtoupper(substr($sql, 0, 6))=="SELECT" && strtoupper(substr($sql, 0, 12))!="SELECT COUNT"){
//		 		$sql = "SELECT TOP(1) ".substr($sql, 6);
				// Seleziona una riga
		 		$sql = str_replace("WHERE", "WHERE ROWNUM <=1 AND ", $sql);
			}
		}
		$sql=normalizza($sql);
		//echo $sql;
//		$stmt = $this->link->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt = oci_parse($this->link,$sql);
		//print_r($this->link->errorInfo());
		if (!$stmt) {
			$messageContext->addMessage("LOG",oci_error($stmt));
			echo "<br>ERROR!!! - SQL -->".$sql;
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
		if ($type==DB2_PARAM_IN) {
			$ret = $stmt->bindParam($id, $key);
		} else {
			$ret = $stmt->bindParam($id, $key, PDO::PARAM_STR|PDO::PARAM_INPUT_OUTPUT, 8192);
		}
		//$ret = db2_bind_param ($stmt, $id, $key, $type);
		//$ret = $stmt->bindParam(1, $key, PDO::PARAM_STR|$type);
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
	function execute(&$stmt, $campi = array() , $utfDecode=False)
	{
		global $messageContext, $settings;
		//$this->sSq('values'.implode(";", $campi));
		/*$first_key = key($campi); // First Element's Key
		if ($first_key!="0") {
			$row=array();
			foreach ($campi as $chiave => $value)
			{
				$row[]=$value;
			}
			$campi=$row;
		}*/
		$campi2 = array();
		/*if ($utfDecode) {
			foreach ($campi as $key => $value) {
				if (isset($value)) {
					$campi2[]=utf8_decode($value);
				} else {
					$campi2[] = '';
				}
			}
		}*/
		if (is_bool($stmt)) {
			error_log("BOOL STATEMENT:".serialize($campi));
			return false;
		}
		
		// Faccio il bind manuale di tutti i campi
		$rr = 0;
		foreach ($campi as $key => $value) {
			$var_type = SQLT_CHR;
			$var_lenght = -1;
			// Se valore numerico
			if (is_int($campi[$key])) $var_type = SQLT_INT;
			// Nell'insert ORACLE non vuole il separtore delle migliaia
//			$campi[$key] = str_replace(",","",$campi[$key]);
//			if (strcspn($campi[$key], ',') != strlen($campi[$key]))$var_type = SQLT_INT;
			// Se passato valore nullo stringa imposto uno spazio a blank
			if ($campi[$key] == "") $campi[$key] = " ";

			oci_bind_by_name($stmt,$rr, $campi[$key],$var_lenght,$var_type);
			$rr++;
		}
		
		$result = oci_execute($stmt);
		//$result = $stmt->execute($campi2);
		// Se non va verifico se devo fare il secondo richiamo
		if (!$result) {
			if ($settings['db_retry_prepare']==True) {
				$errori = oci_error($stmt);
				if ($errori['0']=='07002') {
					$dati = (array) $stmt;
					//echo "<br>QUERY:".$dati['queryString'];
					$stmt = "";
					$stmt = oci_parse($this->link,$dati['queryString']);
					$result = $this->execute($stmt,$campi2);
					//echo "<br>RIPROVO!!";
				}
			}
		}
		if (!$result)
		{
			echo "<br>Errore di execute!";
			echo "<pre>";
			print_r(oci_error($stmt));
			print_r($campi);
			echo "</pre>";
			echo var_dump($stmt);
			$this->lastError="Execute prepare Error: " .oci_error($stmt);
			$messageContext->addMessage("LOG",oci_error($stmt));
			if ($this->debug) {
				echo $this->lastError;
				//print_r($campi);
			}
			//die();
			return false;
		}
		return $result;
	}
	/**
	 * Legge le righe di un cursore SQL
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
		$row = array();
		/*if ($row_number) $this->currentRecord = $row_number;
		if ($row_number != null) {
			$row = $result->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_ABS, $row_number);
			if (!$row) {
				//print_r($result->errorInfo());
				//echo var_dump($result);
			}
			
		} else {*/
		// create an array called $row
		if ($row_number) {
			$this->currentRecord = $row_number;
			for ($i=1; $i<$row_number; $i++) {
				$row = oci_fetch_assoc($result);
			}
		}
		//die("prima prima fetch");
		if ($result) {
			$row = oci_fetch_assoc($result);
			if (!$row) {
				print_r(oci_error($result));
				//echo var_dump($result);
			}
		}
		//echo "<br>".db2_stmt_errormsg(). " - " .$row_number;
		// No altrimenti mi tolgo i campi con *BLANK davanti!! Vedi conversione spool file
        if($row && $trim)
		{
			foreach (array_keys($row) as $chiave)
			{
				$row[$chiave]=rtrim($row[$chiave]);
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

		//db2_free_result($result);

	}
	function freestmt($stmt)
	{
		if (is_object($stmt)) {
			$stmt->closeCursor();
		}

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
		$count = $result->columnCount();

		return $count;
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
//			$optimize = strnpos($mystring, "FIRST_ROWS_");
				
			$len = strlen($mystring);
			
			if ($group > 0){
				$len = $group - $dadove;
			} else{
				if ($order > 0)
				$len = $order - $dadove;
			}

//			if ($order > 0)
//				$len = $order - $dadove;
//			else if ($optimize > 0)
//				$len = $optimize - $dadove;	
				
			$querycount = "SELECT COUNT(*) AS COUNT ".substr($mystring , $dadove, $len);
			
			if($group > 0) {
				$querycount = "select count(*) AS COUNT from (".$querycount.") as temp";
			}			
//			echo "QUERY_COUNT:$querycount<br>";

			// LZ sostituisco != con <>
			$find = strpos(strtoupper($querycount), "!=");
			if ($find >0) {
				$querycount = substr($querycount, 0,$find)."<>".substr($querycount, $find+2) ;
			}				
//			echo "<b>QUERY_FIND:</b>$querycount<br>";
			$querycount = normalizza($querycount);	
			$this->sSq("ROW NUMBER");
			$totale = $this->query($querycount, False, 0);
			$totrec = $this->fetch_array($totale);
			//echo "<br>QUERY COUNT:".$querycount;
			if (isset($totrec["COUNT"]))
			return $totrec["COUNT"];
			else
			return 0;
		}
		else {
			$find = strpos($mystring, "CALL");
			if ($find===False) {
				return $result->rowCount();
			}
		}
		
	}

	/**
	 * Reperisce il tipo di campo passato come parametro. Questa funzione non è implementata con PDO, viene passato solo il nome
	 * campo e gli attributi settati di default. ** NON USARE **
	 *
	 * @param $result result set
	 * @param $field  string nome del campo
	 *
	 * @return $dati  Array con gli attributi del campo
	 */
	function getField($result, $field)
	{
		$dati = array();
		$metaData = $this->getMetaData($result, $field);
		//echo "<br>FIELD:".$field;
		//echo "<pre>";
		//print_r($metaData);
		//echo "</pre>";
		$dati['NAME']=$field;
		$dati['DISPLAY_SIZE'] = $metaData['VIDEO_LENGTH'];
		//$dati['TIPO'] = $metaData['DATA_TYPE'];
		// Converto il tipo in cosa si aspetta di fuori
		$type = "string";
		switch ($metaData['DATA_TYPE']) {
			case "1":
				$type = "string";
				break;
			case "2":
				$type = "real";
				break;
			case "3":
				$type = "real";
				break;
			default:
				$type = "string";
				break;
		}
		$dati['TIPO'] = $type;
		$dati['WIDTH'] = $metaData['BUFFER_LENGTH'];
		$dati['SIZE'] = $metaData['LENGTH_PRECISION'];
		$dati['SCALE'] = $metaData['NUM_SCALE'];

		return $dati;

	}
	function getMetaData($result, $field) {
		global $settings;die(dsasdas);
		$key = md5($result->queryString);
		if (isset($this->metaData[$key])) {
			$table = $this->metaData[$key];
			return $this->columns($table, $field);
		} else {
			$query = $result->queryString; 
			//$table = "X".substr(uniqid(), 0, 9);
			$table = strtoupper("X".$key);
			//$sql = "create table PHPTEMP".$settings['db_separator']."$table as ($query) with no data ";
			//$sql = "SELECT * INTO TEMP.$table FROM PHPLIB.dbo.FAZISIRI";
			$find2 = strpos($query,'FROM');
			if ($find2!==False) {
//				$sql = substr($query,0, 6). " TOP(0) ".substr($query, 6, $find2-6)." INTO TEMP.$table ".substr($query, $find2);
				$sql = substr($query,0, 6)." ".substr($query, 6, $find2-6)." WHERE ROWNUM = 0"." INTO TEMP.$table ".substr($query, $find2);
			}
			$do = query($this->link,$sql);
			$this->metaData[$key]=$table;
			$dati =  $this->columns($table, $field, "", "TEMP");
			// Distruggo la tabella che non mi server +
			$sql = "DROP TABLE TEMP".$settings['db_separator']."$table";
			$do = query($this->link,$sql);
			return $dati;
		}
	}
	/**
	 * @desc rtvLibre : Recupera libreria tabella con SQL *NEW 11/08/2015 Al momento più lento di rtvLibre con routine
	 * @param string $tabella
	 */
	function rtvLibre ($tabella) {
		
		global $settings,$db;
		if (isset ( $_SESSION ['array_librerie'][$tabella])) {
			return $_SESSION ['array_librerie'][$tabella];
		} else {	
//			$schema = "dbo";
			// Recupero  lo schema
//			$sql = "SELECT TABLE_SCHEMA FROM {$this->schema_info}".$settings['db_separator']."{$this->schema_tables} WHERE TABLE_NAME = '$tabella'";
			$sql = "select OWNER from ALL_CATALOG where TABLE_NAME='".strtoupper($tabella)."'and OWNER = '$this->db_name'";
			$stmt = $db->singlePrepare($sql);
			$do = $db->execute($stmt);
			if ($do) {
			$row = $db->fetch_array($stmt);
			}
				$schema = $row['OWNER'];
				$_SESSION ['array_librerie'][$tabella]=$row['OWNER'];
		}
		return $schema;
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
		static $stmtSingle, $stmtMulti;
	
		$this->make_connection();
		$field = array();
		$campi = array();
		// @todo get current_schema
		if ($libre==null){
			//$libre = rtvLibre($tabella, $connzend);
			$libre = $settings['db_name'];
		}
		// Prepare delle query per ottimizzare le prestazioni
		if ($column!="")
		{
			if (!isset($stmtSingle)){
					$sql = "SELECT  
					COLUMN_NAME,
                    DATA_TYPE, DATA_PRECISION, CHAR_LENGTH, DATA_DEFAULT, NULLABLE, 
                    DATA_SCALE 					
					FROM all_tab_columns WHERE TABLE_NAME = ? AND OWNER = ? AND COLUMN_NAME = ?";
					$stmtSingle = $this->prepareStatement($sql);
			}
			$result = $this->execute ($stmtSingle, array (strtoupper ( $tabella ), $libre ,$column ));
			$stmt=$stmtSingle;
		}
		else
		{
			if (!isset($stmtMulti)){
					$sql = "SELECT  
					COLUMN_NAME, DATA_LENGTH,
                    DATA_TYPE, DATA_PRECISION, CHAR_LENGTH, DATA_DEFAULT, NULLABLE, 
                    DATA_SCALE 					
					FROM all_tab_columns WHERE TABLE_NAME = ? AND OWNER = ?";
				$stmtMulti = $this->prepareStatement ($sql);
			}
			$result = $this->execute ($stmtMulti, array (strtoupper ( $tabella ), $libre));
			$stmt=$stmtMulti;
		}
		while ($row = $this->fetch_array($stmt))
		{
			// A seconda del tipo di dato imposto la lunghezza a video
			$dataType = $this->convertDataTypeString($row['DATA_TYPE']);
			$descrizione=$commento;
			// Cerco se la colonna ha una descrizione aggiuntiva
			if ($descrizione =="") {
				$desquery = "SELECT cast(COMMENTS as varchar(20)) as value FROM ALL_COL_COMMENTS WHERE table_name = '$tabella' and OWNER = '$libre' and COLUMN_NAME="."'".$row['COLUMN_NAME']."'";
				$result2 = $this->singleQuery($desquery);
				if ($result) {
					$row2 = $this->fetch_array($result2);
					$descrizione = $row2['VALUE'];
				}
			}
			if ($descrizione=="") $descrizione = $row['COLUMN_NAME'];
				// Valorizzazione array
				$length = $row['DATA_LENGTH'];
				if ($row['CHAR_LENGTH']<>0){ $length = $row['CHAR_LENGTH'];}
			 	if ($row['DATA_PRECISION']<>0) {$length = $row['DATA_PRECISION'];}
				$field[$row['COLUMN_NAME']]=array(
	    		'DATA_TYPE'=>$dataType,
	    		'DATA_TYPE_STRING'=>$row['DATA_TYPE'],
//				'DATA_TYPE_STRING'=>$this->convertDataTypeString($row['DATA_TYPE']),
	      		'NUM_SCALE'=>$row['DATA_SCALE'],
	    		'LENGTH_PRECISION'=>$length,
	    		'VIDEO_LENGTH'=>$length,
	    		'BUFFER_LENGTH'=>$length,
	    		'REMARKS'=>$descrizione,
				//'HEADING'=>$row['COLUMN_HEADING'],
	    		'COLUMN_DEFAULT'=>$row['DATA_DEFAULT'],
	    		'IS_NULLABLE'=>$row['NULLABLE'],
	    		'DATETIME_CODE'=>''
				);
			$campi[]=$row['COLUMN_NAME'];
			// Se ho richiesto una singola colonna ritorno l'array della colonna
			if ($column!="")
			{
				return $field[$column];
			}
		}
		if (!$only) return $field;
		else return $campi;
	}	
	// Inizializzazione valori di default
	function inzDsValue($dataType) {
		global $db;
		$valore ="";
		switch($dataType) {
			case "VARCHAR":
				$valore = ' ';
				break;
			case "VARCHAR2":
				$valore = ' ';
				break;
			case "CHAR":
				$valore = ' ';
				break;
			case "NVARCHAR2":
				$valore = ' ';
				break;
			case "NCHAR":
				$valore = ' ';
				break;
			case "LONG":
				$valore = ' ';
				break;
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
			case "NUMBER":
				$valore = '0';
				break;
//			case "DATE";
//				$valore = "0001-01-01";
//				break;
			case "DATE":
//				$valore = $db->getTimestamp("*INZ");
				$valore = getDb2Timestamp();
				break;
			case "TIMESTAMP":
				$valore = $db->getTimestamp("*INZ");
//				$valore = getDb2Timestamp();
				break;
			case "TIMESTAMP(6)":
				$valore = $db->getTimestamp("*INZ");
//				$valore = getDb2Timestamp();
				break;
		}
		return $valore;
	}
	function convertDataTypeString($string, $what='TO_NUMERIC') {

		static $array = array("CHAR"=>"1","NUMBER"=>"3","DATE"=>"8","VARCHAR2"=>"12", "VARCHAR"=>"12","TIMESTAMP"=>"88", "TIMESTAMP(6)"=>"88","NCHAR"=>"1","NVARCHAR2"=>"1", "LONG"=>"12");
//		static $array2 = array("CHAR"=>"CHAR","NUMBER"=>"NUMERIC","DATE"=>"DATE","VARCHAR2"=>"VARCHAR", "VARCHAR"=>"VARCHAR","TIMESTAMP"=>"TIMESTAMP", "TIMESTAMP(6)"=>"TIMESTAMP(6)","NCHAR"=>"NCHAR","NVARCHAR2"=>"NVARCHAR2", "CLOB"=>"CLOB");
		
		if ($what=='TO_NUMERIC') {
			return $array[$string];
//			return array_search($string, $array2);
		} else {
			return array_search($string, $array);
//			return array_search($string, $array2);
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
			//$sqlDescription = "LABEL ON ".$tableName." (";
			$virgola ="";
			//$descrizioni = array();
			$descrizioni="";
			$dft = " NOT NULL DEFAULT";
			// Imposto le colonne da creare
			foreach($array as $key=>$element)
			{
				$campo = $key;
				// Normalizzo campi KEY DEL DB NON UTILIZZABILI SENZA OVERLAY
				//$campo = $this->checkKeyField($campo, True);
				//if ($campo =="FILE") $campo = '"FILE"';
				//
				if ($element['DATA_TYPE'] =='1') $campo.= " CHAR(".$element['BUFFER_LENGTH'].")";
				if ($element['DATA_TYPE'] =='12') $campo.= " VARCHAR(".$element['BUFFER_LENGTH'] .")";
				if ($element['DATA_TYPE'] =='2') $campo.= " DEC(".$element['LENGTH_PRECISION'].", ".$element['NUM_SCALE'].")";
				if ($element['DATA_TYPE'] =='3') $campo.= " DEC(".$element['LENGTH_PRECISION'].", ".$element['NUM_SCALE'].")";
				if ($element['DATA_TYPE'] =='8') $campo.= " DATE";
				if ($element['DATA_TYPE'] =='88') $campo.= " TIMESTAMP";

				if (isset($settings['db_dft_not_null']) && $settings['db_dft_not_null']==True) {
					$campo .=$dft;
				}
				//$sql.=$virgola.$campo;
				$dftvalue = "' '";
				if (in_array($element['DATA_TYPE'], array("2", "3"))) $dftvalue = ' 0';
//				$sql.=$virgola.$campo. " NOT NULL DEFAULT ".$dftvalue;
				$sql.=$virgola.$campo. " DEFAULT ".$dftvalue." NOT NULL";
				$virgola =",";
				if (isset($element['REMARKS']) && $element['REMARKS']!="") {
					//$descrizioni[$key]=$element['REMARKS'];
//					$descrizioni .="EXEC {$settings['db_name']}.sys.sp_addextendedproperty 'MS_Description', '{$element['REMARKS']}', 'schema', '{$libre}', 'table', '{$table}', 'column', '{$key}';";
					$descrizioni = "SELECT cast(COMMENTS as varchar(20)) as value FROM ALL_COL_COMMENTS WHERE table_name = '$table' and OWNER = '$libre' and column_name = '$key'";
					$found=True;
				}
			}
			$sql.=")";
			// Creo la tabella
			$stmt= $this->query($sql);
			/*$errori = $stmt->errorInfo();
			showArray($errori);
			die($sql);*/
			//db2_free_stmt($stmt);
			if (is_object($stmt)) {
				$stmt->closeCursor();
			}
			// Scrivo le eventuali descrizioni sulla tabella
			$virgola="";
			if ($found) {
				if ($descrizioni !="") {
					$stmt= $this->query($descrizioni);
				}					
			}
		}
	}
	// Controllo se esiste una tabella
	public function ifExist($table, $libre=null)
	{
		global $settings;
		$this->make_connection();
		if (isset($libre) AND $libre!="") {
//			$sql = "select TABLE_SCHEMA from INFORMATION_SCHEMA.TABLES where TABLE_SCHEMA ='$libre' and TABLE_NAME='".strtoupper($table)."'";
			$sql = "select OWNER from ALL_CATALOG where TABLE_NAME='".strtoupper($table)."' and OWNER = '$libre'";
		} else {
//			$sql = "select TABLE_SCHEMA from INFORMATION_SCHEMA.TABLES where TABLE_SCHEMA IN(".$this->in_libl. ") and TABLE_NAME='".strtoupper($table)."'";
			$sql = "select OWNER from ALL_CATALOG where OWNER IN(".$this->in_libl. ") and TABLE_NAME='".strtoupper($table)."'";
		}
		$result = $this->singleQuery($sql);
		$row = $this->fetch_array($result);
		if (isset($row['OWNER']) AND ($row['OWNER']!="")) {
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
//		$sql = "select SYSTEM_TABLE_NAME FROM {$this->schema_info}".$settings['db_separator']."{$this->schema_tables} where TABLE_SCHEMA ='$libre' and TABLE_NAME='".strtoupper($table)."'";
		$sql = "select TABLE_NAME from ALL_CATALOG where TABLE_NAME='".strtoupper($table)."'and OWNER = '$libre'";
		$result = $this->singleQuery($sql, False);
		$row = $this->fetch_array($result);
		if ($row) return $row['TABLE_NAME'];
		return "";		
	}
	// Controllo se esiste una tabella
	public function getTableDescription($table)
	{
		global $settings;
		$descrizione ="";
		$schema = $this->rtvLibre($table); 
		$this->make_connection();
//		$desquery = "SELECT cast(value as varchar) as value
//		FROM sys.fn_listextendedproperty ('MS_Description','schema', '$schema', 'table', '$table', null, null)";echo $desquery;
//		$desquery = "SELECT cast('value' as varchar(20)) as value FROM (SELECT column_name as value FROM cols WHERE table_name)";
		$desquery = "SELECT cast(COMMENTS as varchar(20)) as value FROM ALL_TAB_COMMENTS WHERE table_name = '$table' and OWNER = '$schema'";
		$result = $this->singleQuery($desquery);
		if ($result) {
			$row = $this->fetch_array($result);
			$descrizione = $row['VALUE'];
		} 
		return $descrizione;		
	}
	
	/*Distruzione di tutte le tabelle temporanee create a livello di sessione
	 / @param $ID  string ID sessione in chiusura
	 */
	public function destroyTable($ID)
	{
		global $settings;
		$this->make_connection();
	// Oracle 11g ha il problema dei nomi delle tabelle limitati a 30 chars - Vedere wi400Subfile con creazione nome tabella troncato		
//		$tmpDir = wi400File::getSessionFile($ID);
		$tmpDir = wi400File::getSessionFile(substr($ID,0,15));
		if (file_exists($tmpDir)){
			$handler = opendir($tmpDir);
			while ($sessionFile = readdir($handler)) {
		        if (!is_dir($sessionFile) && strripos($sessionFile, ".".wi400Session::$_TYPE_SUBFILE) > 0){
//					$subFile = wi400Session::loadFile($ID, $sessionFile);
					$subFile = wi400Session::loadFile(substr($ID,0,15), $sessionFile);
		        	$subFile->drop();
		        }
		    }
		    closedir($handler);
		}
	}
	public function clearPHPTEMP ($session_id) {
		global $settings;
	// Oracle 11g ha il problema dei nomi delle tabelle limitati a 30 chars - Vedere wi400Subfile con creazione nome tabella troncato		
//		$sql = "select TABLE_SCHEMA, TABLE_NAME from {$this->schema_info}".$settings['db_separator']."{$this->schema_tables} where TABLE_SCHEMA ='".$settings['db_temp']."' and TABLE_NAME like'%".strtoupper($session_id)."%'";
		$sql = "select TABLE_NAME,OWNER from ALL_CATALOG where TABLE_NAME like'%".strtoupper(substr($session_id,0,15))."%' and OWNER ='".$settings['db_temp']."'";
		$result = $this->query($sql, False, 0);
		if ($result) {
			while ($row = $this->fetch_array($result)) {
				$sql1 = "DROP TABLE ".trim($row['OWNER']).$settings['db_separator'].trim($row['TABLE_NAME']);
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
	// Oracle 11g ha il problema dei nomi delle tabelle limitati a 30 chars - Vedere wi400Subfile con creazione nome tabella troncato		
		$this->make_connection();
//		$tmpDir = wi400File::getSessionFile($ID);
		$tmpDir = wi400File::getSessionFile(substr($ID,0,15));
		if (file_exists($tmpDir)){
			$handler = opendir($tmpDir);
			while ($sessionFile = readdir($handler)) {
				 if (!is_dir($sessionFile) && strripos($sessionFile, ".".wi400Session::$_TYPE_SUBFILE) > 0){
	//				$subFile = wi400Session::loadFile($ID, $sessionFile);
					$subFile = wi400Session::loadFile(substr($ID,0,15), $sessionFile);
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
		if (isset($this->link))
		{
			//$this->link= null;
		}
	}
	function getLink() {
		return $this->link;
	}
	function setLink($link) {
		$this->link=$link;
	}
	function getCallPGM() {
		if (!isset($this->callPGM)) {
			$this->make_connection();
		}
		return $this->callPGM;
	}
	function field_display_size($resultSet ,$key ) {
		//$size = db2_field_display_size ($resultSet ,$key );
		// @todo Non implementata nel driver ODBC
		$size = 40;
		return $size;
	}
	function getColumnListFromTable($table, $libre) {
		
		global $settings;

		$sql = "SELECT
					COLUMN_NAME,
                    DATA_TYPE, DATA_PRECISION, CHAR_LENGTH, DATA_DEFAULT, NULLABLE,
                    DATA_SCALE
					FROM all_tab_columns WHERE TABLE_NAME = ? AND OWNER = ?";
		$stmt = $this->prepareStatement ( $sql );
		$result = $this->execute ( $stmt, array (strtoupper ( $table ), $libre ) );
		$desc1 = array ();
		// Ciclo di costruzione e caricamento del descrittore della DS da utilizzare	
		while ( $info = $this->fetch_array ( $stmt ) ) {
			// Cerco se la colonna ha una descrizione aggiuntiva
			if ($descrizione =="") {
				$desquery = "SELECT cast(COMMENTS as varchar(20)) as value FROM ALL_COL_COMMENTS WHERE table_name = '$table' and OWNER = '$libre' and COLUMN_NAME=".$row['COLUMN_NAME'];
				$result2 = $this->singleQuery($desquery);
				if ($result) {
					$row2 = $this->fetch_array($result2);
					$descrizione = $row2['value'];
				}
			}
			if ($info['COLUMN_NAME']!='NREL') {
			$description = $info ['COLUMN_TEXT'];
			if ($description=="") $description =  $info ['COLUMN_NAME'];	
			$mycol = new wi400Column ( $info ['COLUMN_NAME'], $description );
			// Campi Packed
			if ($info ['DATA_TYPE'] == 'NUMBER') {
				$len = $info ['DATA_PRECISION'];
				$dec = $info ['DATA_SCALE'];
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
		$query ="SELECT * FROM v$version WHERE banner LIKE 'Oracle%'";
		$result = $this->singleQuery($query);
		$row= $this->fetch_array($result);
		return $row['BANNER'];
}	
function resolveTable($sql, $trace = False) {
	global $connzend, $settings;
	
	if (isset($settings['auto_resolve_table'])) {
	$oriSql = $sql;
	$prova = True;
	$i=0;
	$fileArray= array();
	$substArray= array();
	// Recupero dati di sessione ed eventualmente li applico per avere la query risolta piÃ¹ velocemente
	if (isset($_SESSION['pdo_resolve_file'])) {
		$fileArray = $_SESSION['pdo_resolve_file'];
		$substArray = $_SESSION['pdo_resolve_subst'];
		$oriSql = str_replace($substArray, $fileArray, $oriSql);
		$sql = str_replace($fileArray, $substArray, $oriSql);
	}
	/*echo "<pre>";
	print_r($substArray);
	die();*/
	do  {
		if ($i>6) {
			break;
		}
	  	$stmt = oci_parse($this->link,$sql);
		if (!$stmt) {
			//echo $this->link->errorCode();
			$error = oci_error($stmt);
			$errorCode = $error[0];
			if ($trace) {
				echo "<pre>";
				echo "<br>$sql<br>";
				print_r(oci_error($stmt));
				echo "</pre>";
			}
			if ($errorCode=="42S02") {
			  $find = strpos($error[2],'SQL0204');
			  $metodo = 1;
			  if ($find===False) {
			  	$metodo = 2;
			  	$find = strpos($error[2],'SQL7967');
			  }			  
			  //$find = strpos($error[2],'-');
			  //if ($find===False) {
			  //	$metodo = 2;
			  //	$find = strpos($error[2],'istruzione');
			  //}
			  if ($find !==False) {
			  	   if ($metodo==1) {
				  	   	$find = strpos($error[2],'-');
					   	$file = trim(substr($error[2], $find+1, strpos($error[2], 'in',$find+1)-$find-1));
			  	   } else {
				  	   	$find = strpos($error[2],'istruzione');
				  	   	$file = trim(substr($error[2], $find+11, strpos($error[2], 'completata',$find+11)-($find+11)));
			  	   }
				   $fileArray[] = $file;
				   $substArray[] = rtvLibre($file, $connzend).$settings['db_separator'].$file;
				   $fileArray[] = strtolower($file);
				   $substArray[] = rtvLibre($file, $connzend).$settings['db_separator'].$file;
				   	
				   if ($trace) {
					   echo "<br>".rtvLibre($file, $connzend). " - ".$file;
					   echo "<br>Prima:".$sql;
				   }	   
				   // Aggiunto StringToUpper perchè il controllo ritorna la tabella MAIUSCOLA 
				   $sql = str_replace($fileArray, $substArray, $oriSql);
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
	$_SESSION['pdo_resolve_file']=$fileArray;
	$_SESSION['pdo_resolve_subst']=$substArray;
	}
	return $sql;
}
function escapeSpecialKey($mixed) {
	if (is_array($mixed)) {
		$result = array();
		foreach ($mixed as $key => $value) {
			if (!in_array($value, $this->fromSpecial)) {
				$result[]=$value;
			} else {
				$i = array_search($value, $this->fromSpecial);
				$result[] = $this->toSpecial[$i];
			}
		}
		//showArray($mixed);
		//showArray($result);die("FINE!!");
	} else {
		$result ="";
		$result = str_replace($this->fromSpecial, $this->toSpecial , $mixed);
	}
	return $result;
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
function getTimestamp($data=Null, $short=null) {
	global $settings;
	return getDb2Timestamp_AS400($data, $short);
}
function getTime($time) {
	global $settings;
	if ($time=="*INZ") {
		return "00:00:00.00000000";
	} else {
		$unix = strtotime($time);
		return date("H:i:s.00000000", $unix);
	}
}
	function getCurrentOpenFile($filter="") {
		die("getCurrentOpenFile() FUNZIONE NON SUPPORTATA!");
	}
function getSequence($fileNumeratore, $numeratore) {
	// Recupero numeratore
	$numero =0;
	// Controllo se esiste il numeratore altrimenti lo creo.
	$query = "SELECT LXXNUM FROM $fileNumeratore WHERE LXXNUM='$numeratore'";
	$result = $this->query($query);
//	$result = $this->execute($stmt);
	$row = $this->fetch_array($result);
	if (!$row) {
		$query = "INSERT INTO $fileNumeratore (LXXNUM, LXXSEQ) VALUES('$numeratore', 0)";
		$this->query($query);
	}
	// REperisco il numeratore
	// Controllo se allocato
	$query=	"select c.owner,c.object_name,c.object_type,b.sid,b.serial#,b.status,b.osuser,b.machine,b.schemaname".
			" from".
			" v".'$locked'."_object a ,v".'$session'." b,dba_objects c".
			" where b.sid = a.session_id and a.object_id = c.object_id".
			" and c.object_name = '$fileNumeratore' and b.schemaname = '$this->db_name'";
	$result = $this->singlePrepare($query);
	$do = $this->execute($result);
	foreach ($row = $this->fetch_array($result) as $value);{
		$do = $this->execute($result);
	}
	// Blocco il record per l'aggiornamento e reperisco gi� il numeratore aggiornato
	$query = "select LXXSEQ,LXXSEQ +1 AS NUMERO from $fileNumeratore WHERE LXXNUM='$numeratore' for update nowait";
	$result = $this->query($query);
//	$result = $this->execute($stmt);
	$row = $this->fetch_array($result);
	$numero = $row['NUMERO'];
	// Aggiorno il record bloccato
	$query = "update $fileNumeratore SET LXXSEQ = LXXSEQ + 1, LXXTIM= current_timestamp WHERE LXXNUM='$numeratore'";
	$result = $this->query($query);
	// Commit operazioni e sblocco record
	$query = "commit";
	$result = $this->query($query);

	return $numero;
}
static function create_descriptor($file, $connzend, $libre = Null, $desc = False) {
	global $db, $settings;

	static $stmt;

	if ($desc) {
		$name = 'COLUMN_TEXT';
	} else {
		$name = 'COLUMN_NAME';
	}
	// Verifico se ho giÃ  creato il descrittore del file e se esiste se per caso ha la data del giorno
	$putfile = False;
	// Se non mi Ã¨ stata passata la libreria la cerco
	if (! isset ( $libre )) {
		$libre = $db->rtvLibre ( $file, $connzend );
	}
	$filename = wi400File::getCommonFile ( "serialize", $libre . "_" . $file . ".dat" );
	$desc = fileSerialized ( $filename );
	if ($desc != null) {
		return $desc;
	}
	// Se arrivo qui devo ricaricre il descrittore e quindi apro il file per la scrittura
	// Accedo alla tabella su AS400 per recuperarne la struttura
	//if (! isset ( $stmt )) {
/*	$sql = "SELECT COLUMN_NAME, COALESCE(CHARACTER_MAXIMUM_LENGTH, 0) AS LENGTH, UPPER(DATA_TYPE) AS DATA_TYPE, COALESCE(NUMERIC_SCALE, 0) AS NUMERIC_SCALE, COALESCE(NUMERIC_PRECISION, 0) AS NUMERIC_PRECISION FROM
	INFORMATION_SCHEMA".$settings['db_separator']."COLUMNS WHERE TABLE_NAME = '$file'
	AND TABLE_SCHEMA = '$libre' ORDER BY ORDINAL_POSITION";*/
	$sql = "SELECT
					COLUMN_NAME,
                    DATA_TYPE, DATA_PRECISION, CHAR_LENGTH, DATA_DEFAULT, NULLABLE,
                    DATA_SCALE , DATA_LENGHT
					FROM all_tab_columns WHERE TABLE_NAME = '$file' AND OWNER = '$libre'";
		$result = $db->query ( $sql );
	//}
//	$result = $db->execute ($stmt);
	//die($sql);
	// Verifico se ho trovato qualcosa
	if (! $result) {
		return false;
	}
	$desc1 = array ();
	// Ciclo di costruzione e caricamento del descrittore della DS da utilizzare
	while ( $info = $db->fetch_array ( $result ) ) {
		//echo "<br>".$info ['DATA_TYPE'];
		// Campi Packed
		if ($info ['DATA_TYPE'] == 'NUMBER') {
			$len = $info ['DATA_PRECISION'];
			$dec = $info ['DATA_SCALE'];
			$desc1 [] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_PACKED, "Length" => "$len.$dec" );
		}
		// Campi alfanumerici
		if ($info ['DATA_TYPE'] == 'CHAR') {
			$len = $info ['CHAR_LENGTH'];
			$desc1 [] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_CHAR, "Length" => "$len" );

		}
		// Campi alfanumerici
		if ($info ['DATA_TYPE'] == 'CLOB') {
			$len = $info ['DEFAULT_LENGTH'];
			$desc1 [] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_CHAR, "Length" => "$len" );
		
		}
		// Campi alfanumerici
		if ($info ['DATA_TYPE'] == 'NCHAR') {
			$len = $info ['CHAR_LENGTH'];
			$desc1 [] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_CHAR, "Length" => "$len" );
		
		}
		// Campi alfanumerici
		if ($info ['DATA_TYPE'] == 'TEXT') {
			$len = $info ['LENGTH'];
			$desc1 [] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_CHAR, "Length" => "$len" );

		}
		// Campi alfanumerici
		if ($info ['DATA_TYPE'] == 'VARCHAR') {
			$len = $info ['CHAR_LENGTH'];
			$desc1 [] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_CHAR, "Length" => "$len" );
		
		}
		// Campi alfanumerici
		if ($info ['DATA_TYPE'] == 'VARCHAR2') {
			$len = $info ['CHAR_LENGTH'];
			$desc1 [] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_CHAR, "Length" => "$len" );
		
		}
		// Campi alfanumerici
		if ($info ['DATA_TYPE'] == 'NVARCHAR') {
			$len = $info ['CHAR_LENGTH'];
			$desc1 [] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_CHAR, "Length" => "$len" );
		
		}
		// Campi alfanumerici
		if ($info ['DATA_TYPE'] == 'NVARCHAR2') {
			$len = $info ['CHAR_LENGTH'];
			$desc1 [] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_CHAR, "Length" => "$len" );
		
		}
		// Zoned
		if ($info ['DATA_TYPE'] == 'NUMERIC') {
			$len = $info ['DATA_PRECISION'];
			$dec = $info ['DATA_SCALE'];
			$desc1 [] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_ZONED, "Length" => "$len.$dec" );
		}
		// Integer
		if ($info ['DATA_TYPE'] == 'INTEGER') {
			$len = $info ['DATA_PRECISION'];
			$dec = $info ['DATA_SCALE'];
			$desc1 [] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_INT, "Length" => "$len.$dec" );
		}
		// Integer
		if ($info ['DATA_TYPE'] == 'INT') {
			$len = $info ['DATA_PRECISION'];
			$dec = $info ['DATA_SCALE'];
			$desc1 [] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_INT, "Length" => "$len.$dec" );
		}
		// Time
		if ($info ['DATA_TYPE'] == 'TIME') {
			$len = $info ['DATA_PRECISION'];
			$dec = $info ['DATA_SCALE'];
			$desc1 [] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_CHAR, "Length" => "8" );
		}
		// Date
		if ($info ['DATA_TYPE'] == 'DATE') {
			$len = $info ['DATA_PRECISION'];
			$dec = $info ['DATA_SCALE'];
			$desc1 [] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_CHAR, "Length" => "10" );
		}
		// DATETIME2
		if ($info ['DATA_TYPE'] == 'DATETIME2') {
			$len = $info ['DATA_PRECISION'];
			$dec = $info ['DATA_SCALE'];
			$desc1 [] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_CHAR, "Length" => "26" );
		}
		// TimeStamp
		if ($info ['DATA_TYPE'] == 'TIMESTAMP') {
			$len = $info ['DATA_PRECISION'];
			$dec = $info ['DATA_SCALE'];
			$desc1 [] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_CHAR, "Length" => "26" );
		}
		// TimeStamp(6)
		if ($info ['DATA_TYPE'] == 'TIMESTAMP(6)') {
			$len = $info ['DATA_PRECISION'];
			$dec = $info ['DATA_SCALE'];
			$desc1 [] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_CHAR, "Length" => "26" );
		}
		// TimeStamp
		if ($info ['DATA_TYPE'] == 'FLOAT') {
			$len = $info ['DATA_PRECISION'];
			$dec = $info ['DATA_SCALE'];
			$desc1 [] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_FLOAT, "Length" => "26" );
		}

	}
	//db2_free_result($result);
	put_serialized_file($filename, $desc1);
	// Ritorno il descrittore recuperato dalla routine
	return $desc1;
}

}
//Normalizza la query per oracle
function normalizza($sql) {
//		$sql = strtoupper($sql);
		// Se trovo un doppio identificatore messo dalla paginazione
//		$sql = str_replace(" A AS A", " A", $sql);
//		$sql = str_replace(" A A", " A", $sql);
		// As_n devono essere senza AS
		$sql = str_replace(" AS A", " A", $sql);
		$sql = str_replace(" AS B", " B", $sql);
		$sql = str_replace(" AS C", " C", $sql);
		$sql = str_replace(" AS SQL", " SQL", $sql);
		$sql = str_replace(" as a", " a", $sql);
		$sql = str_replace(" as A", " A", $sql);
		$sql = str_replace(" as b", " b", $sql);
		$sql = str_replace(" as B", " B", $sql);
		$sql = str_replace(" as c", " c", $sql);
		$sql = str_replace(" as C", "C", $sql);
		// Tolgo il VALUE( ) es. SELECT VALUE(SUM(SPEDITO,0) AS SPEDITO
		$sql = str_replace("VALUE(", "", $sql);
		$sql = str_replace("), 0)", "), 0", $sql);
		// Sistemo le variabili nelle query , sostituisco il punto di domanda con :n
		$pattern = '/\?/';
		$i = 0;
		$sql = preg_replace_callback($pattern, function($matches) use (&$i) {
		return ':'.$i++;
		}, $sql);
		// Questa data � considerata nulla da oracle
		$sql = str_replace("<>'0001-01-01 00:00:00.0000000'", " IS NOT NULL", $sql);
		// Valori nulli a spazio blank
		$sql = str_replace("=''", "=' '", $sql);
		$sql = str_replace("= ''", "=' '", $sql);
		// Elimino estensione dbo
		$sql = str_replace(".dbo.", ".", $sql);
		// Caratteri speciali
		$sql = str_replace("[", '"', $sql);
		$sql = str_replace("]", '"', $sql);
//		$sql = str_replace("where ", "where rtrim(", $sql);
//		$sql = str_replace("WHERE ", "where rtrim(", $sql);
//		$sql = str_replace(" in", ") in", $sql);
//		$sql = str_replace("=", ")=", $sql);
//		$sql = str_replace(">)=", ")>=", $sql);
//		$sql = str_replace("<)=", ")<=", $sql);
//		$sql = str_replace(" BETWEEN", ") BETWEEN", $sql);
//		strpos($sql, "between ")+8;
//		$campo1 = substr($sql,strpos," ");
//		$sql = str_replace($campo1, "'".$campo1."'", $sql);
///		$sql = str_replace("=", "", $sql);
//		$sql = str_replace("and ", "and rtrim(", $sql);
//		$sql = str_replace("AND ", "and rtrim(", $sql);		
//		echo $sql."</br>";
		return $sql;
		}

?>
