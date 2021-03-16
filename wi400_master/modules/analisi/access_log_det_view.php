<?php

	if($actionContext->getForm() == "DEFAULT") {
		$searchAction = new wi400Detail($azione."_PAR", false);
		$searchAction->setSaveDetail(true);
		//$searchAction->setTitle("Prova");
		$searchAction->setColsNum(2);
		
		$myField = new wi400InputText('ZEUTE');
		$myField->setLabel(_t('USER_CODE'));
		//$myField->addValidation('required');
		$myField->setMaxLength(20);
		$myField->setCase("UPPER");
		$myField->setInfo(_t('USER_CODE_INFO'));
		$decodeParameters = array(
				'TYPE'=> 'common',
				'COLUMN' => 'LAST_NAME',
				'TABLE_NAME' => 'SIR_USERS',
				'KEY_FIELD_NAME' => 'USER_NAME',
				'AJAX' => true,
				'COMPLETE' => true,
				'COMPLETE_MIN' => 2,
				'COMPLETE_MAX_RESULT' => 15
		);
		$myField->setDecode($decodeParameters);
		$myLookUp =new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE",$users_table);
		$myLookUp->addParameter("CAMPO","USER_NAME");
		$myLookUp->addParameter("DESCRIZIONE","EMAIL");
		$myLookUp->addParameter("LU_SELECT", "FIRST_NAME|LAST_NAME");
		$myLookUp->addParameter("LU_AS_TITLES", "Nome|Cognome");
		$myField->setLookUp($myLookUp);
		//$myField->setAutoFocus(True);
		$searchAction->addField($myField);
		
		$myField = new wi400Text('VUOTO');
		$searchAction->addField($myField);
		
		// Gestione delle azioni
		$myField = new wi400InputText('ZEAZI');
		$myField->setLabel("Azione");
		$myField->setCase("UPPER");
		$myField->setMaxLength(40);
		$myField->setInfo(_t("ACTION_CODE_INFO"));
		
		$decodeParameters = array(
				'TYPE'=> 'common',
				'COLUMN' => 'DESCRIZIONE',
				'TABLE_NAME' => 'FAZISIRI',
				'KEY_FIELD_NAME' => 'AZIONE',
				'AJAX' => true,
				'COMPLETE' => true,
				'COMPLETE_MIN' => 2,
				'COMPLETE_MAX_RESULT' => 15
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_AZIONI");
		$myField->setLookUp($myLookUp);
		$searchAction->addField($myField);
		
		$myField = new wi400Text('VUOTO');
		$searchAction->addField($myField);
		
		// Data ricezione iniziale
		$myField = new wi400InputText('DATA_INI');
		$myField->addValidation('date');
		$myField->setLabel("Data iniziale");
		$searchAction->addField($myField);
		
		//Ora ricezione iniziale
		$myField = new wi400InputText('ORA_INI');
		$myField->setLabel("Ora iniziale");
		$myField->addValidation('time');
		$searchAction->addField($myField);
		
		// Data ricezione finale
		$myField = new wi400InputText('DATA_FIN');
		$myField->addValidation('date');
		$myField->setLabel("Data finale");
		$searchAction->addField($myField);
		
		//Ora ricezione finale
		$myField = new wi400InputText('ORA_FIN');
		$myField->setLabel("Ora finale");
		$myField->addValidation('time');
		$searchAction->addField($myField);
		
		$myField = new wi400InputText('ZEIP');
		$myField->setLabel("Indirizzo ip");
		$myField->setMask("0123456789.");
		$myField->setMaxLength(15);
		$myField->setSize(15);
		$searchAction->addField($myField);
		
		$myField = new wi400Text('VUOTO');
		$searchAction->addField($myField);
		
		$myField = new wi400InputText('ZESES');
		$myField->setLabel("Sessione");
		$searchAction->addField($myField);
		
		// Seleziona
		$myButton = new wi400InputButton('SEARCH_BUTTON');
		$myButton->setLabel("Seleziona");
		$myButton->setAction($azione);
		$myButton->setForm("LIST");
		$myButton->setValidation(true);
		$searchAction->addButton($myButton);
		
		$searchAction->dispose();
	}else if ($actionContext->getForm() == "LIST") {
		$detail = new wi400Detail($azione."_INFO");
		
		if(count($fields)) {
			foreach($fields as $chiave => $valore) {
				$myField = new wi400Text($chiave."_INFO", $chiave, $valore);
				$detail->addField($myField);
			}
		}else {
			$myField = new wi400Text("ALL_INFO", "Parametri", "nessun parametro");
			$detail->addField($myField);
		}
		
		$detail->dispose();
		
		echo "<br/>";
		
		$miaLista = new wi400List($azione."_LIST", true);
		
		$miaLista->setFrom("ZSLOGEXT");
		if(count($where)) {
			$miaLista->setWhere(implode(" AND ", $where));
		}
		$miaLista->setOrder("ZETIM DESC");
		
		$req_column = new wi400Column("ZEREQ", "File request");
		$req_column->setActionListId("READ_REQUEST");
		
		$miaLista->setCols(array(
			new wi400Column("ZEUTE", "Utente"),
			new wi400Column("ZEIP", "Ip"),
			new wi400Column("ZESES", "ID sessione"),
			new wi400Column("ZEAZI", "Azione"),
			new wi400Column("ZEFRM", "Form"),
			new wi400Column("ZEGTM", "Gateway"),
			new wi400Column("ZEJOB", "Job"),
			new wi400Column("ZEUSR", "Usr"),
			new wi400Column("ZENBR", "Nbr"),
			new wi400Column("ZETIM", "Timestamp", "TIMESTAMP"),
			$req_column,
			new wi400Column("ZEURL", "Dati liberi")
		));
		
		// Aggiunta filtri avanzati
		$mioFiltro = new wi400Filter("ZEUTE", "Utente");
		$myLookUp =new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE",$users_table);
		$myLookUp->addParameter("CAMPO","USER_NAME");
		$myLookUp->addParameter("DESCRIZIONE","EMAIL");
		$myLookUp->addParameter("LU_SELECT", "FIRST_NAME|LAST_NAME");
		$myLookUp->addParameter("LU_AS_TITLES", "Nome|Cognome");
		$mioFiltro->setLookUp($myLookUp);
		$miaLista->addFilter($mioFiltro);
		
		$mioFiltro = new wi400Filter("ZEAZI", "Azione");
		$myLookUp = new wi400LookUp("LU_AZIONI");
		$mioFiltro->setLookUp($myLookUp);
		$miaLista->addFilter($mioFiltro);
		
		$mioFiltro = new wi400Filter("ZEFRM", "Form");
		$miaLista->addFilter($mioFiltro);
		
		$mioFiltro = new wi400Filter("ZEIP", "Ip");
		$miaLista->addFilter($mioFiltro);
		
		$mioFiltro = new wi400Filter("ZESES", "ID sessione");
		$mioFiltro->setCaseSensitive("LOWER");
		$miaLista->addFilter($mioFiltro);
		
		$action = new wi400ListAction($azione, "READ_REQUEST_FILE");
		$action->setId("READ_REQUEST");
		$action->setLabel("Open request");
		$action->setTarget("WINDOW");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		$miaLista->addKey("ZEREQ");
		
		$miaLista->dispose();
	}else if ($actionContext->getForm() == "READ_REQUEST_FILE") {
		$detail = new wi400Detail($azione."_REQUEST");
		
		$myField = new wi400Text("ID_REQUEST", "File request", $key['ZEREQ']);
		$detail->addField($myField);
		
		$fileOutput = wi400File::getCommonFile("REQUEST", $key['ZEREQ']);
		$dati = unserialize(file_get_contents($fileOutput));
		
		$detail->dispose();
		
		showArray($dati);
	}
	
	
	
	
	