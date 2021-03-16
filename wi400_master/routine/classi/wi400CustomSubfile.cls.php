<?php

/**
 * @name wi400CustomSubfile 
 * @desc Classe per la gestione delle azioni
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author 
 * @version 1.01 03/03/2010
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

class wi400CustomSubfile {

	private $cols = array();
	private $fullTableName;
	private $idList;
	protected $importColumn = array();
	protected $importParameters = array(
		'ALLOW_DELETE'=>True
	);
	/**
	 * @desc: Setto la Lista che mi ha chiamato
	 * @param unknown $idList
	 */
	public function setIdList($idList) {
		$this->idList = $idList;
	}
	/**
	 * Ritorna il nome completo della tabella (subfile) generata
	 *
	 * @return string
	 */
	public function getFullTableName() {
		return $this->fullTableName;
	}
	
	/**
	 * Impostazione del nome completo della tabella (subfile) da generare
	 *
	 * @param string $fullTableName	: nome completo della tabella
	 */
	public function setFullTableName($fullTableName) {
		$this->fullTableName = $fullTableName;
	}
	
	/**
	 * Aggiunta di una colonna al subfile
	 *
	 * @param string $columnId	: ID di una colonna
	 * @param array $columnDesc	: caratteristiche di una colonna - array contenente:
	 * 								1: il tipo di dato (1=stringa, 2=numero)
	 * 								2: la lunghezza del dato
	 * 								3: se il dato è un numero, il numero di decimali in esso presenti
	 * 								4: il titolo della colonna
	 */
	public function addCol($columnId, $columnDesc){
    	$this->cols[$columnId] = $columnDesc;
    }
    
    /**
     * Impostazione delle colonne del subfile a partire da un array
     *
     * @param unknown_type $cols
     */
    public function setCols($cols){
    	$this->cols = $cols;
    }
    
    /**
     * Recupero delle colonne del subfile
     *
     * @return array
     */
    public function getCols(){
    	return $this->cols;
    }
    /**
     * Inizializzo le colonne del subfile
     *
     * @return array
     */
    public function getColsInz(){
    	global $db;
    	$colsinz = array();
    	foreach ($this->cols as $key => $vals) {
    		$valore = $db->inzDsValue($vals['DATA_TYPE_STRING']);
    		/*switch($vals['DATA_TYPE_STRING']) {
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
					$valore = "0001-01-01-00.00.00.0000";
					break;
				case "DATE";
					$valore = "0001-01-01";
					break;
			}*/
    		$colsinz[$key]=$valore;
    	}
    	return $colsinz;
    }   
	public function init($parameters){
	}
	
	public function start($subfile){
	}
	
	public function body($row, $parameters){
		return array();
	}
	
	/**
	 * Impostazione di una riga extra
	 *
	 * @param string $extraDesc	: descrizione delle riga aggiuntiva
	 * @param unknown_type $parameters
	 * @return array	Ritorna i valori della riga extra
	 */
	public function extraRow($extraDesc, $parameters){
		return array();
	}
	
	public function extraRowExport($extraDesc, $parameters){
		return $this->extraRow($extraDesc, $parameters);
	}		
	
	public function end($subfile){
		
	}
	
	/**
	 * @desc import_set_column: Setta le colonne obbligatorie per l'import
	 */
/*	
	public function import_set_column() {
		$this->importColumn = array();
	}
*/	
	/**
	 * @desc import_get_column: Ritorna le colonne obbligatorie su foglio EXCEL
	 */
	public function import_get_column() {
		return $this->importColumn;
	}
	
	/**
	 * @desc import_check_riga: controlla una riga di import passata
	 * @param unknown $row
	 */
	public function import_check_riga($row) {
//	public function import_check_riga($row, $cols_titles) {
		$errori = array();
		
		return $errori;
	}
	
	/**
	 * @desc import_write_row: Scrive la riga di import sul SUBFILE
	 */
	public function import_write_row($row) {
//	public function import_write_row($row, $cols_import) {
//	public function import_write_row($row, $cols_values) {
		return true;
	}

	public function import_set_parameter($parameter, $value) {
		$this->importParameters[$parameter]=$value;
	}
	
	public function import_get_parameters() {
		return $this->importParameters;
	}

}
?>