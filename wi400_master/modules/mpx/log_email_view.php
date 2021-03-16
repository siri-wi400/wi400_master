<?php

	$spacer = new wi400Spacer();

	if($actionContext->getForm()=="DEFAULT") {
		$FileDetail = new wi400Detail($azione."_DET",true);
		$FileDetail->setColsNum(2);
		
		$labelDetail = new wi400Text("FILENAME");
		$labelDetail->setLabel(_t('FILE_PATH'));
		$labelDetail->setValue($file_path);
//		$labelDetail->setLink($appBase."index.php?t=FILEDWN&FILE_NAME=".$file_path."&DECORATION=clean");
		$labelDetail->setLink(create_file_download_link($file_path));
		$FileDetail->addField($labelDetail);
		
		$size = 0;
		if(file_exists($file_path))
			$size = filesize($file_path);
		
		if(file_exists($file_path) && $size>20000000) {
?>
			<script>
				alert("Il file è troppo grande. Aprire direttamente il file.");
			</script>
<?						
		}
		
		$labelDetail = new wi400Text("FILESIZE");
		$labelDetail->setLabel("Size (Bytes)");
		$labelDetail->setValue($size);
		$FileDetail->addField($labelDetail);
/*		
		if(!file_exists($file_path)) {
?>
			<script>
				alert("File non trovato.");
			</script>
<?				
		}
*/		
		$FileDetail->dispose();
		
		$LogDetail = new wi400Detail('LOG_EMAIL_BODY', true);
		$LogDetail->setTitle('Log e-mail');
		$LogDetail->isEditable(true);
/*		
		// Testo del log del lavoro
		$myField = new wi400InputTextArea('LOG_BODY');
		$myField->setReadonly(true);
		$myField->setSaveSession(false);
//		$myField->setLabel("Log error");
		$myField->setSize(190);
		$myField->setRows(25);
		$myField->setValue($lines);
		$LogDetail->addField($myField);
*/
		// Testo del log del lavoro
		$myField = new wi400TextPanel('LOG_EMAIL_BODY');
		$myField->setValue($lines);
		$LogDetail->addField($myField);
		
		$myButton = new wi400InputButton('RELOAD_BUTTON');
		$myButton->setLabel("Ricarica");
		$myButton->setAction($azione);
		$myButton->setForm("DEFAULT");
		$LogDetail->addButton($myButton);
		
		$myButton = new wi400InputButton('DELETE_BUTTON');
		$myButton->setLabel("Pulisci file");
		$myButton->setAction($azione);
		$myButton->setForm("DELETE_FILE");
		$LogDetail->addButton($myButton);
		
		$LogDetail->dispose();
	}

?>