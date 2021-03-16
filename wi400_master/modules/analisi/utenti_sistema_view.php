<?php 

	if($actionContext->getForm()=="DEFAULT") {
		// Inizializzazione lista
		$miaLista = new wi400List($azione."_LIST", !$isFromHistory);
		$miaLista->setSubfile($subfile);
		$miaLista->setFrom($subfile->getTable());
		$miaLista->setOrder("USERID");
		$miaLista->setSelection("SINGLE");
		
		$miaLista->setCols(array(
			new wi400Column("USERID","ID Utente"),
			new wi400Column("DESCRIZIONE","Descrizione utente"),
			new wi400Column("EMAIL","Email"),
			new wi400Column("AREA_FUN","Area funzionale"),
			new wi400Column("DES_AREA_FUN","Descrizione area funzionale"),
			new wi400Column("DES_USER","Descrizione profilo"),
			new wi400Column("ENTE","ID Ente"),
			new wi400Column("DES_ENTE","Descrizione Ente"),
			new wi400Column("NAZIONE","Nazione"),
			new wi400Column("DES_NAZIONE","Descrizione Nazione"),
			new wi400Column("STATO","Stato"),
			new wi400Column("LAST_COL","Ultimo collegamento","INTEGER_DATETIME"),
			new wi400Column("VALIDITA","Validità"),
			new wi400Column("DES_OP_CL","Aperto/Chiuso"),
			new wi400Column("TIMESTAMP_CREAZIONE","Data creazione","STRING_COMPLETE_TIMESTAMP")
		));
		
		// aggiunta chiavi di riga
		$miaLista->addKey("USERID");
		
		// Filtri rapidi
		// Utente
		$mioFiltro = new wi400Filter("USERID","ID Utente","STRING"); 
		$mioFiltro->setFast(true);    
		$miaLista->addFilter($mioFiltro);
		
		// Descrizione utente
		$mioFiltro = new wi400Filter("DESCRIZIONE","Descrizione utente","STRING"); 
		$mioFiltro->setFast(true);    
		$miaLista->addFilter($mioFiltro);
		
		// Filtri avanzati
		$mioFiltro = new wi400Filter("AREA_FUN","Solo con area funzionale","CHECK_STRING","<>''");
		$miaLista->addFilter($mioFiltro);
		
		$mioFiltro = new wi400Filter("USERID","Solo utenti WI400","CHECK_STRING"," in (select USER_NAME from $users_table)");
		$miaLista->addFilter($mioFiltro);
		
		$mioFiltro = new wi400Filter("STATO","Stato","STRING"); 
		$miaLista->addFilter($mioFiltro);
		
		listDispose($miaLista);
	}

?>