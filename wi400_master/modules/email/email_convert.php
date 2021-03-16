<?php

	require_once 'email_common.php';
	
	echo "ACTION: ".$actionContext->getAction()."<br>";
	echo "FORM: ".$actionContext->getForm()."<br>";
	
	$steps = $history->getSteps();
	echo "STEPS:<pre>"; print_r($steps); echo "</pre>";
	
	$array_steps = get_history_steps(1, $steps);
	
	$first_action = $array_steps['FIRST_ACTION'];
	$first_form = $array_steps['FIRST_FORM'];
	
	$last_action = $array_steps['LAST_ACTION'];
	$last_form = $array_steps['LAST_FORM'];
	echo "LAST_ACTION: $last_action - LAST FORM: $last_form<br>";
	
	// Recupero dei dati dell'e-mail
	$sql = "select * from FPDFCONV where ID=?";
	$stmt = $db->singlePrepare($sql, 0, true);
	
	if($actionContext->getForm()=="CONVERTI_TUTTO") {
		echo "CONVERTI TUTTO<br>";
		
//		$ID_array = get_id_array($actionContext->getForm());
		
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
/*			
			if(get_launch_batch_email_atc_cond($ID)) {
				launch_batch_email_atc_action($ID, $params, $isBatch);
			}
			else {
//die("ESECUZIONE NORMALE<br>");
*/			
				$invioConv = new wi400invioConvert($ID, $db, $connzend, false, $params);
//				$invioConv = new wi400invioConvert($ID, false, $params);
					
				$invioConv->set_dati_rec($row);
				
				$msg = "CONVERSIONE RECORD $ID";
				wi400invioConvert::write_log($ID,'1','',$msg,$params['email_log_file'],"CONV");
				
				$invioConv->setStampa("N");
				
				$file_conv = $invioConv->convert();
					
				if($file_conv===false) {
					$msg = "Errore durante la conversione del file. (".$ID.")";
					wi400invioConvert::write_log($ID,'1','001',$msg,$params['email_log_file'],"CONV");
						
					if($isBatch)
						die();
					else
						$messageContext->addMessage("ERROR", $msg);
				}
				else {
					$msg = "Conversione del file riuscita. (".$ID.")";
					wi400invioConvert::agg_log($ID,'1',$risped,'000',$msg,$params['email_log_file'],"CONV",$db);
						
					$messageContext->addMessage("SUCCESS", $msg);
				}			
//			}			
		}
	}
	else if($actionContext->getForm()=="CONVERTI_SEL") {
		echo "CONVERTI ALLEGATI SELEZIONATI<br>";
		
		$idList = "MONITOR_EMAIL_ATC_LIST";
		echo "IDLIST: $idList<br>";
		
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
		
		$atc_array = array();
		foreach($wi400List->getSelectionArray() as $key => $value){
			$keyArray = explode("|",$key);
//			echo "KEY ARRAY:<pre>"; print_r($keyArray); echo "</pre>";
/*		
			$ID = $keyArray[1];
			$atc = $keyArray[2];
*/
			$keyArray = get_list_keys_num_to_campi($wi400List, $keyArray);
//			echo "KEY ARRAY:<pre>"; print_r($keyArray); echo "</pre>";
			
			$ID = $keyArray["ID"];
			$atc = $keyArray["MAIATC"];
//			echo "ID: $ID<br>";
			
			$atc_array[$ID] = $atc;
		}
	
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
/*			
			if(get_launch_batch_email_atc_cond($ID)) {
				launch_batch_email_atc_action($ID, $params, $isBatch);
			}
			else {
				//die("ESECUZIONE NORMALE<br>");
*/			
				$invioConv = new wi400invioConvert($ID, $db, $connzend, false, $params);
//				$invioConv = new wi400invioConvert($ID, false, $params);
			
				$invioConv->set_dati_rec($row);
					
				foreach($atc_array as $ID => $atc) {
					$msg = "CONVERSIONE RECORD $ID";
					wi400invioConvert::write_log($ID,'1','',$msg,$params['email_log_file'],"CONV");
			
					$invioConv->setStampa("N");
			
					$file_conv = $invioConv->convert($atc);
			
					if($file_conv===false) {
						$msg = "Errore durante la conversione del file. (".$ID.")";
						wi400invioConvert::write_log($ID,'1','001',$msg,$params['email_log_file'],"CONV");
			
						if($isBatch)
							die();
						else
							$messageContext->addMessage("ERROR", $msg);
					}
					else {
						$msg = "Conversione del file riuscita. (".$ID.")";
//						wi400invioConvert::agg_log($ID,'1',$risped,'000',$msg,$params['email_log_file'],"CONV",$db);
						wi400invioConvert::write_log($ID,'1','000',$msg,$params['email_log_file'],"CONV");
							
						$messageContext->addMessage("SUCCESS", $msg);
					}
				}
//			}
		}
		else {
			$msg = "Record ID: $ID non trovato nella tabella FPDFCONV";
//			wi400invioConvert::agg_log($ID,'1',0,'006',$msg,$params['email_log_file'],"EXEC",$db);
			wi400invioConvert::write_log($ID,'1','006',$msg,$params['email_log_file'],"CONV");
	
			if($isBatch)
				die();
			else
				$messageContext->addMessage("ERROR", $msg);
		}	
	}
	
	$actionContext->gotoAction($last_action, $last_form, "", true);