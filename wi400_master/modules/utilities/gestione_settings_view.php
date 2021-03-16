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
		$miaLista = new wi400List($azione."_LIST", true);
		$miaLista->setSelection("MULTIPLE");
		
		$miaLista->setField("distinct(a.PARAMETRO), a.*, case when b.parametro<>'' then 1 else 0 end as EXIT");
		$miaLista->setFrom("$tabSettings a left join $tabValori b on a.parametro=b.parametro");
		//$miaLista->setWhere("A.STATO='1'");
		$miaLista->setOrder("EXIT, a.PARAMETRO");
		
		//echo $miaLista->getSql();
	
		$mod_col = new wi400Column("MODIFICA", "Modifica", "", "CENTER");
		$mod_col->setDecorator("ICONS");
		$mod_col->setDefaultValue("MODIFICA");
		$mod_col->setActionListId($azione."_MOD");
		
		$stato_col = new wi400Column("STATO", "Stato", "", "CENTER");
		$stato_col->setDecorator("YES_NO_ICO");
		
		$exit_col = new wi400Column("EXIT", "Valore", "", "CENTER");
		$exit_col->setDecorator("YES_NO_ICO");
	
		$miaLista->setCols(array(
				$mod_col,
				new wi400Column("PARAMETRO", "Parametro"),
				new wi400Column("PARAM_DES", "Descrizione"),
				new wi400Column("TIPO", "Tipo parametro"),
				new wi400Column("OGGETTO", "Tipo oggetto"),
				new wi400Column("FORMATO", "Formato"),
				new wi400Column("LUNGHEZZA", "Lunghezza", "", "right"),
				$stato_col,
				$exit_col
		));
	
		$miaLista->addKey("PARAMETRO");
		$miaLista->addKey("TIPO");
		$miaLista->addKey("OGGETTO");
		$miaLista->addKey("FORMATO");
		$miaLista->addKey("LUNGHEZZA");
		$miaLista->addKey("STATO");
		
		$mioFiltro = new wi400Filter("PARAMETRO", "Parametro", "STRING");
		$mioFiltro->setKey("a.PARAMETRO");
		$mioFiltro->setCaseSensitive("CASE_SENSITIVE_DB");
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
		$button->setLabel("Nuovo parametro");
		$button->setAction($azione);
		$button->setForm("NUOVO");
		$button->setTarget("WINDOW");
		$button->setButtonClass("ccq-button-active color");
		$button->dispose();
		
		$button = new wi400InputButton("IMPORT_BUTTON");
		$button->setLabel("Import settings");
		$button->setAction($azione);
		$button->setForm("IMPORT_SETTINGS");
		$button->setButtonClass("ccq-button-active color");
		$button->setConfirmMessage("Sei sicuro di voler continuare? Verranno eliminati tutti i parametri salvati.");
		$button->dispose();
		
		echo "<br/><br/><br/><br/>";
		
		//Crea la variabile $settings ** adesso crea la variabile $sett per le prove
		//createVariableSettings();
		
		echo "<br/><br/><br/>";
		
		//showArray($_SESSION);
	}else if(in_array($form, array("NUOVO", "MODIFICA"))) {
		$detail = new wi400Detail($azione."_NEW_MOD", !$isFromHistory);
		if(isset($key)) 
			$detail->setSource($key);
		
		$myField = new wi400InputText("PARAMETRO");
		$myField->setLabel("Nome parametro");
		$myField->setSize(55);
		$detail->addField($myField);
		
		$mySelect = new wi400InputSelect("TIPO");
		$mySelect->setLabel("Tipo parametro");
		$mySelect->addOption("classe", "classe");
		$mySelect->addOption("funzione", "funzione");
		$detail->addField($mySelect);
		
		$myField = new wi400InputText("DESCRIZIONE");
		$myField->setLabel("Descrizione");
		$myField->setMaxLength(50);
		$detail->addField($myField);
		
		$myField = new wi400InputText("OGGETTO");
		$myField->setLabel("Tipo oggetto");
		$detail->addField($myField);
		
		$mySelect = new wi400InputSelect("FORMATO");
		$mySelect->setLabel("Formato");
		$mySelect->addOption("string", "string");
		$mySelect->addOption("integer", "integer");
		$mySelect->addOption("date", "date");
		$mySelect->addOption("time", "time");
		$detail->addField($mySelect);
		
		$myField = new wi400InputText("LUNGHEZZA");
		$myField->setLabel("Lunghezza");
		$myField->setMask("0123456789");
		$detail->addField($myField);
		
		$myField = new wi400InputSwitch("STATO");
		$myField->setLabel("Stato");
		$myField->setOnLabel(_t('VALIDO'));
		$myField->setOffLabel(_t('ANULLATO'));
		$myField->setChecked($key['STATO'] ? true : false);
		$myField->setValue("S");
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
	}else if($form == "IMPORT_SETTINGS") {
		
		echo gettype($parameters['debug']);
		
		showArray($parameters);
		
		
	}