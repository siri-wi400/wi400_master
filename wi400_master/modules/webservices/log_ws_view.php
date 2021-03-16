<?php
	if($actionContext->getForm()!="DEFAULT") {
		$ListDetail = new wi400Detail("SEARCH_INFO", true);
		$ListDetail->setColsNum(2);
		
		$labelDetail = new wi400Text("SEARCH_ENTITA");
		$labelDetail->setLabel("Entita");
		if(isset($entita) && $entita!="") {
			$labelDetail->setValue($entita);
		}
		else {
			$labelDetail->setValue("TUTTI");
		}
		$ListDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("PERIODO_SEL");
		$labelDetail->setLabel("Periodo");
		$labelDetail->setValue("Dalle $ora_ini del $data_ric_ini alle $ora_fin del $data_ric_fin");
		$ListDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("SEARCH_ERROR");
		$labelDetail->setLabel("Errore Web Service");
		if(isset($errCheck) && $errCheck!="") {
			$labelDetail->setValue("SI");
		}
		else {
			$labelDetail->setValue("NO");
		}
		$ListDetail->addField($labelDetail);
		
		$ListDetail->dispose();
		
		echo "<br/><br/>";
	}
	if($actionContext->getForm()=="DEFAULT") {
		$searchAction = new wi400Detail($azione."_DET", true);
		$searchAction->setSaveDetail(true);
		$searchAction->setColsNum(2);
		
		// Entità
		$myField = new wi400InputText('ENTITA');
		if(isset($entita) && !empty($entita)) {
			$myField->setValue($entita);
		}
		$myField->setLabel("Entità");
		$myField->setMaxLength(4);
		$myField->setSize(4);
		
		$decodeParameters = array(
			'TYPE' => 'common',
			'TABLE_NAME' => "FAENTITA",
			'COLUMN' => 'AENDCO',
			'KEY_FIELD_NAME' => 'AENCOD',
			'AJAX' => true
		);
		$myField->setDecode($decodeParameters);

		$myLookUp =new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE", "FAENTITA");
		$myLookUp->addParameter("CAMPO","AENCOD");
		$myLookUp->addParameter("DESCRIZIONE","AENDCO");
		$myField->setLookUp($myLookUp);
		
		$searchAction->addField($myField);
		
		$myField = new wi400Text('VUOTO');
		$searchAction->addField($myField);
		
		// Data ricezione iniziale
		$myField = new wi400InputText('DATA_RIC_INI');
		if(!isset($data_ric_ini) || empty($data_ric_ini)) {
			$myField->setValue(dateModelToView($_SESSION['data_validita']));
		}else {
			$myField->setValue($data_ric_ini);
		}
		$myField->addValidation('date');
		$myField->addValidation('required');
		$myField->setLabel("Data ricezione iniziale");
		$searchAction->addField($myField);
		
		//Ora ricezione iniziale
		$myField = new wi400InputText('ORA_INI');
		$myField->setLabel("Ora ricezione iniziale");
		if(!isset($ora_ini) || empty($ora_ini)) {
			$myField->setValue("00:00");
		}else {
			$myField->setValue($ora_ini);
		}
		$myField->addValidation('time');
		$myField->addValidation('required');
		$searchAction->addField($myField);
		
		// Data ricezione finale
		$myField = new wi400InputText('DATA_RIC_FIN');
		if(!isset($data_ric_fin) || empty($data_ric_fin)) {
			$myField->setValue(dateModelToView($_SESSION['data_validita']));
		}else {
			$myField->setValue($data_ric_fin);
		}
		$myField->addValidation('date');
		$myField->addValidation('required');
		$myField->setLabel("Data ricezione finale");
		$searchAction->addField($myField);
		
		//Ora ricezione finale
		$myField = new wi400InputText('ORA_FIN');
		$myField->setLabel("Ora ricezione finale");
		if(!isset($ora_fin) || empty($ora_fin)) {
			$myField->setValue("23:59");
		}else {
			$myField->setValue($ora_fin);
		}
		$myField->addValidation('required');
		$myField->addValidation('time');
		$searchAction->addField($myField);
		
		// Errore web service
		$myField = new wi400InputCheckbox('ERROR_WS');
		if(isset($errCheck) && !empty($errCheck)) {
			$myField->setChecked(true);
		}
		$myField->setLabel("Errore web service");
		$searchAction->addField($myField);
		
		
		// Seleziona
		$myButton = new wi400InputButton('SEARCH_BUTTON');
		$myButton->setLabel("Seleziona");
		$myButton->setAction($azione);
		$myButton->setForm("LIST");
		$myButton->setValidation(true);
		$searchAction->addButton($myButton);
		
		$searchAction->dispose();
	}
	else if ($actionContext->getForm() == "LIST") {		
		$miaLista = new wi400List($azione."_LIST", !$isFromHistory);
		
		$miaLista->setFrom("ZWEBSLOG");
		
		$time_ini = time_to_timestamp($data_ric_ini,$ora_ini);
		$time_fin = time_to_timestamp($data_ric_fin,$ora_fin);
		
		$where_array = array();
		if(isset($entita) && $entita != "") {
			$where_array[] = "LOGENT='$entita'";
		}
		$where_array[] = "LOGTRX between '$time_ini' and '$time_fin'";
		if(isset($errCheck) && $errCheck == 1) {
			$where_array[] = "LOGERR<>''";
		}
		$where = implode(" and ", $where_array);
		//echo $where."<br/>";
		$miaLista->setWhere($where);
		$miaLista->setOrder("LOGTRX desc");
		
		$idReg= new wi400Column("LOGID","Id");
		$idReg->setActionListId($azione."_LOG");
		
		$miaLista->addCol(new wi400Column("LOGTYP","Tipo Log"));
		$miaLista->addCol($idReg);
		$miaLista->addCol(new wi400Column("LOGURS","Utente"));
		$miaLista->addCol(new wi400Column("LOGENT","Cod. entità"));
		$miaLista->addCol(new wi400Column("LOGSEG","Segmenti"));
		$miaLista->addCol(new wi400Column("LOGRCX","Data ricezione", "TIMESTAMP"));
		$miaLista->addCol(new wi400Column("LOGTRX","Data Trasmissione", "TIMESTAMP"));
		$miaLista->addCol(new wi400Column("LOGSTA","Stato"));
		$miaLista->addCol(new wi400Column("LOGERR","Errore"));
		$miaLista->addCol(new wi400Column("LOGDER","Desc errore"));
		$miaLista->addCol(new wi400Column("LOGIP","IP"));
		$miaLista->addCol(new wi400Column("LOGSYS","Sistema informativo"));
		$miaLista->addCol(new wi400Column("LOGOPE","Sistema operativo"));
		$miaLista->addCol(new wi400Column("LOGBRW","Browser"));
		$miaLista->addCol(new wi400Column("LOGAGE","User agent"));
		
		// Aggiunta filtri avanzati
		$mioFiltro = new wi400Filter("LOGENT","Entita","STRING");
		$myLookUp =new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE", "FAENTITA");
		$myLookUp->addParameter("CAMPO","AENCOD");
		$myLookUp->addParameter("DESCRIZIONE","AENDCO");
		$mioFiltro->setLookUp($myLookUp);
		$miaLista->addFilter($mioFiltro);
		
		$mioFiltro = new wi400Filter("LOGUSR","Utente","STRING");
		$miaLista->addFilter($mioFiltro);
		$mioFiltro = new wi400Filter("LOGID","Id","STRING");
		$miaLista->addFilter($mioFiltro);
		
		// Dettaglio log web service
		$action = new wi400ListAction();
		$action->setId($azione."_LOG");
		$action->setAction($azione);
		$action->setForm("DETAIL");
		$action->setLabel("Dettaglio log");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		$miaLista->addKey("LOGID");
		
		$miaLista->dispose();
		
		
		/*echo $time_ini."<br/>";
		echo $time_fin."<br/>";*/		
	}
	else if($actionContext->getForm() == "DETAIL") {
		// Creo un nuovo detail
		$logDetail = new wi400Detail($azione."_DETTAGLIO");
		$logDetail->addTab("log_1", "Dati Identificativi");
		
		$file_out = file_exists($row['LOGXOU']);
		if($xml_in) {
			$logDetail->addTab("log_2", "Input XML");
		}
		if($file_out) {
			$logDetail->addTab("log_3", "Output XML");
		}
		if($log_out) {
			$logDetail->addTab("log_4", "Log File");
		}
		$scheda= 'log_1';

		// TYPE
		$myField = new wi400Text('TYPE');
		$myField->setLabel("Tipo log");
		$myField->setValue($row['LOGTYP']);
		$myField->setReadonly(true);
		$logDetail->addField($myField, $scheda);
		
		// ID
		$myField = new wi400Text('Id');
		$myField->setLabel("Id");
		$myField->setValue($row['LOGID']);
		$logDetail->addField($myField, $scheda);
		
		// UTENTE
		$myField = new wi400Text('UTENTE');
		$myField->setLabel("Utente");
		$myField->setValue($row['LOGUSR']);
		$logDetail->addField($myField, $scheda);
		
		// ENTITA'
		$myField = new wi400Text('ENTITA');
		$myField->setLabel("Entita");
		$myField->setValue($row['LOGENT']);
		$logDetail->addField($myField, $scheda);
		
		// SEQUENZA SEGMENTI
		/*$myField = new wi400Text('SEGMENTI');
		$myField->setLabel("Sequenza segmenti");
		$myField->setValue($row['LOGSEG']);
		$logDetail->addField($myField, $scheda);*/
		foreach($seg_con_desc as $chiave => $valore) {
			$myField = new wi400Text('SEGMENTI_'.$chiave);
			$myField->setLabel("Segmento ".$chiave);
			$myField->setValue($valore);
			$logDetail->addField($myField, $scheda);
		}
		
		// DATA RICEZIONE
		$myField = new wi400Text('DATA_RIC');
		$myField->setLabel("Data ricezione");
		$myField->setValue($row['LOGRCX']);
		$logDetail->addField($myField, $scheda);
		
		// DATA TRASMISSIONE
		$myField = new wi400Text('DATA_TRA');
		$myField->setLabel("Data trasmissione");
		$myField->setValue($row['LOGTRX']);
		$logDetail->addField($myField, $scheda);
		
		$tem = substr($row['LOGTRX'], -9);
		$tempo = substr($row['LOGRCX'], -9);
		
		// Tempo
		$myField = new wi400Text('TIME_EXEC');
		$myField->setLabel("Tempo");
		$myField->setValue((round($tem-$tempo, 6))." secondi");
		$logDetail->addField($myField, $scheda);
		
		// STATO TRASMISSIONE
		$myField = new wi400Text('STAT_TRA');
		$myField->setLabel("Stato trasmissione");
		$myField->setValue($row['LOGSTA']);
		$logDetail->addField($myField, $scheda);
		
		// CODICE ERRORE
		$myField = new wi400Text('COD_ERROR');
		$myField->setLabel("Codice errore");
		$myField->setValue($row['LOGERR']);
		$logDetail->addField($myField, $scheda);
		
		// DESCRIZIONE ERRORE
		$myField = new wi400Text('DESC_ERR');
		$myField->setLabel("Descrizione errore");
		$myField->setValue($row['LOGDER']);
		$logDetail->addField($myField, $scheda);
		
		// IP
		$myField = new wi400Text('IP_ADDRESS');
		$myField->setLabel("Ip");
		$myField->setValue($row['LOGIP']);
		$logDetail->addField($myField, $scheda);
		
		// SISTEMA INFORMATIVO
		$myField = new wi400Text('SIST_INF');
		$myField->setLabel("Sistema informativo");
		$myField->setValue($row['LOGSYS']);
		$logDetail->addField($myField, $scheda);
		
		// XML DI INPUT
		$myField = new wi400Text('XML_FILE_IN');
		$myField->setLabel("File di input");
		$myField->setValue($row['LOGXIN']);
		$logDetail->addField($myField, $scheda);
		
		// XML DI OUTPUT
		$myField = new wi400Text('XML_FILE_OUT');
		$myField->setLabel("File di output");
		$myField->setValue($row['LOGXOU']);
		$logDetail->addField($myField, $scheda);
		
		// SISTEMA OPERATIVO
		$myField = new wi400Text('SIST_OPER');
		$myField->setLabel("Sistema operativo");
		$myField->setValue($row['LOGOPE']);
		$logDetail->addField($myField, $scheda);
		
		// USER AGENT
		$myField = new wi400Text('USER_AGENT');
		$myField->setLabel("User agent");
		$myField->setValue($row['LOGAGE']);
		$logDetail->addField($myField, $scheda);
		
		// BROWSER
		$myField = new wi400Text('BROWSER');
		$myField->setLabel("Browser");
		$myField->setValue($row['LOGBRW']);
		$logDetail->addField($myField, $scheda);
		
		// DATI 1
		$myField = new wi400Text('DATI1');
		$myField->setLabel("Dati estesi 1");
		$myField->setValue($row['LOGEXT']);
		$logDetail->addField($myField, $scheda);
		
		// DATI 2
		$myField = new wi400Text('DATI2');
		$myField->setLabel("Dati estesi 2");
		$myField->setValue($row['LOGEX2']);
		$logDetail->addField($myField, $scheda);
		
		if($xml_in) {
			$scheda = 'log_2';
			$myField = new wi400InputTextArea('XML_IN');
			$myField->setReadonly(true);
			$myField->setSaveSession(false);
			$myField->setSize(190);
			$myField->setRows(25);
			$myField->setValue($xml_in);
			$logDetail->addField($myField, $scheda);
		}
		
		if($file_out) {
			$scheda = 'log_3';
			$myField = new wi400InputTextArea('XML_OUT');
			$myField->setReadonly(true);
			$myField->setSaveSession(false);
			$myField->setSize(190);
			$myField->setRows(25);
			$myField->setValue($xml_out);
			$logDetail->addField($myField, $scheda);
		}
		if($log_out) {
			$scheda = 'log_4';
			$myField = new wi400InputTextArea('LOG_OUT');
			$myField->setReadonly(true);
			$myField->setSaveSession(false);
			$myField->setSize(190);
			$myField->setRows(25);
			$myField->setValue($log_out);
			$logDetail->addField($myField, $scheda);
		}

		$logDetail->dispose();
	}
	
	
	
	
	
	
	
	