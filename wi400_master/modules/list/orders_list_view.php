<?php


	if ($actionContext->getForm() == "SAVE" 
			|| $actionContext->getForm() == "CANCEL"){
		close_lookup_reload_list($_REQUEST["ORDLIST"], "RELOAD");
	}
	
	$levelDetail = new wi400Detail("ORDERS_LEVEL");
	$levelDetail->addParameter("ORDLIST", $_REQUEST["ORDLIST"]);
	
	$levelDetail->setTitle(_t("Aggiungi livello"));
	$levelDetail->setColsNum(2);
	$levelDetail->setColsWidth(array(80,200,70));
	$orderBySelect = new wi400InputSelect("COLONNA");
	$orderBySelect->setLabel(_t("Ordina per"));
	$orderBySelect->setOptions($ordersCol);
	
	$levelDetail->addField($orderBySelect);
	
	$orderTypeSelect = new wi400InputSelect("TYPE");
	$orderTypeSelect->setLabel(_t('TIPO'));
	$orderTypeSelect->setOptions($ordersType);
	
	$levelDetail->addField($orderTypeSelect);
	
	$levelAddButton = new wi400InputButton("ADD_LEVEL");
	$levelAddButton->setAction("ORDERS_LIST");
	$levelAddButton->setForm("ADD_LEVEL");
	$levelAddButton->setLabel(_t('AGGIUNGI'));
	
	$levelDetail->addButton($levelAddButton);
	
	$levelDetail->dispose();

	wi400Spacer::disposeSpaces();

	$levelsList = new wi400List("ORDERS_LEVELS", true);
	$levelsList->addKey("ID");
	$levelsList->setPageRows(5);
	$levelsList->setShowMenu(false);
	$levelsList->setSubfile($ordersSubFile);
	$levelsList->setFrom($ordersSubFile->getFullTableName());
	
	$lc = new wi400Column("COLONNA");
	$lc->setDescription("Colonna");
	$lc->setWidth(200);
	$lc->setSortable(false);
	$levelsList->addCol($lc);
	
	$lc = new wi400Column("TYPE");
	$lc->setDescription(_t('ORDINAMENTO'));
	$lc->setSortable(false);
	$levelsList->addCol($lc);
	
	$deleteAction = new wi400ListAction("ORDERS_LIST", "DELETE_LEVEL");
	$deleteAction->setLabel(_t("Elimina livello"));
	$levelsList->addAction($deleteAction);
	
	$levelsList->dispose();
	
	/* Bottoni fondo lookup */
	$myButton = new wi400InputButton("ORDERS_ADD_BUTTON");
	$myButton->setAction("ORDERS_LIST");
	$myButton->setForm("SAVE");
	$myButton->setLabel(_t("Applica ordinamenti"));
	$buttonsBar[] = $myButton;
		
	$myButton = new wi400InputButton("ORDERS_REMOVE_BUTTON");
	$myButton->setAction("ORDERS_LIST");
	$myButton->setForm("CANCEL");
	$myButton->setLabel(_t("Rimuovi ordinamenti"));
	$buttonsBar[] = $myButton;

	$myButton = new wi400InputButton("ORDERS_CANCEL_BUTTON");
	$myButton->setScript('closeLookUp()');
	$myButton->setLabel(_t('ANNULLA'));
	$buttonsBar[] = $myButton;
	
	