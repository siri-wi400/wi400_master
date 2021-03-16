<?php

	$azione = $actionContext->getAction();
//	echo "AZIONE: $azione<br>";
	
	if($actionContext->getForm()=="DEFAULT")
		$history->addCurrent();
	
	if($actionContext->getForm()=="DEFAULT") {
		if($azione=="LOG_MANAGER") {
			$file_type = "ALL";
		}
		else {
			if(strstr($azione, "_MANAGER")!==false)
				$file_type = substr($azione,0,strpos($azione,"_MANAGER"));
			else
				$file_type = $azione;
		}
//		echo "FILE TYPE: $file_type<br>";
		
		subfileDelete($azione."_LIST");
		
		$subfile = new wi400Subfile($db, $azione."_LIST", $settings['db_temp'], 20);
		$subfile->setConfigFileName("LOG_FILES_LIST");
		$subfile->setModulo("analisi");
		
		$subfile->addParameter("FILE_TYPE", $file_type, false);
		$subfile->addParameter("LOG_FILES_PATHS", $log_files_paths);
		
		if(!isset($exclude_types))
			$exclude_types = array();
//		echo "EXCLUDE TYPES:<pre>"; print_r($exclude_types); echo "</pre>";
		
		$subfile->addParameter("EXCLUDE_TYPES", $exclude_types, false);
		
		if(!isset($include_only_types))
			$include_only_types = array();
//		echo "INCLUDE ONLY TYPES:<pre>"; print_r($include_only_types); echo "</pre>";
		
		$subfile->addParameter("INCLUDE_ONLY_TYPES", $include_only_types, false);
		
		$subfile->setSql("*AUTOBODY");
	}
	else if($actionContext->getForm()=="FILE_PRV") {
//		echo "DETAIL KEY: ".$_REQUEST["DETAIL_KEY"]."<br>";
		
		$detail_key = explode('|', $_REQUEST["DETAIL_KEY"]);
		$file_path = trim($detail_key[0]);
//		echo "FILE_PATH: $file_path<br>";
	}
	else if($actionContext->getForm()=="DELETE_FILES") {
		$idList = $_REQUEST["IDLIST"];
//		echo "IDLIST: $idList<br>";
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
		$rowsSelectionArray = $wi400List->getSelectionArray();
//		echo "ROW SEL:<pre>"; print_r($rowsSelectionArray); echo "</pre>";

		foreach($rowsSelectionArray as $key => $value){
			$keyArray = array();
			$keyArray = explode("|",$key);
			
			$file_path = $keyArray[0];
//			echo "DELETE FILE: $file_path<br>";
			
			if(file_exists($file_path)) {
				unlink($file_path);
			}
		}
		
		$messageContext->addMessage("SUCCESS", _t('JOB_LOG_FILE_CLEAN'));
		
		$actionContext->onSuccess($azione,"DEFAULT");
		$actionContext->onError($azione,"DEFAULT","","",true);
	}