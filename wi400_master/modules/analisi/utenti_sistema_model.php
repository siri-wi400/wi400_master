<?php 

	$azione = $actionContext->getAction();
	
	$history->addCurrent();
	
	if($actionContext->getForm()=="DEFAULT") {
		$sql = "select * from FESTRUTE where ESTSTA<>'0'";
		
		switch($azione) {
			case "UTENTI_SISTEMA": 
//				$sql = "select * from FESTRUTE"; 
				$sql .= "";
				break;
			case "UTENZE_LOCALI_CHIUSI":
//				$sql = "select * from FESTRUTE where ESTSTO='N' and ESTABI='*ENABLED'"; 
				$sql .= " and ESTSTO='N' and ESTABI='*ENABLED'";
				break;
			case "UTENZE_LOCALI_APERTI":
//				$sql = "select * from FESTRUTE where ESTSTO='S' and (ESTABI='*DISABLED' or ESTDUL='')"; 
				$sql .= " and ESTSTO='S' and (ESTABI='*DISABLED' or ESTDUL='')";
				break;
		}
		
//		echo "SQL: $sql<br>";
		
//		subfileDelete($azione."_LIST");
		
		$subfile = new wi400Subfile($db, $azione."_LIST", $settings['db_temp'], 20);
		$subfile->setConfigFileName("UTENTI_SISTEMA");
		$subfile->setModulo('analisi');
		
		$subfile->setSql($sql);
	}

?>