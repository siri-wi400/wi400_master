<?php

	$spacer = new wi400Spacer();

	if($actionContext->getForm()=="DEFAULT") {
		$azioniDetail = new wi400Detail($azione."_DET", false);
		$azioniDetail->setColsNum(2);
		
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
		$myButton->setLabel("Mostra");
		$myButton->setAction($azione);
		$myButton->setForm("SHOW");
//		$myButton->setValidation(true);
		$azioniDetail->addButton($myButton);
		
		$azioniDetail->dispose();
	}
	else if($actionContext->getForm()=="SHOW") {
		$i = 0;
		
		foreach($query_array as $user => $query) {
			if(!empty($query['IND'])) {
				$searchAction = new wi400Detail($azione."_.".$user."_IND_DET", true);
				$searchAction->setTitle("Utente $user - Query Indirizzate (".count($query['IND']).")");
				
				foreach($query['IND'] as $titolo => $testo) {
					$myField = new wi400InputTextArea('QUERY_'.$i);
					$myField->setLabel($titolo);
					$myField->setSize(130);
					$myField->setRows(3);
					$myField->setValue(trim($testo));
					$myField->setReadonly(true);
					$searchAction->addField($myField);
					
					$i++;
				}
				
				$searchAction->dispose();
			}
			
			if(!empty($query['LIB'])) {
				$searchAction = new wi400Detail($azione."_.".$user."_LIB_DET", true);
				$searchAction->setTitle("Utente $user - Query Libere (".count($query['LIB']).")");
					
				foreach($query['LIB'] as $titolo => $testo) {
					$myField = new wi400InputTextArea('QUERY_'.$i);
					$myField->setLabel($titolo);
					$myField->setSize(130);
					$myField->setRows(3);
					$myField->setValue(trim($testo));
					$myField->setReadonly(true);
					$searchAction->addField($myField);
				
					$i++;
				}
					
				$searchAction->dispose();
			}
			
			$spacer->dispose();
		}
	}