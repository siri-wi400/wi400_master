<?php

	$spacer = new wi400Spacer();

	if(in_array($actionContext->getForm(), array("DEFAULT", "LIBERE", "CLASSICHE"))) {
		// GERARCHIE LIBERE
		
		$searchAction = new wi400Detail($azione.'_LIBERE_SRC');
		$searchAction->setTitle('Gerarchie Libere - Parametri');
		$searchAction->isEditable(true);
	
		// Gerarchia
		$myField = new wi400InputText('GERARCHIA_LIB');
		$myField->setLabel("Gerarchia libera");
//		$myField->addValidation('required');
		$myField->setCase("UPPER");
		$myField->setMaxLength(3);
		$myField->setSize(3);
		if($actionContext->getForm()=="CLASSICHE")
			$myField->setValue("");
		else
			$myField->setValue($gerarchia_src);
//		$myField->setReadonly(true);
/*
		$from = "fshmappr o, LATERAL (
			SELECT rrn ( o ) AS NREL
			FROM fshmappr i
			WHERE o.shmcda = i.shmcda and o.shmger = i.shmger and o.shmcdf = i.shmcdf and 
				digits(shmava)!!digits(shmmva)!!digits(shmgva) <= {$_SESSION['data_validita']}
			FETCH FIRST ROW ONLY ) AS x,
		ftabgen, fmdxanae";
		
		$where = "rrn ( o ) = x.NREL and shmsta = '1' and tabsig = '0200' and tabcod = shmger and shmcda = mdxcda  and mdxm03 = '10 '
			group by shmger, tabrec";

		$decodeParameters = array(
			'TYPE' => 'common',
			'TABLE_NAME' => $from,
//			'COLUMN' => 'substr(TABREC, 1, 20) as DES_GER',
			'COLUMN' => 'TABREC',
			'KEY_FIELD_NAME' => 'SHMGER',
			'RETURN_COLUM' => "DES_GER",
			'FILTER_SQL' => $where,
			'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_GERARCHIE");
		$myLookUp->addField("GERARCHIA");
		$myField->setLookUp($myLookUp);
*/
		$decodeParameters = array(
			'TYPE' => 'table',
			'TABLE' => '0200',
			'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_TABELLA");
		$myLookUp->addParameter("TABELLA","0200");
		$myField->setLookUp($myLookUp);
		
		$searchAction->addField($myField);
		
		// Ricarica
		$myButton = new wi400InputButton('RELOAD_BUTTON');
		$myButton->setLabel("Ricarica");
		$myButton->setAction($azione);
		$myButton->setForm("LIBERE");
		$myButton->setValidation(true);
		$searchAction->addButton($myButton);
		
		// Esporta
		$myButton = new wi400InputButton('EXPORT_BUTTON');
		$myButton->setLabel("Esporta");
		$myButton->setAction($azione);
		$myButton->setForm("EXPORT_LIB_SEL");
		$myButton->setTarget("WINDOW");
		$myButton->setConfirmMessage("Esportare?");
		$myButton->setValidation(true);
		$searchAction->addButton($myButton);
		
		$searchAction->dispose();
		
		$spacer->dispose();
		
		// GERARCHIE CLASSICHE
		
		$searchAction = new wi400Detail($azione.'_CLASSICHE_SRC');
		$searchAction->setTitle('Gerarchie Classiche - Parametri');
		$searchAction->isEditable(true);
		
		// Gerarchia
		$mySelect = new wi400InputSelect('GERARCHIA_CLS');
		$mySelect->setLabel("Gerarchia Classica");
		$mySelect->setFirstLabel("Seleziona...");
		$mySelect->setOptions($gerarchie_classiche);
		if($actionContext->getForm()=="LIBERE")
			$myField->setValue("");
		else
			$mySelect->setValue($ger_cls_src);
		$searchAction->addField($mySelect);
		
		// Ricarica
		$myButton = new wi400InputButton('RELOAD_BUTTON');
		$myButton->setLabel("Ricarica");
		$myButton->setAction($azione);
		$myButton->setForm("CLASSICHE");
		$myButton->setValidation(true);
		$searchAction->addButton($myButton);

		// Esporta
		$myButton = new wi400InputButton('EXPORT_BUTTON');
		$myButton->setLabel("Esporta");
		$myButton->setAction($azione);
		$myButton->setForm("EXPORT_CLS_SEL");
		$myButton->setTarget("WINDOW");
		$myButton->setConfirmMessage("Esportare?");
		$myButton->setValidation(true);
		$searchAction->addButton($myButton);
		
		$searchAction->dispose();
	
		// Aggiunta albero delle gerarchie
		if($actionContext->getForm()=="LIBERE") {
			$spacer->dispose();
	
			$treeMerc = new wi400Tree("TREE_GERC_LIB");
			$treeMerc->setRootFunction("struttura_gerarchica_enti");
			$treeMerc->setExtraParams(array("GERARCHIA" => $gerarchia_src));
//			$treeMerc->setSelectionLevels(array(false, true, true, true, true, true));
			$treeMerc->setSelectionLevels(array(false, false, false, false, false, false));
			$treeMerc->dispose();
		}
		else if($actionContext->getForm()=="CLASSICHE") {
			$spacer->dispose();
	
			$treeMerc = new wi400Tree("TREE_GERC_CLS");
			$treeMerc->setRootFunction("gerarchie_classiche");
			$treeMerc->setExtraParams(array("TIPO" => $ger_cls_src));
//			$treeMerc->setSelectionLevels(array(false, true, true, true, true, true));
			$treeMerc->setSelectionLevels(array(false, false, false, false, false, false));
			$treeMerc->dispose();
		}
	}
	else if(in_array($actionContext->getForm(), array("EXPORT_LIB_SEL", "EXPORT_CLS_SEL"))) {
		$selections['TITLE'] = "Tipo file";
		
		$selections['BODY'][] = array("VAL" => "excel5", "DES" => "Excel (XLS)");
		$selections['BODY'][] = array("VAL" => "excel2007", "DES" => "Excel 2007 (XLSX)");
//		$selections['BODY'][] = array("VAL" => "csv", "DES" => "Csv");
		
		$export->viewDefault("", $selections);
		
		$myButton = new wi400InputButton('PRINT_BUTTON');
		$myButton->setLabel("Esporta");
		$myButton->setAction($azione);
		if($actionContext->getForm()=="EXPORT_LIB_SEL")
			$myButton->setForm("EXPORT_LIB");
		else 
			$myButton->setForm("EXPORT_CLS");
		$buttonsBar[] = $myButton;
			
		$myButton = new wi400InputButton("CLOSE_BUTTON");
		$myButton->setScript('closeLookUp()');
		$myButton->setLabel("Chiudi");
		$buttonsBar[] = $myButton;
	}
	else if(in_array($actionContext->getForm(), array("EXPORT_LIB", "EXPORT_CLS"))) {
		$myButton = new wi400InputButton("CLOSE_BUTTON");
		$myButton->setScript('closeLookUp()');
		$myButton->setLabel("Chiudi");
		$buttonsBar[] = $myButton;
		
		downloadDetail($TypeImage, $filename, $temp, "Esportazione completata");
	}