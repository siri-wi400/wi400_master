<?php

	$spacer = new wi400Spacer();
	
	if(in_array($actionContext->getForm(), array("LIST"))) {
		$ListDetail = new wi400Detail($azione."_".$actionContext->getForm()."_DET");
		$ListDetail->setColsNum(2);
		
		$c = 0;
		
		if(!empty($ambito_sel)) {
			$fieldDetail = new wi400Text("AMBITO");
			$fieldDetail->setLabel("Ambito");
			$fieldDetail->setValue(implode("<br>", $ambito_sel));
			$ListDetail->addField($fieldDetail);
			
			$c++;
		}
		
		if(!empty($area_fun_sel)) {
			$fieldDetail = new wi400Text("AREA_FUN");
			$fieldDetail->setLabel("Area Funzione");
			$fieldDetail->setValue(implode("<br>", $area_fun_sel));
			$ListDetail->addField($fieldDetail);
				
			$c++;
		}
		
		if(!empty($data_ini)) {
			$fieldDetail = new wi400Text("PERIODO");
			$fieldDetail->setLabel("Periodo");
			$fieldDetail->setValue("Dal $data_ini al $data_fin");
			$ListDetail->addField($fieldDetail);
			
			$c++;
		}
		
		if(!empty($tipo_segn_sel)) {
			$fieldDetail = new wi400Text("TIPO_SEGN");
			$fieldDetail->setLabel("Tipo Segnalazione");
			$fieldDetail->setValue(implode("<br>", $tipo_segn_sel));
			$ListDetail->addField($fieldDetail);
		
			$c++;
		}
		
		if(!empty($grp_err_sel)) {
			$fieldDetail = new wi400Text("GRP_ERR");
			$fieldDetail->setLabel("Gruppo Errore");
			$fieldDetail->setValue(implode("<br>", $grp_err_sel));
			$ListDetail->addField($fieldDetail);
		
			$c++;
		}
		
		if(!empty($gravita_sel)) {
			$fieldDetail = new wi400Text("GRAVITA");
			$fieldDetail->setLabel("Gravità");
			$fieldDetail->setValue(implode("<br>", $gravita_sel));
			$ListDetail->addField($fieldDetail);
				
			$c++;
		}
		
		if(!empty($cod_err_sel)) {
			$fieldDetail = new wi400Text("COD_ERR");
			$fieldDetail->setLabel("Codice Errore");
			$fieldDetail->setValue(implode("<br>", $cod_err_sel));
			$ListDetail->addField($fieldDetail);
		
			$c++;
		}
		
		if(in_array($des_evento_option, array("EMPTY", "NOT_EMPTY")) || (isset($des_evento) && $des_evento!="")) {
			$labelDetail = new wi400Text("DES_EVENTO");
			$labelDetail->setLabel("Descrizione Evento");
			$labelDetail->setValue(get_text_condition_des($des_evento_option, $des_evento));
			$ListDetail->addField($labelDetail);
			
			$c++;
		}
		
		if(in_array($des_agg_option, array("EMPTY", "NOT_EMPTY")) || (isset($des_agg) && $des_agg!="")) {
			$labelDetail = new wi400Text("DES_AGG");
			$labelDetail->setLabel("Descrizione Aggiuntiva");
			$labelDetail->setValue(get_text_condition_des($des_agg_option, $des_agg));
			$ListDetail->addField($labelDetail);
				
			$c++;
		}
		
		if($c===0) {
			$fieldDetail = new wi400Text("SELEZIONI");
			$fieldDetail->setLabel("Selezioni");
			$fieldDetail->setValue("TUTTE");
			$ListDetail->addField($fieldDetail);
		}
		
		$ListDetail->dispose();
		
		$spacer->dispose();
	}
	
	if($actionContext->getForm()=="DEFAULT") {
		$searchAction = new wi400Detail($azione."_SRC", true);
		$searchAction->setTitle($label);
		$searchAction->isEditable(true);
		$searchAction->setSaveDetail(true);
		
		// Ambito
		$myField = new wi400InputText('AMBITO');
		$myField->setLabel("Ambito");
		$myField->setShowMultiple(true);
		$myField->setSize(50);
		$myField->setMaxLength(50);
		$myField->setCase("UPPER");
		$myField->setValue($ambito_sel);
//		$myField->setOnChange("doSubmit('".$azione."', 'DEFAULT')");
		
		$decodeParameters = array(
			'TYPE' => 'common',
			'TABLE_NAME' => "FIFSLOG2",
			'COLUMN' => 'IFLAMB',
			'KEY_FIELD_NAME' => 'IFLAMB',
			'GROUP_BY' => "IFLAMB",
//			"AJAX" => true,
			"COMPLETE" => false
		);
		$myField->setDecode($decodeParameters);
				
		$myLookUp = new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE", "FIFSLOG2");
		$myLookUp->addParameter("CAMPO", "IFLAMB");
//		$myLookUp->addParameter("DESCRIZIONE", "IFLAMB");
		$myLookUp->addParameter("LU_FIELDS", "IFLAMB");
		$myLookUp->addParameter("LU_GROUP", "IFLAMB");
		$myLookUp->addParameter("LU_WHERE", "IFLAMB<>''");
//		$myLookUp->addParameter("ONCHANGE", "risottomettiForm('DEFAULT')");
//		$myLookUp->addField("AMBITO");
		$myField->setLookUp($myLookUp);
		
		$searchAction->addField($myField);
		
		// Area Funzione
		$myField = new wi400InputText('AREA_FUN');
		$myField->setLabel("Area Funzione");
		$myField->setShowMultiple(true);
		$myField->setSize(50);
		$myField->setMaxLength(50);
		//$myField->setCase("UPPER");
		$myField->setValue($area_fun_sel);
		
		// Limitare con onChange per Ambito
		
		$where_area_fun = "IFLFUN<>''";
		if(!empty($ambito_sel)) {
//			$where_area_fun .= " and IFLAMB in ('".implode("', '", $ambito_sel)."')";
		}
		
		$decodeParameters = array(
			'TYPE' => 'common',
			'TABLE_NAME' => "FIFSLOG2",
			'COLUMN' => 'IFLFUN',
			'KEY_FIELD_NAME' => 'IFLFUN',
			'FILTER_SQL' => $where_area_fun,
//			'WHERE_COND' => "IFLAMB in ('<@REQUEST(AMBITO)@>')",
			'GROUP_BY' => "IFLFUN",
//			"AJAX" => true,
			"COMPLETE" => false
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE", "FIFSLOG2");
		$myLookUp->addParameter("CAMPO", "IFLFUN");
//		$myLookUp->addParameter("DESCRIZIONE", "IFLFUN");
		$myLookUp->addParameter("LU_FIELDS", "IFLFUN");
		$myLookUp->addParameter("LU_GROUP", "IFLFUN");
		$myLookUp->addParameter("FILTER_SQL", $where_area_fun);
//		$myLookUp->addJsParameter("AMBITO");
//		$myLookUp->addParameter("LU_WHERE", "IFLAMB in ('<@REQUEST(AMBITO)@>')");
		$myField->setLookUp($myLookUp);
		
		$searchAction->addField($myField);
		
		// Data iniaziale
		$myField = new wi400InputText('DATA_INI');
		$myField->setLabel('Data iniziale');
		$myField->addValidation('date');
		if(!isset($data_ini))
			$myField->setValue(dateModelToView(date("Ymd")));
		else
			$myField->setValue($data_ini);
		$searchAction->addField($myField);
		
		// Data finale
		$myField = new wi400InputText('DATA_FIN');
		$myField->setLabel('Data finale');
		$myField->addValidation('date');
		if(!isset($data_fin))
			$myField->setValue(dateModelToView(date("Ymd")));
		else
			$myField->setValue($data_fin);
		$searchAction->addField($myField);
		
		// Tipo Segnalazione
		$myField = new wi400InputText('TIPO_SEGN');
		$myField->setLabel("Tipo Segnalazione");
		$myField->setShowMultiple(true);
		$myField->setSize(4);
		$myField->setMaxLength(4);
		$myField->setCase("UPPER");
		$myField->setValue($tipo_segn_sel);
		
		$decodeParameters = array(
			'TYPE' => 'common',
			'TABLE_NAME' => "FIFSLOG2",
			'COLUMN' => 'IFLTIP',
			'KEY_FIELD_NAME' => 'IFLTIP',
			'GROUP_BY' => "IFLTIP",
//			"AJAX" => true,
			"COMPLETE" => false
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE", "FIFSLOG2");
		$myLookUp->addParameter("CAMPO", "IFLTIP");
//		$myLookUp->addParameter("DESCRIZIONE", "IFLTIP");
		$myLookUp->addParameter("LU_FIELDS", "IFLTIP");
		$myLookUp->addParameter("LU_GROUP", "IFLTIP");
		$myLookUp->addParameter("LU_WHERE", "IFLTIP<>''");
		$myField->setLookUp($myLookUp);
		
		$searchAction->addField($myField);
		
		// Gruppo errori
		$myField = new wi400InputText('GRP_ERR');
		$myField->setLabel("Gruppo Errori");
		$myField->setShowMultiple(true);
		$myField->setSize(4);
		$myField->setMaxLength(4);
		$myField->setCase("UPPER");
		$myField->setValue($grp_err_sel);
		
		$decodeParameters = array(
			'TYPE' => 'common',
			'TABLE_NAME' => "FIFSLOG2",
			'COLUMN' => 'IFLGRP',
			'KEY_FIELD_NAME' => 'IFLGRP',
			'GROUP_BY' => "IFLGRP",
//			"AJAX" => true,
			"COMPLETE" => false
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE", "FIFSLOG2");
		$myLookUp->addParameter("CAMPO", "IFLGRP");
//		$myLookUp->addParameter("DESCRIZIONE", "IFLGRP");
		$myLookUp->addParameter("LU_FIELDS", "IFLGRP");
		$myLookUp->addParameter("LU_GROUP", "IFLGRP");
		$myLookUp->addParameter("LU_WHERE", "IFLGRP<>''");
		$myField->setLookUp($myLookUp);
		
		$searchAction->addField($myField);
		
		// Gravità
		$myField = new wi400InputText('GRAVITA');
		$myField->setLabel("Gravità");
		$myField->setShowMultiple(true);
		$myField->setSize(2);
		$myField->setMaxLength(2);
		$myField->setCase("UPPER");
		$myField->setValue($gravita_sel);
		
		$decodeParameters = array(
			'TYPE' => 'common',
			'TABLE_NAME' => "FIFSLOG2",
			'COLUMN' => 'IFLGVT',
			'KEY_FIELD_NAME' => 'IFLGVT',
			'GROUP_BY' => "IFLGVT",
//			"AJAX" => true,
			"COMPLETE" => false
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE", "FIFSLOG2");
		$myLookUp->addParameter("CAMPO", "IFLGVT");
//		$myLookUp->addParameter("DESCRIZIONE", "IFLGVT");
		$myLookUp->addParameter("LU_FIELDS", "IFLGVT");
		$myLookUp->addParameter("LU_GROUP", "IFLGVT");
		$myLookUp->addParameter("LU_WHERE", "IFLGVT<>''");
		$myField->setLookUp($myLookUp);
		
		$searchAction->addField($myField);
		
		// Codice Errore
		$myField = new wi400InputText('COD_ERR');
		$myField->setLabel("Codice Errore");
		$myField->setShowMultiple(true);
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$myField->setCase("UPPER");
		$myField->setValue($cod_err_sel);
		
		$decodeParameters = array(
			'TYPE' => 'common',
			'TABLE_NAME' => "FIFSLOG2 left join FIFSERRO on IFLCOD=IFECOD",
			'COLUMN' => 'IFEDES',
			'KEY_FIELD_NAME' => 'IFLCOD',
//			'GROUP_BY' => "IFLCOD",
//			"AJAX" => true
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE", "FIFSLOG2");
		$myLookUp->addParameter("LU_FROM", " left join FIFSERRO on IFLCOD=IFECOD");
		$myLookUp->addParameter("CAMPO", "IFLCOD");
		$myLookUp->addParameter("DESCRIZIONE", "IFEDES");
		$myLookUp->addParameter("LU_FIELDS", "IFLCOD, IFEDES");
		$myLookUp->addParameter("LU_GROUP", "IFLCOD, IFEDES");
		$myLookUp->addParameter("LU_WHERE", "IFLCOD<>''");
		$myField->setLookUp($myLookUp);
		
		$searchAction->addField($myField);
		
		// @todo Ricerca in stringa con selezione "Contenuto in" per campo IFLDES e IFLDE2 separatamente 
		// Descrizione Evento
		$myField = new wi400InputText('DES_EVENTO_SRC');
		$myField->setLabel("Descrizione Evento");
		$myField->setSelOption(true);
		$myField->setMaxLength(100);
		$myField->setSize(100);
		$myField->setValue($des_evento);
		$searchAction->addField($myField);
		
		// Descrizione Aggiuntiva
		$myField = new wi400InputText('DES_AGG_SRC');
		$myField->setLabel("Descrizione Aggiuntiva");
		$myField->setSelOption(true);
		$myField->setMaxLength(100);
		$myField->setSize(100);
		$myField->setValue($des_agg);
		$searchAction->addField($myField);
		
		$myButton = new wi400InputButton('SEARCH_BUTTON');
		$myButton->setLabel("Seleziona");
		$myButton->setAction($azione);
		$myButton->setForm("LIST");
		$myButton->setValidation(true);
		$searchAction->addButton($myButton);
		
		$searchAction->dispose();
	}
	else if($actionContext->getForm()=="LIST") {
		$miaLista = new wi400List($azione."_LIST", !$isFromHistory);
		
		$miaLista->setField("a.*, IFEDES, IFLDMO!!IFLHMO as DATA_ORA");
		$miaLista->setFrom("FIFSLOG2 a left join FIFSERRO b on IFLCOD=IFECOD");
		$miaLista->setWhere($where);
		$miaLista->setOrder("IFLDMO desc, IFLHMO desc");
		
//		echo "SQL: ".$miaLista->getSql()."<br>";
		
		$miaLista->setSelection("SINGLE");
		
		$file_col = new wi400Column("IFLFIL", "Nome File Flusso");
		$file_col->setActionListId("FILE_VIEW");
		
		$err_col = new wi400Column("IFLCOD", "Codice<br>Errore");
		$err_col->setActionListId("ERR_VIEW");
		
		$prg_col = new wi400Column("IFLNPD", "Programma");
		$prg_col->setShow(false);
		
		$stat_col = new wi400Column("IFLSTA", "Stato");
		$stat_col->setShow(false);
		
		$miaLista->setCols(array(
			new wi400Column("IFLAMB", "Ambito"),
//			new wi400Column("IFLDMO", "Data", "DATE"),
//			new wi400Column("IFLHMO", "Ora", "TIME_INTEGER"),
			new wi400Column("DATA_ORA", "Data e Ora", "STRING_COMPLETE_TIMESTAMP"),
			new wi400Column("IFLFUN", "Area<br>Funzione"),
			$file_col,
			new wi400Column("IFLTIP", "Tipo<br>Segnalazione"),
			$err_col,
			new wi400Column("IFEDES", "Descrizione<br>Errore"),
			new wi400Column("IFLGVT", "Gravità<br>Errore"),
			new wi400Column("IFLGRP", "Gruppo<br>Errori"),
			new wi400Column("IFLDES", "Descrizione<br>Evento"),
			new wi400Column("IFLDE2", "Descrizione<br>Aggiuntiva"),
			new wi400Column("IFLTXT", "Riga Testo<br>Ricevuta"),
			$stat_col,
			new wi400Column("IFLWHO", "Utente"),
			$prg_col
		));
		
		$miaLista->addKey("IFLAMB");
		$miaLista->addKey("IFLFUN");
		$miaLista->addKey("IFLFIL");
		$miaLista->addKey("IFLCOD");
		$miaLista->addKey("IFEDES");
		$miaLista->addKey("IFLGVT");
		$miaLista->addKey("IFLDMO");
		$miaLista->addKey("IFLHMO");
		
		// Filtri
		$myFilter = new wi400Filter("IFLAMB","Ambito");
		$myFilter->setFast(true);
		$myFilter->setCase('UPPER');
		$miaLista->addFilter($myFilter);
		
		$myFilter = new wi400Filter("IFLFUN","Area Funzione");
		$myFilter->setFast(true);
		$myFilter->setCase('UPPER');
		$miaLista->addFilter($myFilter);
		
		$myFilter = new wi400Filter("IFLCOD","Codice Errore");
		$myFilter->setFast(true);
		$myFilter->setCase('UPPER');
		$miaLista->addFilter($myFilter);
		
		$myFilter = new wi400Filter("IFEDES","Descrizione Errore");
		$myFilter->setFast(true);
		$myFilter->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$miaLista->addFilter($myFilter);
		
		$myFilter = new wi400Filter("IFLFIL","Nome File Flusso");
		$myFilter->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$miaLista->addFilter($myFilter);
		
		$myFilter = new wi400Filter("IFLTIP","Tipo Segnalazione");
//		$myFilter->setFast(true);
		$myFilter->setCase('UPPER');
		$miaLista->addFilter($myFilter);
		
		$myFilter = new wi400Filter("IFLGVT","Gravità Errore");
//		$myFilter->setFast(true);
		$myFilter->setCase('UPPER');
		$miaLista->addFilter($myFilter);
		
		// Dettaglio File
		$action = new wi400ListAction();
		$action->setId("FILE_VIEW");
		$action->setAction($azione);
		$action->setForm("FILE_VIEW");
		$action->setTarget("WINDOW");
		$action->setLabel("Dettaglio File");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		// Dettaglio Errore
		$action = new wi400ListAction();
		$action->setId("ERR_VIEW");
		$action->setAction($azione);
		$action->setForm("ERR_VIEW");
		$action->setTarget("WINDOW");
		$action->setLabel("Dettaglio Errore");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		listDispose($miaLista);
	}
	else if($actionContext->getForm()=="FILE_VIEW") {
/*		
		if(file_exists($file_path) && $size>20000000) {
?>
			<script>
				alert("Il file à troppo grande. Aprire direttamente il file.");
			</script>
<?						
		}
*/				
		$ListDetail = new wi400Detail($azione."_".$actionContext->getForm()."_DET");
		$ListDetail->setColsNum(2);
		
		$fieldDetail = new wi400Text("AMBITO");
		$fieldDetail->setLabel("Ambito");
		$fieldDetail->setValue($ambito);
		$ListDetail->addField($fieldDetail);
		
		$fieldDetail = new wi400Text("AREA_FUN");
		$fieldDetail->setLabel("Area Funzione");
		$fieldDetail->setValue($area_fun);
		$ListDetail->addField($fieldDetail);
		
		$fieldDetail = new wi400Text("COD_ERR");
		$fieldDetail->setLabel("Codice Errore");
		$fieldDetail->setValue($cod_err);
		$ListDetail->addField($fieldDetail);
		
		$fieldDetail = new wi400Text("DES_ERR");
		$fieldDetail->setLabel("Descrizione Errore");
		$fieldDetail->setValue($des_err);
		$ListDetail->addField($fieldDetail);
		
		$fieldDetail = new wi400Text("GRAVITA");
		$fieldDetail->setLabel("Gravità");
		$fieldDetail->setValue($gravita);
		$ListDetail->addField($fieldDetail);
		
		$fieldDetail = new wi400Text("VUOTO");
		$fieldDetail->setLabel("");
		$fieldDetail->setValue("");
		$ListDetail->addField($fieldDetail);
		
		$fieldDetail = new wi400Text("FILE");
		$fieldDetail->setLabel("Nome File Flusso");
		$fieldDetail->setValue($file_path);
//		$fieldDetail->setLink(create_file_download_link($file_path));
		$ListDetail->addField($fieldDetail);
		
		$fieldDetail = new wi400Text("VUOTO");
		$fieldDetail->setLabel("");
		$fieldDetail->setValue("");
		$ListDetail->addField($fieldDetail);
		
		$fieldDetail = new wi400Text("DATA");
		$fieldDetail->setLabel("Data");
		$fieldDetail->setValue(dateModelToView($data));
		$ListDetail->addField($fieldDetail);
		
		$fieldDetail = new wi400Text("ORA");
		$fieldDetail->setLabel("Ora");
		$fieldDetail->setValue(timeModelToView($ora, 6));
		$ListDetail->addField($fieldDetail);
		
		$ListDetail->dispose();
		
//		$spacer->dispose();
		
		$actionDetail = new wi400Detail($azione."_".$actionContext->getForm());
		
//		$actionDetail->setTitle('File Log');
//		$actionDetail->isEditable(true);
			
		if($path_parts['extension']=="xml") {
			$myField = new wi400InputTextArea('LOG_BODY');
			$myField->setReadonly(true);
//			$myField->setSaveSession(false);
			$myField->setSize(100);
			$myField->setRows(15);
			$myField->setValue($lines);
			$actionDetail->addField($myField);
		}
		else {
			$myField = new wi400TextPanel('LOG_BODY');
			$myField->setHeight(300);
			$myField->setValue($lines);
			$actionDetail->addField($myField);
		}
		
		$actionDetail->dispose();
		
		$myButton = new wi400InputButton("CLOSE_BUTTON");
		$myButton->setScript('closeLookUp()');
		$myButton->setLabel("Chiudi");
		$buttonsBar[] = $myButton;
	}
	else if($actionContext->getForm()=="ERR_VIEW") {
		$actionDetail = new wi400Detail($azione."_".$actionContext->getForm());
		
		// caricamento dei dati della chiamata recuperati dal subfile
//		$actionDetail->setSource($row);
		
		$fieldDetail = new wi400Text("IFECOD");
		$fieldDetail->setLabel("Codice Errore");
		$fieldDetail->setValue($row["IFECOD"]);
		$actionDetail->addField($fieldDetail);
		
		$fieldDetail = new wi400Text("IFEDES");
		$fieldDetail->setLabel("Descrizione Errore");
		$fieldDetail->setValue($row["IFEDES"]);
		$actionDetail->addField($fieldDetail);
/*		
		$fieldDetail = new wi400InputTextArea('IFEEST');
		$fieldDetail->setReadonly(true);
		$fieldDetail->setSize(100);
		$fieldDetail->setRows(15);
*/		
//		$fieldDetail = new wi400TextPanel('IFEEST');
		$fieldDetail = new wi400TextPanel('DES_ESTESA');
		$fieldDetail->setHeight(300);
		$fieldDetail->setValue($des_est);
		$fieldDetail->setLabel("Descrizione Estesa");
		$actionDetail->addField($fieldDetail);
		
		$actionDetail->dispose();
		
		$myButton = new wi400InputButton("CLOSE_BUTTON");
		$myButton->setScript('closeLookUp()');
		$myButton->setLabel("Chiudi");
		$buttonsBar[] = $myButton;
	}