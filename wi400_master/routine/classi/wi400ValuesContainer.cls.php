<?php

/**
 * @name wi400ValuesContainer 
 * @desc 
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author 
 * @version 1.00
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

class wi400ValuesContainer {
	
	private $values;
    
	/**
	 * Funzione di costruzione della classe wi400ValuesContainer
	 */
    public function __construct(){
    	$this->values = array();
    }

    public function __set($key, $value) {
        $this->values[$key] = $value;
    }

    public function __get($key) {
        if (array_key_exists($key, $this->values)) {
            return  $this->values[$key];
        }
        return "";
    }
    public function getAll() {
    	return $this->values;
    }
    
}

?>