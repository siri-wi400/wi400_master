<?php

$parametri = array('PROGRESS_BAR', 'ID', 'UTENTE', 'VALORE');
$flag_parm = true;
foreach($parametri as $parm) {
	if(!isset($_REQUEST[$parm])) {
		$flag_parm = false;
		break;
	}
}

if($flag_parm) {
	$idProgress = $_REQUEST['PROGRESS_BAR'];
	$idSession = $_REQUEST['ID'];
	$utente = $_REQUEST['UTENTE'];
	$current_val = $_REQUEST['VALORE'];
	
	//require_once dirname(__FILE__).'/../../conf/wi400.conf.php';
	require_once dirname(__FILE__).'/../../base/includes/getconfiguration.php';
	$settings = wi400GetSettings("");
	//require_once realpath('../')."/conf/wi400.conf.php";
	
	// Reperisco file
	// costruisco path file ...
	$path_file = $settings['data_path'].$utente."/tmp/".$_REQUEST['ID'].$idProgress.".perc";
	
	//echo $path_file."<br/>";
	
	if(file_exists($path_file)) {
		$current = file_get_contents($path_file);
		$current = floor($current);
		
		if($current < $current_val) {
			$current = $current_val;
		}
		
		if ($current >= 100){
			$current = 100;
			
			// Cancello file
			unlink($path_file);
		}
		
		echo json_encode(array("percentage" => $current));
	}
}
