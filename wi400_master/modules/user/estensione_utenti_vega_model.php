<?php

	require_once 'estensione_utenti_vega_common.php';
	
	$azione = $actionContext->getAction();
	//echo "AZIONE: $azione - FORM: ".$actionContext->getForm()."<br>";
	
	// Utente
	if($wi400GO->getObject('FORM_USER') != 'COPIA') {
		$codUsr = wi400Detail::getDetailValue("USER_SEARCH", "codusr");
	}
	else {
		$codUsr = wi400Detail::getDetailValue("COPY_USER", "codusr1");
	}
	//echo "USER: $codUsr<br>";
	
//	echo "REQUEST:<pre>"; print_r($_REQUEST); echo "</pre>";
//	echo "request:<pre>"; print_r($request); echo "</pre>";

	//echo "TRIGGER_PARAM:<pre>"; print_r($trigger_param); echo "</pre>";
	
	if(isset($trigger_param['form']) && !empty($trigger_param['form'])) {
		$actionContext->setForm($trigger_param['form']);
	}
	//echo "FORM: ".$actionContext->getForm()."<br>";
	
	if ($actionContext->getForm() == "SAVE"){
		$codUsr = wi400Detail::getDetailValue("USER_DETAIL","codusr");
//		$codUsr = $_REQUEST['CODUSR'];
//		echo "USER: $codUsr<br>";
				
		$dati = wi400Detail::getDetailValues("USER_DETAIL");
//		$dati = $_REQUEST['USER_DETAIL'];
//		echo "USER_DETAIL VALUES: "; showArray($dati);
		
		$soc_pr = $dati['SOCPRP'];
		$tipo_user = $dati['TIPOUSR'];
		$user_pin = $dati['USRPIN'];
		$agente  = $dati['AGENTE'];
		$interlocutore  = $dati['INTERLOCUTORE'];
		if(empty($user_pin))
			$user_pin = 0;
		
		$error = false;
		$desc_error = array();
		if ($agente!="" && $soc_pr!="") {
			$desc_error[] = "Società proprietaria e agente sono in alternativa";
			$error = true;
		}
		if($tipo_user=="AAM" && empty($user_pin)) {
			$desc_error[] = "Inserire il PIN per Utente ANCHE Amministrativo";
			$error = true;
		}
		else if($tipo_user!="AAM" && !empty($user_pin)) {
			$desc_error[] = "Inserire il PIN solo per Utente ANCHE Amministrativo";
			$error = true;
		}
		
		if(!$error) {
		
			$sql_ext = "SELECT * FROM $tipo_tab_ext WHERE USER_NAME=?";
			$stmt_ext = $db->singlePrepare($sql_ext,0,true);
			
			$result_ext = $db->execute($stmt_ext,array($codUsr));
			
			if($row_ext = $db->fetch_array($stmt_ext)) {
				echo "UPDATE<br>";
	
				$keyUpdt = array("USER_NAME" => $codUsr);
				$fieldsUpdt = array(
					"SOCPRP" => $soc_pr, 
					"TIPOUSR" => $tipo_user, 
					"USRPIN" => $user_pin,
					"AGESPE" => $agente,
					"INTERLOCUTORE" => $interlocutore
				);
				
				//echo "CAMPI_UPDY:<pre>"; print_r($fieldsUpdt); echo "</pre>";
				//die();
				$stmt_updt  = $db->prepare("UPDATE", $tipo_tab_ext, $keyUpdt, array_keys($fieldsUpdt));
				
				$result = $db->execute($stmt_updt, $fieldsUpdt);
				
			}
			else {
				echo "INSERT<br>";
	
				$fieldsIns = array(
					"USER_NAME" => $codUsr,
					"SOCPRP" => $soc_pr,
					"TIPOUSR" => $tipo_user,
					"USRPIN" => $user_pin,
					"AGESPE" => $agente,
					"INTERLOCUTORE" => $interlocutore
				);
					
				$stmt_ins  = $db->prepare("INSERT", $tipo_tab_ext, null, array_keys($fieldsIns));
				
	//			echo "CAMPI_INS:<pre>"; print_r($fieldsIns); echo "</pre>";
					
				$result = $db->execute($stmt_ins, $fieldsIns);
			}
			
			if(!$result) {
				$desc_error[] = "Errore durante il salvataggio delle Estensioni dell'utente";
			}
		}
		
		$return_result = array('error' => $desc_error);
		if(empty($desc_error)) {
			$messageContext->addMessage("SUCCESS","Salvataggio delle Estensioni dell'utente eseguita con successo");
		}else {
			foreach($desc_error as $err) {
				$messageContext->addMessage("ERROR", $err);
			}
		}
//		echo json_encode($return_result);
		
		//$actionContext->onSuccess("EPRO","UPDATE"); 
		//$actionContext->onError("EPRO","DETAIL","","",true);
//		die();
	}
	else if($actionContext->getForm()=="DELETE") {
		echo "DELETE<br>";
		
		// Eliminazine
		$sql = "DELETE FROM $tipo_tab_ext WHERE USER_NAME='".$codUsr."'";
		$result = $db->query($sql);
		if ($result)
			$messageContext->addMessage("SUCCESS", "Cancellazione del record utente ".$codUsr." eseguita");
		else
			$messageContext->addMessage("ERROR", "Il record dell'utente ".$codUsr." non è stato cancellato");
	}
	else if($actionContext->getForm()=="GENERA_PIN") {
		$user_pin = sprintf("%04s", rand(0, 9999));
//		echo "RANDOM_PIN: $user_pin<br>";
		
		die($user_pin);
	}