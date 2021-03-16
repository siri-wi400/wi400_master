<?php
	
	if($form == "DEFAULT") {
		$miaLista = new wi400List($azione."_PARAMETRI", true);

		$miaLista->setField("DISTINCT(B.ELEMENTO), B.VALORE");
		$miaLista->setFrom("ZTABTABE B LEFT JOIN ZSYSPARM A ON PARAMETRO=ELEMENTO");
		$miaLista->setWhere("TABELLA='SYSPARAM'");
		
		//echo $miaLista->getSql();

		$det_col = new wi400Column("DETTAGLIO", "Dettaglio", "", "CENTER");
		$det_col->setDecorator("ICONS");
		$det_col->setDefaultValue("SEARCH");
		$det_col->setActionListId($azione."_DETTAGLIO");
		
		$miaLista->setCols(array(
			$det_col,
			new wi400Column("ELEMENTO", "Parametro"),
			new wi400Column("VALORE", "Descrizione")
		));
		
		$miaLista->addKey("ELEMENTO");
		
		$mioFiltro = new wi400Filter("ELEMENTO","Parametro","STRING");
		$mioFiltro->setId("ELEMENTO");
		$mioFiltro->setFast(true);
		$miaLista->addFilter($mioFiltro);
		
		// Dettaglio parametro
		$action = new wi400ListAction();
		$action->setId($azione."_DETTAGLIO");
		$action->setAction($azione);
		$action->setForm("DETAIL");
		$action->setLabel("Dettaglio");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		listDispose($miaLista);
		
	}else if($form == "DETAIL") {
		$detail = new wi400Detail($azione."_RIEPILOGO");
		
		$myField = new wi400Text("PARAMETRO", "Parametro", $parametro);
		$detail->addField($myField);
		
		$detail->dispose();
		
		echo "<br/>";
		
		$miaLista = new wi400List($azione."_PARAMETRI_DETAIL", true);
		$miaLista->setSelection("MULTIPLE");
		
		$miaLista->setFrom("ZSYSPARM ");
		$miaLista->setWhere("PARAMETRO='$parametro'");
		$miaLista->setOrder("SOCIETA, SITO, DEPOSITO");
		
		$input = getFieldFromParam($parametro);
		$col_valore = new wi400Column("VALORE", "Valore");
		$col_valore->setInput($input);
		
		$miaLista->setCols(array(
			new wi400Column("SOCIETA", "Societ&agrave;"),
			new wi400Column("SITO", "Sito"),
			new wi400Column("DEPOSITO", "Deposito"),
			new wi400Column("INTERLOCUTORE", "Interlocutore"),
			$col_valore
		));
		
		$miaLista->addKey("SOCIETA");
		$miaLista->addKey("SITO");
		$miaLista->addKey("DEPOSITO");
		$miaLista->addKey("INTERLOCUTORE");
		$miaLista->addKey("VALORE");
		
		
		// Salva
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("SALVA_VALORE");
		$action->setLabel("Salva");
		$action->setSelection("MULTIPLE");
		$miaLista->addAction($action);
		
		// Nuovo
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("NUOVA_CONFIGURAZIONE");
		$action->setLabel("Nuovo");
		$action->setTarget("WINDOW");
		$action->setSelection("NONE");
		$miaLista->addAction($action);
		
		// Elimina
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("ELIMINA_CONFIGURAZIONE");
		$action->setLabel("Elimina");
		$action->setSelection("MULTIPLE");
		$miaLista->addAction($action);
		
		listDispose($miaLista);
	}else if($form == "NUOVA_CONFIGURAZIONE") {
		$detail = new wi400Detail($azione."_NUOVA_CONFIGURAZIONE", !$isFromHistory);
		
		$myField = new wi400InputText("SOCIETA");
		$myField->setLabel("Società");
		$myField->setSize(4);
		$myField->setMaxLength(4);
		$myField->setOnChange("");
		$myField->removeValidation("required");
		//$myField->setValue("");
		$detail->addField($myField);
		
		$myField = new wi400InputText("SITO");
		$myField->setLabel("Sito");
		$myField->setSize(4);
		$myField->setMaxLength(4);
		$myField->setOnChange("");
		$myField->removeValidation("required");
		//$myField->setValue("");
		$detail->addField($myField);
		
		$myField = new wi400InputText("DEPOSITO");
		$myField->setLabel("Deposito");
		$myField->setSize(2);
		$myField->setMaxLength(2);
		$myField->setOnChange("");
		$myField->removeValidation("required");
		//$myField->setValue("");
		$detail->addField($myField);
		
		$myField = new wi400InputText("INTERLOCUTORE");
		$myField->setLabel("Interlocutore");
		$detail->addField($myField);
		
		$myField = getFieldFromParam($parametro);
		$detail->addField($myField);
		
		$button = new wi400InputButton("SALVA_BUTTON");
		$button->setLabel("Salva");
		$button->setAction($azione);
		$button->setValidation(true);
		$button->setForm("INSERT_CONFIGURAZIONE");
		$detail->addButton($button);
		
		$detail->dispose();
	}