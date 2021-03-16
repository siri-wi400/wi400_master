<?php

//	echo "FORM: ".$actionContext->getForm()."<br>"; die();
//	echo "POST:<pre>"; print_r($_POST); echo "</pre>"; die();

	if($actionContext->getForm()=="DEFAULT") {
		validation_default();
	}
	else if($actionContext->getForm()=="NEW_EMAIL_DET") {
		validation_new_email_det();		
	}
	else if(in_array($actionContext->getForm(), array("ATC_DET", "NEW_ATC_DET"))) {
		validation_atc_det();
	}
	else if(in_array($actionContext->getForm(), array("DEST_DET", "NEW_DEST_DET"))) {
		validation_dest_det();
	}
	
	function validation_default() {
		global $messageContext;
		
		$c = 0;
		
		if(isset($_POST["DATA_INV_INI"]) && trim($_POST["DATA_INV_INI"])!="")
			$data_inv_ini = $_POST['DATA_INV_INI'];
		if(isset($_POST["DATA_INV_FIN"]) && trim($_POST["DATA_INV_FIN"])!="")
			$data_inv_fin = $_POST['DATA_INV_FIN'];
		
		if(isset($_POST["DATA_RIS_INI"]) && trim($_POST["DATA_RIS_INI"])!="")
			$data_ris_ini = $_POST['DATA_RIS_INI'];
		if(isset($_POST["DATA_RIS_FIN"]) && trim($_POST["DATA_RIS_FIN"])!="")
			$data_ris_fin = $_POST['DATA_RIS_FIN'];
		
		if(isset($_POST['ID_SRC']) && !empty($_POST['ID_SRC'])) {
			$id_array = $_POST['ID_SRC'];
				
			foreach($id_array as $id) {
				if(strlen($id)<10) {
					$messageContext->addMessage("ERROR", "La lunghezza degli ID deve essere di 10 caratteri.", "ID_SRC",true);
					break;
				}
			}
				
			$c++;
		}
		
		if(isset($_POST["ID_INI"]) && trim($_POST["ID_INI"])!="")
			$id_ini = $_POST['ID_INI'];
		if(isset($_POST["ID_FIN"]) && trim($_POST["ID_FIN"])!="")
			$id_fin = $_POST['ID_FIN'];
		
		if(isset($data_inv_ini) && isset($data_inv_fin)){
			$check = check_periodo($data_inv_ini, $data_inv_fin);
		
			if($check===false)
				$messageContext->addMessage("ERROR", "La data di INIZIO deve essere precedente a quella di FINE", "DATA_INV_INI",true);
				
			$c++;
		}
		else if(isset($data_inv_ini) && !isset($data_inv_fin)){
			$messageContext->addMessage("ERROR", "Manca la data di fine", "DATA_INV_FIN",true);
				
			$c++;
		}
		else if(!isset($data_inv_ini) && isset($data_inv_fin)){
			$messageContext->addMessage("ERROR", "Manca la data di inizio", "DATA_INV_INI",true);
				
			$c++;
		}
		
		if(isset($data_ris_ini) && isset($data_ris_fin)){
			$check = check_periodo($data_ris_ini, $data_ris_fin);
		
			if($check===false)
				$messageContext->addMessage("ERROR", "La data di INIZIO deve essere precedente a quella di FINE.", "DATA_RIS_INI",true);
				
			$c++;
		}
		else if(isset($data_ris_ini) && !isset($data_ris_fin)){
			$messageContext->addMessage("ERROR", "Manca la data di fine", "DATA_RIS_FIN",true);
				
			$c++;
		}
		else if(!isset($data_ris_ini) && isset($data_ris_fin)){
			$messageContext->addMessage("ERROR", "Manca la data di inizio", "DATA_RIS_INI",true);
				
			$c++;
		}
		
		if(isset($id_ini) && isset($id_fin)) {
			$num_id_ini = substr($id_ini, 1);
			$num_id_fin = substr($id_fin, 1);
		
			if($num_id_ini>$num_id_fin) {
				$messageContext->addMessage("ERROR", "L'ID di INIZIO deve essere precedente a quello di FINE.", "ID_INI",true);
			}
				
			$cod_id_ini = substr($id_ini, 0, 1);
			$cod_id_fin = substr($id_fin, 0, 1);
			if(is_numeric($cod_id_ini)) {
				$messageContext->addMessage("ERROR", "Il carattere iniziale dell'ID di INIZIO deve essere una lettera.", "ID_INI",true);
			}
			if(is_numeric($cod_id_fin)) {
				$messageContext->addMessage("ERROR", "Il carattere iniziale dell'ID di FINE deve essere una lettera.", "ID_FIN",true);
			}
			if($cod_id_ini!=$cod_id_fin) {
				$messageContext->addMessage("ERROR", "Il carattere iniziale dell'ID di INIZIO e di quello di FINE devono corrispondere.", "ID_INI",true);
			}
				
			if(strlen($id_ini)<10)
				$messageContext->addMessage("ERROR", "La lunghezza dell'ID di INIZIO deve essere di 10 caratteri.", "ID_INI",true);
			if(strlen($id_fin)<10)
				$messageContext->addMessage("ERROR", "La lunghezza dell'ID di FINE deve essere di 10 caratteri.", "ID_FIN",true);
				
			$c++;
		}
		else if(isset($id_ini) && !isset($id_fin)){
			$messageContext->addMessage("ERROR", "Manca l'ID di fine", "ID_FIN",true);
				
			$c++;
		}
		else if(!isset($id_ini) && isset($id_fin)){
			$messageContext->addMessage("ERROR", "Manca l'ID di inizio", "IS_INI",true);
				
			$c++;
		}
		
		if($c>1) {
			$messageContext->addMessage("ERROR", "Filtrare per DATA INVIO o DATA RISPEDIZIONE o ID alternativamente", "",true);
		}
	}
	
	function validation_new_email_det() {
		global $messageContext, $db;
		
		if(isset($_POST['ID']) && trim($_POST['ID'])!="") {
			$id = $_POST['ID'];
				
			if(strlen($id)<10)
				$messageContext->addMessage("ERROR", "L'ID deve essere lungo 10", "ID",true);
				
			$cod_id = substr($id, 0, 1);
			$num_id = substr($id, 1);
				
			if(is_numeric($cod_id))
				$messageContext->addMessage("ERROR", "Il primo carattere dell'ID deve essere una lettera", "ID",true);
				
			if(!is_numeric($num_id))
				$messageContext->addMessage("ERROR", "Gli ultimi 9 caratteri dell'ID devono essere numerici", "ID",true);
				
			$sql = "select * from FPDFCONV where ID='$id'";
			$result = $db->singleQuery($sql);
			if($row = $db->fetch_array($result)) {
				$messageContext->addMessage("ERROR", "L'ID $id esiste già", "ID",true);
			}
		}
	}
	
	function validation_atc_det() {
		global $messageContext, $db, $actionContext;
		
		if(isset($_POST['ID']) && trim($_POST['ID'])!="")
			$id = $_POST['ID'];
		
		if(isset($_POST['MAIATC']) && trim($_POST['MAIATC'])!="")
			$atc = $_POST['MAIATC'];
//		echo "ATC: $atc<br>";
		
		$tipo_conv = "";
		if(isset($_POST['TPCONV']) && trim($_POST['TPCONV'])!="")
			$tipo_conv = $_POST['TPCONV'];
		
		if($actionContext->getForm()=="ATC_DET") {
			$keyArray = array();
			$keyArray = getListKeyArray("MONITOR_EMAIL_ATC_LIST");
//			echo "KEY ARRAY:<pre>"; print_r($keyArray); echo "</pre>";
				
			$id_orig = $keyArray['ID'];
			$atc_orig = $keyArray['MAIATC'];
		}
		
		$sql = "select * from FEMAILAL where ID='$id' and MAIATC='$atc'";
		$result = $db->singleQuery($sql);
		if($row = $db->fetch_array($result)) {
			if($actionContext->getForm()=="ATC_DET") {
				if($atc!=$atc_orig)
					$messageContext->addMessage("ERROR", "Esiste già l'allegato $atc", "",true);
			}
			else {
				$messageContext->addMessage("ERROR", "Esiste già l'allegato $atc", "",true);
			}
		}
		
		if($tipo_conv=="BODY") {
			$sql = "select * from FEMAILAL where ID='$id' and TPCONV='BODY'";
			$result = $db->singleQuery($sql);
			if($row = $db->fetch_array($result)) {
//				echo "ROW ATC:<pre>"; print_r($row); echo "</pre>";
				if($actionContext->getForm()=="ATC_DET") {
					if($atc_orig!=$row['MAIATC'])
						$messageContext->addMessage("ERROR", "Esiste già un allegato di tipo BODY", "",true);
				}
				else {
					$messageContext->addMessage("ERROR", "Esiste già un allegato di tipo BODY", "",true);
				}
			}
		}		
	}
	
	function validation_dest_det() {
		global $messageContext, $db, $actionContext;
		
		if(isset($_POST['ID']) && trim($_POST['ID'])!="")
			$id = $_POST['ID'];
		
		if(isset($_POST['MAITOR']) && trim($_POST['MAITOR'])!="")
			$to = $_POST['MAITOR'];
		
		$sql = "select * from FEMAILDT where ID='$id' and MAITOR='$to'";
		$result = $db->singleQuery($sql);
		if($row = $db->fetch_array($result)) {
			if($actionContext->getForm()=="DEST_DET") {
				$keyArray = array();
				$keyArray = getListKeyArray("MONITOR_EMAIL_DEST_LIST");
//				echo "KEY ARRAY:<pre>"; print_r($keyArray); echo "</pre>";
				
				$id_orig = $keyArray['ID'];
				$to_orig = $keyArray['TO'];
				
				if($to!=$to_orig)
					$messageContext->addMessage("ERROR", "Esiste già il destinatario $to", "",true);
			}
			else {
				$messageContext->addMessage("ERROR", "Esiste già il destinatario $to", "",true);
			}
		}
	}