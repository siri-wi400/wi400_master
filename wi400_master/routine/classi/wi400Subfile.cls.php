<?php
class wi400Subfile {


	private $idTable;
	private $numRec;
	private $lastErr;
	private $libre;
	private $createTableName;
	private $fullTableName;
	private $field;
	private $numeroDelay;
	private $firstTime;
	private $stmt;
	public  $array;
	private $delay;
	private $keys;
	private $keysDrop;
	private $database;
	private $columns = null;
	
	private $modulo;

	private $cfgFileName;
	
	private $sql;
	private $parameters;
	private $persistence;
	private $totals;
	
	private $extraRows;
	private $extraRowsExport;
	
	private $finalized;
	private $inizialized;
	
	private $timeLimit;
	private $customTableName;
	

	
    /**
     * @desc getCustomTableName: Recupero il nome custom definito per la tabella del subfile
	 * @return the $customTableName
	 */
	public function getCustomTableName() {
		return $this->customTableName;
	}

	/**
	 * @desc setCustomTableName: Definisco un nome custom per la tabella del subfile
	 * @param field_type $customTableName
	 */
	public function setCustomTableName($customTableName) {
		global $settings;
		$this->customTableName = $customTableName;
		$this->createTableName = $customTableName;
		$this->fullTableName = $this->libre.$settings['db_separator'].$customTableName;
		
	}
	/**
	 * setSQL() : Imposto la query per il caricamento guida dei dati. *AUTOBODY nel caso la logica si
	 *            tutta contenuta all'interno del subfile
	 * @param unknown $sql
	 */
	public function setSql($sql){
    	$this->sql = $sql;
    }
    
    public function getSql(){
    	return $this->sql;
    }
	
    public function isFinalized(){
    	return $this->finalized;
    }
    
    public function setFinalized($finalized){
    	$this->finalized = $finalized;
    }
    
    public function isInizialized(){
    	return $this->inizialized;
    }
    
    public function setInizialized($inizialized){
    	$this->inizialized = $inizialized;
    }
    
    
    public function getConfigFileName(){
    	return $this->cfgFileName;	
    }
    
    public function setConfigFileName($cfn){
    	$this->cfgFileName = $cfn;
    }
    
	public function __construct($db, $idTable, $libre="", $delay=10){
		 
		global $settings;
		
		$this->idTable = $idTable;
		$this->cfgFileName = $idTable;
		//$this->db = $db;
		$this->database = $settings['database'];
		$this->libre = $libre;
		if ($this->libre=="") {
			$this->libre = $settings['db_temp'];
		}
		$this->numeroRecord = 0;
		$this->numeroDelay = 0;
		$this->field = array();
		$this->firstTime = True;
		$this->stmt = "";
		$this->delay=$delay;
		$this->key = array();
		$this->modulo='';
		 
		$this->sql = "";
		$this->cols = array();
		$this->totals = false;
		$this->extraRows = array();
		$this->extraRowsExport = array();
		$this->parameters = array();
		$this->keys = array();
		$this->keysDrop = array();
		$this->persistence = true;
		$this->finalized = false;
		$this->inizialized = false;
		
//	    In Oracle(versione testata 11g) i nomi delle tabelle possono arrivare al massimo a 30 caratteri
		if ($this->database == "OCI_PDO_ORACLE"){
			$this->createTableName = strtoupper(substr($this->idTable,0,14)."_".substr(session_id(),0,15));
			$this->fullTableName = strtoupper($this->libre.$settings['db_separator'].substr($this->idTable,0,14)."_".substr(session_id(),0,15));
		}
		else {
			$this->createTableName = strtoupper($this->idTable."_".session_id());
			$this->fullTableName = $this->libre.$settings['db_separator'].strtoupper($this->idTable."_".session_id());
		}
		$timeLimite = 600;
		if (isset($settings['subfile_time_limit'])) {
			$this->timeLimit = $settings['subfile_time_limit'];
		}
		
	}

	public function addTotal($columnId = "", $value = 0){
		// Inizializzo
		if (!$this->totals) $this->totals = array();
		if ($columnId != ""){
			if (isset($this->totals[$columnId])){
	    		$this->totals[$columnId] = $this->totals[$columnId] + $value;
			}else{
				$this->totals[$columnId] = $value;
			}
		}
    }
    
