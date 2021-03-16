<?php

	if($actionContext->getForm()=="DEFAULT") {
		$actionDetail = new wi400Detail($azione."_".$actionContext->getForm()."_DET");
		
		$email = "";
		$mpx = "";
		
		$to_array = array();
		$cc_array = array();
		$bcc_array = array();
		
		if(isset($_POST['ID'])) {
			// se i dati del form sono stati cambiati e si viene reindirizzati alla pagina stessa (in caso di errore)
			// senza aver ancora salvato le modifiche, vengono ricaricate le modifiche e non i dati originali,
			// in modo che non si debbano rieseguire tutte le modifiche ma solo correggere i campi errati
			// (questo vale sia per la nuova chiamata, che per la modifica
			$actionDetail->setSource($_POST);
	
			if(isset($_POST['INVIO_EMAIL']) && $_POST['INVIO_EMAIL']=="S")
				$email = $_POST['INVIO_EMAIL'];
				
			if(isset($_POST['INVIO_MPX']) && $_POST['INVIO_MPX']=="S")
				$mpx = $_POST['INVIO_MPX'];
			
//			echo "POST TO:"; var_dump($_POST['TO']); echo "<br>";
			if($_POST['TO']!="")
				$to_array = $_POST['TO'];
			
//			echo "POST CC:"; var_dump($_POST['CC']); echo "<br>";
			if($_POST['CC']!="")
				$cc_array = $_POST['CC'];
			
//			echo "POST BCC:"; var_dump($_POST['BCC']); echo "<br>";
			if($_POST['BCC']!="")
				$bcc_array = $_POST['BCC'];
			
			if($_POST['BODY']!="")
				$body = $_POST['BODY'];
			
			if($_POST['SUBJECT']!="")
				$subject = $_POST['SUBJECT'];
		}
		else {
			// caricamento dei dati della chiamata recuperati dal subfile
			$actionDetail->setSource($row_email);
	
			$email = $row_email['MAIEMA'];
			$mpx = $row_email['MAIMPX'];
			
			$subject = $row_email['MAISBJ'];
		}
		
		$actionDetail->setSaveDetail(true);
		
		// ID
		$myField = new wi400InputText('ID');
		$myField->setLabel('ID');
		$myField->addValidation('required');
		$myField->setReadonly(true);
		$myField->setValue($ID);
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$actionDetail->addField($myField);
/*		
		// Utente
		$myField = new wi400InputText('MAIUSR');
		$myField->setLabel("Utente");
		$myField->addValidation('required');
		$myField->setSize(60);
		$myField->setMaxLength(60);
		$myField->setCase("UPPER");
		$myField->setInfo(_t('USER_CODE_INFO'));
		
		$decodeParameters = array(
			'TYPE' => 'common',
			'TABLE_NAME' => $settings['lib_architect']."/JPROFADF",
			'COLUMN' => 'DSPRAD',
			'KEY_FIELD_NAME' => 'NMPRAD',
//			'FILTER_SQL' => "DSPRAD not like 'SIRI - %'",
			'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE",$settings['lib_architect']."/JPROFADF");
		$myLookUp->addParameter("CAMPO","NMPRAD");
		$myLookUp->addParameter("DESCRIZIONE","DSPRAD");
//		$myLookUp->addParameter("LU_WHERE","DSPRAD not like 'SIRI - %'");
		$myField->setLookUp($myLookUp);

//		$myField->setValue($_SESSION['user']);
//		$myField->setReadonly(true);
				
		$actionDetail->addField($myField);
*/		
		// Mittente
		$myField = new wi400InputText('MAIFRM');
		$myField->setLabel("Mittente");
		$myField->addValidation('email');
		$myField->addValidation('required');
		$myField->setMaxLength(64);
		$myField->setSize(64);
/*		
		$myLookUp = new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE",$settings['lib_architect']."/JPROFADF");
		$myLookUp->addParameter("LU_FROM"," left join $users_table on USER_NAME=NMPRAD");
		$myLookUp->addParameter("CAMPO","EMAIL");
		$myLookUp->addParameter("DESCRIZIONE","USER_NAME");
//		$myLookUp->addParameter("LU_WHERE","DSPRAD like 'SIRI - %'");
		$myLookUp->addParameter("LU_ORDER","USER_NAME ASC");
		
		$myLookUp->addParameter("LU_SELECT","DSPRAD");
		$myLookUp->addParameter("LU_AS_TITLES","Nome utente");
		
		$myField->setLookUp($myLookUp);
*/		
		$myField->setReadonly(true);		
		$actionDetail->addField($myField);
/*		
		// Alias Mittente
		$myField = new wi400InputText('MAIALI');
		$myField->setLabel("Alias mittente");
//		$myField->addValidation('required');
		$myField->setMaxLength(50);
		$myField->setSize(50);
		$actionDetail->addField($myField);
*/	
		$myLookUp = new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE",$users_table);
		$myLookUp->addParameter("CAMPO","EMAIL");
		$myLookUp->addParameter("DESCRIZIONE","USER_NAME");
		$myLookUp->addParameter("LU_ORDER","USER_NAME ASC");
		
		// TO
		$myField = new wi400InputText('TO');
		$myField->setLabel(_t("A"));
		$myField->addValidation('required');
		$myField->setShowMultiple(true);
		$myField->setMaxLength(100);
		$myField->setSize(50);
		$myField->setInfo(_t("DIGITARE_DESTINATARIO"));
		$myField->setValue($to_array);
		$myField->setLookUp($myLookUp);
		$actionDetail->addField($myField);
		
		// CC
		$myField = new wi400InputText('CC');
		$myField->setLabel(_t("CC"));
		$myField->setShowMultiple(true);
		$myField->setMaxLength(100);
		$myField->setSize(50);
		$myField->setInfo(_t("DIGITARE_DESTINATARIO"));
		$myField->setValue($cc_array);
		$myField->setLookUp($myLookUp);
		$actionDetail->addField($myField);
		
		// BCC
		$myField = new wi400InputText('BCC');
		$myField->setLabel(_t("BCC"));
		$myField->setShowMultiple(true);
		$myField->setMaxLength(100);
		$myField->setSize(50);
		$myField->setInfo(_t("DIGITARE_DESTINATARIO"));
		$myField->setValue($bcc_array);
		$myField->setLookUp($myLookUp);
		$actionDetail->addField($myField);
		
		// Subject
		$myField = new wi400InputText('MAISBJ');
		$myField->setLabel("Oggetto");
		$myField->addValidation('required');
		$myField->setMaxLength(60);
		$myField->setSize(60);
		$myField->setValue($subject);
		$actionDetail->addField($myField);
		
		// Allegati
		$myField = new wi400Text('ALLEGATI');
		$myField->setLabel("Allegati");
		$myField->setValue(implode("<br>", $atc_array));
		$actionDetail->addField($myField);
		
		if(isset($settings['mail_add_attachments']) && $settings['mail_add_attachments']===true) {
			$myField = new wi400InputFile("IMPORT_FILE");
			$myField->setLabel("Aggiungi Allegato");
			$myField->setOnChange("doSubmit('".$_REQUEST['t']."', '".$_REQUEST['f']."')");
			$actionDetail->addField($myField);
		}
		
		if(isset($atc_path_array) && !empty($atc_path_array)) {
			$i = 0;
			foreach($atc_path_array as $file_path) {
				$actionDetail->addParameter("ALLEGATI_PATH_".$i, $file_path);
				$i++;
			}
		}
		
		// Testo
		if(isset($file_body)) {
			$myField = new wi400Text('FILE_BODY');
			$myField->setLabel("File testo");
			$myField->setValue($file_body);
			$actionDetail->addField($myField);
		}
		
		$myField = new wi400InputTextArea('BODY');
		$myField->setLabel(_t("TESTO"));
		$myField->setSize(75);
		$myField->setRows(5);
		$myField->setInfo(_t("DIGITARE_TESTO"));
		$myField->setValue($body);
		if(isset($file_body))
			$myField->setReadonly(true);
		$actionDetail->addField($myField);
		
		// Salva
		$myButton = new wi400InputButton('INOLTRO_BUTTON');
		$myButton->setLabel("Inoltra");
		$myButton->setAction($azione);
		$myButton->setForm("INOLTRO");
		$myButton->setConfirmMessage("Inoltrare?");
		$myButton->setValidation(true);
		$actionDetail->addButton($myButton);
		
		// Annulla
		$myButton = new wi400InputButton('BACK_BUTTON');
		$myButton->setLabel("Annulla");
		$myButton->setAction("MPX_CONV_INVIO");
		$myButton->setForm("DEFAULT");
		$actionDetail->addButton($myButton);
		
		$actionDetail->dispose();
	}