<?php

	if($actionContext->getForm()=="DEFAULT") {
		$azioniDetail = new wi400Detail($azione."_DET", true);
		$azioniDetail->setColsNum(2);
		
		$labelDetail = new wi400Text("FROM_FILE");
		$labelDetail->setLabel("Da File");
		$labelDetail->setValue("QUERY_TOOL_LIBERO_SRC");
		$azioniDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("TO_FILE");
		$labelDetail->setLabel("A File");
		$labelDetail->setValue("QUERY_TOOL_SRC");
		$azioniDetail->addField($labelDetail);
		
		$myField = new wi400InputSwitch("OVERWRITE");
		$myField->setLabel("Sovrascrivi Filtri");
		$myField->setOnLabel(_t('LABEL_YES'));
		$myField->setOffLabel(_t('LABEL_NO'));
		$myField->setChecked($check_overwrite);
		$myField->setValue(1);
		$azioniDetail->addField($myField);
		
		$myButton = new wi400InputButton('SEARCH_BUTTON');
		$myButton->setLabel("Migra");
		$myButton->setAction($azione);
		$myButton->setForm("MIGRA");
		$myButton->setValidation(true);
		$azioniDetail->addButton($myButton);
		
		$azioniDetail->dispose();
	}
