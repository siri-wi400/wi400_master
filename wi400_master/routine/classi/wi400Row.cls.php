<?php
class wi400Row {

	private $cols;
    private $keys;
    private $rowNum;
    
    public function __construct($array){
    	$this->cols = $array;
    }
    
     public function getRowNum(){
    	return $this->rowNum;
    }
    
    public function setRowNum($rowNum){
    	$this->rowNum = $rowNum; 
    }
    
    public function getKey($id){
    	return $this->keys[$id];
    }
    
    public function getKeys(){
    	return $this->keys;
    }
    
    public function addKey($key){
    	$this->keys[] = $key; 
    }
    
    public function setKeys($key){
    	$this->keys = $key; 
    }
    
    public function getCol($id){
    	return $this->cols[$id];
    }
    
    public function getCols(){
    	return $this->cols;
    }
}
?>