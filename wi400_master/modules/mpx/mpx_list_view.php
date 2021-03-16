<?php 

/*
 * MVC (Model-View-Controller) - File di view
 * Visualizza i dati contenuti nel model e si occupa dell'interazione con utenti e agenti
 */

	/*
	 * View della pagina
	 */

	/*
	 * Istanzio la classe wi400List che gestisce le liste (elenchi in tabella)
	 */
	$mpxList = new wi400List("MPX_LISTA", !$isFromHistory);
	
	/*
	 * Impostazione della tabella del database da cui si prendono i dati
	 * (corrisponde al FROM di una query sql)
	 */
	$mpxList->setFrom("FMPXPARM");
	
	/* Impostazione dell'ordinamento della lista */
	$mpxList->setOrder("ID desc");

	/*
	 * Creazione di una colonna di checkbox per la selezione degli elementi della lista
	 * ed impostazione del tipo di selezione che si vuole effettuare
	 * SINGLE: si può selezionare solo un elemento (riga) alla volta (un solo checkbox selezionto alla volta)
	 * MULTIPLE: si possono selezionare più elementi (righe) alla volta (più checkbox selezionati alla volta)
	 */
	$mpxList->setSelection("MULTIPLE");
	
	/* Crezione di un filtro della lista */
	$mpxFilter = new wi400Filter("ID","ID impostazioni MPX");
	$mpxFilter->setFast(true);
	$mpxList->addFilter($mpxFilter);
	
	$mpxFilter = new wi400Filter("ADDR1","Indirizzo 1");
	$mpxList->addFilter($mpxFilter);
	
	$mpxFilter = new wi400Filter("ADDR2","Indirizzo 2");
	$mpxList->addFilter($mpxFilter);
	
	$mpxFilter = new wi400Filter("ADDR3","Indirizzo 3");
	$mpxList->addFilter($mpxFilter);
	
	/*
	 * Creazione di una colonna della lista
	 * Il primo parametro passato alla funzione indica il campo della tabella da inserire,
	 * il secondo il titolo della colonna da visualizzare
	 */
	$mpxList->addCol(new wi400Column("ID","ID"));
	$mpxList->addCol(new wi400Column("TEST","TEST"));
	$mpxList->addCol(new wi400Column("NUMPAG","NUMPAG"));
	$mpxList->addCol(new wi400Column("WKPRID","WKPRID"));
	$mpxList->addCol(new wi400Column("ADDR1","ADDR1"));
	$mpxList->addCol(new wi400Column("ADDR2","ADDR2"));
	$mpxList->addCol(new wi400Column("ADDR3","ADDR3"));
	$mpxList->addCol(new wi400Column("CAP","CAP"));
	$mpxList->addCol(new wi400Column("CITTA","CITTA'"));
	$mpxList->addCol(new wi400Column("PROV","PROV"));
	$mpxList->addCol(new wi400Column("NAZ","NAZ"));
	$mpxList->addCol(new wi400Column("GLOCOD","GLOCOD"));
	$mpxList->addCol(new wi400Column("SETID","SETID"));
	$mpxList->addCol(new wi400Column("SETCOD","SETCOD"));
	$mpxList->addCol(new wi400Column("PDFCOD","PDFCOD"));
	$mpxList->addCol(new wi400Column("ENVCOD","ENVCOD"));
	
	/*
	 * Creazione azioni
	 */	
	
	$hiddenField = new wi400InputHidden("FROM");
	$hiddenField->setValue('mpx');
	$hiddenField->dispose();
	
	/*
	 * Istanzio la classe wi400ListAction che gestisce le azioni sulle liste
	 * Crea un elenco a tendina di azioni che è possibile eseguire sugli elementi della lista
	 */
	$actionDetail = new wi400ListAction();
//	$actionDetail->setAction("MPX_DETAIL&FROM=mpx");
	$actionDetail->setAction("MPX_DETAIL");
	$actionDetail->setLabel("Dettaglio MPX");
	$actionDetail->setSelection("SINGLE");
	$mpxList->addAction($actionDetail);
		
	$actionDetail = new wi400ListAction();
//	$actionDetail->setAction("MPX_DETAIL&FROM=mpx");
	$actionDetail->setAction("MPX_DETAIL");
	$actionDetail->setForm("INSERT");
	$actionDetail->setLabel("Inserimento impostazioni MPX");
	$actionDetail->setSelection("NONE");
	$mpxList->addAction($actionDetail);
	
	$actionDetail = new wi400ListAction();
//	$actionDetail->setAction("MPX_LIST&FROM=mpx");
	$actionDetail->setAction("MPX_LIST");
	$actionDetail->setForm("DELETE");
	$actionDetail->setLabel("Eliminazione record");
	$actionDetail->setSelection("SINGLE");
	$actionDetail->setConfirmMessage("Eliminare il record?");
	$mpxList->addAction($actionDetail);
	
	/*
	 * Impostazione dei dati da passare all'esecuzione di un'azione
	 * (relativi all'elemento selezionato)
	 */
	$mpxList->addKey("ID");
	
	/* Visualizzazione della view generata */
	$mpxList->dispose();

?>