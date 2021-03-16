<?php
class architettura_acg extends architettura_default {
		
	private $architettura = 'ACG';
	public $user_file = 'KFPRF00F';
	public $user_name = "KNMSI";
	public $user_desc = "KTEXT";
	public $currentSysInf = "";
	public $currentSysInfDes = "";
	
	function retrive_sysinf($user) {
	global $db, $settings;
	$myarray = array ();
	
	if ($user != "") {
		
		// Recupero i parametri utente
		$row = $this->retrive_user_info($user);		
		/*$sql = "select KNMSI from ".$settings['lib_architect'].$settings['db_separator']."KFPRF00F where KKNMU='$user'";
		$result = $db->singleQuery ($sql);
		$row = $db->fetch_array ( $result );*/
		// Recupero le librerie del sistema informativo
		if ($row) {
			$sql = "select KLBSI from ".$settings['lib_architect'].$settings['db_separator']."KFSIF00F where KNMSI='" . $row ['KNMSI'] .  "' AND ATKI1 <>'A'";
			$result1 = $db->query ( $sql, False );
			$row1 = $db->fetch_array ( $result1);
			if ($row1) {
				// Carico in un array la schiera delle librerie del sistema informativo
				$librerie = utf8_decode($row1['KLBSI']);
				$start = 0;
				for($i = 0; $i < 100; $i ++) {
					$lib = trim ( substr ( $librerie, $start, 10 ) );
					if (trim ( $lib ) != "") {
						$myarray [] = utf8_encode($lib);
						$start = $start + 12;
					} else
						break;
				}
			
		}
	}
	return $myarray;
	}
	}
	/* Recupero sistema informativo con archietettura ACG */
	function retrive_sysinf_by_name($name) {
		global $db, $settings;
		$myarray = array ();
		if ($name != "") {
			
			$sql = "select KLBSI from ".$settings['lib_architect'].$settings['db_separator']."KFSIF00F where KNMSI='$name' AND ATKI1 <>'A'";
			$result = $db->singleQuery ( $sql, False );
			$row = $db->fetch_array ( $result );
			if ($row) {
				// Carico in un array la schiera delle librerie del sistema informativo
				$librerie = utf8_decode($row['KLBSI']);
				$start = 0;
				for($i = 0; $i < 100; $i ++) {
					$lib = trim ( substr ( $librerie, $start, 10 ) );
					if (trim ( $lib ) != "") {
						$myarray [] = utf8_encode($lib);
						$start = $start + 12;
					} else
						break;
				}
				// taccon da sistemare
				//$myarray[]=$settings['db_lib_list'];
				$db->freestmt ( $result );
			
			}
		}
		return $myarray;
	}
	// Recupero il sistema informativo legato all'utente
	function retrive_sysinf_name($user, $isName) {
		global $db, $settings;
		
		$name = $user;
		if (! $isName) {
			$row = $this->retrive_user_info($user);
			/*$sql = "select KNMSI from ".$settings['lib_architect'].$settings['db_separator']."KFPRF00F where KKNMU='$user'";
			$result = $db->singleQuery ($sql);
			$row = $db->fetch_array ( $result );
			$name = $row ['KNMSI'];
			$db->freestmt ( $result );*/	
			$name = $row ['KNMSI'];		
		}
		$sql = "select KDSSI from ".$settings['lib_architect'].$settings['db_separator']."KFSIF00F where KNMSI='" . $name . "'";
		$result1 = $db->singleQuery ( $sql );
		$row1 = $db->fetch_array ( $result1 );
		$db->freestmt ( $result1 );	
		$this->currentSysInfDes = $row1 ['KDSSI'];
		return trim ( $name . ' ' . $row1 ['KDSSI'] );
	}
    function retrive_user_info($user) {
 		global $db, $settings;
 		
		$sql = "select * from ".$settings['lib_architect'].$settings['db_separator']."KFPRF00F where KKNMU='$user'";
		$result = $db->singleQuery ($sql);
		$row = $db->fetch_array ( $result );
		$db->freestmt ( $result );
		$this->currentSysInf = $row ['KNMSI'];

		return $row;
    }
    function translate_user_info($row) {
    	$dati = array();
    	$dati['UTENTE']=$row['KNMSI'];
    	$dati['DESCRIZIONE']=$row['KTEXT'];
    	
    	return $dati;
    }
    function getUserMail_arch($userName) {
    	
    }

}
?>
