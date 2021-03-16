<?php

	if($actionContext->getForm()=="DEFAULT") {
		$searchAction = new wi400Detail($azione.'_SRC');
		$searchAction->setTitle('Gerarchie Libere - Parametri');
		$searchAction->isEditable(true);
		
		// Locale
		$myField = new wi400InputText('PDV_SRC');
		$myField->setLabel("Pdv");
		$myField->setShowMultiple(true);
		$myField->setCase("UPPER");
		$myField->setMaxLength(4);
		$myField->setSize(4);
		$myField->setValue($pdv_src);
		
		$decodeParameters = array(
			'TYPE' => 'ente',
			'CLASSE_ENTE' => '01',
			'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_ENTI");
		$myLookUp->addParameter("CLASSE", "01");
		$myLookUp->addField("ENTE");
		$myField->setLookUp($myLookUp);
		
		$searchAction->addField($myField);
		
		// Gerarchia
		$myField = new wi400InputText('GERARCHIA_SRC');
		$myField->setLabel("Gerarchia");
//		$myField->addValidation('required');
		$myField->setShowMultiple(true);
		$myField->setCase("UPPER");
		$myField->setMaxLength(3);
		$myField->setSize(3);
		$myField->setValue($ger_src);
//		$myField->setReadonly(true);
		
		$decodeParameters = array(
			'TYPE' => 'table',
			'TABLE' => '0200',
			'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_TABELLA");
		$myLookUp->addParameter("TABELLA","0200");
		$myField->setLookUp($myLookUp);
		
		$searchAction->addField($myField);
		
		$myButton = new wi400InputButton('SEARCH_BUTTON');
		$myButton->setLabel("Seleziona");
		$myButton->setAction($azione);
		$myButton->setForm("LIST");
		$myButton->setValidation(true);
		$searchAction->addButton($myButton);
		
		// Esporta
		$myButton = new wi400InputButton('EXPORT_BUTTON');
		$myButton->setLabel("Esporta");
		$myButton->setAction($azione);
		$myButton->setForm("EXPORT");
		$myButton->setTarget("WINDOW");
		$myButton->setConfirmMessage("Esportare?");
		$myButton->setValidation(true);
		$searchAction->addButton($myButton);
		
		$searchAction->dispose();
	}
	else if($actionContext->getForm()=="LIST") {
		$ListDetail->dispose();
		
		$spacer = new wi400Spacer();
		$spacer->dispose();
		
		listDispose($miaLista);
	}
	else if($actionContext->getForm()=="EXPORT") {
		// ESPORTAZIONE
		$myButton = new wi400InputButton("CLOSE_BUTTON");
		$myButton->setScript('closeLookUp()');
		$myButton->setLabel("Chiudi");
		$buttonsBar[] = $myButton;

		downloadDetail($TypeImage, $filepath, "", _t("ESPORTAZIONE_COMPLETATA"));
	}