<?php
$spacer = new wi400Spacer();

if(in_array($actionContext->getForm(),array("DETAIL","COPIA"))) {
	// Creo il nuovo dettaglio, non passando il secondo parametro viene impostato a false per specificare
	// se gia' presente in sessione non viene ripulito.
	$azioniDetail = new wi400Detail('USER_DETAIL', False);

	$azioniDetail->addTab("user_1",_t('MAIN_DATA'));
	if(isset($settings['enable_tab_abilitazioni'])) {
		$azioniDetail->addTab("user_2",_t('POLICY'));
	}
	// SCHEDA: DATI GENERALI
	
	$scheda = "user_1";
		
	$azioniDetail->isEditable(true);
	
	if (isset($row)){
		unset($row["MYPASSWORD"]);
		$azioniDetail->setSource($row);
	}
	
	$azioniDetail->addParameter("SAVE_ACTION", $saveAction);
	
	$myField = new wi400InputText('codusr');
	if($actionContext->getForm()=="DETAIL")
		$myField->setValue($codUsr);
	else if($actionContext->getForm()=="COPIA")
		$myField->setValue($user_new);
	$myField->setLabel(_t('USER_CODE'));
	$myField->setReadonly(true);
	$azioniDetail->addField($myField, $scheda);
	
	$myField = new wi400InputText('FIRST_NAME');
	$myField->setFromArray($result);
	$myField->addValidation('required');
	$myField->setInfo(_t('USER_NAME_INFO'));
	$myField->setLabel(_t('NAME'));
	$azioniDetail->addField($myField, $scheda);
	
	$myField = new wi400InputText('LAST_NAME');
	$myField->addValidation('required');
	$myField->setLabel(_t('LAST_NAME'));
	$myField->setFromArray($result);
	$azioniDetail->addField($myField, $scheda);
	
	$myField = new wi400InputText('EMAIL');
	$myField->setFromArray($result);
	if(!isset($settings['mail_required']) || $settings['mail_required']===true)
		$myField->addValidation('required');
	$myField->setLabel(_t('EMAIL'));
	$myField->addValidation("email");
	$azioniDetail->addField($myField, $scheda);

	$myField = new wi400InputText('MENU');
	$myField->addValidation('required');
	$myField->setFromArray($result);
	$myField->setLabel(_t('USER_PERSONAL_MENU'));
	$myLookUp =new wi400LookUp("LU_GENERICO");
	$myLookUp->addParameter("FILE","FMNUSIRI");
	$myLookUp->addParameter("CAMPO","MENU");
	$myLookUp->addParameter("DESCRIZIONE","DESCRIZIONE");
	$myField->setLookUp($myLookUp);	
	$azioniDetail->addField($myField, $scheda);
	
    $myField = new wi400InputText('USER_MENU');
	$myField->setFromArray($result);
	$myField->setLabel(_t('USER_UTILITY_MENU'));
	$myLookUp =new wi400LookUp("LU_GENERICO");
	$myLookUp->addParameter("FILE","FMNUSIRI");
	$myLookUp->addParameter("CAMPO","MENU");
	$myLookUp->addParameter("DESCRIZIONE","DESCRIZIONE");
	$myField->setLookUp($myLookUp);	
	$azioniDetail->addField($myField, $scheda);
    
	// Azione di Default da Caricare
    $myField = new wi400InputText('DEFAULT_ACTION');
	$myField->setFromArray($result);
	$myField->setLabel(_t('ACTION_DEFAULT'));
	$myLookUp =new wi400LookUp("LU_GENERICO");
	$myLookUp->addParameter("FILE","FAZISIRI");
	$myLookUp->addParameter("CAMPO","AZIONE");
	$myLookUp->addParameter("TIPO","A;N");
	$myLookUp->addParameter("DESCRIZIONE","DESCRIZIONE");
	$myField->setLookUp($myLookUp);		
	$azioniDetail->addField($myField, $scheda);
		
	if (in_array($settings['auth_method'], array("DB", "AS", "LDAP"))) {
		$azioniDetail->addTab("user_4",_t('GROUPS'));
/*		
		// Gruppi di appartenenza dell'utente (accesso da login)
		$mySelect = new wi400InputSelectCheckBox('GROUPS');
		$mySelect->setMultiple(true);
		$mySelect->setLabel(_t('GROUPS_ALL'));
		if (isset($wi400_groups)) {
			foreach ($wi400_groups as $groupName){
				$mySelect->addOption($groupName);
			}
		}
		// Recupero selezionati
		$selectedGroups = array();
		if (isset($row["WI400_GROUPS"]) && $row["WI400_GROUPS"] != ""){
			$selectedGroups = explode(";",$row["WI400_GROUPS"]);
		}
		$mySelect->setValue($selectedGroups);
		$azioniDetail->addField($mySelect, 'user_4');
*/		
		$appDrag = new wi400DragAndDrop($azione."_DRAG");
		$appDrag->setWidth("49%");
		$appDrag->setHeight("100");
		$appDrag->setCheckUpdate(true);
		
		$appList1 = new wi400DragList("TO_GROUPS", _t("SELEZIONATI"));
		$appList1->setRows($to_groups);
		$appList1->setColor("#ffebe8");
		$appDrag->addList($appList1);
		
		$appList2 = new wi400DragList("FROM_GROUPS", _t('NON_SELEZIONATI'));
		$appList2->setRows($from_groups);
		$appList2->setColor("#FFFFCC");
		$appDrag->addList($appList2);
		
		$azioniDetail->addField($appDrag, "user_4");
		
	}
	
	// Setto se azione di sistema
	$myField = new wi400InputCheckbox("ADMIN");
	$myField->setLabel(_t('AMMINISTRATORE'));
	$myField->setValue(False);
	//$myField->setReadonly(True);
	if(isset($row['ADMIN']))
		$myField->setChecked($row['ADMIN']=='1');
	$azioniDetail->addField($myField, $scheda);
	
	// p13n in campo OFFICE
/*	
	$mySelect = new wi400InputSelect('OFFICE');
	$mySelect->setLabel("P13N da utilizzare");
//	$mySelect->setFirstLabel("Seleziona un dato");
	$mySelect->addOption("DEFAULT", "");
	createDirMenu($mySelect, 'p13n');
//	$mySelect->addValidation("required");
	if((!isset($row['OFFICE']) || $row['OFFICE']=="")) {
		if(isset($settings['p13n']) && $settings['p13n']!="")
			$p13n = $settings['p13n'];
		else
			$p13n = "";
		$mySelect->setValue($p13n);
	}
	else
		$mySelect->setFromArray($result);
	$azioniDetail->addField($mySelect, $scheda);
*/
//	echo "ROW: ".$row['OFFICE']."<br>";
//	echo "SET: ".$settings['p13n']."<br>";
	$myField = new wi400InputText('OFFICE');
	$myField->setLabel("P13N da utilizzare");
	if((!isset($row['OFFICE']) || $row['OFFICE']=="")) {
		if(isset($settings['p13n']) && $settings['p13n']!="")
			$p13n = $settings['p13n'];
		else
			$p13n = "";
		$myField->setValue($p13n);
	}
	else
		$myField->setFromArray($result);
	
	$file_path = $root_path."p13n";
	
	$myLookUp = new wi400LookUp("LU_DIR_LIST");
	$myLookUp->addParameter("FILE_PATHS", $file_path);
	$myLookUp->addParameter("FILE_TYPES", "DIR");
	$myLookUp->addParameter("FULL_PATH", false);
	$myField->setLookUp($myLookUp);
	
	$azioniDetail->addField($myField, $scheda);
	
	// themes
/*	
	$mySelect = new wi400InputSelect('THEME');
	$mySelect->setLabel("Theme da utilizzare");
//	$mySelect->setFirstLabel("Seleziona un dato");
//	$mySelect->addOption("DEFAULT", "");
	createDirMenu($mySelect, 'themes');
//	$mySelect->addValidation("required");
	if((!isset($row['THEME']) || $row['THEME']=="")) {
		$mySelect->setValue('default');
	}
	else 
		$mySelect->setFromArray($result);
	$azioniDetail->addField($mySelect, $scheda);
*/
	$myField = new wi400InputText('THEME');
	$myField->setLabel("Theme da utilizzare");
	if((!isset($row['THEME']) || $row['THEME']=="")) {
		$myField->setValue('default');
	}
	else
		$myField->setFromArray($result);
	
	$file_path = $root_path."themes";
	
	$myLookUp = new wi400LookUp("LU_DIR_LIST");
	$myLookUp->addParameter("FILE_PATHS", $file_path);
	$myLookUp->addParameter("FILE_TYPES", "DIR");
	$myLookUp->addParameter("FULL_PATH", false);
	$myField->setLookUp($myLookUp);
	
	$azioniDetail->addField($myField, $scheda);
	// Package
	$myField = new wi400InputText('PACKAGE');
	$myField->setLabel("Package da utilizzare");
	if((!isset($row['PACKAGE']) || $row['PACKAGE']=="")) {
		$myField->setValue('');
	}
	else
		$myField->setFromArray($result);
	
	$file_path = $root_path."base/package";
	
	$myLookUp = new wi400LookUp("LU_DIR_LIST");
	$myLookUp->addParameter("FILE_PATHS", $file_path);
	$myLookUp->addParameter("FILE_TYPES", "DIR");
	$myLookUp->addParameter("FULL_PATH", false);
	$myField->setLookUp($myLookUp);
	
	$azioniDetail->addField($myField, $scheda);
		
	$mySelect = new wi400InputSelect('AUTH_METOD');
	$mySelect->setFromArray($result);	
	$mySelect->setLabel(_t('AUTHENTICATION_TYPE'));
	$mySelect->setFirstLabel(_t('TYPE_SELECT'));
	$mySelect->addOption(_t('ARC_DEFAULT'),"*DEFAULT");
	$mySelect->addOption(_t('IBMI_USER'),"AS");
	$mySelect->addOption(_t('LDAP_USER'),"LDAP");
	$mySelect->addOption(_t('DB_USER'),"DB");
	$mySelect->addValidation("required");
	$azioniDetail->addField($mySelect, $scheda);

	$myField = new wi400InputText('USER_GROUP');
	$myField->setFromArray($result);
	$myField->setLabel(_t('USER_GROUP'));
	$myField->setCase('UPPER');
	$myField->setInfo(_t('USER_GROUP_INFO'));	
	$azioniDetail->addField($myField, $scheda);
	
	$mySelect = new wi400InputSelect('LANGUAGE');
	$mySelect->setLabel(_t('USER_LANGUAGE'));
	$mySelect->setFirstLabel(_t('TYPE_SELECT'));
	createLanguageMenu($mySelect);
	$mySelect->addValidation("required");
	$azioniDetail->addField($mySelect, $scheda);
/*			
	$myField = new wi400InputText('OFFICE');
	$myField->setFromArray($result);
	$myField->setLabel(_t('USER_OFFICE'));
	$azioniDetail->addField($myField, $scheda);
*/
	$myField = new wi400InputText('PHONE');
	$myField->setFromArray($result);
	$myField->setLabel(_t('PHONE'));
	$azioniDetail->addField($myField, $scheda);
	
	$myField = new wi400InputText('TITLE');
	$myField->setFromArray($result);
	$myField->setLabel(_t('USER_FUNCTION'));
	$azioniDetail->addField($myField, $scheda);
	
	$myField = new wi400InputText('ADDRESS');
	$myField->setFromArray($result);
	$myField->setLabel(_t('ADDRESS'));
	$azioniDetail->addField($myField, $scheda);
	
	$myField = new wi400InputText('CITY');
	$myField->setFromArray($result);
	$myField->setLabel(_t('CITY'));
	$azioniDetail->addField($myField, $scheda);
			
	$myField = new wi400InputText('STATE_PROVINCE');
	$myField->setFromArray($result);
	$myField->setLabel(_t('STATE'));
	$azioniDetail->addField($myField, $scheda);

	$myField = new wi400InputText('COUNTRY');
	$myField->setFromArray($result);
	$myField->setLabel(_t('COUNTRY'));
	$azioniDetail->addField($myField, $scheda);
	
	$myButton = new wi400InputButton('CHECK');
	$myButton->setLabel("Check");
	$myButton->setAction($azione);
	$myButton->setForm("CHECK");
	$myButton->setValidation(True);
	$azioniDetail->addButton($myButton, $scheda);
	
	$myButton = new wi400InputButton('SAVE_BUTTON');
	if($saveAction=="UPDATE")
		$myButton->setLabel(_t('UPDATE'));
	else if($saveAction=="INSERT")
		$myButton->setLabel(_t('SAVE'));
	$myButton->setAction($azione);
	$myButton->setForm($saveAction);
	$myButton->setValidation(True);
	$azioniDetail->addButton($myButton);
	
	$myButton = new wi400InputButton('CANCEL_BUTTON');
	$myButton->setLabel(_t('CANCEL'));
	$myButton->setAction($azione);
	$myButton->setForm("");
	$myButton->setValidation(False);
	$azioniDetail->addButton($myButton);
	
	if ($saveAction=="UPDATE") {
		$myButton = new wi400InputButton('DELETE_BUTTON');
		$myButton->setLabel(_t('DELETE'));
		$myButton->setAction($azione);
		$myButton->setForm("DELETE");
		$myButton->setValidation(False);
		$azioniDetail->addButton($myButton);
	}
	
	// Caricamento parametri standard utente
	$userStd = parse_ini_file("conf/WI400UserParmDefault.conf");
	
	// Caricamento parametri personali utente
	$myFile = wi400File::getUserFile("parm", "WI400UserParm.conf");
	$userParm = array();
	if (file_exists($myFile)) {
		$userParm = parse_ini_file($myFile);
	}
	
	// SCHEDA: ABILITAZIONI
	
	//$abilitazioni = array_merge($userStd, $userParm);
	if(isset($settings['enable_tab_abilitazioni'])) {
		$scheda = "user_2";
		
		$handle = fopen("conf/UserParmDefault.xml", "rb");
		$contents = stream_get_contents($handle);
		fclose($handle);
		
	    $dom = new DomDocument('1.0');
	    $dom->loadXML($contents);
	    $array = parseXML($dom);
		foreach ($array as $key) {
		//foreach ($userStd as $key=>$value) {
			if (!isset($key['userOverride']) || $key['userOverride']=='True') {
				if ($key['type']=="bool") {
					$myField = new wi400InputSelect($key['name']);
					$myField->addOption(_t('LABEL_YES'),"S");
					$myField->addOption(_t('LABEL_NO'),"N");
					$myField->setFirstLabel("*DEFAULT");				
				}
				else
					$myField = new wi400InputText($key['name']);
				
				$myField->setInfo($key['info']);
				
				if (isset($userParm[$key['name']])) {
					$myField->setLabel($key['description']. ":".$userStd[$key['name']]);
				   	$myField->setValue($userParm[$key['name']]);
				}
				else {
					$myField->setLabel($key['description']. ":".$userStd[$key['name']]."<font color='#DE0021'> *</font>");
					//$myField->setValue($value);
				}
				
				$azioniDetail->addField($myField, $scheda);
			}		
		}
	}	
	if($settings['auth_method']=="DB" || (isset($settings['enable_db_user']) && $settings['enable_db_user']==True)) {
		$azioniDetail->addTab("user_3", "Password");
		
		$myField = new wi400Text('PER_UTENTI');
		$myField->setValue('PER UTENTI DB');
		$azioniDetail->addField($myField, "user_3");
		// Password corrente
		$myField = new wi400InputText('MYPASSWORD');
		$myField->setLabel("Password");
		$myField->setType('PASSWORD');
		$myField->setSize(10);
		$myField->setMaxLength(10);
//		$myField->addValidation("required");
		$myField->setValue("");
		$azioniDetail->addField($myField, "user_3");
	}
	
	$wi400GO->addObject('FORM_USER', $actionContext->getForm());
	$wi400_trigger->executeExitPoint("USER","CUSTOM_TAB", array());
	// Test GLOBAL OBJEXT
	
	$wi400GO->getObject('USER_DETAIL')->dispose();
//	echo $wi400GO->getObject('JS_BUTTON_AGGIORNA');
	//$azioniDetail->dispose();
} 
else if ($actionContext->getForm() == "DEFAULT"){	
	$searchAction = new wi400Detail('USER_SEARCH');
	$searchAction->setTitle(_t('USER_TITLE'));
	$searchAction->isEditable(true);
	
	$myField = new wi400InputText('codusr');
	$myField->setLabel(_t('USER_CODE'));
//	$myField->addValidation('required');
	$myField->setMaxLength(20);
	$myField->setCase("UPPER");
	$myField->setInfo(_t('USER_CODE_INFO'));
	$myField->setValue($codUsr);
	
	if (isset($settings['only_as_user']) && $settings['only_as_user']==True) {
		$decodeParameters = array(
			'TYPE' => 'i5_object',
			'OBJTYPE' => '*USRPRF'
		);
		$myField->setDecode($decodeParameters);
	} else {
		$decodeParameters = array(
				'TYPE'=> 'common',
				'COLUMN' => 'LAST_NAME',
				'TABLE_NAME' => 'SIR_USERS',
				'KEY_FIELD_NAME' => 'USER_NAME',
				'ALLOW_NEW' => True,
				'AJAX' => true,
				'COMPLETE' => true,
				'COMPLETE_MIN' => 2,
				'COMPLETE_MAX_RESULT' => 15
		);
		$myField->setDecode($decodeParameters);
	}
	$myLookUp =new wi400LookUp("LU_GENERICO");
	$myLookUp->addParameter("FILE",$users_table);
	$myLookUp->addParameter("CAMPO","USER_NAME");
	$myLookUp->addParameter("DESCRIZIONE","EMAIL");
	$myLookUp->addParameter("LU_SELECT", "FIRST_NAME|LAST_NAME");
	$myLookUp->addParameter("LU_AS_TITLES", "Nome|Cognome");
	$myField->setLookUp($myLookUp);
	$myField->setAutoFocus(True);
	//$myField->setReadOnly(True);
	//$myField->setShowLookUpEver(True);
/*
	$decodeParameters = array(
		'TYPE' => 'user',
		'AJAX' => true
	);
	$myField->setDecode($decodeParameters);
	
	$myLookUp = new wi400LookUp("LU_USER");
	$myLookUp->addField("codusr");
	$myField->setLookUp($myLookUp);
*/	
	$searchAction->addField($myField);
	
	$myButton = new wi400InputButton('SEARCH_BUTTON');
	$myButton->setLabel(_t('SELECT'));
	$myButton->setAction($azione);
	$myButton->setForm("DETAIL");
	$myButton->setValidation(True);
	$searchAction->addButton($myButton);
	$searchAction->dispose();
	
	$spacer->dispose();
		
	// Copia utenti
	$searchAction = new wi400Detail('COPY_USER');
	$searchAction->setTitle(_t('COPY')." "._t('USER'));
	$searchAction->isEditable(true);
	$searchAction->setColsNum(2);
	
	// Utente da copiare
	$myField = new wi400InputText('codusr1');
	$myField->setLabel(_t("COPY_FROM"));
//	$myField->addValidation("required");
	$myField->setCase("UPPER");	
	$myField->setMaxLength(20);
	$myField->setValue($user_old);
	$myField->setInfo(_t("USER_CODE_INFO"));
	
	if (isset($settings['only_as_user']) && $settings['only_as_user']==True) {
		$decodeParameters = array(
			'TYPE' => 'i5_object',
			'OBJTYPE' => '*USRPRF'
		);
		$myField->setDecode($decodeParameters);
	}
		
	$myLookUp =new wi400LookUp("LU_GENERICO");
	$myLookUp->addParameter("FILE",$users_table);
	$myLookUp->addParameter("CAMPO","USER_NAME");
	$myLookUp->addParameter("DESCRIZIONE","EMAIL");
	$myLookUp->addParameter("LU_SELECT", "FIRST_NAME|LAST_NAME");
	$myLookUp->addParameter("LU_AS_TITLES", "Nome|Cognome");
	$myField->setLookUp($myLookUp);
/*
	if (isset($settings['only_as_user']) && $settings['only_as_user']==True) {
		$decodeParameters = array(
			'TYPE' => 'user',
			'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
	}
	
	$myLookUp = new wi400LookUp("LU_USER");
	$myField->setLookUp($myLookUp);
*/	
	$searchAction->addField($myField);
	
	// Nuovo utente in cui copiare
	$myField = new wi400InputText('codusr2');
	$myField->setLabel(_t("NEW_USER"));
//	$myField->addValidation("required");
	$myField->setCase("UPPER");	
	$myField->setMaxLength(20);
	$myField->setValue($user_new);
	$myField->setInfo(_t("USER_CODE_INFO"));
/*	
	if (isset($settings['only_as_user']) && $settings['only_as_user']==True) {
		$decodeParameters = array(
			'TYPE' => 'i5_object',
			'OBJTYPE' => '*USRPRF'
		);
		$myField->setDecode($decodeParameters);
	}
		
	$myLookUp =new wi400LookUp("LU_GENERICO");
	$myLookUp->addParameter("FILE",$users_table);
	$myLookUp->addParameter("CAMPO","USER_NAME");
	$myLookUp->addParameter("DESCRIZIONE","EMAIL");
	$myField->setLookUp($myLookUp);
*/		
	$searchAction->addField($myField);
	
	$myButton = new wi400InputButton('COPY_BUTTON');
	$myButton->setLabel(_t("COPY"));
	$myButton->setAction($azione);
	$myButton->setForm("COPIA");
	$myButton->setValidation(true);
	$searchAction->addButton($myButton);
	
	$searchAction->dispose();
	
	} // END DEFAULT
	
