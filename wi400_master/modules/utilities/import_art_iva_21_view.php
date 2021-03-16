<?php 

	$spacer = new wi400Spacer();

	if(in_array($actionContext->getForm(),array("DEFAULT","LIST"))) {
		$importAction = new wi400Detail($azione.'_SRC', True);
		$importAction->setTitle('Parametri');
		$importAction->isEditable(true);
		
		$myField = new wi400InputFile("IMPORT_FILE");
		$myField->setLabel("Importa dati da file:");
		$importAction->addField($myField);
		
		$myField = new wi400InputSwitch("CLEAR_FILE");
		$myField->setLabel("Pulizia dei dati già presenti");
		$myField->setOnLabel("SI");
		$myField->setOffLabel("NO");		
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
		$miaLista = new wi400List($azione."_LIST", true);
		$miaLista->setFrom($tabella);
		$miaLista->setOrder("ARTICOLO");
		$miaLista->setSelection("SINGLE");
		
		$miaLista->setCols(array(
			new wi400Column("ARTICOLO","Codice articolo"),
		));
		
		// Aggiunta filtri rapidi
		// Articolo
		$mioFiltro = new wi400Filter("ARTICOLO","Codice articolo","STRING"); 
		$mioFiltro->setFast(true);    
		$miaLista->addFilter($mioFiltro);
		
		$miaLista->dispose();
	}

?>