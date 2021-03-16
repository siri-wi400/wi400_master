<?php

	$detailAction = new wi400Detail($azione."_DET");
	$detailAction->setTitle('Parametri');
	$detailAction->isEditable(true);
	
//	echo "DEBUG: ".$_SESSION['DEBUG']."<br>";
	$myField = new wi400InputSwitch("DEBUG");
	$myField->setLabel("Debug");
	$myField->setOnLabel(_t('LABEL_YES'));
	$myField->setOffLabel(_t('LABEL_NO'));	
	$myField->setChecked($_SESSION['DEBUG']);	
	$detailAction->addField($myField);
	
//	echo "DB DEBUG: ".$_SESSION['DB_DEBUG']."<br>";
	$myField = new wi400InputSwitch("DB_DEBUG");
	$myField->setLabel("DB Debug");
	$myField->setOnLabel(_t('LABEL_YES'));
	$myField->setOffLabel(_t('LABEL_NO'));		
	$myField->setChecked($_SESSION['DB_DEBUG']);
	$detailAction->addField($myField);
	
	// Salva
	$myButton = new wi400InputButton('SAVE_BUTTON');
	$myButton->setLabel("Salva");
	$myButton->setAction($azione);
	$myButton->setForm("SAVE");
	$detailAction->addButton($myButton);
	
	$detailAction->dispose();