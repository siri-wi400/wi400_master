<?php
    /**
     * @name AS400_file.php Classe per accesso nativo ai file AS400 mediante wrapper API i5
	 * @copyright S.I.R.I. Informatica s.r.l.
	 * @author Luca Zovi
	 * @versione 1.01  14/07/2008 
	 * @link www.siri.informatica.it
	 * @info info@siri-informatica.it
	 */
class wi400NativeFile
{
	private $file;
	private $percorso;
	private $connzend;
	private $openmode;
	private $LastErr;
    private $firstReadE;
    private $lastrec;
    private $edit;
    private $debug;
	
    /**
	 * @param $name       string:nome del file da aprire
	 * @param $connzend   string:ID della connessione a Zend
	 * @param $type       (optional) string:Tipo apertura del file. I=Input, IO=Input/Output   
	 * @param $library    (optional) string:Libreria forzata di apertura
	 */
	function __construct($file, $connzend, $type= null, $library = null, $debug=false)
	{
		$this->LastErr ="";
		$this->connzend = $connzend;
		$this->edit = False;
		$this->debug = $debug;
		// Verifico il tipo di apertura richiesta
		if (isset($type)) {
			switch (strtoupper($type))
			{
			case 'I':
				$mode= I5_OPEN_READ;
			    break;
			case 'IO':
				$mode= I5_OPEN_READWRITE;
				$this->edit = True;
			    break;   
 			default:   
			    $mode= I5_OPEN_READ;
			    break; 
			}
		}
		
        if (isset($library)) $this->percorso = "$library/$file";
		else $this->percorso = $file;
		
	    if (isset($type)) {
			$this->file = i5_open($this->percorso, $mode, $connzend);
		}
		else {
			$this->file = i5_open($this->percorso, $connzend);
		}
		if (is_bool($this->file))
		 {
			$this->msgerr();
            
		}
		$this->firstReadE=False;
		$this->openmode = $mode;

		return true;
		
	}
   /**
   * Funzione di aggiornamento/scrittura del record corrente. Per la scrittura occorre prima avere inserito un nuovo record con new()
   * 
   * @return boolean    Risultato routine. True aggiornamento effettuato. False errori riscontrati
   */
	function update()
	{
		$update = i5_update($this->file);
		if ($update === false) {		
			$this->msgerr();
            return false;
		}
		return true;
	}
   /**
   * Funzione di aggiunta di un nuovo record. Per la scrittura occorre poi eseguire update()
   *
   * @return boolean    Risultato routine. True aggiornamento effettuato. False errori riscontrati
   */
	function write($data)
	{
		$update = i5_new_record($this->file, $data);
		if ($update === false) {		
			$this->msgerr();
            return false;
		}
		return true;
	}
	 /**
	 * La funzione permette di settare i valori di un tracciato record per l'aggiornamento o per l'inserimento. La funzione è abilitata
	 * nel caso che il file sia stato aperto in "IO", altrimenti viene ritornato un errore.
	 * 
	 * @param $field      string:nome del campo del tracciato
	 * @param $calue      string:valore da settare
	 * 
     * @return boolean    Risultato routine. True set del valore effettuato. False errori riscontrati
	 */
	function set($field, $value)
	{
		$result = i5_setvalue($this->file, $field, $value);
		if ($result === false) {
			$this->msgerr();
			return false;
		}
		return $result;
	}
		 /**
	 * La funzione reperisce il valore di un campo del tracciato passato come parametro.
	 * 
	 * @param $field      string:nome del campo del tracciato
     * @return boolean    Risultato routine. True campo recuperato. False errori riscontrati
	 */
	function get($field)
	{
		$result = i5_result($this->file, $field);
		if ($result === false) {		
			$this->msgerr();
            return false;
		}
		return $result;
		
	}
     /**
	 * La funzione cancella il record corrente. Il file deve essere stato aperto in "IO".
     *
     * @return boolean    Risultato routine. True cancellazione effettuata. False errori riscontrati
	 */
	function delete()
	{
		$del = i5_delete_record($this->file);
		if ($del === false) {
			$this->msgerr();
            return false;
		}
		return true;	
	}
	 /**
	 * La funzione permette di posizionarsi su di una chiave. La chiave può essere un numero relativo di record o una chiave
	 * composta da un array con i vari campi di una vista logica.
	 * 
	 * @param boolean $is_included  Se i record con la chiave passata vengono inclusi nelle successive letture
	 * @param array $values
	 */
	function range_from($is_included, $values)
	{
		$range_from = i5_range_from($this->file, $is_included, $values);
		if ($range_from === false) {
			$tab = i5_error(); 
			throw new Exception($tab[2]." ".$tab[3], $tab[0]);
		}
	}
	
