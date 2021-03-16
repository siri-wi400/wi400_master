<?php
if($actionContext->getForm()=="DEFAULT") {
		//Richiesta Parametri Codice Sessione
		$searchAction = new wi400Detail("NAVIGATE_OBJECT_DETAIL", False);
		$searchAction->setTitle('Parametri');
		$searchAction->isEditable(true);
		$myField = new wi400InputText('FILE');
		$myField->setLabel("File Oggetto");
		$myField->addValidation('required');
		$myField->setMaxLength(200);
		$myField->setSize(200);
		$myField->setInfo("Inserire l'oggetto serializzato da navigare");
		$searchAction->addField($myField);
		
		$myField = new wi400InputText('TIPO');
		$myField->setLabel("Tipo Oggetto");
		$myField->addValidation('required');
		$myField->setMaxLength(10);
		$myField->setSize(10);
		$myField->setInfo("Inserire il tipo oggetto");
		$searchAction->addField($myField);
		
		$myButton = new wi400InputButton('SEARCH_BUTTON');
		$myButton->setLabel("Seleziona");
		$myButton->setAction($azione);
		$myButton->setForm("DETAIL");
		$myButton->setValidation(true);
		$searchAction->addButton($myButton);
		
		$searchAction->dispose();
} else if ($actionContext->getForm()=="DETAIL") {
	//$dati_reperiti = ob_get_clean();
	//ob_start();
	$sort=False;
	if ($tipo=='SESSION') {
		$sort=True;
	}
	if ($tipo=='Array') {
		$sort=True;
	}
    $html = getHTMLobject($file, $tipo, "1", $sort);
    echo $html;
	die();
	}
	?>

