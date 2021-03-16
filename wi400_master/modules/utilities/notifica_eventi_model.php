<?php

	require_once 'notifica_eventi_common.php';
	
	require_once $routine_path."/classi/wi400invioEmail.cls.php";
//	require_once $routine_path."/classi/wi400invioConvert.cls.php";
	
	$azione = $actionContext->getAction();
	
	if(in_array($actionContext->getForm(), array("DEFAULT", "LIST", "DESTINATARI")))
		$history->addCurrent();
	
	$tipo_evento = wi400Detail::getDetailValue($azione."_SRC", 'TIPO_EVENTO');
	$stato = wi400Detail::getDetailValue($azione."_SRC", 'STATO');
	
	if($actionContext->getForm()=="DEFAULT") {
		$label = $actionContext->getLabel();
		$actionContext->setLabel("Parametri");	
	}
	else if($actionContext->getForm()=="LIST") {
		$where = "";
		
		$where_array = array();
		
		if($tipo_evento!="")
			$where_array[] = "TIPO='$tipo_evento'";
		
		if($stato!="") {
			$where_array[] = "STATO='".$stato_evento_vals[$stato]."'";
		}
		
		if(!empty($where_array))
			$where = implode(" and ", $where_array);
	}
	else if($actionContext->getForm()=="DESTINATARI") {
		$actionContext->setLabel("Destinatari");
		
		$keyArray = array();
		$keyArray = getListKeyArray($azione."_LIST");
		
		$id = $keyArray['ID'];
		$tipo_evento = $keyArray['TIPO'];
		$des = $keyArray['DES'];
		$stato = $keyArray['STATO'];
		$data_ora_ins = $keyArray['DATA_ORA_INS'];
		$data_ora_ntf = $keyArray['DATA_ORA_NTF'];
	}
	else if($actionContext->getForm()=="NEW_DEST") {
	
	}
	else if($actionContext->getForm()=="SAVE_DEST") {
	
	}
	else if($actionContext->getForm()=="DEL_DEST") {
	
	}
	else if($actionContext->getForm()=="NOTIFICA") {
		$idList = $azione."_LIST";
//		echo "IDLIST: $idList<br>";
		
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
		
		$rowsSelectionArray = $wi400List->getSelectionArray();
		
		$sql = "select * from FEVENTLST where ID=?";
		$stmt = $db->singlePrepare($sql, 0, true);
		
		$sql_ntf = "select * from FEVENTNTF where ID=?";
		$stmt_ntf = $db->prepareStatement($sql_ntf, 0, false);
		
		$to_array = array();
		$cc_array = array();
		$bcc_array = array();
		$rpyto_array = array();
		$conto_array = array();
		
		foreach($rowsSelectionArray as $key => $value){
			$keyArray = array();
			$keyArray = explode("|",$key);
			
			$id = $keyArray[0];
			
			$res = $db->execute($stmt, array($id));
			
			if($row = $db->fetch_array($stmt)) {
				// Recupero dei destinatari
				$res_ntf = $db->execute($stmt_ntf, array($row['ID']));
			
				while($row_ntf = $db->fetch_array($stmt_ntf)) {
					if($row_ntf['TIPO']=='TO')
						$to_array[] = $row['TO'];
					else if($row_ntf['TIPO']=='CC')
						$cc_array[] = $row['TO'];
					else if($row_ntf['TIPO']=='BCC')
						$bcc_array[] = $row['TO'];
					else if($row_ntf['TIPO']=='RPYTO')
						$rpyto_array[] = $row['TO'];
					else if($row_ntf['TIPO']=='CONTO')
						$conto_array[] = $row['TO'];
				}
				
//				$from = "info@siri-informatica.it";
				
				$dest_array = array(
					"CC" => $cc_array,
					"BCC" => $bcc_array,
					"RPYTO" => $rpyto_array,
					"CONTO" => $conto_array
				);
				
				$subject = "Segnalazione Evento ".$row['TYPE']." ".$row['ID'];
				
				$body = wi400_format_STRING_COMPLETE_TIMESTAMP($row['DATA_INS'].$row['ORA_INS'])." - ";
				$body .= "Segnalazione Evento ".$row['TIPO']." ".$row['ID']."\r\n";
				$body .= $row['DES'];
				
				$sent = wi400invioEmail::invioEmail($from, $to_array, $dest_array, $subject, $body);
//				$sent = wi400invioEmail::invioConvert($from, $to_array, $dest_array, $subject, $body);
				
				if($sent===false)
					$messageContext->addMessage("ERROR", "Errore durante l'invio dell'email");
				else
					$messageContext->addMessage("SUCCESS", "Email inviata con successo");
			}
		}
		
		$actionContext->gotoAction($azione, "LIST", "", true);
	}
	else if($actionContext->getForm()=="INOLTRA_SEL") {
	
	}
	else if($actionContext->getForm()=="INOLTRA") {
		$from = "";
		if(wi400Detail::getDetailValue($azione."_INOLTRA_SEL_DET","FROM")!="")
			$from = wi400Detail::getDetailValue($azione."_INOLTRA_SEL_DET","FROM");
			
		$to_array = array();
		if(wi400Detail::getDetailValue($azione."_INOLTRA_SEL_DET","TO")!="")
			$to_array = wi400Detail::getDetailValue($azione."_INOLTRA_SEL_DET","TO");
			
		$cc_array = array();
		if(wi400Detail::getDetailValue($azione."_INOLTRA_SEL_DET","CC")!="")
			$cc_array = wi400Detail::getDetailValue($azione."_INOLTRA_SEL_DET","CC");
		
		$bcc_array = array();
		if(wi400Detail::getDetailValue($azione."_INOLTRA_SEL_DET","BCC")!="")
			$bcc_array = wi400Detail::getDetailValue($azione."_INOLTRA_SEL_DET","BCC");
		
		$rpyto_array = array();
		$conto_array = array();
		
		$dest_array = array(
			"CC" => $cc_array,
			"BCC" => $bcc_array,
			"RPYTO" => $rpyto_array,
			"CONTO" => $conto_array
		);
//		echo "TO:<pre>"; print_r($to_array); echo "</pre>";
//		echo "DEST:<pre>"; print_r($dest_array); echo "</pre>";
		
		$idList = $azione."_LIST";
//		echo "IDLIST: $idList<br>";
	
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
	
		$rowsSelectionArray = $wi400List->getSelectionArray();
	
		$sql = "select * from FEVENTLST where ID=?";
		$stmt = $db->singlePrepare($sql, 0, true);
	
//		$from = "info@siri-informatica.it";
	
		foreach($rowsSelectionArray as $key => $value){
			echo "KEY: $key - VAL:<pre>"; print_r($value); echo "</pre>";
			
			$keyArray = array();
			$keyArray = explode("|",$key);
	
			$id = $keyArray[0];
//			echo "ID: $id<br>";
	
			$res = $db->execute($stmt, array($id));
	
			if($row = $db->fetch_array($stmt)) {
				$subject = "Segnalazione Evento ".$row['TIPO']." ".$row['ID'];
//				echo "OGGETTO: $subject<br>";
				
				$body = wi400_format_STRING_COMPLETE_TIMESTAMP($row['DATA_INS'].$row['ORA_INS'])." - ";
				$body .= "Segnalazione Evento ".$row['TIPO']." ".$row['ID']."\r\n";
				$body .= $row['DES'];
	
				$sent = wi400invioEmail::invioEmail($from, $to_array, $dest_array, $subject, $body);
//				$sent = wi400invioEmail::invioConvert($from, $to_array, $dest_array, $subject, $body);
	
				if($sent===false)
					$messageContext->addMessage("ERROR", "Errore durante l'invio della notifica eventi ".$row['TIPO']." ".$row['ID']);
				else
					$messageContext->addMessage("SUCCESS", "Notifica eventi ".$row['TIPO']." ".$row['ID']." inviata con successo");
			}
		}

		$actionContext->onSuccess("CLOSE", "CLOSE_WINDOW_MSG");
		$actionContext->onError($azione, "INOLTRA_SEL", "", "", true);
	}