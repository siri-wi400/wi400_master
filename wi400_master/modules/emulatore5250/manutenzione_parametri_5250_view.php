<?php

	if($actionContext->getForm()=="DEFAULT") {
		// Dettaglio job di schedulazione
		$actionDetail = new wi400Detail($azione."_DETAIL", true);
		
		// Abilitato
		$myField = new wi400InputSwitch("ABILITATO");
		$myField->setLabel("Abilitato");
		$myField->setOnLabel(_t('LABEL_YES'));
		$myField->setOffLabel(_t('LABEL_NO'));
		$myField->setChecked($check_abil);
//		$myField->setValue(1);
//		$myField->setValue("S");
		$actionDetail->addField($myField);
		
		// Log su File
		$myField = new wi400InputSwitch("LOG_FILE");
		$myField->setLabel("Log su File");
		$myField->setOnLabel(_t('LABEL_YES'));
		$myField->setOffLabel(_t('LABEL_NO'));
		$myField->setChecked($check_log_file);
//		$myField->setValue(1);
//		$myField->setValue("S");
		$actionDetail->addField($myField);
		
		// Log Testuale
		$myField = new wi400InputSwitch("LOG_TXT");
		$myField->setLabel("Log Testuale");
		$myField->setOnLabel(_t('LABEL_YES'));
		$myField->setOffLabel(_t('LABEL_NO'));
		$myField->setChecked($check_log_txt);
//		$myField->setValue(1);
//		$myField->setValue("S");
		$actionDetail->addField($myField);
		
		// Reply Command
		$myField = new wi400InputSwitch("REPLY_COMMAND");
		$myField->setLabel("Reply Command");
		$myField->setOnLabel(_t('LABEL_YES'));
		$myField->setOffLabel(_t('LABEL_NO'));
		$myField->setChecked($check_reply_command);
//		$myField->setValue(1);
//		$myField->setValue("S");
		$actionDetail->addField($myField);
		
		// Mappa Campi
		$myField = new wi400InputSwitch("MAPPA_CAMPI");
		$myField->setLabel("Mappa Campi");
		$myField->setOnLabel(_t('LABEL_YES'));
		$myField->setOffLabel(_t('LABEL_NO'));
		$myField->setChecked($check_mappa_campi);
//		$myField->setValue(1);
//		$myField->setValue("S");
		$actionDetail->addField($myField);
		
		// CCSID
		$myField = new wi400InputText("CCSID");
		$myField->setLabel("CCSID");
		$myField->setSize(9);
		$myField->setMaxLength(9);
		$myField->setMask("1234567890");
		$myField->setValue($ccsid);
		$actionDetail->addField($myField);
		
		// Code Page
		$myField = new wi400InputText("CODE_PAGE");
		$myField->setLabel("Code Page");
		$myField->setSize(9);
		$myField->setMaxLength(9);
		$myField->setMask("1234567890");
		$myField->setValue($code_page);
		$actionDetail->addField($myField);
		
		// Tipo Terminale
		$myField = new wi400InputText("TIPO_TERMINALE");
		$myField->setLabel("Tipo Terminale");
		$myField->setSize(9);
		$myField->setMaxLength(9);
		$myField->setMask("1234567890");
		$myField->setValue($tipo_terminale);
		$actionDetail->addField($myField);
		
		// Identifica Video
		$myField = new wi400InputSwitch("ID_VIDEO");
		$myField->setLabel("Identifica Video");
		$myField->setOnLabel(_t('LABEL_YES'));
		$myField->setOffLabel(_t('LABEL_NO'));
		$myField->setChecked($check_id_video);
//		$myField->setValue(1);
//		$myField->setValue("S");
		$actionDetail->addField($myField);
		
		// Salva
		$myButton = new wi400InputButton('SAVE_BUTTON');
		$myButton->setLabel("Salva");
		$myButton->setAction($azione);
		$myButton->setForm("SAVE");
		$myButton->setValidation(true);
		$actionDetail->addButton($myButton);
		$actionDetail->dispose();
	}