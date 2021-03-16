<?php
	
	if($actionContext->getForm() == "DEFAULT") {
		$miaLista = new wi400List($azione."_ARGO", true);
		$miaLista->setField("FLD_ARGO, FLD_DESC");
		$miaLista->setFrom("ZFLDARGD");
		$miaLista->setWhere("FLD_TYPE='****'");
		$miaLista->setOrder("FLD_ARGO");
		
		$miaLista->setHelpTool("ITALAMP", "HEAD", 0, 0);
		
		$dettaglio_col = new wi400Column("GO_DETTAGLIO", "Dettaglio", "", "CENTER");
		$dettaglio_col->setDecorator("ICONS");
		$dettaglio_col->setDefaultValue("SEARCH");
		$dettaglio_col->setActionListId($azione."_DETTAGLIO");
		$dettaglio_col->setSortable(false);
		$dettaglio_col->setExportable(false);
		
		$modifica_col = new wi400Column("MODIFICA", "Modifica", "", "CENTER");
		$modifica_col->setDecorator("NOTE_ICONS");
		$modifica_col->setDefaultValue("1");
		$modifica_col->setActionListId("MOD_REC");
		$modifica_col->setSortable(false);
		$modifica_col->setExportable(false);
		
		$miaLista->setCols(array(
			$dettaglio_col,
			$modifica_col,
			new wi400Column("FLD_ARGO", "Argomento"),
			new wi400Column("FLD_DESC", "Descrizione")
		));
		
		$miaLista->addKey("FLD_ARGO");
		$miaLista->addKey("FLD_DESC");
		
		$action = new wi400ListAction();
		$action->setId($azione."_DETTAGLIO");
		$action->setAction($azione);
		$action->setForm("DETAIL");
		$action->setLabel("Dettaglio");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("NEW_ARGO");
		$action->setLabel("Nuovo argomento");
		$action->setSelection("NONE");
		$action->setTarget("WINDOW");
		$miaLista->addAction($action);
		
		$action = new wi400ListAction();
		$action->setId("MOD_REC");
		$action->setAction($azione);
		$action->setForm("MOD_ARGO");
		$action->setLabel("Modifica");
		$action->setSelection("SINGLE");
		$action->setTarget("WINDOW");
		$miaLista->addAction($action);
		
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("ELIMINA_ARGO");
		$action->setLabel("Elimina");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		listDispose($miaLista);
	}
	else if($actionContext->getForm() == "DETAIL") {
		$detail = new wi400Detail("RIEPILOGO");
		
		$myField = new wi400Text("ARGOMENTO_DESC", "Argomento", $key['FLD_ARGO']." - ".$key['FLD_DESC']);
		$detail->addField($myField);
		
		$detail->dispose();
		
		echo "<br/>";
		
		$select = "FLD_TYPE, FLD_TYPED, FLD_ORDER, FLD_USO, FLD_SINGLE";
		$select .= ", ".get_query_case_cond($uso_array, "FLD_USO", "FLD_USOD");
		
		$miaLista = new wi400List($azione."_DETAIL", true);
		$miaLista->setSelection("MULTIPLE");
		$miaLista->setField($select);
		$miaLista->setFrom("ZFLDARGD");
		$miaLista->setWhere("FLD_ARGO='{$key['FLD_ARGO']}' AND FLD_TYPE<>'****'");
		$miaLista->setOrder("FLD_ORDER");
		
		$mod_col = new wi400Column("MOD_COL", "Modifica", "", "CENTER");
		$mod_col->setDecorator("NOTE_ICONS");
		$mod_col->setDefaultValue("1");
		$mod_col->setActionListId("MOD_SCHEDA");
		$mod_col->setSortable(false);
		$mod_col->setExportable(false);
		
		$col_stampa = new wi400Column("FLD_SINGLE", "Stampa su<br>pagina sigola", "STRING", "center");
		$col_stampa->setDecorator("YES_NO_ICO_NULL");
		
		$miaLista->setCols(array(
			$mod_col,
			new wi400Column("FLD_TYPE", "Tipo scheda"),
			new wi400Column("FLD_TYPED", "Descrizione"),
				new wi400Column("FLD_ORDER", "Ordinamento<br>scheda", "INTEGER", "right"),
//				new wi400Column("FLD_USO", "Utilizzo<br>scheda"),
				new wi400Column("FLD_USOD", "Utilizzo<br>scheda"),
				$col_stampa,
		));
		
		$miaLista->addKey("FLD_TYPE");
		$miaLista->addKey("FLD_TYPED");
		$miaLista->addKey("FLD_ORDER");
		$miaLista->addKey("FLD_USO");
		$miaLista->addKey("FLD_SINGLE");
		
		$action = new wi400ListAction();
		$action->setId("NEW_SCHEDA");
		$action->setAction($azione);
		$action->setForm("NEW_TIPO_SCHEDA");
		$action->setLabel("Nuovo");
		$action->setSelection("NONE");
		$action->setTarget("WINDOW");
		$miaLista->addAction($action);
		
		$action = new wi400ListAction();
		$action->setId("MOD_SCHEDA");
		$action->setAction($azione);
		$action->setForm("MOD_TIPO_SCHEDA");
		$action->setLabel("Modifica");
		$action->setTarget("WINDOW");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("ELIMINA_TIPO_SCHEDA");
		$action->setLabel("Elimina");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		listDispose($miaLista);
	}
	else if(in_array($actionContext->getForm(), array("NEW_ARGO", "MOD_ARGO"))) {
		$form = $actionContext->getForm();
		
		$detail = new wi400Detail("RIEPILOGO");
		$detail->setSource($key);
		
		$myField = new wi400InputText("FLD_ARGO");
		$myField->setLabel("Argomento");
		$myField->setMaxLength(10);
		$myField->setSize(10);
		$detail->addField($myField);
		
		$myField = new wi400InputText("FLD_DESC");
		$myField->setLabel("Descrizione");
		$detail->addField($myField);
		
		$myButton = new wi400InputButton("SALVA");
		$myButton->setLabel("Salva");
		$myButton->setAction($azione);
		if($form == "NEW_ARGO")
			$myButton->setForm("INSERT_ARGO");
		else
			$myButton->setForm("UPDATE_ARGO");
		
		$detail->addButton($myButton);
		
		$detail->dispose();
	}
	else if(in_array($actionContext->getForm(), array("NEW_TIPO_SCHEDA", "MOD_TIPO_SCHEDA"))) {
		$form = $actionContext->getForm();
	
		$detail = new wi400Detail("NEW_MOD_SCHEDA");
		$detail->setSource($key_detail);
	
		$myField = new wi400InputText("FLD_TYPE");
		$myField->setLabel("Tipo scheda");
		$myField->addValidation('required');
		$myField->setMaxLength(4);
		$myField->setSize(4);
		$myField->setCase("UPPER");
		$detail->addField($myField);
	
		$myField = new wi400InputText("FLD_TYPED");
		$myField->setLabel("Descrizione scheda");
		$myField->addValidation('required');
		$myField->setMaxLength(45);
		$myField->setSize(45);
		$detail->addField($myField);
		
		// Ordinamento
		$myField = new wi400InputText('FLD_ORDER');
		$myField->setLabel("Ordinamento scheda");
		$myField->addValidation('required');
		$myField->setMaxLength(2);
		$myField->setSize(2);
		$myField->setMask("0123456789");
		$detail->addField($myField);
		
		// Utilizzo
		$mySelect = new wi400InputSelect('FLD_USO');
		$mySelect->setLabel("Utilizzo");
		$mySelect->setFirstLabel("Seleziona...");
		$mySelect->setOptions($uso_array);
		$detail->addField($mySelect);
		
		// Stampa su pagina singola
		$myField = new wi400InputSwitch("FLD_SINGLE");
		$myField->setLabel("Stampa su pagina singola");
		$myField->setOnLabel(_t('LABEL_YES'));
		$myField->setOffLabel(_t('LABEL_NO'));
		$myField->setChecked($single);
		$myField->setValue("S");
		$detail->addField($myField);
	
		$myButton = new wi400InputButton("SALVA");
		$myButton->setLabel("Salva");
		$myButton->setAction($azione);
		if($form == "NEW_TIPO_SCHEDA")
			$myButton->setForm("INSERT_TIPO_SCHEDA");
		else
			$myButton->setForm("UPDATE_TIPO_SCHEDA");
	
		$detail->addButton($myButton);
	
		$detail->dispose();
	}