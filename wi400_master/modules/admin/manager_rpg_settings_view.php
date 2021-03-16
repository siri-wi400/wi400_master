<?php

	if($actionContext->getForm()=="DEFAULT") {
		$searchAction = new wi400Detail($azione."_SRC", true);
		$searchAction->setTitle($label);
		$searchAction->isEditable(true);
		$searchAction->setSaveDetail(true);
		
		// Modello di conversione
		$myField = new wi400InputText('DATA_AREA');
		$myField->setLabel("Data Area");
		$myField->addValidation("required");
		$myField->setSize(20);
		$myField->setMaxLength(30);
		$myField->setCase("UPPER");
		$myField->setValue($data_area);
		
		$decodeParameters = array(
			'TYPE' => 'common',
			'TABLE_NAME' => "ZDTATABE",
			'COLUMN' => 'DTALIB',
			'KEY_FIELD_NAME' => 'DTANAM',
			'AJAX' => true,
			'COMPLETE' => true,
			'COMPLETE_MIN' => 2,
			'COMPLETE_MAX_RESULT' => 15
		);
		$myField->setDecode($decodeParameters);
/*		
		$myLookUp = new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE", "ZDTATABE");
		$myLookUp->addParameter("CAMPO", "DTANAM");
		$myLookUp->addParameter("DESCRIZIONE", "DTALIB");
//		$myLookUp->addField("MODCONV");
		$myField->setLookUp($myLookUp);
*/		
		$myLookUp = new wi400LookUp("LU_OBJECT");
		$myLookUp->addParameter("OBJTYPE", "*DTAARA");
//		$myLookUp->addParameter("LU_WHERE", "LIBRE!!'/'!!NAME in (select DTALIB!!'/'!!DTANAM from ZDTATABE)");
		$myLookUp->addParameter("LU_FROM_QUERY", "select DTANAM as NAME, DTALIB as LIBRARY from ZDTATABE");
//		$myLookUp->addField("DATA_AREA");
		$myField->setLookUp($myLookUp);
		
		$searchAction->addField($myField);
		
		$myButton = new wi400InputButton('SEARCH_BUTTON');
		$myButton->setLabel("Seleziona");
		$myButton->setAction($azione);
		$myButton->setForm("DETAIL");
		$myButton->setValidation(true);
		$searchAction->addButton($myButton);
		
		$searchAction->dispose();
	}
	else if($actionContext->getForm()=="DETAIL") {
		$detailAction = new wi400Detail($azione."_DET", true);
		$detailAction->setTitle("Dettaglio");
		
		$myField = new wi400InputText('DATA_AREA');
		$myField->setLabel("Data Area");
		$myField->setSize(20);
		$myField->setMaxLength(30);
		$myField->setCase("UPPER");
		$myField->setValue($tabella);
		$myField->setReadonly(true);
		$detailAction->addField($myField);
		
		$c = 1;
		foreach($campi_tab as $cmp => $vals) {
			$label = "";
//			$label .= trim($vals['HEADING']);
//			$label .= " - ";
			$label .= trim($vals['REMARKS']);
			$label .= "<br>(".trim($vals['HEADING']).")";
				
			$len = $vals['LENGTH_PRECISION'];
			
			$valore = data_area_read($tabella, $c, $len);

			$myField = new wi400InputText($cmp);
			$myField->setLabel($label);
			$myField->setSize($len);
			$myField->setMaxLength($len);
			$myField->setValue($valore);
				
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
					$myField->addValidation('date');
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
					$myField->setMask("0123456789,");
					$myField->setInfo("Numero Decimale");
				}
				else {
					$myField->setMask("0123456789");
					$myField->setInfo("Numero Intero");
				}
		
				$align = "right";
			}
			
//			echo "CAMPO: $cmp - INI: $c - LEN: $len - TYPE: $type - VALORE: $valore<br>";
			
			$detailAction->addField($myField);
				
			$c += $len;
		}
		
		$myButton = new wi400InputButton('SAVE_BUTTON');
		$myButton->setLabel("Salva");
		$myButton->setAction($azione);
		$myButton->setForm("SAVE");
		$myButton->setValidation(true);
		$detailAction->addButton($myButton);
		
		$detailAction->dispose();
	}