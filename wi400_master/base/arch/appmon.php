<?php
class architettura_appmon extends architettura_default {
		
	private $architettura = 'APPMON';
	public $currentSysInf = "";
	public $currentSysInfDes = "";
	
	function getType() {
		return $this->architettura;
	}

	function retrive_sysinf($user) {
		global $WI400_PRAGMA, $settings;
		$myarray = array();
		// Recupero il nome del sistema informativo associato all'utente
		global $db;
		// Richiamo Routine RPG per reperire il sistema informativo associato all'utente
		$rtvprfdta = new wi400Routine("RTVPRFDTA", $connzend);
		$rtvprfdta->load_description();
		$rtvprfdta->prepare();
		//$rtvprfdta->set('UTENTE', $_SESSION['user']);
		$rtvprfdta->set('UTENTE', $user);
		//$WI400_PRAGMA['XMLSERVICE_HEX_CONVERT']='out';
		$rtvprfdta->call();
		//unset($WI400_PRAGMA['XMLSERVICE_HEX_CONVERT']);
		$dati = $rtvprfdta->get('DATI');
		$sysinfname = substr($dati, 14 , 10);
		if ($sysinfname=="") {
			// Metodo Alternativo con SQL
			$sql = "select KNMSI from ".$settings['lib_architect'].$settings['db_separator']."KFPRF00F where KKNMU='" . $user . "'";
			$result = $db->singleQuery ( $sql, False );
			$row = $db->fetch_array ( $result );
			if ($row) {
				$sysinfname = $row['KNMSI'];
			}
		}
		$this->currentSysInf = $sysinfname;
	    $myarray = retrive_sysinf_by_name($sysinfname);
		return $myarray;

	}
	function retrive_sysinf_by_name($name) {
        global $db, $INTERNALKEY, $CONTROLKEY, $settings;
		/* Per reperire l'elenco delle librerie devo richiamare il comando specifico che le carica sul lavoro e poi le recupero
		   con un rtvjoba per salvarle nella cache per le successive chiamate
		*/
		$myarray = array ();
		executeCommand("CALL {$settings['lib_architect']}{$settings['db_separator']}MCGSETLIBL PARM('".str_pad($name, 10)."')");
		//echo "CALL {$settings['lib_architect']}{$settings['db_separator']}MCGSETLIBL PARM('".str_pad($name, 10)."')";
	    global $userlibl;
		executeCommand("rtvjoba",array(),array("usrlibl" => "userlibl"));
		//die($userlibl);
		$librerie = $userlibl;
		$myarray = preg_split('/ +/', $librerie);
		return $myarray;
		
	}
	function retrive_sysinf_name($user, $isName) {
		global $db, $connzend;
		// Richiamo Routine RPG per reperire il sistema informativo associato all'utente
		$rtvprfdta = new wi400Routine("RTVPRFDTA", $connzend);
		$rtvprfdta->load_description();
		$rtvprfdta->prepare();
		$rtvprfdta->set('UTENTE', $_SESSION['user']);
		$rtvprfdta->call();
		$dati = $rtvprfdta->get('DATI');
		$sysinfname = substr($dati, 14 , 10);
		$this->currentSysInf = $sysinfname;
		return "$user $sysinfname"; 
	}
	function getUserMail_arch($userName) {
		
	}
	
	}
?>
