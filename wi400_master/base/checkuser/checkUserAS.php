<?php

class checkUserAS  {
	private $isBatch =False;
	// Controllo utente/password su AS400
	function checkUser($name, $pwd) {
		global $db, $routine_path, $messageContext, $settings, $connzend;
		
		if (isset($settings['xmlservice'])) {
			require_once $routine_path.'/classi/wi400RoutineXML.cls.php';
			require_once $routine_path."/generali/xmlsupport.php";		
		} else {
		    require_once $routine_path.'/classi/wi400Routine.cls.php';
		}
		// Password in uppercase
		if (isset($settings['security_password_keysensitive']) && $settings['security_password_keysensitive']==True) {
			$pwd2 = $pwd;
		} else {
			$pwd2 = strtoupper($pwd);
		}
		//
	    $pgm = new wi400Routine("RVRYUSRP", $connzend);
	    $pgm->load_description();
    	$pgm->prepare();
    	$pgm->set('UTENTE',strtoupper($name));
    	$pgm->set('PASSWORD', $pwd2);  
   	 	$pgm->set('FLAG', '0'); 
   	 	$pgm->set('IP', $_SERVER['REMOTE_ADDR']); 
	    $ret = $pgm->call();
   
		if (! $ret) {
			if (!$this->isBatch && isset($messageContext)) $messageContext->addMessage ( "ERROR", _t("W400001"));
			return false;
		}
        $FLAG = $pgm->get('FLAG');
        $DATSCA = $pgm->get("DATSCA");		
		// Utente non riconosciuto dal sistema
		if ($FLAG == '1') {
			if (!$this->isBatch && isset($messageContext)) $messageContext->addMessage("ERROR", _t("NOME_UTENTE_PASSWORD_ERRATA"));
			return false;
		}	
		// Scaduti codici di lincenza WI400
		if ($FLAG == '2') {
			if (!$this->isBatch && isset($messageContext)) $messageContext->addMessage ("ERROR", _t("W400002"));
			return false;
		}
		//Password scaduta
		if ($FLAG == '3') {
//			$messageContext->addMessage ("ERROR", _t("W400011"));
//			return false;
			return $FLAG;
		}
		// Utente *DISABLED
		if ($FLAG == '4') {
			if (!$this->isBatch && isset($messageContext)) $messageContext->addMessage("ERROR", _t("UTENTE_DISABILITATO"));
			return false;
		}			
		// Warning per parola d'ordine in scadena
		if ($DATSCA != "") {
			$data [0] = substr ( $DATSCA, 0, 2 );
			$data [1] = substr ( $DATSCA, 2, 2 );
			$data [2] = substr ( $DATSCA, 4, 4 );
			$dataScadenza = mktime ( 0, 0, 0, $data [1], $data [0] - 15, $data [2] );
			$dataOdierna = time ();
			
			if ($dataScadenza <= $dataOdierna) {
				$dataScadenza = mktime ( 0, 0, 0, $data [1], $data [0], $data [2] );
				$dateDiff = $dataScadenza - $dataOdierna;
				$fullDays = floor ( $dateDiff / (60 * 60 * 24) );
				if (!$this->isBatch && isset($messageContext)) $messageContext->addMessage ( "ERROR", _t("W400008", array($data[0],$data[1],$data[2],$fullDays)), "" );
			}
		}

		return true;
	}
	public function isBatch($isBatch) {
		$this->isBatch=$isBatch;
	}
}

?>