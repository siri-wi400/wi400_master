<?php

	require_once 'notifica_eventi_common.php';

	require_once $routine_path."/classi/wi400invioEmail.cls.php";
//	require_once $routine_path."/classi/wi400invioConvert.cls.php";
/*	
	$sql = "select * from FEVENTLST where STATO='0'";
	$res = $db->query($sql, false, 0);
	
	$sql_ntf = "select * from FEVENTNTF where ID=?";
	$stmt_ntf = $db->prepareStatement($sql_ntf, 0, false);
	
	$keyUpdt = array("ID" => "?");
	$fieldsValue = array("STATO" => "1", "DATA_NTF" => date("Ymd"), "ORA_NTF" => date("his"));
	$stmt_updt = $db->prepare("UPDATE", "FEVENTLST", $keyUpdt, array_keys($fieldsValue));
	
	while($row = $db->fetch_array($res)) {
//		echo "ROW LIST:<pre>"; print_r($row); echo "</pre>";
		
		// Recupero dei destinatari
		$to_array = array();
		$cc_array = array();
		$bcc_array = array();
		$rpyto_array = array();
		$conto_array = array();
		
		$res_ntf = $db->execute($stmt_ntf, array($row['ID']));
		
		while($row_ntf = $db->fetch_array($stmt_ntf)) {
//			echo "ROW EMAIL:<pre>"; print_r($row_ntf); echo "</pre>";
			
			if($row_ntf['TIPO']=='TO')
				$to_array[] = $row_ntf['EMAIL'];
			else if($row_ntf['TIPO']=='CC')
				$cc_array[] = $row_ntf['EMAIL'];
			else if($row_ntf['TIPO']=='BCC')
				$bcc_array[] = $row_ntf['EMAIL'];
			else if($row_ntf['TIPO']=='RPYTO')
				$rpyto_array[] = $row_ntf['EMAIL'];
			else if($row_ntf['TIPO']=='CONTO')
				$conto_array[] = $row_ntf['EMAIL'];
		}
		
//		$from = "info@wi400.com";
		
		$dest_array = array(
			"CC" => $cc_array,
			"BCC" => $bcc_array,
			"RPYTO" => $rpyto_array,
			"CONTO" => $conto_array
		);
//		echo "TO:<pre>"; print_r($to_array); echo "</pre>";
//		echo "DEST:<pre>"; print_r($dest_array); echo "</pre>";
		
		$subject = "Segnalazione Evento ".$row['TIPO']." ".$row['ID'];
		
		$body = "Segnalazione Evento ".$row['TIPO']." ".$row['ID']."\r\n";
		$body .= $row['DES']."\r\n";
//		echo "BODY: $body<br>";
		
		$sent = wi400invioEmail::invioEmail($from, $to_array, $dest_array, $subject, $body);
//		$sent = wi400invioConvert::invioEmail($from, $to_array, $dest_array, $subject, $body);
		
		if($sent===false)
			$messageContext->addMessage("ERROR", "Errore durante l'invio della notifica evento ".$row['ID']);
		else {
			$messageContext->addMessage("SUCCESS", "Notifica evento ".$row['ID']." inviata con successo");
			
			// Update dello stato dell'evento
			$campi = $fieldsValue;
			$campi[] = $row['ID'];
			
			$res_updt = $db->execute($stmt_updt, $campi);
		}
	}
*/
	$sql = "select * from FEVENTLST where STATO='0' order by TIPO";
	$res = $db->query($sql, false, 0);
	
	$sql_ntf = "select * from FEVENTNTF where ID=?";
	$stmt_ntf = $db->prepareStatement($sql_ntf, 0, false);
	
	$keyUpdt = array("ID" => "?");
	$fieldsValue = array("STATO" => "1", "DATA_NTF" => date("Ymd"), "ORA_NTF" => date("his"));
	$stmt_updt = $db->prepare("UPDATE", "FEVENTLST", $keyUpdt, array_keys($fieldsValue));
	
	$tipo = "";
	while($row = $db->fetch_array($res)) {
//		echo "ROW LIST:<pre>"; print_r($row); echo "</pre>";

		if($tipo!=$row['TIPO']) {
			if($tipo!="") {
				foreach($email_array as $email => $body_array) {
					$body_email = implode("\r\n", $body_array);
					
					$sent = wi400invioEmail::invioEmail($from, array($email), array(), $subject, $body_email);
//					$sent = wi400invioConvert::invioEmail($from, $email, array(), $subject, $body_email);
			
					if($sent===false)
						$messageContext->addMessage("ERROR", "Errore durante l'invio della notifica eventi ".$row['TIPO']);
					else {
						$messageContext->addMessage("SUCCESS", "Notifica eventi ".$row['TIPO']." inviata con successo");
													
						// Update dello stato degli eventi
						foreach($id_array as $id) {
							$campi = $fieldsValue;
							$campi[] = $id;
								
							$res_updt = $db->execute($stmt_updt, $campi);
						}
					}
				}
			}
			
			$tipo = $row['TIPO'];
			
//			$from = "info@siri-informatica.it";
			
			$subject = "Segnalazione Eventi ".$row['TIPO'];
			
			$email_array = array();
			$id_array = array();
		}
		
		$id_array[] = $row['ID'];
		
		$body = wi400_format_STRING_COMPLETE_TIMESTAMP($row['DATA_INS'].$row['ORA_INS'])." - ";
		$body .= "Segnalazione Evento ".$row['TIPO']." ".$row['ID']."\r\n";
		$body .= $row['DES']."\r\n";
//		echo "BODY: $body<br>";
		
		// Recupero dei destinatari
		$res_ntf = $db->execute($stmt_ntf, array($row['ID']));
		
		while($row_ntf = $db->fetch_array($stmt_ntf)) {
//			echo "ROW EMAIL:<pre>"; print_r($row_ntf); echo "</pre>";
			
			$email_array[$row_ntf['EMAIL']][] = $body;
		}
	}
	
	foreach($email_array as $email => $body_array) {
		$body_email = implode("\r\n", $body_array);
		
		$sent = wi400invioEmail::invioEmail($from, array($email), array(), $subject, $body_email);
//		$sent = wi400invioConvert::invioEmail($from, $email, array(), $subject, $body_email);
			
		if($sent===false)
			$messageContext->addMessage("ERROR", "Errore durante l'invio della notifica eventi ".$row['TIPO']);
		else {
			$messageContext->addMessage("SUCCESS", "Notifica eventi ".$row['TIPO']." inviata con successo");
										
			// Update dello stato degli eventi
			foreach($id_array as $id) {
				$campi = $fieldsValue;
				$campi[] = $id;
					
				$res_updt = $db->execute($stmt_updt, $campi);
			}
		}
	}