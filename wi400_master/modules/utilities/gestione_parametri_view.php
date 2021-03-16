<?php

	echo '<link rel="stylesheet" type="text/css" href="themes/common/css/button.css"  media="screen">';
	echo "	<style>
				.ccq-button-active.color {
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
		
		$miaLista->setFrom("ZTABTABE");
		if(isset($_SESSION['DA_ABILITAZIONI'])) {
			$miaLista->setWhere("TABELLA='{$_SESSION['DA_ABILITAZIONI']}'");
		}
	
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
			new wi400Column("SOCIETA", "Società"),
			new wi400Column("TEMPLATE", "Template"),
			new wi400Column("DEFAULT", "Default"),
			new wi400Column("MULTI", "Multi"),
		));
	
		$miaLista->addKey("SOCIETA");
		$miaLista->addKey("ELEMENTO");
		$miaLista->addKey("ELEMENTO2");
		$miaLista->addKey("VALORE");
		$miaLista->addKey("VALORE1");
		$miaLista->addKey("VALORE2");
		$miaLista->addKey("VALORE3");
		$miaLista->addKey("TABELLA");
		$miaLista->addKey("DEFAULT");
		$miaLista->addKey("TEMPLATE");
		$miaLista->addKey("MULTI");
		
		$mioFiltro = new wi400Filter("ELEMENTO","Elemento","STRING");
		$mioFiltro->setId("ELEMENTO");
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
	}else if(in_array($form, array("NUOVO", "MODIFICA"))) {
		$detail = new wi400Detail($azione."_NEW_MOD", !$isFromHistory);
		if(isset($key)) 
			$detail->setSource($key);
		
		//showArray($key);
		
		//$myField = getFieldSocieta();
		$myField = new wi400InputText("SOCIETA");
		$myField->setLabel("Società");
		$myField->setSize(4);
		$myField->setMaxLength(4);
		$myField->setReadonly(true);
		$myField->setValue("000");
		$detail->addField($myField);
		
		//Tabella
		$myField = new wi400InputText("TABELLA");
		$myField->setLabel("Tabella");
		//$myField->setSize();
		$myField->setMaxLength(20);
		//$myField->setValue("PARMAZI");
		$myField->setReadonly($mod);
		$detail->addField($myField);
		
		$myField = new wi400InputText("ELEMENTO");
		$myField->setLabel("Nome elemento");
		$myField->setSize(55);
		/*$decodeParameters = array(
				'TYPE'=> 'common',
				'COLUMN' => 'DESCRIZIONE',
				'TABLE_NAME' => 'FAZISIRI',
				'KEY_FIELD_NAME' => 'AZIONE',
				'AJAX' => true,
				'COMPLETE' => true,
				'COMPLETE_MIN' => 2,
				'COMPLETE_MAX_RESULT' => 15
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_AZIONI");
		$myLookUp->addField("ELEMENTO");
		$myField->setLookUp($myLookUp);*/
		$detail->addField($myField);
		
		//Tipo elemento
		$mySelect = new wi400InputSelect("ELEMENTO2");
		$mySelect->setLabel("Tipo elemento");
		$mySelect->addOption("classe", "classe");
		$mySelect->addOption("funzione", "funzione");
		$detail->addField($mySelect);
		
		//Descrizione
		$myField = new wi400InputText("VALORE");
		$myField->setLabel("Descrizione");
		$myField->setMaxLength(50);
		$detail->addField($myField);
		
		//Tipo oggetto
		$myField = new wi400InputText("VALORE1");
		$myField->setLabel("Tipo oggetto");
		$myField->setOnChange("risottomettiForm('".$form."&CHANGE_PARAM=si');");
		$detail->addField($myField);
		
		//Formato elemento
		$mySelect = new wi400InputSelect("VALORE2");
		$mySelect->setLabel("Formato elemento");
		$mySelect->addOption("string", "string");
		$mySelect->addOption("integer", "integer");
		$mySelect->addOption("date", "date");
		$mySelect->addOption("time", "time");
		$mySelect->setOnChange("risottomettiForm('".$form."&CHANGE_PARAM=si');");
		$detail->addField($mySelect);
		
		//Lunghezza
		$myField = new wi400InputText("VALORE3");
		$myField->setLabel("Lunghezza");
		$myField->setMask("0123456789");
		$myField->setOnChange("risottomettiForm('".$form."&CHANGE_PARAM=si');");
		$detail->addField($myField);
		
		//Template
		$mySelect = new wi400InputSelect("TEMPLATE");
		$mySelect->setLabel("Template");
		$mySelect->setFirstLabel("---");
		foreach($template as $file) {
			$file_arr = explode("_", $file);
			$pathinfo = pathinfo($file);
			$mySelect->addOption($file_arr[0], $pathinfo['filename']);
		}
		$mySelect->setOnChange("risottomettiForm('".$form."&CHANGE_PARAM=si');");
		$detail->addField($mySelect);
		
		//Campo multiplo
		$myField = new wi400InputCheckbox("MULTI");
		$myField->setLabel("Multi valore");
		$myField->setValue('1');
		$myField->setChecked($key['MULTI'] ? true : false);
		$detail->addField($myField);
		
		//Default
		$noTemplate = false;
		if($key['TEMPLATE']) {
			//$funzione = 'wi400_template_'.$key['TEMPLATE']."_lookup";
			
			//if(function_exists($funzione)) {
			$myField = getTemplateField($key['TEMPLATE'], 'DEFAULT');
			$myField->setLabel("Default");
			$myField->removeValidation('required');
			$detail->addField($myField);
				
				/*$myField = getTemplateField('fornitore_lookup', 'FORNITORE');
				$detail->addField($myField);*/
			/*}else {
				echo "la funzione $funzione non esiste<br/>";
				$noTemplate = true;
			}*/
		}
		if(!$key['TEMPLATE'] || $noTemplate) {
			$myField = getFieldFromParam(false, $key);
			$myField->setId("DEFAULT");
			$myField->setLabel("Default");
			$detail->addField($myField);
		}
		
		
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