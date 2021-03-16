<?php
	$azione = $actionContext->getAction();
	$form = $actionContext->getForm();
	require_once "telnet_5250_class.php";
	require_once "telnet_5250_common.php";
	
	if($form == "DOWNLOAD_EXTRACTION") {
		$actionContext->setLabel("Esportazione completata");
		
		$filename = "EXT_SUBFILE_".$_REQUEST['ID'].".csv";
		$temp = "tmp";
		$TypeImage = "xls.png";
		
	}