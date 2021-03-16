<?php 
if($actionContext->getGateway()=="SESSION_LIST_FILE") {
	// Reperisco i dati
	$keyArray = array();
	$keyArray = getListKeyArray("SESSION_LIST_FILE_LIST");
	
	// Carico la lista ed il subfile per reperire le informazioni
	$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, "SESSION_LIST_FILE_LIST");
	
	$subfile_name = $wi400List->getSubfile();
	$subfile = wi400Session::load(wi400Session::$_TYPE_SUBFILE, $subfile_name);	
	$subfile_table = $subfile->getTable();
	
	$sql = "SELECT FILE_PATH, TIPO FROM $subfile_table WHERE FILE_NAME='".$keyArray['FILE_NAME']."'";
	$result = $db->singleQuery($sql);
	$row = $db->fetch_array($result);
	
	$int = new wi400InputText("FILE");
	$int->setValue($row['FILE_PATH']);
	wi400Detail::setDetailField("NAVIGATE_OBJECT_DETAIL",$int);
	
	$dat = new wi400InputText("TIPO");
	$dat->setValue($row['TIPO']);
	wi400Detail::setDetailField("NAVIGATE_OBJECT_DETAIL",$dat);
	
	$gat = new wi400InputText("FROM_GATEWAY");
	$gat->setValue($actionContext->getGateway());
	wi400Detail::setDetailField("NAVIGATE_OBJECT_DETAIL",$gat);
}
if($actionContext->getGateway()=="SERIALIZED_FILE") {
	// Reperisco i dati
	$keyArray = array();
	$keyArray = getListKeyArray("SERIALIZED_FILE_LIST");
	
	// Carico la lista ed il subfile per reperire le informazioni
	$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, "SERIALIZED_FILE_LIST");
	
	$subfile_name = $wi400List->getSubfile();
	$subfile = wi400Session::load(wi400Session::$_TYPE_SUBFILE, $subfile_name);
	$subfile_table = $subfile->getTable();
	
	$sql = "SELECT FILE_PATH, TIPO FROM $subfile_table WHERE FILE_NAME='".$keyArray['FILE_NAME']."'";
	$result = $db->singleQuery($sql);
	$row = $db->fetch_array($result);
	
	$int = new wi400InputText("FILE");
	$int->setValue($row['FILE_PATH']);
	wi400Detail::setDetailField("NAVIGATE_OBJECT_DETAIL",$int);
	
	$dat = new wi400InputText("TIPO");
	//$dat->setValue($row['TIPO']);
	$dat->setValue('DATA');
	wi400Detail::setDetailField("NAVIGATE_OBJECT_DETAIL",$dat);
	
	$gat = new wi400InputText("FROM_GATEWAY");
	$gat->setValue($actionContext->getGateway());
	wi400Detail::setDetailField("NAVIGATE_OBJECT_DETAIL",$gat);
}
if($actionContext->getGateway()=="DEVELOPER_DOC") {
	require_once 'developer_functions.php';
	// Reperisco i dati
	$sessione = wi400Detail::getDetailValue("DEVELOPER_DOC_SRC", "SESSIONE");
//	$session_file="/tmp/wi400/sess_".$sessione;
	$session_file = getSessionPath()."sess_".$sessione;
	
	$int = new wi400InputText("FILE");
	$int->setValue($session_file);
	wi400Detail::setDetailField("NAVIGATE_OBJECT_DETAIL",$int);
	
	$dat = new wi400InputText("TIPO");
	//$dat->setValue($row['TIPO']);
	$dat->setValue('SESSION');
	wi400Detail::setDetailField("NAVIGATE_OBJECT_DETAIL",$dat);
	
	$gat = new wi400InputText("FROM_GATEWAY");
	$gat->setValue($actionContext->getGateway());
	wi400Detail::setDetailField("NAVIGATE_OBJECT_DETAIL",$gat);
}
?>
