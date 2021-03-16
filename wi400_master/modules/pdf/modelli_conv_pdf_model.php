<?php

	require_once 'modelli_conv_pdf_common.php';
	
//	echo "REQUEST:<pre>"; print_r($_REQUEST); echo "</pre>";
	
	$azione = $actionContext->getAction();
//	echo "AZIONE: $azione<br>";
	
	$modello = wi400Detail::getDetailValue($azione."_SRC", "CODMOD_SRC");
//	echo "MODELLO: $modello<br>";

	if($actionContext->getForm()=="MODELLI_SEL") {
		if($modello=="") {
			deleteList($azione."_LIST");
				
			$actionContext->gotoAction($azione, "LIST", "", true);
		}
		else {
			$row_mod = get_modello($modello);
//			echo "ROW: "; var_dump($row_mod); echo "<br>";
			
			if($row_mod) {
//				echo "ROW:<pre>", print_r($row_mod); echo "</pre>";
				
				$actionContext->gotoAction($azione, "MODELLO", "", true);
			}
			else {
				$actionContext->gotoAction($azione, "MODELLO_NEW", "", true);
			}
		}
	}
	
	$modelli_array = array();
	if(wi400Detail::getDetailValue($azione."_SRC", "MODELLI_SRC")!="")
		$modelli_array = wi400Detail::getDetailValue($azione."_SRC", "MODELLI_SRC");
//	echo "MODELLI:<pre>"; print_r($modelli_array); echo "</pre>";
	
//	$mod_cls = wi400Detail::getDetailValue($azione."_SRC", "MODCLS_SRC");
//	echo "CLASSE: $mod_cls<br>";

	$mod_cls = array();
	if(wi400Detail::getDetailValue($azione."_SRC", "MODCLS_SRC")!="")
		$mod_cls = wi400Detail::getDetailValue($azione."_SRC", "MODCLS_SRC");
//	echo "CLASSI:<pre>"; print_r($mod_cls); echo "</pre>";

	$pref_option = get_text_condition("MODPNA_SRC", $azione);
	$pref_name = wi400Detail::getDetailValue($azione."_SRC",'MODPNA_SRC');
//	echo "PREF OPTION: $pref_option - NOME PREFINCATO: $pref_name<br>";

	$logo_option = get_text_condition("MODLNA_SRC", $azione);
	$logo_name = wi400Detail::getDetailValue($azione."_SRC",'MODLNA_SRC');
//	echo "LOGO OPTION: $logo_option - NOME LOGO: $logo_name<br>";
	
	$off = 1;
	if(in_array($actionContext->getForm(), array(
		"DEFAULT", "LIST", "MODELLO", 
		"MODELLO_NEW", "MODELLO_COPIA"
	))) {
		$off = 2;
		$history->addCurrent();
	}	
//	echo "OFF: $off<br>";
	
	$steps = $history->getSteps();
//	echo "STEPS:<pre>"; print_r($steps); echo "</pre>";
	
	$array_steps = get_history_steps($off, $steps);
	
	$first_action = $array_steps['FIRST_ACTION'];
	$first_form = $array_steps['FIRST_FORM'];
	
	$last_action = $array_steps['LAST_ACTION'];
	$last_form = $array_steps['LAST_FORM'];
	
	$prev_action = $array_steps['PREV_ACTION'];
	$prev_form = $array_steps['PREV_FORM'];
	
