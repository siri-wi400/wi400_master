<?php 

/*
 * MVC (Model-View-Controller) - File di view
 * Visualizza i dati contenuti nel model e si occupa dell'interazione con utenti e agenti
 */

	if($actionContext->getForm() == "DEFAULT") {
		/* Istanzio la classe wi400Detail che gestisce forms (titolo-campo) */
		$mpxDetail = new wi400Detail("MPX_DETAIL");
		$mpxDetail->setSource($mpxArray);
	
		/* Creazione di un campo testo (preceduto dal nome del campo) */
		$txtDetail = new wi400Text("ID","ID");
		$mpxDetail->addField($txtDetail);

		$txtDetail = new wi400Text("TEST","TEST");
		$mpxDetail->addField($txtDetail);
	
		$txtDetail = new wi400Text("NUMPAG","NUMPAG");
		$mpxDetail->addField($txtDetail);
	
		$txtDetail = new wi400Text("WKPRID","WKPRID");
		$mpxDetail->addField($txtDetail);
	
		$txtDetail = new wi400Text("ADDR1","ADDR1");
		$mpxDetail->addField($txtDetail);
	
		$txtDetail = new wi400Text("ADDR2","ADDR2");
		$mpxDetail->addField($txtDetail);
	
		$txtDetail = new wi400Text("ADDR3","ADDR3");
		$mpxDetail->addField($txtDetail);
	
		$txtDetail = new wi400Text("CAP","CAP");
		$mpxDetail->addField($txtDetail);
	
		$txtDetail = new wi400Text("CITTA","CITTA'");
		$mpxDetail->addField($txtDetail);
	
		$txtDetail = new wi400Text("PROV","PROV");
		$mpxDetail->addField($txtDetail);
	
		$txtDetail = new wi400Text("NAZ","NAZ");
		$mpxDetail->addField($txtDetail);
	
		$txtDetail = new wi400Text("GLOCOD","GLOCOD");
		$mpxDetail->addField($txtDetail);
	
		$txtDetail = new wi400Text("SETID","SETID");
		$mpxDetail->addField($txtDetail);
	
		$txtDetail = new wi400Text("SETCOD","SETCOD");
		$mpxDetail->addField($txtDetail);
	
		$txtDetail = new wi400Text("PDFCOD","PDFCOD");
		$mpxDetail->addField($txtDetail);
	
		$txtDetail = new wi400Text("ENVCOD","ENVCOD");
		$mpxDetail->addField($txtDetail);
	
		/* Istanzio la classe wi400InputButton che gestisce i bottoni */
		$buttonDetail = new wi400InputButton("CANCEL_BUTTON");
		$buttonDetail->setLabel("Torna alla lista");
		if($_REQUEST['FROM']=='conv') {
			$buttonDetail->setAction("MPX_CONV_INVIO");
			$buttonDetail->setForm("");
		}
		else if($_REQUEST['FROM']=='mpx') {
			$buttonDetail->setAction("MPX_LIST");
			$buttonDetail->setForm("");
		}
		$mpxDetail->addButton($buttonDetail);
		
		$hiddenField = new wi400InputHidden("FROM");
		$hiddenField->setValue($_REQUEST['FROM']);
		$hiddenField->dispose();
		
		$buttonDetail = new wi400InputButton("UPDATE_BUTTON");
		$buttonDetail->setLabel("Modifica");
//		$buttonDetail->setAction("MPX_DETAIL&FROM=".$_REQUEST['FROM']);
		$buttonDetail->setAction("MPX_DETAIL");
		$buttonDetail->setForm("UPDATE");
		$mpxDetail->addButton($buttonDetail);
	}
	else if($actionContext->getForm() == "UPDATE" || $actionContext->getForm() == "INSERT") {
		/* Istanzio la classe wi400Detail che gestisce forms (titolo-campo) */
		$mpxDetail = new wi400Detail("MPX_DETAIL");
		
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
			if($_REQUEST['FROM']=='conv') {
				$myField->setValue($ID);
				$myField->setReadonly(true);
			}
			$mpxDetail->addField($myField);
		}
		
		$mySelect = new wi400InputSelect('TEST');
		$mySelect->setLabel("TEST");
		$mySelect->setFirstLabel("Seleziona ..");
		$mySelect->addOption("ON","1");
		$mySelect->addOption("OFF","0");
		$mySelect->addValidation("required");
		$mpxDetail->addField($mySelect);

		$myField = new wi400InputText('NUMPAG');
		$myField->setLabel("NUMPAG");
		$myField->setMaxLength(4);
		$myField->setSize(4);
		$mpxDetail->addField($myField);
		
		$myField = new wi400InputText('WKPRID');
		$myField->setLabel("WKPRID");
		$myField->setMaxLength(10);
		$myField->setSize(10);
		$mpxDetail->addField($myField);
		
		$myField = new wi400InputText('ADDR1');
		$myField->setLabel("ADDR1");
		$myField->setMaxLength(100);
		$myField->setSize(100);
		$mpxDetail->addField($myField);
		
		$myField = new wi400InputText('ADDR2');
		$myField->setLabel("ADDR2");
		$myField->setMaxLength(100);
		$myField->setSize(100);
		$mpxDetail->addField($myField);
		
		$myField = new wi400InputText('ADDR3');
		$myField->setLabel("ADDR3");
		$myField->setInfo("Inserire l'indirizzo del destinatario");		
		$myField->addValidation("required");
		$myField->setMaxLength(100);
		$myField->setSize(100);
		$mpxDetail->addField($myField);
		
		$myField = new wi400InputText('CAP');
		$myField->setLabel("CAP");
		$myField->setInfo("Inserire il CAP del destinatario");		
		$myField->addValidation("required");
		$myField->setMaxLength(5);
		$myField->setSize(5);
		$mpxDetail->addField($myField);	

		$myField = new wi400InputText('CITTA');
		$myField->setLabel("CITTA'");
		$myField->setInfo("Inserire la città del destinatario");		
		$myField->addValidation("required");
		$myField->setMaxLength(50);
		$myField->setSize(50);
		$mpxDetail->addField($myField);
		
		$myField = new wi400InputText('PROV');
		$myField->setLabel("PROV");
		$myField->setInfo("Inserire la provincia del destinatario");	
		$myField->addValidation("required");
		$myField->setMaxLength(2);
		$myField->setSize(2);
		$mpxDetail->addField($myField);
		
		$myField = new wi400InputText('NAZ');
		$myField->setLabel("NAZ");
		$myField->setMaxLength(2);
		$myField->setSize(2);
		$mpxDetail->addField($myField);
		
		$myField = new wi400InputText('GLOCOD');
		$myField->setLabel("GLOCOD");
		$myField->setMaxLength(3);
		$myField->setSize(3);
		$mpxDetail->addField($myField);
		
		$myField = new wi400InputText('SETID');
		$myField->setLabel("SETID");
		$myField->setMaxLength(50);
		$myField->setSize(50);
		$mpxDetail->addField($myField);
		
		$myField = new wi400InputText('SETCOD');
		$myField->setLabel("SETCOD");
		$myField->setMaxLength(3);
		$myField->setSize(3);	
		$mpxDetail->addField($myField);
		
		$myField = new wi400InputText('PDFCOD');
		$myField->setLabel("PDFCOD");
		$myField->setMaxLength(3);
		$myField->setSize(3);	
		$mpxDetail->addField($myField);
		
		$myField = new wi400InputText('ENVCOD');
		$myField->setLabel("ENVCOD");
		$myField->setMaxLength(3);
		$myField->setSize(3);
		$mpxDetail->addField($myField);
		
		$hiddenField = new wi400InputHidden("FROM");
		$hiddenField->setValue($_REQUEST['FROM']);
		$hiddenField->dispose();

		if($actionContext->getForm() == "UPDATE") {
			$buttonDetail = new wi400InputButton("CANCEL_BUTTON");
			$buttonDetail->setLabel("Annulla");
//			$buttonDetail->setAction("MPX_DETAIL&FROM=".$_REQUEST['FROM']);
			$buttonDetail->setAction("MPX_DETAIL");
			$buttonDetail->setForm("DEFAULT");
			$mpxDetail->addButton($buttonDetail);
		
			$buttonDetail = new wi400InputButton("SAVE_BUTTON");
			$buttonDetail->setLabel("Salva");
//			$buttonDetail->setAction("MPX_DETAIL&FROM=".$_REQUEST['FROM']);
			$buttonDetail->setAction("MPX_DETAIL");
			$buttonDetail->setForm("SAVE");
			$mpxDetail->addButton($buttonDetail);		
				
			$buttonDetail = new wi400InputButton("DELETE_BUTTON");
			$buttonDetail->setLabel("Elimina");
//			$buttonDetail->setAction("MPX_DETAIL&FROM=".$_REQUEST['FROM']);
//			$buttonDetail->setAction("MPX_LIST&FROM=".$_REQUEST['FROM']);
			$buttonDetail->setAction("MPX_LIST");
			$buttonDetail->setForm("DELETE");
			$buttonDetail->setValidation(False);
			$buttonDetail->setConfirmMessage("Eliminare il record?");
			$mpxDetail->addButton($buttonDetail);
		}
		else if($actionContext->getForm() == "INSERT"){
			$buttonDetail = new wi400InputButton("CANCEL_BUTTON");
			$buttonDetail->setLabel("Annulla");
			if($_REQUEST['FROM']=='conv') {
				$buttonDetail->setAction("MPX_CONV_INVIO");
				$buttonDetail->setForm("");
			}
			else if($_REQUEST['FROM']=='mpx') {
				$buttonDetail->setAction("MPX_LIST");
				$buttonDetail->setForm("");
			}
			$mpxDetail->addButton($buttonDetail);
			
			$buttonDetail = new wi400InputButton("INSERT_BUTTON");
			$buttonDetail->setLabel("Inserisci");
//			$buttonDetail->setAction("MPX_DETAIL&FROM=".$_REQUEST['FROM']);
			$buttonDetail->setAction("MPX_DETAIL");
			$buttonDetail->setForm("SAVE_INS");
			$buttonDetail->setValidation(False);
			$buttonDetail->setConfirmMessage("Inserire?");
			$mpxDetail->addButton($buttonDetail);
		}
	}
	
	/* Visualizzazione della view generata */
	$mpxDetail->dispose();

?>