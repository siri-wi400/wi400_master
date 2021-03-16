<?php
/**
 * @name db_interface.class.php Interfaccia Generica Sviluppo Driver X DB
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author Luca Zovi
 * @version 1.01 14/01/2016
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 **/
interface WI400_DB_PDO {
	/**
	 * Settaggio parametri del DB
	 *
	 * @param $db_host      Nome DB su AS400. Verificare con WRKRDBDIRE il nome reale su AS400
	 * @param $db_user      Utente
	 * @param $db_pwd       Password
	 * @param $db_schema    Scheam di DB a cui connetteri
	 * @param $connzend     Connessione a zend
	 */
	function set($db_host, $db_user, $db_pwd, $db_name = Null, $connType ='T', $debug = false, $log=false);
	/**
	 * Settaggi vari tramite ARRAY
	 * @param $config_arra  Array di configurazione
	 * 
	 */
	function setConfigParm($configArray);
	/**
	 * Set DB ..
	 *
	 * @param string $db_host Database di connessione da WRKRDBDIRE ..
	 */	
	function setDB($db_host);
	/**
	 * getOptions: Recupera le opzioni del DB
	 * @param unknown $options
	 */
	function getOptions($options);
	/**
	 * Connessione al DB
	 *
	 * @return boolean  True se connessione effettuata, False se la connessione non viene efffettuata
	 */
	function connect($delay=True);	/**
	 * Connessione al DB
	 *
	 * @return boolean  True se connessione effettuata, False se la connessione non viene efffettuata
	 */
	function make_connection();	/**
	 * SET DB Schema
	 *
	 * @return boolean  True se lo schema viene settato, False se lo schema non viene settato o trovato
	 */
	function setSchema($schema=Null);
	/**
	 * Esegue una query sul server. La libreria del file viene reperita in modo dinamico richiamando un programma sul server
	 * AS400 che restituisce la libreria in cui è presente il file sul sistema informativo precaricato. Il cursore viene impostato
	 * di default di tipo scrollabile per permettere la successiva paginazione.
	 *
	 * @param $sql    Statement SQL da eseguire
	 * @return $result  Ritorna un resultset se la query viene eseguita correttamente, False se errore
	 */
	function query($sql, $scrollable=True, $optimize=10, $startFrom=Null);
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
	function singleQuery($sql);
	function esegui($sql);
	/**
	 * Preparazione all'esecuzione di una query di aggiornamento/inserimento dati su DB
	 *
	 * @param $file     String File da aggiornare
	 * @param $key      Array  Array di chiavi da utilizzare per l'aggiornamento
	 * @param $field    Array  Array di campi che devono essere aggiornati
	 *
	 * @return $stmt    Ritorna il prepare dello statement
	 */
	function prepare($operazione, $file, $key=null, $field=null, $numRows=1);
	function singlePrepare($sql, $optimize = 1, $only=False);
	function prepareStatement($sql, $optimize = 0, $only=False);
	function getSequence($fileNumeratore, $numeratore);
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
	function bind_param($stmt, $id, $key, $type=DB2_PARAM_IN);
	/**
	 * Inserisce o aggiorna dati in seguito ad una prepare
	 *
	 * @param $stmt    Risorsa ritornata da una prepare
	 * @param $array   Array contenente i campi da aggiornare
	 *
	 * @return Risultato   True eseguito, False errore
	 */
	function execute(&$stmt, $campi = array(), $utfDecode=True);
	/**
	 * Legge le righe din un cursore SQL
	 *
	 * @param $result    Resultset di una query eseguita
	 * @param $row_number[optional]  Numero di riga da leggere
	 *
	 * @return array     Array con i campi del record letto
	 */
	function fetch_array($result, $row_number = Null, $trim=True);
	function num_rows($result, $caso=1);
	function get_last_query();
	function checkKeyField($fields);
	function getDBAttribute($attribute);
	function setDBAttribute($attribute, $valore);
	function freeResult($result);
	/**
	 * Costruisce automaticamente il descrittore di una DS legata ad un file reperendo automaticamente la sua
	 * struttura 
	 *
	 * @param $file      string:file di cui costruire il descrittore
	 * @param $db        object:oggetto di connessione al DB
	 * @param $connzend  string:connessione a ZEND. Non viene usato $this->Connezend perchÃ¨ la funzione viene usata anceh esternamente
	 * @param $libre     string:libreria del file. Se non passata viene ricercata
	 *
	 * @return array     Array contenente la descrizine dei campi del file
	 */
	static function create_descriptor($file, $connzend, $libre = Null, $desc = False);
	
	function freestmt($stmt);
	/**
	 * Cerca di reperire il numero relativo di record dell'ultimo record inserito nel DB
	 *
	 * @return $integer   Numero relativo di record
	 */
	function insert_id();
	/**
	 * Restituisce il numero campi presenti in un result set
	 *
	 * @param $result    Result set di un query.
	 *
	 * @return $integer   Numero campi
	 */
	function num_fields($result);
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
	function getrownumber($sql, $result = Null, $caso=1);
	/**
	 * Reperisce il tipo di campo passato come parametro
	 *
	 * @param $result result set
	 * @param $field  string nome del campo
	 *
	 * @return $integer   Numero relativo di record
	 */
	function getField($result, $field);
	/**
	 * @desc rtvLibre : Recupera libreria tabella con SQL *NEW 11/08/2015 Al momento più lento di rtvLibre con routine
	 * @param string $tabella
	 */
	function rtvLibre ($tabella);
	/**
	 * Restituisce un array con i campi di una tabella passata come parametro
	 * Ogni elemento dell'array contiene un sottoarray con le caratteristiche del campo
	 *
	 * @param $tabella  string Tabella di cui recuperare i dati
	 *
	 * @return $array   Campi della tabella
	 */
	function columns($tabella, $column="", $only=False, $commento="", $libre=null);
	function convertDataTypeString($string, $what='TO_NUMERIC');
	function inzDsValue($dataType);
	// Imposta le caratteristiche di una campo dai valori passati
	function singleColumns($type="1", $lunghezza=0, $decimali=null , $commento="");
	// Aggiungo lista librerie per connessione SQL
	function add_to_librarylist($libraries, $forceCall=False);
	// Creazione di una tabella
	public function createTable($table, $libre="", $array=array(), $checkExist = false, $not_null=false);
	// Controllo se esiste una tabella
	public function ifExist($table, $libre=null);
	public function getCurrentOpenFile($filter="");
	public function getTimestamp($data=null, $short=null);
	public function getTime($time);
	/**
	 * @desc Recupero il nome da 10 usato dal sistema per gli oggetti 
	 * @param string $file: Nome tabella (> 10)
	 * @param string $libre: Libreria
	 * @return string Codice da 10
	 */
	public function getSystemTableName($table, $libre);
	// Controllo se esiste una tabella
	public function getTableDescription($table);
	/*Distruzione di tutte le tabelle temporanee create a livello di sessione
	 / @param $ID  string ID sessione in chiusura
	 */
	public function destroyTable($ID);
	public function clearPHPTEMP ($session_id);
	/*
	 * Distruzione di tutte le tabelle temporanee create a livello di sessione
	 / @param $ID  string ID sessione in chiusura
	 */
	public function deleteTable($ID);
	function sSq($sql);
	// Distruttore della classe
	function __destruct();
	function getLink();
	function inzCallPGM();
	function getCallPGM();
	function castField($field, $type);
	function field_display_size($resultSet ,$key );
	function getColumnListFromTable($table, $libre);	
	function getInfoDB();}
?>