    public function setTimeLimit($tl){
    	$this->timeLimit = $tl;
    }
    public function getTimeLimit(){
    	return $this->timeLimit;
    }
    
    public function setTotals($totals){
    	$this->totals = $totals;
    }
    
    public function getTotals(){
    	return $this->totals;
    }
    
    public function setExtraRows($extraRows){
    	$this->extraRows = $extraRows;
    }
    
    public function getExtraRows(){
    	return $this->extraRows;
    }
    
    public function addExtraRow($extraDesc, $extraArray = array()){
    	$this->extraRows[$extraDesc] = $extraArray;
    }
    
    public function addExtraRowExport($extraDesc, $extraArray=array()) {
    	$this->extraRowsExport[$extraDesc] = $extraArray;
    }
    
    public function getExtraRowsExport() {
    	return $this->extraRowsExport;
    }

    /*
     * 		isKey indica che il parametro verrà valutato per l'eventuale rigenerazione del subfile. 
     *  	Se indicato droptable, il parametro provocherà anche la cancellazione fisica della tabella
     */
    public function addParameter($parameterKey, $parameterValue, $isKey = false, $dropTable = false){
    	$this->parameters[$parameterKey] = $parameterValue;
    	if ($isKey){
    		$this->keys[$parameterKey] = $parameterValue;
    		if ($dropTable){
    			$this->keysDrop[] = $parameterKey;
    		}
    	}
    }
    
    public function getParameters(){
    	return $this->parameters;
    }
    
    public function getKeys(){
    	return $this->keys;
    }
    
	public function getKeysDrop(){
    	return $this->keysDrop;
    }
    
    public function getParameter($parameter){
    	return $this->parameters[$parameter];
    }
    
    public function setParameters($parameters){
    	$this->parameters = $parameters;
    }
    
	public function getIdTable(){
		return $this->idTable;
	}
	
	public function setPersistence($persistence){
		$this->persistence = $persistence;
	}
	
	public function getPersistence(){
		return $this->persistence;
	}
	
	
	public function exist(){
		$subexist = false;
		$subFileTemp = wi400Session::load(wi400Session::$_TYPE_SUBFILE, $this->idTable);
		if ($subFileTemp !== false && $subFileTemp->isFinalized()){
			$subexist = true;
		} else {
			//echo "<br>NON ESISTE ".$this->idTable;
			//echo "<br>Contenuto-->".var_dump($subFileTemp);
			//echo "<br>finalized ".var_dump($subFileTemp->isFinalized());
		}
		return $subexist;
	}
	
	public function delete(){
		global $db;
		$subFileTemp = wi400Session::load(wi400Session::$_TYPE_SUBFILE, $this->idTable);
		if ($subFileTemp !== false){
			if ($db->ifExist($this->createTableName ,$this->libre )) {
				$sql = "DELETE FROM ".$this->fullTableName;
				$db->query($sql, False);
				$this->setInizialized(false);
				$this->setFinalized(false);
				wi400Session::save(wi400Session::$_TYPE_SUBFILE, $this->idTable, $this);
				// Pulizia Array colonne serializzato
				if (!isset($this->columns)) {
					$this->columns = null;
				}
			}
		}
	}
	
	public function drop(){
		global $db, $settings;
		
		$subFileTemp = wi400Session::load(wi400Session::$_TYPE_SUBFILE, $this->idTable);
		
		if ($subFileTemp !== false){
			$sql = "DROP TABLE ".$this->fullTableName;
			$db->query($sql, False);
			
//			$file = "DB_".$this->customTableName."_".$this->libre.".dat";
			$file = "DB_".$this->createTableName."_".$this->libre.".dat";
			
//			$file_path = $settings['data_path']."COMMON/serialize/".$file;
			$file_path = wi400File::getCommonFile("serialize", $file);
			
			if(file_exists($file_path)) {
				unlink($file_path);
			}
			
			wi400Session::delete(wi400Session::$_TYPE_SUBFILE, $this->idTable);
			
			// Pulizia Array colonne serializzato
			if (!isset($this->columns)) {
				$this->columns = null;
			}
		}
	}
	
