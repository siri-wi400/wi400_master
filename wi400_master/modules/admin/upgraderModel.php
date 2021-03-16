<?php

	// ***************************
	// Config Install
	// ***************************
	$pathToInstall = $settings['pathToInstall'];
	// ***************************
	$file_name = "";
	if ($actionContext->getForm() == "UPLOAD"){

		$doc_root = $_SERVER['DOCUMENT_ROOT'];
		require_once $doc_root."/".$settings['installerPath']."/PHPUnzip.class.php";
		require_once $doc_root."/".$settings['installerPath']."/PHPDeleter.class.php";
		
		set_time_limit(600); 
		
		ini_set("upload_max_filesize","10M");

		if (isset($_FILES['updateFile'])){
			$file_type = $_FILES['updateFile']['type'];
			$file_name = $_FILES['updateFile']['name'];
			$file_size = $_FILES['updateFile']['size'];
			$file_tmp = $_FILES['updateFile']['tmp_name'];
			
			$getExt = explode ('.', $file_name);
			$file_ext = $getExt[count($getExt)-1];
			
			if (strpos($file_name,"WI400") === false){
				$messageContext->addMessage("ERROR","<p>Nome del file errato.</p>");
			}else if ($file_ext != "zip"){
				$messageContext->addMessage("ERROR","<p>Caricare un file di tipo archivio ZIP</p>");
			}else if(!is_uploaded_file($file_tmp)){
				$messageContext->addMessage("ERROR","<p>Scegliere un file da caricare!</p>");
			}else{
				move_uploaded_file ($file_tmp, $doc_root."/".$settings['installerPath']."/".session_id());
				$messageContext->addMessage("SUCCESS","Caricamento effettuato in modo corretto!!");
			}
		}else{
			$messageContext->addMessage("ERROR","<p>Scegliere un file da caricare!</p>");
		}
		
		$actionContext->onError("UPGRADER","DEFAULT");
	}

?>