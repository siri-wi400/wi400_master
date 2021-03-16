<?php
require_once "developer_auth.php";
	$azione = $actionContext->getAction();
	if($actionContext->getForm()=="DEFAULT") {
		$sessione = wi400Detail::getDetailValue("SESSION_LIST_FILE_SRC", "SESSIONE");
		subfileDelete("SERIALIZED_FILE");
		
		$subfile = new wi400Subfile($db, "SERIALIZED_FILE", $settings['db_temp'], 20);
		$subfile->setConfigFileName("SERIALIZED_FILE");
		$subfile->setModulo("developer");
		$path =$data_path."COMMON/serialize/";
		$subfile->addParameter("LOG_FILES_PATHS", $path);
		
		$subfile->setSql("*AUTOBODY");
	}
	else if($actionContext->getForm()=="FILE_PRV") {
//		echo "DETAIL KEY: ".$_REQUEST["DETAIL_KEY"]."<br>";
		
		$detail_key = explode('|', $_REQUEST["DETAIL_KEY"]);
		$file_path = trim($detail_key[0]);
//		echo "FILE_PATH: $file_path<br>";
	}
	else if($actionContext->getForm()=="DELETE_OBJECT") {
		$idList = $_REQUEST["IDLIST"];
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
		$subfile_name = $wi400List->getSubfile();
		$subfile = wi400Session::load(wi400Session::$_TYPE_SUBFILE, $subfile_name);
		$subfile_table = $subfile->getTable();
		$rowsSelectionArray = $wi400List->getSelectionArray();

		foreach($rowsSelectionArray as $key => $value){
			$keyArray = array();
			$keyArray = explode("|",$key);
			$sql = "SELECT FILE_PATH, TIPO FROM $subfile_table WHERE FILE_NAME='".$keyArray[0]."'";
			$result = $db->singleQuery($sql);
			$row = $db->fetch_array($result);
			$file_path = $row['FILE_PATH'];
			if(file_exists($file_path)) {
				unlink($file_path);
			}
		}
		$messageContext->addMessage("SUCCESS", "Cancellazione Eseguita");
		$actionContext->onSuccess($azione,"DEFAULT");
		$actionContext->onError($azione,"DEFAULT","","",true);
	}
	else if($actionContext->getForm()=="DELETE__ALL_OBJECT") {
		$idList = $_REQUEST["IDLIST"];
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
		$subfile_name = $wi400List->getSubfile();
		$subfile = wi400Session::load(wi400Session::$_TYPE_SUBFILE, $subfile_name);
		$subfile_table = $subfile->getTable();
		$sql = "SELECT FILE_PATH, TIPO FROM $subfile_table";
		$result = $db->query($sql);
		while ($row = $db->fetch_array($result)) {
			$file_path = $row['FILE_PATH'];
			if(file_exists($file_path)) {
				unlink($file_path);
			}
		}
		$messageContext->addMessage("SUCCESS", "Cancellazione Eseguita");
		$actionContext->onSuccess($azione, "DEFAULT");
		$actionContext->onError($azione,"DEFAULT","","",true);
	}