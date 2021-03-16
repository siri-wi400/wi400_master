<?php

	if(in_array($actionContext->getForm(), array("DEFAULT", "LIST"))) {
		if($actionContext->getForm()=="DEFAULT") {
			$searchAction = new wi400Detail($azione."_SRC", true);
			$searchAction->setTitle($label);
			$searchAction->setSaveDetail(true);
			$readonly = false;
		}
		else if($actionContext->getForm()=="LIST") {
			$searchAction = new wi400Detail($azione."_LIST_SRC", false);
			$searchAction->setTitle("Parametri");
//			$searchAction->setShowTopButtons(true);
			$searchAction->setColsNum(2);
			$readonly = true;
		}
		
		$searchAction->isEditable(true);
/*		
		$myField = new wi400InputFile("FILE_1");
		$myField->setLabel("File Settings 1");
		$myField->addValidation("required");
		$myField->setAcceptFile(".php, .PHP");
		$myField->addValidation("required");
		$searchAction->addField($myField);
		
		$myField = new wi400InputFile("FILE_2");
		$myField->setLabel("File Settings 2");
		$myField->addValidation("required");
		$myField->setAcceptFile(".php, .PHP");
		$searchAction->addField($myField);
*/
		$myField = new wi400InputText('FILE_1');
		$myField->setLabel("File Settings 1");
		$myField->setSize(100);
		$myField->setMaxLength(300);
		$myField->setReadonly($readonly);
		$myField->setValue($file_1);
		$myField->addValidation("required");
		$searchAction->addField($myField);
		
		$myField = new wi400InputText('FILE_2');
		$myField->setLabel("File Settings 2");
		$myField->setSize(100);
		$myField->setMaxLength(300);
		$myField->setReadonly($readonly);
		$myField->setValue($file_2);
		$searchAction->addField($myField);
		
		if($actionContext->getForm()=="DEFAULT") {
			$myButton = new wi400InputButton('SEARCH_BUTTON');
			$myButton->setLabel("Seleziona");
			$myButton->setAction($azione);
			$myButton->setForm("LIST");
			$myButton->setValidation(true);
			$searchAction->addButton($myButton);
		}
		else if($actionContext->getForm()=="LIST") {
			foreach($params_1 as $par => $val) {
				if(in_array($par, $array_params)) {
					if(!empty($file_1)) {
						$myField = new wi400InputTextArea($par);
						$myField->setLabel($par);
						$myField->setSize(100);
						$myField->setRows(5);
						$myField->setValue($val);
//						$myField->addValidation("required");
						$searchAction->addField($myField);
					}
						
					if(!empty($file_2)) {
						$myField = new wi400InputTextArea($par."_2");
						$myField->setLabel($par);
						$myField->setSize(100);
						$myField->setRows(5);
						if(array_key_exists($par, $params_2))
							$myField->setValue($params_2[$par]);
//						$myField->addValidation("required");
						$searchAction->addField($myField);
					
						unset($params_2[$par]);
					}
				}
				else {
					if(!empty($file_1)) {
						$myField = new wi400InputText($par);
						$myField->setLabel($par);
						$myField->setSize(100);
						$myField->setMaxLength(300);
						$myField->setValue($val);
//						$myField->addValidation("required");
						$searchAction->addField($myField);
					}
					
					if(!empty($file_2)) {
						$myField = new wi400InputText($par."_2");
						$myField->setLabel($par);
						$myField->setSize(100);
						$myField->setMaxLength(300);
						if(array_key_exists($par, $params_2))
							$myField->setValue($params_2[$par]);
						else 
							$myField->setLabel("<font color='red'>".$par."</font>");
//						$myField->addValidation("required");
						$searchAction->addField($myField);
						
						unset($params_2[$par]);
					}
				}
				
				if(empty($file_2)) {
					$myField = new wi400Text("VUOTO");
					$myField->setLabel("");
					$myField->setValue("");
					$searchAction->addField($myField);
				}
			}
			
			if(!empty($file_2) && !empty($params_2)) {
				foreach($params_2 as $par => $val) {
					if(in_array($par, $array_params)) {
							
					}
					else {
						if(!empty($file_1)) {
							$myField = new wi400InputText($par);
							$myField->setLabel("<font color='red'>".$par."</font>");
							$myField->setSize(100);
							$myField->setMaxLength(300);
							$myField->setValue("");
//							$myField->addValidation("required");
							$searchAction->addField($myField);
						}
							
						if(!empty($file_2)) {
							$myField = new wi400InputText($par."_2");
							$myField->setLabel($par);
							$myField->setSize(100);
							$myField->setMaxLength(300);
							$myField->setValue($val);
//							$myField->addValidation("required");
							$searchAction->addField($myField);
						}
					}
				}
			}
/*			
			$myButton = new wi400InputButton('SAVE_BUTTON');
			$myButton->setLabel("Salva");
			$myButton->setAction($azione);
			$myButton->setForm("SAVE");
			$myButton->setConfirmMessage("Salvare?");
			$myButton->setValidation(true);
			$searchAction->addButton($myButton);
*/			
		}
		
		$searchAction->dispose();
	}