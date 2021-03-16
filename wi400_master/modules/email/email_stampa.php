<?php

	require_once 'email_common.php';
	
	echo "ACTION: ".$actionContext->getAction()."<br>";
	echo "FORM: ".$actionContext->getForm()."<br>";
	
	if(in_array($actionContext->getForm(), array("STAMPA_TUTTO", "STAMPA_SEL"))) {
		echo "STAMPA<br>";
		
//		$ID_array = get_id_array($actionContext->getForm());
		
		foreach($ID_array as $ID) {
			echo "ID: $ID<br>";
				
			$is_email = "N";
			$is_mpx = "N";
			$risped = 0;
						
			// Recupero dei dati del record
			$sql = "select * from FPDFCONV where ID='$ID'";
			$res = $db->singleQuery($sql);
			if($row = $db->fetch_array($res)) {
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
			
			$invioConv = new wi400invioConvert($ID, $db, $connzend, false, $params);
//			$invioConv = new wi400invioConvert($ID, false, $params);
				
			$invioConv->set_dati_rec($row);
			
			$msg = "STAMPA RECORD $ID";
			wi400invioConvert::write_log($ID,'1','',$msg,$params['email_log_file'],"STMP");
			
			$invioConv->setStampa("S");
			
			$outq = wi400Detail::getDetailValue('EMAIL_STAMPA_SEL_STAMPA_SEL_DET','OUTQ');
//			echo "OUTQ: $outq<br>";
			
			$duplex = wi400Detail::getDetailValue('EMAIL_STAMPA_SEL_STAMPA_SEL_DET','DUPLEX_SEL');
			
			$atc_key_array = array();
			
			if($actionContext->getForm()=="STAMPA_SEL") {
				$idList_atc = "MONITOR_EMAIL_ATC_LIST";
				echo "ATC IDLIST: $idList_atc<br>";
					
				$wi400List_atc = wi400Session::load(wi400Session::$_TYPE_LIST, $idList_atc);
			
				foreach($wi400List_atc->getSelectionArray() as $key => $value){
					$atc_key_array[] = $key;
				}
				echo "ATC KEY ARRAY:<pre>"; print_r($atc_key_array); echo "</pre>";
			}
			
			$stampa = $invioConv->stampa($outq, $duplex, $atc_key_array);
				
			if($stampa===false) {
				$msg = "Errore durante la stampa del file. (".$ID.")";
				wi400invioConvert::write_log($ID,'1',$risped,'002',$msg,$params['email_log_file'],"STMP");
			
				if($isBatch)
					die();
				else
					$messageContext->addMessage("ERROR", $msg);
			}
			else {
				$msg = "Stampa del file riuscita. (".$ID.")";
				if($actionContext->getForm()=="STAMPA_TUTTO")
					wi400invioConvert::agg_log($ID,'1',$risped,'000',$msg,$params['email_log_file'],"STMP",$db);
				else if($actionContext->getForm()=="STAMPA_SEL")
					wi400invioConvert::write_log($ID,'1','000',$msg,$params['email_log_file'],"STMP");
													
				$messageContext->addMessage("SUCCESS", $msg);
			}
		}
	}