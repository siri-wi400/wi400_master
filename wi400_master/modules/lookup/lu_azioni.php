<?php

	if($actionContext->getForm()=="DEFAULT" || $actionContext->getForm()=="EXPORT_LIST") {
		if($actionContext->getForm()=="DEFAULT")
			$miaLista = new wi400List("AZIONIS", true);
		else if($actionContext->getForm()=="EXPORT_LIST")
			$miaLista = new wi400List("AZIONI_EXP", true);
	
		$miaLista->setFrom("FAZISIRI");
		$AND="";
		$sql='';
		if (isset($_REQUEST["FILTER_SQL"]) AND $_REQUEST["FILTER_SQL"] != ""){
			$sql .= $_REQUEST["FILTER_SQL"];
			$AND = " AND ";
		}

		if (isset($_REQUEST["TIPO"]) AND ($_REQUEST["TIPO"]!=""))
		{
			$tipo = explode(";", $_REQUEST["TIPO"]);
			if (count($tipo)==1) {
					$sql = $sql."$AND TIPO='".$_REQUEST["TIPO"]."'";		
			} elseif (count($tipo)>1) {
					$sql = $sql."$AND TIPO IN ('".implode("' , '", $tipo)."')";
			}
		}
		
		$miaLista->setWhere($sql);
		
		$miaLista->setOrder("AZIONE ASC");
		
		$miaLista->setShowMenu(true);
		$miaLista->setCanExport(false);
		$miaLista->setCanReload(false);
		
		if($actionContext->getForm()=="DEFAULT") {
			//$miaLista->setPassKey('codazi');
			$miaLista->setPassKey(true);
			$miaLista->setPassDesc("DESCRIZIONE");
			$miaLista->setSelection('SINGLE');
		}
		
		$miaLista->setCols(array(
			new wi400Column("AZIONE",_t('CODE')),
			new wi400Column("DESCRIZIONE",_t('DESCRIPTION')),
			new wi400Column("TIPO",_t('ACTION_TYPE')),
			new wi400Column("MODULO","Modulo"),				
			)
		);
		
		$mioFiltro = new wi400Filter("DESCRIZIONE",_t('DESCRIPTION'),"STRING");
		$mioFiltro->setFast(true);
		$mioFiltro->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$miaLista->addFilter($mioFiltro);
		
		$mioFiltro = new wi400Filter("AZIONE",_t('CODE'),"STRING");
		$mioFiltro->setFast(true);
		$miaLista->addFilter($mioFiltro);

		$mioFiltro = new wi400Filter("MODULO","Modulo","STRING");
		$mioFiltro->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$mioFiltro->setFast(true);
		$miaLista->addFilter($mioFiltro);
		
		// Filtri avanzati
		$tipi_azioni = array(
			"A" => _t('ACTION_TYPE_MENU'),
			"M" => _t('ACTION_TYPE_COLLECT'),
			"N" => _t('ACTION_TYPE_SIMPLY'),
			"B" => _t('ACTION_TYPE_BATCH')
		);
		
		$mioFiltro = new wi400Filter("TIPO",_t('ACTION_TYPE'),"SELECT","");
		$filterValues = array();
		foreach($tipi_azioni as $key => $val)
			$filterValues["TIPO='$key'"] = $val;
//		echo "FILTER TIPO:<pre>"; print_r($filterValues); echo "</pre>";
		$mioFiltro->setSource($filterValues);
		$miaLista->addFilter($mioFiltro);
		
		// aggiunta chiavi di riga
		$miaLista->addKey("AZIONE");
		
		if($actionContext->getForm()=="EXPORT_LIST") {
			$miaLista->setSelection('MULTIPLE');
			
			$myButton = new wi400InputButton("EXPORT_BUTTON");
			$myButton->setAction("LU_AZIONI");
			$myButton->setForm("EXPORT_SEL");
			$myButton->setLabel(_t('ACTION_EXPORT_DETAIL'));
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
//			$selections['BODY'][] = array("VAL" => "csv", "DES" => "Csv");
		
//			$export->viewDefault($targets, $selections, '', "AZIONI_EXP");
			$export->viewDefault("AZIONI_EXP", $targets, $selections);
/*		
			$myButton = new wi400InputButton("CANCEL_BUTTON");
			$myButton->setScript('history.back()');
			$myButton->setLabel("Indietro");
			$buttonsBar[] = $myButton;
*/
			$myButton = new wi400InputButton("EXPORT_BUTTON");
			$myButton->setAction("LU_AZIONI");
			$myButton->setForm("EXPORT_LIST");
			$myButton->setLabel(_t('INDIETRO'));
			$buttonsBar[] = $myButton;
		
			$myButton = new wi400InputButton("EXPORT_BUTTON");
			$myButton->setAction("LU_AZIONI");
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
		$myButton->setAction("LU_AZIONI");
		$myButton->setForm("EXPORT_SEL");
		$myButton->setLabel(_t('INDIETRO'));
		$buttonsBar[] = $myButton;
	
		$myButton = new wi400InputButton("CANCEL_BUTTON");
		$myButton->setScript('closeLookUp()');
		$myButton->setLabel(_t('CHIUDI'));
		$buttonsBar[] = $myButton;
		
		$temp = $export->getTemp();
		$TypeImage = $export->getTypeImage();

		downloadDetail($TypeImage, $filename, $temp, _t('ESPORTAZIONE_COMPLETATA'));
	}