<?php

	$idDetail = $actionContext->getAction()."_SRC";
	
	wi400Detail::cleanSession($idDetail);
	
	$image_path_array = array();
	$exclude_types = array();
	$include_only_types = array();
	$remote_path = "";

	if($actionContext->getGateway()=="IMPORT_XLS_DESCARTIMG") {
		require_once p13n('modules/siri_import_xls/import_xls_showroom_common.php');
		
		$image_path_array = array($image_path);
	}
	
	$fieldObj = new wi400InputText("IMAGE_PATH");
	$fieldObj->setValue($image_path_array);
	wi400Detail::setDetailField($idDetail, $fieldObj);
	
	if($remote_path!="") {
		$fieldObj = new wi400InputText("REMOTE_PATH");
		$fieldObj->setValue($remote_path);
		wi400Detail::setDetailField($idDetail, $fieldObj);
	}
	
	if(!empty($exclude_types)) {
		$fieldObj = new wi400InputText("EXCLUDE_TYPES");
		$fieldObj->setValue($exclude_types);
		wi400Detail::setDetailField($idDetail, $fieldObj);
	}
	
	if(!empty($include_only_types)) {
		$fieldObj = new wi400InputText("INCLUDE_ONLY_TYPES");
		$fieldObj->setValue($include_only_types);
		wi400Detail::setDetailField($idDetail, $fieldObj);
	}