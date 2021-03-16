<?php

	require_once 'manager_abilitazioni_common.php';

	$azione = $actionContext->getAction();
//	echo "AZIONE: $azione - FORM: ".$actionContext->getForm()."<br>";
	
	// Aggiunta dell'azione corrente nella history
	if(in_array($actionContext->getForm(),array("DEFAULT")))
		$history->addCurrent();
	
//	echo "GET:<pre>"; print_r($_GET); echo "</pre>";
	
	if($actionContext->getForm()=="CHECK_ALL") {
		$_SESSION['UPDATE_STATUS']='ON';
		
		$checkAction = $_GET["VALUE"];
		$colKey = $_GET["COL"];
		$idList = $_GET['IDLIST'];
		$miaLista = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
//		echo "CHECK ACTION: $checkAction - COL KEY: $colKey<br>";
		
		$sql = "select * from $tabella";
		$stmt = $db->prepareStatement($sql,0,false);
		$res_sel = $db->execute($stmt);
		
		$set_cols = array();
		foreach($abil_cols as $val) {
			$set_cols[$val] = "N";
		}

		$sel_rows = $miaLista->getSelectionArray();
//		echo "<br>SEL ROWS:<pre>"; print_r($sel_rows); echo "</pre>";
		
		while($row = $db->fetch_array($stmt)) {
			$key_rif = $row['USRUSR'];
//			echo "KEY RIF: $key_rif<br>";
			
			$abilitazioni = $set_cols;
			
//			echo "<br>SEL ROW:<pre>"; print_r($sel_rows[$key_rif]); echo "</pre>";
			
			if(!empty($sel_rows) && array_key_exists($key_rif,$sel_rows)) {
				foreach($abilitazioni as $k => $v) {
					if($sel_rows[$key_rif][$k]=="S")
						$abilitazioni[$k] = "S";
				}
			}
			else {
				foreach($abilitazioni as $k => $v) {
					$abilitazioni[$k] = $row[$k];
				}
			}
			
			if ($checkAction == 0) {
				$abilitazioni[$colKey] = "N";
			}
			else {
				$abilitazioni[$colKey] = "S";
			}
			
//			$abilitazioni[$colKey] = $checkAction;
//			echo "ABILITAZIONI:<pre>"; print_r($abilitazioni); echo "</pre>";
			
			$miaLista->setSelectionKey($key_rif, $abilitazioni);			
		}
		
		if ($checkAction == 0) { 
			$checkAction = 1; 
		} 
		else { 
			$checkAction = 0; 
		}
//		echo "CHECK ACTION: $checkAction<br>";

		$miaLista->setHeaderValue($colKey, $checkAction);
		
		wi400Session::save(wi400Session::$_TYPE_LIST, $idList, $miaLista);
		die();
	}
		
	if($actionContext->getForm()=="DEFAULT") {
		wi400Detail::cleanSession($azione."_ADD_USER_DET");
		wi400Detail::cleanSession($azione."_DUPLICA_USER_DET");
	}
	
	if($actionContext->getForm()=="SALVA_SPUNTE") {
		$idList = $_GET['IDLIST'];
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
		$sql = "SELECT * FROM $tabella WHERE USRUSR=?";
		$stmt = $db->singlePrepare($sql);
		foreach($wi400List->getSelectionArray() as $key => $value){
			$keyArray = explode("|",$key);
		    $user = $keyArray[0];
//		    echo "UTENTE: $user<br>";

			$db->execute($stmt, array($user));
			$myRow = $db->fetch_array($stmt);

			if ($myRow) {
//				echo "UPDATE<br>";
//				echo "ROW:<pre>"; print_r($myRow); echo "</pre>";
			   	// Aggiorno stato testata buono fornitore
				$keyName = array("USRUSR"=>'?');
				if (!isset($stmtUpdate)) {
					$stmtUpdate = $db->prepare("UPDATE", $tabella, $keyName, $abil_cols);
				}
				
				$values = array();
				foreach($abil_cols as $val) {
					$abil  = notNull($value[$val], $myRow[$val]);
					
					$values[$val] = $abil;
				}
				
				$values['USRUSR'] = $user;
				
//				echo "VALUES:<pre>"; print_r($values); echo "</pre>";

				$result = $db->execute($stmtUpdate, $values);
			}
			else {
//				echo "INSERT<br>";
			    if (!isset($stmtInsert)) {
					$fieldSpunte = getDS($tabella);
					$stmtInsert = $db->prepare("INSERT", $tabella, null, array_keys($fieldSpunte));
			    }
				
			    // Scrittura Documenti
				$fieldSpunte['USRSUR']= $user;
				
				foreach($abil_cols as $val) {
					$fieldSpunte[$val]= notNull($value[$val]);
				}
//				echo "VALUES:<pre>"; print_r($fieldSpunte); echo "</pre>";
					
				$result = $db->execute($stmtInsert, $fieldSpunte);
			}
		}
//die();	
		$messageContext->addMessage("SUCCESS", "Operazione effettuata con successo, le spunte sono state salvate e la lista è stata ricaricata");
		
	    $actionContext->onSuccess($azione, "DEFAULT");
	}
	else if($actionContext->getForm()=="ADD_USER") {
		$actionContext->setLabel("Aggiunta utente");
	}
	else if($actionContext->getForm()=="SAVE_USER") {
		$fieldsValue = $list_cols;
		
		$fieldsValue['USRUSR'] = "?";
		
		foreach($abil_cols as $val) {
			$abil_obj = wi400Detail::getDetailField($azione.'_ADD_USER_DET', $val);
			$abil = "N";
			if($abil_obj!="" && $abil_obj->getChecked()===true) {
				$abil = "S";
			}
//			echo "ABIL: $abil<br>";

			$fieldsValue[$val] = $abil;
		}
		
		$stmtinsert = $db->prepare("INSERT", $tabella, null, array_keys($fieldsValue));
		
		$user_array = wi400Detail::getDetailValue($azione.'_ADD_USER_DET', "UTENTE");
//		echo "UTENTE:<pre>"; print_r($user_array); "</pre>";

		$sql = "SELECT * FROM $tabella WHERE USRUSR=?";
		$stmt = $db->singlePrepare($sql);
		
		$error = false;
		$c = 0;
		if(!empty($user_array)) {
			foreach($user_array as $user) {
				$res = $db->execute($stmt, array($user));
				if($row = $db->fetch_array($stmt)) {
					$messageContext->addMessage("WARNING", "Utente $user già presente");
					continue;
				}
				
				$fieldsValue['USRUSR'] = $user;
//				echo "FIELDS:<pre>"; print_r($fieldsValue); echo "</pre>";
				
				$result = $db->execute($stmtinsert, $fieldsValue);
				
				if(!$result)
					$error = true;
				else
					$c++;
			}
		}
		
		if($c==0) {
			$error = true;
		}
	
		if($error===true)
			$messageContext->addMessage("ERROR", "Errore durante l'aggiunta degli utenti");
		else
			$messageContext->addMessage("SUCCESS", "Utenti aggiunti con successo");
		
		$actionContext->onSuccess("CLOSE","CLOSE_WINDOW_MSG");
//		$actionContext->onError($azione,"ADD_USER","","",true);
		$actionContext->onError("CLOSE","CLOSE_WINDOW_MSG","","",true);
	}
	else if($actionContext->getForm()=="DUPLICA_USER") {
		$actionContext->setLabel("Duplica utente");
	}
	else if($actionContext->getForm()=="SAVE_DUPLICA_USER") {
		$keyArray = array();
		$keyArray = getListKeyArray($azione."_LIST");
		
		$user = $keyArray['USRUSR'];
		
		$fieldsValue = $list_cols;
		
		$sql = "select * from $tabella where USRUSR='$user'";
		$res = $db->singleQuery($sql);
		
		$fieldsValue['USRUSR'] = "";
		if($row = $db->fetch_array($res)) {
			foreach($abil_cols as $val) {
				$fieldsValue[$val] = $row[$val];
			}
		} 
//		echo "FIELDS:<pre>"; print_r($fieldsValue); echo "</pre>";
	
		$stmtinsert = $db->prepare("INSERT", $tabella, null, array_keys($fieldsValue));
	
//		$user_array = wi400Detail::getDetailValue('ADD_USER_DET', "UTENTE");
		$user_array = array(wi400Detail::getDetailValue($azione."_DUPLICA_USER_DET", "UTENTE"));
//		echo "UTENTE:<pre>"; print_r($user_array); "</pre>";

		$sql = "SELECT * FROM $tabella WHERE USRUSR=?";
		$stmt = $db->singlePrepare($sql);
	
		$error = false;		
		$c = 0;
		if(!empty($user_array)) {
			foreach($user_array as $user) {
				$res = $db->execute($stmt, array($user));
				if($row = $db->fetch_array($stmt)) {
					$messageContext->addMessage("WARNING", "Utente $user già presente");
					continue;
				}
				
				$fieldsValue['USRUSR'] = $user;
//				echo "FIELDS:<pre>"; print_r($fieldsValue); echo "</pre>";
	
				$result = $db->execute($stmtinsert, $fieldsValue);
	
				if(!$result)
					$error = true;
				else
					$c++;
			}
		}
		
		if($c==0) {
			$error = true;
		}
	
		if($error===true)
			$messageContext->addMessage("ERROR", "Errore durante l'aggiunta dell'utente");
		else
			$messageContext->addMessage("SUCCESS", "Utente aggiunto con successo");
	
		$actionContext->onSuccess("CLOSE","CLOSE_WINDOW_MSG");
//		$actionContext->onError($azione,"DUPLICA_USER","","",true);
		$actionContext->onError("CLOSE","CLOSE_WINDOW_MSG","","",true);
	}
	else if($actionContext->getForm()=="DELETE_USER") {
		$key_del = array("USRUSR");
		$stmt_delete = $db->prepare("DELETE", $tabella, $key_del, null);
		
		$error = false;
		
		$idList = $_GET['IDLIST'];
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
		
		foreach($wi400List->getSelectionArray() as $key => $value){
			$keyArray = explode("|",$key);
			$user = $keyArray[0];
			
			$campi_del = array($user);
			
			$result_del = $db->execute($stmt_delete, $campi_del);
			
			if(!$result_del)
				$error = true;
		}
		
		if($error)
			$messageContext->addMessage("ERROR", "Errore durante l'eliminazione degli utenti");
		else
			$messageContext->addMessage("SUCCESS", "Utenti eliminati con successo");
		
		$actionContext->onSuccess($azione,"DEFAULT");
		$actionContext->onError($azione,"DEFAULT","","",true);
	}