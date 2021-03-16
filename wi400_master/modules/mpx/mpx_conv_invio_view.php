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
	$mpxList = new wi400List("MPX_CONV_INVIO", !$isFromHistory);
	
	/*
	 * Impostazione della tabella del database da cui si prendono i dati
	 * (corrisponde al FROM di una query sql)
	 */
	$fieldds = array("ID", "MAIUSR","MAIJOB","MAINBR","MAIEMA","MAIMPX","MAIFRM",
			"MAIALI","MAISBJ","MAISTA","MAIAMB","MAIWDW","MAILIB","MAIRIS","MAIERR",
			"MAIDER","MAIINS","MAIELA");
	$stringdds="";
	$virg="";
	foreach ($fieldds as $key => $value) {
		$stringdds .= $virg." MIN(A.$value) AS $value ";
		$virg=" , ";
	}
	$stringdds.=" , MIN(B.MAITOR) AS MAITOR";
	
	$mpxList->setField("$stringdds, trim((select maitor from femaildt where id=a.Id fetch first 
row only))!!' ('!!                                           
(select count(*) from femaildt where id=a.Id)!!')' AS MAITORD ");
	$mpxList->setFrom("FPDFCONV A, FEMAILDT B");
	
	/* Impostazione dell'ordinamento della lista */
	$mpxList->setOrder("A.ID desc");
	$mpxList->setGroup("A.ID");
	$mpxList->setWhere("A.ID=B.ID");
	/*
	 * Creazione di una colonna di checkbox per la selezione degli elementi della lista
	 * ed impostazione del tipo di selezione che si vuole effettuare
	 * SINGLE: si può selezionare solo un elemento (riga) alla volta (un solo checkbox selezionto alla volta)
	 * MULTIPLE: si possono selezionare più elementi (righe) alla volta (più checkbox selezionati alla volta)
	 */
	$mpxList->setSelection("MULTIPLE");
	
	/* Crezione di un filtro della lista */
	$mpxFilter = new wi400Filter("A.ID","ID Conversione");
	$mpxFilter->setFast(true);
	$mpxList->addFilter($mpxFilter);
	
	$mpxFilter = new wi400Filter("MAIFRM","Indirizzo mittente");
	$mpxFilter->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
	$mpxList->addFilter($mpxFilter);
	
	$mpxFilter = new wi400Filter("MAITOR","Indirizzo Destinatario");
	$mpxFilter->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
	$mpxList->addFilter($mpxFilter);
		
	$mpxFilter = new wi400Filter("MAIUSR","Utente generazione e-mail");
	$mpxList->addFilter($mpxFilter);
	
	$mpxFilter = new wi400Filter("MAISBJ","Oggetto e-mail");
	$mpxFilter->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
	$mpxList->addFilter($mpxFilter);
	
	$mpxFilter = new wi400Filter("MAIEMA","Invio E-mail");
	$mpxList->addFilter($mpxFilter);
	
	$mpxFilter = new wi400Filter("MAIMPX","Invio ad MPX");
	$mpxList->addFilter($mpxFilter);
	
	$mpxFilter = new wi400Filter("MAIERR","Codice errore");
	$mpxList->addFilter($mpxFilter);
	
	$mpxFilter = new wi400Filter("MAIDER","Descrizione errore");
	$mpxList->addFilter($mpxFilter);
	
	/*
	 * Creazione di una colonna della lista
	 * Il primo parametro passato alla funzione indica il campo della tabella da inserire,
	 * il secondo il titolo della colonna da visualizzare
	 */
	
	$id_col = new wi400Column("ID","ID");
	$id_col->setActionListId("MPX_INOLTRO");

	$mpxList->addCol($id_col);
	$mpxList->addCol(new wi400Column("MAIUSR","MAIUSR"));
	$mpxList->addCol(new wi400Column("MAIJOB","MAIJOB"));
	$mpxList->addCol(new wi400Column("MAINBR","MAINBR"));
	$mpxList->addCol(new wi400Column("MAIEMA","MAIEMA"));
	$mpxList->addCol(new wi400Column("MAIMPX","MAIMPX"));
	$mpxList->addCol(new wi400Column("MAIFRM","MAIFRM"));
	//$mpxList->addCol(new wi400Column("MAITOR","MAITOR"));
	$dest_col = new wi400Column("MAITORD","MAITORD");
	$dest_col->setActionListId("MPX_DEST_LIST");
	$mpxList->addCol($dest_col);
	
	$mpxList->addCol(new wi400Column("MAIALI","MAIALI"));
	$mpxList->addCol(new wi400Column("MAISBJ","MAISBJ"));
	$mpxList->addCol(new wi400Column("MAISTA","MAISTA"));
	$mpxList->addCol(new wi400Column("MAIAMB","MAIAMB"));
	$mpxList->addCol(new wi400Column("MAIWDW","MAIWDW"));
	$mpxList->addCol(new wi400Column("MAILIB","MAILIB"));
	$mpxList->addCol(new wi400Column("MAIRIS","MAIRIS"));
	$mpxList->addCol(new wi400Column("MAIERR","MAIERR"));
	$mpxList->addCol(new wi400Column("MAIDER","MAIDER"));
	$mpxList->addCol(new wi400Column("MAIINS","MAIINS"));
	$mpxList->addCol(new wi400Column("MAIELA","MAIELA"));
	
	/*
	 * Creazione azioni
	 */	
	
	/*
	 * Istanzio la classe wi400ListAction che gestisce le azioni sulle liste
	 * Crea un elenco a tendina di azioni che è possibile eseguire sugli elementi della lista
	 */
	$actionDetail = new wi400ListAction();
	/* Impostazione del codice dell'azione */
	$actionDetail->setAction("MPX_CONV_INVIO_DET");
	/* Impostazione del nome dell'azione da visualizzare nell'elenco */
	$actionDetail->setLabel("Dettaglio Conversione ed invio");
	/* Impostazione del tipo di selezione dei dati della lista ai quali applicare l'azione */
	$actionDetail->setSelection("SINGLE");
	/* Assegnazione dell'azione alla lista */
	$mpxList->addAction($actionDetail);
	
	$hiddenField = new wi400InputHidden("FROM");
	$hiddenField->setValue('conv');
	$hiddenField->dispose();
	
	$actionDetail = new wi400ListAction();
