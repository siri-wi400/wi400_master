<?php 

	$azione = $actionContext->getAction();
	
	$modello = "";
	if(wi400Detail::getDetailValue($azione."_SRC", "codmod")!="")
		$modello = wi400Detail::getDetailValue($azione."_SRC", "codmod");
		
	if(in_array($actionContext->getForm(),array("DEFAULT","DETAIL","COPY")))
		$history->addCurrent();
	
	if($actionContext->getForm()=="DEFAULT") {
		// Azione corrente
		$actionContext->setLabel("Parametri");
	}
	else if(in_array($actionContext->getForm(),array("DETAIL","COPY"))) {
		if($actionContext->getForm()=="COPY") {
			// Azione corrente
			$actionContext->setLabel("Copia del modello di conversione");
		}
		
		$sql = "select * from SIR_MODULI where MODNAM ='$modello'";
//		echo "SQL: $sql<br>";		
		$result = $db->query($sql);
		$row = $db->fetch_array($result);
//		echo "ROW:<pre>"; print_r($row); echo "</pre><br>";
		
		$resultArray = $db->columns('SIR_MODULI');
//		echo "COLUMNS:<pre>"; print_r($resultArray); echo "</pre><br>";
	}
	else if($actionContext->getForm()=="INSERT") {
		$fields = $db->columns('SIR_MODULI', Null, false);
//		echo "FIELDS:<pre>"; print_r($fields); echo "</pre><br>";
		
		$stmtinsert = $db->prepare("INSERT", "SIR_MODULI", null, array_keys($fields));
		
		$fieldsValue = impostaTracciato($fields, $_POST);
		
		$steps = $history->getSteps();
		$last_step = $steps[count($steps)-1];
		$action_obj = $history->getAction($last_step);
		if(isset($action_obj)) {
			$form_name = $action_obj->getForm();
		}

		if($form_name=="DETAIL")
			$fieldsValue['MODNAM'] = $modello;
		else
			$fieldsValue['MODNAM'] = $_POST['codmod'];
		
//		echo "POST:<pre>"; print_r($_POST); echo "</pre><br>";
//		echo "VALUES:<pre>"; print_r($fieldsValue); echo "</pre><br>";
	
		$result = $db->execute($stmtinsert, $fieldsValue);
		
	    if($result) 
	    	$messageContext->addMessage("SUCCESS", "Aggiornamento eseguito con successo");
	    else
	    	$messageContext->addMessage("ERROR", "Si sono verificati degli errori");

    	$actionContext->onSuccess($azione,"DEFAULT");
    	$actionContext->onError($azione,"DETAIL","","",true);
	}
	else if($actionContext->getForm()=="UPDATE") {
		$fields = $db->columns('SIR_MODULI', Null, false);	
		$keys = array("MODNAM" => $modello);
		
//		echo "FIELDS:<pre>"; print_r($fields); echo "</pre><br>";
		
		$stmtupdate = $db->prepare("UPDATE", "SIR_MODULI", $keys, array_keys($fields));
		$fieldsValue = impostaTracciato($fields, $_POST);
		$fieldsValue['MODNAM'] = $modello;
		/*foreach ($fieldsValue as $key=>$value) {
			$fieldsValue[$key] = str_replace(",", ".",$value);
		}*/
		$result = $db->execute($stmtupdate, $fieldsValue);
/*		echo "<br>".db2_stmt_errormsg($stmtupdate)."<br>";
		echo "VALUES:<pre>"; print_r($fieldsValue); echo "</pre><br>";
		die();		
*/
		
		if ($result) 
			$messageContext->addMessage("SUCCESS", "Aggiornamento eseguito con successo");
	    else 
	    	$messageContext->addMessage("ERROR", "Si sono verificati degli errori");

		$actionContext->onSuccess($azione,"DEFAULT");
    	$actionContext->onError($azione,"DETAIL","","",true);
    } 
    else if($actionContext->getForm()=="DELETE"){
    	// Cancello articoli del gruppo
		$keys = array("MODNAM");
		$stmtdelete = $db->prepare("DELETE", "SIR_MODULI", $keys, null); 		
		$campi = array($modello);
		
	  	$result = $db->execute($stmtdelete, $campi);
    	
       	if ($result) 
       		$messageContext->addMessage("SUCCESS", "Cancellazione del record azione $modello eseguita");
	    else
	    	$messageContext->addMessage("ERROR", "Il record azione ".$_POST['codmod']." non è stato cancellato");
	    	
	    $actionContext->onSuccess($azione,"DEFAULT");
    	$actionContext->onError($azione,"DETAIL","","",true);
	}

?>