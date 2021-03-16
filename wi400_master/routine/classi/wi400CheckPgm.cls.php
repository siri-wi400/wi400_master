<?php

class checkPgm {
	
    var $msg;
    var $flg; 
    
	public function __construct(){
		$this->msg = "Abilita";
		$this->flg = '0'; 
	}
	
	public function chk_wrksplf(){
		$this->flg = '2';
		$this->msg = "Funzione disabilitata per mancanza di risorse";
	}
    
}

?>