	public function inz($array, $drop=False){
		global $db;
		$this->array = array();
		$this->numeroRecord = 0;
		$this->numeroDelay = 0;
		$this->firstTime = True;
		$this->field = array();
		
		$array['NREL']=$db->singleColumns("3", "9", "0");
		
		// A seconda del parametro passato pulisco o cancello la tabella
		if ($this->exist()){

			if ($drop){
				$sql = "DROP TABLE ".$this->fullTableName;
				$db->query($sql, False);
				$db->createTable($this->createTableName , $this->libre, $array, true);
				$this->setFinalized(False);
				
			}else{
				$sql = "DELETE FROM ".$this->fullTableName;
				$db->query($sql, False);
				$this->setFinalized(False);
			}

		} else {
			
			$subFileTemp = wi400Session::load(wi400Session::$_TYPE_SUBFILE, $this->idTable);
			if ($subFileTemp === false || !$subFileTemp->isInizialized()){
				$db->createTable($this->createTableName , $this->libre, $array, true);
				$this->setFinalized(False);
			}
			
		}
		
		$this->setInizialized(True);
		
		wi400Session::save(wi400Session::$_TYPE_SUBFILE, $this->idTable, $this);
		
		// Carico sulla schiera della classe gli elementi dell'array dei field
		foreach($array as $key=>$element){
			$this->field[$key]= $key;
		}
	}
	
	public function writeSingle($array = array()){
		global $db;
		
		// Numero relativo univoco di record
        $this->numeroRecord = $this->numeroRecord + 1;
		$array[] = $this->numeroRecord;

		foreach ($array as $arrayValue) {
			if ($arrayValue == null){
				$arrayValue = null;
			}
			$this->array[] = $arrayValue;
		}
		
		$this->numeroDelay = $this->numeroDelay + 1;

		if ($this->numeroDelay == $this->delay){
			// Se è la prima write devo fare prima la prepare
			if ($this->firstTime){
				$this->stmt = $db->prepare("INSERT", $this->fullTableName ,$this->key, $this->field, $this->delay);
			}
			$this->firstTime=False;
			$result = $db->execute($this->stmt, $this->array);
			$this->numeroDelay= 0;
			$this->array = array();
		}
	}
	
	public function write($array = array()){
		global $db;
		$single=True;
		foreach ($array as $arrayValue) {
			if (is_array($arrayValue)) {
				// Chiamata ricorsiva
				$this->write($arrayValue);
				$single=False;
			} else {
				if ($arrayValue === null){
					$arrayValue = null;
				}
				$this->array[] = $arrayValue;
			}
		}
		if ($single) {
			// Numero relativo univoco di record
	        $this->numeroRecord = $this->numeroRecord + 1;
			$this->array[] = $this->numeroRecord;	
			$this->numeroDelay = $this->numeroDelay + 1;

			if ($this->numeroDelay == $this->delay){
				// Se è la prima write devo fare prima la prepare
				if ($this->firstTime){
					$this->stmt = $db->prepare("INSERT", $this->fullTableName ,$this->key, $this->field, $this->delay);
				}
				$this->firstTime=False;
				$result = $db->execute($this->stmt, $this->array);
				$this->numeroDelay= 0;
				$this->array = array();
			}
			
			// Incremento contatore totali
			if ($this->totals){
				$countSubCol = 0;
				foreach ($this->field as $colId => $colDesc){
					if (isset($this->totals[$colId])){
						$arrayValues = array_values($array);
						if (strpos($this->totals[$colId], "EVAL:")===0){
							// do nothing
						}else{
							$this->addTotal($colId,$arrayValues[$countSubCol]);
						}
					}
					$countSubCol++;
				}
			}
		}
	}
	public function  write_direct($array){
		global $db;
		if ($this->firstTime)
		{
			$this->stmt = $db->prepare("INSERT", $this->fullTableName ,$this->key, $this->field, false, $this->delay);
		}
		$this->numeroRecord = $this->numeroRecord + 1;
		$array[] = $this->numeroRecord;
		$this->firstTime=False;
		$result = $db->execute($this->stmt, $array);
	}
	/**
	 * @desc flush() Forza la scrittura del buffer ancora in memoria senza chiudere il subfile
	 */
	public function flush(){
		global $db;
	
		if ($this->numeroDelay>0){
			$stmt = $db->prepare("INSERT", $this->fullTableName ,$this->key, $this->field, $this->numeroDelay);
			$result = $db->execute($stmt, $this->array);
			$db->freestmt($stmt);
		}
		//$this->firstTime=True;
		$this->array = array();
		$this->numeroDelay=0;
	}	
	public function finalize(){
		global $db;
		
		if ($this->numeroDelay>0){
			$this->stmt = $db->prepare("INSERT", $this->fullTableName ,$this->key, $this->field, $this->numeroDelay);
			$result = $db->execute($this->stmt, $this->array);
		}
		$this->stmt="";
		$this->setFinalized(True);
		$subFileTemp = wi400Session::save(wi400Session::$_TYPE_SUBFILE, $this->idTable, $this);
	}
	
