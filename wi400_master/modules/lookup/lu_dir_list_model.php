<?php

$azione = $actionContext->getAction();

$file_paths = array();
if(isset($_REQUEST['FILE_PATHS']) && $_REQUEST['FILE_PATHS']!="") {
	$file_paths = explode(";", $_REQUEST['FILE_PATHS']);
}

$file_types = array();
if(isset($_REQUEST['FILE_TYPES']) && $_REQUEST['FILE_TYPES']!="") {
	$file_types = explode(";", $_REQUEST['FILE_TYPES']);
}

$full_path = false;
if(isset($_REQUEST['FULL_PATH']) && !empty($_REQUEST['FULL_PATH'])) {
	$full_path = $_REQUEST['FULL_PATH'];
}

$show_info = true;
if(isset($_REQUEST['SHOW_INFO']) && !empty($_REQUEST['SHOW_INFO'])) {
	$show_info = $_REQUEST['SHOW_INFO'];
}

//echo "FILE PATHS:<pre>"; print_r($file_paths); echo "</pre>";
//echo "FILE TYPES:<pre>"; print_r($file_types); echo "</pre>";
//echo "FULL PATH: $full_path<br>";

subfileDelete("LU_DIR_LIST");

$subfile = new wi400Subfile($db, "LU_DIR_LIST", $settings['db_temp'], 20);
$subfile->setConfigFileName("LU_DIR_LIST");
$subfile->setModulo("lookup");

$subfile->addParameter("FILE_PATHS", $file_paths, false);
$subfile->addParameter("FILE_TYPES", $file_types, false);
$subfile->addParameter("FULL_PATH", $full_path, false);

$subfile->setSql("*AUTOBODY");