<?php 

	$spacer = new wi400Spacer();

	if(in_array($actionContext->getForm(),array("DEFAULT","LIST"))) {
		$importAction = new wi400Detail($azione.'_SRC', True);
		$importAction->setColsNum(2);
		$importAction->setTitle('Parametri');
		$importAction->isEditable(true);
		$importAction->setSaveDetail(true);
		
		$myField = new wi400InputFile("IMPORT_FILE");
		$myField->setLabel("Importa dati da file:");
		$importAction->addField($myField);
		
		$myField = new wi400Text('VUOTO');
		$myField->setLabel("");
		$myField->setValue("");
		$importAction->addField($myField);
		
		$myField = new wi400InputText('TABELLA');
		$myField->setLabel("Tabella");
		$myField->addValidation("required");
		$myField->setInfo("Indica la tabella in cui importare i dati");
		$myField->setCase('UPPER');
		$myField->setMaxLength(50);
		$myField->setSize(50);
		$myField->setValue($tabella);
		$importAction->addField($myField);
		
		$myField = new wi400InputText('LIBRERIA');
		$myField->setLabel("Libreria");
//		$myField->addValidation("required");
		$myField->setInfo("Indica la libreria della tabella in cui importare i dati");
		$myField->setCase('UPPER');
		$myField->setMaxLength(50);
		$myField->setSize(50);
		$myField->setValue($libreria);
		$importAction->addField($myField);
		
		$myField = new wi400InputText('START_ROW');
		$myField->setLabel("Riga Dati");
//		$myField->addValidation("required");
		$myField->setMaxLength(4);
		$myField->setSize(4);
		$myField->setMask("1234567890");
		if($start_row!="") {
			$myField->setValue($start_row);
		}
		$importAction->addField($myField);
		
		$myField = new wi400InputText('FOGLIO');
		$myField->setLabel("Foglio");
//		$myField->addValidation("required");
		$myField->setInfo("Indica il foglio da cui importare i dati");
//		$myField->setCase('UPPER');
		$myField->setMaxLength(50);
		$myField->setSize(50);
		$myField->setValue($foglio);
		$importAction->addField($myField);
		
		$myField = new wi400InputText('COLONNA');
		$myField->setLabel("Colonna (A,B,C...)");
		$myField->setShowMultiple(true);
		$myField->setSortMultiple(true);
//		$myField->addValidation("required");
		$myField->setInfo("Indica le colonne da importare");
		$myField->setCase('UPPER');
		$myField->setMaxLength(3);
		$myField->setSize(3);
		$myField->setValue($colonne_array);
		$importAction->addField($myField);
		
		$myField = new wi400InputText('CAMPO');
		$myField->setLabel("Campo");
		$myField->setShowMultiple(true);
		$myField->setSortMultiple(true);
//		$myField->addValidation("required");
		$myField->setInfo("Indica i campi corrispondenti alle colonne in cui importare i dati");
		$myField->setCase('UPPER');
		$myField->setMaxLength(20);
		$myField->setSize(20);
		$myField->setValue($campi_array);
		$importAction->addField($myField);
		
		$myField = new wi400InputSwitch("IGNORA_STRUTTURA");
		$myField->setLabel("Ignora la struttura della tabella");
		$myField->setOnLabel("SI");
		$myField->setOffLabel("NO");
		$importAction->addField($myField);
		
		$myField = new wi400InputSwitch("CLEAR_FILE");
		$myField->setLabel("Pulizia dei dati già presenti");
		$myField->setOnLabel("SI");
		$myField->setOffLabel("NO");		
		$importAction->addField($myField);
		
		$myButton = new wi400InputButton('IMPORT_BUTTON');
		$myButton->setLabel("Importa");
		$myButton->setAction($azione);
		$myButton->setForm("IMPORT");
		$myButton->setValidation(true);
		$importAction->addButton($myButton);
		
		$myButton = new wi400InputButton('LIST_BUTTON');
		$myButton->setLabel("Visualizza lista");
		$myButton->setAction($azione);
		$myButton->setForm("LIST");
		$myButton->setValidation(true);
		$importAction->addButton($myButton);
		
		$importAction->dispose();
	}

	if($actionContext->getForm()=="LIST") {
		$spacer->dispose();
		
		// Inizializzazione lista
		$miaLista = new wi400List($azione."_LIST", true);
		if(!empty($campi_array))
			$miaLista->setField(implode(",", $campi_array));
		$miaLista->setFrom($from);
		$miaLista->setSelection("SINGLE");
		
//		echo "SQL: ".$miaLista->getSql()."<br>";
		
		if($libreria=="") {
			$campi = getColumnListFromTable($tabella);
		}
		else
			$campi = getColumnListFromTable($tabella, $libreria);
		
		if(!empty($campi_array)) {
			foreach($campi as $key => $val) {
				if(!in_array($key,$campi_array)) {
					unset($campi[$key]);
				}
			}
		}
//		echo "CAMPI:<pre>"; print_r($campi); echo "</pre>";
		
		$miaLista->setCols($campi);
		
		$miaLista->dispose();
	}

?>