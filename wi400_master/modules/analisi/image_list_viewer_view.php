<?php

	$spacer = new wi400Spacer();

	if($actionContext->getForm()=="DEFAULT") {
		$searchAction = new wi400Detail($azione."_SRC", true);
		$searchAction->setTitle($label);
		$searchAction->isEditable(true);
		$searchAction->setSaveDetail(true);
		
		// Path
		$myField = new wi400InputText('IMAGE_PATH');
		$myField->setLabel('Path Immagine');
		$myField->setShowMultiple(true);
		$myField->setSize(100);
		$myField->setMaxLength(300);
//		$myField->setCase("UPPER");
		$myField->setValue($image_path_array);
		$searchAction->addField($myField);
		
		// Remote Path
		$myField = new wi400InputText('REMOTE_PATH');
		$myField->setLabel('Path Remoto');
		$myField->setSize(100);
		$myField->setMaxLength(300);
//		$myField->setCase("UPPER");
		$myField->setValue($remote_path);
		$searchAction->addField($myField);
		
		// Exclude Types
		$myField = new wi400InputText('EXCLUDE_TYPES');
		$myField->setLabel('Tipi da escludere');
		$myField->setShowMultiple(true);
		$myField->setSize(10);
		$myField->setMaxLength(20);
		$myField->setCase("UPPER");
		$myField->setValue($exclude_types);
		$searchAction->addField($myField);
		
		// Include Only Types
		$myField = new wi400InputText('INCLUDE_ONLY_TYPES');
		$myField->setLabel('Tipi da considerare');
		$myField->setShowMultiple(true);
		$myField->setSize(10);
		$myField->setMaxLength(20);
		$myField->setCase("UPPER");
		$myField->setValue($include_only_types);
		$searchAction->addField($myField);
		
		$myButton = new wi400InputButton('SEARCH_BUTTON');
		$myButton->setLabel("Seleziona");
		$myButton->setAction($azione);
		$myButton->setForm("LIST");
		$myButton->setValidation(true);
		$searchAction->addButton($myButton);
		
		$searchAction->dispose();
	}
	else if($actionContext->getForm()=="LIST") {
/*		
		$FileDetail = new wi400Detail($azione."_DET",true);
		$FileDetail->setColsNum(2);
		
		$labelDetail = new wi400Text("FILENAME");
		$labelDetail->setLabel(_t('FILE_PATH'));
		$labelDetail->setValue(implode("<br>", $image_path_array));
		$FileDetail->addField($labelDetail);
		
		$FileDetail->dispose();
		
		$spacer->dispose();
*/		
		// Inizializzazione lista
		$miaLista = new wi400List($azione."_LIST", !$isFromHistory);
		$miaLista->setSubfile($subfile);
		$miaLista->setFrom($subfile->getTable());
		$miaLista->setOrder("TIPO desc, FILE desc");
		$miaLista->setSelection("MULTIPLE");
		
//		$miaLista->setCalculateTotalRows('FALSE');
		
		$file_col = new wi400Column("FILE","File");
		$file_col->setDetailAction($azione, "FILE_PRV");
		
		$img_col = new wi400Column("IMMAGINE", "Immagine", "STRING", "center");
		$img_col->setDefaultValue('EVAL:get_file_image($row["IMG_PATH"], "", "", true, 50)');
		$img_col->setSortable(false);
		
		$miaLista->setCols(array(
			$file_col,
			new wi400Column("TIPO","Tipo"),
			new wi400Column("DIMENSIONE","Dimensione (Bytes)", "INTEGER", "right"),
//			new wi400Column("IMG_PATH","Path Immagine"),
			$img_col
		));
		
		$miaLista->addKey("FILE");
		
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("DELETE_FILES");
		$action->setLabel("Elimina files");
		$action->setSelection("MULTIPLE");
		$miaLista->addAction($action);
		
		$miaLista->dispose();
	}
	else if($actionContext->getForm()=="FILE_PRV") {
		$TypeImage = "";

		$file_parts = pathinfo($file_path);
		if(isset($file_parts['extension']))
			$TypeImage = strtolower($file_parts['extension']);
				
		downloadDetail($TypeImage, $file_path, "", "Esportazione completata");
		
		$myButton = new wi400InputButton("CLOSE_BUTTON");
		$myButton->setScript('closeLookUp()');
		$myButton->setLabel("Chiudi");
		$buttonsBar[] = $myButton;
	}