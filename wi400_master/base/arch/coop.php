<?php
class architettura_coop extends architettura_default {
		
	private $architettura = 'COOP';

	function getType() {
		return $this->architettura;
	}

	function retrive_sysinf($user) {
	
		$myarray = array();
	    $myarray = retrive_sysinf_by_name($name);
		return $myarray;

	}
	function retrive_sysinf_by_name($name) {
        global $db, $INTERNALKEY, $CONTROLKEY, $settings;
		/* Per reperire l'elenco delle librerie devo richiamare il comando specifico che le carica sul lavoro e poi le recupero
		   con un rtvjoba per salvarle nella cache per le successive chiamate
		*/
		$myarray = array ();
		executeCommand("CALL SIDIOBJ/WSSTCL01 PARM('".str_pad($name, 10)."')");
	    global $userlibl;
		executeCommand("rtvjoba",array(),array("usrlibl" => "userlibl"));
		$librerie = $userlibl;
		$myarray = preg_split('/ +/', $librerie);
		return $myarray;
		
	}
	function retrive_sysinf_name($user, $isName) {
	
		return "COOP $user"; 
	}
	function getUserMail_arch($userName) {
		
	}
	
	}
?>
