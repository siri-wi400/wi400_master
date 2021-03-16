<?php 

	$spacer = new wi400Spacer();
	
	if(!in_array($actionContext->getForm(),array("DEFAULT","DELETE_FILE", "SEND_MESSAGE", "SEND_MESSAGE_GO"))) {
		if (isset($HdlJob) && is_bool($HdlJob)){
?>
			<script>
				alert(<?=_t('FILE_NOT_EXIST')?>);
			</script>
<?			
		}
		
		$ListDetail = new wi400Detail($azione."_".$actionContext->getForm()."_DET", false);
		$ListDetail->setColsNum(2);
		$ListDetail->isEditable(True);
		
		$labelDetail = new wi400Text("JOBNUM");
		$labelDetail->setLabel(_t('JOB_NUMBER'));
		$labelDetail->setValue($jobNumber);
		$ListDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("USER_ID");
		$labelDetail->setLabel(_t('USER'));
		$labelDetail->setValue($user_id.": ".$des_ute);
		$ListDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("SESSION");
		$labelDetail->setLabel(_t('SESSION_ID'));
		$labelDetail->setValue($session_id);
		$ListDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("IP");
		$labelDetail->setLabel(_t('IP_ADDRESS'));
		$labelDetail->setValue($ip);
		$ListDetail->addField($labelDetail);
		
		if($actionContext->getForm()!="LOG_LAVORO") {
			$labelDetail = new wi400Text("FILENAME");
			$labelDetail->setLabel(_t('FILE_PATH'));
			$labelDetail->setValue($file_path);
			$ListDetail->addField($labelDetail);
			
			$size = 0;
			if(file_exists($file_path))
				$size = filesize($file_path);
			
			$labelDetail = new wi400Text("FILESIZE");
			$labelDetail->setLabel(_t('FILE_SIZE'));
			$labelDetail->setValue($size);
			$ListDetail->addField($labelDetail);
		}
		
		$labelDetail = new wi400Text("JOBNAME");
		$labelDetail->setLabel(_t('JOB'));
		$labelDetail->setValue($jobName);
		$ListDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("USERNAME");
		$labelDetail->setLabel(_t('USER'));
		$labelDetail->setValue($userName);
		$ListDetail->addField($labelDetail);
		
		$ListDetail->dispose();
		
		$spacer->dispose();
	}
	
	if($actionContext->getForm()=="DEFAULT") {
		$ListDetail = new wi400Detail($azione."_DETAIL",true);
		$ListDetail->setColsNum(2);
		
		$labelDetail = new wi400Text("JOB_NUM");
		$labelDetail->setLabel(_t('JOB_NUMBER'));
		$labelDetail->setValue($ID_job);
		$ListDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("SESSION");
		$labelDetail->setLabel(_t('SESSION_ID'));
		$labelDetail->setValue(session_id());
		$ListDetail->addField($labelDetail);
		
		$myField = new wi400InputCheckbox("JOB_ATTUALE");
		$myField->setLabel(_t('JOB_LOG_CUR_ONLY'));
		$myField->setChecked($job_attuale);
		$ListDetail->addField($myField);
		
		$myButton = new wi400InputButton('RELOAD_BUTTON');
		$myButton->setLabel(_t('REFRESH'));
		$myButton->setAction($azione);
		$myButton->setForm("DEFAULT");
		$myButton->setValidation(true);
		$ListDetail->addButton($myButton);
		
		$ListDetail->dispose();
		
		$spacer->dispose();
		
		// Inizializzazione lista
		$miaLista = new wi400List("LAVORI_ATTIVI_LIST", true);
		$miaLista->setSubfile($subfile);
		$miaLista->setFrom($subfile->getTable());
		$miaLista->setOrder("JOBNAME");
		$miaLista->setSelection("SINGLE");
		
		$miaLista->setCols(array(
			new wi400Column("JOBNUM",_t('JOB_NUMBER')),
			new wi400Column("USER_ID",_t('USER')),
			new wi400Column("USER_DES",_t('USER_NAME')),
			new wi400Column("JOBNAME",_t('JOB')),
			new wi400Column("USERNAME",_t('USER_DES')),
			new wi400Column("IP",_t('IP_ADDRESS')),
			new wi400Column("SESSION",_t('SESSION'))
		));
		
		$colStyle = array();
		$colStyle[] = array('EVAL:$row["JOBNUM"]=="'.$ID_job.'"','wi400_grid_green');
		
		foreach($miaLista->getCols() as $col) {
			$col->setStyle($colStyle);
		}
		
		// aggiunta chiavi di riga
		$miaLista->addKey("JOBNAME");
		$miaLista->addKey("USERNAME");
		$miaLista->addKey("JOBNUM");
		$miaLista->addKey("USER_ID");
		$miaLista->addKey("USER_DES");
		$miaLista->addKey("IP");
		$miaLista->addKey("SESSION");
		
		// Filtri veloci
		$mioFiltro = new wi400Filter("USER_ID","ID Utente","STRING"); 
		$mioFiltro->setFast(true);    
		$miaLista->addFilter($mioFiltro);
		
		$mioFiltro = new wi400Filter("JOBNUM",_t('JOB_NUMBER'),"STRING"); 
		$mioFiltro->setFast(true);    
		$miaLista->addFilter($mioFiltro);
		
		// Filtri avanzati
		$mioFiltro = new wi400Filter("USER_DES",_t('USER_DES'),"STRING"); 
		$miaLista->addFilter($mioFiltro);
		
		$mioFiltro = new wi400Filter("JOBNAME",_t('JOB'),"STRING");    
		$miaLista->addFilter($mioFiltro);
		
		$mioFiltro = new wi400Filter("USERNAME",_t('JOB_ID'),"STRING"); 
		$miaLista->addFilter($mioFiltro);
		
		$mioFiltro = new wi400Filter("IP",_t('IP_ADDRESS'),"STRING"); 
		$miaLista->addFilter($mioFiltro);
		
		// Log lavoro
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("LOG_LAVORO");
		$action->setLabel(_t('JOB_LOG_VIEW'));
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		// Log SQL
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("LOG_SQL");
		$action->setLabel(_t('JOB_LOG_VIEW_SQL'));
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		// Dati sessione
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("DATI_SESSIONE");
		$action->setLabel(_t('JOB_LOG_VIEW_SESS'));
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		// Importa sessione
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("IMPORT_SESSION");
		$action->setLabel(_t('SESSION_IMPORT'));
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		// Send Message
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("SEND_MESSAGE");
		$action->setLabel(_t('SEND_MESSAGE'));
		$action->setSelection("SINGLE");
		$action->setTarget("WINDOW");
		$miaLista->addAction($action);		
		
		// GeoIP
		$action = new wi400ListAction();
		$action->setAction("GEOIP");
		$action->setLabel("GeoIP");
		$action->setTarget("WINDOW",1000,500);
		$action->setGateway("LAVORI_ATTIVI");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		$miaLista->dispose();
	}
	else if($actionContext->getForm()=="LOG_LAVORO") {
		$LogDetail = new wi400Detail('LOG_LAVORO_DETAIL', true);
		$LogDetail->setTitle(_t('JOB_LOG_TITLE'));
		$LogDetail->isEditable(true);

		// Testo del log del lavoro
		$myField = new wi400InputTextArea('LOG_BODY');
		$myField->setReadonly(true);
		$myField->setSaveSession(false);
//		$myField->setLabel("Log lavoro");
		$myField->setSize(195);
		$myField->setRows(25);
		$myField->setValue($lines);
		$LogDetail->addField($myField);
		
		$myButton = new wi400InputButton('RELOAD_BUTTON');
		$myButton->setLabel(_t('REFRESH'));
		$myButton->setAction($azione);
		$myButton->setForm("LOG_LAVORO");
		$LogDetail->addButton($myButton);
		
		$LogDetail->dispose();
	}
	else if($actionContext->getForm()=="LOG_SQL") {
		$LogDetail = new wi400Detail('LOG_SQL_DETAIL', true);
		$LogDetail->setTitle(_t('JOB_LOG_SQL_TITLE'));
		$LogDetail->isEditable(true);
		
		// Testo del log del lavoro
		$myField = new wi400InputTextArea('LOG_BODY');
		$myField->setReadonly(true);
		$myField->setSaveSession(false);
//		$myField->setLabel("Log SQL");
		$myField->setSize(195);
		$myField->setRows(25);
		$myField->setValue($lines);
		$LogDetail->addField($myField);
		
		$myButton = new wi400InputButton('RELOAD_BUTTON');
		$myButton->setLabel(_t('REFRESH'));
		$myButton->setAction($azione);
		$myButton->setForm("LOG_SQL");
		$LogDetail->addButton($myButton);
		
		$myButton = new wi400InputButton('DELETE_BUTTON');
		$myButton->setLabel(_t('FILE_CLEAR'));
		$myButton->setAction($azione);
		$myButton->setForm("DELETE_FILE");
		$LogDetail->addButton($myButton);
		
		$LogDetail->dispose();
	}
	else if($actionContext->getForm()=="DATI_SESSIONE") {
		$LogDetail = new wi400Detail('DATI_SESSIONE_DETAIL', true);
		$LogDetail->setTitle(_t('JOB_LOG_VIEW_SESS'));
		$LogDetail->isEditable(true);
		
		// Testo del log del lavoro
		$myField = new wi400InputTextArea('LOG_BODY');
		$myField->setReadonly(true);
		$myField->setSaveSession(false);
		$myField->setSize(195);
		$myField->setRows(25);
		$myField->setValue($lines);
		$LogDetail->addField($myField);
		
		$myButton = new wi400InputButton('RELOAD_BUTTON');
		$myButton->setLabel(_t('REFRESH'));
		$myButton->setAction($azione);
		$myButton->setForm("DATI_SESSIONE");
		$LogDetail->addButton($myButton);
		
		if($session_id!=session_id()) {
			$myButton = new wi400InputButton('IMPORT_BUTTON');
			$myButton->setLabel(_t('SESSION_IMPORT'));
			$myButton->setAction($azione);
			$myButton->setForm("IMPORT_SESSION");
			$LogDetail->addButton($myButton);
		}
		
		$LogDetail->dispose();
	}
	else if($actionContext->getForm()=="SEND_MESSAGE") {
		$LogDetail = new wi400Detail('SEND_SESSIONE_DETAIL', true);
		$LogDetail->setTitle(_t('SEND_MESSAGE_FORM'));
		$LogDetail->isEditable(true);	
		// Testo del log del lavoro
		$myField = new wi400InputTextArea('MESSAGGIO');
		$myField->setReadonly(false);
		$myField->setSize(40);
		$myField->setRows(10);
		$myField->setValue("");
		$LogDetail->addField($myField);
		$myButton = new wi400InputButton('SEND_MESSAGE');
		$myButton->setLabel(_t('SEND_MESSAGE'));
		$myButton->setAction($azione);
		$myButton->setForm("SEND_MESSAGE_GO");
		$LogDetail->addButton($myButton);
		$LogDetail->dispose();
		
	}
	else if($actionContext->getForm()=="SEND_MESSAGE_GO") {
		close_block_window();
	}
?>