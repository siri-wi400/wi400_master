<?php

	$testDetail = new wi400Detail("TEST_VALIDATION", false);
	$testDetail->setTitle("Esempio di form con validazione");
	
	$testField = new wi400InputText("EMAIL");
	$testField->setLabel("Email");
	$testField->setInfo("Questo dev'essere un campo di tipo email");
	$testField->setSize(60);
	$testField->addValidation(wi400Validation::$VALIDATION_EMAIL);
	
	$testDetail->addField($testField);
	
	$testField = new wi400InputText("NOME");
	$testField->setLabel("Nome");
	$testField->setSize(20);
	$testDetail->addField($testField);
	
	$testField = new wi400InputText("COGNOME");
	$testField->setLabel("Cognome");
	$testField->addValidation(wi400Validation::$VALIDATION_REQUIRED);
	$testField->setSize(20);
	
	$testDetail->addField($testField);

	// Bottone che effettua un submit adazione TEST_FORM form SAVE effettuando validazione
	$button_1 = new wi400InputButton("SAVE");
	$button_1->setAction("ENTI");
	$button_1->setLabel("Salva");
	$button_1->setValidation(true);

	$testDetail->addButton($button_1);
		
	// Bottone che effettua un submit adazione TEST_FORM form CANCEL senza validazione
	$button_2 = new wi400InputButton("CANCEL");
	$button_2->setAction("TEST_FORM");
	$button_2->setForm("CANCEL");
	$button_2->setLabel("Annulla");
	$button_2->setValidation(false);
	
	$testDetail->addButton($button_2);
	
	$testDetail->dispose();
?>