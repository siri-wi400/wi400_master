<?php

	if(in_array($actionContext->getForm(), array("MOD_OTM", "NEW_OTM"))) {
		validation_otm();
	}
	
	function validation_otm() {
		global $messageContext;
		global $actionContext;
		global $db;
		
		$user = trim($_POST['OTMUSR']);
		
		if(!empty(trim($_POST['OTMID']))) {
			if($actionContext->getForm()=="NEW_OTM") {
				if($_POST['OTMTYP']!="STATIC")
					$messageContext->addMessage("ERROR", "Le OTM con nome NON automatico devono essere di tipo STATIC", "OTMTYP",true);
			}
			
			$sql = "select OTMID, OTMUSR from SIR_OTM where OTMID='".trim($_POST['OTMID'])."'";
//			$sql .= "' and OTMUSR<>'".trim($user)."'";
			$res = $db->singleQuery($sql);
			if($row = $db->fetch_array($res)) {
				if($row['OTMUSR']!==$user) {
					$messageContext->addMessage("ERROR", "OTM già utilizzata per un'altro utente", "",true);
				}
				else {
					if($actionContext->getForm()=="NEW_OTM")	
						$messageContext->addMessage("ERROR", "OTM già utilizzata per lo stesso utente", "",true);
				}
			}
		}
	}
