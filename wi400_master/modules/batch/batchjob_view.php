<?php

	$spacer = new wi400Spacer();

	if($actionContext->getForm()=="DEFAULT") {
		$batchDetail = new wi400Detail("batchDetail");
		$batchDetail->setColsNum(2);
		
		$labelDetail = new wi400Text("utente");
		$labelDetail->setLabel(_t('UTENTE'));
		$labelDetail->setValue($_SESSION['user']);
		$batchDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("sessione");
		$labelDetail->setLabel(_t('SESSION_ID'));
		$labelDetail->setValue(session_id());
		$batchDetail->addField($labelDetail);
		
		if($azione=="BATCHJOB") {
			$myField = new wi400InputCheckbox("SOLO_UTENTE");
			$myField->setLabel(_t('SOLO LAVORI DELL UTENTE'));
			$myField->setChecked($solo_user);
			$batchDetail->addField($myField);
		}
		
		$myField = new wi400InputCheckbox("SOLO_SESSIONE");
		$myField->setLabel(_t('SOLO LAVORI DELLA SESSIONE'));
		$myField->setChecked($solo_session);
		$batchDetail->addField($myField);
		
		$myButton = new wi400InputButton('APPLICA');
		$myButton->setLabel(_t('RICARICA'));
		$myButton->setAction($azione);
		$myButton->setValidation(true);
		$batchDetail->addButton($myButton);
					
		$batchDetail->dispose();
		
		$spacer->dispose();
		
		$miaLista = new wi400List("BATCHJOB_LIST", True);
		
		$miaLista->setSubfile($subfile);
		$miaLista->setFrom($subfile->getTable());
		$miaLista->setOrder("TIMESUB DESC");
		$miaLista->setSelection("MULTIPLE");
		
		$miaLista->setTimer(15);
		
//		$cols = getColumnListFromArray('BATCHJOB','batch');

		$col_sessione = new wi400Column("SESSIONE", _t('SESSION'));
		$col_sessione->setShow(false);
		
		$statoDecoded = new wi400Column("STATO_DECODED", _t('DES_STATO'));
		$statoDecoded->setShow(true);
		$statoDecoded->setSortable(false);
		
		$statoCond 	 = array();
		foreach ($statoBatchArray as $key => $desc){
			$statoCond[] = array('EVAL:$row["STATO"]=="'.$key.'"', prepare_string($desc));
		}
		$statoDecoded->setDefaultValue($statoCond);
		
		$statoCond = array();
		foreach ($statoBatchColor as $key => $color){
			$statoCond[] = array('EVAL:$row["STATO"]=="'.$key.'"', $color);
		}
		$statoDecoded->setStyle($statoCond);
		
		$filesCol = new wi400Column("FILECOL",_t('FILES'));
/*		
		$filesIco = new wi400Image("IMG");
		$filesIco->setUrl("folder.gif");
		
		$filesCond = array();
		$filesCond[] = array('EVAL:checkFilePresence("'.$path.'".$row["ID"], array($row["ID"].".out", $row["ID"].".txt"))', $filesIco->getHtml());
		$filesCond[] = array('EVAL:1==1', "");
		
		$filesCol->setDefaultValue($filesCond);
*/
		$filesCol->setDecorator("FOLDER");
		
		$filesCol->setDetailAction("BATCHJOB_FILES", "DEFAULT");
		$filesCol->addDetailKey("ID");
		$filesCol->setSortable(False);
		$filesCol->setExportable(False);
		
		$miaLista->setCols(array(
				new wi400Column("ID", _t('JOB_ID')),
				$col_sessione,
				new wi400Column("UTENTE", _t('USER')),
				new wi400Column("NOME_LAVORO", _t('JOB')),
				new wi400Column("DES_LAVORO", _t('JOB_DES')),
				new wi400Column("TIMESUB", _t('DATA_ORA_INS'), "TIMESTAMP"),
				new wi400Column("TIMESTART", _t('DATA_ORA_INI'), "TIMESTAMP"),
				new wi400Column("TIMECOMPLETE", _t('DATA_ORA_FIN'), "TIMESTAMP"),
				new wi400Column("STATO", _t('STATO')),
				new wi400Column("STATO_BATCH", _t('STATO_BATCH')),
				$statoDecoded,
				$filesCol
		));
		
		$miaLista->addKey("ID");
		$miaLista->addKey("STATO");
		$miaLista->addKey("TIMESUB");
		$miaLista->addKey("TIMESTART");
		$miaLista->addKey("TIMECOMPLETE");
		$miaLista->addKey("STATO_BATCH");
		
		$myFilter = new wi400Filter("ID","ID");
		$myFilter->setFast(true);
		$myFilter->setCase('UPPER');
		$miaLista->addFilter($myFilter);
		
		$myFilter = new wi400Filter("UTENTE","Utente");
		$myFilter->setFast(true);
		$myFilter->setCase('UPPER');
		$miaLista->addFilter($myFilter);
		
		$myFilter = new wi400Filter("NOME_LAVORO","Nome Lavoro");
		$myFilter->setFast(true);
		$myFilter->setCase('UPPER');
		$miaLista->addFilter($myFilter);
		
		$myFilter = new wi400Filter("DES_LAVORO","Descrizione Lavoro");
//		$myFilter->setFast(true);
//		$myFilter->setCase('UPPER');
		$myFilter->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$miaLista->addFilter($myFilter);
		
		// Elenco dei files
		$action = new wi400ListAction();
//		$action->setAction($azione);
//		$action->setForm("FILES_LIST");
		$action->setAction("BATCHJOB_FILES");
		$action->setForm("DEFAULT");
		$action->setLabel(_t('Elenco dei files'));
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		// Rimuovi record dall'elenco
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("REMOVE");
		$action->setLabel(_t('Cancella lavoro'));
		$action->setSelection("MULTIPLE");
		$action->setConfirmMessage(_t('ELIMINARE_LAVORI_SEL_E_FILES'));
		$miaLista->addAction($action);

		listDispose($miaLista);
	}
