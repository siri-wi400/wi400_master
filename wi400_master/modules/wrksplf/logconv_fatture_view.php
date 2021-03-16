<?php

	$spacer = new wi400Spacer();
	
	if(in_array($actionContext->getForm(), array("LIST"))) {
		$ListDetail = new wi400Detail($azione."_".$actionContext->getForm()."_DET");
		$ListDetail->setColsNum(2);
		
		if(isset($societa) && $societa!="") {
//			$des_soc = get_campo_ente($societa, date("Ymd"), "MAFDSE");
			$des_soc = get_campo_ente($societa, $data_rif_ana, "MAFDSE");
			
			$fieldDetail = new wi400Text("SOCIETA");
			$fieldDetail->setLabel("Società");
			$fieldDetail->setValue($societa." - ".$des_soc);
			$ListDetail->addField($fieldDetail);
		}
		
		if(isset($cliente) && $cliente!="") {
			$des_cli = get_campo_fornitore($cliente, date("Ymd"), "MEBRAG");
			
			$fieldDetail = new wi400Text("CLIENTE");
			$fieldDetail->setLabel("Cliente");
			$fieldDetail->setValue($cliente." - ".$des_cli);
			$ListDetail->addField($fieldDetail);
		}
		
		if(isset($stampta) && $stampata!="") {
			$fieldDetail = new wi400Text("STAMPATA");
			$fieldDetail->setLabel("Stampata");
			$fieldDetail->setValue($stampata_array[$stampata]);
			$ListDetail->addField($fieldDetail);
		}
		
		if(isset($data_ini) && $data_ini!="") {
			$fieldDetail = new wi400Text("PERIODO");
			$fieldDetail->setLabel("Periodo di riferimento della fattura");
			$fieldDetail->setValue("Dal $data_ini al $data_fin");
			$ListDetail->addField($fieldDetail);
		}
		else if(isset($data_stmp_ini) && $data_stmp_ini!="") {
			$fieldDetail = new wi400Text("PERIODO_STMP");
			$fieldDetail->setLabel("Periodo di riferimento");
			$fieldDetail->setValue("Dal $data_stmp_ini al $data_stmp_fin");
			$ListDetail->addField($fieldDetail);
		}
		
		if(isset($user) && $user!="") {
			$fieldDetail = new wi400Text("USER");
			$fieldDetail->setLabel("Utente");
			$fieldDetail->setValue($user);
			$ListDetail->addField($fieldDetail);
		}
		
		$ListDetail->dispose();
		
		$spacer->dispose();
	}
	
	if($actionContext->getForm()=="DEFAULT") {
		$searchAction = new wi400Detail($azione."_SRC", true);
		$searchAction->setTitle('Interrogazione PDF archiviati');
		$searchAction->isEditable(true);
		$searchAction->setSaveDetail(true);
		
		// Società
		$myField = new wi400InputText('SOCIETA');
		$myField->setLabel("Società");
		if($azione=="LOGCONV_FATTURE")
			$myField->addValidation('required');
//		$myField->setShowMultiple(true);
		$myField->setCase("UPPER");
		$myField->setMaxLength(4);
		$myField->setSize(4);
		$myField->setUserApplicationValue("SOCIETA");
		$myField->setValue($societa);
		
		$decodeParameters = array(
			'TYPE' => 'ente',
			'CLASSE_ENTE' => '09',
			'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_ENTI");
		$myLookUp->addParameter("CLASSE", "09");
		$myLookUp->addField("SOCIETA");
		$myField->setLookUp($myLookUp);
		
		$searchAction->addField($myField);
		
		// Cliente
		$myField = new wi400InputText('CLIENTE');
		$myField->setLabel("Cliente");
		$myField->setMaxLength(6);
		$myField->setSize(6);
		$myField->setValue($cliente);
		
		$decodeParameters = array(
			'TYPE' => 'interlocutore',
			'TIPO_RAPPORTO' => '11',
			'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp =new wi400LookUp("LU_INTER");
		$myLookUp->addParameter("TIPO_RAPPORTO", "11");
//		$myLookUp->addParameter("FILTER_SQL","MEBCDF IN (SELECT KTLFOR FROM FKTLOTTI)");
		$myLookUp->addField("CLIENTE");
		$myField->setLookUp($myLookUp);
				
		$searchAction->addField($myField);		
/*		
		// Stampata
		$mySelect = new wi400InputSelect('STAMPATA');
		$mySelect->setLabel("Stampata");
		$mySelect->setFirstLabel("Seleziona...");
		$mySelect->setOptions($stampata_array);
		$mySelect->setValue($stampata);
		$searchAction->addField($mySelect);
*/		
		if($azione=="LOGCONV_FATTURE_OP") {
			// Data iniziale
			$myField = new wi400InputText('DATA_STMP_INI');
			$myField->setLabel('Data iniziale');
			$myField->addValidation('date');
			if(!isset($data_stmp_ini) || trim($data_stmp_ini)=="")
				$myField->setValue(dateModelToView(date("Ymd")));
			else
				$myField->setValue($data_stmp_ini);
			$searchAction->addField($myField);
			
			// Data finale
			$myField = new wi400InputText('DATA_STMP_FIN');
			$myField->setLabel('Data finale');
			$myField->addValidation('date');
			if(!isset($data_stmp_fin) || trim($data_stmp_fin)=="")
				$myField->setValue(dateModelToView(date("Ymd")));
			else
				$myField->setValue($data_stmp_fin);
			$searchAction->addField($myField);
		}
		
		if($azione=="LOGCONV_FATTURE") {
			// Data fattura iniziale
			$myField = new wi400InputText('DATA_INI');
			$myField->setLabel('Data fattura iniziale');
			if($azione=="LOGCONV_FATTURE")
				$myField->addValidation('required');
			$myField->addValidation('date');
			if($azione=="LOGCONV_FATTURE" && (!isset($data_ini) || trim($data_ini)==""))
				$myField->setValue(dateModelToView(date("Ymd")));
			else
				$myField->setValue($data_ini);
			$searchAction->addField($myField);
			
			// Data fattura finale
			$myField = new wi400InputText('DATA_FIN');
			$myField->setLabel('Data fattura finale');
			if($azione=="LOGCONV_FATTURE")
				$myField->addValidation('required');
			$myField->addValidation('date');
			if($azione=="LOGCONV_FATTURE" && (!isset($data_fin) || trim($data_fin)==""))
				$myField->setValue(dateModelToView(date("Ymd")));
			else
				$myField->setValue($data_fin);
			$searchAction->addField($myField);
		}
		
		// Utente
		$myField = new wi400InputText('USER');
		$myField->setLabel("Utente");
//		$myField->setShowMultiple(true);
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$myField->setCase("UPPER");
		$myField->setValue($user);
		$myField->setInfo(_t('USER_CODE_INFO'));
		
		$decodeParameters = array(
			'TYPE' => 'common',
			'TABLE_NAME' => $settings['lib_architect']."/JPROFADF",
			'COLUMN' => 'DSPRAD',
			'KEY_FIELD_NAME' => 'NMPRAD',
//			'FILTER_SQL' => "DSPRAD not like 'SIRI - %'",
			'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE",$settings['lib_architect']."/JPROFADF");
		$myLookUp->addParameter("CAMPO","NMPRAD");
		$myLookUp->addParameter("DESCRIZIONE","DSPRAD");
//		$myLookUp->addParameter("LU_WHERE","DSPRAD not like 'SIRI - %'");
		$myField->setLookUp($myLookUp);
		
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
		
		$miaLista->setField("a.*, substr(LOGKY1, 1, 2) as PRTFAT, LCOIZP");
		$miaLista->setFrom("FLOGCONV a left join FLCONSSO on LOGID=LCOIDI");
		$miaLista->setWhere($where);
//		$miaLista->setOrder("LOGKU1, LOGMOD, LOGUSR, substr(LOGKY2, 7, 2)!!substr(LOGKY2, 4, 2)!!substr(LOGKY2, 1, 2) desc");
		$miaLista->setOrder("LOGMOD, LOGKU1, LOGUSR, substr(LOGKY2, 7, 2)!!substr(LOGKY2, 4, 2)!!substr(LOGKY2, 1, 2) desc");
		
//		echo "SQL: ".$miaLista->getSql()."<br>";
		
		$miaLista->setSelection("MULTIPLE");
		
		$miaLista->setIncludeFile(rtvModuloAzione($azione),"logconv_fatture_functions.php");
		
		$col_file = new wi400Column("LOGNOM", "File");
		$col_file->setDetailAction($azione, "DOWNLOAD_FILE");
//		$col_file->addDetailKey("LOGPTH");
//		$col_file->addDetailKey("LOGNOM");

		// Colonna link stampa
		$stmpCol = new wi400Column("STMPCOL", "Stampa");
		
		$imgIco = new wi400Image("IMG");
//		$imgIco->setUrl("tag-image.gif");
		$imgIco->setUrl("printer.gif");
		
		$imgCond = array();
		$imgCond[] = array('EVAL:1==1', $imgIco->getHtml());
		
		$stmpCol->setDefaultValue($imgCond);
		
		//$stmpCol->setDetailAction("LOGCONV_STAMPA", "STAMPA_SEL");
		$stmpCol->setActionListId("STAMPA_PDF");
		$stmpCol->setSortable(False);
		$stmpCol->setExportable(False);
		
		$cons_sost_col = new wi400Column("LCOIZP", "Conservazione<br>Sostitutiva");
		$cons_sost_col->setDecorator("YES_NO_ICO");
		
		$cols = array(
			$stmpCol,
			new wi400Column("LOGUSR", "Utente"),
			$col_file,
			new wi400Column("LOGMOD", "Modulo")
		);
		
		$miaLista->addKey("LOGUSR");
		$miaLista->addKey("LOGJOB");
		$miaLista->addKey("LOGNBR");
		$miaLista->addKey("LOGDTA");
		$miaLista->addKey("LOGPTH");
		$miaLista->addKey("LOGNOM");
		$miaLista->addKey("LOGMOD");
		$miaLista->addKey("LOGID");
		
		$mioFiltro = new wi400Filter("LOGUSR","Utente","STRING");
		$mioFiltro->setFast(true);
		$miaLista->addFilter($mioFiltro);
		
		$mioFiltro = new wi400Filter("LOGDTA","Dati utente","STRING");
		$mioFiltro->setFast(true);
		$miaLista->addFilter($mioFiltro);
		
		$cols[] = new wi400Column("PRTFAT", "Protocollo<br>fattura");
		
		// Chiavi Ricerca
		for($i=1; $i<=$settings['modelli_pdf_keys']; $i++) {
			$title = get_titolo_chiave_ric($i);
			
			if($title=="")
				continue;
			
			$cols[] = new wi400Column("LOGKY".$i, $title);
				
			$mioFiltro = new wi400Filter("LOGKY".$i, $title, "STRING");
			$miaLista->addFilter($mioFiltro);
			
			$miaLista->addKey("LOGKY".$i);
		}

		// Chiavi utente
		for($i=1; $i<=$settings['modelli_pdf_user_keys']; $i++) {
			$title = get_titolo_chiave_user($i);
				
			if($title=="")
				continue;
				
			$col = new wi400Column("LOGKU".$i, $title);
		
			if(strtoupper($title)!="IMPORTO") {
				$mioFiltro = new wi400Filter("LOGKU".$i, $title, "STRING");
				$miaLista->addFilter($mioFiltro);
					
				$miaLista->addKey("LOGKU".$i);
			}
			else {
				$col->setFormat("STRING_TO_DOUBLE_2");
				$col->setAlign("right");
			}
			
			$cols[] = $col;
		}
		
		if($azione=="LOGCONV_FATTURE_OP") {
			$cols[] = new wi400Column("LOGOUT", "OutQ Stampa");
			$cols[] = new wi400Column("LOGSTT", "Timestamp di stampa", "COMPLETE_TIMESTAMP");
		}
		
		$cols[] = $cons_sost_col;
		
		$miaLista->setCols($cols);
		
		if($azione=="LOGCONV_FATTURE") {
			$miaLista->removeCol("LOGMOD");
		}
		else {
			$mioFiltro = new wi400Filter("LOGMOD", "Modulo", "STRING");
			$miaLista->addFilter($mioFiltro);
			
			$mioFiltro = new wi400Filter("LOGOUT", "OutQ Stampa", "STRING");
			$miaLista->addFilter($mioFiltro);
		}
		
		// Stampa
		$action = new wi400ListAction();
		$action->setAction("LOGCONV_STAMPA");
		$action->setId("STAMPA_PDF");		
		$action->setForm("STAMPA_SEL");
		$action->setLabel("Stampa");
		$action->setSelection("MULTIPLE");
		$action->setTarget("WINDOW");
//		$action->setConfirmMessage("Stampare?");
		$miaLista->addAction($action);
		
		if($azione=="LOGCONV_FATTURE_OP") {
			// Stampa Tutto
			$action = new wi400ListAction();
			$action->setAction("LOGCONV_STAMPA");
			$action->setForm("STAMPA_SEL_TUTTO");
			$action->setLabel("Stampa Tutto");
			$action->setSelection("NONE");
			$action->setTarget("WINDOW");
//			$action->setConfirmMessage("Stampare TUTTO?");
			$miaLista->addAction($action);
		}
		
		// Reinvio
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("REINVIO");
//		$action->setId("STAMPA_PDF");
		$action->setLabel("Reinvio");
		$action->setSelection("MULTIPLE");
//		$action->setTarget("WINDOW");
		$action->setConfirmMessage("Eseguire il reinvio?");
		$miaLista->addAction($action);
/*		
		// Rimozione log
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("REMOVE");
		$action->setLabel("Cancella");
		$action->setSelection("MULTIPLE");
		$action->setConfirmMessage("Cancellare?");
		$miaLista->addAction($action);
*/		
		listDispose($miaLista);
	}
	else if($actionContext->getForm()=="DOWNLOAD_FILE") {
		$myButton = new wi400InputButton("CLOSE_BUTTON");
		$myButton->setScript('closeLookUp()');
		$myButton->setLabel("Chiudi");
		$buttonsBar[] = $myButton;
	
//		downloadDetail($TypeImage, $filename, $temp, "Esportazione completata");						
		downloadDetail($TypeImage, $filename, $temp, "Esportazione completata", "", "S", $campi);
	}