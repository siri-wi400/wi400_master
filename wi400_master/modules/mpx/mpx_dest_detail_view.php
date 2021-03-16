<?php 

/*
 * MVC (Model-View-Controller) - File di view
 * Visualizza i dati contenuti nel model e si occupa dell'interazione con utenti e agenti
 */

	if($actionContext->getForm() == "DEFAULT") {
		/* Istanzio la classe wi400Detail che gestisce forms (titolo-campo) */
		$mpxDetail = new wi400Detail("MPX_DEST_DETAIL");
		$mpxDetail->setSource($mpxArray);
	
		/* Creazione di un campo testo (preceduto dal nome del campo) */
		$txtDetail = new wi400Text("ID","ID");
		$mpxDetail->addField($txtDetail);

		$txtDetail = new wi400Text("MAITOR","MAITOR");
		$mpxDetail->addField($txtDetail);
	
		$txtDetail = new wi400Text("MAIALI","MAIALI");
		$mpxDetail->addField($txtDetail);
	
		$txtDetail = new wi400Text("MATPTO","MATPTO");
		$mpxDetail->addField($txtDetail);
	
		/* Istanzio la classe wi400InputButton che gestisce i bottoni */
		$buttonDetail = new wi400InputButton("INDIETRO");
		$buttonDetail->setLabel("Torna alla lista");
		$buttonDetail->setAction("MPX_DEST_LIST");
		$mpxDetail->addButton($buttonDetail);
		
		$buttonDetail = new wi400InputButton("UPDATE_BUTTON");
		$buttonDetail->setLabel("Modifica");
		$buttonDetail->setAction("MPX_DEST_DETAIL");
		$buttonDetail->setForm("UPDATE");
		$mpxDetail->addButton($buttonDetail);
	}
	else if($actionContext->getForm() == "UPDATE" || $actionContext->getForm() == "INSERT") {
		/* Istanzio la classe wi400Detail che gestisce forms (titolo-campo) */
		$mpxDetail = new wi400Detail("MPX_DEST_DETAIL");
		
		if($actionContext->getForm() == "UPDATE")
			$mpxDetail->setSource($mpxArray);

		$mpxDetail->isEditable(true);
		if(isset($row)){
			$mpxDetail->setSource($row);
		}
		
		$myField = new wi400InputText('ID');
		$myField->setLabel("ID");
		if($actionContext->getForm() == "INSERT")
			$myField->setValue($ID);
		$myField->setReadonly(true);
		$mpxDetail->addField($myField);
		
		$myField = new wi400InputText('MAITOR');
		$myField->setLabel("MAITOR");
		$myField->setInfo("Inserire l'indirizzo e-mail del destinatario");		
		$myField->addValidation("required");
		$myField->setMaxLength(64);
		$myField->setSize(64);
		$mpxDetail->addField($myField);
		
		$myField = new wi400InputText('MAIALI');
		$myField->setLabel("MAIALI");
		$myField->setMaxLength(50);
		$myField->setSize(50);
		$mpxDetail->addField($myField);
		
		$myField = new wi400InputText('MATPTO');
		$myField->setLabel("MATPTO");
		$myField->setInfo("Inserire il tipo del destinatario");		
		$myField->addValidation("required");
		$myField->setMaxLength(5);
		$myField->setSize(5);
		$mpxDetail->addField($myField);

		if($actionContext->getForm() == "UPDATE") {
			$buttonDetail = new wi400InputButton("CANCEL_BUTTON");
			$buttonDetail->setLabel("Annulla");
			$buttonDetail->setAction("MPX_DEST_DETAIL");
			$buttonDetail->setForm("");
			$mpxDetail->addButton($buttonDetail);
		
			$buttonDetail = new wi400InputButton("SAVE_BUTTON");
			$buttonDetail->setLabel("Salva");
			$buttonDetail->setAction("MPX_DEST_DETAIL");
			$buttonDetail->setForm("SAVE");
			$mpxDetail->addButton($buttonDetail);		
				
			$buttonDetail = new wi400InputButton("DELETE_BUTTON");
			$buttonDetail->setLabel("Elimina");
			$buttonDetail->setAction("MPX_DEST_LIST");
//			$buttonDetail->setAction("MPX_DEST_DETAIL");
			$buttonDetail->setForm("DELETE");
			$buttonDetail->setValidation(False);
			$buttonDetail->setConfirmMessage("Eliminare il record?");
			$mpxDetail->addButton($buttonDetail);
		}
		else if($actionContext->getForm() == "INSERT"){
			$buttonDetail = new wi400InputButton("CANCEL_BUTTON");
			$buttonDetail->setLabel("Annulla");
			$buttonDetail->setAction("MPX_DEST_LIST");
			$buttonDetail->setForm("");
			$mpxDetail->addButton($buttonDetail);
			
			$buttonDetail = new wi400InputButton("INSERT_BUTTON");
			$buttonDetail->setLabel("Inserisci");
			$buttonDetail->setAction("MPX_DEST_DETAIL");
			$buttonDetail->setForm("SAVE_INS");
			$buttonDetail->setValidation(False);
			$buttonDetail->setConfirmMessage("Inserire?");
			$mpxDetail->addButton($buttonDetail);
		}
	}
	
	/* Visualizzazione della view generata */
	$mpxDetail->dispose();

?>