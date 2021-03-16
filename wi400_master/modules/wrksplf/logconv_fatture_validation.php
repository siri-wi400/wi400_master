<?php

	if($actionContext->getForm()=="DEFAULT") {
		validation_default();
	}
	
	function validation_default() {
		global $messageContext;
		
		// Data fattura
		if(isset($_POST["DATA_INI"]) && trim($_POST["DATA_INI"])!="")
			$data_ini = $_POST['DATA_INI'];
		if(isset($_POST["DATA_FIN"]) && trim($_POST["DATA_FIN"])!="")
			$data_fin = $_POST['DATA_FIN'];
//		echo "DATA INI: $data_ini - DATA_FIN: $data_fin<br>";
		
		if(isset($data_ini) && isset($data_fin)){
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
		
		// Data stampa
		if(isset($_POST["DATA_STMP_INI"]) && trim($_POST["DATA_STMP_INI"])!="")
			$data_stmp_ini = $_POST['DATA_STMP_INI'];
		if(isset($_POST["DATA_STMP_FIN"]) && trim($_POST["DATA_STMP_FIN"])!="")
			$data_stmp_fin = $_POST['DATA_STMP_FIN'];
		
		if(isset($data_stmp_ini) && isset($data_stmp_fin)){
			$check = check_periodo($data_stmp_ini, $data_stmp_fin);
		
			if($check===false)
				$messageContext->addMessage("ERROR", "La data di INIZIO deve essere precedente a quella di FINE", "DATA_STMP_INI",true);
		}
		else if(isset($data_stmp_ini) && !isset($data_stmp_fin)){
			$messageContext->addMessage("ERROR", "Manca la data di fine", "DATA_STMP_FIN",true);
		}
		else if(!isset($data_stmp_ini) && isset($data_stmp_fin)){
			$messageContext->addMessage("ERROR", "Manca la data di inizio", "DATA_STMP_INI",true);
		}
//		echo "STAMPA DATA INI: $data_stmp_ini - DATA_FIN: $data_stmp_fin<br>";
/*		
		if(isset($data_stmp_ini) && isset($data_ini)) {
			$messageContext->addMessage("ERROR", "Filtrare o per Data Stampa o per Data Fattura");
		}
*/
	}