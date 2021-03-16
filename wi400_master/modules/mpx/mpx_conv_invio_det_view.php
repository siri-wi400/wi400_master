<?php 

/*
 * MVC (Model-View-Controller) - File di view
 * Visualizza i dati contenuti nel model e si occupa dell'interazione con utenti e agenti
 */

	if($actionContext->getForm() == "DEFAULT") {
		/* Istanzio la classe wi400Detail che gestisce forms (titolo-campo) */
		$mpxDetail = new wi400Detail("MPX_CONV_INVIO_DET");
		$mpxDetail->setSource($mpxArray);
	
		/* Creazione di un campo testo (preceduto dal nome del campo) */
		$txtDetail = new wi400Text("ID","ID");
		$mpxDetail->addField($txtDetail);

		$txtDetail = new wi400Text("MAIUSR","MAIUSR");
		$mpxDetail->addField($txtDetail);
	
		$txtDetail = new wi400Text("MAIJOB","MAIJOB");
		$mpxDetail->addField($txtDetail);
	
		$txtDetail = new wi400Text("MAINBR","MAINBR");
		$mpxDetail->addField($txtDetail);
	
		$txtDetail = new wi400Text("MAIEMA","MAIEMA");
		$mpxDetail->addField($txtDetail);
	
		$txtDetail = new wi400Text("MAIMPX","MAIMPX");
		$mpxDetail->addField($txtDetail);
	
		$txtDetail = new wi400Text("MAIFRM","MAIFRM");
		$mpxDetail->addField($txtDetail);
	
		$txtDetail = new wi400Text("MAIALI","MAIALI");
		$mpxDetail->addField($txtDetail);
	
		$txtDetail = new wi400Text("MAISBJ","MAISBJ");
		$mpxDetail->addField($txtDetail);
	
		$txtDetail = new wi400Text("MAISTA","MAISTA");
		$mpxDetail->addField($txtDetail);
	
		$txtDetail = new wi400Text("MAIAMB","MAIAMB");
		$mpxDetail->addField($txtDetail);
	
		$txtDetail = new wi400Text("MAIWDW","MAIWDW");
		$mpxDetail->addField($txtDetail);
	
		$txtDetail = new wi400Text("MAILIB","MAILIB");
		$mpxDetail->addField($txtDetail);
	
		$txtDetail = new wi400Text("MAIRIS","MAIRIS");
		$mpxDetail->addField($txtDetail);
	
		$txtDetail = new wi400Text("MAIERR","MAIERR");
		$mpxDetail->addField($txtDetail);
	
		$txtDetail = new wi400Text("MAIDER","MAIDER");
		$mpxDetail->addField($txtDetail);
		
		$txtDetail = new wi400Text("MAIINS","MAIINS");
		$mpxDetail->addField($txtDetail);
		
		$txtDetail = new wi400Text("MAIELA","MAIELA");
		$mpxDetail->addField($txtDetail);
	
		/* Istanzio la classe wi400InputButton che gestisce i bottoni */
		$buttonDetail = new wi400InputButton("CANCEL_BUTTON");
		/* Impostazione della scritta che deve apparire su di un bottone */
		$buttonDetail->setLabel("Torna alla lista");
		/* Impostazione dell'azione associata ad un bottone */
		$buttonDetail->setAction("MPX_CONV_INVIO");
		/* Impostazione del form dell'azione associata al bottone */
		$buttonDetail->setForm("");
		/* Creazione di un bottone */
		$mpxDetail->addButton($buttonDetail);
		
		$buttonDetail = new wi400InputButton("UPDATE_BUTTON");
		$buttonDetail->setLabel("Modifica");
		$buttonDetail->setAction("MPX_CONV_INVIO_DET");
		$buttonDetail->setForm("UPDATE");
		$mpxDetail->addButton($buttonDetail);
	}
	else if($actionContext->getForm() == "UPDATE" || $actionContext->getForm() == "INSERT") {
		/* Istanzio la classe wi400Detail che gestisce forms (titolo-campo) */
		$mpxDetail = new wi400Detail("MPX_CONV_INVIO_DET");
		
		if($actionContext->getForm() == "UPDATE")
			$mpxDetail->setSource($mpxArray);

		$mpxDetail->isEditable(true);
		if(isset($row)){
			$mpxDetail->setSource($row);
		}
		
		if($actionContext->getForm() == "UPDATE") {
			$myField = new wi400InputText('ID');
			$myField->setLabel("ID");
			$myField->setReadonly(true);
			$mpxDetail->addField($myField);
		}
		else if($actionContext->getForm() == "INSERT") {
			$myField = new wi400InputText('ID');
			$myField->setLabel("ID");
			$myField->setInfo("Inserire il codice ID");		
			$myField->addValidation("required");
			$myField->setMaxLength(10);
			$myField->setSize(10);
			if(isset($ID) && !empty($ID)) {
				$myField->setValue($ID);
				$myField->setReadonly(true);
			}
			$mpxDetail->addField($myField);
		}
		
		$myField = new wi400InputText('MAIUSR');
		$myField->setLabel("MAIUSR");
		$myField->setMaxLength(10);
		$myField->setSize(10);
		$mpxDetail->addField($myField);
		
		$myField = new wi400InputText('MAIJOB');
		$myField->setLabel("MAIJOB");
		$myField->setMaxLength(10);
		$myField->setSize(10);
		$mpxDetail->addField($myField);
		
		$myField = new wi400InputText('MAINBR');
		$myField->setLabel("MAINBR");
		$myField->setMaxLength(6);
		$myField->setSize(6);
		$mpxDetail->addField($myField);
		
		$mySelect = new wi400InputSelect('MAIEMA');
		$mySelect->setLabel("MAIEMA");
		$mySelect->setFirstLabel("Seleziona ..");
		$mySelect->addOption("SI","S");
		$mySelect->addOption("NO","N");
		$mpxDetail->addField($mySelect);
		
		$mySelect = new wi400InputSelect('MAIMPX');
		$mySelect->setLabel("MAIMPX");
		$mySelect->setFirstLabel("Seleziona ..");
		$mySelect->addOption("SI","S");
		$mySelect->addOption("NO","N");
		$mpxDetail->addField($mySelect);
		
		$myField = new wi400InputText('MAIFRM');
		$myField->setLabel("MAIFRM");
		$myField->setMaxLength(64);
		$myField->setSize(64);	
		$mpxDetail->addField($myField);
		
		$myField = new wi400InputText('MAIALI');
		$myField->setLabel("MAIALI");
		$myField->setMaxLength(50);
		$myField->setSize(50);	
		$mpxDetail->addField($myField);
		
		$myField = new wi400InputText('MAISBJ');
		$myField->setLabel("MAISBJ");
		$myField->setMaxLength(50);
		$myField->setSize(50);
		$mpxDetail->addField($myField);
		
		$myField = new wi400InputText('MAISTA');
		$myField->setLabel("MAISTA");
		$myField->setMaxLength(1);
		$myField->setSize(1);
		$mpxDetail->addField($myField);
		
		$myField = new wi400InputText('MAIAMB');
		$myField->setLabel("MAIAMB");
		$myField->setMaxLength(1);
		$myField->setSize(1);
		$mpxDetail->addField($myField);
		
		$myField = new wi400InputText('MAIWDW');
		$myField->setLabel("MAIWDW");
		$myField->setMaxLength(1);
		$myField->setSize(1);
		$mpxDetail->addField($myField);
		
		$myField = new wi400InputText('MAILIB');
		$myField->setLabel("MAILIB");
		$myField->setMaxLength(64);
		$myField->setSize(64);
		$mpxDetail->addField($myField);
		
		$myField = new wi400InputText('MAIRIS');
		$myField->setLabel("MAIRIS");
		$myField->setMaxLength(4);
		$myField->setSize(4);
		$mpxDetail->addField($myField);
		
		$myField = new wi400InputText('MAIERR');
		$myField->setLabel("MAIERR");
		$myField->setMaxLength(3);
		$myField->setSize(3);
		$mpxDetail->addField($myField);
		
		$myField = new wi400InputText('MAIDER');
		$myField->setLabel("MAIDER");
		$myField->setMaxLength(40);
		$myField->setSize(40);
		$mpxDetail->addField($myField);
		
		$myField = new wi400InputText('MAIINS');
		$myField->setLabel("MAIINS");
		$myField->setMaxLength(26);
		$myField->setSize(26);
		$mpxDetail->addField($myField);
		
		$myField = new wi400InputText('MAIELA');
		$myField->setLabel("MAIELA");
		$myField->setMaxLength(26);
		$myField->setSize(26);
		$mpxDetail->addField($myField);

		if($actionContext->getForm() == "UPDATE") {
			$buttonDetail = new wi400InputButton("CANCEL_BUTTON");
			$buttonDetail->setLabel("Annulla");
			$buttonDetail->setAction("MPX_CONV_INVIO_DET");
			$buttonDetail->setForm("");
			$mpxDetail->addButton($buttonDetail);
		
			$buttonDetail = new wi400InputButton("SAVE_BUTTON");
			$buttonDetail->setLabel("Salva");
			$buttonDetail->setAction("MPX_CONV_INVIO_DET");
			$buttonDetail->setForm("SAVE");
			$mpxDetail->addButton($buttonDetail);		
				
			$buttonDetail = new wi400InputButton("DELETE_BUTTON");
			$buttonDetail->setLabel("Elimina");
			$buttonDetail->setAction("MPX_CONV_INVIO");
			$buttonDetail->setForm("DELETE");
			$buttonDetail->setValidation(False);
			$buttonDetail->setConfirmMessage("Eliminare il record e tutti quelli ad esso associati?");
			$mpxDetail->addButton($buttonDetail);
		}
		else if($actionContext->getForm() == "INSERT"){
			$buttonDetail = new wi400InputButton("CANCEL_BUTTON");
			$buttonDetail->setLabel("Annulla");
			$buttonDetail->setAction("MPX_CONV_INVIO");
			$buttonDetail->setForm("");
			$mpxDetail->addButton($buttonDetail);
			
			$buttonDetail = new wi400InputButton("INSERT_BUTTON");
			$buttonDetail->setLabel("Inserisci");
			$buttonDetail->setAction("MPX_CONV_INVIO_DET");
			$buttonDetail->setForm("SAVE_INS");
			$buttonDetail->setValidation(False);
			$buttonDetail->setConfirmMessage("Inserire?");
			$mpxDetail->addButton($buttonDetail);
		}
	}
	
	/* Visualizzazione della view generata */
	$mpxDetail->dispose();

?>