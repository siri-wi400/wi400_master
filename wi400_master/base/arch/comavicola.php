<?php
class architettura_comavicola extends architettura_default {
		
	private $architettura = 'COMAVICOLA';

	function getType() {
		return $this->architettura;
	}

	function retrive_sysinf($user) {
	
		$myarray = array();
	    $myarray = retrive_sysinf_by_name($name);
		return $myarray;

	}
	function retrive_sysinf_by_name($name) {
        global $db, $INTERNALKEY, $CONTROLKEY, $settings, $routine_path;
		/* Per reperire l'elenco delle librerie devo richiamare il comando specifico che le carica sul lavoro e poi le recupero
		   con un rtvjoba per salvarle nella cache per le successive chiamate
		*/
        require_once $routine_path."/generali/os400command.php";
		$myarray = array ();
		$ret = data_area_read("$name/LYWSJOBD");
		
		$start = 0;
		if ($ret!="") {
			for($i = 0; $i < 100; $i ++) {
				$lib = trim ( substr ( $ret, $start, 10 ) );
				if (trim ( $lib ) != "") {
					$myarray [] = $lib;
					$start = $start + 10;
				} else
					break;
			}
		}
		//executeCommand("CALL SIDIOBJ/WSSTCL01 PARM('".str_pad($name, 10)."')");
	    //global $userlibl;
		//executeCommand("rtvjoba",array(),array("usrlibl" => "userlibl"));
		//$librerie = $userlibl;
		//$myarray = preg_split('/ +/', $ret);
		return $myarray;
		
	}
	function retrive_sysinf_name($user, $isName) {
	
		return "COMAVICOLA $user"; 
	}
	function getUserMail_arch($userName) {
		
	}
	
	}
?>
