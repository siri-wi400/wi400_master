<?php

	$spacer = new wi400Spacer();
	
	if($actionContext->getForm()=="DEFAULT") {
		$importAction = new wi400Detail($idDetail);
		$importAction->setTitle($label);
		$importAction->isEditable(true);
		$importAction->setSaveDetail(true);
		
		$myField = new wi400InputFile("IMPORT_FILE");
		$myField->setLabel("File Excel da importare");
//		$myField->addValidation("required");
		$importAction->addField($myField);
		
		$myField = new wi400InputSwitch("CLEAN_TAB");
		$myField->setLabel("Svuota tabella");
		$myField->setOnLabel(_t('LABEL_YES'));
		$myField->setOffLabel(_t('LABEL_NO'));
		$myField->setChecked($check_clean_tab);
		$myField->setValue(1);
		$importAction->addField($myField);
		
		$mySelect = new wi400InputSelect('TIPO_IMP');
		$mySelect->setLabel("Tipo importazione");
//		$mySelect->setFirstLabel("Seleziona...");
		$mySelect->setOptions($tipo_imp_array);
		$mySelect->setValue($tipo_imp);
		$importAction->addField($mySelect);
		
		$myField = new wi400InputSwitch("TEST_TXT");
		$myField->setLabel("Lettura come file di testo (di CSV o TXT)");
		$myField->setOnLabel(_t('LABEL_YES'));
		$myField->setOffLabel(_t('LABEL_NO'));
		$myField->setChecked($csv_as_txt);
		$importAction->addField($myField);
		
		$myButton = new wi400InputButton('IMPORT_BUTTON');
		$myButton->setLabel(_t('IMPORT'));
		$myButton->setAction($azione);
		$myButton->setForm("IMPORT");
		$myButton->setValidation(true);
//		$myButton->setConfirmMessage(_t('CONFERMA_IMPORT_FOGLIO'));
		$importAction->addButton($myButton);
		
		$myButton = new wi400InputButton('LIST_BUTTON');
		$myButton->setLabel("Visualizza Lista");
		$myButton->setAction($azione);
		$myButton->setForm("LIST");
		$importAction->addButton($myButton);
		
		$importAction->dispose();
	}
	else if($actionContext->getForm()=="LIST") {
		$miaLista = new wi400List($azione."_LIST", !$isFromHistory);
		
		$miaLista->setFrom($from);
		
//		echo "SQL LIST: ".$miaLista->getSql()."<br>";
		
		foreach($campi_tab as $cmp => $vals) {
			$label = $vals['HEADING'];
//			$label = $campi[$cmp]['REMARKS'];
			
			$len = $vals['LENGTH_PRECISION'];
			
			$type = "STRING";
			$dec = "";
			switch($vals['DATA_TYPE_STRING']) {
				case "DECIMAL":
				case "NUMERIC":
				case "INTEGER":
				case "FLOAT":
					$dec = $vals['NUM_SCALE'];
					break;
				case "DATE";
					$type = "DATE";
					break;
				case "TIMESTMP":
					$type = "COMPLETE_TIMESTAMP";
				break;
			}
			
			$align = "left";
			if($dec!="") {
				$type = "INTEGER";
				if($dec!=0) {
//					$type = "DOUBLE_".$dec;
					$type = array("DOUBLE", $dec);
				}
				
				$align = "right";
			}
			
			$miaLista->addCol(new wi400Column($cmp, $label, $type, $align));
			
			$myFilter = new wi400Filter($cmp, $label, $type);
//			$myFilter->setFast(true);
			$myFilter->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
			$miaLista->addFilter($myFilter);
		}
		
		listDispose($miaLista);
	}