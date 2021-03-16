<?php 

	require_once 'azioni_menu_commons.php';
	
	$azione_corrente = $actionContext->getAction();
	
	$menu = wi400Detail::getDetailValue('ricercaMenu','codmen');
	
	if(in_array($actionContext->getForm(),array("DEFAULT","DETAIL","COPIA")))
		$history->addCurrent();
	
	// Recupero dei dati
	if(in_array($actionContext->getForm(),array("INSERT","UPDATE","COPY"))) {
		$actionList = "";
		if(isset($_POST['AZIONI'])) {
			$actionList = implode(";",$_POST['AZIONI']);
		}
	}
	
	$steps = $history->getSteps();
	$first_action_name = "TMENU";
	if(count($steps)>2) {
		$first_action = $history->getAction($steps[0]);
		$first_action_name = $first_action->getAction();
	}
	
	if(in_array($actionContext->getForm(),array("DETAIL","COPIA"))) {
		$sql="select * from FMNUSIRI where MENU =?";
		$stmt = $db->singlePrepare($sql,0,true);
		
		// Carico i dati dall'azione
		$sql_azione = "select * from FAZISIRI where AZIONE =?";
		$stmt_azione = $db->singlePrepare($sql_azione,0,true);
		
		if($actionContext->getForm()=="DETAIL") {
			$result = $db->execute($stmt,array($menu));
			
			$result_azione = $db->execute($stmt_azione,array($menu));
				
			// Azione corrente
			$actionContext->setLabel(_t('MENU_TITLE'));
		}
		else if($actionContext->getForm()=="COPIA") {
			$menu_old = wi400Detail::getDetailValue("COPY_MENU","codmen1");
			$menu_new = wi400Detail::getDetailValue("COPY_MENU","codmen2");
//			echo "MENU OLD: $menu_old - MENU NEW: $menu_new<br>";
				
			$result = $db->execute($stmt,array($menu_old));
			
			$result_azione = $db->execute($stmt_azione,array($menu_old));
				
			// Azione corrente
			$actionContext->setLabel("Copia menu");
		}
		
		$resultArray = $db->columns('FMNUSIRI');
//		echo "COLUMNS:<pre>"; print_r($resultArray); echo "</pre>";
		
		$row = $db->fetch_array($stmt);
//		echo "ROW:<pre>"; print_r($row); echo "</pre>";
		
		$saveAction = "INSERT";
		if($actionContext->getForm()=="DETAIL" && isset($row["MENU"])) {
			$saveAction = "UPDATE";
		}
		else if($actionContext->getForm()=="COPIA") {
			$saveAction = "COPY";
		}
//		echo "SAVE: $saveAction<br>";

		// Carico i dati dall'azione
		$row_azione = $db->fetch_array($stmt_azione);
//		echo "ROW AZIONE:<pre>"; print_r($row_azione); echo "</pre>";
		
		if(!isset($row['DESCRIZIONE'])) {
		    $row['DESCRIZIONE'] = $row_azione['DESCRIZIONE'];
		}
	}
	else if(in_array($actionContext->getForm(), array("INSERT", "COPY"))) {
		$menu = $_POST['codmen'];
		
		$error = false;
		
		if($actionContext->getForm()=="COPY") {
			// Copia azione
			
			$menu_old = wi400Detail::getDetailValue("COPY_MENU","codmen1");
			$menu_new = wi400Detail::getDetailValue("COPY_MENU","codmen2");
			
			// Carico i dati dall'azione
			$sql_azione = "select * from FAZISIRI where AZIONE ='".$menu_old."'";
			$res_azione = $db->singleQuery($sql_azione);
			$row_azione = $db->fetch_array($res_azione);
//			echo "ROW AZIONE:<pre>"; print_r($row_azione); echo "</pre>";
			
			$campi_azione = $row_azione;
			unset($campi_azione['ID']);
			$campi_azione['AZIONE'] = $menu_new;

			$stmt_ins_azione = $db->prepare("INSERT", "FAZISIRI", null, array_keys($campi_azione));			
			$res_ins_azione = $db->execute($stmt_ins_azione, $campi_azione);
			
			if(!$res_ins_azione) {
				$error = true;
				$messageContext->addMessage("ERROR", _t('UPDATE_ERROR'));
			}
		}
		
		if($error===false) {
			$field = array("MENU", "DESCRIZIONE", "SCRIPT", "ICOMENU", "EXPICO", "CHKPGM", "AZIONI", "AZIONE");
			$key = array("MENU"=>$_POST['codmen']);
			$stmt_ins = $db->prepare("INSERT", "FMNUSIRI", $key, $field);
			$campi = array($_POST['codmen'],$_POST['DESCRIZIONE'], $_POST['SCRIPT'], $_POST['ICOMENU'], $_POST['EXPICO'],
				$_POST['CHKPGM'], $actionList, $_POST['AZIONE']);
			$res_ins = $db->execute($stmt_ins, $campi);
//			echo "CAMPI:<pre>"; print_r($campi); echo "</pre>";
		    
		    if ($res_ins) 
		    	$messageContext->addMessage("SUCCESS", _t('UPDATE_SUCCESS'), "",true);
		    else 
		    	$messageContext->addMessage("ERROR", _t('UPDATE_ERROR'));
		}

    	$actionContext->onSuccess($first_action_name,"DEFAULT");
    	if($actionContext->getForm()=="INSERT")
    		$actionContext->onError($azione_corrente,"DETAIL");
    	else if($actionContext->getForm()=="COPY")
    		$actionContext->onError($azione_corrente,"COPIA");
	}
	else if($actionContext->getForm()=="UPDATE") {
		$field = array("DESCRIZIONE", "SCRIPT", "ICOMENU", "EXPICO", "CHKPGM", "AZIONI", "AZIONE");
		$key = array("MENU"=>$_POST['codmen']);
		$stmt = $db->prepare("UPDATE", "FMNUSIRI", $key, $field);
		$campi = array($_POST['DESCRIZIONE'], $_POST['SCRIPT'], $_POST['ICOMENU'], $_POST['EXPICO'],
			$_POST['CHKPGM'], $actionList, $_POST['AZIONE']);
		$result = $db->execute($stmt, $campi);
	    if ($result) 
	    	$messageContext->addMessage("SUCCESS", _t('UPDATE_SUCCESS'));
	    else 
	    	$messageContext->addMessage("ERROR", _t('UPDATE_ERROR'));

   		$actionContext->onSuccess($first_action_name,"DEFAULT");
    	$actionContext->onError($azione_corrente,"DETAIL");
	}
	else if($actionContext->getForm()=="DELETE") {
		$sql = "delete from FMNUSIRI where MENU='$menu'";
        $result = $db->query($sql); 
        
       	if($result) 
       		$messageContext->addMessage("SUCCESS", _t('DELETE_SUCCESS',array($menu)));
	    else 
	    	$messageContext->addMessage("ERROR", _t('DELETE_ERROR', array($menu)));
	    
	    $actionContext->onSuccess($first_action_name, "DEFAULT");
    	$actionContext->onError($azione_corrente, "DETAIL");
	}
	else if($actionContext->getForm() == "CHECK"){
		
		$resultArray = getDetail("DETTAGLIO_FMNUSIRI");
//		echo "RESULT ARRAY:<pre>"; print_r($resultArray); echo "</pre>";
		
		$saveAction = $_POST['SAVE_ACTION'];
		
		$actionContext->gotoAction($azione_corrente,"DETAIL");
	}

?>