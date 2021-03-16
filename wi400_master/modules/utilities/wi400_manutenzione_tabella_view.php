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
		
		$button = new wi400InputButton("DEFINIZIONE_PARAM");
		$button->setLabel("Definizione parametro");
		$button->setAction($azione);
		$button->setForm("DEFINIZIONE_PARAM");
		$button->setTarget('WINDOW', 700, 600);
		$detail->addButton($button);
		
		$detail->dispose();
		
		echo "<br/>";
		
		$miaLista = new wi400List($azione."_LIST", true);
		$miaLista->setSelection("MULTIPLE");
		
		$miaLista->setFrom("ZTABTABE");
		$miaLista->setOrder("TABELLA");
		$miaLista->setWhere("TABELLA = '$tabella' AND TIPO<>'D'");
	
		$mod_col = new wi400Column("MODIFICA", "Modifica", "", "CENTER");
		$mod_col->setDecorator("ICONS");
		$mod_col->setDefaultValue("MODIFICA");
		$mod_col->setActionListId($azione."_MOD");
	
		$miaLista->setCols(array(
				$mod_col,
				new wi400Column("ELEMENTO", "Elemento"),
				new wi400Column("VALORE", "Descrizione"),
		));
	
		$miaLista->addKey("SOCIETA");
		$miaLista->addKey("TABELLA");
		$miaLista->addKey("ELEMENTO");
		$miaLista->addKey("VALORE");
		
		$mioFiltro = new wi400Filter("ELEMENTO","Elemento","STRING");
		$mioFiltro->setId("ELEMENTO");
		$mioFiltro->setFast(true);
		$miaLista->addFilter($mioFiltro);
		
		$mioFiltro = new wi400Filter("VALORE", "Descrizione","STRING");
		$mioFiltro->setId("VALORE");
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
	}else if(in_array($form, array("NUOVO", "MODIFICA", "DEFINIZIONE_PARAM"))) {
		$detail = new wi400Detail($azione."_NEW_MOD", !$isFromHistory);
		if(isset($key)) 
			$detail->setSource($key);
		
		$fields = array();
		
		if($form == "DEFINIZIONE_PARAM") {
			$myField = new wi400InputText("SOCIETA");
			$myField->setLabel("Societa");
			$myField->setReadonly(true);
			$myField->setValue($societa);
			$myField->setSize(20);
			$fields['SOCIETA'] = $myField;
			
			$myField = new wi400InputText("TABELLA");
			$myField->setLabel("Nome tabella");
			$myField->setReadonly(true);
			$myField->setValue($tabella);
			$myField->addValidation("required");
			$myField->setSize(20);
			$fields['TABELLA'] = $myField;
			
			$mySelect = new wi400InputSelect("ELEMENTO2");
			$mySelect->setLabel("Tipo elemento");
			$mySelect->addOption("classe", "classe");
			$mySelect->addOption("funzione", "funzione");
			$fields['ELEMENTO2'] = $mySelect;
			
			$myField = new wi400InputText("VALORE1");
			$myField->setLabel("Tipo oggetto");
			$fields['VALORE1'] = $myField;
			
			$mySelect = new wi400InputSelect("VALORE2");
			$mySelect->setLabel("Formato elemento");
			$mySelect->addOption("string", "string");
			$mySelect->addOption("integer", "integer");
			$mySelect->addOption("date", "date");
			$mySelect->addOption("time", "time");
			$fields['VALORE2'] = $mySelect;
			
			$myField = new wi400InputText("VALORE3");
			$myField->setLabel("Lunghezza");
			$myField->setMask("0123456789");
			$fields['VALORE3'] = $myField;
		}else {
			$myField = new wi400InputText("ELEMENTO");
			$myField->setLabel("Nome elemento");
			$myField->addValidation("required");
			$myField->setSize(55);
			$fields['ELEMENTO'] = $myField;
			
			$myField = new wi400InputText("VALORE");
			$myField->setLabel("Descrizione");
			$myField->addValidation("required");
			$myField->setMaxLength(50);
			$fields['VALORE'] = $myField;
		}
		
		/*if($form == "DEFINIZIONE_PARAM") {
			unset($fields['ELEMENTO']);
			unset($fields['VALORE']);
		}else {
			
		}*/
		
		$detail->setFields($fields);
		
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
	}else if($form == "DEFINIZIONE_PARAM") {
		
	}
	
	
	