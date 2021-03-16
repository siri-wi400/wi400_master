<?php

	if($actionContext->getForm()=="DEFAULT") {
		$miaLista = new wi400List($azione."_LIST", !$isFromHistory);
		
		$select = "a.*";
		$select .= ", ".get_query_case_cond($stato_array, "OTMSTA", "DES_STATO");
		
		$miaLista->setField($select);
		$miaLista->setFrom("SIR_OTM a");
		$miaLista->setOrder("OTMTYP, OTMUSR, OTMID");
		
		$miaLista->setSelection("SINGLE");
		
		$miaLista->setIncludeFile(rtvModuloAzione($azione), "manager_otm_functions.php");
/*		
		$cols = getColumnListFromTable("SIR_OTM");
//		echo "COLONNE:<pre>"; print_r($cols); echo "</pre>";

		$col = $cols["OTMTIM"];
		$col->setFormat("COMPLETE_TIMESTAMP");
*/		
		// Stato
		$stato_col = new wi400Column("DES_STATO", "Stato OTM");
		
		$stato_cond = array();
		$stato_cond[] = array('EVAL:$row["OTMSTA"]=="A"', "wi400_grid_red");
		$stato_cond[] = array('EVAL:$row["OTMSTA"]=="1"', "wi400_grid_green");
		$stato_cond[] = array('EVAL:1==1', "");
		
		$stato_col->setStyle($stato_cond);
		
		// Tipo
		$tipo_col = new wi400Column("OTMTYP", "Tipo Contenuto");
		
		$tipo_cond = array();
		$tipo_cond[] = array('EVAL:$row["OTMTYP"]=="STATIC"', "wi400_grid_yellow");
		$tipo_cond[] = array('EVAL:$row["OTMTYP"]=="XML"', "wi400_grid_aqua");
		$tipo_cond[] = array('EVAL:1==1', "");
		
		$tipo_col->setStyle($tipo_cond);
		
		// Dettaglio
		$col_det = new wi400Column("DETTAGLIO", "Dettaglio", "STRING", "center");
		$col_det->setActionListId("DETTAGLIO");
		$col_det->setDefaultValue("SEARCH");
		$col_det->setDecorator("ICONS");
		
		$check_time = getDb2Timestamp();
//		echo "CHECK_TIME: $check_time<br>";
		
		// Scadenza OTM
		$col_scad = new wi400Column("OTMEXP", "Scadenza OTM", "COMPLETE_TIMESTAMP");
		
		$scad_cond = array();
		$scad_cond[] = array('EVAL:!check_periodo("'.$check_time.'", $row["OTMEXP"])', "wi400_grid_red");
		$scad_cond[] = array('EVAL:1==1', "");
		
		$col_scad->setStyle($scad_cond);
		
		// White List
		$col_wl = new wi400Column("WHITE_LIST", "White<br>List", "STRING", "center");
		
		$cond_wl = array();
		$cond_wl[] = array('EVAL:$row["OTMTYP"]=="STATIC" && checkWitheList($row["OTMID"])', "YES");
		$cond_wl[] = array('EVAL:$row["OTMTYP"]=="STATIC" && !checkWitheList($row["OTMID"])', "NO");
		$cond_wl[] = array('EVAL:1==1', "");
		
		$col_wl->setDefaultValue($cond_wl);
		$col_wl->setDecorator("ICONS");
		
		$cols = array(
			$col_det,
			new wi400Column("OTMID", "ID della Password"),
			new wi400Column("OTMUSR", "Utente legato all'ID"),
			new wi400Column("OTMTIM", "Inserimento OTM", "COMPLETE_TIMESTAMP"),
			$col_scad,
//			new wi400Column("OTMSTA", "Stato OTM", "STRING", "center"),
			$stato_col,
			$tipo_col,
				$col_wl,
			new wi400Column("OTMCON", "Contenuto"),
		);
		
		$miaLista->setCols($cols);
		
		$miaLista->addKey("OTMID");
		$miaLista->addKey("OTMUSR");
		$miaLista->addKey("OTMTYP");
		
		$myFilter = new wi400Filter("OTMID","ID della Password");
		$myFilter->setFast(true);
		$myFilter->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$miaLista->addFilter($myFilter);
		
		$myFilter = new wi400Filter("OTMUSR","Utente legato all'ID");
		$myFilter->setFast(true);
		$myFilter->setCase('UPPER');
		$miaLista->addFilter($myFilter);
		
		$myFilter = new wi400Filter("OTMSTA","Stato OTM","SELECT","");
		$filterValues = array();
		foreach($stato_array as $key => $val) {
			$filterValues["OTMSTA='$key'"] = $val;
		}
//		echo "FILTERS:"; print_r($filterValues); echo "<br>";
		$myFilter->setSource($filterValues);
		$miaLista->addFilter($myFilter);
		
		$myFilter = new wi400Filter("OTMTYP","Tipo Conenuto","SELECT","");
		$filterValues = array();
		foreach($tipo_array as $key => $val) {
			$filterValues["OTMTYP='$key'"] = $val;
		}
//		echo "FILTERS:"; print_r($filterValues); echo "<br>";
		$myFilter->setSource($filterValues);
		$miaLista->addFilter($myFilter);
		
		// Dettaglio
		$action = new wi400ListAction();
		$action->setId("DETTAGLIO");
		$action->setAction($azione);
		$action->setForm("MOD_OTM");
		$action->setLabel("Dettaglio");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		// Nuova OTM
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("NEW_OTM");
		$action->setLabel("Nuova OTM");
		$action->setSelection("NONE");
		$miaLista->addAction($action);
/*		
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("DELETE_OTM");
		$action->setLabel("Elimina OTM");
		$action->setSelection("SINGLE");
		$action->setConfirmMessage("Eliminare?");
		$miaLista->addAction($action);
*/		
		// Aggiorna WHITE LIST
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("UPDT_WHITE_LIST");
		$action->setLabel("Aggiorna White List");
		$action->setSelection("NONE");
		$miaLista->addAction($action);
		
		listDispose($miaLista);
	}
	else if(in_array($actionContext->getForm(), array("MOD_OTM", "NEW_OTM"))) {
		$idDetail = $azione."_".$actionContext->getForm()."_DET";
		
		$actionDetail = new wi400Detail($idDetail, false);
		
		if(in_array($actionContext->getForm(),array("MOD_OTM"))) {
			if(!existDetail($idDetail)) {
				// caricamento dei dati della chiamata recuperati dal subfile
				$actionDetail->setSource($row);
			}
		}
		
		// ID
		$myField = new wi400InputText('OTMID');
		$myField->setLabel('ID della Password');
//		$myField->addValidation('required');
		if($actionContext->getForm()=="MOD_OTM") {
			$myField->setReadonly(true);
		}
		$myField->setInfo("L'ID deve essere LUNGO al massimo 30 caratteri. Se non si inserisce ne verrà creato uno automaticamente.");
		$myField->setSize(30);
		$myField->setMaxLength(30);
		$actionDetail->addField($myField);
		
		// Utente
		$myField = new wi400InputText('OTMUSR');
		$myField->setLabel("Utente legato all'ID");
		$myField->addValidation('required');
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$myField->setCase("UPPER");
		$myField->setInfo(_t('USER_CODE_INFO'));
		
		if(!isset($settings['mail_tab_abil']) ||
			(isset($settings['mail_tab_abil']) && in_array("JPROFADF", $settings['mail_tab_abil']))
		) {
			$decodeParameters = array(
				'TYPE' => 'common',
				'TABLE_NAME' => $settings['lib_architect']."/JPROFADF",
				'COLUMN' => 'DSPRAD',
				'KEY_FIELD_NAME' => 'NMPRAD',
//				'FILTER_SQL' => "DSPRAD not like 'SIRI - %'",
				'AJAX' => true
			);
			$myField->setDecode($decodeParameters);
				
			$myLookUp = new wi400LookUp("LU_GENERICO");
			$myLookUp->addParameter("FILE",$settings['lib_architect']."/JPROFADF");
			$myLookUp->addParameter("CAMPO","NMPRAD");
			$myLookUp->addParameter("DESCRIZIONE","DSPRAD");
//			$myLookUp->addParameter("LU_WHERE","DSPRAD not like 'SIRI - %'");
			$myField->setLookUp($myLookUp);
		}
		else {
			$decodeParameters = array(
				'TYPE'=> 'common',
				'COLUMN' => 'LAST_NAME',
				'TABLE_NAME' => 'SIR_USERS',
				'KEY_FIELD_NAME' => 'USER_NAME',
				'ALLOW_NEW' => True,
				'AJAX' => true,
				'COMPLETE' => true,
				'COMPLETE_MIN' => 2,
				'COMPLETE_MAX_RESULT' => 15
			);
			$myField->setDecode($decodeParameters);
			
			$myLookUp = new wi400LookUp("LU_GENERICO");
			$myLookUp->addParameter("FILE",$users_table);
			$myLookUp->addParameter("CAMPO","USER_NAME");
			$myLookUp->addParameter("DESCRIZIONE","EMAIL");
			$myLookUp->addParameter("LU_SELECT", "FIRST_NAME|LAST_NAME");
			$myLookUp->addParameter("LU_AS_TITLES", "Nome|Cognome");
			$myField->setLookUp($myLookUp);
//			$myField->setAutoFocus(True);
		}
		
		$actionDetail->addField($myField);
		
		// Inserimento OTM
//		if($actionContext->getForm()=="MOD_OTM") {
			$myField = new wi400InputText('OTMTIM');
			$myField->setLabel('Inserimento OTM');
			$myField->setReadonly(true);
			$myField->setSize(30);
			$myField->setMaxLength(30);
			if($actionContext->getForm()=="NEW_OTM")
				$myField->setValue($timeStamp);
			$actionDetail->addField($myField);
//		}
		
		// Scadenza OTM
		$myField = new wi400InputText('OTMEXP');
		$myField->setLabel('Scadenza OTM');
//		$myField->addValidation('required');
		$myField->setSize(30);
		$myField->setMaxLength(30);
		if($actionContext->getForm()=="NEW_OTM")
			$myField->setValue($timeStamp);
		$myField->setInfo("Timestamp Scadenza dell'OTM");
		$actionDetail->addField($myField);
		
		// Stato
		$mySelect = new wi400InputSelect('OTMSTA');
		$mySelect->setLabel("Stato OTM");
		$mySelect->addValidation('required');
		$mySelect->setFirstLabel("Seleziona...");
		$mySelect->setOptions($stato_array);
		$actionDetail->addField($mySelect);
		
		// Tipo
		if($actionContext->getForm()=="MOD_OTM" && $row['OTMTYP']=="STATIC") {
			$myField = new wi400InputText('OTMTYP');
			$myField->setLabel('Tipo OTM');
			$myField->addValidation('required');
			$myField->setSize(10);
			$myField->setMaxLength(10);
			$myField->setReadonly(true);
			$mySelect->setInfo("Le OTM con nome non automatico devono essere di tipo STATIC");
			$actionDetail->addField($myField);
		}
		else {
			$mySelect = new wi400InputSelect('OTMTYP');
			$mySelect->setLabel("Tipo OTM");
			$mySelect->addValidation('required');
			$mySelect->setFirstLabel("Seleziona...");
			$mySelect->setOptions($tipo_array);
			$mySelect->setInfo("Le OTM con nome non automatico devono essere di tipo STATIC");
			$actionDetail->addField($mySelect);
		}
		
		// Contenuto
		$myField = new wi400InputTextArea('OTMCON');
		$myField->setLabel("Contenuto");
		$myField->setSize(200);
		$myField->setRows(10);
		$actionDetail->addField($myField);
		
		// Salva
		$myButton = new wi400InputButton('SAVE_BUTTON');
		$myButton->setLabel("Salva");
		$myButton->setAction($azione);
		if($actionContext->getForm()=="MOD_OTM")
			$myButton->setForm("UPDT_OTM");
		else if($actionContext->getForm()=="NEW_OTM")
			$myButton->setForm("INS_OTM");
		$myButton->setConfirmMessage("Salvare?");
		$myButton->setValidation(true);
		$actionDetail->addButton($myButton);
		
		if($actionContext->getForm()=="MOD_OTM") {
			$myButton = new wi400InputButton('DELETE_BUTTON');
			$myButton->setLabel("Elimina");
			$myButton->setAction($azione);
			$myButton->setForm("DELETE_OTM");
			$myButton->setConfirmMessage("Eliminare?");
			$myButton->setValidation(true);
			$actionDetail->addButton($myButton);
		}
		
		$actionDetail->dispose();
	}