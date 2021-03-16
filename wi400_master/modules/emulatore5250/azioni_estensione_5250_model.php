<?php
if ($actionContext->getForm() == "USER_PANEL") {
	$tipo_azione = $trigger_param['tipo_azione'];
	if ($tipo_azione=="T" || $tipo_azione=="G") {
		$azioniDetail = $wi400GO->getObject('DETTAGLIO_AZIONE');
		$azioniDetail->addTab("user_5", "Telnet 5250");
		$azionewi = wi400Detail::getDetailValue("SEARCH_ACTION","codazi");
		$sql = "SELECT * FROM FAZI5250 WHERE AZIONEWI=?";
		$stmt = $db->singlePrepare($sql,0,true);
		$result = $db->execute($stmt,array($azionewi));
		$sec = $db->fetch_array($stmt);
		
		$scheda='user_5';
		// Azione di architettura da richiamare
		$myField = new wi400InputText('AZIONE');
		$myField->setLabel("Azione di architettura");
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$myField->setValue($sec['AZIONE']);
		$azioniDetail->addField($myField, $scheda);
		// Programma da richiamare
		$myField = new wi400InputText('PGM');
		$myField->setLabel("Programma");
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$myField->setValue($sec['PGM']);
		$azioniDetail->addField($myField, $scheda);
		// Libreria
		$myField = new wi400InputText('LIBRE');
		$myField->setLabel("Libreria");
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$myField->setValue($sec['LIBRE']);
		$azioniDetail->addField($myField, $scheda);
		// Richiede Video
		$myField = new wi400InputSwitch("VIDEO");
		$myField->setLabel("Richiede Video");
		$myField->setOnLabel(_t('LABEL_YES'));
		$myField->setOffLabel(_t('LABEL_NO'));
		$myField->setValue($sec['VIDEO']);
		$azioniDetail->addField($myField, $scheda);
		// Batch
		$myField = new wi400InputSwitch("BATCH");
		$myField->setLabel("Sottomissione Batch");
		$myField->setOnLabel(_t('LABEL_YES'));
		$myField->setOffLabel(_t('LABEL_NO'));
		$myField->setValue($sec['BATCH']);
		$azioniDetail->addField($myField, $scheda);
		// Conferma Esecuzione
		$myField = new wi400InputSwitch("CONFERMA");
		$myField->setLabel("Conferma Esecuzione");
		$myField->setOnLabel(_t('LABEL_YES'));
		$myField->setOffLabel(_t('LABEL_NO'));
		$myField->setValue($sec['CONFERMA']);
		$myField->setChecked($sec['CONFERMA'] ? true : false);
		$azioniDetail->addField($myField, $scheda);
		// Richiede KPJBA
		$myField = new wi400InputSwitch("ONLYJBU");
		$myField->setLabel("Richiede KPJBA");
		$myField->setOnLabel(_t('LABEL_YES'));
		$myField->setOffLabel(_t('LABEL_NO'));
		$myField->setValue($sec['ONLYJBU']);
		$azioniDetail->addField($myField, $scheda);
		// Sistema Informativo
		$myField = new wi400InputText('SYSINF');
		$myField->setLabel("Sistema Informativo");
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$myField->setValue($sec['SYSINF']);
		$azioniDetail->addField($myField, $scheda);
		// TEMPLATE HTML
		$myField = new wi400InputText('TEMPLATE');
		$myField->setLabel("Template HTML da utilizzare");
		$myField->setSize(30);
		$myField->setMaxLength(30);
		$myField->setValue($sec['TEMPLATE']);
		$azioniDetail->addField($myField, $scheda);
		// KPJBA
		$myField = new wi400InputText('OKPJBU');
		$myField->setLabel("Dati KPJBU in formato Stringa");
		$myField->setSize(256);
		$myField->setMaxLength(256);
		$myField->setValue($sec['OKPJBU']);
		$azioniDetail->addField($myField, $scheda);
		// Bottone si salvataggio
		$myButton = new wi400InputButton('SEARCH_BUTTON');
		$myButton->setLabel("Salva Parametri");
		$myButton->setAction($azione);
		$myButton->setForm("AZIONI_SAVE");
		$myButton->setValidation(True);
		$azioniDetail->addButton($myButton, $scheda);
	}
}	else if ($actionContext->getForm() == "AZIONI_SAVE"){
		$azionewi = wi400Detail::getDetailValue("SEARCH_ACTION","codazi");
		$dati = wi400Detail::getDetailValues("DETTAGLIO_AZIONE");
		$rec = getDS("FAZI5250");
		
		// Valorizzo la DS
		$rec['AZIONEWI']=$azionewi;
		$rec['AZIONE']=$dati['AZIONE'];
		$rec['PGM']=$dati['PGM'];
		$rec['LIBRE']=$dati['LIBRE'];
		$rec['VIDEO']=$dati['VIDEO'];
		$rec['BATCH']=$dati['BATCH'];
		$rec['CONFERMA']=$dati['CONFERMA'];
		$rec['SYSINF']=$dati['SYSINF'];
		$rec['ONLYJBU']=$dati['ONLYJBU'];
		$rec['TEMPLATE']=$dati['TEMPLATE'];
		// Parametri non gestiti
		/*$rec['IDFL01']=$dati['IDFL01'];
		$rec['IDFL02']=$dati['IDFL02'];
		$rec['IDFL03']=$dati['IDFL03'];
		$rec['IDFL04']=$dati['IDFL04'];
		$rec['IDFL05']=$dati['IDFL05'];*/
		$rec['STATO']="1";
		$rec['OKPJBU']=$dati['OKPJBU'];
		// Verifico se esiste
		$sql = "SELECT * FROM FAZI5250 WHERE AZIONEWI=?";
		$stmt = $db->singlePrepare($sql,0,true);
		$result = $db->execute($stmt,array($azionewi));
		$sec = $db->fetch_array($stmt);
		if (!$sec) {
			$stmtTes = $db->prepare("INSERT", "FAZI5250", null, array_keys($rec));
			$result = $db->execute($stmtTes, $rec);
		} else {
			$field = array_keys($rec);
			$key = array("AZIONEWI"=>$azionewi);
			$stmt = $db->prepare('UPDATE', "FAZI5250", $key, $field);
			$result = $db->execute($stmt, $rec);
		}
		// Query di aggiornamento
		$actionContext->onSuccess("TAZIONI","DETAIL");
}	
	
	