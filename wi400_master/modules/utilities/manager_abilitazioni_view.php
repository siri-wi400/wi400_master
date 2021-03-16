<?php

	if($actionContext->getForm()=="DEFAULT") {
		$miaLista = new wi400List($azione."_LIST", !$isFromHistory);
		
		$miaLista->setField("a.*, rtrim(b.first_name)!!' '!!rtrim(b.last_name) as user_des");
		$miaLista->setFrom("$tabella a left join sir_users b on usrusr=user_name");
		$miaLista->setOrder("USRUSR");
		
		$miaLista->setSelection("MULTIPLE");
		
//		$miaLista->setCanExport("RESUBMIT");
		
		$miaLista->setCols(array(
			new wi400Column("USRUSR", "Codice<br>utente"),
			new wi400Column("USER_DES", "Descrizione<br>utente"),
//			new wi400Column("USRAPP", "Codice<br>applicazione")
		));
		
//		foreach($abil_cols as $val) {
		foreach($des_cols as $val => $des) {
//			$col = new wi400Column($val, $des_cols[$val], "STRING", "center");
			$col = new wi400Column($val, $des, "STRING", "center");
			
			// Azione di spunta colonna
			$col->setHeaderAction($azione);
			$col->setHeaderForm("CHECK_ALL");
			$col->setHeaderIco(array("uncheck.png","check.png"));
			$col->setHeaderCallBack("setUpdateStatus(UPDATE_STATUS_ON)");
			
			$inputField = new wi400InputCheckbox($val."I");
			$inputField->setCheckUpdate(True);
			$inputField->setValue("S");
			$inputField->setUncheckedValue("N");
				
			$col->setInput($inputField);
			$col->setSortable(false);
			$col->setGroup("ABILITAZIONI");
			
			$miaLista->addCol($col);
		}
		
		$miaLista->addKey("USRUSR");
		
		$myFilter = new wi400Filter("USRUSR","Codice Utente");
		$myFilter->setFast(true);
		$myFilter->setCase('UPPER');
		$miaLista->addFilter($myFilter);
		
		// Salva spunte
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("SALVA_SPUNTE");
		$action->setLabel("Conferma Spunte");
//		$action->setSelection("MULTIPLE");
		$miaLista->addAction($action);
		
		// Aggiunta utente
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("ADD_USER");
		$action->setLabel("Aggiunta utente");
		$action->setTarget("WINDOW");
		$action->setSelection("NONE");
//		Serialize form data in WINDOW target mode. Settare a False se vengono trasmessi troppi campi dal video precednete (Campi Input)
//		Se false non vengono passati in $_GET i dati di input. Explorer ha una lunghezza massima di 2000 byte
		$action->setSerialize(false);
		$miaLista->addAction($action);
		
		// Duplica utente
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("DUPLICA_USER");
		$action->setLabel("Duplica utente");
		$action->setTarget("WINDOW");
		$action->setSelection("SINGLE");
//		Serialize form data in WINDOW target mode. Settare a False se vengono trasmessi troppi campi dal video precednete (Campi Input)
//		Se false non vengono passati in $_GET i dati di input. Explorer ha una lunghezza massima di 2000 byte
		$action->setSerialize(false);
		$miaLista->addAction($action);
		
		// Elimina utente
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("DELETE_USER");
		$action->setLabel("Elimina utente");
		$action->setSelection("MULTIPLE");
		$miaLista->addAction($action);
		
		listDispose($miaLista);
	}
	else if($actionContext->getForm()=="ADD_USER") {
		$detAction = new wi400Detail($azione."_".$actionContext->getForm()."_DET", true);
		$detAction->setTitle('Aggiunta utente');
		$detAction->isEditable(true);
		
		$myField = new wi400InputText('UTENTE');
		$myField->setLabel(_t('USER_CODE'));
		$myField->addValidation('required');
		$myField->setShowMultiple(true);
		$myField->setSize(20);
		$myField->setMaxLength(20);
		$myField->setCase("UPPER");
		$myField->setInfo(_t('USER_CODE_INFO'));
//		$myField->setValue($user);
/*		
//		if (isset($settings['only_as_user']) && $settings['only_as_user']==True) {
			$decodeParameters = array(
//				'TYPE' => 'i5_object',
//				'OBJTYPE' => '*USRPRF'
					'TYPE'=> 'common',
					'COLUMN' => 'EMAIL',
					'TABLE_NAME' => $users_table,
					'KEY_FIELD_NAME' => 'USER_NAME',
					'AJAX' => true			
			);
			$myField->setDecode($decodeParameters);
//		}

		$myLookUp = new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE",$users_table);
		$myLookUp->addParameter("CAMPO","USER_NAME");
		$myLookUp->addParameter("DESCRIZIONE","EMAIL");
		$myField->setLookUp($myLookUp);
*/		
		$decodeParameters = array(
			'TYPE' => 'user',
			'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_USER");
		$myField->setLookUp($myLookUp);
			
		$detAction->addField($myField);
		
		foreach($abil_cols as $val) {
			// Abilitazione
			$inputField = new wi400InputCheckbox($val);
			$inputField->setLabel(str_replace("<br>", " ", $des_cols[$val]));
			$inputField->setChecked(false);
			
			$detAction->addField($inputField);
		}
		
		$detAction->dispose();
		
		$myButton = new wi400InputButton("SAVE_BUTTON");
		$myButton->setAction($azione);
		$myButton->setForm("SAVE_USER");
		$myButton->setLabel("Salva");
		$myButton->setValidation(true);
		$buttonsBar[] = $myButton;
		
		$myButton = new wi400InputButton("CLOSE_BUTTON");
		$myButton->setScript('closeLookUp()');
		$myButton->setLabel("Annulla");
		$buttonsBar[] = $myButton;
	}
	else if($actionContext->getForm()=="DUPLICA_USER") {
		$detAction = new wi400Detail($azione."_".$actionContext->getForm()."_DET", true);
		$detAction->setTitle('Duplica utente');
		$detAction->isEditable(true);
	
		$myField = new wi400InputText('UTENTE');
		$myField->setLabel(_t('USER_CODE'));
		$myField->addValidation('required');
//		$myField->setShowMultiple(true);
		$myField->setSize(20);
		$myField->setMaxLength(20);
		$myField->setCase("UPPER");
		$myField->setInfo(_t('USER_CODE_INFO'));
//		$myField->setValue($user);
	
//		if (isset($settings['only_as_user']) && $settings['only_as_user']==True) {
			$decodeParameters = array(
//				'TYPE' => 'i5_object',
//				'OBJTYPE' => '*USRPRF'
				'TYPE'=> 'common',
				'COLUMN' => 'EMAIL',
				'TABLE_NAME' => $users_table,
				'KEY_FIELD_NAME' => 'USER_NAME',
				'AJAX' => true
			);
			$myField->setDecode($decodeParameters);
//		}
	
		$myLookUp = new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE",$users_table);
		$myLookUp->addParameter("CAMPO","USER_NAME");
		$myLookUp->addParameter("DESCRIZIONE","EMAIL");
		$myField->setLookUp($myLookUp);
	
		$detAction->addField($myField);
		
		$detAction->dispose();
		
		$myButton = new wi400InputButton("SAVE_BUTTON");
		$myButton->setAction($azione);
		$myButton->setForm("SAVE_DUPLICA_USER");
		$myButton->setLabel("Salva");
		$myButton->setValidation(true);
		$buttonsBar[] = $myButton;
		
		$myButton = new wi400InputButton("CLOSE_BUTTON");
		$myButton->setScript('closeLookUp()');
		$myButton->setLabel("Annulla");
		$buttonsBar[] = $myButton;
	}
	else if($actionContext->getForm()=="CLOSE_WINDOW") {
		close_window();
	}