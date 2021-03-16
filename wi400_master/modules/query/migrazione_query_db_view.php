<?php

	if($actionContext->getForm()=="DEFAULT") {
		$azioniDetail = new wi400Detail($azione."_DET", false);
		$azioniDetail->setColsNum(2);
		
		$mySelect = new wi400InputSelect('FROM_FILE');
		$mySelect->setLabel("Da File");
		$mySelect->addOption("QUERY_TOOL_SRC", "QUERY_TOOL_SRC");
		$mySelect->addOption("QUERY_TOOL_LIBERO_SRC", "QUERY_TOOL_LIBERO_SRC");
		$mySelect->setValue($from_det);
		$azioniDetail->addField($mySelect);
/*		
		$myField = new wi400InputSwitch("OVERWRITE");
		$myField->setLabel("Sovrascrivi Query");
		$myField->setOnLabel(_t('LABEL_YES'));
		$myField->setOffLabel(_t('LABEL_NO'));
		$myField->setChecked($check_overwrite);
		$myField->setValue(1);
		$azioniDetail->addField($myField);
*/
		$mySelect = new wi400InputSelect('TIPO_GEST');
		$mySelect->setLabel("Tipo Gestione Query con stesso nome");
//		$mySelect->setFirstLabel("Seleziona...");
//		$mySelect->setFirstLabel("Ignora query");
		$mySelect->addOption("Confronta query", "CONFRONTA");
		$mySelect->addOption("Aggiungi solo associazione utente", "ADD_USER");
		$mySelect->addOption("Sovrascrivi query", "OVERWRITE");
		$mySelect->addOption("Duplica query", "DUPLICA");
		$mySelect->addOption("Ignora query", "IGNORA");
		$mySelect->setValue($tipo_gest);
		$azioniDetail->addField($mySelect);
		
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
//			'FILTER_SQL' => $id_user_name." not in ('".implode("', '", $to_user_sel)."')",
			'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
			
		$myLookUp = new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE",$id_user_file_lib.$settings['db_separator'].$id_user_file);
		$myLookUp->addParameter("CAMPO",$id_user_name);
		$myLookUp->addParameter("DESCRIZIONE",$id_user_desc);
//		$myLookUp->addParameter("LU_WHERE",$id_user_name." not in ('".implode("', '", $to_user_sel)."')");
		if($id_user_file==$users_table) {
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