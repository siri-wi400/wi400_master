<?php

	require_once $moduli_path.'/utilities/filtri_detail_common.php';
	require_once 'query_tool_db_commons.php';
	
	$azione = $actionContext->getAction();
	
	if(in_array($actionContext->getForm(), array("DEFAULT", "QUERY_LIST", "USER_LIST"))) {
		$history->addCurrent();
	}
	
	$steps = $history->getSteps();
//	echo "STEPS:<pre>"; print_r($steps); echo "</pre>";
	
	$user_src = wi400Detail::getDetailValue($azione."_SRC",'USER_SRC');
	
	if(is_null($user_src))
		$user_src = $idUser;
	
	if(!in_array($actionContext->getForm(), array("DEFAULT", "QUERY_LIST", "ADD_QUERY_SEL"))) {
		$keyArray1 = array();
		$keyArray1 = getListKeyArray($azione."_QUERY_LIST");
		
		$id_query = $keyArray1['ID_QUERY'];
//		$des_query = $keyArray1['DES_QUERY'];

		$sql_query = "select * from TABQUERY where ID_QUERY='$id_query'";
		$res_query = $db->singleQuery($sql_query);
		$row_query = $db->fetch_array($res_query);
		
		$des_query = $row_query['DES_QUERY'];
		$note_query = $row_query['NOTE'];
		$area_query = $row_query['AREA'];
		$funz_query = $row_query['FUNZIONE'];
		
		if(in_array($azione."_USER_LIST", $steps) && $actionContext->getForm()!="USER_LIST") {
			$keyArray2 = array();
			$keyArray2 = getListKeyArray($azione."_USER_LIST");
			
//			$id_query = $keyArray2['ID_QUERY'];
			$user_query = $keyArray2['USER'];
		}
	}
	
	if($actionContext->getForm()=="DEFAULT") {
		$label = $actionContext->getLabel();
		$actionContext->setLabel("Parametri");
	}
	else if($actionContext->getForm()=="QUERY_LIST") {
		wi400Detail::cleanSession($azione."_MOD_DES_DET");
		
		$select = "a.ID_QUERY, a.DES_QUERY, a.NOTE, a.AREA, a.FUNZIONE, a.USERINS, a.USERMOD, 
			(select count(*) from USERQUERY c where c.ID_QUERY=a.ID_QUERY and STATO<>'0') as NUM_USERS";
		$from = "TABQUERY a";
		$from .= " left join USERQUERY b on a.ID_QUERY=b.ID_QUERY and b.STATO='1'";
		$where = "a.STATO='1'";
		
		if(!empty($user_src)) {
			$where .= " and a.ID_QUERY in (select ID_QUERY from USERQUERY where USER_NAME='$user_src' and STATO='1')";
		}
		
		$group_by = "a.ID_QUERY, a.DES_QUERY, a.NOTE, a.AREA, a.FUNZIONE, a.USERINS, a.USERMOD";		
	}
	else if($actionContext->getForm()=="USER_LIST") {
		$actionContext->setLabel("Distribuzione Query");
		
		$from = "USERQUERY";
		$where = "ID_QUERY=$id_query";
		$where .= " and STATO='1'";
	}
	else if($actionContext->getForm()=="ADD_USER_SEL_INT") {
		wi400Detail::cleanSession($azione."_ADD_USER_SEL");
		$actionContext->gotoAction($azione, "ADD_USER_SEL", "", true);
	}
	else if($actionContext->getForm()=="ADD_USER_SEL") {
		$actionContext->setLabel("Aggiungi utenti");
		
		$from = $id_user_file_lib.$settings['db_separator'].$id_user_file;
		$where = $id_user_name." not in (select USER_NAME from USERQUERY where ID_QUERY=$id_query and STATO='1')";
		
		$where_cond = array();
		foreach($query_admin_array as $val) {
			$where_cond[] = "WI400_GROUPS like '%$val%'";
		}
		$where .= " and (".implode(" or ", $where_cond).")";
	}
	else if($actionContext->getForm()=="ADD_USER") {
		$fieldsValue = getDs("USERQUERY");
//		echo "TAB FIELDS:<pre>"; print_r($fieldsValue); echo "</pre>";
			
		$stmt_ins = $db->prepare("INSERT", "USERQUERY", null, array_keys($fieldsValue));
		
//		$user_sel = $_REQUEST['USER_SEL'];
		$user_sel = wi400Detail::getDetailValue($azione."_ADD_USER_SEL",'USER_SEL');
/*
		$idList = $azione."_ADD_USER_SEL_LIST";
		
//		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
		$wi400List = getList($idList);
		
		$rowsSelectionArray = $wi400List->getSelectionArray();
*/		
		$error = false;
		foreach($user_sel as $usr) {
/*		
		foreach($rowsSelectionArray as $key => $val) {
			$keys = explode("|", $key);
//			echo "KEYS:<pre>"; print_r($keys); echo "</pre>";
		
			$usr = $keys[0];
*/			
			$campi = $fieldsValue;
			$campi['ID_QUERY'] = $id_query;
			$campi['ID_FOLDER'] = -1;
			$campi['USER_NAME'] = $usr;
			$campi['STATO'] = "1";
			$campi['USERINS'] = $idUser;
			$campi['DATINS'] = $date;
			$campi['ORAINS'] = $hour;
			$campi['USERMOD'] = $idUser;
			$campi['DATMOD'] = $date;
			$campi['ORAMOD'] = $hour;
			
			$res_ins = $db->execute($stmt_ins, $campi);
			
			if(!$res_ins)
				$error = true;
		}
		
		if($error===false) {
			$messageContext->addMessage("SUCCESS", "Aggiunta di utenti avvenuta con successo");
			
			wi400Detail::cleanSession($azione."_ADD_USER_SEL");
		}
		else
			$messageContext->addMessage("ERROR", "Errore durante l'aggiunta di utenti");
		
//		$actionContext->onSuccess($azione, "USER_LIST");
//		$actionContext->onError($azione, "USER_LIST", "", "", true);
		$actionContext->onSuccess("CLOSE", "CLOSE_WINDOW_MSG");
		$actionContext->onError($azione, "ADD_USER_SEL", "", "", true);
	}
	else if($actionContext->getForm()=="ADD_QUERY_SEL") {
		$actionContext->setLabel("Aggiungi query");
		
		$from = "TABQUERY";
		$where = "STATO='1'";
		
		$where .= " and ID_QUERY not in (select ID_QUERY from USERQUERY where USER_NAME='$user_src' and STATO='1')";
	}
	else if($actionContext->getForm()=="ADD_QUERY") {
		$fieldsValue = getDs("USERQUERY");
		
		$fieldsValue['ID_FOLDER'] = -1;
		$fieldsValue['USER_NAME'] = $user_src;
		$fieldsValue['STATO'] = "1";
		$fieldsValue['USERINS'] = $idUser;
		$fieldsValue['DATINS'] = $date;
		$fieldsValue['ORAINS'] = $hour;
		$fieldsValue['USERMOD'] = $idUser;
		$fieldsValue['DATMOD'] = $date;
		$fieldsValue['ORAMOD'] = $hour;
		
//		echo "USER FIELDS:<pre>"; print_r($fieldsValue); echo "</pre>";
			
		$stmt_ins = $db->prepare("INSERT", "USERQUERY", null, array_keys($fieldsValue));
		
//		$query_sel = $_REQUEST['QUERY_SEL'];

		$idList = $azione."_ADD_QUERY_SEL_LIST";
		
//		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
		$wi400List = getList($idList);
		
		$rowsSelectionArray = $wi400List->getSelectionArray();
		
		$error = false;
//		foreach($query_sel as $qry) {
		foreach($rowsSelectionArray as $key => $val) {
			$keys = explode("|", $key);
//			echo "KEYS:<pre>"; print_r($keys); echo "</pre>";
		
			$qry = $keys[0];
			
			$campi = $fieldsValue;
			$campi['ID_QUERY'] = $qry;
				
			$res_ins = $db->execute($stmt_ins, $campi);
				
			if(!$res_ins)
				$error = true;
		}
		
		if($error===false) {
			$messageContext->addMessage("SUCCESS", "Aggiunta di query avvenuta con successo");
			wi400Detail::cleanSession($azione."_ADD_QUERY_SEL");
		}
		else
			$messageContext->addMessage("ERROR", "Errore durante l'aggiunta di query");
		
//		$actionContext->onSuccess($azione, "QUERY_LIST");
//		$actionContext->onError($azione, "QUERY_LIST", "", "", true);
		$actionContext->onSuccess("CLOSE", "CLOSE_WINDOW_MSG");
		$actionContext->onError($azione, "ADD_QUERY_SEL", "", "", true);
	}
	else if($actionContext->getForm()=="ADD_QUERY_ALL") {
		$fieldsValue = getDs("USERQUERY");
		
		$fieldsValue['ID_FOLDER'] = -1;
		$fieldsValue['USER_NAME'] = $user_src;
		$fieldsValue['STATO'] = "1";
		$fieldsValue['USERINS'] = $idUser;
		$fieldsValue['DATINS'] = $date;
		$fieldsValue['ORAINS'] = $hour;
		$fieldsValue['USERMOD'] = $idUser;
		$fieldsValue['DATMOD'] = $date;
		$fieldsValue['ORAMOD'] = $hour;
		
//		echo "USER FIELDS:<pre>"; print_r($fieldsValue); echo "</pre>";
			
		$stmt_ins = $db->prepare("INSERT", "USERQUERY", null, array_keys($fieldsValue));
	
//		$query_sel = $_REQUEST['QUERY_SEL'];
	
		$idList = $azione."_ADD_QUERY_SEL_LIST";
	
//		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
		$wi400List = getList($idList);
		
		$sql = $wi400List->getSql();
		$res = $db->query($sql, false, 0);
		
		$error = false;
		while($row = $db->fetch_array($res)) {
			$campi = $fieldsValue;
			$campi['ID_QUERY'] = $row['ID_QUERY'];
	
			$res_ins = $db->execute($stmt_ins, $campi);
	
			if(!$res_ins)
				$error = true;
		}
	
		if($error===false) {
			$messageContext->addMessage("SUCCESS", "Aggiunta di query avvenuta con successo");
			wi400Detail::cleanSession($azione."_ADD_QUERY_SEL");
		}
		else
			$messageContext->addMessage("ERROR", "Errore durante l'aggiunta di query");
	
//		$actionContext->onSuccess($azione, "QUERY_LIST");
//		$actionContext->onError($azione, "QUERY_LIST", "", "", true);
		$actionContext->onSuccess("CLOSE", "CLOSE_WINDOW_MSG");
		$actionContext->onError($azione, "ADD_QUERY_SEL", "", "", true);
	}
	else if(in_array($actionContext->getForm(), array("RIMUOVI_USER_SEL", "RIMUOVI_USER", "RIMUOVI_QUERY_SEL", "RIMUOVI_QUERY"))) {
		if(in_array($actionContext->getForm(), array("RIMUOVI_USER_SEL", "RIMUOVI_USER")))
			$idList = $azione."_USER_LIST";
		else if(in_array($actionContext->getForm(), array("RIMUOVI_QUERY_SEL", "RIMUOVI_QUERY")))
			$idList = $azione."_QUERY_LIST";
		
//		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
		$wi400List = getList($idList);
		
		$rowsSelectionArray = $wi400List->getSelectionArray();
		
		// UPDATE
		$keyUpdt = array("ID_QUERY" => "?", "USER_NAME" => "?", "STATO" => "1");
		$fieldsValue_updt = array(
			"STATO" => "0",
			"USERMOD" => $idUser,
			"DATMOD" => $date,
			"ORAMOD" => $hour
		);
		
		$stmt_updt = $db->prepare("UPDATE", "USERQUERY", $keyUpdt, array_keys($fieldsValue_updt));
		
		$error = false;
		foreach($rowsSelectionArray as $key => $val) {
			$keys = explode("|", $key);
//			echo "KEYS:<pre>"; print_r($keys); echo "</pre>";
		
			if(in_array($actionContext->getForm(), array("RIMUOVI_USER_SEL", "RIMUOVI_USER"))) {
				$id_query = $keys[0];
				$user_query = $keys[1];
			}
			else if(in_array($actionContext->getForm(), array("RIMUOVI_QUERY_SEL", "RIMUOVI_QUERY"))) {
				$id_query = $keys[0];
				
				$user_query = $user_src;
			}
			
			$campi = $fieldsValue_updt;
			$campi[] = $id_query;
			$campi[] = $user_query;
//			echo "CAMPI:<pre>"; print_r($campi); echo "</pre>";
			
			$res_updt = $db->execute($stmt_updt, $campi);
					
			if(!$res_updt)
				$error = true;
		}
		
		if($actionContext->getForm()=="RIMUOVI_USER_SEL")
			$msg = "dell'utente alla query";
		else if($actionContext->getForm()=="RIMUOVI_USER")
			$msg = "degli utenti alla query";
		else if($actionContext->getForm()=="RIMUOVI_QUERY_SEL")
			$msg = "dell'utente alla query";
		else if($actionContext->getForm()=="RIMUOVI_QUERY")
			$msg = "dell'utnete alle query";
		
		if($error===false)
			$messageContext->addMessage("SUCCESS", "Rimozione dell'associazione $msg avvenuta con successo");
		else
			$messageContext->addMessage("ERROR", "Errore durante la rimozione dell'associazione $msg");
		
		if(in_array($actionContext->getForm(), array("RIMUOVI_USER_SEL", "RIMUOVI_USER"))) {
			$actionContext->onSuccess($azione, "USER_LIST");
			$actionContext->onError($azione, "USER_LIST", "", "", true);
		}
		else if(in_array($actionContext->getForm(), array("RIMUOVI_QUERY_SEL", "RIMUOVI_QUERY"))) {
			$actionContext->onSuccess($azione, "QUERY_LIST");
			$actionContext->onError($azione, "QUERY_LIST", "", "", true);
		}
	}
	else if($actionContext->getForm()=="DELETE") {
		$idList = $azione."_QUERY_LIST";
		
//		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
		$wi400List = getList($idList);
		
		$rowsSelectionArray = $wi400List->getSelectionArray();
		
		// UPDATE
		$keyUpdt = array("ID_QUERY" => "?", "STATO" => "1");
		$fieldsValue_updt = array(
			"STATO" => "0",
			"USERMOD" => $idUser,
			"DATMOD" => $date,
			"ORAMOD" => $hour
		);
		
		$stmt_updt = $db->prepare("UPDATE", "TABQUERY", $keyUpdt, array_keys($fieldsValue_updt));
		
		$stmt_updt_user = $db->prepare("UPDATE", "USERQUERY", $keyUpdt, array_keys($fieldsValue_updt));
		
		$error = false;
		foreach($rowsSelectionArray as $key => $val) {
			$keys = explode("|", $key);
//			echo "KEYS:<pre>"; print_r($keys); echo "</pre>";
		
			$id_query = $keys[0];
				
			$campi = $fieldsValue_updt;
			$campi[] = $id_query;
//			echo "CAMPI:<pre>"; print_r($campi); echo "</pre>";
				
			$res_updt = $db->execute($stmt_updt, $campi);
				
			if(!$res_updt) {
				$error = true;
			}
			else {
				$res_updt_user = $db->execute($stmt_updt_user, $campi);
				
//				if(!$res_updt_user)
//					$error = true;
			}
		}
		
		if($error===false)
			$messageContext->addMessage("SUCCESS", "Eliminazione della query avvenuta con successo");
		else
			$messageContext->addMessage("ERROR", "Errore durante l'eliminazione della query");
		
		$actionContext->onSuccess($azione, "QUERY_LIST");
		$actionContext->onError($azione, "QUERY_LIST", "", "", true);
	}
	else if($actionContext->getForm()=="MOD_DES") {
		
	}
	else if($actionContext->getForm()=="SAVE_DES") {
		$des_query = trim(wi400Detail::getDetailValue($azione."_MOD_DES_DET", "DES_QUERY"));
		$note_query = trim(wi400Detail::getDetailValue($azione."_MOD_DES_DET", "NOTE"));
		$area_query = trim(wi400Detail::getDetailValue($azione."_MOD_DES_DET", "AREA"));
		$funz_query = trim(wi400Detail::getDetailValue($azione."_MOD_DES_DET", "FUNZIONE"));
		
		$keyUpdt = array("ID_QUERY" => "?", "STATO" => "1");
		$fieldsValue_updt = array(
			"DES_QUERY" => $des_query,
			"NOTE" => $note_query,
			"AREA" => $area_query,
			"FUNZIONE" => $funz_query,
			"USERMOD" => $idUser,
			"DATMOD" => $date,
			"ORAMOD" => $hour
		);
//		echo "TAB FIELDS:<pre>"; print_r($fieldsValue_updt); echo "</pre>";
		
		$stmt_updt = $db->prepare("UPDATE", "TABQUERY", $keyUpdt, array_keys($fieldsValue_updt));
			
		$campi = $fieldsValue_updt;
		$campi[] = $id_query;
//		echo "CAMPI:<pre>"; print_r($campi); echo "</pre>";
		
		$res = $db->execute($stmt_updt, $campi);
			
		if(!$res)
			$messageContext->addMessage("ERROR", "Errore durante il salvataggio");
		else {
			$messageContext->addMessage("SUCCESS", "Salvataggio eseguito con successo.");
		
			wi400Detail::cleanSession($azione."_MOD_DES_DET");
		}
		
		$actionContext->onSuccess("CLOSE", "CLOSE_WINDOW_MSG");
		$actionContext->onError($azione, "MOD_DES", "", "", true);
	}