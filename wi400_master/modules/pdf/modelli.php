<?php 

	if($actionContext->getForm()=="DEFAULT") {
		$searchAction = new wi400Detail($azione.'_SRC');
		$searchAction->setTitle('Ricerca Modello');
		$searchAction->isEditable(true);
		
		$myField = new wi400InputText('codmod');
		$myField->setLabel("Codice");
		$myField->addValidation("required");
		$myField->setValue($modello);
		$myField->setCase("UPPER");
		$myField->setInfo('Inserire il codice modello da inserire/manutenere');
		
		$myLookUp =new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE", "SIR_MODULI");
		$myLookUp->addParameter("CAMPO", "MODNAM");
		$myLookUp->addParameter("DESCRIZIONE", "MODDES");
		$myLookUp->addField("codmod");
		$myField->setLookUp($myLookUp);
		
		$searchAction->addField($myField);
		
		$myButton = new wi400InputButton('SEARCH_BUTTON');
		$myButton->setLabel("Seleziona");
		$myButton->setAction($azione);
		$myButton->setForm("DETAIL");
		$searchAction->addButton($myButton);
		
		$myButton = new wi400InputButton('SEARCH_BUTTON');
		$myButton->setLabel("Copia");
		$myButton->setAction($azione);
		$myButton->setForm("COPY");
		$searchAction->addButton($myButton);
		
		$searchAction->dispose();
	}
	else if(in_array($actionContext->getForm(),array("DETAIL","COPY"))) {
		$modelDetail = new wi400Detail($azione."_DETAIL", True);
//		$modelDetail->setColsNum(2);
		
		$modelDetail->addTab("scheda_1", "Dati generali");
	    $modelDetail->addTab("scheda_2", "Archiviazione e rotture");
	    $scheda = "scheda_1";
	    
	    if(isset($_POST['MODDES']))
	    	$modelDetail->setSource($_POST);
		else if(isset($row['MODNAM']))
			$modelDetail->setSource($row);
		
		if($actionContext->getForm()=="DETAIL") {
			$myField = new wi400InputText('codmod');
			$myField->setLabel("Codice Modello");
			$myField->setValue($modello);
			if(isset($row['MODNAM']))
				$myField->setReadonly(true);
			$modelDetail->addField($myField, $scheda);
		}
		else {
			$myField = new wi400InputText('codmod');
			$myField->setLabel("Codice Modello");
			
			$myLookUp =new wi400LookUp("LU_GENERICO");
			$myLookUp->addParameter("FILE", "SIR_MODULI");
			$myLookUp->addParameter("CAMPO", "MODNAM");
			$myLookUp->addParameter("DESCRIZIONE", "MODDES");
			$myLookUp->addField("codmod");
			$myField->setLookUp($myLookUp);
			
			$modelDetail->addField($myField, $scheda);
		}
		
		$myField = new wi400InputText('MODDES');
		$myField->setLabel("Descrizione");
		$myField->setFromArray($resultArray);	
	    $myField->addValidation("required");
		$modelDetail->addField($myField, $scheda);
		
		$myField = new wi400InputText('MODCIN');
		$myField->setLabel("Colonna Iniziale");
		$myField->setInfo("Inserire la colonna iniziale di stampa. Vuoto o Zero per calcolo automatico");	
		$myField->setFromArray($resultArray);	
		$myField->addValidation("required");
		$modelDetail->addField($myField, $scheda);
		
		$myField = new wi400InputText('MODRIN');
		$myField->setLabel("Riga Iniziale");
		$myField->setInfo("Inserire la riga iniziale di stampa. Vuoto o Zero per calcolo automatico");
		$myField->setFromArray($resultArray);	
		$myField->addValidation("required");
		$modelDetail->addField($myField, $scheda);
		
		$myField = new wi400InputText('MODPDP');
		$myField->setLabel("Path di output del PDF");
		$myField->setFromArray($resultArray);	
		$myField->addValidation("required");
		$myField->setInfo("Path del prefincato. esempio /out/pdf/fatture");	
		$modelDetail->addField($myField, $scheda);
		
		$mySelect = new wi400InputText('MODPDN');
		$mySelect->setLabel("Nome del PDF da generare");
		$myField->setFromArray($resultArray);	
		$myField->addValidation("required");
		$modelDetail->addField($mySelect, $scheda);
		
		$mySelect = new wi400InputSelect('MODZIP');
		$mySelect->setLabel("Compressione File");
		$mySelect->setFirstLabel("Seleziona ..");
		$mySelect->addOption("SI","S");
		$mySelect->addOption("NO","N");
		$mySelect->addValidation("required");
		$modelDetail->addField($mySelect, $scheda);
		
		$myField = new wi400InputText('MODPPA');
		$myField->setLabel("Path del prefincato");
		$myField->setFromArray($resultArray);	
		$myField->setInfo("Path del prefincato. esempio /prefincati/pdf/fatture");		
		$modelDetail->addField($myField, $scheda);
		
		$myField = new wi400InputText('MODPNA');
		$myField->setLabel("Nome del prefincato");
		$myField->setFromArray($resultArray);	
		$modelDetail->addField($myField, $scheda);
		
		$mySelect = new wi400InputSelect('MODFNA');
		$mySelect->setLabel("Nome del font");
		$mySelect->setFirstLabel("Seleziona un font");
		$mySelect->addOption("*DEFAULT", "*DEFAULT");
		$mySelect->addOption("Courier","courier");
		$mySelect->addOption("Helvetica","helvetica");
		$mySelect->addOption("Times new Roman","times");
		$mySelect->addOption("Symbol","symbol");
		$mySelect->addOption("Ean 8","E8_50");
		$mySelect->addValidation("required");
		$modelDetail->addField($mySelect, $scheda);
		
		$myField = new wi400InputText('MODFAL');
		$myField->setLabel("Altezza font da utilizzate");
		$myField->setInfo("Inserire l'altezza del font. Vuoto o Zero per calcolo automatico");
		$myField->setFromArray($resultArray);	
		$modelDetail->addField($myField, $scheda);
			
		$myField = new wi400InputText('MODFAC');
		$myField->setInfo("Inserire l'altezza del carattere. Vuoto o Zero per calcolo automatico");
		$myField->setFromArray($resultArray);	
		$myField->setLabel("Altezza Carattere");
		$modelDetail->addField($myField, $scheda);
				
		$myField = new wi400InputText('MODIAL');
		$myField->setLabel("Altezza interlinea");
		$myField->setInfo("Inserire l'altezza interlinea. Vuoto o Zero per calcolo automatico");	
		$myField->setFromArray($resultArray);	
		$modelDetail->addField($myField, $scheda);
			
		$mySelect = new wi400InputSelect('MODPPL');
		$mySelect->setLabel("Orientamento pagina");
		$mySelect->setFirstLabel("Seleziona un formato");
		$mySelect->addOption("Portrait","P");
		$mySelect->addOption("Lanscape","L");
		$mySelect->addOption("Automatico","A");
		$mySelect->addValidation("required");
		$modelDetail->addField($mySelect, $scheda);
		
		$myField = new wi400InputText('MODPAL');
		$myField->setLabel("Altezza foglio");
		$myField->setInfo("Inserire l'altezza del foglio. Vuoto o Zero per calcolo automatico o reperimento da formato pagina");	
		$myField->setFromArray($resultArray);	
		$modelDetail->addField($myField, $scheda);
		
		$myField = new wi400InputText('MODPLA');
		$myField->setLabel("Larghezza foglio");
		$myField->setInfo("Inserire la larghezza del foglio. Vuoto o Zero per calcolo automatico o reperimento da formato pagina");
		$myField->setFromArray($resultArray);	
		$modelDetail->addField($myField, $scheda);
					
		$mySelect = new wi400InputSelect('MODPFO');
		$mySelect->setLabel("Formato pagina");
		$mySelect->setFirstLabel("Seleziona un formato");
		$mySelect->addOption("Formato A5","A5");
		$mySelect->addOption("Formato A4","A4");
		$mySelect->addOption("Formato A3","A3");
		$mySelect->addOption("Lettera","Letter");
		$mySelect->addOption("Legale","Legal");
		$mySelect->addValidation("required");
		$modelDetail->addField($mySelect, $scheda);
	
		$mySelect = new wi400InputSelect('MODUMI');
		$mySelect->setLabel("Unità di misura");
		$mySelect->setFirstLabel("Seleziona un dato");
		$mySelect->addOption("Millimetri","mm");
		$mySelect->addOption("Centimetri","cm");
		$mySelect->addOption("Punti","pt");
		$mySelect->addOption("Pollici","in");
		$mySelect->addValidation("required");
		$modelDetail->addField($mySelect, $scheda);
	
		$mySelect = new wi400InputSelect('MODCLS');
		$mySelect->setLabel("Classe di conversione da utlizzare");
		$mySelect->setFirstLabel("Seleziona un dato");
		$mySelect->addOption("*DEFAULT", "*DEFAULT");
		createPersMenu($mySelect);
		$mySelect->addValidation("required");
		$modelDetail->addField($mySelect, $scheda);
		
		$myField = new wi400InputText('MODCPY');
		$myField->setLabel("Numero copie");
		$myField->setInfo("Inserire il numero di copie.");	
		$myField->setFromArray($resultArray);
		$myField->addValidation('integer');
		$myField->addValidation("required");	
		$modelDetail->addField($myField, $scheda);
		
		$myField = new wi400InputText('MODPDA');
		$myField->setLabel("Da pagina");
		$myField->setInfo("Inserire il numero di pagina da cui iniziare.");	
		$myField->setFromArray($resultArray);
		$myField->addValidation('integer');
		$myField->addValidation("required");
		$modelDetail->addField($myField, $scheda);
		
		$myField = new wi400InputText('MODPA');
		$myField->setLabel("A pagina");
		$myField->setInfo("Inserire il numero di pagina a cui arrivare.");	
		$myField->setFromArray($resultArray);
		$myField->addValidation('integer');
		$myField->addValidation("required");
		$modelDetail->addField($myField, $scheda);
		
		$mySelect = new wi400InputSelect('MODABP');
		$mySelect->setLabel("Abilitazione PDF");
		$mySelect->setFirstLabel("Seleziona ..");
		$mySelect->addOption("SI","S");
		$mySelect->addOption("NO","N");
		$mySelect->addValidation("required");
		
		$modelDetail->addField($mySelect, $scheda);
		
		$mySelect = new wi400InputSelect('MODABE');
		$mySelect->setLabel("Abilitazione e-mail");
		$mySelect->setFirstLabel("Seleziona ..");
		$mySelect->addOption("SI","S");
		$mySelect->addOption("NO","N");
		$mySelect->addValidation("required");
		$modelDetail->addField($mySelect, $scheda);
		$scheda = "scheda_2";
		$mySelect = new wi400InputSelect('MODABA');
		$mySelect->setLabel("Abilitazione archiviazione");
		$mySelect->setFirstLabel("Seleziona ..");
		$mySelect->addOption("SI","S");
		$mySelect->addOption("NO","N");
		$mySelect->addValidation("required");
		$modelDetail->addField($mySelect, $scheda);
		
		$myField = new wi400InputText('MODRUA');
		$myField->setLabel("Regole di archiviazione");
		$myField->setFromArray($resultArray);
		$myField->setInfo("Inserire le regole di archiviazione da utilizzare: 1) Usare il PDF generato senza copiarlo");	
		$modelDetail->addField($myField, $scheda);
		
		for($i=1; $i<=$settings['modelli_pdf_keys']; $i++) {
			$myField = new wi400InputText('MODKY'.$i);
			$myField->setLabel("Pos. chiave archiviazione $i");
			$myField->setFromArray($resultArray);
			$myField->setInfo("Inserire la posizione della chiave di archiviazione RIGA;COLONNA;LUNGHEZZA");
			$myField->setSize(20);
			$myField->setMaxLength(20);
			$modelDetail->addField($myField, $scheda);
			
			$myField = new wi400InputText('MODKA'.$i);
			$myField->setLabel("Alias chiave archiviazione $i");
			$myField->setFromArray($resultArray);
			$myField->setInfo("Inserire l'alias della chiave di archiviazione");
			$myField->setSize(20);
			$myField->setMaxLength(20);
			$modelDetail->addField($myField, $scheda);
		}

		if($actionContext->getForm()=="COPY" || ($actionContext->getForm()=="DETAIL" && !isset($row['MODNAM']))) {
			$myButton = new wi400InputButton('SAVE_BUTTON');
			$myButton->setLabel("Salva");
			$myButton->setAction($azione);
			$myButton->setForm("INSERT");
			$myButton->setValidation(true);
			$modelDetail->addButton($myButton);
		}
		else {
			$myButton = new wi400InputButton('SAVE_BUTTON');
			$myButton->setLabel("Aggiorna Dati");
			$myButton->setAction($azione);
			$myButton->setForm("UPDATE");
			$myButton->setValidation(true);
			$modelDetail->addButton($myButton);
			
			$myButton = new wi400InputButton('DELETE_BUTTON');
			$myButton->setLabel("Elimina");
			$myButton->setAction($azione);
			$myButton->setForm("DELETE");
			$myButton->setValidation(False);
			$myButton->setConfirmMessage("Cancellare questo metodo?");
			$modelDetail->addButton($myButton);
		}
		
		$myButton = new wi400InputButton('CANCEL_BUTTON');
		$myButton->setLabel("Annulla");
		$myButton->setAction($azione);
		$myButton->setForm("DEFAULT");
		$modelDetail->addButton($myButton);
		
		$modelDetail->dispose();
	}
	
	// Scansione della directory routine/classi/pers e creazione delle opzioni basate sui file personalizzati presenti
	function createPersMenu($mySelect) {
	    global $base_path, $settings;
	
	    $path = $base_path."/package/".$settings['package'].'/persconv';
	    $dir = opendir("$path");
	    
	    $modelli = array();
	    
	    while($file = readdir($dir)) {
	    	if(is_file("$path/$file") && strncmp($file,"wi400SpoolCvt_",14)==0) {
	        	$fileName = basename($file, ".cls.php"); 
	        	$model = substr($fileName,14);
				$modelli[] = $model;
	        }
	    }
	    
	    sort($modelli);
	    
	    foreach($modelli as $model) {
	    	$mySelect->addOption($model, $model);
	    }
	}

?>