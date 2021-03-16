<?php

	$spacer = new wi400Spacer();
	
	if($actionContext->getForm()=="DEFAULT") {
		$searchAction = new wi400Detail($azione."_SRC", true);
//		$searchAction->setTitle($label);
		$searchAction->setTitle("Parametri");
		$searchAction->isEditable(true);
		$searchAction->setSaveDetail(true);
		
		// Codice Modello
		$myField = new wi400InputText('CODMOD_SRC');
		$myField->setLabel("Codice Modello");
//		$myField->addValidation("required");
		$myField->setValue($modello);
		$myField->setCase("UPPER");
		$myField->setMaxLength(20);
		$myField->setSize(20);
//		$myField->setInfo('Inserire il codice modello');

		$decodeParameters = array(
			'TYPE' => 'common',
			'TABLE_NAME' => "SIR_MODULI",
			'COLUMN' => 'MODDES',
			'KEY_FIELD_NAME' => 'MODNAM',
			'ALLOW_NEW' => True,
			'AJAX' => true,
			'COMPLETE' => true,
			'COMPLETE_MIN' => 2,
			'COMPLETE_MAX_RESULT' => 15
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE", "SIR_MODULI");
		$myLookUp->addParameter("CAMPO", "MODNAM");
		$myLookUp->addParameter("DESCRIZIONE", "MODDES");
		$myLookUp->addField("CODMOD_SRC");
		$myField->setLookUp($myLookUp);
		
		$searchAction->addField($myField);
		
		// Codice Modelli
		$myField = new wi400InputText('MODELLI_SRC');
		$myField->setLabel("Modelli");
		$myField->setShowMultiple(true);
		$myField->setValue($modelli_array);
		$myField->setCase("UPPER");
		$myField->setMaxLength(20);
		$myField->setSize(20);
//		$myField->setInfo('Inserire il codice modello');
		
		$decodeParameters = array(
	 		'TYPE' => 'common',
	 		'TABLE_NAME' => "SIR_MODULI",
	 		'COLUMN' => 'MODDES',
	 		'KEY_FIELD_NAME' => 'MODNAM',
	 		'AJAX' => true,
	 		'COMPLETE' => true,
	 		'COMPLETE_MIN' => 2,
	 		'COMPLETE_MAX_RESULT' => 15
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE", "SIR_MODULI");
		$myLookUp->addParameter("CAMPO", "MODNAM");
		$myLookUp->addParameter("DESCRIZIONE", "MODDES");
		$myLookUp->addField("MODELLI_SRC");
		$myField->setLookUp($myLookUp);
		
		$searchAction->addField($myField);
		
		// Classe di conversione
/*		
		$mySelect = new wi400InputSelect('MODCLS_SRC');
		$mySelect->setLabel("Classe di conversione");
		$mySelect->setFirstLabel("Seleziona...");
		$mySelect->setOptions($classi_conv_array);
		$mySelect->setValue($mod_cls);
		$searchAction->addField($mySelect);
*/		
		$myField = new wi400InputText('MODCLS_SRC');
		$myField->setLabel("Classi di conversione");
//		$myField->addValidation("required");
		$myField->setShowMultiple(true);
		$myField->setValue($mod_cls);
		$myField->setCase("UPPER");
		$myField->setMaxLength(100);
		$myField->setSize(20);
//		$myField->setInfo('Inserire la Classe di conversione');
/*
		$path_classi = $base_path."/package/".$settings['package'].'/persconv';
		
		$myLookUp = new wi400LookUp("LU_DIR_LIST");
		$myLookUp->addParameter("FILE_PATHS", $path_classi);
		$myLookUp->addParameter("FILE_TYPES", "php");
		$myLookUp->addParameter("FULL_PATH", false);
		$myLookUp->addParameter("SHOW_INFO", false);
		$myLookUp->addParameter("LU_SELECT", "substr(FILE, 15) as CLASSE");
		$myLookUp->addParameter("LU_CAMPO", "CLASSE");
		$myLookUp->addParameter("LU_CAMPO_LABEL", "Classe<br>Conversione");
		$myField->setLookUp($myLookUp);
*/		
		$myLookUp = new wi400LookUp("LU_MODELLI_CONV_PDF");
		$myField->setLookUp($myLookUp);
		
		$searchAction->addField($myField);
		
		// Nome Prefincato
		$myField = new wi400InputText('MODPNA_SRC');
		$myField->setLabel("Nome Prefincato");
		$myField->setSelOption(true);
		$myField->setMaxLength(60);
		$myField->setSize(60);
		$myField->setValue($pref_name);
		$searchAction->addField($myField);
/*		
		// Nome Logo
		$myField = new wi400InputText('MODLNA_SRC');
		$myField->setLabel("Nome Logo");
		$myField->setSelOption(true);
		$myField->setMaxLength(60);
		$myField->setSize(60);
		$myField->setValue($pref_name);
		$searchAction->addField($myField);
*/		
		$myButton = new wi400InputButton('SEARCH_BUTTON');
		$myButton->setLabel("Seleziona");
		$myButton->setAction($azione);
		$myButton->setForm("MODELLI_SEL");
		$myButton->setValidation(true);
		$searchAction->addButton($myButton);
/*		
		$myButton = new wi400InputButton('NEW_BUTTON');
		$myButton->setLabel("Nuovo modello");
		$myButton->setAction($azione);
		$myButton->setForm("MODELLO_NEW");
		$searchAction->addButton($myButton);
*/		
		$myButton = new wi400InputButton('COPIA_BUTTON');
		$myButton->setLabel("Copia modello");
		$myButton->setAction($azione);
		$myButton->setForm("MODELLO_COPIA");
		$myButton->setValidation(true);
		$searchAction->addButton($myButton);
		
		$searchAction->dispose();
	}
	else if($actionContext->getForm()=="LIST") {
		$ListDetail = new wi400Detail($azione."_".$actionContext->getForm()."_DET");
		$ListDetail->setColsNum(2);
		
		if($where=="") {
			$fieldDetail = new wi400Text("SELEZIONI");
			$fieldDetail->setLabel("Selezioni");
			$fieldDetail->setValue("TUTTI I MODELLI");
			$ListDetail->addField($fieldDetail);
		}
		else {
			if(isset($modelli_array) && !empty($modelli_array)) {
				$fieldDetail = new wi400Text("MODELLI_DET");
				$fieldDetail->setLabel("Modelli");
				$fieldDetail->setValue(implode("<br>", $modelli_array));
				$ListDetail->addField($fieldDetail);
			}
			
			if(isset($mod_cls) && !empty($mod_cls)) {
				$fieldDetail = new wi400Text("MODCLS_DET");
				$fieldDetail->setLabel("Classi di conversione");
//				$fieldDetail->setValue($mod_cls);
				$fieldDetail->setValue(implode("<br>", $mod_cls));
				$ListDetail->addField($fieldDetail);
			}
			
			if(in_array($pref_option, array("EMPTY", "NOT_EMPTY")) || (isset($pref_name) && $pref_name!="")) {
				$labelDetail = new wi400Text("MODPNA_SRC");
				$labelDetail->setLabel("Nome Prefincato");
				$labelDetail->setValue(get_text_condition_des($pref_option, $pref_name));
				$ListDetail->addField($labelDetail);
			}
/*			
			if(in_array($logo_option, array("EMPTY", "NOT_EMPTY")) || (isset($logo_name) && $logo_name!="")) {
				$labelDetail = new wi400Text("MODLNA_SRC");
				$labelDetail->setLabel("Nome Logo");
				$labelDetail->setValue(get_text_condition_des($logo_option, $logo_name));
				$ListDetail->addField($labelDetail);
			}
*/			
		}
		
		$ListDetail->dispose();
		
		$spacer->dispose();
		
		$miaLista = new wi400List($azione."_LIST", !$isFromHistory);
		
		$miaLista->setField($select);
		$miaLista->setFrom("SIR_MODULI a");
		$miaLista->setWhere($where);
		$miaLista->setOrder("MODNAM");
		
//		echo "SQL: ".$miaLista->getSql()."<br>";
		
		$miaLista->setSelection("SINGLE");
		
		$miaLista->setIncludeFile(rtvModuloAzione($azione), "modelli_conv_pdf_functions.php");
		
		$icon_cols = array();
		foreach($action_icons_array as $key) {
			$val = $des_icons_array[$key];
				
			$col = new wi400Column($key, $val, "STRING", "center");
			$col->setActionListId($key);				
			
			$cond = array();
			$cond[] = array('EVAL:1==1', $type_icons_array[$key]);
			
			$col->setDefaultValue($cond);
			$col->setDecorator("ICONS");
			$col->setExportable(false);
			$col->setSortable(false);
				
			$icon_cols[$key] = $col;
		}
		
		$col_zip = new wi400Column("MODZIP", "Zip", "STRING", "center");
		$col_zip->setDecorator("YES_NO_ICO");
		
		$col_abil_pdf = new wi400Column("MODABP", "Abilitazione<br>PDF", "STRING", "center");
		$col_abil_pdf->setDecorator("YES_NO_ICO");
		
		$col_abil_ema = new wi400Column("MODABE", "Abilitazione<br>E-Mail", "STRING", "center");
		$col_abil_ema->setDecorator("YES_NO_ICO");
		
		$col_abil_arc = new wi400Column("MODABA", "Abilitazione<br>Archiviazione", "STRING", "center");
		$col_abil_arc->setDecorator("YES_NO_ICO");
		
		$ex_pref_col = new wi400Column("EX_PREF", "Esistenza<br>prefincato", "STRING", "center");
		$ex_pref_col->setDefaultValue('EVAL:check_file_exists($row["MODPPA"], $row["MODPNA"])');
		$ex_pref_col->setDecorator("YES_NO_ICO");
/*		
		$ex_logo_col = new wi400Column("EX_LOGO", "Esistenza<br>logo", "STRING", "center");
		$ex_logo_col->setDefaultValue('EVAL:check_file_exists($row["MODLPA"], $row["MODLNA"])');
		$ex_logo_col->setDecorator("YES_NO_ICO");
*/		
		$cols_1 = array(
			new wi400Column("MODNAM", "Codice Modello"),
			new wi400Column("MODDES", "Descrizione Modello"),
				new wi400Column("MODCLS", "Classe di conversione"),
			new wi400Column("MODCIN", "Colonna<br>Iniziale", "DOUBLE_2", "right"),
			new wi400Column("MODRIN", "Riga<br>Iniziale", "DOUBLE_2", "right"),
			new wi400Column("MODPDP", "Path PDF output"),
			new wi400Column("MODPDN", "Nome PDF output"),
			$col_zip,
			new wi400Column("MODPPA", "Path Prefincato"),
			new wi400Column("MODPNA", "Nome Prefincato"),
			$ex_pref_col,
/*				
				new wi400Column("MODLPA", "Path Logo"),
				new wi400Column("MODLNA", "Nome Logo"),
				$ex_logo_col,
				new wi400Column("MODLOX", "Posizione<br>Logo X", "INTEGER", "right"),
				new wi400Column("MODLOY", "Posizione<br>Logo Y", "INTEGER", "right"),
				new wi400Column("MODLOW", "Larghezza<br>Logo", "INTEGER", "right"),
				new wi400Column("MODLOH", "Altezza<br>Logo", "INTEGER", "right"),
*/				
			new wi400Column("MODFNA", "Tipo<br>Font"),
			new wi400Column("MODFAL", "Altezza<br>Font", "DOUBLE_2", "right"),
			new wi400Column("MODFAC", "Altezza<br>Carattere", "DOUBLE_2", "right"),
			new wi400Column("MODIAL", "Altezza<br>Interlinea", "DOUBLE_2", "right"),
//			new wi400Column("MODPPL", "Orientamento<br>Pagina"),
			new wi400Column("DES_MODPPL", "Orientamento<br>Pagina"),
			new wi400Column("MODPAL", "Altezza<br>Pagina", "DOUBLE_2", "right"),
			new wi400Column("MODPLA", "Larghezza<br>Pagina", "DOUBLE_2", "right"),
			new wi400Column("MODPFO", "Formato<br>Pagina"),
			new wi400Column("MODUMI", "U.M."),
			new wi400Column("MODCPY", "Numero<br>Copie", "INTEGER", "right"),
			new wi400Column("MODPDA", "Da<br>Pagina", "INTEGER", "right"),
			new wi400Column("MODPA", "A<br>Pagina", "INTEGER", "right"),
			$col_abil_pdf,
			$col_abil_ema,
			$col_abil_arc
		);
		
		$cols = array_merge($icon_cols, $cols_1);
		
		$miaLista->setCols($cols);
		
		for($i=1; $i<=$settings['modelli_pdf_keys']; $i++) {
			// Chiave Archiaviazione
			$campo = "MODKY".$i;
			if($i>9)
				$campo = "MODY".$i;
			
			$col = new wi400Column($campo, "Chiave<br>Archiviazione<br>$i");
			$col->setShow(false);
			
			$miaLista->addCol($col);
			
			// Alias Chiave
			$campo = "MODKA".$i;
			if($i>9)
				$campo = "MODA".$i;
			
			$col = new wi400Column($campo, "Alias<br>Chiave<br>$i");
			$col->setShow(false);
				
			$miaLista->addCol($col);
		}
		
		// Chiavi
		$miaLista->addKey("MODNAM");
		
		// Filtri
		$myFilter = new wi400Filter("MODNAM","Codice Modello");
		$myFilter->setFast(true);
		$myFilter->setCase('UPPER');
		$miaLista->addFilter($myFilter);
		
		$myFilter = new wi400Filter("MODDES","Descrizione Modello");
		$myFilter->setFast(true);
		$myFilter->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$miaLista->addFilter($myFilter);
		
		$myFilter = new wi400Filter("MODCLS","Classe di conversione","SELECT","");
		$filterValues = array();
		foreach($classi_conv_array as $key => $val) {
			$filterValues["MODCLS=".$key] = $val;
		}
		$myFilter->setSource($filterValues);
		$miaLista->addFilter($myFilter);
		
		$myFilter = new wi400Filter("MODPNA","Nome Prefincato");
		$myFilter->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$miaLista->addFilter($myFilter);
/*		
		$myFilter = new wi400Filter("MODLNA","Nome Logo");
		$myFilter->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$miaLista->addFilter($myFilter);
*/		
		// Dettaglio
		$action = new wi400ListAction();
		$action->setId("DETTAGLIO");
		$action->setAction($azione);
		$action->setForm("MODELLO");
		$action->setLabel("Dettaglio");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		// Elimina
		$action = new wi400ListAction();
		$action->setId("ELIMINA");
		$action->setAction($azione);
		$action->setForm("ELIMINA");
		$action->setLabel("Elimina");
		$action->setSelection("SINGLE");
		$action->setConfirmMessage("Eliminare?");
		$miaLista->addAction($action);
		
		// Copia modello
		$action = new wi400ListAction();
		$action->setId("COPIA");
		$action->setAction($azione);
		$action->setForm("MODELLO_COPIA");
		$action->setLabel("Copia");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		// Nuovo modello
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("MODELLO_NEW");
		$action->setLabel("Nuovo modello");
		$action->setSelection("NONE");
		$miaLista->addAction($action);
		
		listDispose($miaLista);
	}
	else if(in_array($actionContext->getForm(), array("MODELLO", "MODELLO_NEW", "MODELLO_COPIA"))) {
		$idDetail = $azione."_".$actionContext->getForm()."_DET";
		
		$modelDetail = new wi400Detail($idDetail, false);
		
		if(in_array($actionContext->getForm(),array("MODELLO", "MODELLO_COPIA"))) {
			if(!existDetail($idDetail)) {
				// caricamento dei dati della chiamata recuperati dal subfile
				$modelDetail->setSource($row);
			}
		}
		
		if($actionContext->getForm()!="MODELLO_NEW")
			$modelDetail->setSaveDetail(true);
		
		$modelDetail->addTab("scheda_1", "Dati generali");
		$modelDetail->addTab("scheda_2", "Archiviazione e rotture");
		
		// SCHEDA 1
		
		$scheda = "scheda_1";
		
		$myField = new wi400InputText('CODMOD');
		$myField->setLabel("Codice Modello");
		$myField->addValidation("required");
		$myField->setCase("UPPER");
//		if($actionContext->getForm()=="MODELLO") {
		if($actionContext->getForm()!="MODELLO_COPIA") {
			$myField->setValue($modello);
			$myField->setReadonly(true);
		}
		$modelDetail->addField($myField, $scheda);
		
		$myField = new wi400InputText('MODDES');
		$myField->setLabel("Descrizione");
		$myField->addValidation("required");
		$myField->setFromArray($resultArray);
		$modelDetail->addField($myField, $scheda);
		
		$mySelect = new wi400InputSelect('MODCLS');
		$mySelect->setLabel("Classe di conversione da utlizzare");
		$mySelect->addValidation("required");
		$mySelect->setFirstLabel("Seleziona un dato");
		$mySelect->setOptions($classi_conv_array);
		$myField->setFromArray($resultArray);
		$modelDetail->addField($mySelect, $scheda);
		
		$myField = new wi400InputText('MODCIN');
		$myField->setLabel("Colonna Iniziale");
		$myField->setInfo("Inserire la colonna iniziale di stampa. Vuoto o Zero per calcolo automatico");
		$myField->addValidation("required");
		$myField->setFromArray($resultArray);
		$modelDetail->addField($myField, $scheda);
		
		$myField = new wi400InputText('MODRIN');
		$myField->setLabel("Riga Iniziale");
		$myField->setInfo("Inserire la riga iniziale di stampa. Vuoto o Zero per calcolo automatico");
		$myField->addValidation("required");
		$myField->setFromArray($resultArray);
		$modelDetail->addField($myField, $scheda);
		
		$myField = new wi400InputText('MODPDP');
		$myField->setLabel("Path di output del PDF");
		$myField->addValidation("required");
		$myField->setInfo("Path di output del PDF. Esempio /out/pdf/fatture");
		$myField->setFromArray($resultArray);
		$modelDetail->addField($myField, $scheda);
		
		$mySelect = new wi400InputText('MODPDN');
		$mySelect->setLabel("Nome del PDF da generare");
		$myField->addValidation("required");
		$myField->setFromArray($resultArray);
		$myField->setFromArray($resultArray);
		$modelDetail->addField($mySelect, $scheda);
		
		$mySelect = new wi400InputSelect('MODZIP');
		$mySelect->setLabel("Compressione File");
		$mySelect->setFirstLabel("Seleziona...");
		$mySelect->setOptions($si_no_array);
		$mySelect->addValidation("required");
		$modelDetail->addField($mySelect, $scheda);
		
		$myField = new wi400InputText('MODPPA');
		$myField->setLabel("Path del Prefincato");
		$myField->setInfo("Path del Prefincato. Esempio /prefincati/pdf/fatture");
		$myField->setFromArray($resultArray);
		$modelDetail->addField($myField, $scheda);
		
		$myField = new wi400InputText('MODPNA');
		$myField->setLabel("Nome del Prefincato");
		$myField->setFromArray($resultArray);
		$modelDetail->addField($myField, $scheda);
/*		
		$myField = new wi400InputText('MODLPA');
		$myField->setLabel("Path del Logo");
		$myField->setInfo("Path del Logo. Esempio /prefincati/pdf/fatture");
		$myField->setFromArray($resultArray);
		$modelDetail->addField($myField, $scheda);
		
		$myField = new wi400InputText('MODLNA');
		$myField->setLabel("Nome del Logo");
		$myField->setFromArray($resultArray);
		$modelDetail->addField($myField, $scheda);
		
		$myField = new wi400InputText('MODLOX');
		$myField->setLabel("Posizione Logo X");
		$myField->setInfo("Inserire la posizione X del logo");
		$myField->setFromArray($resultArray);
		$modelDetail->addField($myField, $scheda);
		
		$myField = new wi400InputText('MODLOY');
		$myField->setLabel("Posizione Logo Y");
		$myField->setInfo("Inserire la posizione Y del logo");
		$myField->setFromArray($resultArray);
		$modelDetail->addField($myField, $scheda);
		
		$myField = new wi400InputText('MODLOW');
		$myField->setLabel("Larghezza Logo");
		$myField->setInfo("Inserire la larghezza del logo. Vuoto o Zero per calcolo automatico");
		$myField->setFromArray($resultArray);
		$modelDetail->addField($myField, $scheda);
		
		$myField = new wi400InputText('MODLOH');
		$myField->setLabel("Altezza Logo");
		$myField->setInfo("Inserire l'altezza del logo. Vuoto o Zero per calcolo automatico");
		$myField->setFromArray($resultArray);
		$modelDetail->addField($myField, $scheda);
*/		
		$mySelect = new wi400InputSelect('MODFNA');
		$mySelect->setLabel("Nome del font");
		$mySelect->setFirstLabel("Seleziona...");
		$mySelect->setOptions($font_array);
		$mySelect->addValidation("required");
		$modelDetail->addField($mySelect, $scheda);
		
		$myField = new wi400InputText('MODFAL');
		$myField->setLabel("Altezza font da utilizzate");
		$myField->setInfo("Inserire l'altezza del font. Vuoto o Zero per calcolo automatico");
		$myField->setFromArray($resultArray);
		$modelDetail->addField($myField, $scheda);
			
		$myField = new wi400InputText('MODFAC');
		$myField->setLabel("Altezza Carattere");
		$myField->setInfo("Inserire l'altezza del carattere. Vuoto o Zero per calcolo automatico");
		$myField->setFromArray($resultArray);
		$modelDetail->addField($myField, $scheda);
		
		$myField = new wi400InputText('MODIAL');
		$myField->setLabel("Altezza interlinea");
		$myField->setInfo("Inserire l'altezza interlinea. Vuoto o Zero per calcolo automatico");
		$myField->setFromArray($resultArray);
		$modelDetail->addField($myField, $scheda);
			
		$mySelect = new wi400InputSelect('MODPPL');
		$mySelect->setLabel("Orientamento pagina");
		$mySelect->setFirstLabel("Seleziona...");
		$mySelect->setOptions($orientamento_array);
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
		$mySelect->setFirstLabel("Seleziona...");
		$mySelect->setOptions($formato_pagina_array);
		$mySelect->addValidation("required");
		$modelDetail->addField($mySelect, $scheda);
		
		$mySelect = new wi400InputSelect('MODUMI');
		$mySelect->setLabel("Unità di misura");
		$mySelect->setFirstLabel("Seleziona...");
		$mySelect->setOptions($um_array);
		$mySelect->addValidation("required");
		$modelDetail->addField($mySelect, $scheda);
		
		$myField = new wi400InputText('MODCPY');
		$myField->setLabel("Numero copie");
		$myField->setInfo("Inserire il numero di copie.");
		$myField->addValidation('integer');
		$myField->addValidation("required");
		$myField->setFromArray($resultArray);
		$modelDetail->addField($myField, $scheda);
		
		$myField = new wi400InputText('MODPDA');
		$myField->setLabel("Da pagina");
		$myField->setInfo("Inserire il numero di pagina da cui iniziare.");
		$myField->addValidation('integer');
		$myField->addValidation("required");
		$myField->setFromArray($resultArray);
		$modelDetail->addField($myField, $scheda);
		
		$myField = new wi400InputText('MODPA');
		$myField->setLabel("A pagina");
		$myField->setInfo("Inserire il numero di pagina a cui arrivare.");
		$myField->addValidation('integer');
		$myField->addValidation("required");
		$myField->setFromArray($resultArray);
		$modelDetail->addField($myField, $scheda);
		
		$mySelect = new wi400InputSelect('MODABP');
		$mySelect->setLabel("Abilitazione PDF");
		$mySelect->setFirstLabel("Seleziona...");
		$mySelect->setOptions($si_no_array);
		$mySelect->addValidation("required");
		$modelDetail->addField($mySelect, $scheda);
		
		$mySelect = new wi400InputSelect('MODABE');
		$mySelect->setLabel("Abilitazione e-mail");
		$mySelect->setFirstLabel("Seleziona...");
		$mySelect->setOptions($si_no_array);
		$mySelect->addValidation("required");
		$modelDetail->addField($mySelect, $scheda);
		
		// SCHEDA 2
		
		$scheda = "scheda_2";
		
		$mySelect = new wi400InputSelect('MODABA');
		$mySelect->setLabel("Abilitazione archiviazione");
		$mySelect->setFirstLabel("Seleziona...");
		$mySelect->setOptions($si_no_array);
		$mySelect->addValidation("required");
		$modelDetail->addField($mySelect, $scheda);
		
		$myField = new wi400InputText('MODRUA');
		$myField->setLabel("Regole di archiviazione");
		$myField->setInfo("Inserire le regole di archiviazione da utilizzare: 1) Usare il PDF generato senza copiarlo");
		$myField->setFromArray($resultArray);
		$modelDetail->addField($myField, $scheda);
		
		for($i=1; $i<=$settings['modelli_pdf_keys']; $i++) {
			$myField = new wi400InputText('MODKY'.$i);
			$myField->setLabel("Pos. chiave archiviazione $i");
			$myField->setInfo("Inserire la posizione della chiave di archiviazione RIGA;COLONNA;LUNGHEZZA");
			$myField->setSize(20);
			$myField->setMaxLength(20);
			$myField->setFromArray($resultArray);
			$modelDetail->addField($myField, $scheda);
				
			$myField = new wi400InputText('MODKA'.$i);
			$myField->setLabel("Alias chiave archiviazione $i");
			$myField->setInfo("Inserire l'alias della chiave di archiviazione");
			$myField->setSize(20);
			$myField->setMaxLength(20);
			$myField->setFromArray($resultArray);
			$modelDetail->addField($myField, $scheda);
		}
		
		// BOTTONI
		
		// Salva
		$myButton = new wi400InputButton('SAVE_BUTTON');
		$myButton->setLabel("Salva");
		$myButton->setAction($azione);
		if($actionContext->getForm()=="MODELLO")
			$myButton->setForm("UPDT_MODELLO");
		else
			$myButton->setForm("INS_MODELLO");
		$myButton->setConfirmMessage("Salvare?");
		$myButton->setValidation(true);
		$modelDetail->addButton($myButton);
		
		if($actionContext->getForm()=="MODELLO") {
			// Elimina
			$myButton = new wi400InputButton('CANCEL_BUTTON');
			$myButton->setLabel("Elimina");
			$myButton->setAction($azione);
			$myButton->setForm("ELIMINA");
			$myButton->setConfirmMessage("Eliminare?");
			$modelDetail->addButton($myButton);
		}
		
		// Annulla
		$myButton = new wi400InputButton('BACK_BUTTON');
		$myButton->setLabel("Annulla");
		$myButton->setAction($azione);
		$myButton->setForm($last_form);
		$modelDetail->addButton($myButton);
		
		$modelDetail->dispose();
	}