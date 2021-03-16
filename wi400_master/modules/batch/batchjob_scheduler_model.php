<?php 

	require_once "batch_commons.php";
	
	$azione = $actionContext->getAction();
	
	$tabella = "ZSCHDJOB";
	
	if(!in_array($actionContext->getForm(),array("MODIFICA_STATO","UPDATE_STATO"))) {
		$XML_path = $settings['data_path']."BATCH/SCHEDULED/";
	
//		echo "XML PATH: $XML_path<br>";
//		echo "TIMESTAMP: ".(mktime(date('H'),date('i'),0,date('m'),date('d'),date('Y')))."<br>";

		$required_params = array("action","form","name_job","des_job","base_path","app_base","name","user");
	
		$ID = wi400Detail::getDetailValue("BATCHJOB_SCH_DETAIL","ID");
		$frequenza = wi400Detail::getDetailValue("BATCHJOB_SCH_DETAIL","FREQUENZA");
		if (isset($settings['xmlservice'])) {
//			$ret = data_area_read("PHPLIB/ZJOBSCDE");
			$ret = data_area_read("ZJOBSCDE");				
		}
//		echo "RET: $ret<br>";
	}

	if($actionContext->getForm()=="UPDATE_STATO") {
		//$wi400List = $_SESSION['BATCHJOB_SCH_LIST'];
		$wi400List = getList('BATCHJOB_SCH_LIST');
		$rowsSelectionArray = $wi400List->getSelectionArray();
		
		$update_all = false;
		if(isset($_REQUEST["UPDATE_ALL"]))
			$update_all = true;
		
		if($update_all===true) {
			foreach($rowsSelectionArray as $key => $value){
				$keyArray = array();
				$keyArray = explode("|",$key);
				
				$result = update_stato_batch_scheduler($keyArray[0], $_POST['STATO'], $tabella);
			}
			
			$actionContext->setForm("CLOSE_WINDOW");		
		}
		else {
//			echo "UPDATE UNO ALLA VOLTA<br>";
			
			$rows = array_keys($rowsSelectionArray);
//			echo "ROWS:<pre>"; print_r($rows); echo "</pre><br>";
			
			$row = $rows[0];
//			echo "ROW: $row<br>";

			$keyArray = array();
			$keyArray = explode("|",$row);
			
			$result = update_stato_batch_scheduler($keyArray[0], $_POST['STATO'], $tabella);
			
			if($result) {
				// eliminare la riga dall'array
				unset($rowsSelectionArray[$row]);
//				echo "ROWS:<pre>"; print_r($rowsSelectionArray); echo "</pre><br>";
			
				if(!empty($rowsSelectionArray)) {
					$wi400List->setSelectionArray($rowsSelectionArray);
				
					$actionContext->setForm("MODIFICA_STATO");
				}
				else {
					$actionContext->setForm("CLOSE_WINDOW");
				}
			}
			else {
				$messageContext->addMessage("ERROR","Errore durante l'aggiornamento dello stato del lavoro.");
			}
		}
	}
	
	if(in_array($actionContext->getForm(),array("DEFAULT","NEW_JOB","DETAIL_JOB","DETAIL_XML")))
		$history->addCurrent();
		
	if(in_array($actionContext->getForm(),array("INSERT_JOB","UPDATE_JOB"))) {
		$ore = 0;
		$minuti = 0;
		if($_POST['FREQUENZA']=="*DAILY")
			$ore = 24;
		else if($_POST['FREQUENZA']=="*WEEKLY")
			$ore = 7*24;
		else if($_POST['FREQUENZA']=="*TIME") {
			$interval_parts = explode(":",$_POST['INTERVALLO']);
			$ore = $interval_parts[0];
			$minuti = $interval_parts[1];
		}
		
		$intervallo_sec = ($ore*60*60) + ($minuti*60); 

//		echo "INTERVALLO: ".$_POST['INTERVALLO']." - SECONDI: $intervallo_sec<br>";
		
		$firing_date = $_POST['FIRING_DATE'];
		$firing_time = $_POST['FIRING_TIME'];

		$firing_unix = time_to_unix_timestamp($firing_date, $firing_time);
		
		$firing = wi400_format_UNIX_TIMESTAMP($firing_unix);
		
//		echo "FIRING: $firing_date $firing_time - FIRING UNIX: $firing_unix - CONTROL: $firing<br>";
		
		$last_firing_unix = 0;
		if(!empty($_POST['LAST_FIRING']))
			$last_firing_unix = time_to_unix_timestamp($_POST['LAST_FIRING']);
			
		$last_firing = wi400_format_UNIX_TIMESTAMP($last_firing_unix);
			
//		echo "LAST_FIRING: ".$_POST['LAST_FIRING']." - FIRING UNIX: $last_firing_unix - CONTROL: $last_firing<br>";
	}
		
	if($actionContext->getForm()=="DEFAULT") {
		wi400Detail::cleanSession("BATCHJOB_SCH_DETAIL");
		wi400Detail::cleanSession("XML_FILE_DETAIL");
		
		subfileDelete("BATCHJOB_SCH_LIST");
		
		$sql = "select * from $tabella";
		
		$subfile = new wi400Subfile($db, "BATCHJOB_SCH_LIST", $settings['db_temp'], 20);
		$subfile->setConfigFileName("BATCHJOB_SCHEDULER");
		$subfile->setModulo('batch');
		$subfile->setSql($sql);
		if (isset($settings['xmlservice'])) {
			$dataArea='PHPLIB/ZJOBSCDE';
			$ret = data_area_read($dataArea);
			$zchkjob = new wi400Routine('ZCHKJOB', $connzend);
			$zchkjob->load_description();
			$zchkjob->prepare();
			$zchkjob->set('JOB',$ret);
			$zchkjob->call();
			$status = $zchkjob->get('STATUS');
			$actstatus = $zchkjob->get('ACTSTATUS');
		} else {
			$status ="SYSTEM";
			$actstatus="VERIFICARE SCHEDULATORE SISTEMA";
		}
//		echo "RET: $ret - STATO: $status - FREEZE: $actstatus<br>";
	}
	else if($actionContext->getForm()=="NEW_JOB") {
		// Azione corrente
		$actionContext->setLabel("Nuovo lavoro");
		
		if(!isset($ID) || $ID=="")
			$ID = (int)getSysSequence("SCHEDULED");
		
		if(!isset($frequenza) || empty($frequenza)) {
			$frequenza = "*DAILY";
			$intervallo = "24:00";
		}
		
		if(!isset($firing) || empty($firing)) {
			$firing_unix = mktime(date('H'),date('i'),0,date('m'),date('d'),date('Y'));
			$firing = wi400_format_UNIX_TIMESTAMP($firing_unix);
			
			$firing_date = substr($firing,0,10);
			$firing_time = substr($firing,11,5); 
		}
		
//		echo "FIRING UNIX: $firing_unix<br>";
		
		if(!isset($stato) || $stato=="")
			$stato = "1";
	}
	else if($actionContext->getForm()=="DETAIL_JOB") {
		// Recupero delle chiavi
		$keyArray = array();
		$keyArray = getListKeyArray('BATCHJOB_SCH_LIST');
		
		$ID = $keyArray['ID'];
		$XML_path = $keyArray['XML_PATH'];
		$XML_file = $keyArray['XML_FILE'];
		
		$sql = "select * from $tabella where ID=?";
		
		$stmt = $db->prepareStatement($sql);
		$result = $db->execute($stmt, array($ID));
		$jobArray = $db->fetch_array($stmt);
		
		$frequenza = $jobArray['FREQUENZA'];
		
		$intervallo = wi400_format_SECONDS_SHORT_TIME($jobArray['INTERVALLO']);
		
//		echo "FIRING: ".$jobArray['FIRING']."<br>";
		
		$firing = wi400_format_UNIX_TIMESTAMP($jobArray['FIRING']);
		$firing_date = substr($firing,0,10);
		$firing_time = substr($firing,11,5); 
		
		$last_firing = wi400_format_UNIX_TIMESTAMP($jobArray['LAST_FIRING']);
		
		$stato = $jobArray['STATO'];
		
		// Azione corrente
		$actionContext->setLabel("Modifica lavoro ($ID)");
	}
	else if($actionContext->getForm()=="DETAIL_XML") {
		wi400Detail::cleanSession("XML_FILE_DETAIL");
		wi400Detail::cleanSession("XML_FILE_BODY");
		
		// Recupero delle chiavi
		$keyArray = array();
		$keyArray = getListKeyArray('BATCHJOB_SCH_LIST');
		
		$ID = $keyArray['ID'];
		$nome = $keyArray['NOME'];
		$des_job = $keyArray['DES_LAVORO'];
		$XML_path = $keyArray['XML_PATH'];
		$XML_file = $keyArray['XML_FILE'];
		
		$XML = $XML_path."/".$XML_file;
		
//		echo "XML: $XML<br>";
		
		$dom_xml = load_XML_file($XML);
		
		$params = array();
		$extra_params = array();
		
		if($dom_xml!==false) {
			// Estrazione dei dati di interesse dalla response XML
			$params = parse_XML_file($dom_xml);

			if(!is_array($params)) {
				$params = array();
			}
			
			if(!empty($params)) {
				foreach($params as $key => $val) {
					if(!in_array($key,$required_params))
					$extra_params[$key] = $val;
				}
			}
					
//			throw_soap_fault($params);
					
//			echo "PARAMS: "; print_r($params); echo "<br>";
//			echo "EXTRA_PARAMS: "; print_r($extra_params); echo "<br>";
		}
		
		// Azione corrente
		$actionContext->setLabel("File XML ($ID)");
	}
	else if($actionContext->getForm()=="INSERT_JOB") {
		$fields = array("ID","NOME","DES_LAVORO","XML","FREQUENZA","INTERVALLO","FIRING","NUMERO_ESECUZIONI"
			,"LAST_FIRING","STATO");
			
		$XML = $XML_path.$_POST['XML'];
		
//		echo "XML: $XML<br>";
		
		$campi = array($_POST['ID'],$_POST['NOME'],$_POST['DES_LAVORO'],$XML,$_POST['FREQUENZA'],$intervallo_sec,
			$firing_unix,0,$last_firing_unix,$_POST['STATO']);
			
		$stmt = $db->prepare("INSERT", $tabella, '', $fields);
		$result = $db->execute($stmt, $campi);
		
		if($result)
			$messageContext->addMessage("SUCCESS", "Inserimento record eseguito con successo");
		else
			$messageContext->addMessage("ERROR", "Si sono verificati degli errori nell'inserimento");
		
		$actionContext->onSuccess($azione, "DEFAULT");
		$actionContext->onError($azione, "DETAIL_JOB");
	}
	else if($actionContext->getForm()=="UPDATE_JOB") {
		// Recupero delle chiavi
		$keyArray = array();
		$keyArray = getListKeyArray('BATCHJOB_SCH_LIST');
		
		$ID = $keyArray['ID'];
		
		$XML = $XML_path.$_POST['XML'];

//		echo "XML: $XML<br>";		
		
		// Impostazione della condizione WHERE
		$keys = array("ID"=>$ID);
		
		$fields = array("NOME","DES_LAVORO","XML","FREQUENZA","INTERVALLO","FIRING","LAST_FIRING","STATO");
		
		$campi = array($_POST['NOME'],$_POST['DES_LAVORO'],$XML,$_POST['FREQUENZA'],$intervallo_sec,
			$firing_unix,$last_firing_unix,$_POST['STATO']);
		
		$stmt = $db->prepare("UPDATE", $tabella, $keys, $fields);
		$result = $db->execute($stmt, $campi);
		
		if($result)
			$messageContext->addMessage("SUCCESS", "Aggiornamento record eseguito con successo");
		else
			$messageContext->addMessage("ERROR", "Si sono verificati degli errori durante l'aggiornamento del record");
		
		$actionContext->onSuccess($azione, "DEFAULT");
		$actionContext->onError($azione, "DETAIL_JOB");
	}
	else if($actionContext->getForm()=="REMOVE_JOBS") {
		$idList = $_REQUEST['IDLIST'];
		
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
		
		// Recupero degli elementi selezionati
		$rowsSelectionArray = $wi400List->getSelectionArray();
		
//		echo "SELECTIONS: "; print_r($rowsSelectionArray); echo "<br>";
		
		foreach($rowsSelectionArray as $key => $val) {
			$lavoro_parts = explode("|", $key);
			
			$ID = $lavoro_parts[0];
			$XML_path = $lavoro_parts[2];
			$XML_file = $lavoro_parts[3];
			
			$XML = $XML_path."/".$XML_file;
			
//			echo "XML: $XML<br>";
			
			if(file_exists($XML)) {
				$path_parts = pathinfo($XML);
				
				if(isset($path_parts['extension']) && in_array($path_parts['extension'],array("txt","xml")))
					unlink($XML);
				else
					$messageContext->addMessage("ALERT", "Il file: $XML del lavoro $ID non ha un estensione corretta");
			}
			else {
				$messageContext->addMessage("ALERT", "Il file: $XML del lavoro $ID non esiste");
			}
		
			$stmtdelete = $db->prepare("DELETE", $tabella, array("ID"), null);
			$deleteRes = $db->execute($stmtdelete, array($ID));
			
			if($deleteRes)
				$messageContext->addMessage("SUCCESS", "Eliminazione del lavoro $ID avvenuta con successo");
			else
				$messageContext->addMessage("ERROR", "Errore nell'eliminazione del lavoro: $ID");
		}
		
		$actionContext->onSuccess($azione, "DEFAULT");
		$actionContext->onError($azione, "DEFAULT");
	}
	else if($actionContext->getForm()=="SAVE_XML") {
		// Recupero delle chiavi
		$keyArray = array();
		$keyArray = getListKeyArray('BATCHJOB_SCH_LIST');
		
		$ID = $keyArray['ID'];
		$nome = $keyArray['NOME'];
		
		$XML = $XML_path."/".$keyArray['XML_FILE'];

//		echo "XML: $XML<br>";

		$id_array = array();
		$id_array = wi400Detail::getDetailValue("XML_FILE_EXTRAS","XML_ID");
		
		$value_array = array();
		$value_array = wi400Detail::getDetailValue("XML_FILE_EXTRAS","XML_VALUE");
		
		foreach($required_params as $val) {
			$id_array[] = $val;
			$value_array[] = wi400Detail::getDetailValue("XML_FILE_BODY","job_".$val);
		}
		
//		echo "ID_ARRAY: "; print_r($id_array); echo "<br>";
//		echo "VALUE_ARRAY: "; print_r($value_array); echo "<br>";

		if(!empty($id_array) && !empty($value_array)) {
			$XML_code = create_XML_file($id_array, $value_array);
			
			// 'w': sovrascrive il file ; 'a': scrive in coda al testo già esistente nel file
			$file_handle = fopen($XML, 'w'); 
			fwrite($file_handle, $XML_code);
			fclose($file_handle);
			
			$messageContext->addMessage("SUCCESS", "File XML salvato corretamente");
		}
		else
			$messageContext->addMessage("ERROR", "Nessun dato presente per poter creare il file XML");

		$actionContext->onSuccess($azione, "DEFAULT");
		$actionContext->onError($azione, "DEFAULT");
	}
	else if($actionContext->getForm()=="STOP_BATCH_SCH") {
		$batc_act = stop_batch_scheduler($ret);
		
		if ($batc_act===false){
			$messageContext->addMessage("ERROR", "Errore durante l'interruzione della schedulazione dei lavori.");
		} 
		else {
			$messageContext->addMessage("SUCCESS", "Schedulazione dei lavori terminata con successo.");
		}
		
		$actionContext->onSuccess($azione, "DEFAULT");
		$actionContext->onError($azione, "DEFAULT");
	}
	else if($actionContext->getForm()=="FREEZE_BATCH_SCH") {
		$batc_act = freeze_batch_scheduler($ret);
		
		if ($batc_act===false){
			$messageContext->addMessage("ERROR", "Errore durante il congelamento della schedulazione dei lavori.");
		} 
		else {
			$messageContext->addMessage("SUCCESS", "Schedulazione dei lavori congelata con successo.");
		}
		
		$actionContext->onSuccess($azione, "DEFAULT");
		$actionContext->onError($azione, "DEFAULT");
	}
	else if($actionContext->getForm()=="START_BATCH_SCH") {
		if($status=="*ACTIVE" && $actstatus!="HLD") {
			$batc_act = start_batch_scheduler();
			
			if ($batc_act===false){
				$messageContext->addMessage("ERROR", "Errore durante la riattivazione della schedulazione dei lavori.");
			} 
			else {
				$messageContext->addMessage("SUCCESS", "Schedulazione dei lavori riattivata con successo.");
			}
		}
		else {
			if($status=="*OUTQ")
				$batc_act = start_batch_scheduler();
			else if($actstatus=="HLD")
				$batc_act = release_batch_scheduler($ret);
				
			if ($batc_act===false){
				$messageContext->addMessage("ERROR", "Errore durante la riattivazione della schedulazione dei lavori.");
			} 
			else {
				$messageContext->addMessage("SUCCESS", "Schedulazione dei lavori riattivata con successo.");
			}
		}
		
		$actionContext->onSuccess($azione, "DEFAULT");
		$actionContext->onError($azione, "DEFAULT");
	}
	else if($actionContext->getForm()=="MODIFICA_STATO") {
		$actionContext->setLabel("Modifca lo stato");
	}

?>