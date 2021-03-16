<?php

	require_once 'test_globale_commons.php';

	$azione = $actionContext->getAction();
	$form = $actionContext->getForm();
	
	$history->addCurrent();
	
	if($actionContext->getForm()=="DEFAULT") {
		$actionContext->setLabel("Parametri");
		
		$to_groups = array();
		$from_groups = array();
		
		if(isset($_SESSION['TO_GROUP'])) {
			$query = "SELECT * FROM ORDINI WHERE ID_ORDINE IN ('".implode("', '", $_SESSION['TO_GROUP'])."')";
			$rs = $db->query($query);
			while($row = $db->fetch_array($rs)) {
				if($row['STATO'] == '1') {
					$to_groups[$row['ID_ORDINE']] = getLabel($row, $azione);
				}
			}
		}
		
		if(isset($_SESSION['FROM_GROUP'])) {
			$query = "SELECT * FROM ORDINI WHERE ID_ORDINE IN ('".implode("', '", $_SESSION['FROM_GROUP'])."')";
		}else {
			$query = "SELECT * FROM ORDINI";
		}
		$rs = $db->query($query);
		
		while($row = $db->fetch_array($rs)) {
			$from_groups[$row['ID_ORDINE']] = getLabel($row, $azione);
		}
	}
	else if($form == "DETAIL") {
		
	}else if($form == "INFO") {

		$query = "SELECT * FROM ORDINI where ID_ORDINE='{$_REQUEST['ID_ORDINE']}'";
		$rs = $db->query($query);
		if(!$row = $db->fetch_array($rs)) {
			die("ID_ORDINE NON TROVATO! -> ".$_REQUEST['ID_ORDINE']);
		}
		/*if(!$row = getInfoOrdine($_REQUEST['ID_ORDINE'])) {
			die("ID_ORDINE NON TROVATO! -> ".$_REQUEST['ID_ORDINE']);
		}*/
	}else if($form == "ELIMINA") {
		$query = "DELETE FROM ORDINI where ID_ORDINE='{$_REQUEST['ID_ORDINE']}'";
		//$rs = $db->query($query);
		
		$rs = "1";
		if($rs) {
			$messageContext->addMessage("SUCCESS", "Eliminazione effettuata con successo!");
			$to_groups = $_SESSION['TO_GROUP'];
			$from_groups = $_SESSION['FROM_GROUP'];
			
			$to_groups = explode(",", $to_groups);
			$from_groups = explode(",", $from_groups);
			
			if(($key = array_search($_REQUEST['ID_ORDINE'], $to_groups)) !== false) {
				unset($to_groups[$key]);
			}
			if(($key = array_search($_REQUEST['ID_ORDINE'], $from_groups)) !== false) {
				unset($from_groups[$key]);
			}
			
			$_SESSION['TO_GROUP'] = $to_groups;
			$_SESSION['FROM_GROUP'] = $from_groups;
		}else {
			$messageContext->addMessage("ERROR", "Errore eliminazione!");
		}
		
		//die("sono qui???");
		$actionContext->gotoAction("CLOSE", "CLOSE_WINDOW_MSG", "", true, true);
		
	}else if($form == "RIPRISTINA") {
		unset($_SESSION['TO_GROUP']);
		unset($_SESSION['FROM_GROUP']);
		
		showArray($_REQUEST);
		
		$actionContext->gotoAction($azione, $_REQUEST['CURRENT_ACTION'], "", true);
	}else if($form == "SALVA_DRAG_DROP") {
		
	}