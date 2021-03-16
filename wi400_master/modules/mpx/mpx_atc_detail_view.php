<?php 

/*
 * MVC (Model-View-Controller) - File di view
 * Visualizza i dati contenuti nel model e si occupa dell'interazione con utenti e agenti
 */

	if($actionContext->getForm() == "DEFAULT") {
		/* Istanzio la classe wi400Detail che gestisce forms (titolo-campo) */
		$mpxDetail = new wi400Detail("MPX_ATC_DETAIL");
		$mpxDetail->setSource($mpxArray);
	
		/* Creazione di un campo testo (preceduto dal nome del campo) */
		$txtDetail = new wi400Text("ID","ID");
		$mpxDetail->addField($txtDetail);

		$txtDetail = new wi400Text("MAIATC","MAIATC");
		$mpxDetail->addField($txtDetail);
	
		$txtDetail = new wi400Text("MAIPAT","MAIPAT");
		$mpxDetail->addField($txtDetail);
	
		$txtDetail = new wi400Text("CONV","CONV");
		$mpxDetail->addField($txtDetail);
	
		$txtDetail = new wi400Text("TPCONV","TPCONV");
		$mpxDetail->addField($txtDetail);
	
		$txtDetail = new wi400Text("MAIMOD","MAIMOD");
		$mpxDetail->addField($txtDetail);
	
		$txtDetail = new wi400Text("MAIARG","MAIARG");
		$mpxDetail->addField($txtDetail);
	
		$txtDetail = new wi400Text("MAINAM","MAINAM");
		$mpxDetail->addField($txtDetail);
	
		$txtDetail = new wi400Text("FILZIP","FILZIP");
		$mpxDetail->addField($txtDetail);	
	
		/* Istanzio la classe wi400InputButton che gestisce i bottoni */
		$buttonDetail = new wi400InputButton("INDIETRO");
		$buttonDetail->setLabel("Torna alla lista");
		$buttonDetail->setAction("MPX_ATC_LIST");
		$mpxDetail->addButton($buttonDetail);
		
		$buttonDetail = new wi400InputButton("UPDATE_BUTTON");
		$buttonDetail->setLabel("Modifica");
		$buttonDetail->setAction("MPX_ATC_DETAIL");
		$buttonDetail->setForm("UPDATE");
		$mpxDetail->addButton($buttonDetail);
	}
	else if($actionContext->getForm() == "UPDATE" || $actionContext->getForm() == "INSERT") {
		/* Istanzio la classe wi400Detail che gestisce forms (titolo-campo) */
		$mpxDetail = new wi400Detail("MPX_ATC_DETAIL");
		
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
		
		$myField = new wi400InputText('MAIATC');
		$myField->setLabel("MAIATC");
		$myField->setInfo("Inserire il path dell'allegato");		
		$myField->addValidation("required");
		$myField->setMaxLength(100);
		$myField->setSize(100);
		$mpxDetail->addField($myField);
		
		$myField = new wi400InputText('MAIPAT');
		$myField->setLabel("MAIPAT");
		$myField->setMaxLength(100);
		$myField->setSize(100);		
		$mpxDetail->addField($myField);
		
		$mySelect = new wi400InputSelect('CONV');
		$mySelect->setLabel("CONV");
		$mySelect->setFirstLabel("Seleziona ..");
		$mySelect->addOption("SI","S");
		$mySelect->addOption("NO","N");
		$mpxDetail->addField($mySelect);
		
		$myField = new wi400InputText('TPCONV');
		$myField->setLabel("TPCONV");
		$myField->setMaxLength(10);
		$myField->setSize(10);
		$mpxDetail->addField($myField);
		
		$myField = new wi400InputText('MAIMOD');
		$myField->setLabel("MAIMOD");
		$myField->setMaxLength(10);
		$myField->setSize(10);
		$mpxDetail->addField($myField);
		
		$myField = new wi400InputText('MAIARG');
		$myField->setLabel("MAIARG");
		$myField->setMaxLength(10);
		$myField->setSize(10);
		$mpxDetail->addField($myField);
		
		$myField = new wi400InputText('MAINAM');
		$myField->setLabel("MAINAM");
		$myField->setMaxLength(100);
		$myField->setSize(100);
		$mpxDetail->addField($myField);
		
		$mySelect = new wi400InputSelect('FILZIP');
		$mySelect->setLabel("FILZIP");
		$mySelect->setFirstLabel("Seleziona ..");
		$mySelect->addOption("SI","S");
		$mySelect->addOption("NO","N");
		$mpxDetail->addField($mySelect);

		if($actionContext->getForm() == "UPDATE") {
			$buttonDetail = new wi400InputButton("CANCEL_BUTTON");
			$buttonDetail->setLabel("Annulla");
			$buttonDetail->setAction("MPX_ATC_DETAIL");
			$buttonDetail->setForm("");
			$mpxDetail->addButton($buttonDetail);
		
			$buttonDetail = new wi400InputButton("SAVE_BUTTON");
			$buttonDetail->setLabel("Salva");
			$buttonDetail->setAction("MPX_ATC_DETAIL");
			$buttonDetail->setForm("SAVE");
			$mpxDetail->addButton($buttonDetail);		
				
			$buttonDetail = new wi400InputButton("DELETE_BUTTON");
			$buttonDetail->setLabel("Elimina");
			$buttonDetail->setAction("MPX_ATC_LIST");
//			$buttonDetail->setAction("MPX_ATC_DETAIL");
			$buttonDetail->setForm("DELETE");
			$buttonDetail->setValidation(False);
			$buttonDetail->setConfirmMessage("Eliminare il record?");
			$mpxDetail->addButton($buttonDetail);
		}
		else if($actionContext->getForm() == "INSERT"){
			$buttonDetail = new wi400InputButton("CANCEL_BUTTON");
			$buttonDetail->setLabel("Annulla");
			$buttonDetail->setAction("MPX_ATC_LIST");
			$buttonDetail->setForm("");
			$mpxDetail->addButton($buttonDetail);
			
			$buttonDetail = new wi400InputButton("INSERT_BUTTON");
			$buttonDetail->setLabel("Inserisci");
			$buttonDetail->setAction("MPX_ATC_DETAIL");
			$buttonDetail->setForm("SAVE_INS");
			$buttonDetail->setValidation(False);
			$buttonDetail->setConfirmMessage("Inserire?");
			$mpxDetail->addButton($buttonDetail);
		}
	}
	
	/* Visualizzazione della view generata */
	$mpxDetail->dispose();

?>