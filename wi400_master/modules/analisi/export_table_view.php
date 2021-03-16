<?php

	if($actionContext->getForm()=="DEFAULT") {
		if($esporta===false) {
?>
			<script>
				alert("Raggiunto limite di esportazione in XLS/XLSX. Eseguire l'esportazione in formato CSV.");
//				closeLookUp();
			</script>
<?php
		}
		
		$searchAction = new wi400Detail($azione."_SRC", true);
		$searchAction->setColsNum(2);
		$searchAction->setTitle('Parametri');
		$searchAction->isEditable(true);
		$searchAction->setSaveDetail(true);
		
		$myField = new wi400InputText('TABELLA');
		$myField->setLabel("Tabella");
		$myField->addValidation("required");
		$myField->setInfo("Indica la tabella da esportare");
		$myField->setCase('UPPER');
		$myField->setMaxLength(50);
		$myField->setSize(10);
		$myField->setOnChange("doSubmit('".$azione."', 'DEFAULT')");
		$myField->setValue($tabella);
		
		$myLookUp = new wi400LookUp("LU_FILE_LIST");
		$myField->setLookUp($myLookUp);
		
		// @todo ESEGUIRE DECODING
		
		$searchAction->addField($myField);
		
		$myField = new wi400InputText('LIBRERIA');
		$myField->setLabel("Libreria");
//		$myField->addValidation("required");
		$myField->setInfo("Indica la libreria della tabella da esportare");
		$myField->setCase('UPPER');
		$myField->setMaxLength(50);
		$myField->setSize(10);
		$myField->setOnChange("doSubmit('".$azione."', 'DEFAULT')");
		$myField->setValue($libreria);
		
		$myLookUp = new wi400LookUp("LU_FILE_LIB");
		$myField->setLookUp($myLookUp);
		
		// @todo ESEGUIRE DECODING
		
		$searchAction->addField($myField);
		
		// Tipo di conversione
		$mySelect = new wi400InputSelect('TIPO_EXP');
		$mySelect->setLabel("Tipo esportazione");
		$mySelect->addValidation('required');
		$mySelect->setFirstLabel("Seleziona...");
		$mySelect->setOptions($tipo_export_array);
		$mySelect->setValue($exportFormat);
		$searchAction->addField($mySelect);
		
		// Directory da controllare
		$myField = new wi400InputText('TO_PATH');
		$myField->setLabel('Dove');
//		$myField->addValidation('required');
		$myField->setMaxLength(300);
		$myField->setSize(60);
		$myField->setValue($to_path);
		$myField->setInfo("Indica l'indirizzo in cui esportare la tabelle");
		$searchAction->addField($myField);
		
		$myField = new wi400InputSwitch("DES_TITOLI");
		$myField->setLabel("Descrizioni Titoli");
		$myField->setOnLabel(_t('LABEL_YES'));
		$myField->setOffLabel(_t('LABEL_NO'));
		$myField->setChecked($check_des_titoli);
		$myField->setValue(1);
		$searchAction->addField($myField);
		
		$myField = new wi400Text("VUOTO");
		$myField->setLabel("");
		$myField->setValue("");
		$searchAction->addField($myField);
/*		
		$myField = new wi400InputSwitch("NOTIFICA");
		$myField->setLabel("Notifica");
		$myField->setOnLabel(_t('LABEL_YES'));
		$myField->setOffLabel(_t('LABEL_NO'));
		$myField->setChecked($check_notifica);
		$myField->setInfo("Notifica del completamento dell'esportazione");
		$myField->setValue(1);
		$searchAction->addField($myField);
*//*
		$notifica_html = wi400ExportList::addNotificationSelections(true, false);

		$myField = new wi400Text("NOTIFICA_TXT");
		$myField->setLabel("Notifica");
		$myField->setValue($notifica_html['BODY']);
		$searchAction->addField($myField);
*/
		$mySelect = new wi400InputSelect('NOTIFICA');
		$mySelect->setLabel("Notifica");
//		$mySelect->addValidation('required');
		$mySelect->setFirstLabel("Seleziona...");
		$mySelect->setOptions($tipo_notifica_array);
		$mySelect->setValue($notifica);
		$searchAction->addField($mySelect);
				
		$myField = new wi400InputSwitch("ZIP");
		$myField->setLabel(_t("FILE_COMPRESSO"));
		$myField->setOnLabel(_t('LABEL_YES'));
		$myField->setOffLabel(_t('LABEL_NO'));
		$myField->setChecked($check_zip);
		$myField->setValue(1);
		$searchAction->addField($myField);
		
		$myButton = new wi400InputButton('EXPORT_BUTTON');
		$myButton->setLabel("Esporta (Batch)");
		$myButton->setAction($azione);
		$myButton->setForm("EXPORT");
//		$myButton->setTarget("WINDOW");
		$myButton->setValidation(true);
		$myButton->setConfirmMessage("Esportare la tabella in modalità batch?");
		$searchAction->addButton($myButton);
		
		$myButton = new wi400InputButton('EXPORT_DIRETTA');
		$myButton->setLabel("Esporta (Diretta)");
		$myButton->setAction($azione);
		$myButton->setForm("EXPORT_DIRETTA");
		$myButton->setTarget("WINDOW");
		$myButton->setValidation(true);
		$myButton->setConfirmMessage("Esportare la tabella direttamente?");
		$searchAction->addButton($myButton);
		
		$myButton = new wi400InputButton('LIST_BUTTON');
		$myButton->setLabel("Lista");
		$myButton->setAction($azione);
		$myButton->setForm("LIST");
//		$myButton->setTarget("WINDOW");
		$myButton->setValidation(true);
		$searchAction->addButton($myButton);
		
		$searchAction->dispose();
	}
	else if($actionContext->getForm()=="LIST") {
		$from = $tabella;
		if($libreria!="")
			$from = $libreria."/".$tabella;
		
		$miaLista = new wi400List($azione."_LIST", true);
		$miaLista->setFrom($from);
		
		$campi = $db->columns($tabella, "", False, "", $libreria);
//		echo "CAMPI:<pre>"; print_r($campi); echo "</pre>";
		
		if(!empty($campi)) {
			foreach($campi as $key => $vals) {
				$label = $vals['HEADING'];
//				$label = $vals['REMARKS'];
					
//				$len = $vals['LENGTH_PRECISION'];
					
				$tipo = "STRING";
				$align = "left";
				switch($vals['DATA_TYPE_STRING']) {
					case "INTEGER":
						$tipo = "INTEGER";
						$align = "right";
						break;
					case "DECIMAL":
					case "NUMERIC":
					case "FLOAT":
						$dec = $vals['NUM_SCALE'];
						if($dec==0)
							$tipo = "INTEGER";
						else
							$tipo = "DOUBLE_".$dec;
						$align = "right";
						break;
					case "TIMESTAMP":
						$tipo = "COMPLETE_TIMESTAMP";
						break;
					case "DATE";
					$tipo = "SHORT_TIMESTAMP";
					break;
				}
		
				$miaLista->addCol(new wi400Column($key, $label, $tipo, $align));
			}
		}
		
		$miaLista->dispose();
	}
	else if($actionContext->getForm()=="EXPORT_DIRETTA") {
		// ESPORTAZIONE
		$myButton = new wi400InputButton("CLOSE_BUTTON");
		$myButton->setScript('closeLookUp()');
		$myButton->setLabel("Chiudi");
		$buttonsBar[] = $myButton;

		downloadDetail($TypeImage, $filepath, "", _t("ESPORTAZIONE_COMPLETATA"));
	}