/*	
	else if($actionContext->getForm()=="FILES_LIST") {
		$ListDetail = new wi400Detail("BatchListDetail");
		$ListDetail->setColsNum(2);
		
		$labelDetail = new wi400Text("ID");
		$labelDetail->setLabel("ID");
		$labelDetail->setValue($id);
		$ListDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("STATO");
		$labelDetail->setLabel(_t('STATO'));
		$labelDetail->setValue($stato);
		$ListDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("STATO_BATCH");
		$labelDetail->setLabel(_t('STATO_BATCH'));
		$labelDetail->setValue($stato_batch);
		$ListDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("TIMESUB");
		$labelDetail->setLabel(_t('DATA_ORA_INS'));
		$labelDetail->setValue(wi400_format_TIMESTAMP($time_sub));
		$ListDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("TIMESTART");
		$labelDetail->setLabel(_t('DATA_ORA_INI'));
		$labelDetail->setValue(wi400_format_TIMESTAMP($time_start));
		$ListDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("TIMECOMPLETE");
		$labelDetail->setLabel(_t('DATA_ORA_FIN'));
		$labelDetail->setValue(wi400_format_TIMESTAMP($time_complete));
		$ListDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("LOGFILES");
		$labelDetail->setLabel(_t('FILES_DI_LOG'));
		if($log_file===true)
			$labelDetail->setValue(_t('PRESENTI'));
		else
			$labelDetail->setValue(_t('ASSENTI'));
		$ListDetail->addField($labelDetail);
		
		$ListDetail->dispose();
		
		$spacer->dispose();
		
		// Inizializzazione lista
		$miaLista = new wi400List("BATCH_FILE_LST", !$isFromHistory);
		$miaLista->setFrom($subfile->getTable());
		$miaLista->setOrder("FILE");
		$miaLista->setSelection("MULTIPLE");
		
		$filePrv = new wi400Column("FILE",_t('FILE'));
		$filePrv->setDetailAction($azione, "FILE_PRV");
				
		$miaLista->setCols(array(
			$filePrv,
			new wi400Column("SIZE_B",_t('FILE_SIZE'), "INTEGER", 'right'),
//			new wi400Column("SIZE_F","Size (MB)", "DOUBLE_2", 'right')
			new wi400Column("SIZE_F",_t('SIZE'), "DOUBLE_2", 'right'),
			new wi400Column("TYPE_F",_t('TIPO DIMENSIONE'))
		));
		
		// aggiunta chiavi di riga
		$miaLista->addKey("FILE");
		$miaLista->addKey("ID");
		
		// Rimuovi record dall'elenco
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("REMOVE_FILE");
		$action->setLabel(_t('Cancella file'));
		$action->setSelection("MULTIPLE");
		$action->setConfirmMessage(_t('ELIMINARE_FILES_SEL'));
		$miaLista->addAction($action);
		
		listDispose($miaLista);
	}
	else if($actionContext->getForm()=="FILE_PRV") {
//		$TypeImage = "xls.png";
		$TypeImage = "";

		$file_parts = pathinfo($file_path);
		if(isset($file_parts['extension']))
			$TypeImage = strtolower($file_parts['extension']);
				
		downloadDetail($TypeImage, $file_path, "", _t('ESPORTAZIONE_COMPLETATA'));
		
		$myButton = new wi400InputButton("CLOSE_BUTTON");
		$myButton->setScript('closeLookUp()');
		$myButton->setLabel(_t('CHIUDI'));
		$buttonsBar[] = $myButton;
	}
*/	
?>