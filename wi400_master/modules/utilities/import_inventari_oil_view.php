<?php 

	$spacer = new wi400Spacer();

	if(in_array($actionContext->getForm(),array("DEFAULT","LIST"))) {
		$importAction = new wi400Detail('IMPORT_FILE_DET', True);
		$importAction->setTitle('Caricamento inventari oil');
		$importAction->isEditable(true);
		
		$myField = new wi400InputFile("IMPORT_FILE");
		$myField->setLabel("Importa dati da file:");
		$importAction->addField($myField);
		
		$myField = new wi400InputCheckbox("CLEAR_FILE");
		$myField->setLabel("Pulizia dei dati già presenti");
		$myField->setChecked(false);
		$importAction->addField($myField);
		
		$myButton = new wi400InputButton('IMPORT_BUTTON');
		$myButton->setLabel("Importa");
		$myButton->setAction($azione);
		$myButton->setForm("IMPORT");
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
		$miaLista = new wi400List("IMPORT_INVENTARI_OIL_LIST", true);
		$miaLista->setFrom("FCAROIL, FMDAANAR");
		$miaLista->setWhere("MDACDA=WARTIC and ZDT_MDA(MDACDA, ".$_SESSION['data_validita'].")=ZDT_DATA(MDAAVA,MDAMVA,MDAGVA)");
		$miaLista->setOrder("WARTIC");
		$miaLista->setSelection("SINGLE");
		
		$miaLista->setCols(array(
			new wi400Column("WARTIC","Codice articolo"),
			new wi400Column("MDADSA","Descrizione articolo"),
			new wi400Column("WQUANT","Quantità","INTEGER","right"),
			new wi400Column("WCOSTO","Costo unitario","DOUBLE_2","right")
		));
		
		// Aggiunta filtri rapidi
		// Articolo
		$mioFiltro = new wi400Filter("WARTIC","Codice articolo","STRING"); 
		$mioFiltro->setFast(true);    
		$miaLista->addFilter($mioFiltro);
		
		$miaLista->dispose();
	}

?>