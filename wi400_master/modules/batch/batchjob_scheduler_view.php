<?php 

	$spacer = new wi400Spacer();

	if($actionContext->getForm()=="DEFAULT") {
		// Dettaglio job di schedulazione
		$ListDetail = new wi400Detail("BATCHJOB_SCH_DET",true);
		$ListDetail->setColsNum(2);
		
		$stato_str = "<b><font color='".$col_status[$status]."'>".$des_status[$status]."</font></b>";
		
		$labelDetail = new wi400Text("STATUS_JOB_SCH");
		$labelDetail->setLabel("Stato di attivazione dello schedulatore");
		$labelDetail->setValue($stato_str);
		$ListDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("ACTSTATUS_JOB_SCH");
		$labelDetail->setLabel("Stato");
		$labelDetail->setValue($actstatus);
		$ListDetail->addField($labelDetail);
		
		$esecuzione = file_get_contents("laststart.log");
		$labelDetail = new wi400Text("LAST_RUN");
		$labelDetail->setLabel("Ultima Esecuzione");
		$labelDetail->setValue($esecuzione);
		$ListDetail->addField($labelDetail);
		
		$ListDetail->addParameter("STATUS", $status);
		$ListDetail->addParameter("ACTSTATUS", $actstatus);
		
		// Bottoni
		// Avvia
		if (isset($settings['xmlservice'])) {
			$myButton = new wi400InputButton('START_BUTTON');
			$myButton->setLabel("Avvia");
			$myButton->setAction($azione);
			$myButton->setForm("START_BATCH_SCH");
			$myButton->setValidation(true);
			if($status=="*ACTIVE" && $actstatus!="HLD")
				$myButton->setDisabled(true);
			$ListDetail->addButton($myButton);
			
			// Termina
			$myButton = new wi400InputButton('STOP_BUTTON');
			$myButton->setLabel("Ferma");
			$myButton->setAction($azione);
			$myButton->setForm("STOP_BATCH_SCH");
			$myButton->setValidation(true);	
			if($status=="*OUTQ")
				$myButton->setDisabled(true);
			$ListDetail->addButton($myButton);
			
			// Congela
			$myButton = new wi400InputButton('FREEZE_BUTTON');
			$myButton->setLabel("Congela");
			$myButton->setAction($azione);
			$myButton->setForm("FREEZE_BATCH_SCH");
			$myButton->setValidation(true);
			if($actstatus=="HLD" || $status=="*OUTQ")
				$myButton->setDisabled(true);
			$ListDetail->addButton($myButton);
			
			// Verifica
			$myButton = new wi400InputButton('RELOAD_BUTTON');
			$myButton->setLabel("Verifica");
			$myButton->setAction($azione);
			$myButton->setForm("DEFAULT");
			$ListDetail->addButton($myButton);
		}
		$ListDetail->dispose();
		
		$spacer->dispose();
		
		// Inizializzazione lista
		$miaLista = new wi400List("BATCHJOB_SCH_LIST", true);
		
		$miaLista->setSubfile($subfile);
		$miaLista->setFrom($subfile->getTable());
		$miaLista->setOrder("FIRING DESC,ID");
//		$miaLista->setOrder("ID");
		$miaLista->setSelection("MULTIPLE");
		
		// Stato
		$stato_col = new wi400Column("DES_STATO","Stato");
		$stato_Style = array();
		foreach($statoBatchScheduledColor as $key => $val) {
			$stato_Style[] = array('EVAL:$row["STATO"]=="'.$key.'"', $val);
		}
		$stato_col->setStyle($stato_Style);

		$miaLista->setCols(array(
			new wi400Column("ID","Progressivo lavoro"),
			new wi400Column("NOME","Nome lavoro"),
			new wi400Column("DES_LAVORO","Descrizione lavoro"),
//			new wi400Column("XML_PATH","Path XML"),
//			new wi400Column("XML_FILE","File XML"),
			new wi400Column("DES_FREQUENZA","Frequenza"),
			new wi400Column("INTERVALLO","Intervallo","SECONDS_SHORT_TIME"),
			new wi400Column("FIRING","Prossima esecuzione","UNIX_TIMESTAMP"),
			$stato_col,
			new wi400Column("NUMERO_ESECUZIONI","Numero esecuzioni","INTEGER","right"),
			new wi400Column("LAST_FIRING","Ultima schedulazione","UNIX_TIMESTAMP")
		));
		
		// aggiunta chiavi di riga
		$miaLista->addKey("ID");
		$miaLista->addKey("NOME");
		$miaLista->addKey("DES_LAVORO");
		$miaLista->addKey("XML_PATH");
		$miaLista->addKey("XML_FILE");
		$miaLista->addKey("DES_FREQUENZA");
		$miaLista->addKey("INTERVALLO");
		$miaLista->addKey("FIRING");
		$miaLista->addKey("STATO");
		$miaLista->addKey("DES_STATO");
		$miaLista->addKey("NUMERO_ESECUZIONI");
		$miaLista->addKey("LAST_FIRING");
		
		$miaLista->addParameter("IDLIST", "BATCHJOB_SCH_LIST");
		
		// Aggiunta filtri rapidi
		// Nome
		$mioFiltro = new wi400Filter("NOME","Nome","STRING"); 
		$mioFiltro->setFast(true);    
		$miaLista->addFilter($mioFiltro);

		// Aggiunta filtri avanzati
		// Frequenza
		$mioFiltro = new wi400Filter("FREQUENZA","Frequenza","SELECT","");
		$filterValues = array();
		foreach($frequenza_array as $key => $val)
			$filterValues["FREQUENZA='$key'"] = $val;
		$mioFiltro->setSource($filterValues);
		$miaLista->addFilter($mioFiltro);
		
		// Stato
		$mioFiltro = new wi400Filter("STATO","Stato","SELECT","");
		$filterValues = array();
		foreach($statoBatchScheduled as $key => $val)
			$filterValues["STATO='$key'"] = $val;
		$mioFiltro->setSource($filterValues);
		$miaLista->addFilter($mioFiltro);
		
		// Inserisci un nuovo lavoro
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("NEW_JOB");
		$action->setLabel("Inserisci un nuovo lavoro");
		$action->setSelection("NONE");
		$miaLista->addAction($action);
		
		// Modifica il lavoro
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("DETAIL_JOB");
		$action->setLabel("Modifica il lavoro");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		// Gestione del file XML
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("DETAIL_XML");
		$action->setLabel("File XML");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		// Eliminazione dei lavori
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("REMOVE_JOBS");
		$action->setLabel("Cancella lavori");
		$action->setSelection("MULTIPLE");
		$action->setConfirmMessage("Eliminare i lavori selezionati e i file XML ad essi associati?");
		$miaLista->addAction($action);
		
		// Eliminazione dei lavori
		$action = new wi400ListAction();
		$action->setAction("BATCHJOB_SCH_EXE");
		$action->setLabel("Esegui i lavori schedulati");
		$action->setSelection("NONE");
		$action->setConfirmMessage("Eseguire i lavori schedulati?");
		$miaLista->addAction($action);
		
		// Modifica dello stato
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("MODIFICA_STATO");
		$action->setLabel("Modifica dello stato");
		$action->setSelection('MULTIPLE');
		$action->setTarget("WINDOW",1200);		
		$miaLista->addAction($action);

		$miaLista->dispose();
	}
	else if(in_array($actionContext->getForm(),array("NEW_JOB","DETAIL_JOB"))) {
		$jobDetail = new wi400Detail('BATCHJOB_SCH_DETAIL', false);
		$jobDetail->setTitle('Lavoro');
		$jobDetail->isEditable(true);
		
		// Progressivo lavoro
		$myField = new wi400InputText('ID');
		$myField->setLabel("Progressivo lavoro");
		$myField->setReadonly(true);
		$myField->setMaxLength(10);
		$myField->setSize(10);
		$myField->setValue($ID);
		$jobDetail->addField($myField);
		
		// Nome lavoro
		$myField = new wi400InputText('NOME');
		$myField->setLabel("Nome lavoro");
		$myField->addValidation('required');
		$myField->setMaxLength(10);
		$myField->setSize(10);
		if($actionContext->getForm()=="DETAIL_JOB")
			$myField->setValue($jobArray['NOME']);
		$myField->setInfo("Inserire il nome del lavoro");
		$jobDetail->addField($myField);
		
		// Descrizione lavoro
		$myField = new wi400InputText('DES_LAVORO');
		$myField->setLabel("Descrizione lavoro");
		$myField->addValidation('required');
		$myField->setMaxLength(100);
		$myField->setSize(100);
		if($actionContext->getForm()=="DETAIL_JOB")
			$myField->setValue($jobArray['DES_LAVORO']);
		$myField->setInfo("Inserire la descrizione del lavoro");
		$jobDetail->addField($myField);
		
		// XML
		$myField = new wi400InputText('XML');
		$myField->setLabel("File XML");
		$myField->addValidation('required');
		$myField->setMaxLength(100);
		$myField->setSize(100);
		if($actionContext->getForm()=="DETAIL_JOB")
			$myField->setValue($XML_file);
		$myField->setInfo("Inserire l'indirizzo del file XML contenente i dati di lancio");
		$jobDetail->addField($myField);
		
		// Frequenza
		$mySelect = new wi400InputSelect('FREQUENZA');
		$mySelect->setLabel("Frequenza");
		$mySelect->addValidation("required");
		$mySelect->setOptions($frequenza_array);
		$mySelect->setValue($frequenza);
		$mySelect->setOnChange("checkIntervalloFrequenza(this,'INTERVALLO')");
		$mySelect->setInfo("Inserire il tipo di frequenza di lancio del lavoro");
		$jobDetail->addField($mySelect);
		
		// Intervallo
		$myField = new wi400InputText('INTERVALLO');
		$myField->setLabel("Intervallo");
		$myField->setMaxLength(10);
		$myField->setSize(10);		
		$myField->setReadonly($readOnlyArray[$frequenza]);
		$myField->setValue($intervallo);
		$myField->setInfo("Inserire l'intervallo di tempo tra un'esecuzione e l'altra");
		$jobDetail->addField($myField);
		
		// Data prossima esecuzione
		$myField = new wi400InputText('FIRING_DATE');
		$myField->setLabel("Data prossima esecuzione");
		$myField->addValidation('date');
		$myField->setMaxLength(10);
		$myField->setSize(10);
		$myField->setValue($firing_date);
		$myField->setInfo("Inserire la data della prossima esecuzione");
		$jobDetail->addField($myField);
		
		// Ora prossima esecuzione
		$myField = new wi400InputText('FIRING_TIME');
		$myField->setLabel("Ora prossima esecuzione");
		$myField->setMaxLength(5);
		$myField->setSize(5);
		$myField->setValue($firing_time);
		$myField->setInfo("Inserire l'ora della prossima esecuzione");
		$myField->addValidation('time');
		$jobDetail->addField($myField);
		
		// Numero di esecuzioni
		$myField = new wi400InputText('NUMERO_ESECUZIONI');
		$myField->setLabel("Numero di esecuzioni");
		$myField->setReadonly(true);
		$myField->setMaxLength(6);
		$myField->setSize(6);
		if($actionContext->getForm()=="DETAIL_JOB")
			$myField->setValue($jobArray['NUMERO_ESECUZIONI']);
		else
			$myField->setValue(0);
		$jobDetail->addField($myField);
		
		// Ultima schedulazione
		$myField = new wi400InputText('LAST_FIRING');
		$myField->setLabel("Ultima schedulazione");
		$myField->setReadonly(true);
		$myField->setMaxLength(16);
		$myField->setSize(16);
		if($actionContext->getForm()=="DETAIL_JOB")
			$myField->setValue($last_firing);
		$jobDetail->addField($myField);
		
		// Stato
		$mySelect = new wi400InputSelect('STATO');
		$mySelect->setLabel("Stato");
		$mySelect->addValidation("required");
		$mySelect->setOptions($statoBatchScheduled);
		$mySelect->setValue($stato);
		$mySelect->setInfo("Inserire lo stato del lavoro");
		$jobDetail->addField($mySelect);
		
		// Bottoni
		// Cancella
		$myButton = new wi400InputButton('CANCEL_BUTTON');
		$myButton->setLabel("Annulla");
		$myButton->setAction($azione);
		$myButton->setForm("DEFAULT");
		$jobDetail->addButton($myButton);
		
		// Salva
		$myButton = new wi400InputButton('SAVE_BUTTON');
		$myButton->setLabel("Salva");
		$myButton->setAction($azione);
		if($actionContext->getForm()=="NEW_JOB")
			$myButton->setForm("INSERT_JOB");
		else if($actionContext->getForm()=="DETAIL_JOB")
			$myButton->setForm("UPDATE_JOB");
		$myButton->setValidation(true);
		$jobDetail->addButton($myButton);
	
		$jobDetail->dispose();
	}
	else if($actionContext->getForm()=="DETAIL_XML") {
		$XMLDetail = new wi400Detail('XML_FILE_DETAIL', false);
		$XMLDetail->setTitle('File XML');
		$XMLDetail->setColsNum(2);
		$XMLDetail->isEditable(true);
		
		// Lavoro
		$myField = new wi400Text("ID_NOME");
		$myField->setLabel("ID e nome:");
		$myField->setValue($ID." - ".$nome);
		$XMLDetail->addField($myField);	
		
		// Lavoro
		$myField = new wi400Text("DES_LAVORO");
		$myField->setLabel("Descrizione lavoro:");
		$myField->setValue($des_job);
		$XMLDetail->addField($myField);	
		
		// Indirizzo
		$myField = new wi400Text("FILE_XML");
		$myField->setLabel("Indirizzo:");
		$myField->setValue($XML);
		$XMLDetail->addField($myField);
		
		$XMLDetail->dispose();
		
		$spacer->dispose();
	
		$XMLDetail = new wi400Detail('XML_FILE_BODY', false);
		$XMLDetail->setTitle('Parametri principali');
		$XMLDetail->setColsNum(2);
		$XMLDetail->isEditable(true);
		
		// action
		$myField = new wi400InputText('job_action');
		$myField->setLabel("action");
		$myField->addValidation("required");
		$myField->setMaxLength(50);
		$myField->setSize(50);
		$myField->setValue($params['action']);
		$XMLDetail->addField($myField);
		
		// form
		$myField = new wi400InputText('job_form');
		$myField->setLabel("form");
		$myField->setMaxLength(50);
		$myField->setSize(50);
		$myField->setValue($params['form']);
		$XMLDetail->addField($myField);
		
		// name job
		$myField = new wi400InputText('job_name_job');
		$myField->setLabel("name_job");
		$myField->addValidation("required");
		$myField->setMaxLength(50);
		$myField->setSize(50);
		if(!isset($params['name_job']))
			$myField->setValue($ID." - ".$nome);
		else
			$myField->setValue($params['name_job']);
		$XMLDetail->addField($myField);
		
		// des_job
		$myField = new wi400InputText('job_des_job');
		$myField->setLabel("des_job");
		$myField->addValidation("required");
		$myField->setMaxLength(50);
		$myField->setSize(50);
		if(!isset($params['des_job']))
			$myField->setValue($des_job);
		else
			$myField->setValue($params['des_job']);
		$XMLDetail->addField($myField);
		
		// base_path
		$myField = new wi400InputText('job_base_path');
		$myField->setLabel("base_path");
		$myField->addValidation("required");
		$myField->setMaxLength(50);
		$myField->setSize(50);
		$url = "http://".$_SERVER['SERVER_ADDR'].":"."89".$appBase."batch.php";
		if(!isset($params['base_path']) || $params['base_path']=="")
			$myField->setValue($url);
		else
			$myField->setValue($params['base_path']);
		$XMLDetail->addField($myField);
		
		// app_base
		$myField = new wi400InputText('job_app_base');
		$myField->setLabel("app_base");
		$myField->addValidation("required");
		$myField->setMaxLength(50);
		$myField->setSize(50);
		if(!isset($params['app_base']) || $params['app_base']=="")
			$myField->setValue($appBase);
		else
			$myField->setValue($params['app_base']);
		$XMLDetail->addField($myField);
		
		// name
		$myField = new wi400InputText('job_name');
		$myField->setLabel("name");
		$myField->addValidation("required");
		$myField->setMaxLength(50);
		$myField->setSize(50);
		$myField->setValue($params['name']);
		$XMLDetail->addField($myField);
		
		// user
		$myField = new wi400InputText('job_user');
		$myField->setLabel("user");
		$myField->addValidation("required");
		$myField->setMaxLength(50);
		$myField->setSize(50);
		$myField->setValue($params['user']);
		$XMLDetail->addField($myField);
		
		$XMLDetail->dispose();
		
		$spacer->dispose();
		
		$XMLDetail = new wi400Detail('XML_FILE_EXTRAS', true);
		$XMLDetail->setTitle('Parametri');
		$XMLDetail->setColsNum(2);
		$XMLDetail->isEditable(true);
		
		// Chiavi dei parametri
		$myField = new wi400InputText('XML_ID');
		$myField->setLabel("id=");
		$myField->setMaxLength(50);
		$myField->setSize(50);
		$myField->setShowMultiple(true);
		$myField->setSortMultiple(true);
		$myField->setValue(array_flip($extra_params));
		$XMLDetail->addField($myField);
		
		// Valori dei parametri
		$myField = new wi400InputText('XML_VALUE');
		$myField->setLabel("value=");
		$myField->setMaxLength(50);
		$myField->setSize(50);
		$myField->setShowMultiple(true);
		$myField->setSortMultiple(true);
		$myField->setCheckDuplicate(false);
		$myField->setValue($extra_params);
		$XMLDetail->addField($myField);
		
		// Bottoni
		// Cancella
		$myButton = new wi400InputButton('CANCEL_BUTTON');
		$myButton->setLabel("Annulla");
		$myButton->setAction($azione);
		$myButton->setForm("DEFAULT");
		$XMLDetail->addButton($myButton);
		
		// Salva
		$myButton = new wi400InputButton('SAVE_BUTTON');
		$myButton->setLabel("Salva");
		$myButton->setAction($azione);
		$myButton->setForm("SAVE_XML");
		$myButton->setValidation(true);
		$XMLDetail->addButton($myButton);
		
		$XMLDetail->dispose();
	}
	else if($actionContext->getForm()=="MODIFICA_STATO") {
		$actionDetail = new wi400Detail($azione.'_MOD_STATO_DET', True);
		
		// Stato
		$mySelect = new wi400InputSelect('STATO');
		$mySelect->setLabel("Stato");
		$mySelect->addValidation("required");
		$mySelect->setOptions($statoBatchScheduled);
		$mySelect->setFirstLabel("Seleziona ..");
		$mySelect->setInfo("Inserire lo stato del lavoro");
		$actionDetail->addField($mySelect);
		
		$myField = new wi400InputCheckbox("UPDATE_ALL");
		$myField->setLabel("Tutti");
		$myField->setChecked(false);
		$actionDetail->addField($myField);
		
		$myButton = new wi400InputButton('UPDATE_BUTTON');
		$myButton->setLabel("Applica");
		$myButton->setAction($azione);
		$myButton->setForm("UPDATE_STATO");
		$myButton->setValidation(true);
		$actionDetail->addButton($myButton);
		
		$myButton = new wi400InputButton('CANCEL_BUTTON');
		$myButton->setLabel("Annulla");
//		$myButton->setScript('closeLookUp()');
		$myButton->setAction($azione);
		$myButton->setForm("CLOSE_WINDOW");
		$actionDetail->addButton($myButton);
		
		$actionDetail->dispose();
		
		$spacer->dispose();
		
		$actionDetail = new wi400Detail($azione.'_LIST_DET', false);
		
		$tableDetail = new wi400Table("BATCH_JOB_FIRST");
		$tableDetail->setLabel("Lavori selezionati");
		$tableDetail->setCols(array(
			new wi400Column("ID","Progressivo lavoro"),
			new wi400Column("NOME","Nome lavoro"),
			new wi400Column("DES_LAVORO","Descrizione lavoro"),
			new wi400Column("DES_FREQUENZA","Frequenza"),
			new wi400Column("INTERVALLO","Intervallo","SECONDS_SHORT_TIME"),
			new wi400Column("FIRING","Prossima esecuzione","UNIX_TIMESTAMP"),
			new wi400Column("DES_STATO","Stato"),
			new wi400Column("NUMERO_ESECUZIONI","Numero esecuzioni","INTEGER","right"),
			new wi400Column("LAST_FIRING","Ultima schedulazione","UNIX_TIMESTAMP")
		));
		
		$isFirst = true;
		foreach($rowsSelectionArray as $key => $value){
			$keyArray = array();
			$keyArray = explode("|",$key);
			
			$tab_row = array(
				$keyArray[0],
				$keyArray[1],
				$keyArray[2],
				$keyArray[5],
				$keyArray[6],
				$keyArray[7],
				$keyArray[9],
				$keyArray[10],
				$keyArray[11]
			);
						
			$tableDetail->addRow($tab_row);
		}
	
		$actionDetail->addField($tableDetail);
		
		$actionDetail->dispose();
	}
	else if($actionContext->getForm()=="CLOSE_WINDOW") {
		close_window();
	}
	
?>

<script>
function checkIntervalloFrequenza(which, where){
	var fieldObj = document.getElementById(where);
	if (which.value == "*TIME") {
		fieldObj.className = "inputtext";
		fieldObj.readOnly = false;
	}
	else{
		fieldObj.value = "";
		if(which.value=="*DAILY")
			fieldObj.value = "24:00";
		else if(which.value=="*WEEKLY")
			fieldObj.value = "168:00";
		fieldObj.className = "inputtextDisabled";
		fieldObj.readOnly = true;
	}
}
</script>