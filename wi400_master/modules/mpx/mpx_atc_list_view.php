<?php 

/*
 * MVC (Model-View-Controller) - File di view
 * Visualizza i dati contenuti nel model e si occupa dell'interazione con utenti e agenti
 */

	/*
	 * View della pagina
	 */

	if($actionContext->getForm()=="DEFAULT") {

		/*
		 * Istanzio la classe wi400List che gestisce le liste (elenchi in tabella)
		 */
		$mpxList = new wi400List("MPX_ATC_LIST",!$isFromHistory);
		
		/*
		 * Impostazione della tabella del database da cui si prendono i dati
		 * (corrisponde al FROM di una query sql)
		 */
		$mpxList->setFrom("FEMAILAL");
		$mpxList->setWhere("ID='$ID'");
		
		/* Impostazione dell'ordinamento della lista */
		$mpxList->setOrder("TPCONV desc, MAIATC");
		
		/*
		 * Creazione di una colonna di checkbox per la selezione degli elementi della lista
		 * ed impostazione del tipo di selezione che si vuole effettuare
		 * SINGLE: si può selezionare solo un elemento (riga) alla volta (un solo checkbox selezionto alla volta)
		 * MULTIPLE: si possono selezionare più elementi (righe) alla volta (più checkbox selezionati alla volta)
		 */
		$mpxList->setSelection("MULTIPLE");
		
		/*
		 * Creazione di una colonna della lista
		 * Il primo parametro passato alla funzione indica il campo della tabella da inserire,
		 * il secondo il titolo della colonna da visualizzare
		 */
		$mpxList->addCol(new wi400Column("ID","ID"));
/*	
//		$mpxList->addCol(new wi400Column("MAIATC","MAIATC"));
		$atcPrv = new wi400Column("MAIATC","MAIATC","","","",true);
		$atcPrv->setDetailAction("MPX_ATC_PRV&DECORATION=clean&CAMPO=atc", "");
		$mpxList->addCol($atcPrv);
		
//		$mpxList->addCol(new wi400Column("MAIPAT","MAIPAT"));
		$atcPrv = new wi400Column("MAIPAT","MAIPAT","","","",true);
		$atcPrv->setDetailAction("MPX_ATC_PRV&DECORATION=clean&CAMPO=pat", "");
		$mpxList->addCol($atcPrv);
*/	
		$atcPrv = new wi400Column("MAIATC","MAIATC");
		$atcPrv->setDetailAction("MPX_ATC_LIST&CAMPO=atc", "ATC_PRV");
		$atcPrv->setDetailUrlEncode(true);
		$mpxList->addCol($atcPrv);
		
		$atcPrv = new wi400Column("MAIPAT","MAIPAT");
		$atcPrv->setDetailAction("MPX_ATC_LIST&CAMPO=pat", "ATC_PRV");
		$atcPrv->setDetailUrlEncode(true);
		$mpxList->addCol($atcPrv);
		
		$mpxList->addCol(new wi400Column("CONV","CONV"));
		$mpxList->addCol(new wi400Column("TPCONV","TPCONV"));
		$mpxList->addCol(new wi400Column("MAIMOD","MAIMOD"));
		$mpxList->addCol(new wi400Column("MAIARG","MAIARG"));
/*	
//		$mpxList->addCol(new wi400Column("MAINAM","MAINAM"));
		$atcPrv = new wi400Column("MAINAM","MAINAM","","","",true);
		$atcPrv->setDetailAction("MPX_ATC_PRV&DECORATION=clean&CAMPO=nam", "");
		$mpxList->addCol($atcPrv);
*/	
		$atcPrv = new wi400Column("MAINAM","MAINAM");
		$atcPrv->setDetailAction("MPX_ATC_LIST&CAMPO=nam", "ATC_PRV");
//		$atcPrv->setDetailUrlEncode(true);									// @todo Problema prv files in ambiente WI400_VPORRAZZO
		$mpxList->addCol($atcPrv);
		
		$mpxList->addCol(new wi400Column("FILZIP","FILZIP"));
		
		$mpxList->addCol(new wi400Column("MAISTO","MAISTO"));
		$mpxList->addCol(new wi400Column("MAIOUT","MAIOUT"));
		$mpxList->addCol(new wi400Column("MAISTT","MAISTT", "COMPLETE_TIMESTAMP"));
	
		/*
		 * Impostazione dei dati da passare all'esecuzione di un'azione
		 * (relativi all'elemento selezionato)
		 */
		$mpxList->addKey("ID");
		$mpxList->addKey("MAIATC");
		$mpxList->addKey("MAIPAT");
		$mpxList->addKey("MAINAM");
		
		/*
		 * Creazione azioni
		 */	
		
		/*
		 * Istanzio la classe wi400ListAction che gestisce le azioni sulle liste
		 * Crea un elenco a tendina di azioni che è possibile eseguire sugli elementi della lista
		 */
		$actionDetail = new wi400ListAction();
		$actionDetail->setAction("MPX_ATC_DETAIL");
		$actionDetail->setLabel("Dettaglio record");
		$actionDetail->setSelection("SINGLE");	
		$mpxList->addAction($actionDetail);
		
		$hiddenField = new wi400InputHidden("ID");
		$hiddenField->setValue($ID);
		$hiddenField->dispose();
		
		$actionDetail = new wi400ListAction();
		$actionDetail->setAction("MPX_ATC_DETAIL");
		$actionDetail->setForm("INSERT");
		$actionDetail->setLabel("Inserimento nuovo record");
		$actionDetail->setSelection("NONE");
		$mpxList->addAction($actionDetail);
		
		$actionDetail = new wi400ListAction();
		$actionDetail->setAction("MPX_ATC_LIST");
		$actionDetail->setForm("DELETE");
		$actionDetail->setLabel("Eliminazione record");
		$actionDetail->setSelection("SINGLE");
		$actionDetail->setConfirmMessage("Eliminare il record?");
		$mpxList->addAction($actionDetail);
		
		/* Visualizzazione della view generata */
		$mpxList->dispose();
	}
	else if($actionContext->getForm()=="ATC_PRV") {
		downloadDetail($TypeImage, $filename, $temp, "Esportazione completata");
	}

?>