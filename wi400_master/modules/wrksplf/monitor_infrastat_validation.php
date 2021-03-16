<?php

	if($actionContext->getForm()=="DEFAULT") {
		validation_default();
	}
	
	function validation_default() {
		global $messageContext;
		
		if(isset($_POST["ANNO_SRC_INI"]) && trim($_POST["ANNO_SRC_INI"])!="")
			$anno_rif_ini = $_POST['ANNO_SRC_INI'];
		if(isset($_POST["MESE_SRC_INI"]) && trim($_POST["MESE_SRC_INI"])!="")
			$mese_rif_ini = $_POST['MESE_SRC_INI'];
		if(isset($_POST["ANNO_SRC_FIN"]) && trim($_POST["ANNO_SRC_FIN"])!="")
			$anno_rif_fin = $_POST['ANNO_SRC_FIN'];
		if(isset($_POST["MESE_SRC_FIN"]) && trim($_POST["MESE_SRC_FIN"])!="")
			$mese_rif_fin = $_POST['MESE_SRC_FIN'];
		
		$data_rif_ini = "";
		if($anno_rif_ini!="" && $mese_rif_ini!="")
			$data_rif_ini = sprintf("%04s", $anno_rif_ini).sprintf("%02s", $mese_rif_ini)."01";
		
		$data_rif_fin = "";
		if($anno_rif_fin!="" && $mese_rif_fin!="")
			$data_rif_fin = sprintf("%04s", $anno_rif_fin).sprintf("%02s", $mese_rif_fin)."31";
		
		if(isset($data_rif_ini) && isset($data_rif_fin)){
			$check = check_periodo($data_rif_ini, $data_rif_fin);
		
			if($check===false)
				$messageContext->addMessage("ERROR", "La data di INIZIO deve essere precedente a quella di FINE");
		}
	}