<?php
    class architettura_gaas extends architettura_default {
		
	public $architettura = 'GAAS';
	public $user_file = 'JPROFADF';
	public $user_name = "NMPRAD";
	public $user_desc = "DSPRAD";
	public $currentSysInf = "";
	public $currentSysInfDes = "";
	
	function retrive_sysinf($user) {
		global $db, $settings;
		$myarray = array ();
		
		if ($user != "") {
			
			// Recupero i parametri utente
			$row = $this->retrive_user_info($user);
			/*$sql = "select SINFAD from ".$settings['lib_architect'].$settings['db_separator']."JPROFADF where NMPRAD='$user' AND FLANAD <>'A'";
			$result = $db->singleQuery ($sql);
			$row = $db->fetch_array ( $result );*/
			// Recupero le librerie del sistema informativo
			if ($row) {
				$sql = "select LBSIAF from ".$settings['lib_architect'].$settings['db_separator']."JSINFAFF where SINFAF='" . $row ['SINFAD'] . "' AND FLANAF <>'A'";
				$result1 = $db->query ( $sql, False );
				$row1 = $db->fetch_array ( $result1 );
				if ($row1) {
					$myarray = $this->retrive_sysinf_by_name( $row ['SINFAD'] );
				
				}
			}
		}
		
		return $myarray;
	}
	/* Recupero sistema informativo con archietettura GAAS */
	function retrive_sysinf_by_name($name) {
		global $db, $settings;
		$myarray = array ();
		if ($name != "") {
			
			$sql = "select LBSIAF from ".$settings['lib_architect'].$settings['db_separator']."JSINFAFF where SINFAF='$name' AND FLANAF <>'A'";
			$result = $db->singleQuery ( $sql, False );
			$row = $db->fetch_array ( $result );
			if ($row) {
				// Carico in un array la schiera delle librerie del sistema informativo
				$librerie = $row ['LBSIAF'];
				//$start = 0;
				$parts = preg_split('/\s+/', $librerie);
				//print_r($parts);
				//die();
				foreach ($parts as $key => $value) {
					if (trim($value!="")) {
						$myarray [] = $value;
					}
				}
				/*for($i = 0; $i < 100; $i ++) {
					$lib = trim ( substr ( $librerie, $start, 10 ) );
					if (trim ( $lib ) != "") {
						$myarray [] = $lib;
						$start = $start + 12;
					} else
						break;
				}*/
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
			/*$sql = "select SINFAD from ".$settings['lib_architect'].$settings['db_separator']."JPROFADF where NMPRAD='$user'";
			$result = $db->singleQuery ($sql);
			$row = $db->fetch_array ( $result );
			$name = $row ['SINFAD'];
			$db->freestmt ( $result );*/
			$name = $row ['SINFAD'];			
		}
		$sql = "select * from ".$settings['lib_architect'].$settings['db_separator']."JSINFAFF where SINFAF='" . $name . "'";
		$result1 = $db->singleQuery ( $sql );
		$row1 = $db->fetch_array ( $result1 );
        if (isset($row1['IASPAF']) && $row1['IASPAF']) {
        	$_SESSION['current_asp'] = $row1['IASPAF'];
        }
		$db->freestmt ( $result1 );
		$this->currentSysInfDes = $row1 ['DSSIAF'];
		return trim ( $name . ' ' . $row1 ['DSSIAF'] );
	}
    function retrive_user_info($user) {
 		global $db, $settings;
 		
		$sql = "select * from ".$settings['lib_architect'].$settings['db_separator']."JPROFADF where NMPRAD='$user' AND FLANAD<>'A'";
		$result = $db->singleQuery ($sql);
		$row = $db->fetch_array ( $result );
		$name = $row ['SINFAD'];
		$this->currentSysInf = $row['SINFAD'];
		$db->freestmt ( $result ); 	

		return $row;
    }
    function translate_user_info($row) {
    	$dati = array();
    	$dati['UTENTE']=$row['NMPRAD'];
    	$dati['DESCRIZIONE']=$row['DSPRAD'];
    	
    	return $dati;
    }
    function getASP() {
    	return "";
    } 
    /**
     * @desc Ritorna un campo input Field per la richiesta dell'azione a video
     */
    static function getFieldAzione() {
    	// Ritorna il campo da utilizzare per richiedere l'azione
    	//return "JPROF01L";
    }
    function getUserMail_arch($userName) {
	    global $db,$settings,$users_table;
	    
//	    $email = getUserMail($userName);
	    
//	    if($email=="") {
	    $sql_arch = "SELECT MAILAD FROM ".$settings['lib_architect'].$settings['db_separator']."JPROFADF
				WHERE NMPRAD='$userName' AND FLANAD<>'A'";
	//		echo "SQL: $sql_arch<br>";
			$result_arch = $db->query($sql_arch);
			$row_arch = $db->fetch_array($result_arch);
			
			if (isset($row_arch['MAILAD']) && $row_arch['MAILAD']!="") {
				$email = trim($row_arch['MAILAD']);
			}
			else {
				$email = "";
			}
//		}
		
		return $email;
	}
	
    }
?>
