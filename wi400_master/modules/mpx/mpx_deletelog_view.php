<?php

	if($actionContext->getForm()=="DEFAULT") {
		$deleteAction = new wi400Detail('delete_log');
		$deleteAction->setTitle('Parametri');
		$deleteAction->isEditable(true);
		
		// Data di riferimento
		$myField = new wi400InputText('DATA_RIF');
		$myField->addValidation('required');
		$myField->setValue(dateModelToView($_SESSION['data_validita']));
		$myField->addValidation('date');
		$myField->setLabel("Entro la data:");
		$deleteAction->addField($myField);
		
		$myButton = new wi400InputButton('DELETE_BUTTON');
		$myButton->setLabel("Elimina");
		$myButton->setAction("MPX_DELLOG");
		$myButton->setForm("DELETE");
		$myButton->setValidation(true);
		$myButton->setConfirmMessage("Eliminare tutti i log e i dati a loro associati?");
		$deleteAction->addButton($myButton);		
		
		$deleteAction->dispose();
	}

?>