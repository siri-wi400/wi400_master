<?php

	require_once 'monitor_email_commons.php';

	$azione = "MONITOR_EMAIL";

	if(in_array($actionContext->getForm(), array("INS_EMAIL", "UPDT_EMAIL"))) {
		if($actionContext->getForm()=="UPDT_EMAIL") {
			$keyUpdt = array("ID" => $_POST['ID']);
				
			$fieldsValue = array();
				
			$fieldsValue['MAIUSR'] = $_POST['MAIUSR'];
				
			$fieldsValue['MAIEMA'] = "N";
			if(isset($_POST['INVIO_EMAIL']) && $_POST['INVIO_EMAIL']=="S")
				$fieldsValue['MAIEMA'] = $_POST['INVIO_EMAIL'];
				
			$fieldsValue['MAIMPX'] = "N";
			if(isset($_POST['INVIO_MPX']) && $_POST['INVIO_MPX']=="S")
				$fieldsValue['MAIMPX'] = $_POST['INVIO_MPX'];
				
			$fieldsValue['MAIFRM'] = $_POST['MAIFRM'];
			$fieldsValue['MAIALI'] = $_POST['MAIALI'];
			$fieldsValue['MAISBJ'] = $_POST['MAISBJ'];
				
			echo "UPDATE - FIELDS:<pre>"; print_r($fieldsValue); echo "</pre>";
				
			$stmt_updt = $db->prepare("UPDATE", "FPDFCONV", $keyUpdt, array_keys($fieldsValue));
			
			$result = $db->execute($stmt_updt, $fieldsValue);

			if(!$result)
				$messageContext->addMessage("ERROR","Errore durante la modifica dei dati dell'e-mail");
			else
				$messageContext->addMessage("SUCCESS","Modifica dei dati dell'e-mail eseguita con successo");
			
			$actionContext->onError($azione, "EMAIL_DET", "", "", true);
			$actionContext->onSuccess($azione, "EMAIL_LIST");
		}
		else if($actionContext->getForm()=="INS_EMAIL") {
			$fieldsValue = getDs("FPDFCONV");
				
			$stmt_ins = $db->prepare("INSERT", "FPDFCONV", null, array_keys($fieldsValue));
				
			// ID
//			$fieldsValue['ID'] = $_POST['ID'];
			
			$id = getSysSequence("EMAIL_CONV");
			$id = substr($id, 1);
			$id = "T".str_pad($id, 9, "0", STR_PAD_LEFT);
//			echo "ID: $id<br>";
			
			$fieldsValue['ID'] = $id;
				
			$fieldsValue['MAIUSR'] = $_POST['MAIUSR'];
			$fieldsValue['MAIFRM'] = $_POST['MAIFRM'];
			$fieldsValue['MAIALI'] = $_POST['MAIALI'];
			$fieldsValue['MAISBJ'] = $_POST['MAISBJ'];
				
			$fieldsValue['MAIEMA'] = "N";
			if(isset($_POST['INVIO_EMAIL']) && $_POST['INVIO_EMAIL']=="S")
				$fieldsValue['MAIEMA'] = $_POST['INVIO_EMAIL'];
	
			$fieldsValue['MAIMPX'] = "N";
			if(isset($_POST['INVIO_MPX']) && $_POST['INVIO_MPX']=="S")
				$fieldsValue['MAIMPX'] = $_POST['INVIO_MPX'];
			
			$fieldsValue['MAISTA'] = "1";
				
			$fieldsValue['MAIINS'] = $timeStamp;
			$fieldsValue['MAIELA'] = $timeStamp;
				
			echo "INSERT - FIELDS:<pre>"; print_r($fieldsValue); echo "</pre>";
			
			$result = $db->execute($stmt_ins, $fieldsValue);

			if(!$result)
				$messageContext->addMessage("ERROR","Errore durante l'aggiunta dell'e-mail");
			else
				$messageContext->addMessage("SUCCESS","Aggiunta dell'e-mail eseguita con successo");

			$actionContext->onError($azione, "NEW_EMAIL_DET", "", "", true);
			
			$steps = $history->getSteps();
//			echo "STEPS:<pre>"; print_r($steps); echo "</pre>";

			$last_action = "";
			$last_form = "";
			if(!empty($steps) && count($steps)>=2) {
				$last_step = $steps[count($steps)-2];
//				echo "LAST STEP: $last_step<br>";
			
				$last_action_obj = $history->getAction($last_step);
				if (isset($last_action_obj)) {
					$last_action = $last_action_obj->getAction();
					$last_form = $last_action_obj->getForm();
				}
			}
			echo "LAST_ACTION: $last_action - LAST FORM: $last_form<br>";

			if($last_form=="DEFAULT") {
//				$actionContext->onSuccess($azione, "DEFAULT", "", "NEW_EMAIL");
//				$actionContext->onSuccess($azione, "DEFAULT&ID_EMAIL=$id", "", "NEW_EMAIL");
				$actionContext->onSuccess($azione, "DEFAULT&ID_EMAIL=$id");
			}
			else {
//				$actionContext->onSuccess($azione, "EMAIL_LIST");
			}
		}
	}
	else if(in_array($actionContext->getForm(), array("INS_ATC", "UPDT_ATC"))) {
		if($actionContext->getForm()=="UPDT_ATC") {
			$keyArray = array();
			$keyArray = getListKeyArray("MONITOR_EMAIL_ATC_LIST");
				
			$id = $keyArray['ID'];
			$atc = $keyArray['MAIATC'];
				
			$keyUpdt = array("ID" => $id, "MAIATC" => $atc);
				
			$fieldsValue = array();
		
			$fieldsValue['MAIATC'] = $_POST['MAIATC'];
			$fieldsValue['MAIPAT'] = $_POST['MAIPAT'];
				
			$fieldsValue['CONV'] = "N";
			if(isset($_POST['CONVERSIONE']) && $_POST['CONVERSIONE']=="S")
				$fieldsValue['CONV'] = $_POST['CONVERSIONE'];
				
			$fieldsValue['TPCONV'] = $_POST['TPCONV'];
			$fieldsValue['MAIMOD'] = $_POST['MAIMOD'];
			$fieldsValue['MAIARG'] = $_POST['MAIARG'];
			$fieldsValue['MAINAM'] = $_POST['MAINAM'];
				
			$fieldsValue['FILZIP'] = "N";
			if(isset($_POST['ZIP']) && $_POST['ZIP']=="S")
				$fieldsValue['FILZIP'] = $_POST['ZIP'];
			
			$fieldsValue['MAISTO'] = "N";
			if(isset($_POST['STAMPATO']) && $_POST['STAMPATO']=="S")
				$fieldsValue['MAISTO'] = $_POST['STAMPATO'];
			
			$fieldsValue['MAIOUT'] = $_POST['MAIOUT'];
			
			$fieldsValue['MAISTT'] = getDb2Timestamp("00/00/0000");
		
			echo "UPDATE - FIELDS:<pre>"; print_r($fieldsValue); echo "</pre>";
		
			$stmt_updt = $db->prepare("UPDATE", "FEMAILAL a", $keyUpdt, array_keys($fieldsValue));

			$result = $db->execute($stmt_updt, $fieldsValue);

			if(!$result)
				$messageContext->addMessage("ERROR","Errore durante la modifica dei dati dell'allegato");
			else
				$messageContext->addMessage("SUCCESS","Modifica dei dati dell'allegato eseguita con successo");
			
			$actionContext->onError($azione, "ATC_DET", "", "", true);
		}
		else if($actionContext->getForm()=="INS_ATC") {
			$fieldsValue = getDs("FEMAILAL");
	
			$stmt_ins = $db->prepare("INSERT", "FEMAILAL", null, array_keys($fieldsValue));
				
			$fieldsValue['ID'] = $_POST['ID'];
	
			$fieldsValue['MAIATC'] = $_POST['MAIATC'];
			$fieldsValue['MAIPAT'] = $_POST['MAIPAT'];
			$fieldsValue['TPCONV'] = $_POST['TPCONV'];
			$fieldsValue['MAIMOD'] = $_POST['MAIMOD'];
			$fieldsValue['MAIARG'] = $_POST['MAIARG'];
			$fieldsValue['MAINAM'] = $_POST['MAINAM'];
	
			$fieldsValue['CONV'] = "N";
			if(isset($_POST['CONVERSIONE']) && $_POST['CONVERSIONE']=="S")
				$fieldsValue['CONV'] = $_POST['CONVERSIONE'];
	
			$fieldsValue['FILZIP'] = "N";
			if(isset($_POST['ZIP']) && $_POST['ZIP']=="S")
				$fieldsValue['FILZIP'] = $_POST['ZIP'];
			
			if(isset($fieldsValue['MAISTT'])) {
				$fieldsValue['MAISTT'] = getDb2Timestamp();
			}
			
			$fieldsValue['MAISTO'] = "N";
			if(isset($_POST['STAMPATO']) && $_POST['STAMPATO']=="S")
				$fieldsValue['MAISTO'] = $_POST['STAMPATO'];
				
			$fieldsValue['MAIOUT'] = $_POST['MAIOUT'];
				
			$fieldsValue['MAISTT'] = getDb2Timestamp("00/00/0000");
	
			echo "INSERT - FIELDS:<pre>"; print_r($fieldsValue); echo "</pre>";

			$result = $db->execute($stmt_ins, $fieldsValue);

			if(!$result)
				$messageContext->addMessage("ERROR","Errore durante l'aggiunta dell'allegato");
			else
				$messageContext->addMessage("SUCCESS","Aggiunta dell'allegato eseguita con successo");
			
			$actionContext->onError($azione, "NEW_ATC_DET", "", "", true);
		}

		$actionContext->onSuccess($azione, "ATC_LIST");
	}
	else if(in_array($actionContext->getForm(), array("INS_DEST", "UPDT_DEST"))) {
		if($actionContext->getForm()=="UPDT_DEST") {
			$keyArray = array();
			$keyArray = getListKeyArray("MONITOR_EMAIL_DEST_LIST");
				
			$id = $keyArray['ID'];
			$to = $keyArray['MAITOR'];

			$keyUpdt = array("ID" => $id, "MAITOR" => $to);
			echo "UPDATE - KEYS:<pre>"; print_r($keyUpdt); echo "</pre>";
				
			$fieldsValue = array();
				
			$fieldsValue['MAITOR'] = $_POST['MAITOR'];
			$fieldsValue['MAIALI'] = $_POST['MAIALI'];
			$fieldsValue['MATPTO'] = $_POST['MATPTO'];
				
			echo "UPDATE - FIELDS:<pre>"; print_r($fieldsValue); echo "</pre>";
				
			$stmt_updt = $db->prepare("UPDATE", "FEMAILDT", $keyUpdt, array_keys($fieldsValue));
			
			$result = $db->execute($stmt_updt, $fieldsValue);

			if(!$result)
				$messageContext->addMessage("ERROR","Errore durante la modifica dei dati del destinatario");
			else
				$messageContext->addMessage("SUCCESS","Modifica dei dati del destinatario eseguita con successo");
			
			$actionContext->onError($azione, "DEST_DET", "", "", true);
			$actionContext->onSuccess($azione, "DEST_LIST");
		}
		else if($actionContext->getForm()=="INS_DEST") {
			$fieldsValue = getDs("FEMAILDT");
	
			$stmt_ins = $db->prepare("INSERT", "FEMAILDT", null, array_keys($fieldsValue));
	
			$fieldsValue['ID'] = $_POST['ID'];
	
			$fieldsValue['MAITOR'] = $_POST['MAITOR'];
			$fieldsValue['MAIALI'] = $_POST['MAIALI'];
			$fieldsValue['MATPTO'] = $_POST['MATPTO'];
				
			echo "INSERT - FIELDS:<pre>"; print_r($fieldsValue); echo "</pre>";
			
			$result = $db->execute($stmt_ins, $fieldsValue);

			if(!$result)
				$messageContext->addMessage("ERROR","Errore durante l'aggiunta del destinatario");
			else
				$messageContext->addMessage("SUCCESS","Aggiunta del destinatario eseguita con successo");
			
			$actionContext->onError($azione, "NEW_DEST_DET", "", "", true);
			$actionContext->onSuccess("CLOSE", "CLOSE_WINDOW_MSG");
		}

//		$actionContext->onSuccess($azione, "DEST_LIST");
	}
	else if(in_array($actionContext->getForm(), array("INS_CONTENTS", "UPDT_CONTENTS"))) {
		if($actionContext->getForm()=="UPDT_CONTENTS") {
			$keyArray = array();
			$keyArray = getListKeyArray("MONITOR_EMAIL_CONTENTS_LIST");
	
			$id = $keyArray['ID'];
			$tipo = $keyArray['UCTTYP'];
	
			$keyUpdt = array("ID" => $id, "UCTTYP" => $tipo);
	
			$fieldsValue = array();
	
			$fieldsValue['UCTTYP'] = $_POST['UCTTYP'];
			$fieldsValue['UCTKEY'] = $_POST['UCTKEY'];
//			$fieldsValue['UCTRIG'] = $_POST['UCTRIG'];
			$fieldsValue['UCTRIG'] = 1;
	
			echo "UPDATE - FIELDS:<pre>"; print_r($fieldsValue); echo "</pre>";
	
			$stmt_updt = $db->prepare("UPDATE", "FEMAILCT", $keyUpdt, array_keys($fieldsValue));
				
			$result = $db->execute($stmt_updt, $fieldsValue);
	
			if(!$result)
				$messageContext->addMessage("ERROR","Errore durante la modifica dei dati del contenuto");
			else
				$messageContext->addMessage("SUCCESS","Modifica dei dati del contenuto eseguita con successo");
				
			$actionContext->onError($azione, "CONTENTS_DET", "", "", true);
			$actionContext->onSuccess($azione, "CONTENTS_LIST");
		}
		else if($actionContext->getForm()=="INS_CONTENTS") {
			$fieldsValue = getDs("FEMAILCT");
	
			$stmt_ins = $db->prepare("INSERT", "FEMAILCT", null, array_keys($fieldsValue));
	
			$fieldsValue['ID'] = $_POST['ID'];
	
			$fieldsValue['UCTTYP'] = $_POST['UCTTYP'];
			$fieldsValue['UCTKEY'] = $_POST['UCTKEY'];
//			$fieldsValue['UCTRIG'] = $_POST['UCTRIG'];
			$fieldsValue['UCTRIG'] = 1;
	
			echo "INSERT - FIELDS:<pre>"; print_r($fieldsValue); echo "</pre>";
				
			$result = $db->execute($stmt_ins, $fieldsValue);
	
			if(!$result)
				$messageContext->addMessage("ERROR","Errore durante l'aggiunta del contenuto");
			else
				$messageContext->addMessage("SUCCESS","Aggiunta del contenuto eseguita con successo");
				
			$actionContext->onError($azione, "NEW_CONTENTS_DET", "", "", true);
			$actionContext->onSuccess("CLOSE", "CLOSE_WINDOW_MSG");
		}
	
//		$actionContext->onSuccess($azione, "CONTENTS_LIST");
	}
	else if(in_array($actionContext->getForm(), array("INS_MPX", "UPDT_MPX"))) {
//		$fieldsValue = array();
		$fieldsValue = getDs("FMPXPARM");
		
//		echo "POST:<pre>"; print_r($_POST); echo "</pre>";
		
		$fieldsValue['ID'] = $_POST['ID'];
		$fieldsValue['TEST'] = "0";
		if(isset($_POST['MPX_TEST']) && $_POST['MPX_TEST']=="1")
			$fieldsValue['TEST'] = $_POST['MPX_TEST'];
		$fieldsValue['NUMPAG'] = 0;
		if(isset($_POST['NUMPAG']) && $_POST['NUMPAG']!="")
			$fieldsValue['NUMPAG'] = $_POST['NUMPAG'];
		$fieldsValue['WKPRID'] = $_POST['WKPRID'];
		$fieldsValue['ADDR1'] = $_POST['ADDR1'];
		$fieldsValue['ADDR2'] = $_POST['ADDR2'];
		$fieldsValue['ADDR3'] = $_POST['ADDR3'];
		$fieldsValue['CAP'] = $_POST['CAP'];
		$fieldsValue['CITTA'] = $_POST['CITTA'];
		$fieldsValue['PROV'] = $_POST['PROV'];
		$fieldsValue['NAZ'] = $_POST['NAZ'];
		$fieldsValue['GLOCOD'] = $_POST['GLOCOD'];
		$fieldsValue['SETID'] = $_POST['SETID'];
		$fieldsValue['SETCOD'] = $_POST['SETCOD'];
		$fieldsValue['PDFCOD'] = $_POST['PDFCOD'];
		$fieldsValue['ENVCOD'] = $_POST['ENVCOD'];
		
		if($actionContext->getForm()=="UPDT_MPX") {
			$keyUpdt = array("ID" => $_POST['ID']);
	
			unset($fieldsValue['ID']);
			echo "UPDATE - FIELDS:<pre>"; print_r($fieldsValue); echo "</pre>";
	
			$stmt_updt = $db->prepare("UPDATE", "FMPXPARM", $keyUpdt, array_keys($fieldsValue));
				
			$result = $db->execute($stmt_updt, $fieldsValue);
	
			if(!$result)
				$messageContext->addMessage("ERROR","Errore durante la modifica dei dati dell'e-mail");
			else
				$messageContext->addMessage("SUCCESS","Modifica dei dati dell'e-mail eseguita con successo");
		}
		else if($actionContext->getForm()=="INS_MPX") {
//			$fieldsValue = getDs("FMPXPARM");
	
			$stmt_ins = $db->prepare("INSERT", "FMPXPARM", null, array_keys($fieldsValue));
	
			echo "INSERT - FIELDS:<pre>"; print_r($fieldsValue); echo "</pre>";
				
			$result = $db->execute($stmt_ins, $fieldsValue);
	
			if(!$result)
				$messageContext->addMessage("ERROR","Errore durante l'aggiuta dei parametri MPX");
			else
				$messageContext->addMessage("SUCCESS","Aggiunta dei parametri MPX eseguita con successo");
		}
	
		$actionContext->onSuccess($azione, "EMAIL_LIST");
		$actionContext->onError($azione, "MPX_DET");
	}