<?php
class architettura_proj extends architettura_default {
		
	public $architettura = 'PROJ';
	public $user_file = 'KPPRF00F';
	public $user_name = "PRFNSI";
	public $user_desc = "DSPRAD";
	public $currentSysInf = "";
	public $currentSysInfDes = "";
	
	function retrive_sysinf($user) {
		global $db, $settings;
		$myarray = array ();
		if ($user != "") {
			
			// Recupero i parametri utente
			$row = $this->retrive_user_info($user);
			/*$sql = "select PRFNSI from ".$settings['lib_architect'].$settings['db_separator']."KPPRF00F  where PRFNMU='$user'";
			$result = $db->singleQuery ( $sql, False );
			$row = $db->fetch_array ( $result );*/
			// Recupero le librerie del sistema informativo
			if ($row) {
				$myarray = $this->retrive_sysinf_by_name( $row ['PRFNSI'] );
			
			}
		}
		return $myarray;
	}
	/* Recupero sistema informativo con archietettura GAAS */
	function retrive_sysinf_by_name($name) {
		global $db, $settings;
		$myarray = array ();
		if ($name != "") {
			
			$sql = "select * from ".$settings['lib_architect'].$settings['db_separator']."KPLIN00F where LINNSI='" . $name . "'";
			$result = $db->singleQuery ( $sql, False );
			$row = $db->fetch_array ( $result );
			if ($row) {
				// Carico in un array la schiera delle librerie del sistema informativo
				$start = 1;
				for($i = 0; $i < 100; $i ++) {
					$campo = 'LINLIBL' . str_pad ( $start, 2, '0', STR_PAD_LEFT );
					$start = $start + 1;
					$lib = trim ( $row [$campo] );
					if (trim ( $lib ) != "") {
						$myarray [] = $lib;
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
			/*$sql = "select PRFNSI from ".$settings['lib_architect'].$settings['db_separator']."KPPRF00F  where PRFNMU='$user'";
			$result = $db->singleQuery ( $sql );
			$row = $db->fetch_array ( $result );
			$name = $row ['PRFNSI'];
			$db->freestmt ( $result );*/
			$name = $row ['PRFNSI'];			
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
 		
			$sql = "select * from ".$settings['lib_architect'].$settings['db_separator']."KPPRF00F  where PRFNMU='$user'";
			$result = $db->singleQuery ( $sql );
			$row = $db->fetch_array ( $result );
			$name = $row ['PRFNSI'];
			$db->freestmt ( $result );
			$this->currentSysInf = $name;

		return $row;
    }
function translate_user_info($row) {
    	$dati = array();
    	$dati['UTENTE']=$row['PRFNSI'];
    	$dati['DESCRIZIONE']=$row['DSPRAD'];
    	
    	return $dati;
    }
    
	}
?>
