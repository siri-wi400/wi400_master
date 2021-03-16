<?php

	$spacer = new wi400Spacer();
	
	if(!in_array($actionContext->getForm(), array("DEFAULT", "ADD_USER_SEL", "ADD_QUERY_SEL", "MOD_DES"))) {
		$azioniDetail = new wi400Detail($azione."_".$actionContext->getForm()."_DET", true);
		$azioniDetail->setColsNum(2);
		
		if(!empty($user_src)) {
			$labelDetail = new wi400Text("USER_DET");
			$labelDetail->setLabel("Utente");
			$labelDetail->setValue($user_src);
			$azioniDetail->addField($labelDetail);
		}
		else {
			$labelDetail = new wi400Text("USER_DET");
			$labelDetail->setLabel("Utenti");
			$labelDetail->setValue("TUTTI");
			$azioniDetail->addField($labelDetail);
		}
		
		if($actionContext->getForm()!="QUERY_LIST") {
			$labelDetail = new wi400Text("ID_QUERY_DET");
			$labelDetail->setLabel("ID Query");
			$labelDetail->setValue($id_query);
			$azioniDetail->addField($labelDetail);
			
			$labelDetail = new wi400Text("DES_QUERY_DET");
			$labelDetail->setLabel("Descrizione Query");
			$labelDetail->setValue($des_query);
			$azioniDetail->addField($labelDetail);
			
			$labelDetail = new wi400Text("AREA_DET");
			$labelDetail->setLabel("Area");
			$labelDetail->setValue($area_query);
			$azioniDetail->addField($labelDetail);
			
			$labelDetail = new wi400Text("FUNZIONE_DET");
			$labelDetail->setLabel("Funzione");
			$labelDetail->setValue($funz_query);
			$azioniDetail->addField($labelDetail);
			
			$labelDetail = new wi400Text("NOTE_DET");
			$labelDetail->setLabel("Note");
			$labelDetail->setValue($note_query);
			$azioniDetail->addField($labelDetail);
		}
		
		$azioniDetail->dispose();
		
		$spacer->dispose();
	}
	
	if($actionContext->getForm()=="DEFAULT") {
		$searchAction = new wi400Detail($azione."_SRC", false);
		$searchAction->setTitle($label);
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
		
		$decodeParameters = array(
			'TYPE' => 'common',
			'TABLE_NAME' => $id_user_file_lib.$settings['db_separator'].$id_user_file,
			'COLUMN' => $id_user_desc,
			'KEY_FIELD_NAME' => $id_user_name,
			'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
		
		$where_cond = array();
		foreach($query_admin_array as $val) {
			$where_cond[] = "WI400_GROUPS like '%$val%'";
		}
		$where = "(".implode(" or ", $where_cond).")";
		
		$myLookUp = new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE",$id_user_file_lib.$settings['db_separator'].$id_user_file);
		$myLookUp->addParameter("CAMPO",$id_user_name);
		$myLookUp->addParameter("DESCRIZIONE",$id_user_desc);
		$myLookUp->addParameter("FILTER_SQL", $where);
		if($id_user_file==$users_table) {
			$myLookUp->addParameter("LU_SELECT","FIRST_NAME");
		}
		$myField->setLookUp($myLookUp);
		
		$searchAction->addField($myField);
		
		$myButton = new wi400InputButton('SEARCH_BUTTON');
		$myButton->setLabel("Seleziona");
		$myButton->setAction($azione);
		$myButton->setForm("QUERY_LIST");
		$myButton->setValidation(true);
		$searchAction->addButton($myButton);
		
		$searchAction->dispose();
	}
	else if($actionContext->getForm()=="QUERY_LIST") {
		$miaLista = new wi400List($azione."_".$actionContext->getForm(), !$isFromHistory);
		
		$miaLista->setField($select);
		$miaLista->setFrom($from);
		$miaLista->setWhere($where);
//		$miaLista->setOrder("ID_QUERY");
		$miaLista->setOrder("DES_QUERY");
		$miaLista->setGroup($group_by);
		
//		echo "SQL: ".$miaLista->getSql()."<br>";
		
		$miaLista->setSelection("MULTIPLE");
		
		$distr_col = new wi400Column("DISTRIBUZIONE", "Distribuzione<br>Query", "STRING", "center");
		$distr_col->setActionListId("DISTRIBUZIONE");
		$distr_col->setDefaultValue("MODIFICA");
		$distr_col->setDecorator("ICONS");
		$distr_col->setSortable(false);
		$distr_col->setExportable(false);
		
		$rim_col = new wi400Column("RIMUOVI", "Rimuovi<br>Associazione", "STRING", "center");
		$rim_col->setActionListId("RIMUOVI");
		$rim_col->setDefaultValue("BIN");
		$rim_col->setDecorator("ICONS");
		$rim_col->setSortable(false);
		$rim_col->setExportable(false);
		
		$det_col = new wi400Column("DETTAGLIO", "Dettaglio<br>Query", "STRING", "center");
		$det_col->setActionListId("DETTAGLIO");
		$det_col->setDefaultValue("SEARCH");
		$det_col->setDecorator("ICONS");
		$det_col->setSortable(false);
		$det_col->setExportable(false);
		
		$del_col = new wi400Column("ELIMINA", "Elimina<br>Query", "STRING", "center");
		$del_col->setActionListId("ELIMINA");
		$del_col->setDefaultValue("NO");
		$del_col->setDecorator("ICONS");
		$del_col->setSortable(false);
		$del_col->setExportable(false);
		
		$numCond = array();
		$numCond[] = array('EVAL:$row["NUM_USERS"]>1', 'wi400_grid_yellow');
		$numCond[] = array('EVAL:1==1', '');
		
		$num_col = new wi400Column("NUM_USERS", "Numero<br>Utenti", "INTEGER", "right");
		$num_col->setStyle($numCond);
		
		$creaCond = array();
		$creaCond[] = array('EVAL:$row["USERINS"]=="'.$idUser.'"', 'wi400_grid_green');
		$creaCond[] = array('EVAL:1==1', '');
		
		$crea_col = new wi400Column("USERINS", "Utente<br>Creazione");
		$crea_col->setStyle($creaCond);
		
		$modCond = array();
		$modCond[] = array('EVAL:$row["USERINS"]!=$row["USERMOD"]', 'wi400_grid_orange');
		$modCond[] = array('EVAL:1==1', '');
		
		$mod_col = new wi400Column("USERMOD", "Utente<br>Modifica");
		$mod_col->setStyle($modCond);
		
		$miaLista->setCols(array(
			$distr_col,
			$rim_col,
			$det_col,
			$del_col,
			new wi400Column("ID_QUERY", "ID<br>Query", "INTEGER", "right"),
			new wi400Column("DES_QUERY", "Descrizione<br>Query"),
			new wi400Column("AREA", "Area<br>Query"),
			new wi400Column("FUNZIONE", "Funz<br>Query"),
			new wi400Column("NOTE", "Note<br>Query"),
			$num_col,
			$crea_col,
			$mod_col
		));
		
		$miaLista->addKey("ID_QUERY");
//		$miaLista->addKey("DES_QUERY");

		$myFilter = new wi400Filter("DES_QUERY", "Descrizione Qery");
		$myFilter->setFast(true);
		$myFilter->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$miaLista->addFilter($myFilter);
		
		$myFilter = new wi400Filter("ID_QUERY", "ID Query");
		$myFilter->setSqlKey("a.ID_QUERY");
		$myFilter->setFast(true);
		$myFilter->setCase('UPPER');
		$miaLista->addFilter($myFilter);
		
		$myFilter = new wi400Filter("TIPO_QUERY", "Tipo query", "SELECT", "");
		$filterValues = array(
			"(SQL_QUERY=='')" => "Query indirizzate",
			"(SQL_QUERY<>'')" => "Query libere",
		);
		$myFilter->setSource($filterValues);
		$miaLista->addFilter($myFilter);
		
		$myFilter = new wi400Filter("AREA", "Area");
//		$myFilter->setFast(true);
		$myFilter->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$miaLista->addFilter($myFilter);
		
		$myFilter = new wi400Filter("FUNZIONE", "Funzione");
//		$myFilter->setFast(true);
		$myFilter->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$miaLista->addFilter($myFilter);
		
		$action = new wi400ListAction();
		$action->setId("DISTRIBUZIONE");
		$action->setAction($azione);
		$action->setForm("USER_LIST");
		$action->setLabel("Distribuzione Query");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		if(!empty($user_src)) {
			$action = new wi400ListAction();
			$action->setAction($azione);
			$action->setForm("ADD_QUERY_SEL");
			$action->setLabel("Aggiungi Query");
			$action->setSelection("NONE");
			$action->setTarget("WINDOW");
			$miaLista->addAction($action);
			
			$action = new wi400ListAction();
			$action->setId("RIMUOVI");
			$action->setAction($azione);
			$action->setForm("RIMUOVI_QUERY_SEL");
			$action->setLabel("Rimuovi Associazione");
			$action->setSelection("SINGLE");
			$action->setConfirmMessage("Rimuovere l'associazione dell'utente alla query selezionata?");
			$action->setShow(false);
			$miaLista->addAction($action);
			
			$action = new wi400ListAction();
			$action->setAction($azione);
			$action->setForm("RIMUOVI_QUERY");
			$action->setLabel("Rimuovi Associazioni");
			$action->setSelection("MULTIPLE");
			$action->setConfirmMessage("Rimuovere l'associazione dell'utente alle query selezionate?");
			$miaLista->addAction($action);
		}
		else {
			$miaLista->removeCol("RIMUOVI");
		}
		
		$action = new wi400ListAction();
		$action->setId("DETTAGLIO");
		$action->setAction("QUERY_TOOL_DB");
		$action->setForm("DEFAULT");
		$action->setGateway("QUERY_MANAGER_DB");
		$action->setLabel("Dettaglio Query");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("MOD_DES");
		$action->setTarget("WINDOW");
		$action->setLabel("Modifica descrizione query");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		$action = new wi400ListAction();
		$action->setId("ELIMINA");
		$action->setAction($azione);
		$action->setForm("DELETE");
		$action->setLabel("Elimina Query");
		$action->setSelection("SINGLE");
		$action->setConfirmMessage("Eliminare la query selezionata?");
		$miaLista->addAction($action);
		
		listDispose($miaLista);
	}
	else if($actionContext->getForm()=="USER_LIST") {
		$idList = $azione."_".$actionContext->getForm();
		
		$miaLista = new wi400List($idList, !$isFromHistory);
		
		$miaLista->setFrom($from);
		$miaLista->setWhere($where);
		$miaLista->setOrder("USER_NAME");
		
//		echo "SQL: ".$miaLista->getSql()."<br>";
		
		$miaLista->setSelection("MULTIPLE");
		
		$rim_col = new wi400Column("RIMUOVI", "Rimuovi<br>Associazione", "STRING", "center");
		$rim_col->setActionListId("RIMUOVI");
		$rim_col->setDefaultValue("BIN");
		$rim_col->setDecorator("ICONS");
		$rim_col->setSortable(false);
		$rim_col->setExportable(false);
		
		$userCond = array();
		$userCond[] = array('EVAL:$row["USER_NAME"]=="'.$idUser.'"', 'wi400_grid_green');
		$userCond[] = array('EVAL:$row["USER_NAME"]=="'.$user_src.'"', 'wi400_grid_yellow');
		$userCond[] = array('EVAL:1==1', '');
		
		$user_col = new wi400Column("USER_NAME", "Utente");
		$user_col->setStyle($userCond);
		
		$miaLista->setCols(array(
			$rim_col,
			$user_col
		));
		
		$miaLista->addKey("ID_QUERY");
		$miaLista->addKey("USER_NAME");
		
		$myFilter = new wi400Filter("USER_NAME","Utente");
		$myFilter->setFast(true);
		$myFilter->setCase('UPPER');
		$miaLista->addFilter($myFilter);
		
		$action = new wi400ListAction();
		$action->setAction($azione);
//		$action->setForm("ADD_USER_SEL");
		$action->setForm("ADD_USER_SEL_INT");
		$action->setLabel("Aggiungi utenti");
		$action->setSelection("NONE");
		$action->setTarget("WINDOW");
		$miaLista->addAction($action);
		
		$action = new wi400ListAction();
		$action->setId("RIMUOVI");
		$action->setAction($azione);
		$action->setForm("RIMUOVI_USER_SEL");
		$action->setLabel("Rimuovi Associazione");
		$action->setSelection("SINGLE");
		$action->setConfirmMessage("Rimuovere l'associazione dell'utente selezionato alla query?");
		$action->setShow(false);
		$miaLista->addAction($action);
		
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("RIMUOVI_USER");
		$action->setLabel("Rimuovi Associazioni");
		$action->setConfirmMessage("Rimuovere l'associazione degli utenti selezionati alla query?");
		$action->setSelection("MULTIPLE");
		$miaLista->addAction($action);
		
		listDispose($miaLista);
	}	
	else if($actionContext->getForm()=="ADD_USER_SEL") {
		$idDetail = $azione."_".$actionContext->getForm();
		
		$azioniDetail = new wi400Detail($idDetail, false);
		
		$azioniDetail->isEditable(true);
		$azioniDetail->setSaveDetail(true);
		
		// Utente
		$myField = new wi400InputText('USER_SEL');
		$myField->setLabel("Utente");
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$myField->setCase("UPPER");
		$myField->addValidation('required');
		$myField->setValue($user_sel);
		$myField->setShowMultiple(true);
		
		$decodeParameters = array(
			'TYPE' => 'common',
			'TABLE_NAME' => $from,
//			'COLUMN' => $id_user_desc,
			'COLUMN' => "DES",
			'LU_SELECT' => "FIRST_NAME!!' '!!$id_user_desc as DES",
			'KEY_FIELD_NAME' => $id_user_name,
			'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE",$from);
		$myLookUp->addParameter("CAMPO",$id_user_name);
		$myLookUp->addParameter("DESCRIZIONE","DES");
		$myLookUp->addParameter("LU_WHERE", $where);
		$myLookUp->addParameter("LU_FIELDS", "$id_user_name, FIRST_NAME!!' '!!$id_user_desc as DES");
		$myLookUp->addParameter("LU_FILTER_SQL_KEY", "FIRST_NAME!!' '!!$id_user_desc");
		$myField->setLookUp($myLookUp);
		
		$azioniDetail->addField($myField);
		
		$myButton = new wi400InputButton('SAVE_BUTTON');
		$myButton->setLabel("Salva");
		$myButton->setAction($azione);
		$myButton->setForm("ADD_USER");
		$myButton->setValidation(true);
		$azioniDetail->addButton($myButton);
		
		$myButton = new wi400InputButton('CLOSE_BUTTON');
		$myButton->setLabel("Chiudi");
		$myButton->setAction("CLOSE");
		$myButton->setForm("CLOSE_LOOKUP");
		$myButton->addParameter("CLEAN_DETAIL", $idDetail);
		$azioniDetail->addButton($myButton);
		
		$azioniDetail->dispose();
	}
/*
	else if($actionContext->getForm()=="ADD_QUERY_SEL") {
		$idDetail = $azione."_".$actionContext->getForm();
		
		$azioniDetail = new wi400Detail($idDetail, false);
		
		// Query
		$myField = new wi400InputText('QUERY_SEL');
		$myField->setLabel("Query");
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$myField->setCase("UPPER");
		$myField->addValidation('required');
		$myField->setValue($query_sel);
		$myField->setShowMultiple(true);
		
		$decodeParameters = array(
			'TYPE' => 'common',
			'TABLE_NAME' => "TABQUERY",
			'COLUMN' => "DES_QUERY",
			'KEY_FIELD_NAME' => "ID_QUERY",
			'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE", "TABQUERY");
		$myLookUp->addParameter("CAMPO", "ID_QUERY");
		$myLookUp->addParameter("DESCRIZIONE", "DES_QUERY");
		$myLookUp->addParameter("LU_WHERE", "ID_QUERY not in (ID_QUERY from USERQUERY where USER_NAME='$user_src')");
		$myField->setLookUp($myLookUp);
		
		$azioniDetail->addField($myField);
		
		$myButton = new wi400InputButton('SAVE_BUTTON');
		$myButton->setLabel("Salva");
		$myButton->setAction($azione);
		$myButton->setForm("ADD_QUERY");
		$myButton->setValidation(true);
		$azioniDetail->addButton($myButton);
		
		$myButton = new wi400InputButton('CLOSE_BUTTON');
		$myButton->setLabel("Annulla");
		$myButton->setAction("CLOSE");
		$myButton->setForm("CLOSE_LOOKUP");
		$myButton->addParameter("CLEAN_DETAIL", $idDetail);
		$azioniDetail->addButton($myButton);
		
		$azioniDetail->dispose();
	}
*//*
	else if($actionContext->getForm()=="ADD_USER_SEL") {
		$miaLista = new wi400List($azione."_".$actionContext->getForm()."_LIST", true);
	
		$miaLista->setFrom($from);
		$miaLista->setWhere($where);
		$miaLista->setOrder($id_user_name);
	
//		echo "SQL: ".$miaLista->getSql()."<br>";
	
		$miaLista->setSelection("MULTIPLE");
	
		$miaLista->setCols(array(
			new wi400Column($id_user_name, "Utente"),
			new wi400Column($id_user_desc, "Descrizione<br>Utente")
		));
	
		$miaLista->addKey($id_user_name);
		
		$myFilter = new wi400Filter($id_user_name, "Utente");
		$myFilter->setFast(true);
		$myFilter->setCase('UPPER');
		$miaLista->addFilter($myFilter);
		
		$myFilter = new wi400Filter($id_user_desc, "Descrizione Utente");
		$myFilter->setFast(true);
		$myFilter->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$miaLista->addFilter($myFilter);
	
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("ADD_USER");
		$action->setLabel("Aggiungi");
		$action->setSelection("MULTIPLE");
		$miaLista->addAction($action);
	
		listDispose($miaLista);
*//*		
		$myButton = new wi400InputButton('SAVE');
		$myButton->setLabel("Aggiungi");
		$myButton->setAction($azione);
		$myButton->setForm("ADD_USER");
		$buttonsBar[] = $myButton;
		
		$myButton = new wi400InputButton("CLOSE_BUTTON");
		$myButton->setAction("CLOSE");
		$myButton->setForm("CLOSE_LOOKUP");
		$myButton->setLabel("Chiudi");
		$buttonsBar[] = $myButton;
*/
//	}
	else if($actionContext->getForm()=="ADD_QUERY_SEL") {
		$miaLista = new wi400List($azione."_".$actionContext->getForm()."_LIST", !$isFromHistory);

		$miaLista->setFrom($from);
		$miaLista->setWhere($where);
//		$miaLista->setOrder("ID_QUERY");
		$miaLista->setOrder("DES_QUERY");

//		echo "SQL: ".$miaLista->getSql()."<br>";

		$miaLista->setSelection("MULTIPLE");

		$miaLista->setCols(array(
			new wi400Column("ID_QUERY", "ID<br>Query", "INTEGER", "right"),
			new wi400Column("DES_QUERY", "Descrizione<br>Query"),
			new wi400Column("AREA", "Area<br>Query"),
			new wi400Column("FUNZIONE", "Funz<br>Query"),
			new wi400Column("NOTE", "Note<br>Query"),
		));

		$miaLista->addKey("ID_QUERY");
		
		$myFilter = new wi400Filter("ID_QUERY", "ID Query");
		$myFilter->setFast(true);
		$myFilter->setCase('UPPER');
		$miaLista->addFilter($myFilter);
		
		$myFilter = new wi400Filter("DES_QUERY", "Descrizione Qery");
		$myFilter->setFast(true);
		$myFilter->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$miaLista->addFilter($myFilter);
		
		$myFilter = new wi400Filter("AREA", "Area");
//		$myFilter->setFast(true);
		$myFilter->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$miaLista->addFilter($myFilter);
		
		$myFilter = new wi400Filter("FUNZIONE", "Funzione");
//		$myFilter->setFast(true);
		$myFilter->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$miaLista->addFilter($myFilter);

		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("ADD_QUERY");
		$action->setLabel("Aggiungi");
		$action->setSelection("MULTIPLE");
		$miaLista->addAction($action);
		
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("ADD_QUERY_ALL");
		$action->setLabel("Aggiungi TUTTE");
		$action->setSelection("NONE");
		$miaLista->addAction($action);

		listDispose($miaLista);
/*		
		$myButton = new wi400InputButton('SAVE');
		$myButton->setLabel("Aggiungi");
		$myButton->setAction($azione);
		$myButton->setForm("ADD_QUERY");
		$buttonsBar[] = $myButton;
		
		$myButton = new wi400InputButton("CLOSE_BUTTON");
		$myButton->setAction("CLOSE");
		$myButton->setForm("CLOSE_LOOKUP");
		$myButton->setLabel("Chiudi");
		$buttonsBar[] = $myButton;
*/		
	}
	else if($actionContext->getForm()=="MOD_DES") {
		$searchAction = new wi400Detail($azione."_MOD_DES_DET", false);
		$searchAction->setTitle(_t("PARAMETRI"));
		$searchAction->isEditable(true);
	
		// Query
		$myField = new wi400InputText('ID_QUERY');
		$myField->setLabel("Query");
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$myField->setCase("UPPER");
		$myField->setValue($id_query);
		$myField->setReadonly(true);
		$searchAction->addField($myField);
	
		// Descrizione query
		$myField = new wi400InputText('DES_QUERY');
		$myField->setLabel("Descrizione Query");
		$myField->setSize(100);
		$myField->setMaxLength(100);
//		$myField->setCase("UPPER");
		$myField->addValidation('required');
		$myField->setValue($des_query);
		$searchAction->addField($myField);
		
		// Area query
		$myField = new wi400InputText('AREA');
		$myField->setLabel("Area");
		$myField->setSize(20);
		$myField->setMaxLength(20);
//		$myField->setCase("UPPER");
//		$myField->addValidation('required');
		$myField->setValue($area_query);
		$searchAction->addField($myField);
		
		// Funzione query
		$myField = new wi400InputText('FUNZIONE');
		$myField->setLabel("Funzione");
		$myField->setSize(20);
		$myField->setMaxLength(20);
//		$myField->setCase("UPPER");
//		$myField->addValidation('required');
		$myField->setValue($funz_query);
		$searchAction->addField($myField);
		
		// Note query
//		$myField = new wi400InputText('NOTE');
		$myField = new wi400InputTextArea('NOTE');
		$myField->setLabel("Note");
		$myField->setSize(100);
//		$myField->setMaxLength(200);
		$myField->setRows(2);
//		$myField->setCase("UPPER");
//		$myField->addValidation('required');
		$myField->setValue($note_query);
		$searchAction->addField($myField);
	
		$myButton = new wi400InputButton('SAVE_BUTTON');
		$myButton->setLabel("Salva");
		$myButton->setAction($azione);
		$myButton->setForm("SAVE_DES");
		$myButton->setConfirmMessage("Salvare?");
		$searchAction->addButton($myButton);
		
		$myButton = new wi400InputButton('CLOSE_BUTTON');
		$myButton->setLabel("Chiudi");
		$myButton->setAction("CLOSE");
		$myButton->setForm("CLOSE_LOOKUP");
		$searchAction->addButton($myButton);
	
		$searchAction->dispose();
	}