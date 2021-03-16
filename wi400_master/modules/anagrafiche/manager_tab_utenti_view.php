<?php
	if(in_array($actionContext->getForm(), array("SOC_LIST", "ENTI_LIST", "REPARTO_LIST"))) {
		$ListDetail = new wi400Detail($azione."_".$actionContext->getForm()."_DET");
		$ListDetail->setColsNum(1);
		
		$keyArray = getListKeyArray($azione."_UTENTI_LIST");
		
		// Utente
		$fieldDetail = new wi400Text("UTENTE");
		$fieldDetail->setLabel("Utente");
		$fieldDetail->setValue($keyArray['SEAUSR']);
		$ListDetail->addField($fieldDetail);
		
		// Societa'
		$fieldDetail = new wi400Text("SOCIETA");
		$fieldDetail->setLabel("Societ&agrave;");
		$fieldDetail->setValue($keyArray['SEASOC']." - ".getDescrizioneEnte($keyArray['SEASOC']));
		$ListDetail->addField($fieldDetail);
		
		// Pdv
		if($actionContext->getForm() == "REPARTO_LIST") {
			$fieldDetail = new wi400Text("NEGOZIO");
			$fieldDetail->setLabel("Pdv");
			$fieldDetail->setValue($key_enti['SECCDE']." - ".getDescrizioneEnte($key_enti['SECCDE']));
			$ListDetail->addField($fieldDetail);
		}
		
		$ListDetail->dispose();
		
		echo "<br/>";
	}

	if($actionContext->getForm()=="DEFAULT") {
		$miaLista = new wi400List($azione."_UTENTI_LIST", true);
		$miaLista->setSelection("SINGLE");
		
		$miaLista->setField("SEAUSR, SEASOC, digits(seaava)!!digits(seamva)!!digits(seagva) DATA_INSERIMENTO, SEASTA");
		$miaLista->setFrom("FSEAUSER");
		
		$stato_col = new wi400Column("SEASTA", "Stato");
		$stato_col->setDecorator("YES_NO_ICO");
		
		$dettaglio_art_col = new wi400Column("DETTAGLIO_ART", "Dettaglio articoli", "", "CENTER");
		$dettaglio_art_col->setDecorator("ICONS");
		$dettaglio_art_col->setDefaultValue("SEARCH");
		$dettaglio_art_col->setSortable(false);
		$dettaglio_art_col->setExportable(false);
		$dettaglio_art_col->setActionListId($azione."_DETTAGLIO_ART");
		
		$dettaglio_ent_col = new wi400Column("DETTAGLIO_ENT", "Dettaglio entit&agrave;", "", "CENTER");
		$dettaglio_ent_col->setDecorator("ICONS");
		$dettaglio_ent_col->setDefaultValue("SEARCH");
		$dettaglio_ent_col->setSortable(false);
		$dettaglio_ent_col->setExportable(false);
		$dettaglio_ent_col->setActionListId($azione."_DETTAGLIO_ENT");
		
		$modifica_col = new wi400Column("MODIFICA", "Modifica", "", "CENTER");
		$modifica_col->setDecorator("NOTE_ICONS");
		$modifica_col->setDefaultValue("1");
		$modifica_col->setActionListId("MOD_UTENTE");
		
		$miaLista->setCols(array(
				$dettaglio_art_col,
				$dettaglio_ent_col,
				$modifica_col,
				new wi400Column("SEAUSR", "Utente"),
				new wi400Column("SEASOC", "Societ&agrave;"),
				new wi400Column("DATA_INSERIMENTO", "Data inserimento", "DATE"),
				$stato_col
		));
		
		$miaLista->addKey("SEAUSR");
		$miaLista->addKey("SEASOC");
		$miaLista->addKey("SEASTA");
		
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("NEW_UTENTE");
		$action->setLabel("Nuovo");
		$action->setTarget("WINDOW");
		$action->setSelection("NONE");
		$miaLista->addAction($action);
		
		$action = new wi400ListAction();
		$action->setId("MOD_UTENTE");
		$action->setAction($azione);
		$action->setForm("MOD_UTENTE");
		$action->setLabel("Modifica");
		$action->setTarget("WINDOW");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		// Dettaglio articolo
		$action = new wi400ListAction();
		$action->setId($azione."_DETTAGLIO_ART");
		$action->setAction($azione);
		$action->setForm("SOC_LIST");
		$action->setLabel("Dettaglio articolo");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		// Dettaglio ENTI'
		$action = new wi400ListAction();
		$action->setId($azione."_DETTAGLIO_ENT");
		$action->setAction($azione);
		$action->setForm("ENTI_LIST");
		$action->setLabel("Dettaglio entit&agrave;");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		listDispose($miaLista);
	}
	else if(in_array($actionContext->getForm(), array("NEW_UTENTE", "MOD_UTENTE"))) {
		//else if($actionContext->getForm() == "NEW_REC") {
		$idDetail = $azione."_".$actionContext->getForm()."_DET";
		$actionDetail = new wi400Detail($idDetail, true);
		$readonly = false;
		$stat = true;

		if($actionContext->getForm() == "MOD_UTENTE") {
			if($keyArray['SEASTA'] == "0") {
				$stat = false;
			}
			$readonly = true;
		}
	
		// Utente
		$myField = new wi400InputText('SEAUSR');
		$myField->setLabel("Utente");
		$myField->setReadOnly($readonly);
		$myField->setValue($keyArray['SEAUSR']);
		$myField->addValidation('required');
		$myField->setCase('UPPER');
		$actionDetail->addField($myField);
	
		// Societa'
		$myField = new wi400InputText('SEASOC');
		$myField->setValue($keyArray['SEASOC']);
		$myField->setLabel("Societ&agrave;");
		$myField->addValidation('required');
		$myField->setMaxLength(4);
		$myField->setSize(4);
		$decodeParameters = array(
				'TYPE' => 'ente',
				'CLASSE_ENTE' => '09',
				'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_ENTI");
		$myLookUp->addParameter("CLASSE", "09");
		$myField->setLookUp($myLookUp);
		$actionDetail->addField($myField);

		// Stato
		$myField = new wi400InputSwitch("SEASTA");
		$myField->setLabel("Stato");
		$myField->setOnLabel(_t('VALIDO'));
		$myField->setOffLabel(_t('ANULLATO'));
		$myField->setChecked($stat);
		$myField->setValue("S");
		$actionDetail->addField($myField);
	
		// Salva
		$myButton = new wi400InputButton('SAVE_BUTTON');
		$myButton->setLabel("Salva");
		$myButton->setAction($azione);
		if($actionContext->getForm()=="MOD_UTENTE")
			$myButton->setForm("UPDT_UTENTE");
		else
			$myButton->setForm("INS_UTENTE");
		$myButton->setConfirmMessage("Salvare?");
		$myButton->setValidation(true);
		$actionDetail->addButton($myButton);
	
		// Annulla
		$myButton = new wi400InputButton('BACK_BUTTON');
		$myButton->setLabel("Annulla");
		$myButton->setAction("CLOSE");
		$myButton->setForm("CLOSE_LOOKUP");
		$myButton->addParameter("CLEAN_DETAIL", $idDetail);
		$actionDetail->addButton($myButton);
	
		$actionDetail->dispose();
	}
	else if($actionContext->getForm() == "SOC_LIST") {
		$miaLista = new wi400List($azione."_SOC_LIST", true);
		$miaLista->setSelection("SINGLE");
		
		$miaLista->setField("d.sebcda, a.mdadsa, d.sebdef, digits(d.sebava)!!digits(d.sebmva)!!digits(d.sebgva) as DATA_INS, d.sebsta");
		$miaLista->setFrom("FSEBANAG d, FMDAANAR A,
			LATERAL ( SELECT rrn(o) AS NREL
					FROM   LMDAANAR o
					WHERE  A.MDACDA = o.MDACDA and
					digits(o.MDAAVA)!!digits(o.MDAMVA)!!digits(o.MDAGVA) <=".date('Ymd')."
					FETCH FIRST ROW ONLY ) AS x");
		$miaLista->setWhere("rrn(A) = x.NREL and d.SEBCDA=MDACDA and d.SEBSOC='".$keyArray['SEASOC']."'");
		
		$stato_col = new wi400Column("SEBSTA", "Stato");
		$stato_col->setDecorator("YES_NO_ICO");
		
		$miaLista->setCols(array(
				new wi400Column("SEBCDA", "Codice articolo"),
				new wi400Column("MDADSA", "Descrizione"),
				new wi400Column("SEBDEF", "Desc. fornitore"),
				new wi400Column("DATA_INS", "Data inserimento", "DATE", "CENTER"),
				$stato_col
		));
		
		$miaLista->addKey("SEBCDA");
		
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("DELETE_SOC");
		$action->setLabel("Elimina");
		$action->setSelection("SINGLE");
		$action->setConfirmMessage("Sicuro di voler eliminare il campo?");
		$miaLista->addAction($action);
		
		listDispose($miaLista);
	}
	else if($actionContext->getForm() == "ENTI_LIST") {
		$miaLista = new wi400List($azione."_ENTI_LIST", true);
		$miaLista->setSelection("SINGLE");
		
		$miaLista->setField("secusr, seccde, digits(secava)!!digits(secmva)!!digits(secgva) as DATA_INS_ENT, secsta");
		$miaLista->setFrom("FSECENTI");
		$miaLista->setWhere("SECSOC='".$keyArray['SEASOC']."'");
		
		$stato_col = new wi400Column("SECSTA", "Stato");
		$stato_col->setDecorator("YES_NO_ICO");
		
		$modifica_col = new wi400Column("MODIFICA_ENT", "Modifica", "", "CENTER");
		$modifica_col->setDecorator("NOTE_ICONS");
		$modifica_col->setDefaultValue("1");
		$modifica_col->setActionListId("MOD_ENTI");
		
		$pdv_col = new wi400Column("SECCDE", "Codice PDV");
		$pdv_col->setActionListId($azione."_DETTAGLIO");
		
		$miaLista->setCols(array(
				$modifica_col,
				new wi400Column("SECUSR", "Codice utente"),
				$pdv_col,
				new wi400Column("DATA_INS_ENT", "Data inserimento", "DATE", "CENTER"),
				$stato_col
		));
		
		$miaLista->addKey("SECUSR");
		$miaLista->addKey("SECCDE");
		$miaLista->addKey("SECSTA");
		
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("NEW_ENTI");
		$action->setLabel("Nuovo");
		$action->setTarget("WINDOW");
		$action->setSelection("NONE");
		$miaLista->addAction($action);
		
		$action = new wi400ListAction();
		$action->setId("MOD_ENTI");
		$action->setAction($azione);
		$action->setForm("MOD_ENTI");
		$action->setLabel("Modifica");
		$action->setTarget("WINDOW");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		// Dettaglio entita'
		$action = new wi400ListAction();
		$action->setId($azione."_DETTAGLIO");
		$action->setAction($azione);
		$action->setForm("REPARTO_LIST");
		$action->setLabel("Dettaglio reparto");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		listDispose($miaLista);
	}
	else if(in_array($actionContext->getForm(), array("NEW_ENTI", "MOD_ENTI"))) {
		//showArray($chiavi);
		//showArray($key_utenti);
		
		$idDetail = $azione."_".$actionContext->getForm()."_DET";
		$actionDetail = new wi400Detail($idDetail, true);
		$readonly = true;
		$pdv_readonly = false;
		$stat = true;
	
		if($actionContext->getForm() == "MOD_ENTI") {
			if($chiavi['SECSTA'] == "0") {
				$stat = false;
			}
			$pdv_readonly = true;
		}
	
		// Utente
		$myField = new wi400InputText('SECUSR');
		$myField->setLabel("Utente");
		$myField->setReadOnly($readonly);
		$myField->setValue($key_utenti['SEAUSR']);
		$myField->addValidation('required');
		$myField->setCase('UPPER');
		$actionDetail->addField($myField);
		
		// Societa'
		$myField = new wi400InputText('SECSOC');
		$myField->setValue($key_utenti['SEASOC']);
		$myField->setLabel("Societ&agrave;");
		$myField->setReadOnly($readonly);
		$myField->addValidation('required');
		$myField->setMaxLength(4);
		$myField->setSize(4);
		$decodeParameters = array(
				'TYPE' => 'ente',
				'CLASSE_ENTE' => '09',
				'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_ENTI");
		$myLookUp->addParameter("CLASSE", "09");
		$myField->setLookUp($myLookUp);
		$actionDetail->addField($myField);
	
		// Pdv
		$myField = new wi400InputText('SECCDE');
		$myField->setLabel("Pdv");
		if(isset($chiavi['SECCDE'])) {
			$myField->setValue($chiavi['SECCDE']);
		}
		$myField->setReadonly($pdv_readonly);
		$myField->addValidation('required');
		$myField->setMaxLength(4);
		$myField->setSize(4);
		$decodeParameters = array(
				'TYPE' => 'ente',
				'CLASSE_ENTE' => '01;02',
				'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
	
		$myLookUp = new wi400LookUp("LU_ENTI");
		$myLookUp->addParameter("CLASSE", "01;02");
		$myField->setLookUp($myLookUp);
		$actionDetail->addField($myField);
	
		// Stato
		$myField = new wi400InputSwitch("SECSTA");
		$myField->setLabel("Stato");
		$myField->setOnLabel(_t('VALIDO'));
		$myField->setOffLabel(_t('ANULLATO'));
		$myField->setChecked($stat);
		$myField->setValue("S");
		$actionDetail->addField($myField);
	
		// Salva
		$myButton = new wi400InputButton('SAVE_BUTTON');
		$myButton->setLabel("Salva");
		$myButton->setAction($azione);
		if($actionContext->getForm()=="MOD_ENTI")
			$myButton->setForm("UPDT_ENTI");
		else
			$myButton->setForm("INS_ENTI");
		$myButton->setConfirmMessage("Salvare?");
		$myButton->setValidation(true);
		$actionDetail->addButton($myButton);
	
		// Annulla
		$myButton = new wi400InputButton('BACK_BUTTON');
		$myButton->setLabel("Annulla");
		$myButton->setAction("CLOSE");
		$myButton->setForm("CLOSE_LOOKUP");
		$myButton->addParameter("CLEAN_DETAIL", $idDetail);
		$actionDetail->addButton($myButton);
	
		$actionDetail->dispose();
	}
	else if($actionContext->getForm() == "REPARTO_LIST") {
//		showArray($key_utenti);
//		showArray($key_enti);
		
		$miaLista = new wi400List($azione."_REPARTO_LIST", true);
		$miaLista->setSelection("SINGLE");
		
		$miaLista->setIncludeFile("alberto_test", "manager_tab_utenti_function.php");
		
		$miaLista->setField("sedusr, sedcde, sedsoc, sedrep, digits(sedava)!!digits(sedmva)!!digits(sedgva) as DATA_INS_REP, sedsta");
		$miaLista->setFrom("FSEDREPA");
		$miaLista->setWhere("SEDSOC='{$key_utenti['SEASOC']}'");
		
		$stato_col = new wi400Column("SEDSTA", "Stato");
		$stato_col->setDecorator("YES_NO_ICO");
		
		$modifica_col = new wi400Column("MODIFICA_REP", "Modifica", "", "CENTER");
		$modifica_col->setDecorator("NOTE_ICONS");
		$modifica_col->setDefaultValue("1");
		$modifica_col->setActionListId("MOD_REP");
		
		$des_col = new wi400Column("DESC_REP", "Descrizione");
		$des_col->setDefaultValue('EVAL:get_tabella_descrizione("0153", $row["SEDREP"])');
		//$des_col->setDefaultValue('PROVA PROVA1 PROVA2 PROVA3 PROVA4 PROVA5');
		$des_col->setWidth("1000");
		
		$miaLista->setCols(array(
			$modifica_col,
			new wi400Column("SEDREP", "Reparto"),
			$des_col,
			new wi400Column("DATA_INS_REP", "Data inserimento", "DATE", "CENTER"),
			$stato_col
		));
		
		$miaLista->addKey("SEDUSR");
		$miaLista->addKey("SEDCDE");
		$miaLista->addKey("SEDREP");
		$miaLista->addKey("SEDSTA");
		
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("NEW_REPARTO");
		$action->setLabel("Nuovo");
		$action->setTarget("WINDOW");
		$action->setSelection("NONE");
		$miaLista->addAction($action);
		
		$action = new wi400ListAction();
		$action->setId("MOD_REP");
		$action->setAction($azione);
		$action->setForm("MOD_REPARTO");
		$action->setLabel("Modifica");
		$action->setTarget("WINDOW");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);

		listDispose($miaLista);
	}
	else if(in_array($actionContext->getForm(), array("NEW_REPARTO", "MOD_REPARTO"))) {
		//showArray($chiavi);
		//showArray($key_utenti);
	
		$idDetail = $azione."_".$actionContext->getForm()."_DET";
		$actionDetail = new wi400Detail($idDetail, true);
		$readonly = true;
		$stat = true;
	
		if($actionContext->getForm() == "MOD_REPARTO") {
			if($key_rep['SEDSTA'] == "0") {
				$stat = false;
			}
		}
	
		// Utente
		$myField = new wi400InputText('SEDUSR');
		$myField->setLabel("Utente");
		$myField->setReadOnly($readonly);
		$myField->setValue($key_utenti['SEAUSR']);
		$myField->addValidation('required');
		$myField->setCase('UPPER');
		$actionDetail->addField($myField);
		
		// Pdv
		$myField = new wi400InputText('SEDCDE');
		$myField->setLabel("Pdv");
		$myField->setReadOnly($readonly);
		$myField->setValue($key_enti['SECCDE']);
		$myField->addValidation('required');
		$myField->setMaxLength(4);
		$myField->setSize(4);
		$myField->setCase('UPPER');
		$decodeParameters = array(
				'TYPE' => 'ente',
				'CLASSE_ENTE' => '01;02',
				'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_ENTI");
		$myLookUp->addParameter("CLASSE", "01;02");
		$myField->setLookUp($myLookUp);
		$actionDetail->addField($myField);
	
		// Societa'
		$myField = new wi400InputText('SEDSOC');
		$myField->setValue($key_utenti['SEASOC']);
		$myField->setLabel("Societ&agrave;");
		$myField->setReadOnly($readonly);
		$myField->addValidation('required');
		$myField->setMaxLength(4);
		$myField->setSize(4);
		$decodeParameters = array(
				'TYPE' => 'ente',
				'CLASSE_ENTE' => '09',
				'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
	
		$myLookUp = new wi400LookUp("LU_ENTI");
		$myLookUp->addParameter("CLASSE", "09");
		$myField->setLookUp($myLookUp);
		$actionDetail->addField($myField);
	
		// Reparto
		$myField = new wi400InputText('SEDREP');
		$myField->setLabel("Reparto");
		if(isset($key_rep['SEDREP'])) {
			$myField->setValue($key_rep['SEDREP']);
		}
		//$myField->setReadonly($rep_readonly);
		$myField->addValidation('required');
		$myField->setMaxLength(3);
		$myField->setSize(3);
		$decodeParameters = array(
			'TYPE' => 'table',
		  	'TABLE' => '0153',
			'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
	
		$myLookUp = new wi400LookUp("LU_TABELLA");
		$myLookUp->addParameter("TABELLA","0153");
		$myField->setLookUp($myLookUp);
		$actionDetail->addField($myField);
	
		// Stato
		$myField = new wi400InputSwitch("SECSTA");
		$myField->setLabel("Stato");
		$myField->setOnLabel(_t('VALIDO'));
		$myField->setOffLabel(_t('ANULLATO'));
		$myField->setChecked($stat);
		$myField->setValue("S");
		$actionDetail->addField($myField);
	
		// Salva
		$myButton = new wi400InputButton('SAVE_BUTTON');
		$myButton->setLabel("Salva");
		$myButton->setAction($azione);
		if($actionContext->getForm()=="MOD_REPARTO")
			$myButton->setForm("UPDT_REPARTO");
		else
			$myButton->setForm("INS_REPARTO");
		$myButton->setConfirmMessage("Salvare?");
		$myButton->setValidation(true);
		$actionDetail->addButton($myButton);
	
		// Annulla
		$myButton = new wi400InputButton('BACK_BUTTON');
		$myButton->setLabel("Annulla");
		$myButton->setAction("CLOSE");
		$myButton->setForm("CLOSE_LOOKUP");
		$myButton->addParameter("CLEAN_DETAIL", $idDetail);
		$actionDetail->addButton($myButton);
	
		$actionDetail->dispose();
	}
	
	
?>