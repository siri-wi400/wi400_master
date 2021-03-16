<?php

	if($actionContext->getForm()=="DEFAULT") {
		$deleteAction = new wi400Detail('DELETE_FILES_TMP_SEL');
		$deleteAction->setTitle('Pulizia');
		$deleteAction->isEditable(true);
		$deleteAction->setColsNum(2);

		$mySelect = new wi400InputCheckbox('DIR_SERIALIZE');
		$mySelect->setLabel("Cache serialized dei file");
		$mySelect->setValue('DIR_SERIALIZE');
		$deleteAction->addField($mySelect);
		
		// vuoto
		$field = new wi400Text("vuoto");
		$field->setLabel("");
		$deleteAction->addField($field);
		
		$mySelect = new wi400InputCheckbox('SESSION');
		$mySelect->setLabel("Sessioni");
		$mySelect->setValue('SESSION');
		$deleteAction->addField($mySelect);
		
		// vuoto
		$field = new wi400Text("vuoto");
		$field->setLabel("");
		$deleteAction->addField($field);
		
		$mySelect = new wi400InputCheckbox('TMP_TABLES');
		$mySelect->setLabel("Tabelle temporanee");
		$mySelect->setValue('TMP_TABLES');
		$deleteAction->addField($mySelect);
		
		// vuoto
		$field = new wi400Text("vuoto");
		$field->setLabel("");
		$deleteAction->addField($field);
		
		$mySelect = new wi400InputCheckbox('LOG_SQL');
		$mySelect->setLabel("Log SQL");
		$mySelect->setValue('LOG_SQL');
		$deleteAction->addField($mySelect);
		
		// vuoto
		$field = new wi400Text("vuoto");
		$field->setLabel("");
		$deleteAction->addField($field);
		
		$mySelect = new wi400InputCheckbox('WSLOG');
		$mySelect->setLabel("Log dei webserver");
		$mySelect->setValue('WSLOG');
		$deleteAction->addField($mySelect);
		
		$myButton = new wi400InputButton('DELETE_BUTTON');
		$myButton->setLabel("Elimina");
		$myButton->setAction("DELETE_FILES_TMP");
		$myButton->setForm("DELETE");
		$myButton->setValidation(true);
		$deleteAction->addButton($myButton);
		
		// Data di creazione file
		$myField = new wi400InputText('DATA_CREAZIONE');
		$myField->setLabel("Data limite pulizia");
		if(!isset($data_creazione) || empty($data_creazione)) {
			$data = mktime(0, 0, 0, date("m")  , date("d")-7, date("Y"));	
			$myField->setValue(date("d/m/Y", $data));
		}
		else
			$myField->setValue($data_creazione);
		$myField->addValidation('date');	
		$myField->setInfo('Inserire la data di creazione dei files, entro cui eseguire la pulizia dei log dei webserver');
		$deleteAction->addField($myField);
		
		$deleteAction->dispose();
	}

?>