	/**
	 * La funzione posizione il file su si una determinata chiave che può essere un numero relativo di record o la chiave passata
	 * come array del tipo array("CAMPO1"=>'0002',"CAMPO02"=>'9000')
	 * 
	 * @param boolean $is_included   se vengono incluse anche le chivi passate
	 * @param array $values          array di chiavi
	 */
	function range_to($is_included, $values)
	{
		$range_to = i5_range_to($this->file, $is_included, $values);
		if ($range_to === false) {
			$tab = i5_error(); 
			throw new Exception($tab[2]." ".$tab[3], $tab[0]);
		}
	}
	
	function range_clear()
	{
		$range_clear = i5_range_clear($this->file);
		if ($range_clear === false) {
			$tab = i5_error(); 
			throw new Exception($tab[2]." ".$tab[3], $tab[0]);
		}
	}
	
	/**
	 * Funzione che effettua una chain sul record per numero relativo di record
	 * 
	 * @param integer $record_number
     * @return boolean/array    Risultato routine. Array se trovato. False se non trovato o errori riscontrati
	 */
	function chainPhisical($record_number)
	{
		$data_seek = i5_data_seek($this->file,$record_number);
		if ($data_seek === false) {
			$this->msgerr();
            return false;		}
		return $data_seet;
	}
	/**
	 * @param mixed $operator
	 * @param array $key_value
	 */
	function seek($operator, $key_value)
	{
		$seek = i5_seek($this->file, $operator, $key_value);
		if ($seek === false) {
			$tab = i5_error(); 
			throw new Exception($tab[2]." ".$tab[3], $tab[0]);
		}
	}
	/**
	 * Si posiziona sul file aperto per una chiave passata come parametro. Le successive READ utilizzeranno la chiave
	 * impostata dal setll per la lettura
	 * 
	 * @param mixed $operator  Operatore di compare per la chiave
	 * @param array $key_value
	 * @return array 
	 */
	function setll($operator, $key_value)
	{
		$seek = i5_seek($this->file, $operator, $key_value);
		if ($seek === false) {
			$tab = i5_error(); 
			throw new Exception($tab[2]." ".$tab[3], $tab[0]);
		}
		$this->firstReadE=True;
	}	
	/**
	 * Si posiziona sul file aperto per una chiave passata come parametro ed effettua la lettura del primo record	
	 * 
	 * @param mixed $operator  Operatore di compare per la chiave
	 * @param array $key_value
	 * @return array 
	 */

	function chain($operator, $key_value, $nolook=True)
	{
		$seek = i5_seek($this->file, $operator, $key_value);
		if ($seek === false) {
			$this->msgerr();
            return false;
				}
	    $option = I5_READ_SEEK;
            
		$fetch_assoc = $this->internal_read($option, $nolook);
		if ($fetch_assoc === false) {
			$this->msgerr();
            return false;
		}
		return $fetch_assoc;
	}	
    private function internal_read($option, $nolook=True)
	{
		$fetch_assoc = i5_fetch_array($this->file, $option);
		if (($this->edit==True) AND ($nolook==True))
		{
			$result = i5_edit($this->file);
		}
			if ($fetch_assoc ==! false) {
			$this->lastRec = $fetch_assoc;
			return $fetch_assoc;
		}
		else {
			$this->msgerr();
            return false;
		}
	}
	/**
	 * La funzione ritorna il numero relativo del record corrente
	 * 
	 * @return integer
	 */
	function nrel()
	{
		$nrel = i5_bookmark($this->file);
		if ($nrel ==! false) {
			return $nrel;
		}
		else {
			$this->msgerr();
            return false;
		}
		
	}
	
	/**
	 * La funzione ritorna una array con le chiavi del file aperto
	 * 
	 * @return array
	 */
	function get_keys()
	{
		$keys = i5_get_keys($this->file);
		if ($keys ==! false) {
			return $keys;
		}
		else {
			$tab = i5_error(); throw new Exception($tab[2]." ".$tab[3], $tab[0]);
		}
	}
	
