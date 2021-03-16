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
		
		if(file_exists($file_path) && $size>20000000) {
?>
			<script>
				alert("Il file à troppo grande. Aprire direttamente il file.");
			</script>
<?						
		}
		
		$labelDetail = new wi400Text("FILESIZE");
		$labelDetail->setLabel(_t('FILE_SIZE'));
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
		
		$LogDetail = new wi400Detail('LOG_BODY_DET', true);
		$LogDetail->setTitle('Log error');
		$LogDetail->isEditable(true);
/*		
		Testo del log del lavoro
		$myField = new wi400InputTextArea('LOG_BODY');
		$myField->setReadonly(true);
		$myField->setSaveSession(false);
		$myField->setSize(190);
		$myField->setRows(25);
		$myField->setValue($lines);
		$LogDetail->addField($myField);
*/		
		if(isset($path_parts['extension'])) {
			if($path_parts['extension']=="xml") {
				$myField = new wi400InputTextArea('LOG_BODY');
				$myField->setReadonly(true);
//				$myField->setSaveSession(false);
				$myField->setSize(190);
				$myField->setRows(25);
				$myField->setValue($lines);
				$LogDetail->addField($myField);
			}			
			else {
				// Testo del log del lavoro
				$myField = new wi400TextPanel('LOG_BODY');
				$myField->setHeight(400);
				$myField->setValue($lines);
				$myField->setWidthParent(true);
				$LogDetail->addField($myField);
			}
		}
		
		if(!isset($disable) || (isset($disable) && !in_array("RELOAD_BUTTON", $disable))) {
			$myButton = new wi400InputButton('RELOAD_BUTTON');
			$myButton->setLabel(_t('REFRESH'));
			$myButton->setAction($azione);
			$myButton->setForm($actionContext->getForm());
			$LogDetail->addButton($myButton);
		}
		
		if(!isset($disable) || (isset($disable) && !in_array("DELETE_BUTTON", $disable))) {
			$myButton = new wi400InputButton('DELETE_BUTTON');
			$myButton->setLabel(_t('FILE_CLEAR'));
			$myButton->setAction($azione);
			$myButton->setForm("DELETE_FILE");
			$LogDetail->addButton($myButton);
		}
		
//		echo "EXTRA BUTTONS:<pre>"; print_r($extraButtons); echo "</pre>";
		if(isset($extraButtons) && !empty($extraButtons)) {
			foreach($extraButtons as $button) {
				$myButton = new wi400InputButton($button['ID']);
				$myButton->setLabel($button['LABEL']);
				$myButton->setAction($button['ACTION']);
				$myButton->setForm($button['FORM']);
				if(isset($button['TARGET']))
					$myButton->setTarget($button['TARGET']);
				$LogDetail->addButton($myButton);
			}
		}
		
		if($azione=="LOG_VIEWER") {
			$myButton = new wi400InputButton('BACK_BUTTON');
			$myButton->setLabel(_t('INDIETRO'));
			$myButton->setAction($last_action);
			$myButton->setForm($last_form);
			$LogDetail->addButton($myButton);
		}	
		
		$LogDetail->dispose();
		
?>
		<script>
			jQuery(document).ready(function () {
				jQuery('.wi400TextPanel').scrollTop('9999999');
			});
		</script>
<?php 
	}