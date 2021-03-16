<?php

	  $keyArray = getListKeyArray("RECORDLIST");
      $libreria = $_REQUEST["LIBRERIA"];
      $tabella =  $_REQUEST["TABELLA"];
      $nrel = $keyArray["NREL"];
	  $actionContext->setLabel("Libreria:".$libreria. " Tabella:".$tabella." NREL:".$nrel);
      $sql="SELECT * FROM $libreria/$tabella A WHERE RRN(A)=$nrel";
      $result=$db->singleQuery($sql);
      $row = $db->fetch_array($result);
      $result=$db->columns($tabella,"",False,"",$libreria);
      // Aggiornamento Record ...
      $field = array();
      $struttura= $db->columns($tabella,"",False,"",$libreria);		
      
      if ($actionContext->getForm() == "UPDATE") {
      	 // Valorizzo i campi modificati a video con i campi del tracciato di output
	 	foreach($row as $key => $tracciato) {
			if(isset($_POST[$key])) {
				// Verifico il tipo di campo per prepararlo per la scrittura
				if ($struttura[$key]['DATA_TYPE']=='2' or $struttura[$key]['DATA_TYPE']=='2') {
					$_POST[$key]= doubleViewToModel($_POST[$key]);
				}
				$row[$key] = $_POST[$key];
				$field[$key]= $key;
			}
		}
		$key = array("RRN($tabella)"=>$nrel);
		$stmt = $db->prepare("UPDATE", "$libreria/$tabella", $key, $field);
		$do = $db->execute($stmt, $row);
		if (!$do) $messageContext->addMessage("ERROR", "Errori di aggiornamento!");
		
      }
      // Inserimento Record ...
      if ($actionContext->getForm() == "INSERT") {
      	 // Valorizzo i campi modificati a video con i campi del tracciato di output
      foreach($row as $key => $tracciato) {
			if(isset($_POST[$key])) {
				// Verifico il tipo di campo per prepararlo per la scrittura
				if ($struttura[$key]['DATA_TYPE']=='2' or $struttura[$key]['DATA_TYPE']=='2') {
					$_POST[$key]= doubleViewToModel($_POST[$key]);
				}
				$row[$key] = $_POST[$key];
				$field[$key]= $key;
			}
		}		$stmt = $db->prepare("INSERT", "$libreria/$tabella", Null, $field);
		$do = $db->execute($stmt, $row);
		if (!$do) $messageContext->addMessage("ERROR", "Errori di aggiornamento!");
      }
      // Cancellazione record ...
      if ($actionContext->getForm() == "DELETE") {
      	 // Valorizzo i campi modificati a video con i campi del tracciato di output
		$key = array("RRN($tabella)");
        $field= $db->columns($tabella,"",True,"",$libreria);		
		$stmt = $db->prepare("DELETE", "$libreria/$tabella", $key, null);
		$do = $db->execute($stmt, array($nrel));
		if (!$do) $messageContext->addMessage("ERROR", "Errori di aggiornamento!");
		
      }
?>