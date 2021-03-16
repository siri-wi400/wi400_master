<?php
/*
	if($actionContext->getForm()=="DEFAULT") {
		if(isset($_POST['BODY']) && trim($_POST['BODY'])!="")
			$body = $_POST['BODY'];
		
		if(isset($_POST['FILE_BODY']) && trim($_POST['FILE_BODY'])!="") {
			$file_body = $_POST['FILE_BODY'];
			
			$exists = true;
			if(!file_exists($file_body)) {
				$messageContext->addMessage("ERROR", "Il File Testo non esiste.", "",true);
				$exists = false;
			}
		}
		
//		if((!isset($body) || $body=="") && $exists===false)
//			$messageContext->addMessage("ERROR", "Testo dell'e-mail obbligatorio.", "BODY",true);
	}
*/