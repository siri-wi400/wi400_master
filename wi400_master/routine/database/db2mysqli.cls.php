<?php
/**
 * @name db2mysqli.cls.php Classe per accesso al DB2 AS400
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author Luca Zovi
 * @version 1.00 10/02/2010
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */
require_once "db_interface.cls.php";
class DB2MYSQLI implements WI400_DB {
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
	public  $DBAttribute = array(
			"DB_SUPPORT_PAGINATION"=>True
	);
	
	/**
	 * Settaggio parametri del DB
	 *
	 * @param $settings['db_host']      Nome DB su AS400. Verificare con WRKRDBDIRE il nome reale su AS400
	 * @param $settings['db_user']      Utente
	 * @param $settings['db_pwd']       Password
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
//		$this->db_name = "PHPLIB";
		
	}
	function setConfigParm($configArray) {
		return true;
	}
	function getOptions($opzione) {
		return $this->options[$opzione];
	}
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
		// connect to mysqli
		$this->make_connection();

	}
	/**
	 * Connessione al DB
	 *
	 * @return boolean  True se connessione effettuata, False se la connessione non viene efffettuata
	 */
	function make_connection()
	{
		if (!isset($this->link))
		{
	    $msg="Click del ".date("dmYhis"). " connessione!";
        $this->sSq($msg);
		// connect to MYSQL
		$this->link = new mysqli($this->db_host, $this->db_user, $this->db_pwd);
        $this->setSchema($this->db_name);
		if ($this->link==False)
		{
			$this->lastError="Errore di connessione al server mysqli: " . mysql_conn_errormsg(). $this->db_host;
			if ($this->debug) echo $this->lastError;
			return false;
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

				$do = mysqli_select_db($this->link, $schema);
				if ($do==False)
				{
					$this->lastError="Schema DB $this->db_name non selezionato";
					//if ($this->debug) echo $this->lastError;
					return false;
				}
			}
		return True;
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
		$this->make_connection();			
		// Se si tratta di una SELECT aggiungiamo l'optimize per velocizzare l'apertura
		/*if ((strtoupper(substr($sql, 0, 6))=="SELECT") && $optimize>0){
			$sql .= " LIMIT $optimize";
		}*/
		$result = $this->link->query($sql);

		if (!$result)
		{
			$this->lastError="Query Error: " .mysqli_error($this->link)." ".$sql;
			global $messageContext;
			if (isset($messageContext)) {
			$messageContext->addMessage("ERROR",mysqli_error($this->link));
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
		//$sql .= " FETCH FIRST ROW ONLY WITH NC";
		$this->make_connection();
		$sql.= " LIMIT 1";
		$result = $this->link->query($sql); // Cursore normale per lettura, array('cursor' => DB2_SCROLLABLE));
		if (!$result)
		{
			$this->lastError="Query Error: " . mysqli_error($this->link)." ".$sql;
			global $messageContext;
			if (isset($messageContext)) {
			$messageContext->addMessage("ERROR", mysqli_error($this->link));
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
	    mysqli_real_query($this->link, $sql);
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
        // Compatibilità con query preesistenti
		//$filagg=str_replace("/", $settings['db_separator'], $filagg);

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
				$where = ' WHERE ';
				$and = ' ';
				foreach ($key as $chiave=>$values)
				{
					$where.=$and.$values."=?";
					$and = ' AND ';
				}
				$sql = "$operazione FROM $filagg $where";
			}
		}
        $stmt = $this->link->prepare($sql);
		
		if (!$stmt)
		{
			$this->lastError="Prepare Error: " .mysqli_error($this->link)." ".$sql;
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
		$this->make_connection();
		if ($optimize > 0){
			$sql.= " LIMIT $optimize";
		}	
        $stmt = $this->link->prepare($sql);

        $this->sSq($sql);
		return $stmt;
			
	}

	function prepareStatement($sql, $optimize = 0, $only=False){
		$this->make_connection();
		if ($optimize > 0){
			$sql.= " LIMIT ".$optimize;
		}
        $stmt = $this->link->prepare($sql);
		
        $this->sSq($sql);
		return $stmt;
	}
	function bind_param($stmt, $id, $key, $type=DB2_PARAM_IN) {
	
		global $messageContext;
	
		die("SINGLE BIND PARAM non implementato!!");
		
	}
	/**
	 * Inserisce o aggiorna dati in seguito ad una prepare
	 *
	 * @param $stmt    Risorsa ritornata da una prepare
	 * @param $array   Array contenente i campi da aggiornare
	 *
	 * @return Risultato   True eseguito, False errore
	 */
	function execute($stmt, $campi = array())
	{
        if (is_object($stmt)) {

        $theType='';
        $function = 'mysqli_stmt_bind_param($stmt, $theType ';        
        foreach ($campi as $key=>$value) {
        	$type='s';
        	if (is_integer($value)) {
        		$type = 'i';
        	} else if (is_float($value)) {
        		$type = 'd';
        	}
        	$theType.=$type;
            $function.=', $campi["'.$key.'"]';
        } 
        $function.=");";        	

        eval($function);
              
		$result = $stmt->execute();
		if (!$result)
		{
			$this->lastError="Execute prepare Error: " .mysqli_stmt_error($stmt);
			if ($this->debug) echo $this->lastError;
			return false;
		}
		$stmt->store_result();
		
		return $result;
        }
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
		$row = array();
		if ($result) {
			
		if ($row_number) $this->currentRecord = $row_number;	
		if (get_class($result)=='mysqli_result') {
		// Non vale per la prepare	
		if (isset($row_number) && $row_number>0) {
			mysqli_data_seek ($result, $row_number-1);
		}
		$row = mysqli_fetch_assoc($result);

		} else if (get_class($result)=='mysqli_stmt') {
				
        $fields_r = $this->fetchFields($result);

        // Bind di tutti i parametri
        $function = 'mysqli_stmt_bind_result($result ';
        foreach ($fields_r as $key=>$value) {
            $function.=', $fields_r["'.$key.'"]';
        } 
        $function.=");";
        eval($function);
        $do = $result->fetch();
        if ($do) {
        	$row=$fields_r;
        } 
		}
		}
		if($row && $trim)
		{
			foreach (array_keys($row) as $chiave)
			{
				$row[$chiave]=trim($row[$chiave]);

			}
		}
		if (empty($row)) {
			return false;
		} else {		 
			return $row;
		}
	}

	function num_rows($result)
	{
		return mysqli_num_rows($result);

	}
	function get_last_query() {
		return $this->lastQuery;
	}
	function freeResult($result)
	{

		mysqli_free_result($result);

	}
	function freestmt($stmt)
	{
       
	}

	/**
	 * Cerca di reperire il numero relativo di record dell'ultimo record inserito nel DB
	 *
	 * @return $integer   Numero relativo di record
	 */
	function insert_id()
	{
		$last_id = 0;

		return mysqli_insert_id();
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
		$result = mysqli_num_fields();
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
	function getrownumber($sql, $result = Null)
	{

	}
	function getLink(){
		return $this->link;
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
		$info = mysqli_fetch_fields($result);
		foreach ($finfo as $val) {
			if ($val->name==$field) {
				$dati['NAME']= $val->name;
				$dati['DISPLAY_SIZE'] = $val->length;
				$dati['TIPO'] = $val->type;
				$dati['WIDTH'] = $val->length;
				$dati['SIZE'] = $val->length;
				$dati['SCALE'] = $val->decimals;
				return $dati;
			}
		}
	}
	function rtvLibre ($tabella) {
	
		global $settings;
		die("METODO NON IMPLEMENTATO");
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
				     	$sql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_NAME = ?	AND TABLE_SCHEMA = ? AND COLUMN_NAME=?";
				        $stmtSingle = $this->prepareStatement($sql);
			} 
			$result = $this->execute ($stmtSingle, array (strtoupper ( $tabella ), $libre ,$column ));
			$stmt=$stmtSingle;
		}
		else
		{
			if (!isset($stmtMulti)){
				     	$sql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_NAME = ?	AND TABLE_SCHEMA = ?";
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
			if ($descrizione=="") $descrizione = $row['COLUMN_COMMENT'];
			if (isset($row['NUMERIC_PRECIZION'])) {
				$length = $row['NUMERIC_PRECIZION'];
			} else {
			    $length = $row['CHARACTER_MAXIMUM_LENGTH'];
			}
			// Valorizzazione array
			$field[$row['COLUMN_NAME']]=array(
    		'DATA_TYPE'=>$dataType,
    		'DATA_TYPE_STRING'=>$row['DATA_TYPE'],
      		'NUM_SCALE'=>$row['NUMERIC_SCALE'],
    		'LENGTH_PRECISION'=>$length,
    		'VIDEO_LENGTH'=>$length,
    		'BUFFER_LENGTH'=>$length,
    		'REMARKS'=>$descrizione,
    		'COLUMN_DEFAULT'=>$row['COLUMN_DEFAULT'],
    		'IS_NULLABLE'=>$row['IS_NULLABLE'],
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
	
	    static $array = array("CHAR"=>"1","DECIMAL"=>"2","NUMERIC"=>"3","VARCHAR"=>"12","INTEGER"=>"19","INT"=>19);
	    $string = strtoupper($string);
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
    		'NULLABLE'=>"",
    		'DATETIME_CODE'=>""
    		);
	}
	// Aggiungo lista librerie per connessione SQL
	function add_to_librarylist($libraries, $forceCall=False)
	{
		global $settings;
			
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
		}
		$this->libl = $stringa;
		$this->in_libl = $in_libl;
	}
	// Creazione di una tabella
	public function createTable($table, $libre="", $array, $checkExist = false, $not_null=false)
	{
	    global $settings;
	    
		$this->make_connection();	
		$tableName= $table;
		$found = False;
		if ($libre!="") $tableName =$libre.$settings['db_separator'].$table;
		$sql = "CREATE TABLE IF NOT EXISTS ".$tableName." (";
		$virgola ="";
		$descrizioni = array();
		//$dft = " NOT NULL WITH DEFAULT";
		// Imposto le colonne da creare
		foreach($array as $key=>$element)
		{
			$campo = $key;
			
			// @todo gestire campo blob se char maggiore di 255
			if ($element['DATA_TYPE'] =='1') $campo.= " CHAR(".$element['BUFFER_LENGTH'].")";
			if ($element['DATA_TYPE'] =='12') $campo.= " VARCHAR(".$element['BUFFER_LENGTH'] .")";
			if ($element['DATA_TYPE'] =='2') $campo.= " DEC(".$element['LENGTH_PRECISION'].", ".$element['NUM_SCALE'].")";
			if ($element['DATA_TYPE'] =='3') $campo.= " DEC(".$element['LENGTH_PRECISION'].", ".$element['NUM_SCALE'].")";
			//$campo .=$dft;
			$sql.=$virgola.$campo;
			$virgola =",";
			if (isset($element['REMARKS']) && $element['REMARKS']!="") {
				$sql.= " COMMENT '".trim($element['REMARKS'])."'";
			}
		}
		$sql.=")";
		// Creo la tabella
		$stmt= $this->query($sql);
		$this->freestmt($stmt);

	}
	function getInfoDB() {
		global $settings;
		$query ="SELECT CONNECTION_ID()";
		$result = $this->singleQuery($query);
		$row= $this->fetch_array($result);
		return $row['CONNECTION_ID()'];
	}
	// Controllo se esiste una tabella
	public function ifExist($table, $libre=null)
	{
		$this->make_connection();	
		if (isset($libre) AND $libre!="") {
			$sql = "select TABLE_SCHEMA from information_schema.TABLES where TABLE_SCHEMA ='$libre' and TABLE_NAME='".strtoupper($table)."'";
		} else {
			$sql = "select TABLE_SCHEMA from information_schema.TABLES where TABLE_SCHEMA IN(".$this->in_libl. ") and TABLE_NAME='".strtoupper($table)."'";
		}
		$result = $this->singleQuery($sql);
		$row = $this->fetch_array($result);
		if (isset($row['TABLE_SCHEMA']) AND ($row['TABLE_SCHEMA']!="")) return true;
		return False;
	}
	public function getSystemTableName($table, $libre) {
		global $settings;
		die("METODO NON IMPLEMENTATO!!");
	}
	// Controllo se esiste una tabella
	public function getTableDescription($table)
	{
		$this->make_connection();	
		$sql = "select TABLE_COMMENT FROM information_schema.TABLES where TABLE_SCHEMA IN(".$this->in_libl. ") and TABLE_NAME='".strtoupper($table)."'";
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
	
		$sql = "select TABLE_SCHEMA, TABLE_NAME from INFORMATION_SCHEMA".$settings['db_separator']."TABLES where TABLE_SCHEMA ='".$settings['db_temp']."' and TABLE_NAME like'%".strtoupper($session_id)."%'";
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
//		  if ($this->debug) {
		  if ($this->log) {
		  	  $user ="NONE";
		  	  if (isset($_SESSION['user']) && $_SESSION['user']!="") {
		  	  	$user = $_SESSION['user'];
		  	  }
		      $sessionId = session_id();
			  if ($sessionId =="") $sessionId = "batch";	
			  $file = $settings['log_sql'].$user."_".$sessionId.".txt";
			  $handle = fopen($file, "a");
			  fwrite($handle, date("[Y-m-d_H:i:s.u]").$sql."\r\n");
			  fclose($handle);
		  }
	}
	function getCallPGM() {
		if (!isset($this->callPGM)) {
			$this->make_connection();
		}
		return $this->callPGM;
	}
	function field_display_size($resultSet ,$key ) {
		die("Metodo Non implementato!!!");
		//$size = db2_field_display_size ($resultSet ,$key );
		//return $size;
	}
	// Distruttore della classe
	function __destruct()
	{
		if (!isset($this->link) && is_resource($this->link))
		{
			mysqli_close($this->link);
		}	
	}

	private function fetchFields($selectStmt)
    {
        if (get_class($selectStmt)=='mysqli_stmt') {
    	$metadata = $selectStmt->result_metadata();
        $fields_r = array();
        while ($field = $metadata->fetch_field()) {
        	$fieldname = $field->name;
            $fields_r[$fieldname] = $fieldname;
        }
        return $fields_r;
        }
    }
	function getColumnListFromTable($table, $libre) {
		
		$sql = "SELECT COLUMN_NAME, DATA_TYPE, NUMERIC_SCALE, NUMERIC_PRECISION, COLUMN_COMMENT FROM   
		information_schema.COLUMNS WHERE TABLE_NAME = ? 
		AND TABLE_SCHEMA = ? ORDER BY ORDINAL_POSITION";
		$stmt = $this->prepareStatement ( $sql );
		$result = $this->execute ( $stmt, array (strtoupper ( $table ), $libre ) );
		$desc1 = array ();
		// Ciclo di costruzione e caricamento del descrittore della DS da utilizzare	
		while ($info = $this->fetch_array ( $stmt ) ) {
			if ($info['COLUMN_NAME']!='NREL') {
			$description = $info ['COLUMN_COMMENT'];
			if ($description=="") $description =  $info ['COLUMN_NAME'];	
			$mycol = new wi400Column ( $info ['COLUMN_NAME'], $description );
			// Campi Packed
			if (strpos(strtoupper($info ['DATA_TYPE']), "DECIMAL")) {
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
	function escapeSpecialKey($query) {
		$query = str_replace("FILE", '"FILE"', $query);
		return $query;
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
	function getTimestamp($data=Null) {
		global $settings;
		return getDb2Timestamp_AS400($data);
	}
	function getTime($time) {
		global $settings;
		if ($time=="*INZ") {
			return "00:00:00.000000";
		} else {
			$unix = strtotime($time);
			return date("h:i:s.000000", $unix);
		}
	}
	function getCurrentOpenFile($filter="") {
		die("getCurrentOpenFile() FUNZIONE NON SUPPORTATA!");
	}
	function getSequence($fileNumeratore, $name) {
		global $db;
		$numero =0;
		// Controllo se esiste il numeratore altrimenti lo creo.
		$query = "SELECT LXXNUM FROM $fileNumeratore WHERE LXXNUM='$name'";
		$result = $db->query($query);
		$row = $db->fetch_array($result);
		if (!$row) {
			$query = "INSERT INTO $fileNumeratore (LXXNUM, LXXSEQ) VALUES('$name', 0)";
			$db->query($query);
		}
		// Aggiorno il numeratore
		$query = "UPDATE $fileNumeratore SET LXXSEQ = (@cur_value := LXXSEQ) + 1 WHERE LXXNUM = '$name'";
		$db->query($query);
		// REperisco il numeratore
		$query = "SELECT @cur_value";
		$result = $db->query($query);
		$row = $db->fetch_array($result);
		$numero = $row['@cur_value'];
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
		//if (! isset ( $stmt )) {
		$sql = "SELECT COLUMN_NAME, COALESCE(CHARACTER_MAXIMUM_LENGTH, 0) AS LENGTH, UPPER(DATA_TYPE) AS DATA_TYPE, COALESCE(NUMERIC_SCALE, 0) AS NUMERIC_SCALE, COALESCE(NUMERIC_PRECISION, 0) AS NUMERIC_PRECISION, COALESCE(COLUMN_COMMENT,'') AS COLUMN_TEXT FROM
	INFORMATION_SCHEMA".$settings['db_separator']."COLUMNS WHERE TABLE_NAME = '$file'
		AND TABLE_SCHEMA = '$libre' ORDER BY ORDINAL_POSITION";
		//	$stmt = $db->prepareStatement ( $sql );
		//}
		//$result = $db->execute ( $stmt, array ($file, $libre ) );
		$result = $db->query($sql);
		// Verifico se ho trovato qualcosa
		if (! $result) {
			return false;
		}
		$desc1 = array ();
		// Ciclo di costruzione e caricamento del descrittore della DS da utilizzare
		while ( $info = $db->fetch_array ( $result ) ) {
			// Campi Packed
			if ($info ['DATA_TYPE'] == 'DECIMAL') {
				$len = $info ['NUMERIC_PRECISION'];
				$dec = $info ['NUMERIC_SCALE'];
				$desc1 [] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_PACKED, "Length" => "$len.$dec" );
			}
			// Campi alfanumerici
			if ($info ['DATA_TYPE'] == 'CHAR') {
				$len = $info ['LENGTH'];
				$desc1 [] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_CHAR, "Length" => "$len" );
	
			}
			// Campi alfanumerici
			if ($info ['DATA_TYPE'] == 'TEXT') {
				$len = $info ['LENGTH'];
				$desc1 [] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_CHAR, "Length" => "$len" );
	
			}
			// Zoned
			if ($info ['DATA_TYPE'] == 'NUMERIC') {
				$len = $info ['NUMERIC_PRECISION'];
				$dec = $info ['NUMERIC_SCALE'];
				$desc1 [] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_ZONED, "Length" => "$len.$dec" );
			}
			// Integer
			if ($info ['DATA_TYPE'] == 'INTEGER') {
				$len = $info ['NUMERIC_PRECISION'];
				$dec = $info ['NUMERIC_SCALE'];
				$desc1 [] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_INT, "Length" => "$len.$dec" );
			}
			// Time
			if ($info ['DATA_TYPE'] == 'TIME') {
				$len = $info ['NUMERIC_PRECISION'];
				$dec = $info ['NUMERIC_SCALE'];
				$desc1 [] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_CHAR, "Length" => "8" );
			}
			// Date
			if ($info ['DATA_TYPE'] == 'DATE') {
				$len = $info ['NUMERIC_PRECISION'];
				$dec = $info ['NUMERIC_SCALE'];
				$desc1 [] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_CHAR, "Length" => "10" );
			}
			// TimeStamp
			if ($info ['DATA_TYPE'] == 'TIMESTAMP') {
				$len = $info ['NUMERIC_PRECISION'];
				$dec = $info ['NUMERIC_SCALE'];
				$desc1 [] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_CHAR, "Length" => "26" );
			}
			// TimeStamp
			if ($info ['DATA_TYPE'] == 'FLOAT') {
				$len = $info ['NUMERIC_PRECISION'];
				$dec = $info ['NUMERIC_SCALE'];
				$desc1 [] = array ("Name" => "$info[$name]", "IO" => I5_IN | I5_OUT, "Type" => I5_TYPE_FLOAT, "Length" => "26" );
			}
	
		}
		//db2_free_result($result);
		put_serialized_file($filename, $desc1);
		// Ritorno il descrittore recuperato dalla routine
		return $desc1;
	}
	
}
?>