<?php

	if($actionContext->getForm()=="DEFAULT") {
		validation_default();
	}
	
	function validation_default() {
		global $messageContext;
		
		// Range periodo
		if(isset($_POST["MESE"]) && !empty($_POST["MESE"]) && isset($_POST["ANNO"]) && empty($_POST["ANNO"])){
			$messageContext->addMessage("ERROR", "Inserire il range completo mese + anno", "ANNO",true);
		}
		// Controllo il mese
		if(isset($_POST["MESE"]) && !empty($_POST["MESE"])){
			if ($_POST["MESE"] > 12 || $_POST["MESE"] < 1)
			$messageContext->addMessage("ERROR", "Mese non corretto", "MESE",true);
		}
//		if(isset($_POST["ANNO"]) && !empty($_POST["ANNO"]) && isset($_POST["MESE"]) && empty($_POST["MESE"])){
//			$messageContext->addMessage("ERROR", "Inserire il range completo mese/anno", "MESE",true);
//		}
	}