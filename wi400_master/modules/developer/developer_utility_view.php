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
		$myButton->setAction($azione);
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
		// Pulizia Cache WSDL 
		$mySelect = new wi400InputCheckbox('WSDL');
		$mySelect->setLabel("Clear WSDL Cache (Web Services)");
		$mySelect->setValue('WSDL');
		$deleteAction->addField($mySelect);
		$field = new wi400Text("vuoto5");
		$field->setLabel("");
		$deleteAction->addField($field);
		// Pulizia Cache op cache 
		if ($opcache) {			
			$mySelect = new wi400InputCheckbox('OPCACHE');
			$mySelect->setLabel("Clear OPCACACHE");
			$mySelect->setValue('OPCACHE');
			$deleteAction->addField($mySelect);
			$field = new wi400Text("vuoto3");
			$field->setLabel("");
			$deleteAction->addField($field);
		}
		// Pulizia wincache
		if ($wincache) {
			$mySelect = new wi400InputCheckbox('WINCACHE');
			$mySelect->setLabel("Clear WINCACHE");
			$mySelect->setValue('WINCACHE');
			$deleteAction->addField($mySelect);
			$field = new wi400Text("vuoto4");
			$field->setLabel("");
			$deleteAction->addField($field);
		}
		// PHPINFO
		
		$myButton = new wi400InputButton('PHPINFO');
		$myButton->setLabel("PHPInfo");
		$myButton->setAction($azione);
		$myButton->setForm("PHPINFO");
		$myButton->setTarget("WINDOW", 1000, 700);
		$deleteAction->addButton($myButton);
		// Esecuzione QUERY
		$myButton = new wi400InputButton('QUERY');
		$myButton->setLabel("Query");
		$myButton->setAction("QUERY_TOOL_DB");
		$myButton->setTarget("WINDOW", 1000, 700);
		$deleteAction->addButton($myButton);
		// Visualizzazione Lavori Attivi
		if ($showxml == True){
			$myButton = new wi400InputButton('JOB_LOg');
			$myButton->setLabel("Lavori Attivi");
			$myButton->setAction("JOB_LOG");
			$myButton->setTarget("WINDOW", 1000, 700);
			$deleteAction->addButton($myButton);
		}
		
		$myButton = new wi400InputButton('MAPPING_BUTTON');
		$label_mapping = "Attiva mapping";
		if(isset($_SESSION['BUTTON_MAPPA_DETAIL'])) {
			$label_mapping = "Disattiva mapping";
		}
		$myButton->setLabel($label_mapping);
		$myButton->setAction($azione);
		$myButton->setForm('MAPPING_BUTTON');
		$deleteAction->addButton($myButton);
		
		$deleteAction->dispose();
	}else if($actionContext->getForm() == "MAPPING_BUTTON") {
?>
		<script type="text/javascript">
			wi400top.doSubmit(wi400top.CURRENT_ACTION, wi400top.CURRENT_FORM);
		</script>
<?php 
	}
	
	if($actionContext->getForm()=="PHPINFO") {
		
		phpinfo();
	}
?>