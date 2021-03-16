<?php

	$spacer = new wi400Spacer();

	if($actionContext->getForm()=="DEFAULT") {
		if($file_exists===false) {
?>
		<script>
			alert("Il file non esiste.");
		</script>
<?		
		}
		else if(empty($parameters)) {
?>
		<script>
			alert("Il file è vuoto.");
		</script>
<?
		}
		
		$actionDetail = new wi400Detail($azione."_DET", true);
		$actionDetail->setColsNum(2);
		
		// Path
		$labelDetail = new wi400Text("FILE");
		$labelDetail->setLabel("File");
		$labelDetail->setValue($file_path);
//		$labelDetail->setLink($appBase."index.php?t=FILEDWN&FILE_NAME=".$file_path."&DECORATION=clean");
		$labelDetail->setLink(create_file_download_link($file_path));
		$actionDetail->addField($labelDetail);
		
		$actionDetail->dispose();
		
		$spacer->dispose();
		
		$actionDetail = new wi400Detail($azione."_PARAMS_DET", true);
		$actionDetail->setTitle("Parametri");
		
		// Sistemi Informativi
		$myField = new wi400InputText('SYS_INF');
		$myField->setLabel("Sistemi informativi");
		$myField->addValidation('required');
		$myField->setValue(array_keys($parameters));
		$myField->setCase("UPPER");
		$myField->setShowMultiple(true);
//		$myField->setSortMultiple(true);

		$decodeParameters = array(
			'TYPE' => 'common',
			'TABLE_NAME' => $settings['lib_architect']."/JSINF01L",
			'COLUMN' => 'DSSIAF',
			'KEY_FIELD_NAME' => 'SINFAF',
			'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE",$settings['lib_architect']."/JSINF01L");
		$myLookUp->addParameter("CAMPO","SINFAF");
		$myLookUp->addParameter("DESCRIZIONE","DSSIAF");
		$myField->setLookUp($myLookUp);

		$actionDetail->addField($myField);
		
		// Salva
		$myButton = new wi400InputButton('SAVE_BUTTON');
		$myButton->setLabel("Salva");
		$myButton->setAction($azione);
		$myButton->setForm("SAVE");
		$myButton->setConfirmMessage("Salvare?");
		$myButton->setValidation(true);
		
		$actionDetail->addButton($myButton);
		
		$actionDetail->dispose();
	}