	/**
	 * La funzione legge una riga del file posizionando il contenuto letto all'interno di una array con chiavi numeriche
	 * 
	 * @param int[optional] $option
	 * @return array
	 */
	function fetch_array($option = null)
	{
		if (isset($option)) {
		    $fetch_array = i5_fetch_array($this->file, $option);
		}
		else {
			$fetch_array = i5_fetch_array($this->file);
		}
		if ($fetch_array ==! false) {
			return $fetch_array;
		}
		else {
			$tab = i5_error(); 
			throw new Exception($tab[2]." ".$tab[3], $tab[0]);
		}
	}
	
	/**
	 * @param int[optional] $option
	 * @return array
	 */
	function fetch_row($option)
	{
		if (isset($option)) {
		    $fetch_row = i5_fetch_row($this->file, $option);
		}
		else {
			$fetch_row = i5_fetch_row($this->file);
		}
		if ($fetch_row ==! false) {
			return $fetch_row;
		}
		else {
			$tab = i5_error(); 
			throw new Exception($tab[2]." ".$tab[3], $tab[0]);
		}
	}
	
	/**
	 * @param int[optional] $option
	 * @return array
	 */
	function fetch_assoc($option)
	{
		if (isset($option)) {
		    $fetch_assoc = i5_fetch_assoc($this->file, $option);
		}
		else {
			$fetch_assoc = i5_fetch_assoc($this->file);
		}
		if ($fetch_assoc ==! false) {
			return $fetch_assoc;
		}
		else {
			$tab = i5_error(); 
			throw new Exception($tab[2]." ".$tab[3], $tab[0]);
		}
	}
	/**
	 * La funzione ritorna l'ultimo errore verificatosi all'interno della classe
	 * 
	 * @return string
	 */
	
	function getLastErr()
	{
		return $this->LastErr;
	}
	/**
	 * Costruisce il messaggio di errore con numero errore e messaggio di errore.
	 * @return string
	 */

	function msgerr()
	{
	$this->LastErr = i5_errno()." ".i5_errormsg();
    if ($this->debug) echo $this->LastError;
	}
	/**
	 * Funzione per lettura in READE del file. Prima di leggere occore fare un SETLL che imposta anche la chiave
	 * 
	 * @param bool $nolook Indicatore di lock per il record letto. Di default se apertura in IO il record viene lockato
	 * 
	 * @return array
	 */
	function reade($nolook=False)
	{
            // Verifico se è la prima lettura per impostare il parametro di lettura di READE
		    if ($this->firstReadE==True)
            {
		    	 $option = I5_READ_SEEK;
                 $this->firstReadE=False;
            }     
            else
			{
             $option = I5_READ_KEYNEXTEQ;

            }
            
		    $fetch_assoc = $this->internal_read($option, $nolook);

		if ($fetch_assoc ==! false) {
			$this->lastRec = $fetch_assoc;
			return $fetch_assoc;
		}
		else {
			$this->msgerr();
            return false;
		}
	}
	/**
	 * Funzione per lettura in READ del file. Prima di leggere occore fare un SETLL che imposta anche la chiave
	 * 
	 * @param bool $nolook Indicatore di lock per il record letto. Di default se apertura in IO il record viene lockato
	 * 
	 * @return array
	 */
	function read($nolook=False)
	{
        $option = I5_READ_NEXT;      
	    $fetch_assoc = $this->internal_read($option, $nolook);

		if ($fetch_assoc ==! false) {
			$this->lastRec = $fetch_assoc;
			return $fetch_assoc;
		}
		else {
			$this->msgerr();
            return false;
		}
	}
			/**
	 * @param int[optional] $option
	 * @return array
	 */
	function readlast($nolook = False)
	{
        $option = I5_READ_LAST;      
	    $fetch_assoc = $this->internal_read($option, $nolook);

		if ($fetch_assoc ==! false) {
			$this->lastRec = $fetch_assoc;
			return $fetch_assoc;
		}
		else {
			$this->msgerr();
            return false;
		}
	}
			/**
	 * @param int[optional] $option
	 * @return array
	 */
	function readp()
	{
        $option = I5_READ_PREV;          
	    $fetch_assoc = i5_fetch_array($this->file, $option);

		if ($fetch_assoc ==! false) {
			$this->lastRec = $fetch_assoc;
			return $fetch_assoc;
		}
		else {
			$this->msgerr();
            return false;
		}
	}
	/**
	 * @param int[optional] $option
	 * @return object
	 */
	function fetch_object($option)
	{
		if (isset($option)) {
		    $fetch_object = i5_fetch_object($this->file, $option);
		}
		else {
			$fetch_object = i5_fetch_object($this->file);
		}
		if ($fetch_object ==! false) {
			return $fetch_object;
		}
		else {
			$tab = i5_error();
			return false; 
		}
	}
	
