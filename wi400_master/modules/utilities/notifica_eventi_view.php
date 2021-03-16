<?php

	$spacer = new wi400Spacer();
	
	if(in_array($actionContext->getForm(), array("LIST", "DESTINATARI"))) {
		$ListDetail = new wi400Detail($azione."_".$actionContext->getForm()."_DET");
//		$ListDetail->setColsNum(2);

		if($actionContext->getForm()=="LIST") {
			if($tipo_evento=="" && $stato=="") {
				$fieldDetail = new wi400Text("EVENTI");
				$fieldDetail->setLabel("Eventi");
				$fieldDetail->setValue("TUTTI");
				$ListDetail->addField($fieldDetail);
			}
			else {
				if($tipo_evento!="") {
					$fieldDetail = new wi400Text("TIPO_EVENTO");
					$fieldDetail->setLabel("Tipo Evento");
					$fieldDetail->setValue($tipo_evento);
					$ListDetail->addField($fieldDetail);
				}
				
				if($stato!="") {
					$fieldDetail = new wi400Text("STATO");
					$fieldDetail->setLabel("Stato");
					$fieldDetail->setValue($stato_evento_array[$stato]);
					$ListDetail->addField($fieldDetail);
				}
			}
		}
		else {
			$fieldDetail = new wi400Text("ID");
			$fieldDetail->setLabel("ID");
			$fieldDetail->setValue($id);
			$ListDetail->addField($fieldDetail);
			
			$fieldDetail = new wi400Text("TIPO_EVENTO");
			$fieldDetail->setLabel("Tipo Evento");
			$fieldDetail->setValue($tipo_evento);
			$ListDetail->addField($fieldDetail);
			
			$fieldDetail = new wi400Text("DES");
			$fieldDetail->setLabel("Descrizione Evento");
			$fieldDetail->setValue($des);
			$ListDetail->addField($fieldDetail);
			
			$fieldDetail = new wi400Text("STATO");
			$fieldDetail->setLabel("Stato");
			$fieldDetail->setValue($stato);
			$ListDetail->addField($fieldDetail);
			
			$fieldDetail = new wi400Text("DATA_ORA_INS");
			$fieldDetail->setLabel("Data e ora inserimento");
			$fieldDetail->setValue(wi400_format_STRING_COMPLETE_TIMESTAMP($data_ora_ins));
			$ListDetail->addField($fieldDetail);
			
			$fieldDetail = new wi400Text("DATA_ORA_NTF");
			$fieldDetail->setLabel("Data e ora notifica");
			$fieldDetail->setValue(wi400_format_STRING_COMPLETE_TIMESTAMP($data_ora_ntf));
			$ListDetail->addField($fieldDetail);
		}
		
		$ListDetail->dispose();
		
		$spacer->dispose();
	}
	
	if($actionContext->getForm()=="DEFAULT") {
		$searchAction = new wi400Detail($azione."_SRC", true);
		$searchAction->setTitle('Parametri');
		$searchAction->isEditable(true);
		$searchAction->setSaveDetail(true);
		
		// Tipo di evento
		$mySelect = new wi400InputSelect('TIPO_EVENTO');
		$mySelect->setLabel("Tipo evento");
		$mySelect->setFirstLabel("Seleziona...");
		$mySelect->setOptions($tipo_evento_array);
		$mySelect->setValue($tipo_evento);
		$searchAction->addField($mySelect);
		
		// Stato evento
		$mySelect = new wi400InputSelect('STATO');
		$mySelect->setLabel("Stato");
		$mySelect->setFirstLabel("Seleziona...");
		$mySelect->setOptions($stato_evento_array);
		$mySelect->setValue($stato);
//		echo "SEL:<pre>"; print_r($mySelect->getOptions()); echo "</pre>";
		$searchAction->addField($mySelect);
		
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
		
		$fields = "a.*, 
			DATA_INS!!ORA_INS as DATA_ORA_INS, 
			DATA_NTF!!ORA_NTF as DATA_ORA_NTF,
			(case when STATO='0' then 'Da notificare' else 'Notificato' end) as DES_STATO";
		
		$miaLista->setField($fields);
		$miaLista->setFrom("FEVENTLST a");
		$miaLista->setWhere($where);
		$miaLista->setOrder("ID desc");
		
		echo "SQL: ".$miaLista->getSql()."<br>";
		
		$miaLista->setSelection("MULTIPLE");
		
		// Tipo
		$tipo_col = new wi400Column("TIPO", "Tipo evento");
		
		$tipo_cond = array();
		foreach($tipo_evento_color as $key => $val) {
			$tipo_cond[] = array('EVAL:$row["TIPO"]=="'.$key.'"', $val);
		}

		$tipo_col->setStyle($tipo_cond);
		
		// Stato
		$stato_col = new wi400Column("DES_STATO", "Stato");

		$stato_cond = array();
		$stato_cond[] = array('EVAL:$row["STATO"]=="0"', "wi400_grid_yellow");
		$stato_cond[] = array('EVAL:1==1');
		
		$stato_col->setStyle($stato_cond);
		
		$miaLista->setCols(array(
			new wi400Column("ID", "ID"),
			$tipo_col,
			new wi400Column("DES", "Descrizione Evento"),
//			new wi400Column("STATO", "Stato"),
			$stato_col,
			new wi400Column("DATA_ORA_INS", "Data e ora<br>inserimento", "STRING_COMPLETE_TIMESTAMP"),
			new wi400Column("DATA_ORA_NTF", "Data e ora<br>notifica", "STRING_COMPLETE_TIMESTAMP")
		));
		
		$icon_cols = array();
		foreach($action_icons_array as $key) {
			$val = $des_icons_array[$key];
				
			$col = new wi400Column($key, $val, "STRING", "center");
			$col->setActionListId($key);
				
			$cond = array();
			$cond[] = array('EVAL:1==1', $type_icons_array[$key]);
				
			$col->setDefaultValue($cond);
			$col->setDecorator("ICONS");
			$col->setSortable(false);
			$col->setExportable(false);
				
			$miaLista->addCol($col);
		}
		
		$miaLista->addKey("ID");
		$miaLista->addKey("TIPO");
		$miaLista->addKey("DES");
		$miaLista->addKey("STATO");
		$miaLista->addKey("DATA_ORA_INS");
		$miaLista->addKey("DATA_ORA_NTF");
		
		// Filtri rapidi
		$myFilter = new wi400Filter("ID","ID");
		$myFilter->setFast(true);
		$myFilter->setCase('UPPER');
		$miaLista->addFilter($myFilter);
		
		$myFilter = new wi400Filter("DES","Descrizione Evento");
		$myFilter->setFast(true);
		$myFilter->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$miaLista->addFilter($myFilter);
		
		// Filtri avanzati
		$myFilter = new wi400Filter("TIPO","Tipo evento","SELECT","");
		$filterValues = array();
		foreach($tipo_evento_array as $key => $val) {
			$filterValues["TIPO='$key'"] = $val;
		}
//		echo "FILTERS:"; print_r($filterValues); echo "<br>";
		$myFilter->setSource($filterValues);
		$miaLista->addFilter($myFilter);
		
		$myFilter = new wi400Filter("STATO","Stato","SELECT","");
		$filterValues = array();
		foreach($stato_evento_array as $key => $val) {
			$filterValues["STATO='$key'"] = $val;
		}
//		echo "FILTERS:"; print_r($filterValues); echo "<br>";
		$myFilter->setSource($filterValues);
		$miaLista->addFilter($myFilter);
		
		// Destinatari
		$action = new wi400ListAction();
		$action->setLabel("Destinatari");
		$action->setId("DESTINATARI");
		$action->setAction($azione);
		$action->setForm("DESTINATARI");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		// Notifica
		$action = new wi400ListAction();
		$action->setLabel("Notifica");
//		$action->setId("NOTIFICA");
		$action->setAction($azione);
		$action->setForm("NOTIFICA");
		$action->setSelection("MULTIPLE");
		$action->setConfirmMessage("Notificare gli eventi selezionati?");
		$miaLista->addAction($action);
		
		// Notifica Nuovi Eventi (STATO=='0')
		$action = new wi400ListAction();
		$action->setLabel("Notifica Nuovi Eventi");
		$action->setAction("NOTIFICA_EVENTI_BATCH");
		$action->setForm("DEFAULT");
		$action->setSelection("NONE");
		$action->setConfirmMessage("Notificare tutti i nuovi eventi (Stato 'Da notificare')?");
		$miaLista->addAction($action);
		
		// Inoltra notifica
		$action = new wi400ListAction();
		$action->setLabel("Inoltra Notifica");
//		$action->setId("INOLTRA");
		$action->setAction($azione);
		$action->setForm("INOLTRA_SEL");
		$action->setSelection("MULTIPLE");
		$action->setTarget("WINDOW");
//		$action->setConfirmMessage("Inoltrare gli eventi selezionati?");
		$miaLista->addAction($action);
		
		listDispose($miaLista);
	}
	else if($actionContext->getForm()=="DESTINATARI") {
		$miaLista = new wi400List($azione."_".$actionContext->getForm()."_LIST", !$isFromHistory);
		
		$select = "a.*";
		
		$matpto_cond = array();
		foreach($tipo_dest_array as $key => $val) {
			$matpto_cond[] = "when TIPO='$key' then '$val'";
		}
		$select .= ", (case ".implode(" ", $matpto_cond)." end) as DES_TIPO";
		
		$miaLista->setField($select);		
		$miaLista->setFrom("FEVENTNTF a");
		$miaLista->setWhere("ID='$id'");
		$miaLista->setOrder("EMAIL");
		
		$miaLista->setSelection("MULTIPLE");
		
		$des_tipo_dest_col = new wi400Column("DES_TIPO", "Tipo<br>destinatario");
		
		$tipo_dest_cond = array();
		foreach($tipo_dest_colors as $key => $val) {
			$tipo_dest_cond[] = array('EVAL:$row["TIPO"]=="'.$key.'"', $val);
		}
		
		$des_tipo_dest_col->setStyle($tipo_dest_cond);
		
		$miaLista->setCols(array(
			new wi400Column("EMAIL", "E-mail"),
			$des_tipo_dest_col
		));
		
		$miaLista->addKey("ID");
		$miaLista->addKey("EMAIL");
		$miaLista->addKey("TIPO");
		
		// Filtri rapidi
		$myFilter = new wi400Filter("EMAIL","E-mail");
		$myFilter->addValidation('email');
		$myFilter->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$miaLista->addFilter($myFilter);
		
		// Filtri avanzati
		$myFilter = new wi400Filter("TIPO","Tipo destinatario","SELECT","");
		$filterValues = array();
		foreach($tipo_dest_array as $key => $val) {
			$filterValues["TIPO='$key'"] = $val;
		}
//		echo "FILTERS:"; print_r($filterValues); echo "<br>";
		$myFilter->setSource($filterValues);
		$miaLista->addFilter($myFilter);
/*		
		// Aggiungi destinatario
		$action = new wi400ListAction();
		$action->setLabel("Aggiungi destinatario");
		$action->setId("NEW_DEST");
		$action->setAction($azione);
		$action->setForm("NEW_DEST");
		$action->setTarget("WINDOW");
		$action->setSelection("NONE");
		$miaLista->addAction($action);
		
		// Elimina destinatario
		$action = new wi400ListAction();
		$action->setLabel("Elimina destinatario");
		$action->setId("DEL_DEST");
		$action->setAction($azione);
		$action->setForm("DEL_DEST");
		$action->setSelection("MULIPLE");
		$miaLista->addAction($action);
*/		
		listDispose($miaLista);
	}
	else if($actionContext->getForm()=="NEW_DEST") {
	
	}
	else if($actionContext->getForm()=="INOLTRA_SEL") {
		$emailDetail = new wi400Detail($azione."_INOLTRA_SEL_DET", true);
		$emailDetail->setTitle("Inoltro Notifica Evento");
		$emailDetail->isEditable(true);
		
		// recupero l'indirizzo e-mail dell'utente loggato
		$userMail = getUserMail($_SESSION['user']);
		
		// FROM
		$myField = new wi400InputText('FROM');
		$myField->setLabel(_t("DA"));
		$myField->addValidation("required");
		$myField->setMaxLength(100);
		$myField->setSize(50);
		$myField->setInfo(_t("DIGITARE_MITTENTE"));
		if($from=="") {
			if($settings['self_export']===true)
				$myField->setValue($userMail);
			else
				$myField->setValue($settings['smtp_user']);	
		}
		else
			$myField->setValue($from);
		$emailDetail->addField($myField);
		
		// TO
		$myField = new wi400InputText('TO');
		$myField->setLabel(_t("A"));
		$myField->setShowMultiple(true);
		$myField->addValidation("required");
		$myField->setMaxLength(100);
		$myField->setSize(50);
		$myField->setInfo(_t("DIGITARE_DESTINATARIO"));
//		$myField->setValue($to_array);
			
		if($lookup_email===true) {
			$myLookUp =new wi400LookUp("LU_GENERICO");
			$myLookUp->addParameter("FILE",$users_table);
			$myLookUp->addParameter("CAMPO","EMAIL");
			$myLookUp->addParameter("DESCRIZIONE","USER_NAME");
			$myLookUp->addParameter("LU_ORDER","USER_NAME ASC");	
			$myField->setLookUp($myLookUp);
		}
		
		$emailDetail->addField($myField);
		
		// CC
		$myField = new wi400InputText('CC');
		$myField->setLabel(_t("CC"));
		$myField->setShowMultiple(true);
		$myField->setMaxLength(100);
		$myField->setSize(50);
		$myField->setInfo(_t("DIGITARE_DESTINATARIO"));
//		$myField->setValue($cc_array);
		
		if($lookup_email===true) {
			$myLookUp =new wi400LookUp("LU_GENERICO");
			$myLookUp->addParameter("FILE",$users_table);
			$myLookUp->addParameter("CAMPO","EMAIL");
			$myLookUp->addParameter("DESCRIZIONE","USER_NAME");
			$myLookUp->addParameter("LU_ORDER","USER_NAME ASC");
			$myField->setLookUp($myLookUp);
		}
		
		$emailDetail->addField($myField);
		
		// BCC
		$myField = new wi400InputText('BCC');
		$myField->setLabel(_t("BCC"));
		$myField->setShowMultiple(true);
		$myField->setMaxLength(100);
		$myField->setSize(50);
		$myField->setInfo(_t("DIGITARE_DESTINATARIO"));
//		$myField->setValue($bcc_array);
	
		if($lookup_email===true) {
			$myLookUp =new wi400LookUp("LU_GENERICO");
			$myLookUp->addParameter("FILE",$users_table);
			$myLookUp->addParameter("CAMPO","EMAIL");
			$myLookUp->addParameter("DESCRIZIONE","USER_NAME");
			$myLookUp->addParameter("LU_ORDER","USER_NAME ASC");
			$myField->setLookUp($myLookUp);
		}
		
		$emailDetail->addField($myField);
				
		$myButton = new wi400InputButton('INVIO_BUTTON');
		$myButton->setLabel(_t("INVIA"));
		$myButton->setAction($azione);
		$myButton->setForm("INOLTRA");
		$myButton->setValidation(true);
		$emailDetail->addButton($myButton);
		
		$myButton = new wi400InputButton('BACK_BUTTON');
		$myButton->setLabel("Chiudi");
		$myButton->setScript('closeLookUp()');
		$emailDetail->addButton($myButton);
		
		$emailDetail->dispose();
	}