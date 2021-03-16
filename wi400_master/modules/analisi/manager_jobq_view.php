<?php

	if($actionContext->getForm()=="DEFAULT") {
		$miaLista = new wi400List($azione."_LIST", !$isFromHistory);

		$select = "a.*";
		$select .= ", ".get_query_case_cond($stato_array, "STATUS", "DES_STATO");
		
		$miaLista->setField($select);
		$miaLista->setFrom("ZJOBQUEE a");
		$miaLista->setOrder("ID");
		
		$miaLista->setSelection("SINGLE");
		
//		$miaLista->setIncludeFile(rtvModuloAzione($azione), "manager_jobq_functions.php");
/*		
		$cols = getColumnListFromTable("ZJOBQUEE");
//		echo "COLONNE:<pre>"; print_r($cols); echo "</pre>";

		$col = $cols["LASTRUN"];
		$col->setFormat("COMPLETE_TIMESTAMP");
*/		
		// Stato
		$stato_col = new wi400Column("DES_STATO", "Stato");
		
		$stato_cond = array();
		$stato_cond[] = array('EVAL:$row["STATUS"]=="0"', "wi400_grid_red");
		$stato_cond[] = array('EVAL:$row["STATUS"]=="1"', "wi400_grid_green");
		$stato_cond[] = array('EVAL:1==1', "");
		
		$stato_col->setStyle($stato_cond);
		
		// Tipo
		$tipo_col = new wi400Column("TIPO", "Tipo Contenuto");
		
		$tipo_cond = array();
		$tipo_cond[] = array('EVAL:$row["TIPO"]=="REDIS"', "wi400_grid_aqua");
		$tipo_cond[] = array('EVAL:$row["TIPO"]=="AS400"', "wi400_grid_yellow");
		$tipo_cond[] = array('EVAL:$row["TIPO"]=="DB"', "wi400_grid_orange");
		$tipo_cond[] = array('EVAL:1==1', "");
		
		$tipo_col->setStyle($tipo_cond);
		
		// Dettaglio
		$col_det = new wi400Column("DETTAGLIO", "Dettaglio", "STRING", "center");
		$col_det->setActionListId("DETTAGLIO");
		$col_det->setDefaultValue("SEARCH");
		$col_det->setDecorator("ICONS");
		
		$check_time = getDb2Timestamp();
//		echo "CHECK_TIME: $check_time<br>";
		
		$cols = array(
			$col_det,
			new wi400Column("ID", "Codice JOBQ"),
			$tipo_col,
			new wi400Column("LASTRUN", "ultima esecuzione", "COMPLETE_TIMESTAMP"),
//			new wi400Column("STATUS", "Stato", "STRING", "center"),
			$stato_col,
			new wi400Column("NAMOBJ", "Oggetto AS400"),
			new wi400Column("LIBOBJ", "Libreria AS400"),
			new wi400Column("KEYOBJ", "Chiave Oggetto AS400"),
			new wi400Column("IPSERV", "IP del server"),
			new wi400Column("IPPORT", "Porta del server"),
			new wi400Column("AZIGO", "AZIONE Partenza CODA"),
			new wi400Column("AZIRUN", "AZIONE Esecuzione CODA"),
		);
		
		$miaLista->setCols($cols);
		
		$miaLista->addKey("ID");
		$miaLista->addKey("TIPO");
		
		$myFilter = new wi400Filter("ID","Codice JOBQ");
		$myFilter->setFast(true);
		$myFilter->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$miaLista->addFilter($myFilter);
		
		$myFilter = new wi400Filter("TIPO","Tipo Conenuto","SELECT","");
		$filterValues = array();
		foreach($tipo_array as $key => $val) {
			$filterValues["TIPO='$key'"] = $val;
		}
//		echo "FILTERS:"; print_r($filterValues); echo "<br>";
		$myFilter->setSource($filterValues);
		$miaLista->addFilter($myFilter);
		
		$myFilter = new wi400Filter("STATUS","Stato","SELECT","");
		$filterValues = array();
		foreach($stato_array as $key => $val) {
			$filterValues["STATUS='$key'"] = $val;
		}
//		echo "FILTERS:"; print_r($filterValues); echo "<br>";
		$myFilter->setSource($filterValues);
		$miaLista->addFilter($myFilter);
		
		// Dettaglio
		$action = new wi400ListAction();
		$action->setId("DETTAGLIO");
		$action->setAction($azione);
		$action->setForm("MOD_JOBQ");
		$action->setLabel("Dettaglio");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		// Nuova OTM
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("NEW_JOBQ");
		$action->setLabel("Nuovo JOBQ");
		$action->setSelection("NONE");
		$miaLista->addAction($action);
/*		
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("DELETE_JOBQ");
		$action->setLabel("Elimina JOBQ");
		$action->setSelection("SINGLE");
		$action->setConfirmMessage("Eliminare?");
		$miaLista->addAction($action);
*/		
		listDispose($miaLista);
	}
	else if(in_array($actionContext->getForm(), array("MOD_JOBQ", "NEW_JOBQ"))) {
		$idDetail = $azione."_".$actionContext->getForm()."_DET";
		
		$actionDetail = new wi400Detail($idDetail, false);
		
		if(in_array($actionContext->getForm(),array("MOD_JOBQ"))) {
			if(!existDetail($idDetail)) {
				// caricamento dei dati della chiamata recuperati dal subfile
				$actionDetail->setSource($row);
			}
		}
		
		$tipo_sel = "";
		if(isset($_REQUEST['TIPO'])) {
			$tipo_sel = $_REQUEST['TIPO'];
		}else {
			$tipo_sel = $row['TIPO'];
		}
		
		// ID
		$myField = new wi400InputText('ID');
		$myField->setLabel('Codice JOBQ');
//		$myField->addValidation('required');
		if($actionContext->getForm()=="MOD_JOBQ") {
			$myField->setReadonly(true);
		}
		$myField->setInfo("L'ID deve essere LUNGO al massimo 20 caratteri.");
		$myField->setSize(20);
		$myField->setMaxLength(20);
		$actionDetail->addField($myField);
		
		// Tipo
		$mySelect = new wi400InputSelect('TIPO');
		$mySelect->setLabel("Tipo OTM");
		$mySelect->addValidation('required');
		$mySelect->setFirstLabel("Seleziona...");
		$mySelect->setOptions($tipo_array);
		$mySelect->setOnChange("doSubmit('".$azione."', '".$actionContext->getForm()."')");
		$actionDetail->addField($mySelect);
		
		// Ultima Esecuzione
		$myField = new wi400Text("LASTRUN");
		$myField->setLabel("Ultima Esecuzione");
		$actionDetail->addField($myField);
		
		// Stato
		$mySelect = new wi400InputSelect('STATUS');
		$mySelect->setLabel("Stato");
		$mySelect->addValidation('required');
		$mySelect->setFirstLabel("Seleziona...");
		$mySelect->setOptions($stato_array);
		$actionDetail->addField($mySelect);
		
		if(in_array($tipo_sel, array("AS400", "DB"))) {
			$myField = new wi400InputText('NAMOBJ');
			$myField->setLabel('Oggetto AS400');
//			$myField->addValidation('required');
			$myField->setSize(20);
			$myField->setMaxLength(20);
			$actionDetail->addField($myField);
			
			$myField = new wi400InputText('LIBOBJ');
			$myField->setLabel('Libreria AS400');
//			$myField->addValidation('required');
			$myField->setSize(10);
			$myField->setMaxLength(10);
			$actionDetail->addField($myField);
			
			$myField = new wi400InputText('KEYOBJ');
			$myField->setLabel('Chiave Oggetto AS400');
//			$myField->addValidation('required');
			$myField->setSize(10);
			$myField->setMaxLength(10);
			$actionDetail->addField($myField);
		}
		else if(in_array($tipo_sel, array("REDIS"))) {
			$myField = new wi400InputText('IPSERV');
			$myField->setLabel('IP del server');
//			$myField->addValidation('required');
			$myField->setMask("0123456789.");
			$myField->setSize(20);
			$myField->setMaxLength(20);
			$actionDetail->addField($myField);
			
			$myField = new wi400InputText('IPPORT');
			$myField->setLabel('Porta del server');
//			$myField->addValidation('required');
			$myField->setSize(5);
			$myField->setMaxLength(5);
			$actionDetail->addField($myField);
		}
		
		$myField = new wi400InputText('AZIGO');
		$myField->setLabel('AZIONE Partenza CODA');
//		$myField->addValidation('required');
		$myField->setSize(40);
		$myField->setMaxLength(40);
		$actionDetail->addField($myField);
		
		$myField = new wi400InputText('AZIRUN');
		$myField->setLabel('AZIONE Esecuzione CODA');
//		$myField->addValidation('required');
		$myField->setSize(40);
		$myField->setMaxLength(40);
		$actionDetail->addField($myField);
		
		// Salva
		$myButton = new wi400InputButton('SAVE_BUTTON');
		$myButton->setLabel("Salva");
		$myButton->setAction($azione);
		if($actionContext->getForm()=="MOD_JOBQ")
			$myButton->setForm("UPDT_JOBQ");
		else if($actionContext->getForm()=="NEW_JOBQ")
			$myButton->setForm("INS_JOBQ");
		$myButton->setConfirmMessage("Salvare?");
		$myButton->setValidation(true);
		$actionDetail->addButton($myButton);
		
		if($actionContext->getForm()=="MOD_JOBQ") {
			$myButton = new wi400InputButton('DELETE_BUTTON');
			$myButton->setLabel("Elimina");
			$myButton->setAction($azione);
			$myButton->setForm("DELETE_JOBQ");
			$myButton->setConfirmMessage("Eliminare?");
			$myButton->setValidation(true);
			$actionDetail->addButton($myButton);
		}
		
		$actionDetail->dispose();
	}