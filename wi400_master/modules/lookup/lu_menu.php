<?php
	
	if($actionContext->getForm()=="DEFAULT" || $actionContext->getForm()=="EXPORT_LIST") {
		if($actionContext->getForm()=="DEFAULT")
			$miaLista = new wi400List("MENUS", true);
		else if($actionContext->getForm()=="EXPORT_LIST")
			$miaLista = new wi400List("MENU_EXP", true);
		
		$miaLista->setFrom("FMNUSIRI");
		$miaLista->setOrder("MENU ASC");
		$sql="";
		if (isset($_REQUEST["FILTER_SQL"]) AND $_REQUEST["FILTER_SQL"] != ""){
			$sql = $_REQUEST["FILTER_SQL"];
		} else {
			$sql ="";
		}
		$miaLista->setWhere($sql);
		
		$miaLista->setShowMenu(false);
		
		if($actionContext->getForm()=="DEFAULT") {
			//$miaLista->setPassKey('codazi');
			$miaLista->setPassKey(true);
			$miaLista->setSelection('SINGLE');
		}
		
		$miaLista->setCols(array(
			new wi400Column("MENU","Codice"),
			new wi400Column("DESCRIZIONE","Descrizione")
		));
	
		// aggiunta chiavi di riga
		$mioFiltro = new wi400Filter("MENU","Codice","STRING");
		$mioFiltro->setFast(true);
		$mioFiltro->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$miaLista->addFilter($mioFiltro);
		
		$mioFiltro = new wi400Filter("DESCRIZIONE","Descrizione","STRING");
		$mioFiltro->setFast(true);
		$mioFiltro->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$miaLista->addFilter($mioFiltro);
		
		$miaLista->addKey("MENU");
		
		if($actionContext->getForm()=="EXPORT_LIST") {
			$miaLista->setSelection('MULTIPLE');
				
			$myButton = new wi400InputButton("EXPORT_BUTTON");
			$myButton->setAction("LU_MENU");
			$myButton->setForm("EXPORT_SEL");
			$myButton->setLabel(_t('EXPORT'));
			$buttonsBar[] = $myButton;
		}
		
		$myButton = new wi400InputButton("CANCEL_BUTTON");
		$myButton->setScript('closeLookUp()');
		$myButton->setLabel(_t('CANCEL'));
		$buttonsBar[] = $myButton;
		
		// Verifico se mi è stato passato in $_REQUEST un eventuale onchange
		if (isset($_REQUEST["ONCHANGE"]) AND $_REQUEST["ONCHANGE"] != ""){
			$str = addslashes($_REQUEST["ONCHANGE"]);
			$miaLista->setPassKeyJsFunction($str);
		}
		
		listDispose($miaLista);
	}
	else if	($actionContext->getForm() == "EXPORT_SEL") {
		$targets['BODY'][] = array("VAL" => "ALL", "DES" => _t('LABEL_ALL'), "CHECK" => true);
		$targets['BODY'][] = array("VAL" => "SELECTED", "DES" => _t('SELEZIONATI'));
		$targets['BODY'][] = array("VAL" => "PAGE", "DES" => _t('PAGINA_CORRENTE'));
		
		$selections['BODY'][] = array("VAL" => "excel5", "DES" => "Excel (XLS)");
		$selections['BODY'][] = array("VAL" => "excel2007", "DES" => "Excel 2007 (XLSX)", "CHECK" => true);
//		$selections['BODY'][] = array("VAL" => "csv", "DES" => "Csv");
	
//		$export->viewDefault($targets, $selections, '', "AZIONI_EXP");
		$export->viewDefault("MENU_EXP", $targets, $selections);
/*
		$myButton = new wi400InputButton("CANCEL_BUTTON");
		$myButton->setScript('history.back()');
		$myButton->setLabel("Indietro");
		$buttonsBar[] = $myButton;
*/
		$myButton = new wi400InputButton("EXPORT_BUTTON");
		$myButton->setAction("LU_MENU");
		$myButton->setForm("EXPORT_LIST");
		$myButton->setLabel(_t('INDIETRO'));
		$buttonsBar[] = $myButton;
	
		$myButton = new wi400InputButton("EXPORT_BUTTON");
		$myButton->setAction("LU_MENU");
		$myButton->setForm("EXPORT");
		$myButton->setLabel(_t('ESPORTA'));
		$buttonsBar[] = $myButton;
	
		$myButton = new wi400InputButton("CANCEL_BUTTON");
		$myButton->setScript('closeLookUp()');
		$myButton->setLabel(_t('ANNULLA'));
		$buttonsBar[] = $myButton;
	}
	else if ($actionContext->getForm() == "EXPORT") {
/*
		$myButton = new wi400InputButton("CANCEL_BUTTON");
		$myButton->setScript('history.back()');
		$myButton->setLabel("Indietro");
		$buttonsBar[] = $myButton;
*/
		$myButton = new wi400InputButton("EXPORT_BUTTON");
		$myButton->setAction("LU_MENU");
		$myButton->setForm("EXPORT_SEL");
		$myButton->setLabel(_t('INDIETRO'));
		$buttonsBar[] = $myButton;
	
		$myButton = new wi400InputButton("CANCEL_BUTTON");
		$myButton->setScript('closeLookUp()');
		$myButton->setLabel(_t('CHIUDI'));
		$buttonsBar[] = $myButton;
			
		$temp = $export->getTemp();
//		$TypeImage = $export->getTypeImage();
		$TypeImage = "zip";
	
		downloadDetail($TypeImage, $filename, $temp, _t('ESPORTAZIONE_COMPLETATA'));
	}