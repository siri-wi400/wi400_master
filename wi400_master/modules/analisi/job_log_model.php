<?php 

	require_once 'job_log_commons.php';
	
//	echo "SESSION ID:".session_id()."<br>";
//	echo "IP: ".$_SESSION['IP']." - MY IP: ".$_SERVER['MY_IP']."<br>";

	set_time_limit(2400);
	ini_set("memory_limit","200M");
	
	$azione = $actionContext->getAction();
	
//	echo "REQUEST: <pre>"; print_r($_REQUEST); echo "</pre>";

	if($actionContext->getForm()!="DEFAULT") {
		$steps = $history->getSteps();
	
		$last_step = $steps[count($steps)-1];
		$last_action = substr($last_step,strlen($azione)+1);
//		echo "LAST ACTION: $last_action<br>";
	}
	
	if(!in_array($actionContext->getForm(),array("DELETE_FILE","IMPORT_SESSION")))
		$history->addCurrent();
	
//	$ID_job = str_pad($_SESSION['connectionID'],6,'0',STR_PAD_LEFT);
	$result = executeCommand("rtvjoba",array(),array("nbr" => "ID_job"));
//	echo "JOB_ID: $ID_job<br>";

	if(!in_array($actionContext->getForm(),array("DEFAULT","DELETE_FILE", "SEND_MESSAGE", "SEND_MESSAGE_GO"))) {
		$keyArray = array();
		$keyArray = getListKeyArray('LAVORI_ATTIVI_LIST');
		
		$jobName = $keyArray["JOBNAME"];
		$userName = $keyArray["USERNAME"];
		$jobNumber = $keyArray["JOBNUM"];
		$user_id = $keyArray['USER_ID'];
		$des_ute = $keyArray['USER_DES'];
		$ip = $keyArray['IP'];
		
		$dati = array();
		$dati = get_job_log_data($jobName,$userName,$jobNumber,$actionContext->getForm());
		$lines = $dati['LINES'];
		$session_id = $dati['SESSION_ID'];
		
//		echo "<br>LINES: $lines<br>";	
	}
	else if($actionContext->getForm()=="DELETE_FILE") {
		$file_path = wi400Detail::getDetailValue($azione."_LOG_SQL_DET","FILENAME");
//		echo "FILE:$file_path<br>";

		if(file_exists($file_path)) {
			unlink($file_path);
			$messageContext->addMessage("SUCCESS", _t('JOB_LOG_FILE_CLEAN'));
		}
		
//		$actionContext->setForm("DEFAULT");
		$actionContext->onSuccess($azione,$last_action);
	}
	
	if($actionContext->getForm()=="DEFAULT") {
		subfileDelete("LAVORI_ATTIVI_LIST");
		
		$job_attuale_obj = wi400Detail::getDetailField($azione.'_DETAIL',"JOB_ATTUALE");
		$job_attuale = false;
		if($job_attuale_obj!="") {
			$job_attuale = $job_attuale_obj->getChecked();
		}
		
		$subfile = new wi400Subfile($db, "LAVORI_ATTIVI_LIST", $settings['db_temp'], 20);
		$subfile->setConfigFileName("LAVORI_ATTIVI_LIST");
		$subfile->setModulo("analisi");
		
		$subfile->addParameter("JOB_ATTUALE",$job_attuale,false);
		$subfile->addParameter("ID_JOB",$ID_job,false);
		$subfile->addParameter("FORM",$actionContext->getForm(),false);
		
		$subfile->setSql("*AUTOBODY");
	}
	else if($actionContext->getForm()=="LOG_LAVORO") {
		// Azione corrente
		$actionContext->setLabel(_t('JOB_LOG'));
	}
	else if($actionContext->getForm()=="LOG_SQL") {
		// Azione corrente
		$actionContext->setLabel("Log SQL");

		$directory = $settings['log_sql'];
		
		$dir_handle = opendir($directory);
		
		$file_path = _t('FILE_NOT_EXIST')."<br>";
		while(($file = readdir($dir_handle))!==false) {
//			echo "FILE: $file<br>";
/*
			if(strpos($file,$session_id)!==false) {
				$file_path = $directory.$file;
//				echo "FILE PATH: $file_path<br>";
				break;
			}		
*/
			if($file==$user_id."_".$session_id.".txt") {
				$file_path = $directory.$file;
//				echo "FILE PATH: $file_path<br>";
				break;	
			}	
		}
		
		closedir($dir_handle);
		
		$lines = "";
		if(file_exists($file_path)) {
			$path_parts = pathinfo($file_path);
			if(isset($path_parts['extension']) && $path_parts['extension']=="txt")
				$lines = file_get_contents($file_path);
		}
	}
	else if($actionContext->getForm()=="DATI_SESSIONE") {
		// Azione corrente
		$actionContext->setLabel(_t('SESSION_DATA'));
		
		$dati = array();
		$file_path = "";
		$lines = "";
		
		$dati = get_session_data($session_id);
		if(!empty($dati)) {
			$file_path = $dati['FILE_PATH'];
			$lines = $dati['LINES'];
		}
	}
	else if($actionContext->getForm()=="SEND_MESSAGE_GO") {
		$message = array();
		$message[0]="MSG";
		$message[1]=wi400Detail::getDetailValue("SEND_SESSIONE_DETAIL", "MESSAGGIO");
		$message[2]=session_id();
		$message[3]="*YES";
		$keyArray = getListKeyArray("LAVORI_ATTIVI_LIST");
			
		$session_id = $keyArray['SESSION'];
		$handle = fopen($settings['message_path'].$session_id.".txt", "a+");
		fwrite($handle, implode("|", $message)."\r\n");
		fclose($handle);

	}
	else if($actionContext->getForm()=="IMPORT_SESSION") {	
		$steps = $history->getSteps();
		
		if(in_array($azione."_DATI_SESSIONE",$steps)) {
			$session_id = wi400Detail::getDetailValue($azione."_DATI_SESSIONE_DET","SESSION");
//			echo "MY ID: $my_id - OTHER ID: $session_id<br>";
		}
		else {
			$keyArray = array();
			$keyArray = getListKeyArray("LAVORI_ATTIVI_LIST");
			
			$job_num = $keyArray['JOBNUM'];
			if($job_num==$ID_job) {
				header("Location: ".$appBase."index.php?t=".$azione);
				exit();
			}
		}
		
		$my_id = session_id();
//		echo "SESSION ID: ".session_id()."<br>";
		
		session_write_close();

		session_id($session_id);
		session_start();
//		echo "JOB_LOG SESSION ID: ".session_id()."<br>";
		
		$_SESSION['LOGOUT_ACTION'] = "BACK_SESSION";
		$_SESSION['OLD_SESSION_ID'] = $my_id;
//		$_SESSION['MY_IP'] = $_SERVER['REMOTE_ADDR'];
		$_SESSION['OLD_MY_IP'] = $_SESSION['MY_IP'];
		
//		echo "JOB_LOG IP: ".$_SESSION['MY_IP']." - MY IP: ".$_SESSION['OLD_MY_IP']."<br>";

		// Redirect
		$action = $_SESSION['DEFAULT_ACTION'];
		header("Location: ".$appBase."index.php?t=".$action);
		exit();
	}

?>