/**
 * function createLanguageMenu():
 * 		Takes no arguments.  Creates the drop down menu for the language list.
 */
function createLanguageMenu($mySelect) {
	// scan the lang directory and create the menu based on the language files present
	global $base_path;
	
	$path = "$base_path/lang";
	
	$dir = opendir ( "$path" );
	while ( $thafile = readdir ( $dir ) ) {
	if (is_file ( "$path/$thafile" ) && preg_match ( "/.lang\.php$/", "$path/$thafile" )) {
			$thafile = str_replace ( ".lang.php", "", $thafile );
			$mySelect->addOption ( "$thafile", "$thafile" );
		}
	}
}
/*
// Scansione della directory p13n e creazione delle opzioni basate sui file personalizzati presenti
function createDirMenu($mySelect, $directory) {
	global $root_path, $settings;

	$file_path = $root_path.$directory;
//	echo "PATH: $file_path<br>";
	
	$dir_handle = opendir($file_path);			// alternativa, ordina automaticamente i files: scandir($file_path, 1);
		
	// Recupero dei file della directory
	while(($file_name = readdir($dir_handle))!==false) {
		if($file_name!="." && $file_name!=".." && $file_name!="CVS") {
//		echo "FILE:$file_name<br>";
				
			$file = $file_path."/".$file_name;
//			echo "FILE: $file<br>";
			
			if(is_dir($file)) {
				$array_dir[] = $file_name;
			}
		}
	}
	
	sort($array_dir);
	
	foreach($array_dir as $dir) {
		$mySelect->addOption($dir, $dir);
	}
		
	closedir($dir_handle);
}
*/
?>