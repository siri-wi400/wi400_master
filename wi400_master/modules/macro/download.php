<?php
	global $data_path;
	
	$fileClass = realpath('../../')."/routine/generali/wi400File.php";
	require_once $fileClass;
	$iniFile = realpath('../../')."/conf/wi400.conf.php";
	require_once $iniFile;

	$data_path = $settings["data_path"];
	
	
	$filename = wi400File::getUserFile("tmp", $_GET['FILE_NAME'], $_GET['USER_NAME']);
	
	header("Content-Type: application/octet-stream");
	header("Content-Disposition: attachment; filename=".basename($filename).";");
	header("Content-Length: ".filesize($filename));	
		
	$file_handle = fopen($filename, "rb");
	
	while(!feof($file_handle)) {
		echo fread($file_handle, 65536);
		flush();
		if (connection_status () != 0) {
			@fclose($file_handle);
			die();
		}
	}
