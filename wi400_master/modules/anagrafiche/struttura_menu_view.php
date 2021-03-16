<?php

	$spacer = new wi400Spacer();

	if($actionContext->getForm()=="DEFAULT") {
		// Esportazione struttura Menu
		$exportAction = new wi400Detail('EXPORT_MENU_STRUTTURA', True);
		$exportAction->setTitle("Esportazione Struttura Menu");
		$exportAction->isEditable(true);
		
		$myField = new wi400InputText('MENU');
		$myField->setLabel(_t('MENU_CODE'));
//		$myField->addValidation('required');
		$myField->setMaxLength(30);
		$myField->setCase("UPPER");
//		$myField->setInfo(_t('MENU_CODE_INFO'));
		$myField->setValue($menu_array);
		$myField->setShowMultiple(true);
		
		$decodeParameters = array(
			'TYPE'			  => 'common',
			'COLUMN' => 'DESCRIZIONE',
			'TABLE_NAME' 	  => 'FAZISIRI',
			'KEY_FIELD_NAME'  => 'AZIONE',
			'FILTER_SQL'       => "TIPO='M'",
			'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_AZIONI");
		$myLookUp->addField("codmen");
		$myLookUp->addParameter("TIPO", "M");
		$myField->setLookUp($myLookUp);
		
		$exportAction->addField($myField);
		
		$myButton = new wi400InputButton('STRUTTURA_BUTTON');
		$myButton->setLabel(_t("ESPORTA"));
		$myButton->setAction("STRUTTURA_MENU");
		$myButton->setForm("MENU");
		$myButton->setTarget("WINDOW");
		$myButton->setConfirmMessage("Esportare la struttura dei menu?");
		$exportAction->addButton($myButton);
		
		$exportAction->dispose();
		
		$spacer->dispose();
		
		// Esportazione struttura Menu degli utenti
		$exportAction = new wi400Detail('EXPORT_MENU_STRUTTURA_USER', True);
		$exportAction->setTitle("Esportazione Struttura Menu degli Utenti");
		$exportAction->isEditable(true);
		
		$myField = new wi400InputText('USER_1');
		$myField->setLabel(_t('USER_CODE'));
//		$myField->addValidation('required');
		$myField->setMaxLength(20);
		$myField->setCase("UPPER");
//		$myField->setInfo(_t('USER_CODE_INFO'));
		$myField->setValue($user_array_1);
		$myField->setShowMultiple(true);
		
		$decodeParameters = array(
			'TYPE' => 'i5_object',
			'OBJTYPE' => '*USRPRF'
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp =new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE", $users_table);
		$myLookUp->addParameter("CAMPO","USER_NAME");
		$myLookUp->addParameter("DESCRIZIONE","EMAIL");
		$myField->setLookUp($myLookUp);
		
		$exportAction->addField($myField);
		
		$myButton = new wi400InputButton('STRUTTURA_BUTTON');
		$myButton->setLabel(_t("ESPORTA"));
		$myButton->setAction("STRUTTURA_MENU");
		$myButton->setForm("USER");
		$myButton->setTarget("WINDOW");
		$myButton->setConfirmMessage("Esportare la struttura dei menu degli utenti?");
		$exportAction->addButton($myButton);
		
		$exportAction->dispose();
		
		$spacer->dispose();
		
		// Esportazione struttura Menu degli utenti
		$exportAction = new wi400Detail('EXPORT_SEARCH_ACTION', True);
		$exportAction->setTitle("Trova Azione");
		$exportAction->isEditable(true);
		
		// Gestione delle azioni
		$myField = new wi400InputText('AZIONE');
		$myField->setLabel(_t("ACTION_CODE"));
//		$myField->addValidation("required");
		$myField->setCase("UPPER");
		$myField->setMaxLength(40);
		$myField->setValue($azione_search);
//		$myField->setInfo(_t("ACTION_CODE_INFO"));
		
		$decodeParameters = array(
			'TYPE'=> 'common',
			'COLUMN' => 'DESCRIZIONE',
			'TABLE_NAME' => 'FAZISIRI',
			'KEY_FIELD_NAME' => 'AZIONE',
			'AJAX' => true
		);
//		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_AZIONI");
//		$myLookUp->addField("AZIONE");
		$myField->setLookUp($myLookUp);
		
		$exportAction->addField($myField);
		
		$myField = new wi400InputText('USER_2');
		$myField->setLabel(_t('USER_CODE'));
//		$myField->addValidation('required');
		$myField->setMaxLength(20);
		$myField->setCase("UPPER");
//		$myField->setInfo(_t('USER_CODE_INFO'));
		$myField->setValue($user_array_2);
		$myField->setShowMultiple(true);
		
		$decodeParameters = array(
			'TYPE' => 'i5_object',
			'OBJTYPE' => '*USRPRF'
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp =new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE", $users_table);
		$myLookUp->addParameter("CAMPO","USER_NAME");
		$myLookUp->addParameter("DESCRIZIONE","EMAIL");
		$myField->setLookUp($myLookUp);
		
		$exportAction->addField($myField);
		
		$myButton = new wi400InputButton('STRUTTURA_BUTTON');
		$myButton->setLabel(_t("ESPORTA"));
		$myButton->setAction("STRUTTURA_MENU");
		$myButton->setForm("SEARCH_AZIONE");
		$myButton->setTarget("WINDOW");
		$myButton->setConfirmMessage("Esportare la struttura dei menu in cui è presente l'azione?");
		$exportAction->addButton($myButton);
		
		$exportAction->dispose();
	}
	else if(in_array($actionContext->getForm(), $in_azioni)) {
		$myButton = new wi400InputButton("CLOSE_BUTTON");
		$myButton->setScript('closeLookUp()');
		$myButton->setLabel("Chiudi");
		$buttonsBar[] = $myButton;
		
		downloadDetail($TypeImage, $filename, $temp, "Esportazione completata");
	}