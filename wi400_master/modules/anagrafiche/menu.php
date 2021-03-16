<?php 

	$spacer = new wi400Spacer();

	if($actionContext->getForm()=="DEFAULT") {
		$searchAction = new wi400Detail('ricercaMenu', False);
		$searchAction->setTitle(_t('MENU_TITLE'));
		$searchAction->isEditable(true);
		
		$myField = new wi400InputText('codmen');
		$myField->setLabel(_t('MENU_CODE'));
//		$myField->addValidation('required');
		$myField->setMaxLength(30);
		$myField->setCase("UPPER");	
		$myField->setInfo(_t('MENU_CODE_INFO'));
		
		$sql_case_desc = "case when B.DESCRIZIONE<>'' then B.DESCRIZIONE else A.DESCRIZIONE end as DESCRIZIONE";
		
		$decodeParameters = array(
			'TYPE' => 'common',
			'TABLE_NAME' => "(SELECT A.AZIONE, $sql_case_desc FROM FAZISIRI A LEFT JOIN FMNUSIRI B ON A.AZIONE=B.MENU WHERE A.TIPO='M') x",
			'KEY_FIELD_NAME' => 'AZIONE',
			'COLUMN' => 'DESCRIZIONE',
			'AJAX' => true,
			'ALLOW_NEW' => True,
			'COMPLETE' => true,
			'COMPLETE_MIN' => 2,
			'COMPLETE_MAX_RESULT' => 15
		);
		$myField->setDecode($decodeParameters);
		
		/*$myLookUp = new wi400LookUp("LU_AZIONI");
		$myLookUp->addParameter("TIPO", "M");*/
		$myLookUp = new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("TITLE", "Codici Menu");
		$myLookUp->addParameter("LU_FROM", "(SELECT A.AZIONE, $sql_case_desc FROM FAZISIRI A LEFT JOIN FMNUSIRI B ON A.AZIONE=B.MENU WHERE A.TIPO='M') x");
		$myLookUp->addParameter("CAMPO", "AZIONE");
		$myLookUp->addParameter("DESCRIZIONE","DESCRIZIONE");
		$myLookUp->addField("codmen");
		$myField->setLookUp($myLookUp);
		
		$searchAction->addField($myField);
		
		$myButton = new wi400InputButton('SEARCH_BUTTON');
		$myButton->setLabel(_t('SELECT'));
		$myButton->setValidation(true);
		$myButton->setAction($azione_corrente);
		$myButton->setForm("DETAIL");
		$searchAction->addButton($myButton);
		
		$searchAction->dispose();
		
		$spacer->dispose();
		
		// Copia menu
		$searchAction = new wi400Detail('COPY_MENU', false);
		$searchAction->setTitle("Copia menu");
		$searchAction->isEditable(true);
		$searchAction->setColsNum(2);
		
		// Menu da copiare
		$myField = new wi400InputText('codmen1');
		$myField->setLabel(_t('MENU_CODE'));
//		$myField->addValidation("required");
		$myField->setCase("UPPER");	
		$myField->setMaxLength(40);
		//$myField->setValue($menu_old);
		$myField->setInfo(_t("ACTION_CODE_INFO"));
		
		$decodeParameters = array(
			'TYPE'			  => 'common',
			'COLUMN' => 'DESCRIZIONE',
			'TABLE_NAME' 	  => 'FAZISIRI',
			'KEY_FIELD_NAME'  => 'AZIONE',
		    'FILTER_SQL'       => "TIPO='M'",
			'AJAX' => true,
			'ALLOW_NEW' => True,
			'COMPLETE' => true,
			'COMPLETE_MIN' => 2,
			'COMPLETE_MAX_RESULT' => 15
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_AZIONI");
//		$myLookUp->addField("codmen");
		$myLookUp->addParameter("TIPO", "M");
		$myField->setLookUp($myLookUp);
		
		$searchAction->addField($myField);
		
		// Nuova azione in cui copiare
		$myField = new wi400InputText('codmen2');
		$myField->setLabel(_t("CODE")." ".strtolower(_t("COPY")));
//		$myField->addValidation("required");
		$myField->setCase("UPPER");	
		$myField->setMaxLength(40);
		//$myField->setValue($azione_new);
		$myField->setInfo(_t("ACTION_CODE_INFO"));
/*		
		$decodeParameters = array(
			'TYPE'=> 'common',
			'COLUMN' => 'DESCRIZIONE',
			'TABLE_NAME' => 'FAZISIRI',
			'KEY_FIELD_NAME' => 'AZIONE',
			'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_AZIONI");
//		$myLookUp->addField("codazi");
		$myField->setLookUp($myLookUp);
*/		
		$searchAction->addField($myField);
		
		$myButton = new wi400InputButton('COPY_BUTTON');
		$myButton->setLabel(_t("COPY"));
		$myButton->setAction($azione_corrente);
		$myButton->setForm("COPIA");
		$myButton->setValidation(true);
		$searchAction->addButton($myButton);
		
		$searchAction->dispose();
		
		$spacer->dispose();
		
		// Esportazione azioni
		$exportAction = new wi400Detail('EXPORT_MENU', True);
		$exportAction->setTitle(_t("MENU_EXPORT_TITLE"));
		$exportAction->isEditable(true);
		
		$myButton = new wi400InputButton('EXPORT_BUTTON');
		$myButton->setLabel(_t("EXPORT"));
//		$myButton->setScript("openWindow(_APP_BASE + APP_SCRIPT + '?DECORATION=lookUp&t=LU_AZIONI&f=EXPORT_LIST', '"._t('ACTION_EXPORT_TITLE')."')");
		$myButton->setAction("LU_MENU");
		$myButton->setForm("EXPORT_LIST");
		$myButton->setTarget("WINDOW");
		$myButton->setTitle(_t('MENU_EXPORT_TITLE'));
		$exportAction->addButton($myButton);
		
		$exportAction->dispose();
		
		$spacer->dispose();
		
		// Importazione azioni
		$importAction = new wi400Detail('IMPORT_MENU', True);
		$importAction->setTitle(_t("MENU_IMPORT_TITLE"));
		$importAction->isEditable(true);
		
		$myField = new wi400InputFile("IMPORT_FILE");
		$myField->setLabel(_t("MENU_IMPORT_DETAIL"));
		$importAction->addField($myField);
		
		$myField = new wi400InputCheckbox("OVERWRITE_EXP");
		$myField->setLabel(_t("MENU_REPLACE"));
		$myField->setChecked(false);
		$importAction->addField($myField);
		
		$myButton = new wi400InputButton('IMPORT_BUTTON');
		$myButton->setLabel(_t('IMPORT'));
		$myButton->setAction("MENU_IMPORT");
		$importAction->addButton($myButton);
		
		$importAction->dispose();
	}
	else if(in_array($actionContext->getForm(),array("DETAIL","COPIA"))) {
		$azioniDetail = new wi400Detail("DETTAGLIO_FMNUSIRI");
		$azioniDetail->isEditable(true);
		
		$actionsList = array();
		if(isset($row)) {
			// Se il menu esiste già
			$azioniDetail->setSource($row);
			
			if(isset($row["AZIONI"])) {
				$actionsList = explode(";",$row["AZIONI"]);
			}
		}
		else if(isset($_POST["AZIONI"])) {
			$actionsList = $_POST["AZIONI"];
		}
		
		$azioniDetail->addParameter("SAVE_ACTION", $saveAction);
		
		// Codice menu
		$myField = new wi400InputText('codmen');
		$myField->setLabel(_t('MENU_CODE'));
		if($actionContext->getForm()=="DETAIL")
			$myField->setValue($menu);
		else if($actionContext->getForm()=="COPIA")
			$myField->setValue($menu_new);
		$myField->setReadonly(true);
		$azioniDetail->addField($myField);
		
		// Descrizione
		$myField = new wi400InputText('DESCRIZIONE');
		$myField->setLabel(_t('DESCRIPTION'));
		$myField->setFromArray($resultArray);
		if(isset($_POST['DESCRIZIONE'])) {
			$myField->setValue($_POST['DESCRIZIONE']);
		}
		$myField->addValidation('required');
		$myField->setInfo(_t('MENU_DESCRIPTION_INFO'));
		$azioniDetail->addField($myField);
		
		// Script azione
		$myField = new wi400InputText('SCRIPT');
		$myField->setLabel(_t('ACTION_SCRIPT'));
		$azioniDetail->addField($myField);
		
		// Icone menu
		$myField = new wi400InputText('ICOMENU');
		$myField->setFromArray($resultArray);
		$myField->setLabel(_t('MENU_ICON'));
		$azioniDetail->addField($myField);
		
		// Icone menu "Expanded"
		$myField = new wi400InputText('EXPICO');	
		$myField->setFromArray($resultArray);
		$myField->setLabel(_t('MENU_ICON_EXPAND'));
		$azioniDetail->addField($myField);
		
		// Pgm Controllo Menu
		$myField = new wi400InputText('CHKPGM');
		$myField->setFromArray($resultArray);
		$myField->setLabel(_t('MENU_PGM_CHECK'));
		$azioniDetail->addField($myField);
		
		// Azioni menu
		$myField = new wi400InputText('AZIONI');
		$myField->setLabel(_t('MENU_ACTIONS'));
		$myField->addValidation('required');
		//$myField->setHideHeaderTable(True);
		$myField->setValue($actionsList);
		$myField->setCase("UPPER");			
		$myField->setShowMultiple(true);
		$myField->setSortMultiple(true);
	
		$decodeParameters = array(
			'TYPE'			  => 'menu',
			'COLUMN' 		  => 'DESCRIZIONE',
			'TABLE_NAME' 	  => 'FAZISIRI',
			'KEY_FIELD_NAME'  => 'AZIONE',
			'AJAX'            => true
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_AZIONI");
		$myLookUp->addField("AZIONI");
		$myField->setLookUp($myLookUp);
		
		$azioniDetail->addField($myField);

		// Percorso Help Menu
		$myField = new wi400InputText('AZIONE');
		$myField->setLabel(_t('MENU_HELP_PATH'));
		$azioniDetail->addField($myField);
		
		// Check
		$myButton = new wi400InputButton('CHECK');
		$myButton->setLabel("Check");
		$myButton->setAction($azione_corrente);
		$myButton->setForm("CHECK");
		$azioniDetail->addButton($myButton);
		
		// Salva
		$myButton = new wi400InputButton('SAVE_BUTTON');
		if($saveAction=="UPDATE")
			$myButton->setLabel(_t('UPDATE'));
		else if(in_array($saveAction, array("INSERT", "COPY")))
			$myButton->setLabel(_t('SAVE'));
		$myButton->setAction($azione_corrente);
		$myButton->setForm($saveAction);
		$myButton->setValidation(true);
		$azioniDetail->addButton($myButton);
		
		// Annulla
		$myButton = new wi400InputButton('CANCEL_BUTTON');
		$myButton->setLabel(_t('CANCEL'));
		$myButton->setAction($first_action_name);
		$myButton->setForm("DEFAULT");
		$myButton->setValidation(False);
		$azioniDetail->addButton($myButton);
	
		// Elimina menu
		if($saveAction=="UPDATE") {
			$myButton = new wi400InputButton('DELETE_BUTTON');
			$myButton->setLabel(_t('DELETE'));
			$myButton->setAction($azione_corrente);
			$myButton->setForm("DELETE");
			$myButton->setValidation(False);
			$azioniDetail->addButton($myButton);
		}
		
		$azioniDetail->dispose();
	}

?>