<?php

/**
 * Classe per il riordino sequenziale delle righe in una lista
 *
 */
class wi400SortList extends wi400Action  {

	private $sortKeys;
	private $sortColumns;
	private $sorter;
	private $sortTable;
	private $sortMask="";
	private $where = "";
	
    /**
	 * @return the $sortMask
	 */
	public function getSortMask() {
		return $this->sortMask;
	}

	/**
	 * @param field_type $sortMask
	 */
	public function setSortMask($sortMask) {
		$this->sortMask = $sortMask;
	}

	public function __construct(){
    	$this->sortKeys = array();
    	$this->sortColumns = array();
    }
    
    /**
     * Chiavi di riga (campi WHERE dell'UPDATE)
     * @param unknown $sk
     */
    public function addSortKey($sk){
    	$this->sortKeys[] = $sk;
    }
    
    public function getSortKeys(){
    	return $this->sortKeys;
    }
    
    /**
     * Valori delle colonne da visualizzare nell'elenco da riordinare
     * @param unknown $sc
     * @return unknown
     */
    public function addSortColumn($sc){
    	$this->sortColumns[] = $sc;
    }
    
    public function setSortColumns($sc){
    	return $this->sortColumns = $sc;
    }
    
    public function getSortColumns(){
    	return $this->sortColumns;
    }
    
    /**
     * Campo sequenza da modificare (campo SET dell'UPDATE)
     * @param unknown $sorter
     */
    public function setSorter($sorter){
    	$this->sorter = $sorter;
    }
    
    public function getSorter(){
    	return $this->sorter;
    }
    
    /**
     * Tabella AS400 in esame (elemento FROM dell'UPDATE)
     * @param unknown $st
     */
    public function setSortTable($st){
    	$this->sortTable = $st;
    }
    
    public function getSortTable(){
    	return $this->sortTable;
    }
    
    /**
     * Setta un where alla lista di ordinamento
     * 
     * @param string $val
     */
    public function setSortWhere($val) {
    	$this->where = $val;
    }
    
    
    /**
     * Ritorna il where settato alla lista di ordinamento
     * 
     * @param void
     * @return string
     */
    public function getSortWhere() {
		return $this->where;
    }
    
}