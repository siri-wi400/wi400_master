<?php

	$spacer = new wi400Spacer();

	if(in_array($actionContext->getForm(),array("DEFAULT","SEARCH"))) {
		$searchAction = new wi400Detail($azione.'_SRC', True);
		$searchAction->setTitle('Parametri di ricerca');
		$searchAction->isEditable(true);
		
		// Parole da cercare
		$myField = new wi400InputText('SEARCH_WORDS');
		$myField->setLabel('Parole da cercare');
		$myField->addValidation('required');
		$myField->setMaxLength(300);
		$myField->setSize(60);
		$myField->setValue($searchString);
		$searchAction->addField($myField);
		
		// Tipo di ricercca
		$mySelect = new wi400InputSelect('SEARCH_TYPE');
		$mySelect->setLabel("Tipo di ricerca");
		$mySelect->addValidation('required');
		$mySelect->setFirstLabel("Seleziona...");
		$mySelect->setOptions($array_SearchTypes);
		$mySelect->setValue($searchType);
		$searchAction->addField($mySelect);
		
		// Case Sensitive
		$myField = new wi400InputCheckbox("CASE_SENSITIVE");
		$myField->setLabel("Case Sensitive");
		$myField->setChecked($caseSensitive);
		$searchAction->addField($myField);
		
		// Directory da controllare
		$myField = new wi400InputText('SEARCH_DIR');
		$myField->setLabel('Directory da controllare');
//		$myField->addValidation('required');
		$myField->setMaxLength(300);
		$myField->setSize(60);
		$myField->setValue($scanDir);
		$searchAction->addField($myField);
/*
		$myField = new wi400InputFile("SEARCH_FILE");
		$myField->setLabel("File da controllare");
//		$myField->addValidation('required');
		$searchAction->addField($myField);
*/
		// Directory da controllare
		$myField = new wi400InputText('SEARCH_FILE');
		$myField->setLabel('File da controllare');
//		$myField->addValidation('required');
		$myField->setShowMultiple(true);
		$myField->setMaxLength(300);
		$myField->setSize(60);
		$myField->setValue($file_name_array);
//		$myField->setInfo("Path completo del file da controllare");
		$searchAction->addField($myField);	
		
		// Elenco subdirectories da controllare
		$myField = new wi400InputText('SUBDIR_ARRAY');
		$myField->setLabel('Elenco di Subdirectories da controllare');
//		$myField->addValidation('required');
		$myField->setShowMultiple(true);
		$myField->setMaxLength(300);
		$myField->setSize(60);
		$myField->setValue($subDir_array);
		$searchAction->addField($myField);
		
		// Subdirectory da controllare iniziale
		$myField = new wi400InputText('SUBDIR_SPAN_INI');
		$myField->setLabel('Subdirectory da controllare iniziale');
//		$myField->addValidation('required');
		$myField->setMaxLength(300);
		$myField->setSize(60);
		$myField->setValue($startSpan);
		$searchAction->addField($myField);
		
		// Subdirectory da controllare finale
		$myField = new wi400InputText('SUBDIR_SPAN_FIN');
		$myField->setLabel('Subdirectory da controllare finale');
//		$myField->addValidation('required');
		$myField->setMaxLength(300);
		$myField->setSize(60);
		$myField->setValue($endSpan);
		$searchAction->addField($myField);
		
		// Elenco estensioni da controllare
		$myField = new wi400InputText('SEARCH_EXTENSIONS');
		$myField->setLabel('Elenco di Estensioni da controllare');
//		$myField->addValidation('required');
		$myField->setShowMultiple(true);
		$myField->setMaxLength(10);
		$myField->setSize(10);
		$myField->setValue($ExtentionsToBeSearched);
		$searchAction->addField($myField);
		
		// Controllare tutte le estensioni
		$myField = new wi400InputCheckbox("SEARCH_ALL_EXT");
		$myField->setLabel("Controllare tutte le estensioni");
		$myField->setChecked($search_all_ext);
		$myField->setInfo("Controlla tutte le estensioni, tranne quelle specificate da ignorare");
		$searchAction->addField($myField);
		
		// Elenco estensioni da scartare
		$myField = new wi400InputText('IGNORE_EXTENSIONS');
		$myField->setLabel('Elenco di Estensioni da ignorare');
//		$myField->addValidation('required');
		$myField->setShowMultiple(true);
		$myField->setMaxLength(10);
		$myField->setSize(10);
		$myField->setValue($ExtentionsToBeIgnored);
		$searchAction->addField($myField);
		
		// Display Search Count Only
		$myField = new wi400InputCheckbox("SEARCH_COUNT_ONLY");
		$myField->setLabel("Display Search Count Only");
		$myField->setChecked($searchCountOnly);
		$searchAction->addField($myField);
		
		$myButton = new wi400InputButton('SEARCH_BUTTON');
		$myButton->setLabel("Avvia Ricerca");
		$myButton->setAction($azione);
		$myButton->setForm("SEARCH");
		$myButton->setConfirmMessage("Avviare la ricerca?");
		$myButton->setValidation(true);
		$searchAction->addButton($myButton);
		
		$myButton = new wi400InputButton('CLEAR_BUTTON');
		$myButton->setLabel("Annulla");
		$myButton->setAction($azione);
		$myButton->setForm("CLEAR");
		$myButton->setConfirmMessage("Ricominciare?");
		$searchAction->addButton($myButton);
		
		$searchAction->dispose();
	}
	
	if($actionContext->getForm()=="SEARCH") {
		$spacer->dispose();
		
//		echo $risultati;
/*		
		$actionDetail = new wi400Detail($azione.'_DET', True);
		$actionDetail->setTitle('Risultati della ricerca');
		
		// Descrizione
		$myField = new wi400TextPanel('RISULTATI');
		$myField->setValue(strip_tags(prepare_string($risultati)));
		$actionDetail->addField($myField);
		
		$actionDetail->dispose();
*/		
		$actionDetail = new wi400Detail($azione.'_STAT_DET', True);
		$actionDetail->setTitle('Statistiche della ricerca');
		$actionDetail->setColsNum(3);
		
		$myField = new wi400Text("NUM_POS_FILES");
		$myField->setLabel("N° Files con Risultati");
		$myField->setValue($fileCounter);
		$actionDetail->addField($myField);
	
		$myField = new wi400Text("NUM_SEARCH_FILES");
		$myField->setLabel("N° files controllati");
		$myField->setValue($num_search_files);
		$actionDetail->addField($myField);
		
		$myField = new wi400Text("NUM_SEARCH_DIRS");
		$myField->setLabel("N° directories controllate");
		$myField->setValue($num_search_dirs);
		$actionDetail->addField($myField);
		
		$actionDetail->dispose();
		
		$i = 1;
		foreach($array_results as $key => $vals) {
			$actionDetail = new wi400Detail($azione.'_DET_'.$i, True);
			$actionDetail->setTitle("Risultato #".$i);
			
			$myField = new wi400Text("FILE");
			$myField->setLabel("File");
			$myField->setValue($key);
			$actionDetail->addField($myField);
			
			$myField = new wi400Text("NUM_RES");
			$myField->setLabel("N° di risultati");
			$myField->setValue($vals['NUM_RES']);
			$actionDetail->addField($myField);
			
			$myField = new wi400Text("RESULTS");
			$myField->setLabel("Risultati");
			$myField->setValue($vals['RESULTS']);
			$actionDetail->addField($myField);
			
			$actionDetail->dispose();
			
			$i++;
		}
	}