	public function getFullTableName(){
	
	    //global $settings;
	    return $this->fullTableName;
		//return $this->libre.$settings['db_separator'].$this->idTable."_".session_id();
	}
	
	public function getTable()
	{
		return $this->fullTableName;
	}
	public function getTableName()
	{
		return $this->createTableName;
	}
	public function getTotalRecord()
	{
		return $this->numeroRecord;
	}
	public function getModulo()
	{
		return $this->modulo;
	}	
	public function setModulo($modulo)
	{
		$this->modulo=$modulo;
	}

	public function getFields() {
		return $this->field;
	}
	/**
	 * @desc Questa funzione aggiorna i record di un subfile in base all'array di selezione di una lista in modo automatico e setta il flag di modificato sepresente
	 * @param array $arraySelezione
	 */
	public function updateRecordSelected($arraySelezione, $arrayValue=array(), $arrayExtra=array()) {
		global $db;
		$column = $this->getColumns();
		$columnMeta = $this->getColumnsMetaData();
		//$column=$db->columns($this->getTableName(), "",True, "", "PHPTEMP");
		$columns = array_flip($column);
		$count=0;
		// Confronto le colonne del subfile con i value passati per capire cosa aggiornare
		foreach ($arrayValue as $chiave => $value) {
			$dati = array();
			foreach ($columns as $key=>$indice){
				if (isset($value[$key]) && $key!="NREL") {
					//echo "<br>META:".$columnMeta[$key]['DATA_TYPE_STRING'];
					if ((isset($columnMeta[$key]['DATA_TYPE_STRING'])) && ($columnMeta[$key]['DATA_TYPE_STRING']=="NUMERIC" || $columnMeta[$key]['DATA_TYPE_STRING']=="DECIMAL")) {
						$dati[$key]=str_replace(".","",$value[$key]);
						$dati[$key]=str_replace(",",".",$dati[$key]);
					} else {
						$dati[$key]=$value[$key];
					}
				}
			}
			// Controllo se mi sono stati passati extra value
			if (sizeof($arrayExtra)!=0) {
				if (isset($arrayExtra[$count])) {
					foreach ($columns as $key){
						if (isset($arrayExtra[$count][$key]) && $key!="NREL") {
							if ((isset($columnMeta[$key]['DATA_TYPE_STRING'])) && ($columnMeta[$key]['DATA_TYPE_STRING']=="NUMERIC" || $columnMeta[$key]['DATA_TYPE_STRING']=="DECIMAL")) {
								$dati[$key]=str_replace(".","",$arrayExtra[$count][$key]);
								$dati[$key]=str_replace(",",".",$dati[$key]);
							} else {
								$dati[$key]=$arrayExtra[$count][$key];
							}
						}
					}					
				}
			}
			// Aggiungo il campo modify per dire che è stato toccato
			if (!isset($dati['IS_MODIFY']) && isset($columns['IS_MODIFY'])) {
				$dati['IS_MODIFY']='X';
			}
			// Prepare dello statement
			$keyName = array("NREL"=>'?');
			if (!isset($stmtUpdate)) {
				$stmtUpdate = $db->prepare("UPDATE", $this->getTable(), $keyName, array_keys($dati));
			}
			//die();
			$db->execute($stmtUpdate, array_merge($dati, array("NREL"=>$arraySelezione[$count]['NREL'])));
			$count++;
		}
	}
	/**
	 * @desc Questa funzione aggiorna i record di un subfile in base all'array di selezione di una lista in modo automatico e setta il flag di modificato sepresente
	 * @param array $arraySelezione
	 */
//	public function updateRecord($nrel, $arrayValue=array()) {
	public function updateRecord($nrel, $arrayValue=array(), $is_modify=true) {
		global $db;
		static $stmtUpdate;
		//print_r($arrayValue);
		$column = $this->getColumns();
		//$column=$db->columns($this->getTableName(), "",True, "", "PHPTEMP");
		$columns = array_flip($column);
		// Confronto le colonne del subfile con i value passati per capire cosa aggiornare
		$dati = array();
		foreach ($arrayValue as $chiave => $value) {
				if (isset($columns[$chiave]) && $chiave!="NREL") {
					$dati[$chiave]=$value;
				}
		}
		// Aggiungo il campo modify per dire che è stato toccato
//		if (!isset($dati['IS_MODIFY']) && isset($columns['IS_MODIFY'])) {
		if ($is_modify===true && !isset($dati['IS_MODIFY']) && isset($columns['IS_MODIFY'])) {
			$dati['IS_MODIFY']='X';
		}
		// Prepare dello statement
		$keyName = array("NREL"=>'?');
//		if (!isset($stmtUpdate)) {
				$stmtUpdate = $db->prepare("UPDATE", $this->getTable(), $keyName, array_keys($dati));
//		}
		$db->execute($stmtUpdate, array_merge($dati, array("NREL"=>$nrel)));
		}
	/**
	 * @desc Questa funzione ritorna le colonne del subfile
	 */
	public function getColumns() {
		global $db, $settings;
		//print_r($arrayValue);
		if (!isset($this->columns)) {
			$this->columns=$db->columns($this->getTableName(), "",True, "", $settings['db_temp']);
		}
		return $this->columns;		
	}
	public function getRecordById($nrel) {
		global $db;
		
		$sql = "SELECT * FROM ". $this->getTable(). " WHERE NREL=".$nrel;
		$result = $db->singleQuery($sql);
		return $db->fetch_array($result);
		
	}
	/**
	 * @desc Questa funzione ritorna le colonne del subfile
	 */
	public function getColumnsMetaData() {
		global $db, $settings, $root_path, $routine_path;
		if (isset($this->modulo)) {
//		    $path = p13n("modules/".$this->modulo.'/subfile/'.$this->idTable.".cls.php");
		    $path = p13n("modules/".$this->modulo.'/subfile/'.$this->cfgFileName.".cls.php");
		    require_once $path;
		} else {	
//			require_once $routine_path . "/classi/subfile/$this->idTable.cls.php";
			require_once $routine_path . "/classi/subfile/$this->cfgFileName.cls.php";
		}
		// Ripristinato .. se min getArrayCampi c'è un $this si spacca tutto.
		$parameters= array();
//		$classe = new $this->idTable($parameters);
		$classe = new $this->cfgFileName($parameters);
		//$classe = $idSubfile;
		$cols = $classe->getArrayCampi ();
		return $cols;
	}
	/**
	 * @desc Dumpa il contenuto del subfile su un file TXT Formattato
	 */
	public function dumpFormattedTXT($fileName="", $parameters = array()) {
		global $db;
		
		if ($fileName=="") {
			$fileName  = date("YmdHis")."_".$this->IdTable.".txt";
			$temp = "export";
			$fileName = wi400File::getUserFile($temp, $fileName);
		}
		if (!$handle = fopen($fileName, 'w')) {
			return false;
		} else {
			// Reperisco la struttura delle colonne
			$cols = $this->getColumnsMetaData();
			// Ciclo dei lettura sui dati del subfile
			$sql = "SELECT * FROM ".$this->getFullTableName();
			$result = $db->query($sql);
			while ($row = $db->fetch_array($result)) {
				$string = $this->dumpFormattedTXTRow($row, $parameters);
				$string.="\r\n";
				fwrite($handle, $string);
			}
			return $fileName;
		}
		
	}	
	/**
	 * @desc Dumpa Row il contenuto del subfile su un file TXT Formattato
	 */
	public function dumpFormattedTXTRow($row , $parameters) {
		global $db;
		static $cols;
		$string = "";
		if (!isset($cols)) $cols = $this->getColumnsMetaData();
		$excludeColumn = array();
		if (isset($parameters['EXCLUDE_COLUMN'])) {
			$excludeColumn = $parameters['EXCLUDE_COLUMN'];
		}
		$go = False;
		foreach ($row as $key => $value) {
			if ($key!="NREL" && !in_array($key, $excludeColumn)) {
				// Se colonna di partenza
				if (isset($parameters['START_COLUMN'])) {
					if ($key==$parameters['START_COLUMN'] && $go==False) {
						$go = True;
					} elseif ($go==False) {
						continue;
					}
				}
				// Se colonna di partenza
				if (isset($parameters['END_COLUMN'])) {
					if ($key==$parameters['END_COLUMN']) {
						break;
					}
				}
				// Verifico se Colonna di BREAK RIGA
				$metaData = $cols[$key];
				$dato="";
				$type = strtoupper($metaData['DATA_TYPE_STRING']);
				if ($type=='CHAR' || $type=='VARCHAR') {
					$dato = str_pad(substr($value, 0, $metaData['LENGTH_PRECISION']), $metaData['LENGTH_PRECISION'], " ", STR_PAD_RIGHT);
				}
				if ($type=='NUMERIC' || $type=='DECIMAL') {
					$precision = pow(10, $metaData['NUM_SCALE']);
					$dato = floor($value*$precision)/$precision;
					//echo "<br>DATO:".$dato;
					$dato = number_format($dato, $metaData['NUM_SCALE'], "","");
					//echo "<br>DATO:".$dato;
					$dato = str_pad(substr($dato, 0, $metaData['LENGTH_PRECISION']), $metaData['LENGTH_PRECISION'], "0", STR_PAD_LEFT);
					// Verifico se deve essere esposto il segno
					$prmseg ="";
					if (isset($parameters['SEGNO']['ALL'])) {
						$prmseg = $parameters['SEGNO'];
					}
					if (isset($parameters['SEGNO'][$key])) {
						$prmseg = $parameters['SEGNO'][$key];
					}
					if (is_array($prmseg)) {
						$segno = "+";
						if ($value <0) $segno = "-";
						// Tipo Segno Positivo
						if (isset($prmseg['NOPOSITIVO'])) {
							if ($segno=="+") $segno = " ";
						}
						// Includi
						$includi = True;
						if (isset($prmseg['INCLUDI'])) {
							$includi = $prmseg['INCLUDI'];
						}
						$start = 0;
						if ($includi) $start = 1;
						// Posizione
						$posizione = "RIGHT";
						if (isset($prmseg['POSIZIONE'])) {
							$posizione = $prmseg['POSIZIONE'];
						}
						switch ($posizione) {
							case "RIGHT":
								$dato = substr($dato, 1).$segno;
								break;
							case "LEFT":
								$dato = $segno.substr($dato, 1);
								break;
						}
					}
				}
				$string .=$dato;
			}
				
		}	
		return $string;	
	}
	/**
	 * @desc Questa funzione pulisce un record passato come parametro
	 **/
	public function clearRecord($nrel) {
		global $db;
		//print_r($arrayValue);
		$column = $this->getColumnsMetaData();
		//$columns = array_flip($column);
		$keyName = array("NREL"=>'?');
		$dati = array();
		foreach ($column as $key => $value) {
			//echo $value;
			if ($key!="NREL") {
				$dati[$key]=$db->inzDsValue($value['DATA_TYPE_STRING']);
				/*if  ((isset($value['DATA_TYPE_STRING'])) && ($value['DATA_TYPE_STRING']=="NUMERIC" || $value['DATA_TYPE_STRING']=="DECIMAL") && ($value['NUM_SCALE']>=0)){
					$dati[$key]=0;
				} else {
					$dati[$key]="";
				}*/
			}
		}
		$stmtUpdate = $db->prepare("UPDATE", $this->getTable(), $keyName, array_keys($dati));
		$db->execute($stmtUpdate, array_merge($dati, array("NREL"=>$nrel)));
	}
	/**
	 * @desc Questa funzione cancella un record passato come parametro
	 **/
	public function deleteRecord($nrel) {
		global $db;
		//print_r($arrayValue);
		$sql = "DELETE FROM ".$this->getTable()." WHERE NREL=$nrel";
		$db->query($sql);
	}
}
?>
