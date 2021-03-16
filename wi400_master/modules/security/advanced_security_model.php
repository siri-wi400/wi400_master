<?php
if (isset($settings['advanced_security']) && $settings['advanced_security']==True) {
	require_once "advanced_security_functions.php";
	
	$azione = $actionContext->getAction();
	echo "AZIONE: $azione - FORM: ".$actionContext->getForm()."<br>";
	
	if(in_array($actionContext->getForm(), array("SAVE", "USER_PANEL", "SECURITY_BLOCCA", "SECURITY_SBLOCCA")))	{
		// Utente
		if($wi400GO->getObject('FORM_USER') != 'COPIA') {
			$codUsr = wi400Detail::getDetailValue("USER_SEARCH", "codusr");
		}
		else {
			$codUsr = wi400Detail::getDetailValue("COPY_USER", "codusr1");
		}
		echo "USER: $codUsr<br>";
	
//		echo "REQUEST:<pre>"; print_r($_REQUEST); echo "</pre>";
//		echo "request:<pre>"; print_r($request); echo "</pre>";
	
		echo "TRIGGER_PARAM:<pre>"; print_r($trigger_param); echo "</pre>";
	
		if(isset($trigger_param['form']) && !empty($trigger_param['form'])) {
			$actionContext->setForm($trigger_param['form']);
		}
		
		if(($actionContext->getForm()=="SAVE"))
			$actionContext->setForm("SECURITY_SAVE");
		
		echo "FORM: ".$actionContext->getForm()."<br>";
	}
	
	if ($actionContext->getForm() == "BEFORE_CHECK") {
		require_once $routine_path."/classi/wi400AdvancedUserSecurity.cls.php";
		
		$advanced_security = new wi400AdvancedUserSecurity($trigger_param['user']);
		$bloccato = $advanced_security->checkUserBlocked($trigger_param['user']);

		if (gettype($bloccato) == 'string' && $bloccato == "AMM") {
			goHeader($appBase."index.php?t=LOGIN");
			$error_mess = _t("W400021").getErrorConLink($trigger_param['user']);
			$messageContext->addMessage ("ERROR", $error_mess);
			die();
		}
		if ($bloccato == True) {
			goHeader($appBase."index.php?t=LOGIN");
			$error_mess = _t("W400020").getErrorConLink($trigger_param['user']);
			$messageContext->addMessage ("ERROR", $error_mess);
			die();
		}
	}
	
	if ($actionContext->getForm() == "BEFORE_CHECK_2") {
		// Prova per richiamare un secondo trigger in cascata
	}
	
	if ($actionContext->getForm() == "AFTER_CHECK") {
		$check = $trigger_param['check'];
		$user  = $trigger_param['user'];
		
		require_once $routine_path."/classi/wi400AdvancedUserSecurity.cls.php";
		
		$advanced_security = new wi400AdvancedUserSecurity($user);
		
		if ($check===False) {
			 // Controllo quanti Login non sono andati a buon fine
			 $sp = $advanced_security->getSecurityParam($user);
			 if ($sp['NUMERR']>=$sp['MAXTENTA']) {
				 // Se ho superato crea il file di blocco
				 $advanced_security->setUserBlocked($user, $dati);
				 // Aggiorno il contatore di blocco sul file e lo porto a *ZERO
				 $advanced_security->resetUserError(0);
				 // Redirect al LOGIN con segnalazione di errore
				 goHeader($appBase."index.php?t=LOGIN&DECORATION=login");
				 $error_mess = _t("W400020").getErrorConLink($user);
				 $messageContext->addMessage ("ERROR", $error_mess);
				 die();
			 }
			 $advanced_security->resetUserError(($sp['NUMERR']+1));
		 }
		 // Forzatura cambio password o password scaduta
		 if ($check===True) {
			 $sp = $advanced_security->getSecurityParam($user);
			 if ($sp['CAMBIO_PASSWORD']=="S") {
				 goHeader($appBase."index.php?t=CHGPWD&f=LOGIN&DECORATION=login&user=$user");
				 $messageContext->addMessage ("INFO", "Reimpostazione Passowrd");
				 die();
			 }
		 }	
	}
	
	if ($actionContext->getForm() == "USER_PANEL") {
		$azioniDetail = $wi400GO->getObject('USER_DETAIL');
		$azioniDetail->addTab("user_5", "Security");
		
//		$codUsr = wi400Detail::getDetailValue("USER_SEARCH","codusr");
//		echo "USER: $codUsr<br>";
		
		$sql = "SELECT * FROM $users_table WHERE USER_NAME=?";
		$stmt = $db->singlePrepare($sql,0,true);
		$result = $db->execute($stmt,array($codUsr));
		$row = $db->fetch_array($stmt);
		
		$scheda='user_5';
		
		$myField = new wi400Text('PER_UTENTI');
		$myField->setValue('PER UTENTI DB');
		$azioniDetail->addField($myField, $scheda);
		
		require_once $routine_path."/classi/wi400AdvancedUserSecurity.cls.php";
		$advanced_security = new wi400AdvancedUserSecurity($codUsr);
		$sec = $advanced_security->getUserViewSecurityParam();
		
		// Durata Password
		$myField = new wi400InputText('DURATAP');
		$myField->setLabel("Durata Password");
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$myField->setValue($sec['DURATAP']);
		$azioniDetail->addField($myField, $scheda);
		// Scadenza al prossimo LOGIN
		$myField = new wi400InputSelect("SCADE_NEXT");
		$myField->setLabel("Scadenza password al prossimo login");
		$myField->addOption(_t('LABEL_YES'),"S");
		$myField->addOption(_t('LABEL_NO'),"N");
		$myField->setValue($sec['SCADE_NEXT']);
		$azioniDetail->addField($myField, $scheda);
		// Complessita Password
		$myField = new wi400InputSelect("COMPLEX");
		$myField->setLabel("Complessità password");
		$arr = $advanced_security->getComplexArray();
		foreach ($arr as $key => $value) {
			$myField->addOption($key, $value);
		}
		$myField->setValue($sec['COMPLEX']);
		$azioniDetail->addField($myField, $scheda);
		// Massimo Tentativi
		$myField = new wi400InputText('MAXTENTA');
		$myField->setLabel("Numero massimo di tentativi di login");
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$myField->setValue($sec['MAXTENTA']);
		$azioniDetail->addField($myField, $scheda);
		// Abilitia Ripristino Password
		$myField = new wi400InputSelect("ABIRIP");
		$myField->setLabel("Abilita ripristino utente");
		$myField->addOption(_t('LABEL_YES'),"S");
		$myField->addOption(_t('LABEL_NO'),"N");
		$myField->addOption("Default Sistema","*SYSVAL");
		$myField->setValue($sec['ABIRIP']);
		$azioniDetail->addField($myField, $scheda);
		// Abilitia Log Avanzato
		$myField = new wi400InputSelect("ABILOG");
		$myField->setLabel("Log Avanzato azioni utente");
		$myField->addOption(_t('LABEL_YES'),"S");
		$myField->addOption(_t('LABEL_NO'),"N");
		$myField->addOption("Default Sistema","*SYSVAL");
		$myField->setValue($sec['ABILOG']);
		$azioniDetail->addField($myField, $scheda);
		// Abilitia Cambio password
		$myField = new wi400InputSelect("ABICHP");
		$myField->setLabel("Abilita Cambio Passowrd");
		$myField->addOption(_t('LABEL_YES'),"S");
		$myField->addOption(_t('LABEL_NO'),"N");
		$myField->addOption("Default Sistema","*SYSVAL");
		$myField->setValue($sec['ABICHP']);
		$azioniDetail->addField($myField, $scheda);
		// Utente Bloccato
		$bloccato = 'N';
		$check = $advanced_security->checkUserBlocked();
		if ($check) $bloccato = "S";
		$myField = new wi400Text("USER_BLOCKED");
		$myField->setLabel("Utente Bloccato");
		$myField->setValue($bloccato);
		$azioniDetail->addField($myField, $scheda);
		// Tentativi Errati di Login
		$myField = new wi400Text("NUMERR");
		$myField->setLabel("Numero Errori");
		$myField->setValue($sec['NUMERR']);
		$azioniDetail->addField($myField, $scheda);
		// Data in cui è stata cambiata l'ultima password
		$myField = new wi400Text("LSTCHP");
		$myField->setLabel("Ultimo cambio password");
		$myField->setValue($sec['LSTCHP']);
		$azioniDetail->addField($myField, $scheda);
		// Ultimo Login
		$myField = new wi400Text("LAST_LOGIN");
		$myField->setLabel("Ultimo login");
		$myField->setValue(date("h:i:s d/m/Y", $row['TIME_OFFSET']));
		$azioniDetail->addField($myField, $scheda);
/*
		// Bottone si salvataggio
		$myButton = new wi400InputButton('SEARCH_BUTTON');
		$myButton->setLabel("Salva Security");
		$myButton->setAction($azione);
		$myButton->setForm("SECURITY_SAVE");
		$myButton->setValidation(True);
		$azioniDetail->addButton($myButton, $scheda);
*/		
		// Sblocca utente
		$myButton = new wi400InputButton('SBLOCCA');
		$myButton->setLabel("Sblocca Utente");
		$myButton->setAction($azione);
		$myButton->setForm("SECURITY_SBLOCCA");
		$myButton->setValidation(True);
		$azioniDetail->addButton($myButton, $scheda);
		// Blocca utente
		$myButton = new wi400InputButton('BLOCCA');
		$myButton->setLabel("Blocca Utente");
		$myButton->setAction($azione);
		$myButton->setForm("SECURITY_BLOCCA");
		$myButton->setValidation(True);
		$azioniDetail->addButton($myButton, $scheda);
	}	
	else if ($actionContext->getForm() == "SECURITY_BLOCCA"){
//		$codUsr = wi400Detail::getDetailValue("USER_SEARCH","codusr");

		require_once $routine_path."/classi/wi400AdvancedUserSecurity.cls.php";
		$advanced_security = new wi400AdvancedUserSecurity($codUsr);
		$advanced_security->setUserBlocked();
		$actionContext->onSuccess("EPRO","DETAIL");
	}
	else if ($actionContext->getForm() == "SECURITY_SBLOCCA"){
//		$codUsr = wi400Detail::getDetailValue("USER_SEARCH","codusr");
		
		require_once $routine_path."/classi/wi400AdvancedUserSecurity.cls.php";
		$advanced_security = new wi400AdvancedUserSecurity($codUsr);
		$advanced_security->setUserUnBlocked("AMM");
		$actionContext->onSuccess("EPRO","DETAIL");
	}
	else if ($actionContext->getForm() == "SECURITY_SAVE"){
//		$codUsr = wi400Detail::getDetailValue("USER_SEARCH","codusr");
		
		require_once $routine_path."/classi/wi400AdvancedUserSecurity.cls.php";
		
		$advanced_security = new wi400AdvancedUserSecurity($codUsr);
		$dati = wi400Detail::getDetailValues("USER_DETAIL");
		$secData['DURATAP'] = $dati['DURATAP'];
		$secData['SCADE_NEXT'] = $dati['SCADE_NEXT'];
		$secData['COMPLEX'] = $dati['COMPLEX'];
		$secData['MAXTENTA'] = $dati['MAXTENTA'];
		$secData['ABIRIP'] = $dati['ABIRIP'];
		$secData['ABILOG'] = $dati['ABILOG'];
		$secData['ABICHP'] = $dati['ABICHP'];
		$advanced_security->setUserData($secData);
		$actionContext->onSuccess("EPRO","DETAIL");
	}	
}	