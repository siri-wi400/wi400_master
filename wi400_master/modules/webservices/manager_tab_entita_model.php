<?php

	require_once 'manager_tab_entita_common.php';
	
	$azione = $actionContext->getAction();
	$form = $actionContext->getForm();
	
	if($form != "DEFAULT") {
		$key_entita = getListKeyArray($azione."_ENTITA_LIST");
	}
	
	if($form == "AJAX_CHECK_WSDL") {
		$url = base64_decode($_REQUEST['WSDL']);
		
		try {
			$params = array();
			$client = new SoapClient($url,$params);
			echo "Wsdl creato con successo!";
		} catch (SoapFault $exception) {
			echo "Errore wsdl";
		}

		die();
	}else if($form == "LIST_SOAP_ACTION") {
		$actionContext->setLabel("Lista SoapAction");
		
		subfileDelete("SOAP_ACTION");
		
		$subfile = new wi400Subfile($db, "SOAP_ACTION", $settings['db_temp'], 20);
		$subfile->setModulo("webservices");
		$subfile->setConfigFileName("SOAP_ACTION");
		$subfile->setSql("*AUTOBODY");
	}
	
	if($form=="DEFAULT") {
		$history->addCurrent();
	}else if($form=="NEW_REC") {
		//$keyArray = getListKeyArray($azione."_ENTITA_LIST");
		$actionContext->setLabel("Nuova Entità");
	}else if($form=="MOD_REC") {
		//$keyArray = getListKeyArray($azione."_ENTITA_LIST");
		$actionContext->setLabel("Modifica Entità ".$key_entita['AENCOD']);
	}
	else if(in_array($form, array("INS_REC", "UPDT_REC"))) {
		$date = date("dmy");
		$hour = date("His");
		
		//Reperisco i decimi di secondo dall'ora
		list($whole, $decimal) = explode('.', "".round(microtime(true), 3));
		$hour .= $decimal;
		
		$error_form = "NEW_REC";
		if($form == "UPDT_REC") {
			//$sql = "UPDATE phplib.faentita SET aendco='".$_REQUEST['AENDCO']."' WHERE aencod='".$_REQUEST['AENCOD']."'";
			//$res = $db->singleQuery($sql);
			$keyUpdt = array("AENCOD" => $key_entita[0]);
			
			$fieldsValue = array();
			$fieldsValue['AENDCO'] = $_REQUEST['AENDCO'];
			//Il $stato arriva dal validation
			$fieldsValue['AENSTA'] = $stato;
			$fieldsValue['AENDMO'] = $date;
			$fieldsValue['AENHMO'] = $hour;
			$fieldsValue['AENWHO'] = $_SESSION['user'];
			if($enable_fields) {
				$fieldsValue['AENLIB'] = $_REQUEST['AENLIB'];
				$fieldsValue['AENCDA'] = $_REQUEST['AENCDA'];
				$fieldsValue['AENRVE'] = $_REQUEST['AENRVE'];
				$fieldsValue['AENRSC'] = $_REQUEST['AENRSC'];
			}
			
			$stmt_updt = $db->prepare("UPDATE", "FAENTITA", $keyUpdt, array_keys($fieldsValue));

			$res = $db->execute($stmt_updt, $fieldsValue);
			
			if(!$res) {
				$messageContext->addMessage("ERROR","Errore durante la modifica dell'entità");
			}else {
				$messageContext->addMessage("SUCCESS","Modifica dell'entità eseguita con successo");
			}
			$error_form = "MOD_REC";
		}else {
			$key_entita = array($_REQUEST['AENCOD']);
			$sql = "INSERT INTO faentita (aencod, aendco, aensta, aendmo, aenhmo, aenwho";
			if($enable_fields) {
				$sql .= ", aenlib, aencda, aenrve, aenrsc) ";
				$value_hidden = ", '".$_REQUEST['AENLIB']."', '".$_REQUEST['AENCDA']."', '".$_REQUEST['AENRVE']."', '".$_REQUEST['AENRSC']."');";
			}else {
				$sql .= ") ";
				$value_hidden = ");";
			}
			$sql .= "values('".$key_entita[0]."', '".$_REQUEST['AENDCO']."', '$stato', '$date', '$hour', '".$_SESSION['user']."'$value_hidden";

			$res = $db->singleQuery($sql);
			
			if(!$res) {
				$messageContext->addMessage("ERROR","Errore durante l'aggiunta dell'entità");
			}else {
				$messageContext->addMessage("SUCCESS","Aggiunta dell'entità eseguita con successo");
			}
		}
		
		if($enable_scheda_parametri) {
			save_parametri($parametri_testata, $form == "UPDT_REC" ? true : false, $key_entita[0]);
			
			$path_folder = $settings['data_path']."common/wsinfo/";
			
			$query = "SELECT ASECOD FROM FASEGMEN WHERE ASEENT='{$key_entita[0]}'";
			$rs = $db->query($query);
			while($row = $db->fetch_array($rs)) {
				$file_ws = "WS_".$key_entita[0]."-".$row['ASECOD'].".txt";
				$path_file = $path_folder.$file_ws;
				if(file_exists($path_file)) {
					unlink($path_file);
				}
			}
		}
		
		$actionContext->onError($azione, $error_form, "", "", true);
		$actionContext->onSuccess("CLOSE", "CLOSE_WINDOW_MSG");
	}
	else if($form=="SEGMEN_LIST") {
		$history->addCurrent();
		$actionContext->setLabel("Segmenti per entità");
	}
	else if(in_array($form, array("NEW_SEGMEN", "MOD_SEGMEN"))) {
		if($form == "MOD_SEGMEN") {
			$key_segmento = getListKeyArray($azione."_SEGMEN_LIST");
			$actionContext->setLabel("Modifica Segmento - ".$key_segmento[0]);
		}else {
			$actionContext->setLabel("Nuovo Segmento");
			$key_segmento = array("nuovo");
		}
		
		subfileDelete("PARAMETRI_INPUT_OUTPUT");
		
		$subfile = new wi400Subfile($db, "PARAMETRI_INPUT_OUTPUT", $settings['db_temp'], 20);
		$subfile->setModulo("webservices");
		$subfile->setConfigFileName("PARAMETRI_INPUT_OUTPUT");
		$subfile->setSql("*AUTOBODY");
		
		$query = "SELECT * FROM FWSPINOU WHERE ASEENT='{$key_entita[0]}' and ASECOD='{$key_segmento[0]}'";
		
		//echo $query."<br>";
		
		$subfile->addParameter("QUERY", $query);
		$subfile->addParameter("MAX_ROWS", 20);
	}
	else if(in_array($form, array("INS_SEGMEN", "UPDT_SEGMEN"))) {
		$key_segmento = getListKeyArray($azione."_SEGMEN_LIST");
		
		$date = date("dmy");
		$hour = date("His");
	
		//Reperisco i decimi di secondo dall'ora
		list($whole, $decimal) = explode('.', "".round(microtime(true), 3));
		$hour .= $decimal;
	
		$error_form = "NEW_SEGMEN";
		
		//showArray($_REQUEST);
		if($form=="UPDT_SEGMEN") {
			$keyUpdt = array("ASEENT" => $key_entita[0],
							"ASECOD" => $key_segmento[0]);
				
			$fieldsValue = array();
			$fieldsValue['ASEDCO'] = $_REQUEST['ASEDCO'];
			//Il $stato arriva dal validation
			$fieldsValue['ASESTA'] = $stato;
			$fieldsValue['ASEAUT'] = $autentic; // $autentic dal validation
			$fieldsValue['ASEDMO'] = $date;
			$fieldsValue['ASEHMO'] = $hour;
			$fieldsValue['ASEWHO'] = $_SESSION['user'];
			$fieldsValue['ASEPRM'] = $_REQUEST['ASEPRM'];
			$fieldsValue['ASENUK'] = intval($_REQUEST['ASENUK']);
			$fieldsValue['ASEKEY'] = $_REQUEST['ASEKEY'];
			$fieldsValue['ASESTK'] = $_REQUEST['ASESTK'];
			$fieldsValue['ASEPHP'] = $_REQUEST['ASEPHP'];
			if($enable_fields) {
				$fieldsValue['ASERCD'] = $_REQUEST['ASERCD'];
				if(isset($_REQUEST['ROUTINE_PHP'])) {
					$fieldsValue['ASERIN'] = "*RUNPHP";
				}else {
					$fieldsValue['ASERIN'] = $_REQUEST['ASERIN'];
				}
				$fieldsValue['ASERDE'] = $_REQUEST['ASERDE'];
				if(isset($_REQUEST['ROUTINE_SCRITTURA_PHP'])) {
					$fieldsValue['ASERSD'] = "*RUNPHP";
				}else {
					$fieldsValue['ASERSD'] = $_REQUEST['ASERSD'];
				}
			}
				
			$stmt_updt = $db->prepare("UPDATE", "FASEGMEN", $keyUpdt, array_keys($fieldsValue));
	
			$res = $db->execute($stmt_updt, $fieldsValue);
				
			if(!$res) {
				$messageContext->addMessage("ERROR","Errore durante la modifica dell'entità");
			}else {
				$messageContext->addMessage("SUCCESS","Modifica dell'entità eseguita con successo");
			}
			$error_form = "MOD_SEGMEN";
		}else {
			/*$sql = "INSERT INTO fasegmen (aseent, asecod, asedco, asesta, asedmo, asehmo, asewho, aseprm)
				values('".$_REQUEST['ASEENT']."', '".$_REQUEST['ASECOD']."', '".$_REQUEST['ASEDCO']."', 
						'$stato', '$date', '$hour', '".$_SESSION['user']."', '".$_REQUEST['ASEPRM']."')";
			$res = $db->singleQuery($sql);*/
			$key_segmento = array($_REQUEST['ASECOD']);
			
			$values = array();
			$values['ASEENT'] = $key_entita[0];
			$values['ASECOD'] = $key_segmento[0];
			$values['ASEDCO'] = $_REQUEST['ASEDCO'];
			$values['ASESTA'] = $stato;
			$values['ASEAUT'] = $autentic; // $autentic dal validation
			$values['ASEDMO'] = $date;
			$values['ASEHMO'] = $hour;
			$values['ASEWHO'] = $_SESSION['user'];
			$values['ASEPRM'] = $_REQUEST['ASEPRM'];
			$values['ASENUK'] = intval($_REQUEST['ASENUK']);
			$values['ASEKEY'] = $_REQUEST['ASEKEY'];
			$values['ASESTK'] = $_REQUEST['ASESTK'];
			$values['ASEPHP'] = $_REQUEST['ASEPHP'];
				$values['ASERCD'] = $_REQUEST['ASERCD'];
				if(isset($_REQUEST['ROUTINE_PHP'])) {
					$values['ASERIN'] = "*RUNPHP";
				}else {
					$values['ASERIN'] = $_REQUEST['ASERIN'];
				}
				$values['ASERDE'] = $_REQUEST['ASERDE'];
				if(isset($_REQUEST['ROUTINE_SCRITTURA_PHP'])) {
					$fieldsValue['ASERSD'] = "*RUNPHP";
				}else {
					$fieldsValue['ASERSD'] = $_REQUEST['ASERSD'];
				}

			$stmtDoc = $db->prepare("INSERT", "FASEGMEN", null, array_keys($values));
			$res = $db->execute($stmtDoc, $values);
				
			if(!$res) {
				$messageContext->addMessage("ERROR","Errore durante l'aggiunta dell'entità");
			}else {
				$messageContext->addMessage("SUCCESS","Aggiunta dell'entità eseguita con successo");
			}
		}
		
		if($res) {
			$files = scandir($settings['data_path' ]."/common/serialize");
			foreach ($files as $valore) {
				$pos = strpos($valore, "FASEGMEN");
				if($pos != "") {
					unlink($settings['data_path']."common/serialize/$valore");
				}
			}
		}
		
		if($enable_scheda_parametri) {
			save_parametri($parametri_dettaglio, $form == "UPDT_SEGMEN" ? true : false, $key_entita[0], $key_segmento[0]);
			
			$wi400List = getList($azione."_IO_PARAM");
			$subfile_name = $wi400List->getSubfile();
			$subfile = wi400Session::load(wi400Session::$_TYPE_SUBFILE, $subfile_name);
			$subfile_table = $subfile->getTable();
			
			$file = "FWSPINOU";
			$sql = "DELETE FROM $file WHERE ASEENT='{$key_entita[0]}' AND ASECOD='{$key_segmento[0]}'";
			$rs = $db->query($sql);
			
			$sql = "SELECT * FROM $subfile_table where IS_MODIFY='X'";
			$rs = $db->query($sql);
			$fields = getDs($file);
			
			$fields['ASEENT'] = $key_entita[0];
			$fields['ASECOD'] = $key_segmento[0];
			$stmt_insert_row = $db->prepare("INSERT", $file, null, array_keys($fields));
			
			while($row = $db->fetch_array($rs)) {
				$fields['ASESEQ'] = intval($row['ASESEQ']);
				//$fields['ASEMET'] = "q";
				$fields['ASETYP'] = $row['ASETYP'];
				$fields['ASENAM'] = $row['ASENAM'];
				$fields['ASENA2'] = $row['ASENA2'];
				$fields['ASEDES'] = $row['ASEDES'];
				$fields['ASEORI'] = $row['ASEORI'];
				$fields['ASEGET'] = $row['ASEGET'];
				$fields['ASEDFT'] = $row['ASEDFT'];
				
				$res = $db->execute($stmt_insert_row, $fields);
				if(!$res) {
					$messageContext->addMessage("ERROR","Errore aggiunta I/O ".$row['ASENAM']);
				}/*else {
					echo "inserito ".$row['NREL']."___".$row['ASENAM']."<BR/>";
				}*/
			}
			
			$file_ws = "WS_".$key_entita[0]."-".$key_segmento[0].".txt";
			$path_file = $settings['data_path']."common/wsinfo/".$file_ws;
			if(file_exists($path_file)) {
				unlink($path_file);
			}
		}

		$actionContext->onError($azione, $error_form, "", "", true);
		$actionContext->onSuccess("CLOSE", "CLOSE_WINDOW_MSG");
	}else if($form == "PARAMETRI_ENTITA") {
		$actionContext->setLabel("Parametri entita - ".$key_entita[0]);
		$sql = "SELECT * FROM FWSDPARM WHERE ASEENT='{$key_entita[0]}' AND ASECOD=''";
		$rs = $db->query($sql);
	}