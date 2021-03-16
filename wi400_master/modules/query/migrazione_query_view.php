<?php

	if($actionContext->getForm()=="DEFAULT") {
		$azioniDetail = new wi400Detail($azione."_DET", false);
		$azioniDetail->setColsNum(2);
		
		$labelDetail = new wi400Text("FROM_FILE");
		$labelDetail->setLabel("Da File");
		$labelDetail->setValue("QUERY_TOOL_LIBERO_SRC");
		$azioniDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("TO_FILE");
		$labelDetail->setLabel("A File");
		$labelDetail->setValue("QUERY_TOOL_SRC");
		$azioniDetail->addField($labelDetail);
		
		$myField = new wi400InputSwitch("OVERWRITE");
		$myField->setLabel("Sovrascrivi Filtri");
		$myField->setOnLabel(_t('LABEL_YES'));
		$myField->setOffLabel(_t('LABEL_NO'));
		$myField->setChecked($check_overwrite);
		$myField->setValue(1);
		$azioniDetail->addField($myField);
		
		// Utente
		$myField = new wi400InputText('TO_USER');
		$myField->setLabel("Per Utente");
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$myField->setCase("UPPER");
		$myField->setValue($to_user_sel);
		$myField->setShowMultiple(true);
//		$myField->addValidation('required');
			
		$decodeParameters = array(
			'TYPE' => 'common',
			'TABLE_NAME' => $id_user_file_lib.$settings['db_separator'].$id_user_file,
			'COLUMN' => $id_user_desc,
			'KEY_FIELD_NAME' => $id_user_name,
//			'FILTER_SQL' => "DSPRAD not like 'SIRI - %'",
			'FILTER_SQL' => $id_user_name."<>'$user_src'",
			'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
			
		$myLookUp = new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE",$id_user_file_lib.$settings['db_separator'].$id_user_file);
		$myLookUp->addParameter("CAMPO",$id_user_name);
		$myLookUp->addParameter("DESCRIZIONE",$id_user_desc);
//		$myLookUp->addParameter("LU_WHERE","DSPRAD not like 'SIRI - %'");
		$myLookUp->addParameter("LU_WHERE",$id_user_name."<>'$user_src'");
		if ($id_user_file==$users_table) {
			$myLookUp->addParameter("LU_SELECT","FIRST_NAME");
		}
		$myField->setLookUp($myLookUp);
			
		$azioniDetail->addField($myField);
		
		$myButton = new wi400InputButton('SEARCH_BUTTON');
		$myButton->setLabel("Migra");
		$myButton->setAction($azione);
		$myButton->setForm("MIGRA");
		$myButton->setValidation(true);
		$azioniDetail->addButton($myButton);
		
		$azioniDetail->dispose();
	}