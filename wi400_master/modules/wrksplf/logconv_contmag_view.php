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
		
		if(isset($mese) && $mese!="" && isset($anno) && $anno!="") {
			$fieldDetail = new wi400Text("PERIODO");
			$fieldDetail->setLabel("Periodo");
			$fieldDetail->setValue($mese."/".$anno);
			$ListDetail->addField($fieldDetail);
		}
		
		if(isset($mese) && $mese=="" && isset($anno) && $anno!="") {
			$fieldDetail = new wi400Text("PERIODO");
			$fieldDetail->setLabel("Periodo");
			$fieldDetail->setValue($anno);
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
//		$myField->setShowMultiple(true);
		$myField->setCase("UPPER");
		$myField->setMaxLength(4);
		$myField->setSize(4);
		$myField->setUserApplicationValue("SOCIETA");
		if((!isset($societa) || $societa==""))
			$myField->setValue($_SESSION['locale']);
		else
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
		
		// Periodo
			// Mese
		$myField = new wi400InputText('MESE');
		$myField->setLabel('Mese');
		$myField->setSize(2);
		$myField->setMaxLength(2);
		$myField->setMask('0123456789');
//		if((!isset($mese) || $mese==""))
//			$myField->setValue(date("m"));
//		else
		$myField->setValue($mese);
		$searchAction->addField($myField);
			// Anno
		$myField = new wi400InputText('ANNO');
		$myField->setLabel('Anno');
		$myField->setSize(4);
		$myField->setMaxLength(4);
		$myField->setMask('0123456789');
//		if((!isset($anno) || $anno==""))
//			$myField->setValue(date("Y"));
//		else
		$myField->setValue($anno);
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
		$miaLista->setOrder("LOGMOD, LOGKU1, LOGUSR, substr(LOGKY2, 7, 2)!!substr(LOGKY2, 4, 2)!!substr(LOGKY2, 1, 2) desc");
		
//		echo "SQL: ".$miaLista->getSql()."<br>";
		
//		$miaLista->setSelection("MULTIPLE");
		
		$miaLista->setIncludeFile(rtvModuloAzione($azione),"logconv_contmag_functions.php");
		
		$col_file = new wi400Column("LOGNOM", "File");
		$col_file->setDetailAction($azione, "DOWNLOAD_FILE");
//		$col_file->addDetailKey("LOGPTH");
//		$col_file->addDetailKey("LOGNOM");
		
		$cols = array(
			$col_file,
			new wi400Column("LOGKU4", "Società"),
			new wi400Column("LOGKU3", "Periodo"),
		);
		
		$miaLista->addKey("LOGUSR");
		$miaLista->addKey("LOGJOB");
		$miaLista->addKey("LOGNBR");
		$miaLista->addKey("LOGDTA");
		$miaLista->addKey("LOGPTH");
		$miaLista->addKey("LOGNOM");
		$miaLista->addKey("LOGMOD");
		$miaLista->addKey("LOGID");
		
		$mioFiltro = new wi400Filter("LOGKU4","Società","STRING");
		$mioFiltro->setDescription("Società");
		$miaLista->addFilter($mioFiltro);
		
		$mioFiltro = new wi400Filter("LOGKU3","Periodo","STRING");
		$mioFiltro->setDescription("Periodo");
		$miaLista->addFilter($mioFiltro);
		
		$mioFiltro = new wi400Filter("LOGNOM","File","STRING");
		$mioFiltro->setDescription("File");
		$mioFiltro->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$miaLista->addFilter($mioFiltro);
		
		$miaLista->setCols($cols);
		
		listDispose($miaLista);
	}
	else if($actionContext->getForm()=="DOWNLOAD_FILE") {
		$myButton = new wi400InputButton("CLOSE_BUTTON");
		$myButton->setScript('closeLookUp()');
		$myButton->setLabel("Chiudi");
		$buttonsBar[] = $myButton;
							
		downloadDetail($TypeImage, $filename, $temp, "Esportazione completata", "", "S", $campi);
	}