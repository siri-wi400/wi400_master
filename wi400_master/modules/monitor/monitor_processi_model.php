<?php
	require_once 'monitor_processi_commons.php';
	
	$azione = $actionContext->getAction();
	$form = $actionContext->getForm();
	
	if(!in_array($form,array("ATTIVITA")))
		$history->addCurrent();
	
	if($form == 'DEFAULT') {
		subfileDelete($azione."_LIST");
		
		$sql = "SELECT * FROM $tab_processi";
		
		$subfile = new wi400Subfile($db, "MONITOR_PROCESSI", $settings['db_temp'], 20);
		$subfile->setConfigFileName("MONITOR_PROCESSI");
		$subfile->setModulo("monitor");
		$subfile->setSql($sql);
	}else if($form == 'ATTIVITA') {
		$actionContext->setLabel('Attività processi');
		
		$wi400List = getList($azione."_LIST");
		$rowsSelectionArray = $wi400List->getSelectionArray();
		
		/*$sessioni = array();
		foreach($rowsSelectionArray as $key => $val) {
			$keyArray = explode("|",$key);
			
			$sessioni[] = $keyArray[0];
		}
		
		$righe = getTracciatoProcessi($sessioni);*/
	}else if($form == 'ESEGUI_ATTIVITA') {
		
		showArray($_REQUEST);
		
		$wi400List = getList($azione."_LIST");
		$rowsSelectionArray = $wi400List->getSelectionArray();
		
		foreach($rowsSelectionArray as $key => $value) {
			$row = get_list_keys_num_to_campi($wi400List, explode("|",$key));

			if(isset($_REQUEST['KILL_SESSION'])) {
				$session = $row['PROSID'];
				$path_sess = session_save_path();
			
				// Cancello tutte le tabelle temporanee create
				$db->destroyTable($session);
				$db->clearPHPTEMP($session);
				// Cancello i file wi400Session
				wi400Session::destroyBySession($session);
			
				//Cancello il file di sessione
				/*$file = $path_sess.$session;
				if(file_exists($file)) {
					unlink($file);
				}*/
			}
			if(isset($_REQUEST['KILL_JOB_AS400'])) {
				$output = shell_exec("system 'QGPL/KILL JOB(".$row['PROJOA'].")'");
			}
			if(isset($_REQUEST['KILL_JOB_DB'])) {
				$output = shell_exec("system 'QGPL/KILL JOB(".$row['PROJAD'].")'");
			}
			if(isset($_REQUEST['KILL_JOB_PHP'])) {
				$output = shell_exec("kill -9 ".$row['PROPID']);
			}
		}
		
		$messageContext->addMessage('SUCCESS', 'Attività eseguita con successo.');
		
		$actionContext->gotoAction('CLOSE', 'CLOSE_WINDOW_MSG', '', true); 
	}