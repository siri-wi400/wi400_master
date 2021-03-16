<?php

	if($actionContext->getForm()=="DEFAULT") {
//		echo "REQUEST:<pre>"; print_r($_REQUEST); echo "</pre>";
		
		if($_REQUEST['f']=="IMPORT") {
			validation_default();
		}
	}
	
	function validation_default() {
		global $messageContext;
		
		if(!isset($_FILES["IMPORT_FILE"])) {
			$messageContext->addMessage("ERROR", "Selezionare il file da importare", "IMPORT_FILE", true);
		}
	}