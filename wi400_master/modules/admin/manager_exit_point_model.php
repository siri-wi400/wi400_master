<?php

	$azione = $actionContext->getAction();
	$form = $actionContext->getForm();
	
	$history->addCurrent();
	
	if($form != "DEFAULT") {
		$keyTestata = getListKeyArray($azione."_TESTATA");
	}
	
	$wi400_trigger->deleteCache();
	
	if($form == "DEFAULT") {
		
	}else if($form == "RIGHE") {
		$actionContext->setLabel("Dettaglio");
		
		//showArray($key);
		$where = array("EAID='{$keyTestata['EXID']}'", "EAEVENT='{$keyTestata['EXEVENT']}'");
	}else if($form == "MODIFICA_RIGA") {
		$actionContext->setLabel("Modifica");
		
		$keyRighe = getListKeyArray($azione."_RIGHE");
		$prg = $keyRighe['EAPRG'];
		
		$dati = $wi400_trigger->getDettaglioExitPoint($keyTestata['EXID'], $keyTestata['EXEVENT'], $prg);
	}else if($form == "NUOVA_RIGA") {
		$actionContext->setLabel("Nuovo");
		
		$dati = array("EASTA" => "0");
	}else if($form == "SALVA_RIGA") {
		$keyRighe = getListKeyArray($azione."_RIGHE");
		$prg = $keyRighe['EAPRG'];
		
		$valori = wi400Detail::getDetailValues($azione."_CAMPI_RIGA");
		
		if(!$valori['EASTA']) $valori['EASTA'] = "0";
		
		if($_REQUEST['CURRENT_FORM'] == "MODIFICA_RIGA") {
			$rs = $wi400_trigger->updateDettaglioValue($valori, $keyTestata['EXID'], $keyTestata['EXEVENT'], $prg);
			if($rs) {
				$messageContext->addMessage("SUCCESS", "Modifica effettuata con successo.");
			}else {
				$messageContext->addMessage("ERROR", "Errore durante la modifica.");
			}
		}else {
			
			
			$valori['EAID'] = $keyTestata['EXID'];
			$valori['EAEVENT'] = $keyTestata['EXEVENT'];
			
			
			$rs = $wi400_trigger->insertDettaglioValue($valori, $keyTestata['EXID'], $keyTestata['EXEVENT']);
			if($rs) {
				$messageContext->addMessage("SUCCESS", "Nuovo exit point aggiunto con successo.");
			}else {
				$messageContext->addMessage("ERROR", "Errore aggiunta nuovo exit point.");
			}
		}
		
		$actionContext->gotoAction("CLOSE", "CLOSE_WINDOW_MSG", "", true);
	}else if($form == "ELIMINA_RIGA") {
		$keyRighe = getListKeyArray($azione."_RIGHE");
		$prg = $keyRighe['EAPRG'];
		
		$rs = $wi400_trigger->deleteDettaglioValue($keyTestata['EXID'], $keyTestata['EXEVENT'], $prg);
		
		if($rs) {
			$messageContext->addMessage("SUCCESS", "Eliminazione effettuata con successo.");
		}else {
			$messageContext->addMessage("ERROR", "Errore durante l'eliminazione.");
		}
		
		$actionContext->gotoAction($azione, "RIGHE", "", true);
	}else if($form == "ELIMINA_LOG") {
		$keyTestata = getListKeyArray($azione."_TESTATA");
		$keyRighe = getListKeyArray($azione."_RIGHE");
		$prg = $keyRighe['EAPRG'];
		$id = $keyTestata['EXID'];
		$event = $keyTestata['EXEVENT'];
		$sql = "DELETE FROM ZEXILOGA WHERE EAID='$id' AND EAEVENT='$event' AND EAPRG=$prg";
		$db->query($sql);
		$actionContext->gotoAction($azione, "DETTAGLIO_LOG", "", true);
	} else if($form == "RISOTTOMETTI") {
		$keyArray = getListKeyArray($azione."_LOG");
		$event = new wi400ExitPoint();
		$event->resubmitEvent($keyArray['EAINTID']);
		$actionContext->gotoAction($azione, "DETTAGLIO_LOG", "", true);
	}