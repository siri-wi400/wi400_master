<?php
function retriveAuthMethod($user) {
	global $db, $settings, $users_table;
	$auth_method = "";
	$sql = "SELECT * FROM $users_table WHERE USER_NAME=?";
	$stmt = $db->singlePrepare($sql,0,true);
	$result = $db->execute($stmt,array($user));
	$row = $db->fetch_array($stmt);
	$auth_method = $row['AUTH_METOD'];
	if ($auth_method =="" || $auth_method == "*DEFAULT") {
		$auth_method = $settings['auth_method'];
	}
	return $auth_method;
}
function writeNewUser($user, $dati=array(), $sec = Array(), $ext = Array(), $block=False) {
	global $db, $users_table, $settings, $moduli_path, $routine_path;
	
	// Controllo se esiste, se non esiste lo creo
	$row = rtvUserInfo($user);
	if (!isset($row['MENU'])) {
		$field = array("USER_NAME", "FIRST_NAME", "LAST_NAME", "EMAIL", "OFFICE", "THEME","PHONE", "TITLE", "ADDRESS", "CITY",
				"STATE_PROVINCE", "COUNTRY", "MENU", "USER_MENU", "LANGUAGE", "OTHER", "LASTACTIVE", "AUTH_METOD","USER_GROUP","WI400_GROUPS",
				"DEFAULT_ACTION", "MYPASSWORD", "ADMIN", "PACKAGE");
		
		$key = array();
		$stmt = $db->prepare("INSERT", $users_table, $key, $field);
		
		$myPassword = md5(uniqid());
		// Default thema e p13n da settings se non passati
		// Nome
		$nome ="";
		if (isset($dati['NOME'])) {
			$nome = $dati['NOME'];
		}
		$cognome ="";
		if (isset($dati['COGNOME'])) {
			$cognome = $dati['COGNOME'];
		}
		$email ="";
		if (isset($dati['EMAIL'])) {
			$email = $dati['EMAIL'];
		}
		$menu ="";
		if (isset($dati['MENU'])) {
			$menu = $dati['MENU'];
		}
		$user_menu ="";
		if (isset($dati['USER_MENU'])) {
			$user_menu = $dati['USER_MENU'];
		}
		$auth_method ="DB";
		if (isset($dati['USER_MENU'])) {
			$auth_method = $dati['AUTH_METHOD'];
		}
		$lingua = "Italian";
		if(isset($dati['LINGUA'])) {
			$lingua = $dati['LINGUA'];
		}
		$groups ="";
		if (isset($dati['GROUPS'])) {
			$groups = $dati['GROUPS'];
		}
		$wi400_groups = '';
		if(isset($dati['WI400_GROUPS'])) {
			if(is_string($dati['WI400_GROUPS'])) {
				$dati['WI400_GROUPS'] = array($dati['WI400_GROUPS']);
			}
			$wi400_groups = implode(";", $dati['WI400_GROUPS']);
		}
		$default_action = '';
		if(isset($dati['DEFAULT_ACTION'])) {
			$default_action = $dati['DEFAULT_ACTION'];
		}
		$campi = array($user, $nome, $cognome, $email, "", "default",
				"", "", "", "", "", "",
				$menu, $user_menu, $lingua, "nessuna nota", 1, $auth_method, $groups,
				$wi400_groups, $default_action, $myPassword, '0', "");
		// Inserimento Record
		$result = $db->execute($stmt, $campi);
	}
	// Scrittura Parametri Aggiuntivi 
	if (count($ext)> 0) {
		require_once $moduli_path."/user/estensione_utenti_vega_common.php";
		$row = getUserExtraInfoVega($user);
		// update oppure Insert nuova riga
		if ($row) {
			$keyUpdt = array("USER_NAME" => $user);
			$fieldsUpdt = getDS($tipo_tab_ext, $ext);
			$fieldsUpdt['USER_NAME']=$user;
			$stmt_updt  = $db->prepare("UPDATE", $tipo_tab_ext, $keyUpdt, array_keys($fieldsUpdt));
			$result = $db->execute($stmt_updt, $fieldsUpdt);
		} else {
			$fieldsIns = getDS($tipo_tab_ext, $ext);
			$fieldsIns['USER_NAME']=$user;
			
			$stmt_ins  = $db->prepare("INSERT", $tipo_tab_ext, null, array_keys($fieldsIns));
			$result = $db->execute($stmt_ins, $fieldsIns);
			
		}
	}
	// Scrittura record Security
	if (count($sec)> 0) {
		require_once $routine_path."/classi/wi400AdvancedUserSecurity.cls.php";
		$advanced_security = new wi400AdvancedUserSecurity($user);
		if (isset($sec['DURATAP'])) {
			$secData['DURATAP'] = $sec['DURATAP'];
		}
		if (isset($sec['SCADE_NEXT'])) {
			$secData['SCADE_NEXT'] = $sec['SCADE_NEXT'];
		}
		if (isset($sec['COMPLEX'])) {
			$secData['COMPLEX'] = $sec['COMPLEX'];
		}
		if (isset($sec['MAXTENTA'])) {
			$secData['MAXTENTA'] = $sec['MAXTENTA'];
		}
		if (isset($sec['ABIRIP'])) {
			$secData['ABIRIP'] = $sec['ABIRIP'];
		}
		if (isset($sec['ABILOG'])) {
			$secData['ABILOG'] = $sec['ABILOG'];
		}
		if (isset($sec['ABICHP'])) {
			$secData['ABICHP'] = $sec['ABICHP'];
		}
		$advanced_security->setUserData($secData);
		// Blocco l'utente una volta creato
		if ($block) {
			$advanced_security->setUserBlocked("AMM");
		}
	}
}
function printMessageError() {
	global $messageContext;
	
	$messaggi  = $messageContext->getMessages();
	$messageContext->removeMessages();
	foreach($messaggi as $mess) {
		//if($mess[0] == 'ERROR') {
			$messageContext->addMessage($mess[0], $mess[1]);
		//}
	}
	//$messageContext->addMessage('ERROR', 'ALBERTO2');
	//die("alberto");
}