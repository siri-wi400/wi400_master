<?php

	$azione = $actionContext->getAction();

//	echo "AZIONE: $azione<br>";
//	echo "PATHS:<pre>"; print_r($log_files_paths); echo "</pre>";
//	echo "FILES:<pre>"; print_r($log_files_array); echo "</pre>";
	
	set_time_limit(2400);
	ini_set("memory_limit","200M");
	
	if($actionContext->getForm()=="DEFAULT") {
		if(!isset($_REQUEST['DECORATION'])) $history->addCurrent();
	}
	
	$lines = "";
	
	if($azione!="LOG_VIEWER") {
		$file_path = $log_files_paths[$azione].$log_files_array[$azione];
	}
	else {
		$steps = $history->getSteps();
//		echo "STEPS:<pre>"; print_r($steps); echo "</pre>";
		$last_step = $steps[count($steps)-2];
		$last_action_obj = $history->getAction($last_step);
		if (isset($last_action_obj)) {
			$last_action = $last_action_obj->getAction();
			$last_form = $last_action_obj->getForm();
		}
//		echo "LAST_ACTION:$last_action<br>";

		$idList = $last_action."_LIST";
//		echo "ID LIST: $idList<br>";
		
		$keyArray = array();
		$keyArray = getListKeyArray($idList);
		
		$file_path = $keyArray['FILE'];
	}
//	echo "FILE:$file_path<br>";
	
	if($actionContext->getForm()=="DEFAULT") {
		$size = 0;
		if(file_exists($file_path)) {
			$size = filesize($file_path);
			if($size<20000000) {
				$path_parts = pathinfo($file_path);
//				if(isset($path_parts['extension']) && $path_parts['extension']=="log")
					$lines = file_get_contents($file_path);
			}
		}
	}
	else if(in_array($actionContext->getForm(),array("DELETE_FILE","DELETE_FILE_BATCH"))) {
		if(file_exists($file_path)) {
			unlink($file_path);
			$messageContext->addMessage("SUCCESS", _t('JOB_LOG_FILE_CLEAN'));
		}

		if($actionContext->getForm()=="DELETE_FILE") {
			$actionContext->onSuccess($azione,"DEFAULT");
			$actionContext->onError($azione,"DEFAULT","","",true);
		}
	}