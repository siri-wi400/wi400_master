<?php

echo "<font color='green'>email_invio.php</font><br>";

/**
 * @name email_invio.php
 * @desc Applicazione per l'invio di file via e-mail o di PDF ad MPX
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author Valeria Porrazzo
 * @version 1.00 26/06/2013
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

	require_once 'email_common.php';
	
	echo "ACTION: ".$actionContext->getAction()."<br>";
	echo "FORM: ".$actionContext->getForm()."<br>";
	
	// Recupero dei dati dell'e-mail
	$sql = "select * from FPDFCONV where ID=?";
	$stmt = $db->singlePrepare($sql, 0, true);
/*	
	// Controllo della presenza di allegati da generare tramite azione
	$sql_al_act = "select * from FEMAILAL where ID=? and TPCONV='ACTION'";
	$stmt_al_act = $db->singlePrepare($sql_al_act, 0, true);
	
	// Recupero dei contenuti XML legati all'e-mail
	$sql_xml = "select * from FEMAILCT where ID=? and UCTTYP='XML'";
	$stmt_xml = $db->singlePrepare($sql_xml, 0, true);
*/
	if(in_array($actionContext->getForm(), array("ESECUZIONE", "INOLTRO", "INVIO_MPX"))) {
		foreach($ID_array as $ID) {
			echo "ID: $ID<br>";
			
			$is_email = "N";
			$is_mpx = "N";
			$risped = 0;
			
			// Recupero dei dati del record
/*			
			$sql = "select * from FPDFCONV where ID='$ID'";
			$res = $db->singleQuery($sql);
			
			if($row = $db->fetch_array($res)) {
*/
			$res = $db->execute($stmt, array($ID));
			
			if($row = $db->fetch_array($stmt)) {			
				$is_email = $row['MAIEMA'];
				$is_mpx = $row['MAIMPX'];
				$risped = $row['MAIRIS'];
			}
			else {
				$msg = "Record ID: $ID non trovato nella tabella FPDFCONV";
				wi400invioConvert::agg_log($ID,'1',0,'006',$msg,$params['email_log_file'],"EXEC",$db);
				
				if($isBatch)
					die();
				else
					$messageContext->addMessage("ERROR", $msg);
			}
			
			if($is_mpx=="S") {
				$params['mpx_xml_path'] = $moduli_path."/mpx/include/";
				$params['mpx_xml_invio'] = $moduli_path."/mpx/include/invio/";
				$params['mpx_pdf_path'] = $moduli_path."/mpx/include/";
				
				if($settings['mpx_uri']!="")
					$params['mpx_uri'] = $settings['mpx_uri'];
				else
					$params['mpx_uri'] = 'http://'.$settings['mpx_server'].":".$settings['mpx_port'].$appBase."modules/mpx/include/response_MPX.php?ID=".$ID;
			}
/*			
			// Controllo della presenza di allegati da generare tramite azione
			$res_al_act = $db->execute($stmt_al_act, array($ID));
			
			if($row_al_act = $db->fetch_array($stmt_al_act)) {							
				// Verifico se esiste il file XML
				$xml_path = "/siri/email/$ID/xml_$ID.xml";
				echo "XML PATH: $xml_path<br>";
					
				$postxml = "";
				if(file_exists($xml_path)) {
					echo "FILE XML<br>";
					
					$handle = fopen($xml_path, "r");
				
					$postxml = fread($handle, filesize($xml_path));
				
					fclose($handle);
				}
				else {
					echo "FEMAILCT<br>";
					
					$res_xml = $db->execute($stmt_xml, array($ID));
					
					if($row_xml = $db->fetch_array($stmt_xml)) {
						$postxml = trim($conts['UCTKEY']);
					}
					else {
						$msg = "Contenuto XML legato all'e-mail non trovato. (".$ID.")";
						wi400invioConvert::write_log($ID,'1','025',$msg,$params['email_log_file'],"E-MAIL");
						
						if($isBatch)
							die();
						else
							$messageContext->addMessage("ERROR", $msg);
					}
				}
				
				$postxml = '<?xml version="1.0" ?><parametri><parametro id="action" value="SENDMAIL"/><parametro id="id" value="T000000617"/><parametro id="lista_librerie" value="SOCKETWNRC;UNICOMF;PHPLIB;MERSY_PERS;MERSY_DB;MERSY_OB;MERSY_SET;MERSY_TCP;MERSY_PLEX;MERSY_DIZD;MTRUNTOB;MTRUNTDB;MTRUNTPLEX;PLEX;QGPL;QDEVTOOLS;INTERFACCE;SENDYUNI;SENDYOBJ;PROBAS;MERSY_DWH;MERSY_GUAR"/><parametro id="user" value="MERSY"/><parametro id="appBase" value="//WI400_SVIL//"/><parametro id="nodb" value="True"/><parametro id="private" value="INVIOEMAIL_955556_197742"/><parametro id="jobname" value="MAIL"/><parametro id="timeout" value="1200"/><parametro id="fileSave" value="/SIRI/EMAIL/T000000617/xml_T000000617.xml"/><parametro id="WEMCISP" value=""/><parametro id="WEMCPCO" value="000000"/><parametro id="WEMCCAN" value="IPP"/><parametro id="WEMCFOR" value="68600"/><parametro id="WEMSTS" value=""/><parametro id="WEMTSHW" value=""/><parametro id="WEMCSHW" value="ZY"/><parametro id="WEMBSHW" value="2015"/><parametro id="WEMCAZI" value="900"/></parametri>';
				
				if(isset($postxml) && !empty($postxml)) {
					$xml_fields = array("POSTXML" => $postxml);
						
					$fields_string = "";
					foreach($xml_fields as $key => $value) {
						$xml_fields_string .= $key.'='.$value.'&';
					}
					
					rtrim($xml_fields_string, '&');
//					echo $fields_string;

//					$url = 'http://127.0.0.1:89'.$appBase.'batch.php';
//					$url = "http://".$_SERVER['SERVER_ADDR'].$appBase.'batch.php';
					$url = "http://".$_SERVER['HTTP_HOST'].$appBase.'batch.php';
					echo "URL: $url<br>";
					
//					echo "URL: ".curPageURL()."<br>";
//					echo "SERVER HOST: ".$_SERVER['HTTP_HOST']."<br>";
//					echo "SERVER NAME: ".$_SERVER['SERVER_NAME']."<br>";
//					echo "SERVER:<pre>"; print_r($_SERVER); echo "</pre>";

					//open connection
					$ch = curl_init();
					
					// set the url, number of POST vars, POST data
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_POST, count($xml_fields));
					curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_fields_string);
						
					//execute post
					$curl_result = curl_exec($ch);
						
					print_r($curl_result);
					
//					die("ESECUZIONE BATCH CON RECUPERO DI PARAMETRI DA FILE XML LEGATO ALL'ID DELL'E-MAIL");
						
					//close connection
					curl_close($ch);
				}
			}
*//*			
			if(!isset($batchContext) && get_launch_batch_email_atc_cond($ID)) {
				echo "<font color='orange'>INVIO_EMAIL_launch_batch_email_atc_action</font><br>";
				
				launch_batch_email_atc_action($ID, $params, $isBatch);
			}
			else {
*/				echo "<font color='orange'>ESECUZIONE NORMALE</font><br>";
		
				$invioConv = new wi400invioConvert($ID, $db, $connzend, false, $params);
//				$invioConv = new wi400invioConvert($ID, false, $params);
			
				$invioConv->set_dati_rec($row);
							
//				if(in_array($actionContext->getForm(), array("ESECUZIONE", "INOLTRO", "INVIO_MPX"))) {
					if($actionContext->getForm()=="INOLTRO") {
						echo "INOLTRO<br>";
						
						$msg = "INOLTRO RECORD $ID";
						wi400invioConvert::write_log($ID,'1','',$msg,$params['email_log_file'],"INOLTRO");
						
						$is_email = "S";
						$is_mpx = "N";
						
						$invioConv->setStampa("N");
						$invioConv->setArchiviazione("N");
					}
					
					if($is_email=="N" && $is_mpx=="N") {
						$actionContext->gotoAction("EMAIL_CONVERT", "CONVERTI_TUTTO", "", true);
					}
					
					if($is_email=="S") {
						echo "INVIO EMAIL<br>";
						
						$msg = "INVIO E-MAIL RECORD $ID";
						wi400invioConvert::write_log($ID,'1','',$msg,$params['email_log_file'],"E-MAIL");
						
						$email = $invioConv->invio_email();
						
						if($email===false) {
							$msg = "Errore durante l'invio del file via e-mail. (".$ID.")";
							wi400invioConvert::write_log($ID,'1','003',$msg,$params['email_log_file'],"E-MAIL");
							
							if($isBatch)
								die();
							else
								$messageContext->addMessage("ERROR", $msg);
						}
						else {
							$msg = "Invio del file via e-mail riuscita. (".$ID.")";
							wi400invioConvert::agg_log($ID,'1',$risped,'000',$msg,$params['email_log_file'],"E-MAIL",$db);
							
							$messageContext->addMessage("SUCCESS", $msg);
						}
						
						if($actionContext->getForm()=="INOLTRO") {
							echo "ELIMINA ELEMENTI INOLTRO<br>";
							
							$keyDel = array("ID");
							
							$stmt_del = $db->prepare("DELETE", "FPDFCONV", $keyDel, null);
							$res = $db->execute($stmt_del, array($ID));
							
							$stmt_del = $db->prepare("DELETE", "FEMAILAL", $keyDel, null);
							$res = $db->execute($stmt_del, array($ID));
							
							$stmt_del = $db->prepare("DELETE", "FEMAILCT", $keyDel, null);
							$res = $db->execute($stmt_del, array($ID));
							
							$stmt_del = $db->prepare("DELETE", "FEMAILDT", $keyDel, null);
							$res = $db->execute($stmt_del, array($ID));
							
							$file = wi400File::getUserFile('tmp', $ID."_BODY.txt");
//							echo "FILE: $file<br>";
						
							// Canellazione del file BODY temporaneo
							if(isset($file) && file_exists($file)) {
								unlink($file);
							}
							
							// @todo Come mai funziona il reindirizzamento ad un'altra azione anche se l'azione EMAIL_INVIO è di tipo BATCH?
//							$actionContext->onSuccess("CLOSE", "CLOSE_LOOKUP");
							$actionContext->onSuccess("CLOSE", "CLOSE_WINDOW_MSG");
							$actionContext->onError("EMAIL_INOLTRO", "DEFAULT", "", "", true);
						}
					}
						
					if($is_mpx=="S") {
						if($actionContext->getForm()=="INVIO_MPX") {
							echo "INVIO MPX<br>";
							
							$msg = "INVIO MPX RECORD $ID";
							wi400invioConvert::write_log($ID,'1','',$msg,$params['email_log_file'],"MPX");
							
						   /*
							* Invio di file PDF ad MPX
							*
							* I file XML generati ed accumulati per l'invio ad MPX vengono raggruppati in un unico file XML e cancellati,
							* mentre il file XML così ottenuto viene inviato ad MPX
							*/
							
							$mpx = $invioConv->invio_mpx($isBatch);
							
							if($mpx===false) {
								$msg = "Errore durante l'invio del file ad MPX. (".$ID.")";
								wi400invioConvert::write_log($ID,'1','004',$msg,$params['email_log_file'],"MPX");
								
								if($isBatch)
									die();
								else
									$messageContext->addMessage("ERROR", $msg);
							}
							else {
								$msg = "Invio del file ad MPX riuscita. (".$ID.")";
								wi400invioConvert::agg_log($ID,'1',$risped,'000',$msg,$params['email_log_file'],"MPX",$db);
									
								$messageContext->addMessage("SUCCESS", $msg);
							}
						}
						else {
							echo "CONVERSIONE MPX<br>";
							
							$msg = "CONVERSIONE MPX RECORD $ID";
							wi400invioConvert::write_log($ID,'1','',$msg,$params['email_log_file'],"MPX");
							
							$mpx_conv = $invioConv->mpx_conv();
							
							if($mpx_conv===false) {
								$msg = "Errore durante la conversione del file per MPX. (".$ID.")";
								wi400invioConvert::write_log($ID,'1','005',$msg,$params['email_log_file'],"MPX");
									
								if($isBatch)
									die();
								else
									$messageContext->addMessage("ERROR", $msg);
							}
							else {
								$msg = "Conversione del file per MPX riuscita. (".$ID.")";
								wi400invioConvert::agg_log($ID,'1',$risped,'000',$msg,$params['email_log_file'],"MPX",$db);
								
								$messageContext->addMessage("SUCCESS", $msg);
							}
						}
					}
					
					if($actionContext->getForm()=="INOLTRO") {
//						$actionContext->gotoAction("CLOSE", "CLOSE_WINDOW_MSG", "", true);
					}
//				}
//			}
		}
	}