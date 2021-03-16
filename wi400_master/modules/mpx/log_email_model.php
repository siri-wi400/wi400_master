<?php

	$azione = $actionContext->getAction();
	
	set_time_limit(2400);
	ini_set("memory_limit","200M");
	
	if($actionContext->getForm()!="DELETE_FILE")
		$history->addCurrent();
	
	$lines = "";
	$file_path = "";
	
	if($actionContext->getForm()=="DELETE_FILE") {
		$file_path = wi400Detail::getDetailValue($azione."_DET","FILENAME");
//		echo "FILE:$file_path<br>";

		if(file_exists($file_path)) {
			unlink($file_path);
			$messageContext->addMessage("SUCCESS", "Pulizia del file avvenuta con successo");
		}
		
		$actionContext->setForm("DEFAULT");
	}

	if($actionContext->getForm()=="DEFAULT") {
		// Azione corrente
		$actionContext->setLabel("File log delle e-mail");
	
//		$file_path = $moduli_path."/mpx/include/cvtspool_invio.log";
//		$file_path = $root_path."logs/email/cvtspool_invio.log";

		// file di log
		$file_email_path = get_log_file_path("LOG_EMAIL");
		
		$file_email_name = get_log_file_name("LOG_EMAIL");
		
		$file_path = $file_email_path.$file_email_name;
		
		if(file_exists($file_path)) {
			$path_parts = pathinfo($file_path);
			if(isset($path_parts['extension']) && $path_parts['extension']=="log")
				$lines = file_get_contents($file_path);
		}
	}

?>