//	echo "FIRST_ACTION: $first_action - FIRST FORM: $first_form<br>";
//	echo "LAST_ACTION: $last_action - LAST FORM: $last_form<br>";
//	echo "PREV_ACTION: $prev_action - LAST FORM: $prev_form<br>";

	if(in_array($azione."_LIST", $steps) && $actionContext->getForm()!="LIST") {
		$keyArray = array();
		$keyArray = getListKeyArray($azione."_LIST");
			
		$modello = $keyArray['MODNAM'];
//		echo "MODELLO - LIST: $modello<br>";
	}
	
	if(in_array($actionContext->getForm(), array("MODELLO", "MODELLO_COPIA"))) {
/*	
		$sql = "select * from SIR_MODULI where MODNAM='$modello'";
//		echo "SQL: $sql<br>";
	
		$result = $db->singleQuery($sql);
		if($row = $db->fetch_array($result)) {
//			echo "ROW:<pre>", print_r($row); echo "</pre>";
			
			$resultArray = $db->columns('SIR_MODULI');
//			echo "COLUMNS:<pre>"; print_r($resultArray); echo "</pre><br>";
		}
		else {
			$actionContext->gotoAction($azione, "MODELLO_NEW", "", true);
		}
*/
		$row = get_modello($modello);
//		echo "ROW: "; var_dump($row); echo "<br>";
		
		if($row) {
//			echo "ROW:<pre>", print_r($row); echo "</pre>";
		
			$resultArray = $db->columns('SIR_MODULI');
//			echo "COLUMNS:<pre>"; print_r($resultArray); echo "</pre><br>";
		}		
	}
	
	if(in_array($actionContext->getForm(), array("DEFAULT", "LIST"))) {
		wi400Detail::cleanSession($azione."_MODELLO_DET");
		wi400Detail::cleanSession($azione."_MODELLO_NEW_DET");
		wi400Detail::cleanSession($azione."_MODELLO_COPIA_DET");
	}
		
	if($actionContext->getForm()=="DEFAULT") {
		$label = $actionContext->getLabel();
//		$actionContext->setLabel("Parametri");
	}
	else if($actionContext->getForm()=="LIST") {
		$actionContext->setLabel("Lista Modelli");
		
		$select = "a.*";
		$select .= ", ".get_query_case_cond($orientamento_array, "MODPPL", "DES_MODPPL");
		
		$where_array = array();
		
		if(!empty($modelli_array)) {
			$where_array[] = "MODNAM in ('".implode("', '", $modelli_array)."')";
		}
/*		
		if($mod_cls!="") {
			$where_array[] = "MODCLS='$mod_cls'";
		}
*/		
		if(!empty($mod_cls)) {
			$where_array[] = "MODCLS in ('".implode("', '", $mod_cls)."')";
		}
		
		// Prefincato
		if($pref_name!="") {
			$where_array[] = where_text_condition($pref_option, $pref_name, "MODPNA");
		}
/*		
		// Logo
		if($logo_name!="") {
			$where_array[] = where_text_condition($logo_option, $logo_name, "MODLNA");
		}
*/		
		$where = "";
		if(!empty($where_array))
			$where = implode(" and ", $where_array);
//		echo "WHERE: $where<br>";
	}
	else if($actionContext->getForm()=="MODELLO") {
		$actionContext->setLabel("Modello");
	}
	else if($actionContext->getForm()=="MODELLO_NEW") {
		$actionContext->setLabel("Nuovo modello");
	}
	else if($actionContext->getForm()=="MODELLO_COPIA") {
		$actionContext->setLabel("Nuovo modello");
	}
	else if(in_array($actionContext->getForm(), array("INS_MODELLO", "UPDT_MODELLO"))) {
		$fields = $db->columns('SIR_MODULI', Null, false);
//		echo "FIELDS:<pre>"; print_r($fields); echo "</pre><br>";
		
		$fieldsValue = impostaTracciato($fields, $_POST);
		
//		echo "POST:<pre>"; print_r($_POST); echo "</pre><br>";
//		echo "VALUES:<pre>"; print_r($fieldsValue); echo "</pre><br>";
		
		if($actionContext->getForm()=="UPDT_MODELLO") {
			unset($fieldsValue['MODNAM']);
			
			$keys = array("MODNAM" => $modello);
			$stmtupdate = $db->prepare("UPDATE", "SIR_MODULI", $keys, array_keys($fieldsValue));
			
			$result = $db->execute($stmtupdate, $fieldsValue);
			
			if(!$result)
				$messageContext->addMessage("ERROR","Errore durante la modifica dei dati del modello");
			else
				$messageContext->addMessage("SUCCESS","Modifica dei dati del modello eseguita con successo");
		}
		else if($actionContext->getForm()=="INS_MODELLO") {
			$stmtinsert = $db->prepare("INSERT", "SIR_MODULI", null, array_keys($fieldsValue));
			
			$fieldsValue['MODNAM'] = $_POST['CODMOD'];
			
			$result = $db->execute($stmtinsert, $fieldsValue);
			
			if(!$result)
				$messageContext->addMessage("ERROR","Errore durante l'aggiunta del modello");
			else
				$messageContext->addMessage("SUCCESS","Aggiunta del modello eseguita con successo");
		}
		
//		if($last_form=="DEFAULT")
//			$actionContext->onSuccess($azione, "DEFAULT", "", "MODELLO_NEW");
//		else
			$actionContext->onSuccess($azione, $prev_form);
			
		$actionContext->onError($azione, $last_form, "", "", true);
	}
	else if($actionContext->getForm()=="ELIMINA") {
		$keyDel = array("MODNAM");		
		$stmt_del = $db->prepare("DELETE", "SIR_MODULI", $keyDel, null);
				
		$res = $db->execute($stmt_del, array($modello));
		
		if(!$res)
			$messageContext->addMessage("ERROR","Errore durante l'eliminazine del modello");
		else
			$messageContext->addMessage("SUCCESS","Eliminazione del modello eseguita con successo");
		
		if(in_array($azione."_LIST", $steps)) {
			$actionContext->onSuccess($azione, $last_form);
		}
		else {
			$actionContext->onSuccess($azione, $prev_form);
		}
		
		$actionContext->onError($azione, $last_form, "", "", true);
	}