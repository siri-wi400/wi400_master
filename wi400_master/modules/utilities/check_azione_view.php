<?php

	if($actionContext->getForm()=="DEFAULT") {
		$searchAction = new wi400Detail($azione.'_SRC');
		$searchAction->setTitle("Controllo Azioni");
		$searchAction->setSaveDetail(true);
		$searchAction->isEditable(true);
				
		// Gestione delle azioni
		if($actionContext->getGateway()!="CHECK_AZIONE") {
		$myField = new wi400InputText('CODAZI');
		$myField->setLabel(_t("CODE"));
		$myField->addValidation("required");
		$myField->setCase("UPPER");
		$myField->setMaxLength(40);
		//$myField->setValue($azione);
		$myField->setInfo(_t("ACTION_CODE_INFO"));
		
		$decodeParameters = array(
				'TYPE'=> 'common',
				'COLUMN' => 'DESCRIZIONE',
				'TABLE_NAME' => 'FAZISIRI',
				'KEY_FIELD_NAME' => 'AZIONE',
				'ALLOW_NEW' => True,
				'AJAX' => true,
				'COMPLETE' => true,
				'COMPLETE_MIN' => 2,
				'COMPLETE_MAX_RESULT' => 15
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_AZIONI");
		//		$myLookUp->addField("codazi");
		$customTool = new wi400CustomTool("TRI_ANASOC", "NUOVO");
		$customTool->addParameter("RETURN_ID", "codazi");
		$customTool->addParameter("RETURN_DETAIL", "SEARCH_ACTION");
		$customTool->setIco("themes/common/images/table-select-row.png");
		$customTool->setTarget("WINDOW");
		$myField->addCustomTool($customTool);
		$myField->setLookUp($myLookUp);
		
		$searchAction->addField($myField);
		
		$myButton = new wi400InputButton('CHECK_AZIONE');
		$myButton->setLabel("Check");
		$myButton->setAction($azione);
		$myButton->setForm("CHECK_AZIONE");
		$myButton->setValidation(true);
		$searchAction->addButton($myButton);
		
		$searchAction->dispose();
			}
		}
	elseif($actionContext->getForm()=="CHECK_AZIONE") {
		$searchAction = new wi400Detail($azione.'_CHK');
		$searchAction->setTitle("Create Directory");
		$searchAction->setSaveDetail(true);
		$searchAction->isEditable(true);
//		showArray($WI4_CHECK_MESSAGE);
		foreach ($WI4_CHECK_MESSAGE as $message)
		{
			echo $message."</br>";
		}

		if ($array_create_dir){
			
			$myField = new wi400Text('CREATE_DIRE');
			$myField->setLabel("Create Directory");
			$myField->setValue(implode("</br>",$array_create_dir));
			$searchAction->addField($myField);

			$myButton = new wi400InputButton('CREATE_DIR');
			$myButton->setLabel("Create Directory");
			$myButton->setAction($azione);
			$myButton->addParameter("ARRAY_DIR", implode(";",$array_create_dir));
			$myButton->setForm("CREATE_DIR");
			$myButton->setValidation(true);
			$searchAction->addButton($myButton);
			
			$searchAction->dispose();
			
		}
	}