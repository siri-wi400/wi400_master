<?php

	if($actionContext->getForm()=="DEFAULT") {
		validation_default();
	}
	
	function validation_default() {
		global $messageContext;
		
		if(isset($_POST["DATA_INI"]) && trim($_POST["DATA_INI"])!="")
			$data_ini = $_POST['DATA_INI'];
		if(isset($_POST["DATA_FIN"]) && trim($_POST["DATA_FIN"])!="")
			$data_fin = $_POST['DATA_FIN'];
		
		if(isset($data_ini) && isset($data_ini)){
			$check = check_periodo($data_ini, $data_fin);
		
			if($check===false)
				$messageContext->addMessage("ERROR", "La data di INIZIO deve essere precedente a quella di FINE", "DATA_INI",true);
		}
		else if(isset($data_ini) && !isset($data_fin)){
			$messageContext->addMessage("ERROR", "Manca la data di fine", "DATA_FIN",true);
		}
		else if(!isset($data_ini) && isset($data_fin)){
			$messageContext->addMessage("ERROR", "Manca la data di inizio", "DATA_INI",true);
		}
	}