	/**
	 * Ritorna un array con le informazioni sui campi letti da una fetch o read. Può essere passato un parametro aggiuntivo
	 * per specificare la variabile di cui si vogliono reperire le informazioni.
	 * 
	 * @param mixed[optional] $field
	 * @return array
	 */
	function info($field = null)
	{
		if (isset($field)) {
			$info = i5_info($this->file, $field);
		}
		else {
			$info = i5_info($this->file);
		}
		if ($info ==! false) {
			return $info;
		}
		else {
			$tab = i5_error();
			return false; 
				
		}
	}
	
	/**
	 * Ritorna la lunghezza del campo passato come parametro. Passare l'indice numerico o il nome del campo
	 * @param mixed[optional] $field
	 * @return integer
	 */
	function field_length($field)
	{
		$len = i5_field_len($this->file, $field);
		if ($len ==! false) {
			return $len;
		}
		else {
			$tab = i5_error(); 
			return false;
			
		}		
	}
	
	/**
	 * Ritorna il nome del campo passato come parametro. Passare l'indice numerico o il nome del campo
	 * 
	 * @param mixed[optional] $field
	 * @return integer
	 */
	function field_name($field)
	{
		$name = i5_field_name($this->file, $field);
		if ($name ==! false) {
			return $name;
		}
		else {
			$tab = i5_error();
			return false; 
			
		}
	}
	
	/**
	 * Ritorna lo spazio occupato dal campo passato come parametro
	 * 
	 * @param mixed[optional] $field
	 * @return integer
	 */
	function field_scale($field)
	{
		$scale = i5_field_scale($this->file, $field);
		if (!is_bool($scale)) {
			return $scale;
		}
		else {
			$tab = i5_error(); 
			return false;
		}
	}
	
	/**
	 * Ritorna il tipo del campo passato come parametro
	 * 
	 * @param mixed[optional] $field
	 * @return string
	 */
	function field_type($field)
	{
        $type = i5_field_type($this->file, $field);
        if ($type ==! false) {
        	return $type;
        }
        else {
        	$tab = i5_error();
        	return false; 
        	
        }
	}

	/**
	 * Ritorna il valore di un campo passato come parametro
	 * 
	 * @param mixed $field
	 * @return mixed
	 */
	function result($field)
	{
		$result = i5_result($this->file, $field);
		if ($result ==! false) {
			return $result;
		}
		else {
			$tab = i5_error();
			return false; 
			}
	}
	
	/**
	 * Ritorna un array con la lista dei campi con le loro caratteristiche
	 * 
	 * @return array/boolean
	 */
	function list_fields()
	{
		$list = i5_list_fields($this->file);
		if ($list ==! false) {
			return $list;
		}
		else {
			$tab = i5_error();
			return false; 
			
		}
	}
	/**
	 * Funzione che ritorna il numero di campi ritornati dalla lettura del file con uno dei metodi fetch o read
	 * 
	 * @return integer
	 */
	function num_fields()
	{
		$num = i5_num_fields($this->file);
		if ($num ==! false) {
			return $num;
		}
		else {
			$tab = i5_error(); 
			throw new Exception($tab[2]." ".$tab[3], $tab[0]);
		}
	}
	/**
	 * Distruttore della funzione chiamato in automatico
	 */	
	function __destruct()
	{
		i5_free_file($this->file);
	}
	function set_options($job_name = null, $sql_naming = null, $decimal_point = null, $code_page_file = null, $init_lib = null)
	{
		$options = array(I5_OPTIONS_JOBNAME      => $job_name, 
		                 I5_OPTIONS_SQLNAMING    => $sql_naming, 
		                 I5_OPTIONS_DECIMALPOINT => $decimal_point, 
		                 I5_OPTIONS_CODEPAGEFILE => $code_page_file, 
		                 I5_OPTIONS_INITLIBL     => $init_lib);
		
		foreach ($options as $key=>$value) {
			if (isset($options[$key])) {
				$this->options[$key] = $value;
			}
		}
		
	}
}
?>