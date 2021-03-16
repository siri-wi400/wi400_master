<?php

	if($actionContext->getForm()=="DEFAULT") {
		$importAction = new wi400Detail("IMPORT_SRC", true);
//		$importAction->setColsNum(2);
		
		$myField = new wi400InputFile("IMPORT_FILE");
		$myField->setLabel("Testata Messaggistica");
		$importAction->addField($myField);
		
		$button = new wi400InputButton("IMPORT_BUTTON");
		$button->setLabel("Importa");
		$button->setAction($azione);
		$button->setForm("IMPORT");
		$importAction->addButton($button);
		
		$importAction->dispose();
		
		echo "<br>";
		
		$importAction = new wi400Detail("IMPORT_SRC", true);
		$files_path = $settings['template_path']."Inserimento Messaggi.xlsm";
		$labelDetail = new wi400Text("SCARICA_TEMPLATE");
		$labelDetail->setLabel("Scarica template");
		$labelDetail->setValue("Inserimento Messaggi Template");
		$labelDetail->setLink(create_file_download_link($files_path));
		$importAction->addField($labelDetail);
		
		$importAction->dispose();
	}