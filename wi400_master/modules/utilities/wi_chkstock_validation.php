<?php

if($actionContext->getForm()=="DEFAULT"){
		// Data richiesta
		if(isset($_POST["DATAD"]) && trim($_POST["DATAD"])!="")
			$data_ric_ini = $_POST['DATAD'];
		if(isset($_POST["DATAL"]) && trim($_POST["DATAL"])!="")
			$data_ric_fin = $_POST['DATAL'];
			
		if(isset($data_ric_ini) && isset($data_ric_fin)){
			$check = check_periodo($data_ric_ini, $data_ric_fin);
				
			if($check===false)
				$messageContext->addMessage("ERROR", "La data di INIZIO deve essere precedente a quella di FINE", "DATAD",true);
		}
		// Cumulativo o articolo - non possono essere inseriti entrambi			
		if($_POST["CUMU"]!="" && $_POST["ARTI"]!=""){
				$messageContext->addMessage("ERROR", "Non possono essere inseriti cumulativo ed articolo insieme", "CUMU",true);
		}
		// Almeno una selezione
		if(($_POST['MAGAZZINO']) =="" && ($_POST['POST']) =="" && ($_POST['CUMU']) =="" && ($_POST['ARTI']) ==""){
			$messageContext->addMessage("ERROR", "Effettuare almeno una selezione", "",true);
		}
/*		// Se solo le date inserite lancio il lavoro in batch senza fare vedere la lista a video
		$formatw = "EXPORT_BATCH";
		if (($_POST['MAGAZZINO']) !="" || ($_POST['POST']) !="" || ($_POST['CUMU']) !="" || ($_POST['ARTI']) !=""){
			$formatw = "LIST";
		}
		$actionContext->onSuccess("WI_CHKSTOCK",$formatw);
		$actionContext->onError("WI_CHKSTOCK","DEFAULT");*/
}
