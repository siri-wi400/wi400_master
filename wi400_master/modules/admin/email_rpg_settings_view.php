<?php

	if($actionContext->getForm()=="DEFAULT") {
		// Dettaglio job di schedulazione
		$actionDetail = new wi400Detail("EMAIL_RPG_SETTINGS_DETAIL",true);
/*		
		$mySelect = new wi400InputSelect("AMBI");
		$mySelect->setLabel("Ambiente");
		$mySelect->addValidation("required");
		$mySelect->setInfo("Selezionare l'ambiente");
		$mySelect->addOption("Produzione", "P");
		$mySelect->addOption("Test", "T");
		$mySelect->setValue($ambiente);
		$actionDetail->addField($mySelect);
*/
		$myField = new wi400InputText("AMBI");
		$myField->setLabel("Ambiente");
		$myField->addValidation("required");
		$myField->setValue(trim($ambiente));
		$myField->setSize(1);
		$myField->setInfo("Digitare l'ambiente di generazione e-mail");
		$myField->setMaxLength(1);
		$actionDetail->addField($myField);
		
		$myField = new wi400InputText("DFTTO");
		$myField->setLabel("A");
		$myField->setValue(trim($to));
		$myField->addValidation("email");
		$myField->setInfo("Specificare il destinatario di default se non specificato in fase di generazione della e-mail");		
		$myField->setSize(64);
		$myField->setMaxLength(64);
		$actionDetail->addField($myField);
		
		$myField = new wi400InputText("DFTFRM");
		$myField->setLabel("Da");
		$myField->setValue(trim($from));
		$myField->addValidation("email");
		$myField->setInfo("Specificare il mittente di default se non specificato in fase di generazione della e-mail");		
		$myField->setSize(64);
		$myField->setMaxLength(64);
		$actionDetail->addField($myField);
		
		$myField = new wi400InputText("SUBJECT");
		$myField->setLabel("Oggetto");
		$myField->setValue(trim($subject));
		$myField->setInfo("Specificare l'oggetto di default se non specificato in fase di generazione della e-mail");		
		$myField->setSize(64);
		$myField->setMaxLength(64);
		$actionDetail->addField($myField);
		
		$myField = new wi400InputText("COUNTRY");
		$myField->setLabel("Nazione");
		$myField->setValue(trim($country));
		$myField->setInfo("Specificare la nazione di generazione della mail.");		
		$myField->setSize(3);
		$myField->setMaxLength(3);
		$actionDetail->addField($myField);
		
		$myField = new wi400InputSwitch("EXIT");
		$myField->setLabel("Uscita senza spedizione");
		$myField->setChecked($exit=="E");
		$myField->setInfo("Specificare se nell'ambiente in fase di configurazione le e-mail devono essere generate ma non spedite");		
		$myField->setOnLabel("SI");
		$myField->setOffLabel("NO");		
		$actionDetail->addField($myField);
		
		$myField = new wi400InputText("PATH_EMAIL");
		$myField->setLabel("Path e-mail");
		$myField->addValidation("required");
		$myField->setValue(trim($path_email));
		$myField->setInfo("Specificare path iniziale dove verrano memorizzate le e-mail");		
		$myField->setSize(64);
		$myField->setMaxLength(64);
		$actionDetail->addField($myField);
		
		$mySelect = new wi400InputSelect("INVOKE_METHOD");
		$mySelect->setLabel("Tipo metodo di invio");
		$mySelect->setInfo("Selezionare il tipo di metodo di invio");
		$mySelect->setFirstLabel("Seleziona ..");
		$mySelect->addOption("Web Services", "W");
		$mySelect->setValue($invoke_method);
		$actionDetail->addField($mySelect);
		
		$myField = new wi400InputSwitch("INVOKE_BATCH");
		$myField->setLabel("Invio in modalità batch");
		$myField->setInfo("Specificare se per l'ambiente in fase di configurazione tutte le e-mail verrano inviate in modalità batch");		
		$myField->setOnLabel("SI");
		$myField->setOffLabel("NO");
		$myField->setChecked($invoke_batch=="S");
		$actionDetail->addField($myField);
		
		$myField = new wi400InputText("WI400_URL");
		$myField->setLabel("Wi400 Url");
//		$myField->addValidation("required");
		$myField->setValue(trim($wi400_url));
		$myField->setInfo("Specificare l'ambiente WI400 da utilizzate per la spedizione");		
		$myField->setSize(64);
		$myField->setMaxLength(64);
		$actionDetail->addField($myField);
		
		$myField = new wi400InputSwitch("MULTIASP");
		$myField->setLabel("MULTIASP");
		$myField->setOnLabel("SI");
		$myField->setInfo("Specificare se si sta lavorando in un ambiente multiasp");		
		$myField->setOffLabel("NO");
		$myField->setChecked($multiasp=="S");
		$actionDetail->addField($myField);
		
		$myField = new wi400InputSwitch("FINDMOD");
		$myField->setLabel("Ricerca Modulo");
		$myField->setOnLabel("SI");
		$myField->setInfo("Specificare se viene effettuata la ricerca automatica del modulo");
		$myField->setOffLabel("NO");
		$myField->setChecked($findmod=="S");
		$actionDetail->addField($myField);
				
		$myField = new wi400InputText("DEFAULT_BODY");
		$myField->setLabel("Body di Default");
		$myField->setValue(trim($default_body));
		$myField->setInfo("Specificare il body di default se non specificato in fase di generazione della e-mail");
		$myField->setSize(64);
		$myField->setMaxLength(64);
		$actionDetail->addField($myField);

		$myField = new wi400InputSwitch("MULTISYS");
		$myField->setLabel("Multi sistema");
		$myField->setOnLabel("SI");
		$myField->setInfo("Specificare se si sta lavorando in un ambiente multi sistema informativo");
		$myField->setOffLabel("NO");
		$myField->setChecked($multisys=="S");
		$actionDetail->addField($myField);

		$myField = new wi400InputText("FTP_USER");
		$myField->setLabel("Utente FTP");
		$myField->setValue(trim($ftp_user));
		$myField->setInfo("Specificare l'utente FTP per la stampa del PDF");
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$actionDetail->addField($myField);

		$myField = new wi400InputText("FTP_PASS");
		$myField->setLabel("Password FTP");
		$myField->setType("PASSWORD");
		$myField->setValue(trim($ftp_pass));
		$myField->setInfo("Specificare la password FTP per la stampa del PDF");
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$actionDetail->addField($myField);		
		
		$myField = new wi400InputText("JOBQ");
		$myField->setLabel("JOBQ per Sottomissione Batch");
		$myField->setValue(trim($jobq));
		$myField->setInfo("Specificare la JOBQ per l'esecuzione BATCH");
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$actionDetail->addField($myField);
		
		$myField = new wi400InputText("DEFAULT_ALIAS");
		$myField->setLabel("Alias di Default");
		$myField->setValue(trim($default_alias));
		$myField->setInfo("Specificare l'alias di default");
		$myField->setSize(50);
		$myField->setMaxLength(50);
		$actionDetail->addField($myField);
		
		$myField = new wi400InputSwitch("WRTXMLCONT");
		$myField->setLabel("Salvataggio XML innesco nel content");
		$myField->setChecked($wrtxmlcont=="S");
		$myField->setInfo("Specificare se l'XML di innesco chiamato viene salvato sui DB dei contents");
		$myField->setOnLabel("SI");
		$myField->setOffLabel("NO");
		$actionDetail->addField($myField);
		
		$myField = new wi400InputSwitch("WRTBODCONT");
		$myField->setLabel("Salvataggio BODY nel content");
		$myField->setChecked($wrtbodcont=="S");
		$myField->setInfo("Specificare se il testo del body viene salvato sui DB dei contents");
		$myField->setOnLabel("SI");
		$myField->setOffLabel("NO");
		$actionDetail->addField($myField);

		$myField = new wi400InputSwitch("WRTSPLCONT");
		$myField->setLabel("Salvataggio SPOOL File nel content");
		$myField->setChecked($wrtsplcont=="S");
		$myField->setInfo("Specificare se i dati degli spool convertiti vengono salvato sui DB dei contents");
		$myField->setOnLabel("SI");
		$myField->setOffLabel("NO");
		$actionDetail->addField($myField);		

		$myField = new wi400InputText("JOBQ_BATCH");
		$myField->setLabel("JOBQ per Sottomissione Batch");
		$myField->setValue(trim($jobq_batch));
		$myField->setInfo("Specificare la JOBQ per l'esecuzione dei lavori BATCH");
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$actionDetail->addField($myField);
		
		$myField = new wi400InputText("JOBQ_LIB_BATCH");
		$myField->setLabel("Libreria JOBQ per Sottomissione Batch");
		$myField->setValue(trim($jobq_lib_batch));
		$myField->setInfo("Specificare la Libreria JOBQ per l'esecuzione dei lavori BATCH");
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$actionDetail->addField($myField);
		
		$myField = new wi400InputText("JOBQ_MAIL");
		$myField->setLabel("JOBQ per Sottomissione Mail");
		$myField->setValue(trim($jobq_mail));
		$myField->setInfo("Specificare la JOBQ per l'esecuzione dei lavori MAIL");
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$actionDetail->addField($myField);
		
		$myField = new wi400InputText("JOBQ_LIB_MAIL");
		$myField->setLabel("Libreria JOBQ per Sottomissione Mail");
		$myField->setValue(trim($jobq_lib_mail));
		$myField->setInfo("Specificare la Libreria JOBQ per l'esecuzione dei Mail");
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$actionDetail->addField($myField);
		
		$myField = new wi400InputText("SUPER_USER");
		$myField->setLabel("Super utente per concessione autorizzazioni");
		$myField->setValue(trim($super_user));
		$myField->setInfo("Specificare il Super Utente per la concessione di autorizzazioni");
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$actionDetail->addField($myField);	

		$myField = new wi400InputText("SUPER_PWD");
		$myField->setLabel("Password Super Utente");
		$myField->setType("PASSWORD");
		$myField->setValue(trim($super_pwd));
		$myField->setInfo("Specificare la password X il Super utente");
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$actionDetail->addField($myField);
		
		$myField = new wi400InputText("PORT");
		$myField->setLabel("Porta del server");
		$myField->setValue(trim($port));
		$myField->setInfo("Specificare la porta a cui risponde l'http di WI400");
		$myField->setSize(5);
		$myField->setMaxLength(5);
		$actionDetail->addField($myField);

		$myField = new wi400InputText("DATA_PATH");
		$myField->setLabel("Data Path");
		$myField->setValue(trim($batch_data_path));
		$myField->setInfo("Path per salvataggio dati, copiare da config");
		$myField->setSize(60);
		$myField->setMaxLength(60);
		$actionDetail->addField($myField);
		
		$myField = new wi400InputText("PATH_SERVER");
		$myField->setLabel("Percorso Server");
		$myField->setValue(trim($path_server));
		$myField->setInfo("Percorso server nel formato http://<example.com>");
		$myField->setSize(60);
		$myField->setMaxLength(60);
		$actionDetail->addField($myField);
		// Salva
		$myButton = new wi400InputButton('SAVE_BUTTON');
		$myButton->setLabel("Salva");
		$myButton->setAction($azione);
		$myButton->setForm("SAVE");
		$myButton->setValidation(true);
		$actionDetail->addButton($myButton);
		$actionDetail->dispose();
	}

?>