<?php
class checkUserLDAP  {
	
function checkUser($name, $pwd) {
	global $settings, $messageContext, $db;
	
	$found = False;
	if (isset($settings['ldap_gc']) && $settings['ldap_gc']==True) {
		$ds = ldap_connect ( $settings['ldap_host'].":".$settings['ldap_port'] );
	} else {
	    $ds = ldap_connect ( $settings['ldap_host'] , $settings['ldap_port'] );
	}
	
	if (! $ds) {
		$messageContext->addMessage ( "ERROR", _t("W400003"), "", false );
		return false;
	} else {
		ldap_set_option ( $ds, LDAP_OPT_REFERRALS, 0 );
		ldap_set_option ( $ds, LDAP_OPT_PROTOCOL_VERSION, 3 );
		$r = ldap_bind ( $ds, $settings['ldap_binddn'], $settings['ldap_bindpwd'] );
		if (! $r) {
			$messageContext->addMessage ( "ERROR", _t("W400004") . ldap_error ( $ds ), "", false );
			return false;
		}
		// $person is all or part of a person's name, eg "Jo"
		$person = $name;
		$dn = $settings['ldap_rootdn'];
		//$filter="(|(sn=$person)(givenname=$person))";
		$filter = "(|(sAMAccountName=$person))";
		$sr = ldap_search ( $ds, $dn, $filter );
		//$info = ldap_get_entries($ds, $sr);
		// Loop lettura 
		$info = ldap_get_entries ( $ds, $sr );
		// Dovrebbe essere uno solo l'utente selezionato altrimenti non saprei proprio cosa fare
		for($i = 0; $i < $info ["count"]; $i ++) {
			$found = True;
			$dnp = $info [$i] ["dn"];
			// Salvo tutti i gruppi di appartenenza
			$gruppi = array ();
			for($j = 0; $j < $info [$i] ["memberof"] ['count']; $j ++) {
				$memberof = ldap_explode_dn ( $info [$i] ["memberof"] [$j], 0 );
				$membro = strtoupper ( substr ( $memberof [0], 3 ) );
				if (substr ( $membro, 0, 4 ) == 'TOW_') {
					$gruppi [] = $membro;
				}
			}
			$_SESSION ['WI400_GROUPS'] = $gruppi;
			if (isset($settings['ldap_extra_attribute']) && $settings['ldap_extra_attribute']==False) {
				$_SESSION ['WI400_GROUPS']=array();
			}
				
			if (isset($_SESSION ['WI400_GROUPS_BACKUP'])) {
				$_SESSION['WI400_GROUPS'] = array_unique(array_merge($_SESSION ['WI400_GROUPS_BACKUP'],$_SESSION['WI400_GROUPS']), SORT_REGULAR);
			}
			if (isset ( $info [$i] ["extensionattribute1"] [0] )) {
				$_SESSION ['LDAP_ATTRIBUTE'] = $info [$i] ["extensionattribute1"] [0];
				$_SESSION ['LDAP_GROUP_ATTRIBUTE'] = $info [$i] ["extensionattribute1"] [0];
			} else {
				$_SESSION ['LDAP_ATTRIBUTE'] = "";
				$_SESSION ['LDAP_GROUP_ATTRIBUTE'] = "";
			}
			if (isset($settings['ldap_extra_attribute']) && $settings['ldap_extra_attribute']==False) {
				$_SESSION ['LDAP_ATTRIBUTE'] = "";
				$_SESSION ['LDAP_GROUP_ATTRIBUTE'] = "";
			}
			//$memberof = ldap_explode_dn($info[$i]["memberof"][0], 0);
			$password = $pwd;
			$utente = strtolower ( $info [$i] ["dn"] );
			// Verifica password
			if ($password !="" && ldap_bind ( $ds, $dnp, $password )) {
				// OK;
			} else {
				$messageContext->addMessage ( "ERROR", _t("W400005"), "", True );
				return false;
			}
		}
		ldap_close ( $ds );
	}
	// Scrittura LOG connessioni
	$esito = "";
	if ($found) {
		$esito ="OK";
	} else {
		$esito="KO";
	}	
	/*$values= array();
	$values['ZSUTE']= $name; //UTENTE
	$values['ZSESI']= $esito; // ESITO LOG
	$values['ZSSWS']= 'LDAP'; //USER
	$values['ZSIP']= $_SERVER['REMOTE_ADDR']; //INDIRIZZO IP
	$values['ZDEV']= ''; //DEVICE ?
	$values['ZTIME']= getDb2Timestamp();  //TIMESTAMP
	$stmtDoc = $db->prepare("INSERT", "ZSLOG", null, array_keys($values));
	$result = $db->execute($stmtDoc, $values);*/
	// Scrittura Log Accesso
	writeLogAccess($name);
	/*$values= array();
	$values['ZSUTE']= $name; //UTENTE
	$values['ZSESI']= 'OK'; // ESITO LOG
	$values['ZSSWS']= 'LDAP'; //USER
	$values['ZSIP']= $_SERVER['REMOTE_ADDR']; //INDIRIZZO IP
	$values['ZDEV']= ''; //DEVICE ?
	$values['ZTIME']= getDb2Timestamp();  //TIMESTAMP
	// Reperisco gli attributi del lavoro
	if (isset($settings['XMLSERVICE'])) {
		$rtv = executeCommand("rtvjoba", array(), array("JOB"=>"JOB","USER"=>'USER',"NBR"=>"NBR"));
		$values['ZUSR']=$JOB;
		$values['ZJOB']=$USER;
		$values['ZNBR']=$NBR;
		$values['ZFRE']='';
	}
	
	$stmtDoc = $db->prepare("INSERT", "ZSLOG", null, array_keys($values));
	$result = $db->execute($stmtDoc, $values);*/
	
	//
	if ($found) {
		return true;
	} else {
		return false;
	}
}
	
}
?>
