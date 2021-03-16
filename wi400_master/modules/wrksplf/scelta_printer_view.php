<?php

	if($actionContext->getForm()=="PRINTER_SEL") {
		$actionDetail = new wi400Detail($azione.'_SCELTA_PRINTER', True);
		
		$myField = new wi400InputText('OUTQ');
		$myField->setLabel("OUTQ");
		$myField->setInfo(_t('SPOOL_OUTQ_INFO'));
		$myField->setOnChange("doSubmit('".$azione."', 'PRINTER_SEL')");
		
		if(isset($outq) && $outq!="")
			$myField->setValue($outq);
		
		$myField->setCase("UPPER");
//		$myField->addValidation('required');
		
		if ($settings['platform']=="AS400") {
			$myLookUp = new wi400LookUp("LU_OBJECT");
			$myLookUp->addField("OUTQ");
			$myLookUp->addParameter("OBJTYPE", "*OUTQ");
//			$myLookUp->addParameter("ONCHANGE", "doSubmit('".$azione."', 'DEFAULT')");
//			$myLookUp->addParameter("ONCHANGE", "top.doSubmit('".$azione."', 'DEFAULT')");
//			$myLookUp->addParameter("ONCHANGE", "window.opener.doSubmit('".$azione."', 'DEFAULT')");
//			$myLookUp->addParameter("ONCHANGE", "risottomettiForm('DEFAULT')");
//			$myLookUp->addParameter("ONCHANGE", "risottomettiForm('DEFAULT', '".$azione."')");
//			$myLookUp->addParameter("ONCHANGE", "reloadSelAction('".$azione."', 'DEFAULT')");
			$myField->setLookUp($myLookUp);
			
			$decodeParameters = array(
				'TYPE' => 'i5_object',
				'OBJTYPE' => '*OUTQ',
				'AJAX' => true
			);
		
			$myField->setDecode($decodeParameters);
		} else {
			$myLookUp = new wi400LookUp("LU_GENERICO");
			$myLookUp->addParameter("DIRECT_SQL",base64_encode("SELECT DISTINCT ELEMENTO,ELEMENTO2 FROM TRI_TABTABE WHERE TABELLA='CODE_STAMPA'"));
			$myLookUp->addParameter("CAMPO","ELEMENTO");
			$myLookUp->addParameter("DESCRIZIONE","ELEMENTO2");
			$myLookUp->addParameter("LU_ORDER","VALORE");
			$myLookUp->addParameter("LU_KEY","ELEMENTO");
			$myLookUp->addParameter("TITLE", "Seleziona Stampante");
			$myField->setLookUp($myLookUp);
		}
		
		$actionDetail->addField($myField);
		
		$myField = new wi400InputText('COPIE');
		$myField->setMaxLength(1);
		$myField->setSize(1);
		$myField->setMask("1234567890");
		$myField->setLabel("Copie");
		$myField->setValue($copie);
		$myField->setOnChange("doSubmit('".$azione."', 'PRINTER_SEL')");
		$actionDetail->addField($myField);
/*		
		if($default!==false) {
			if($default=="GEN") {
				$tipo_imp = "Stampante di Default";
			}
			else if($default=="USER") {
				$tipo_imp = "Stampante di Default Utente";
			}
			
			$labelDetail = new wi400Text("DEFAULT");
			$labelDetail->setLabel("Tipo Impostazione");
			$labelDetail->setValue($tipo_imp);
			$actionDetail->addField($labelDetail);
		}
*/		
		if($outq!="") {
			$myButton = new wi400InputButton('SAVE_BUTTON');
			$myButton->setLabel("Seleziona");
			$myButton->setAction($azione);
			$myButton->setForm("SAVE_PRINTER");
			$myButton->setConfirmMessage("Selezionare la stampante?");
			$myButton->setValidation(true);
			$actionDetail->addButton($myButton);
		}
		
		//if(($coda_array['CODA_USER']!="" && $outq!=$coda_array['CODA_USER']) ||
		//	($coda_array['CODA_USER']=="" && $outq!=$coda_array['CODA_DEF'])
		//) {
//		if($default!="GEN") {
			$myButton = new wi400InputButton('DEFAULT_BUTTON');
			if($outq=="" && $coda_array['CODA_USER']!="" && $coda_array['CODA_USER']!=$coda_array['CODA_DEF']) {
				$myButton->setLabel("Reimpostare il default originale");
			}
			else {
				$myButton->setLabel("Impostare come default");
			}
			$myButton->setAction($azione);
			$myButton->setForm("DEFAULT_PRINTER");
			$myButton->setConfirmMessage("Impostare come stampante di default?");
			$myButton->setValidation(true);
			$actionDetail->addButton($myButton);
//		}
		
			$myButton = new wi400InputButton('RELOAD_BUTTON');
			$myButton->setLabel("Resetta Selezione");
			$myButton->setAction($azione);
			$myButton->setForm("RESET_PRINTER");
			$myButton->setConfirmMessage("Resettare la stampante di default?");
//			$myButton->setValidation(true);
			$actionDetail->addButton($myButton);
		//}
		
		$myButton = new wi400InputButton('BACK_BUTTON');
		$myButton->setLabel("Chiudi");
		$myButton->setScript('closeLookUp()');
		$actionDetail->addButton($myButton);
		
		if($coda_def!="")
			$actionDetail->addParameter("DEF_PRINT", $coda_def);
		
		$actionDetail->dispose();
	}