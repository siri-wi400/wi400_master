<?php

	echo '<link rel="stylesheet" type="text/css" href="themes/common/css/button.css"  media="screen">';
	echo "	<style>
				.color {
					float: left;
					border: solid 1px black;
					color: #FFF;
					text-shadow: none;
					width: 82px;
					white-space: normal;
					background-color: #143962;
					background: -webkit-linear-gradient(#7CA6D6, #143962);
					background: linear-gradient(#7CA6D6, #143962);
					background: -o-linear-gradient(#7CA6D6, #143962);
					background: -moz-linear-gradient(#7CA6D6, #143962);
				}
			</style>";
	if($form == "DEFAULT") {
		$detail = new wi400Detail($azione."_SRC", TRUE);// !$isFromHistory);
		
		// Società
		//$myField = getFieldSocieta();
		$myField = new wi400InputHidden('SOCIETA');
		$myField->setValue('000');
		$detail->addField($myField);
		
		// Tabella	
		$myField = new wi400InputText("TABELLA");
		$myField->setLabel("Nome tabella");
		$myField->addValidation("required");
		$myField->setSize(20);
		$myField->setCase("UPPER");
		$myField->setInfo("Scegliere la tabella");	
		
		$select = "TABELLA";
		$from = "ZTABTABE";
		
		$where = "";
		
		$decodeParameters = array(
				'TYPE' => 'common',
				'TABLE_NAME' => "$from",
				'COLUMN' => 'TABELLA',
				'KEY_FIELD_NAME' => 'TABELLA',
				'GROUP_BY' => "TABELLA",
				'FILTER_SQL' => $where,
				'ALLOW_NEW' => True,
				'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
			
		$myLookUp = new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE",$from);
		$myLookUp->addParameter("CAMPO","TABELLA");
		$myLookUp->addParameter("LU_WHERE", $where);
		$myLookUp->addParameter("LU_FIELDS", $select);
		$myLookUp->addParameter("LU_GROUP", $select);
		$myField->setLookUp($myLookUp);
		
		$detail->addField($myField);
		
		$button = new wi400InputButton("ESEGUI");
		$button->setLabel("Esegui");
		$button->setAction($azione);
		$button->setForm("LISTA");
		$button->setValidation(true);
		$detail->addButton($button);
		
		$detail->dispose();
		
	}else if($form == "LISTA") {
		$detail = new wi400Detail($azione."_SRC", !$isFromHistory);
		
		$myField = new wi400InputText("SOCIETA");
		$myField->setLabel("Società");
		$myField->setReadonly(true);
		$myField->setValue($societa);
		$myField->setSize(20);
		$detail->addField($myField);
		
		$myField = new wi400InputText("TABELLA");
		$myField->setLabel("Nome tabella");
		$myField->setReadonly(true);
		$myField->setValue($tabella);
		$myField->setSize(20);
		$detail->addField($myField);
		
		$detail->dispose();
		
		echo "<br/>";
		
		$miaLista = new wi400List($azione."_LIST", true);
		$miaLista->setSelection("MULTIPLE");
		
		$miaLista->setFrom("ZTABTABE");
		$miaLista->setOrder("TABELLA");
		$miaLista->setWhere("TABELLA = '$tabella'");
	
		$mod_col = new wi400Column("MODIFICA", "Modifica", "", "CENTER");
		$mod_col->setDecorator("ICONS");
		$mod_col->setDefaultValue("MODIFICA");
		$mod_col->setActionListId($azione."_MOD");
	
		$miaLista->setCols(array(
				$mod_col,
				new wi400Column("SOCIETA", "Società"),
				new wi400Column("TABELLA", "Tabella"),
				new wi400Column("ELEMENTO", "Elemento"),
				new wi400Column("ELEMENTO2", "Tipo elemento"),
				new wi400Column("VALORE", "Descrizione"),
				new wi400Column("VALORE1", "Tipo oggetto"),
				new wi400Column("VALORE2", "Formato Elemento"),
				new wi400Column("VALORE3", "Lunghezza"),
				new wi400Column("SOCIETA", "Società")
		));
	
		$miaLista->addKey("SOCIETA");
		$miaLista->addKey("TABELLA");
		$miaLista->addKey("ELEMENTO");
		$miaLista->addKey("ELEMENTO2");
		$miaLista->addKey("VALORE");
		$miaLista->addKey("VALORE1");
		$miaLista->addKey("VALORE2");
		$miaLista->addKey("VALORE3");
		
		$mioFiltro = new wi400Filter("ELEMENTO","Elemento","STRING");
		$mioFiltro->setId("ELEMENTO");
		$mioFiltro->setFast(true);
		$miaLista->addFilter($mioFiltro);
		
		$mioFiltro = new wi400Filter("TABELLA","Tabella","STRING");
		$mioFiltro->setId("TABELLA");
		$mioFiltro->setFast(true);
		$miaLista->addFilter($mioFiltro);
	
		// Dettaglio parametro
		$action = new wi400ListAction();
		
		$action->setAction($azione);
		$action->setForm("NUOVO");
		$action->setLabel("Nuovo");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
	
		// Modifica parametro
		$action = new wi400ListAction();
		$action->setId($azione."_MOD");
		$action->setAction($azione);
		$action->setForm("MODIFICA");
		$action->setLabel("Modifica");
		$action->setTarget("WINDOW");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		// Elimina 
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("ELIMINA");
		$action->setLabel("Elimina");
		$action->setSelection("MULTIPLE");
		$miaLista->addAction($action);
	
		listDispose($miaLista);
	
		echo "<br/>";
	
		$button = new wi400InputButton("NUOVO_BUTTON");
		$button->setLabel("Nuovo elemento di tabella");
		$button->setAction($azione);
		$button->setForm("NUOVO");
		$button->setTarget("WINDOW");
		$button->setButtonClass("ccq-button-active color");
		$button->dispose();
	}else if(in_array($form, array("NUOVO", "MODIFICA"))) {
		$detail = new wi400Detail($azione."_NEW_MOD", !$isFromHistory);
		if(isset($key)) 
			$detail->setSource($key);
		
		$myField = new wi400InputText("SOCIETA");
		$myField->setLabel("Societa");
		$myField->setReadonly(true);
		$myField->setValue($societa);
		$myField->setSize(20);
		$detail->addField($myField);
		
		$myField = new wi400InputText("TABELLA");
		$myField->setLabel("Nome tabella");
		$myField->setReadonly(true);
		$myField->setValue($tabella);
		$myField->addValidation("required");
		$myField->setSize(20);
		$detail->addField($myField);
		
		$myField = new wi400InputText("ELEMENTO");
		$myField->setLabel("Nome elemento");
		$myField->addValidation("required");
		$myField->setSize(55);
		$detail->addField($myField);
		
		$mySelect = new wi400InputSelect("ELEMENTO2");
		$mySelect->setLabel("Tipo elemento");
		$mySelect->addOption("classe", "classe");
		$mySelect->addOption("funzione", "funzione");
		$detail->addField($mySelect);
		
		$myField = new wi400InputText("VALORE");
		$myField->setLabel("Descrizione");
		$myField->addValidation("required");
		$myField->setMaxLength(50);
		$detail->addField($myField);
		
		$myField = new wi400InputText("VALORE1");
		$myField->setLabel("Tipo oggetto");
		$detail->addField($myField);
		
		$mySelect = new wi400InputSelect("VALORE2");
		$mySelect->setLabel("Formato elemento");
		$mySelect->addOption("string", "string");
		$mySelect->addOption("integer", "integer");
		$mySelect->addOption("date", "date");
		$mySelect->addOption("time", "time");
		$detail->addField($mySelect);
		
		$myField = new wi400InputText("VALORE3");
		$myField->setLabel("Lunghezza");
		$myField->setMask("0123456789");
		$detail->addField($myField);
		
		$button = new wi400InputButton("SALVA_BUTTON");
		$button->setLabel("Salva");
		$button->setAction($azione);
		$button->setForm($salva_form);
		$button->setValidation(true);
		$detail->addButton($button);
		
		$button = new wi400InputButton("CANCEL_BUTTON");
		$button->setLabel("Annulla");
		$button->setAction("CLOSE");
		$button->setForm("CLOSE_LOOKUP");
		$detail->addButton($button);
		
		$detail->dispose();
	}