//	$actionDetail->setAction("MPX_DETAIL&FROM=conv");
	$actionDetail->setAction("MPX_DETAIL");
	$actionDetail->setLabel("Dettaglio MPX");
	$actionDetail->setSelection("SINGLE");
	$mpxList->addAction($actionDetail);
	
	$actionDetail = new wi400ListAction();
	$actionDetail->setAction("MPX_ATC_LIST");
	$actionDetail->setLabel("Lista degli allegati");
	$actionDetail->setSelection("SINGLE");	
	$mpxList->addAction($actionDetail);
	
	$actionDetail = new wi400ListAction();
	$actionDetail->setAction("MPX_DEST_LIST");
	$actionDetail->setId("MPX_DEST_LIST");
	$actionDetail->setLabel("Lista dei destinatari");
	$actionDetail->setSelection("SINGLE");
	$mpxList->addAction($actionDetail);
	
	$actionDetail = new wi400ListAction();
	$actionDetail->setAction("MPX_CONV_INVIO_DET");
	$actionDetail->setForm("INSERT");
	$actionDetail->setLabel("Inserimento nuovo record");
	$actionDetail->setSelection("NONE");
	$mpxList->addAction($actionDetail);
	
	$actionDetail = new wi400ListAction();
	$actionDetail->setAction("MPX_CONV_INVIO");
	$actionDetail->setForm("DELETE");
	$actionDetail->setLabel("Eliminazione record");
	$actionDetail->setSelection("SINGLE");
	$actionDetail->setConfirmMessage("Eliminare il record e tutti quelli ad esso associati?");
	$mpxList->addAction($actionDetail);
	
	$actionDetail = new wi400ListAction();
//	$actionDetail->setAction("MPX_DETAIL&FROM=conv");
	$actionDetail->setAction("MPX_DETAIL");
	$actionDetail->setForm("INSERT");
	$actionDetail->setLabel("Inserimento impostazioni MPX");
	$actionDetail->setSelection("SINGLE");
	$mpxList->addAction($actionDetail);
	
	$actionDetail = new wi400ListAction();
	$actionDetail->setAction("MPX_INVIO");
	$actionDetail->setForm("ESECUZIONE");
	$actionDetail->setLabel("Invio e-mail / Conversione / Generazione XML");
	$actionDetail->setSelection("MULTIPLE");
	$actionDetail->setConfirmMessage("Eseguire conversione/invio via e-mail/generazione XML?");
	$mpxList->addAction($actionDetail);

	$actionDetail = new wi400ListAction();
	$actionDetail->setAction("MPX_INVIO");
	$actionDetail->setForm("NEW_ESECUZIONE");
	$actionDetail->setLabel("*NEW Invio e-mail / Conversione / Generazione XML");
	$actionDetail->setSelection("MULTIPLE");
	$actionDetail->setConfirmMessage("Eseguire conversione/invio via e-mail/generazione XML?");
	$mpxList->addAction($actionDetail);
		
	$actionDetail = new wi400ListAction();
	$actionDetail->setAction("MPX_INVIO");
	$actionDetail->setForm("INVIO_MPX");
	$actionDetail->setLabel("Invio ad MPX");
	$actionDetail->setSelection("NONE");
	$actionDetail->setConfirmMessage("Eseguire l'invio ad MPX?");
	$mpxList->addAction($actionDetail);
	
	$actionDetail = new wi400ListAction();
	$actionDetail->setId("MPX_INOLTRO");
	$actionDetail->setAction("MPX_INOLTRO");
	$actionDetail->setForm("DEFAULT");
	$actionDetail->setLabel("Inoltro e-mail");
	$actionDetail->setSelection("SINGLE");
	$mpxList->addAction($actionDetail);
	
	/*
	 * Impostazione dei dati da passare all'esecuzione di un'azione
	 * (relativi all'elemento selezionato)
	 */
	$mpxList->addKey("ID");
	
	/* Visualizzazione della view generata */
	$mpxList->dispose();

?>