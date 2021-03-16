<?php

	$spacer = new wi400Spacer();
	
	if($actionContext->getForm()!="DEFAULT") {
//		$azioniDetail = new wi400Detail($azione."_".$actionContext->getForm()."_DET", true);
		$azioniDetail = new wi400Detail($azione."_FILTRI_LIST_DET", true);
		$azioniDetail->setColsNum(2);
		
		if(in_array($actionContext->getForm(), array("FILTRI_LIST", "DELETE_FILTRI_LIST"))) {
			$labelDetail = new wi400Text("FILE");
			$labelDetail->setLabel("File");
			$labelDetail->setValue(wi400_format_FILE_PATH_TO_NAME($file));
			$azioniDetail->addField($labelDetail);
		}
		
		if($actionContext->getForm()=="FILTRI_LIST") {
			$myField = new wi400InputSwitch("OVERWRITE");
			$myField->setLabel("Sovrascrivi Filtri");
			$myField->setOnLabel(_t('LABEL_YES'));
			$myField->setOffLabel(_t('LABEL_NO'));
			$myField->setChecked($check_overwrite);
			$myField->setValue(1);
			$azioniDetail->addField($myField);
		}
		
		if($user_src!="") {
			$des_user = "";
			
			$stmt_user = $db->singlePrepare($sql_user);
			
			$res_user = $db->execute($stmt_user, array($user_src));
			
			if($row_user = $db->fetch_array($stmt_user)) {
//				$des_user = $row_user['DSPRAD'];
				$des_user = $row_user['DES_USER'];
			}
			
			if($actionContext->getForm()=="DELETE_FILTRI_LIST") {
				$labelDetail = new wi400Text("VUOTO");
				$labelDetail->setLabel("");
				$labelDetail->setValue("");
				$azioniDetail->addField($labelDetail);
			}
			
			$labelDetail = new wi400Text("FROM_USER");
			if($actionContext->getForm()=="FILTRI_LIST")
				$labelDetail->setLabel("Da Utente");
			else
				$labelDetail->setLabel("Utente");
			$labelDetail->setValue($user_src." - ".$des_user);
			$azioniDetail->addField($labelDetail);
		}
		else {
			$labelDetail = new wi400Text("FROM_USER");
			$labelDetail->setLabel("Utenti");
			$labelDetail->setValue("TUTTI");
			$azioniDetail->addField($labelDetail);
		}
		
		if(in_array($actionContext->getForm(), array("FILTRI_LIST", "DELETE_FILTRI_LIST"))) {
			$labelDetail = new wi400Text("VUOTO");
			$labelDetail->setLabel("");
			$labelDetail->setValue("");
			$azioniDetail->addField($labelDetail);
			
			// Utente
			$myField = new wi400InputText('TO_USER');
			$myField->setLabel("A Utente");
			$myField->setSize(10);
			$myField->setMaxLength(10);
			$myField->setCase("UPPER");
			$myField->setValue($to_user_array);
			$myField->setShowMultiple(true);
//			$myField->addValidation('required');
			
			$decodeParameters = array(
				'TYPE' => 'common',
				'TABLE_NAME' => $id_user_file_lib.$settings['db_separator'].$id_user_file,
				'COLUMN' => $id_user_desc,
				'KEY_FIELD_NAME' => $id_user_name,
//				'FILTER_SQL' => "DSPRAD not like 'SIRI - %'",
				'FILTER_SQL' => $id_user_name."<>'$user_src'",
				'AJAX' => true
			);
			$myField->setDecode($decodeParameters);
			
			$myLookUp = new wi400LookUp("LU_GENERICO");
			$myLookUp->addParameter("FILE",$id_user_file_lib.$settings['db_separator'].$id_user_file);
			$myLookUp->addParameter("CAMPO",$id_user_name);
			$myLookUp->addParameter("DESCRIZIONE",$id_user_desc);
//			$myLookUp->addParameter("LU_WHERE","DSPRAD not like 'SIRI - %'");
			$myLookUp->addParameter("LU_WHERE",$id_user_name."<>'$user_src'");
			if ($id_user_file==$users_table) {
				$myLookUp->addParameter("LU_SELECT","FIRST_NAME");
			}
			$myField->setLookUp($myLookUp);
			
			$azioniDetail->addField($myField);
			
			if($actionContext->getForm()=="DELETE_FILTRI_LIST") {
				$labelDetail = new wi400Text("VUOTO");
				$labelDetail->setLabel("");
				$labelDetail->setValue("");
				$azioniDetail->addField($labelDetail);
			}
			
			$myField = new wi400InputSwitch("TO_ALL");
			$myField->setLabel("A Tutti");
			$myField->setOnLabel(_t('LABEL_YES'));
			$myField->setOffLabel(_t('LABEL_NO'));
			$myField->setChecked($check_to_all);
			$myField->setValue(1);
			$azioniDetail->addField($myField);
		}
		
		if($actionContext->getForm()=="DELETE_FILTRI_LIST") {
			$myField = new wi400InputSwitch("NO_CURRENT");
			$myField->setLabel("Non rimuovere<br>da questo utente");
			$myField->setOnLabel(_t('LABEL_YES'));
			$myField->setOffLabel(_t('LABEL_NO'));
			$myField->setChecked($check_no_current);
			$myField->setValue(1);
			$azioniDetail->addField($myField);
		}

		if(in_array($actionContext->getForm(), array("FILTRI_LIST", "DELETE_FILTRI_LIST"))) {
			// Default Detail
			$labelDetail = new wi400Text("DEFAULT_FILTER_FILE");
			$labelDetail->setLabel("Filtro di Default");
			$labelDetail->setValue($default_detail_file);
			$azioniDetail->addField($labelDetail);
		}

		if($actionContext->getForm()=="FILTRI_LIST") {
			// Default Detail da asseganare
			$myField = new wi400InputText('DEFAULT_DETAIL');
			$myField->setLabel("Filtro di Default da assegnare");
			$myField->setSize(10);
			$myField->setMaxLength(100);
//			$myField->setCase("UPPER");
//			$myField->addValidation('required');
			$myField->setValue($default_detail);
			$myField->setInfo("Inserire il valore di default del filtro");
			
			$azioniDetail->addField($myField);
		}
		
		$azioniDetail->dispose();
		
		$spacer->dispose();
	}
	
	if($actionContext->getForm()=="DEFAULT") {
		$searchAction = new wi400Detail($azione."_SRC");
		$searchAction->setTitle("Parametri");
		$searchAction->isEditable(true);
//		$searchAction->setSaveDetail(true);
		
		// Utente
		$myField = new wi400InputText('USER_SRC');
		$myField->setLabel("Utente");
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$myField->setCase("UPPER");
//		$myField->addValidation('required');
		$myField->setValue($user_src);
		$myField->setInfo(_t('USER_CODE_INFO'));
		
		$decodeParameters = array(
			'TYPE' => 'common',
			'TABLE_NAME' => $id_user_file_lib.$settings['db_separator'].$id_user_file,
			'COLUMN' => $id_user_desc,
			'KEY_FIELD_NAME' => $id_user_name,
//			'FILTER_SQL' => "DSPRAD not like 'SIRI - %'",
			'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE",$id_user_file_lib.$settings['db_separator'].$id_user_file);
		$myLookUp->addParameter("CAMPO",$id_user_name);
		$myLookUp->addParameter("DESCRIZIONE",$id_user_desc);
//		$myLookUp->addParameter("LU_WHERE","DSPRAD not like 'SIRI - %'");
		if ($id_user_file==$users_table) {
			$myLookUp->addParameter("LU_SELECT","FIRST_NAME");
		}
		$myField->setLookUp($myLookUp);
		
		$searchAction->addField($myField);
		
		$myButton = new wi400InputButton('SEARCH_BUTTON');
		$myButton->setLabel("Seleziona");
		$myButton->setAction($azione);
		$myButton->setForm("DETAIL_LIST");
		$myButton->setValidation(true);
		$searchAction->addButton($myButton);
		
		$searchAction->dispose();
	}
	else if($actionContext->getForm()=="DETAIL_LIST") {
		// Inizializzazione lista
		$miaLista = new wi400List($azione."_DETAIL_LIST", !$isFromHistory);
		$miaLista->setSubfile($subfile);
		$miaLista->setFrom($subfile->getTable());
//		$miaLista->setOrder("TIPO desc, FILE_NAME desc");
		$miaLista->setOrder("FILE desc, UTENTE");
		$miaLista->setSelection("MULTIPLE");
		
		$miaLista->setCalculateTotalRows('FALSE');
		
		$file_path = new wi400Column("FILE_PATH","File Path");
		$file_path->setDefaultValue('EVAL:$row["FILE_NAME"]');
		
//		$file_col = new wi400Column("FILE_NAME","File","FILE_PATH_TO_NAME");
		$file_col = new wi400Column("FILE","File");
//		$file_col->setActionListId("FILTRI_LIST");
		
		$col_det = new wi400Column("FILTRI", "Distribuzione<br>Filtri", "STRING", "center");
		$col_det->setActionListId("FILTRI_LIST");
//		$col_det->setDefaultValue("SAVE");
		$col_det->setDefaultValue("MODIFICA");
		$col_det->setDecorator("ICONS");
		$col_det->setExportable(false);
		$col_det->setSortable(false);
		
		$col_del = new wi400Column("DELETE_FILTRI", "Rimozione<br>Filtri", "STRING", "center");
		$col_del->setActionListId("DELETE_FILTRI_LIST");
		$col_del->setDefaultValue("BIN");
		$col_del->setDecorator("ICONS");
		$col_del->setExportable(false);
		$col_del->setSortable(false);

		$miaLista->setCols(array(
			$col_det,
			$col_del,
			new wi400Column("UTENTE", "Utente"),
			new wi400Column("DES_UTENTE", "Descrizione Utente"),
//			$file_path,
			$file_col,
			new wi400Column("DIMENSIONE","Dimensione (Bytes)", "INTEGER", "right")
		));
		
		if($user_src!="") {
			$miaLista->removeCol("UTENTE");
			$miaLista->removeCol("DES_UTENTE");
		}
		
		$miaLista->addKey("FILE_NAME");
		$miaLista->addKey("UTENTE");
		
		$myFilter = new wi400Filter("FILE","File");
		$myFilter->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$myFilter->setFast(true);
		$miaLista->addFilter($myFilter);
		
		if($user_src=="") {
			$myFilter = new wi400Filter("UTENTE","Utente","LOOKUP");
			
			$decodeParameters = array(
				'TYPE' => 'common',
				'TABLE_NAME' => $id_user_file_lib.$settings['db_separator'].$id_user_file,
				'COLUMN' => $id_user_desc,
				'KEY_FIELD_NAME' => $id_user_name,
//				'FILTER_SQL' => "DSPRAD not like 'SIRI - %'",
				'AJAX' => true
			);
			$myFilter->setDecode($decodeParameters);
			
			$myLookUp = new wi400LookUp("LU_GENERICO");
			$myLookUp->addParameter("FILE",$id_user_file_lib.$settings['db_separator'].$id_user_file);
			$myLookUp->addParameter("CAMPO",$id_user_name);
			$myLookUp->addParameter("DESCRIZIONE",$id_user_desc);
//			$myLookUp->addParameter("LU_WHERE","DSPRAD not like 'SIRI - %'");
			if ($id_user_file==$users_table) {
				$myLookUp->addParameter("LU_SELECT","FIRST_NAME");
			}
			$myFilter->setLookUp($myLookUp);
			
			$miaLista->addFilter($myFilter);
			
			$myFilter = new wi400Filter("DES_UTENTE","Descrizione Utente");
			$myFilter->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
			$myFilter->setFast(true);
			$miaLista->addFilter($myFilter);
		}
		
		$action = new wi400ListAction();
		$action->setId("FILTRI_LIST");
		$action->setAction($azione);
		$action->setForm("FILTRI_LIST");
		$action->setLabel("Distribuzione Filtri");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		$action = new wi400ListAction();
		$action->setId("DELETE_FILTRI_LIST");
		$action->setAction($azione);
		$action->setForm("DELETE_FILTRI_LIST");
		$action->setLabel("Rimozione Filtri");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		$miaLista->dispose();
	}
	else if(in_array($actionContext->getForm(), array("FILTRI_LIST", "DELETE_FILTRI_LIST"))) {
		$appDrag = new wi400DragAndDrop("FILTRI_DRAG");
		$appDrag->setWidth("49%");
		$appDrag->setHeight("100");
		$appDrag->setCheckUpdate(true);
		
		$appList1 = new wi400DragList("FROM_FILTRI","Filtri $user_src");
		$appList1->setRows($from_filtri);
		$appList1->setColor("#ffebe8");
		$appDrag->addList($appList1);
		
		if($actionContext->getForm()=="FILTRI_LIST")
			$appList2 = new wi400DragList("TO_FILTRI","Filtri da copiare");
		else if($actionContext->getForm()=="DELETE_FILTRI_LIST")
			$appList2 = new wi400DragList("TO_FILTRI","Filtri da rimuovere");
		$appList2->setRows($to_filtri);
		$appList2->setColor("#CCCC21");
		$appDrag->addList($appList2);
		
		$appDrag->dispose();
		
		$button = new wi400InputButton("CANCEL");
		$button->setLabel("Annulla");
		$button->setAction($azione);
		$button->setForm("FILTRI_LIST");
		$button->dispose();
		
		if($actionContext->getForm()=="FILTRI_LIST") {
			$button = new wi400InputButton("SAVE_APP");
			$button->setLabel("Salva");
			$button->setAction($azione);
			$button->setForm("SAVE");
			$button->setConfirmMessage("Salvare?");
			$button->setValidation(true);
			$button->dispose();
		}
		else if($actionContext->getForm()=="DELETE_FILTRI_LIST") {
			$button = new wi400InputButton("DELETE_APP");
			$button->setLabel("Rimuovi");
			$button->setAction($azione);
			$button->setForm("DELETE");
			$button->setConfirmMessage("Rimuovere?");
			$button->setValidation(true);
			$button->dispose();
		}
		
		$button = new wi400InputButton("SEL_ALL");
		$button->setLabel("Seleziona tutti");
		$button->setAction($azione);
		$button->setForm("SEL_ALL");
		$button->dispose();
	}