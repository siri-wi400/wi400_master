<?php

	if($actionContext->getForm()=="CHECK_PIN_SEL") {
		$azioniDetail = new wi400Detail($azione."_CHECK_PIN_SRC");
		$azioniDetail->setTitle("Controllo Autorizzazione");
		
		// PIN Sicurezza
//		$myField = new wi400InputText("CHECK_PIN");
		$myField = new wi400InputText(uniqid('CHECK_PIN_'));
		$myField->setLabel("Pin Sicurezza");
		$myField->addValidation('required');
		$myField->setMaxLength(4);
		$myField->setSize(4);
		$myField->setInfo("Inserire il PIN di sicurezza");
		$myField->setMask("0123456789");
		$myField->setType('PASSWORD');
		$azioniDetail->addField($myField);
		
		$myButton = new wi400InputButton('SEARCH_BUTTON');
		$myButton->setLabel("Seleziona");
		$myButton->setAction($azione);
		$myButton->setForm("DEFAULT");
		$myButton->setValidation(true);
		$azioniDetail->addButton($myButton);
		
		$azioniDetail->dispose();
	}