<?php

	require_once 'image_list_viewer_common.php';
	
	$azione = $actionContext->getAction();
//	echo "AZIONE: $azione<br>";
	
	if($actionContext->getForm()=="DEFAULT")
		$history->addCurrent();
	
	$image_path_array = array();
	if(wi400Detail::getDetailValue($azione."_SRC", 'IMAGE_PATH')!="")
		$image_path_array = wi400Detail::getDetailValue($azione."_SRC", 'IMAGE_PATH');
//	echo "IMAGE PATH ARRAY:<pre>"; print_r($image_path_array);
	
	$remote_path = wi400Detail::getDetailValue($azione."_SRC", 'REMOTE_PATH');
//	echo "REMOTE PATH: $remote_path<br>";
	
	$exclude_types = array();
	if(wi400Detail::getDetailValue($azione."_SRC", 'EXCLUDE_TYPES')!="")
		$exclude_types = wi400Detail::getDetailValue($azione."_SRC", 'EXCLUDE_TYPES');
	
	$include_only_types =  wi400Detail::getDetailValue($azione."_SRC",'INCLUDE_ONLY_TYPES');
	if(is_null($include_only_types))
		$include_only_types = $include_only_types_def;
	else if($include_only_types=="")
		$include_only_types = array();
//	echo "INCLUDE_ONLY_TYPES:<pre>"; print_r($include_only_types); echo "</pre>";
	
	if($actionContext->getForm()=="DEFAULT") {
		$label = $actionContext->getLabel();
		
		$actionContext->setLabel("Parametri");
	}
	else if($actionContext->getForm()=="LIST") {
		subfileDelete($azione."_LIST");
		
		$subfile = new wi400Subfile($db, $azione."_LIST", $settings['db_temp'], 20);
		$subfile->setConfigFileName("IMAGE_LIST_VIEWER");
		$subfile->setModulo("analisi");
		
		$subfile->addParameter("IMAGE_PATHS", $image_path_array);
		$subfile->addParameter("REMOTE_PATH", $remote_path);
		
		if(!isset($exclude_types))
			$exclude_types = array();
//		echo "EXCLUDE TYPES:<pre>"; print_r($exclude_types); echo "</pre>";
		
		$subfile->addParameter("EXCLUDE_TYPES", $exclude_types, false);
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
		
		$actionContext->onSuccess($azione,"LIST",$actionContext->getGateway());
		$actionContext->onError($azione,"LIST","",$actionContext->getGateway(),true);
	}