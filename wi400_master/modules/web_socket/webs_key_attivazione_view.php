<?php
if($actionContext->getForm()=="DEFAULT") {
	$searchAction = new wi400Detail($azione."_DETAIL", False);
	$searchAction->isEditable(true);
	$searchAction->setColsNum(2);
	$searchAction->setTitle("Attivazione Chiavi WEBSOCKET");

	// Inizializzazione lista
	$miaLista = new wi400List($azione."_LIST", True);
	$miaLista->setSelection("MULTIPLE");
	$miaLista->setField('*');
	$miaLista->setFrom("SIR_WEBS");
	$miaLista->setWhere("WEBTYP='STATIC'");
	$miaLista->setOrder("WEBID");
	
	$miaLista->setIncludeFile("web_socket", "webs_key_attivazione_function.php");
	
	// Colonne
	$inputField = new wi400InputSelect("STATO_SEL");
	$inputField->addOption($array_stati[0], '1');
	$inputField->addOption($array_stati[1], 'D');

	$orderedStatoColumn = new wi400Column("STATO","Stato OTM");
	$orderedStatoColumn->setAlign("right");
	$orderedStatoColumn->setSortable(false);
	$orderedStatoColumn->setDefaultValue('EVAL:$row["WEBSTA"]');
	$orderedStatoColumn->setInput($inputField);

	$ScadenzaColumn = new wi400Column("SCADENZA");
	$ScadenzaColumn->setAlign("right");
	$ScadenzaColumn->setSortable(false);
	$ScadenzaColumn->setDescription("Scadenza");
	$ScadenzaColumn->setDefaultValue('EVAL:getwebscadenza($row["WEBLIF"],$row["WEBTIU"])');

	$miaLista->setCols(array(
			new wi400Column("WEBID","Id password"),
			new wi400Column("WEBUSR","Utente"),
			new wi400Column("WEBTIM","Data inserimento"),
			new wi400Column("WEBTIU","Data unix"),
			new wi400Column("WEBLIF","Max life"),
			$orderedStatoColumn,
			new wi400Column("WEBTIP","Tipo contenuto"),
			new wi400Column("WEBNOT","Note"),
			new wi400Column("WEBCON","Contenuto"),
			$ScadenzaColumn,
	));

	// Aggiunta chiavi di riga
	$miaLista->addKey("WEBID");
	$miaLista->addKey("WEBUSR");
	$miaLista->addKey("STATO");

		// Aggiunta azioni di lista
		// Salva
		$listAction = new wi400ListAction();
		$listAction->setAction($azione);
		$listAction->setForm("SAVE_WEB");
		$listAction->setLabel("Aggiorna");
		$miaLista->addAction($listAction);
		
		// Elimina
		$listAction = new wi400ListAction();
		$listAction->setAction($azione);
		$listAction->setForm("DEL_WEB");
		$listAction->setLabel("Elimina");
		$miaLista->addAction($listAction);

	listDispose($miaLista);
}