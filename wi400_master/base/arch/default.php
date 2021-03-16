<?php
    class architettura_default {
		
	private $architettura = 'DEFAULT';
	public $user_file = '';
	public $user_name = "";
	public $user_desc = "";

	function getType() {
		return $this->architettura;
	}

	function retrive_sysinf($user) {
	
		$myarray = array();
	
		return $myarray;

	}
	function retrive_sysinf_by_name($name) {

		$myarray = array ();
	
		return $myarray;
		
	}
	function retrive_sysinf_name($user, $isName) {
	
		return "DEFAULT"; 
	}
	function getUserMail_arch($userName) {
		
	}
	
	}
?>
