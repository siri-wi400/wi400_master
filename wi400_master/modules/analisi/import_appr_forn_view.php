<?php 

	$spacer = new wi400Spacer();

	if(in_array($actionContext->getForm(),array("DEFAULT","LIST"))) {
		$importAction = new wi400Detail('IMPORT_FILE_DET', True);
		$importAction->setTitle('Importazione approvvigionabilità del fornitore');
		$importAction->isEditable(true);
		
		$myField = new wi400InputFile("IMPORT_FILE");
		$myField->setLabel("Importa dati da file:");
		$myField->addValidation('required');
		$importAction->addField($myField);
		
		$myField = new wi400InputCheckbox("CLEAR_FILE");
		$myField->setLabel("Pulizia dei dati già presenti");
		$myField->setChecked(false);
		$importAction->addField($myField);
	
		$myButton = new wi400InputButton('IMPORT_BUTTON');
		$myButton->setLabel("Importa");
		$myButton->setAction($azione);
		$myButton->setForm("IMPORT");
		$myButton->setConfirmMessage("Importare?");
		$myButton->setValidation(true);
		$importAction->addButton($myButton);
		
		$myButton = new wi400InputButton('LIST_BUTTON');
		$myButton->setLabel("Visualizza lista");
		$myButton->setAction($azione);
		$myButton->setForm("LIST");
		$importAction->addButton($myButton);
		
		$importAction->dispose();
	}

	if($actionContext->getForm()=="LIST") {
		$spacer->dispose();
		
		// Inizializzazione lista
		$miaLista = new wi400List("IMPORT_APPR_FORN_LIST", true);
		$miaLista->setFrom("FAREADIR");
		$miaLista->setOrder("AREBUY,ARECDF,ARECL3,ARECL2,AREANN,AREMES,ARENPV");
		$miaLista->setSelection("SINGLE");
		
		$miaLista->setCols(array(
			new wi400Column("AREBUY","Buyer"),
			new wi400Column("AREDBU","Descrizione buyer"),
			new wi400Column("ARECDF","Fornitore"),
			new wi400Column("AREDCF","Descrizione fornitore"),
			new wi400Column("ARECL3","Cluster liv.3"),
			new wi400Column("AREDC3","Des.cluster 3"),
			new wi400Column("ARECL2","Cluster liv.2"),
			new wi400Column("AREDC2","Des.cluster 2"),
			new wi400Column("AREANN","Anno"),
			new wi400Column("AREMES","Mese"),
			new wi400Column("ARENPV","N° Pdv"),
			new wi400Column("AREREG","Regione"),
			new wi400Column("AREPRO","Provincia"),
			new wi400Column("ARECDC","Cdc"),
			new wi400Column("ARENO","NO"),
			new wi400Column("ARENOT","Note")
		));
		
		// Aggiunta filtri rapidi
		// Fornitore
		$mioFiltro = new wi400Filter("ARECDF","Fornitore","STRING"); 
		$mioFiltro->setFast(true);    
		$miaLista->addFilter($mioFiltro);
		
		$mioFiltro = new wi400Filter("AREDCF","Descrizione fornitore","STRING"); 
		$mioFiltro->setFast(true);    
		$miaLista->addFilter($mioFiltro);
		
		// Aggiunta filtri avanzati
		$mioFiltro = new wi400Filter("AREBUY","Buyer","STRING");     
		$miaLista->addFilter($mioFiltro);
		
		$mioFiltro = new wi400Filter("AREDBU","Descrizione Buyer","STRING");     
		$miaLista->addFilter($mioFiltro);
		
		$mioFiltro = new wi400Filter("ARECL3","Cluster liv.3","STRING");     
		$miaLista->addFilter($mioFiltro);
		
		$mioFiltro = new wi400Filter("AREDC3","Des.cluster 3","STRING");     
		$miaLista->addFilter($mioFiltro);
		
		$mioFiltro = new wi400Filter("ARECL2","Cluster liv.2","STRING");     
		$miaLista->addFilter($mioFiltro);
		
		$mioFiltro = new wi400Filter("AREDC2","Des.cluster 2","STRING");     
		$miaLista->addFilter($mioFiltro);
		
		$miaLista->dispose();
	}

?>