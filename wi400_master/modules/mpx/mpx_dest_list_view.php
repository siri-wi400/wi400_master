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
	$mpxList = new wi400List("MPX_DEST_LIST", !$isFromHistory);
	
	/*
	 * Impostazione della tabella del database da cui si prendono i dati
	 * (corrisponde al FROM di una query sql)
	 */
	$mpxList->setFrom("FEMAILDT");
	$mpxList->setWhere("ID='$ID'");
	
	/* Impostazione dell'ordinamento della lista */
	$mpxList->setOrder("MATPTO desc, MAIALI asc, MAITOR asc");
	
	/*
	 * Creazione di una colonna di checkbox per la selezione degli elementi della lista
	 * ed impostazione del tipo di selezione che si vuole effettuare
	 * SINGLE: si può selezionare solo un elemento (riga) alla volta (un solo checkbox selezionto alla volta)
	 * MULTIPLE: si possono selezionare più elementi (righe) alla volta (più checkbox selezionati alla volta)
	 */
	$mpxList->setSelection("MULTIPLE");
	
	/*
	 * Impostazione dei dati da passare all'esecuzione di un'azione
	 * (relativi all'elemento selezionato)
	 */
	$mpxList->addKey("ID");
	$mpxList->addKey("MAITOR");
	$mpxList->addKey("MATPTO");
	
	/*
	 * Creazione di una colonna della lista
	 * Il primo parametro passato alla funzione indica il campo della tabella da inserire,
	 * il secondo il titolo della colonna da visualizzare
	 */
	$mpxList->addCol(new wi400Column("ID","ID"));
	$mpxList->addCol(new wi400Column("MAITOR","MAITOR"));
	$mpxList->addCol(new wi400Column("MAIALI","MAIALI"));
	$mpxList->addCol(new wi400Column("MATPTO","MATPTO"));
	
	/*
	 * Creazione azioni
	 */	
	
	/*
	 * Istanzio la classe wi400ListAction che gestisce le azioni sulle liste
	 * Crea un elenco a tendina di azioni che è possibile eseguire sugli elementi della lista
	 */
	$actionDetail = new wi400ListAction();
	$actionDetail->setAction("MPX_DEST_DETAIL");
	$actionDetail->setLabel("Dettaglio record");
	$actionDetail->setSelection("SINGLE");	
	$mpxList->addAction($actionDetail);
	
	$hiddenField = new wi400InputHidden("ID");
	$hiddenField->setValue($ID);
	$hiddenField->dispose();
	
	$actionDetail = new wi400ListAction();
	$actionDetail->setAction("MPX_DEST_DETAIL");
	$actionDetail->setForm("INSERT");
	$actionDetail->setLabel("Inserimento nuovo record");
	$actionDetail->setSelection("NONE");
	$mpxList->addAction($actionDetail);
	
	$actionDetail = new wi400ListAction();
	$actionDetail->setAction("MPX_DEST_LIST");
	$actionDetail->setForm("DELETE");
	$actionDetail->setLabel("Eliminazione record");
	$actionDetail->setSelection("SINGLE");
	$actionDetail->setConfirmMessage("Eliminare il record?");
	$mpxList->addAction($actionDetail);
	
	/* Visualizzazione della view generata */
	$mpxList->dispose();

?>