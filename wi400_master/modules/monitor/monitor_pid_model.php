<?php

	require_once 'monitor_pid_commons.php';
	
	$azione = $actionContext->getAction();
    $file_path = $data_path."/COMMON/USRPIDS/";

	
	if(in_array($actionContext->getForm(),array("DEFAULT","FILE_VIEWER")))
		$history->addCurrent();
	
	if(in_array($actionContext->getForm(),array("FILE_VIEWER","SAVE_FILE"))) {
		$keyArray = array();
		$keyArray = getListKeyArray($azione."_LIST");
		
		$file_path = $keyArray['FILE'];
		$stato = $keyArray['STATO'];
		$locale = $keyArray['LOCALE'];
		$des_locale = $keyArray['DES_LOCALE'];
		$data_creazione = $keyArray['DATA_CREAZIONE'];
		$size = $keyArray['DIMENSIONE'];
	}
	
	if($actionContext->getForm()=="DEFAULT") {
		subfileDelete($azione."_LIST");
		
		$subfile = new wi400Subfile($db, $azione."_LIST", $settings['db_temp'], 20);
		$subfile->setConfigFileName("MONITOR_PID_LIST");
		$subfile->setModulo("monitor");
		$subfile->addParameter("FILE_PATH", $file_path);
		$subfile->setSql("*AUTOBODY");
	}
	else if($actionContext->getForm()=="FILE_PRV") {
//		echo "DETAIL KEY: ".$_REQUEST["DETAIL_KEY"]."<br>";
		
		$detail_key = explode('|', $_REQUEST["DETAIL_KEY"]);
		$file_path = trim($detail_key[0]);
//		echo "FILE_PATH: $file_path<br>";
	}
	else if($actionContext->getForm()=="FILE_VIEWER") {
		$show = true;
		if(!in_array($stato,array("0","1"))) {
			$show = false;
		}
		
		if($show===true && file_exists($file_path)) {
			if($size<20000000) {
				$lines = file_get_contents($file_path);
			}
		}
		else {
			$messageContext->addMessage("ALERT","Il file non può essere aperto.");
			$actionContext->gotoAction($azione,"DEFAULT","",true);
		}
	}
	if($actionContext->getForm()=="KILL_PROCESS") {

		$keyArray = array();
		$keyArray = getListKeyArray($azione."_LIST");
		// Recupero subfile
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $azione."_LIST");
		$subfile_name = $wi400List->getSubfile();
		$subfile = wi400Session::load(wi400Session::$_TYPE_SUBFILE, $subfile_name);
		$subfile_table = $subfile->getTable();
		// Query 
		$query = "SELECT * FROM $subfile_table where RRN=".$keyArray['RRN'];
		$result = $db->singleQuery($query);
		$row = $db->fetch_array($result);
        // KILL PID
        $output = shell_exec("kill -9 ".$row['PID']);
		// KILL INFO		
		$joba = explode("_", $row['JOBINFO']);
		$job = $joba[2]."/".$joba[1]."/".$joba[0];;
        $output = shell_exec("system 'QGPL/KILL JOB($job)'");	
		// KILL DB
		$joba = explode("_", $row['DBINFO']);
		$job = $joba[2]."/".$joba[1]."/".$joba[0];
        $output = shell_exec("system 'QGPL/KILL JOB($job)'");
		$messageContext->addMessage("INFO", "Operazione effettuata, controllare la lista per verificare l'operazione");
		
		$actionContext->onSuccess($azione,"DEFAULT");
		$actionContext->onError($azione,"DEFAULT");			     		
	}
?>