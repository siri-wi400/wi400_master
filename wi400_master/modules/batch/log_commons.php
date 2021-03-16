<?php

	$keyArray = array();
	$keyArray = getListKeyArray("BATCHJOB_LIST");
	
	$id = $keyArray['ID'];
	
	if(!isset($id)) {
		$keys = $_REQUEST['DETAIL_KEY'];
//		echo "REQUEST: <pre>"; print_r($_REQUEST); echo "</pre>";
		
		$keyArray = explode("|", $keys);
		
		$id = $keyArray[0];
	}
	
	$path = $settings['data_path']."BATCH/ID/";

	$log_files_paths = array(
		"BATCHJOB_FILES" => $path.$id."/",
	);