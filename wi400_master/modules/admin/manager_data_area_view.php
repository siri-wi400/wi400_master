<?php

	if($actionContext->getForm()=="DEFAULT") {
		$miaLista = new wi400List($azione."_LIST", !$isFromHistory);
		
//		$miaLista->setFrom("ZDTATABE");
		$miaLista->setSubfile($subfile);
		$miaLista->setFrom($subfile->getTable());
//		$miaLista->setOrder("DTANAM");
		$miaLista->setOrder("DTANAM_DES");
		
		$col = new wi400Column("DETTAGLIO", "Dettaglio", "STRING", "center");
		$col->setActionListId("DETTAGLIO");
		$col->setDefaultValue("SEARCH");
		$col->setDecorator("ICONS");
		$col->setExportable(false);
		$col->setSortable(false);
		
		$miaLista->setCols(array(
			$col,
			new wi400Column("DTANAM", "Nome Data Area"),
			new wi400Column("DTALIB", "Libreria Data Area"),
				new wi400Column("DTANAM_DES", "Descrizione Data Area"),
			new wi400Column("DTADS", "Nome DS campi Data Area"),
			new wi400Column("DTADSL", "Libreria DS campi Data Area"),
				new wi400Column("DTADS_DES", "Descrizione DS campi Data Area"),
		));
		
		$miaLista->addKey("DTANAM");
		$miaLista->addKey("DTALIB");
		$miaLista->addKey("DTADS");
		$miaLista->addKey("DTADSL");
		
		// Dettaglio
		$action = new wi400ListAction();
		$action->setId("DETTAGLIO");
		$action->setLabel("Dettaglio");
		$action->setAction($azione);
		$action->setForm("DETAIL");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		// Nuovo
		$action = new wi400ListAction();
		$action->setLabel("Nuovo");
		$action->setAction($azione);
		$action->setForm("NEW_DETAIL");
		$action->setSelection("NONE");
		$miaLista->addAction($action);
		
		// Elimina
		$action = new wi400ListAction();
		$action->setLabel("Elimina");
		$action->setAction($azione);
		$action->setForm("DELETE");
		$action->setSelection("SINGLE");
		$action->setConfirmMessage("Eliminare?");
		$miaLista->addAction($action);
		
		listDispose($miaLista);
		
		$myButton = new wi400InputButton('EXPORT_BUTTON');
		$myButton->setLabel("Nuovo");
		$myButton->setAction($azione);
		$myButton->setForm("NEW_DETAIL");
		$myButton->dispose();
	}
	else if(in_array($actionContext->getForm(), array("NEW_DETAIL", "DETAIL"))) {
		$idDetail = $azione."_".$actionContext->getForm();
		
		$actionDetail = new wi400Detail($idDetail, false);
		
		if(in_array($actionContext->getForm(),array("DETAIL"))) {
			if(!existDetail($idDetail)) {
				// caricamento dei dati della chiamata recuperati dal subfile
				$actionDetail->setSource($row);
			}
		}
		
		// DTANAM
		$myField = new wi400InputText('DTANAM');
		$myField->setLabel('Nome Data Area');
		if($actionContext->getForm()=="DETAIL")
			$myField->setReadonly(true);
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$myField->setCase("UPPER");
		$myField->addValidation("required");
		
		$decodeParameters = array(
			'TYPE' => 'i5_object',
			'OBJTYPE' => '*DTAARA',
			'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_OBJECT");
		$myLookUp->addParameter("OBJTYPE", "*DTAARA");
		$myLookUp->addParameter("LU_WHERE", "NAME not in (select DTANAM from ZDTATABE)");
		$myLookUp->addField("DTANAM");
		$myField->setLookUp($myLookUp);
		
		$actionDetail->addField($myField);
		
		// DTALIB
		$myField = new wi400InputText('DTALIB');
		$myField->setLabel('Libreria Data Area');
//		if($actionContext->getForm()=="DETAIL")
//			$myField->setReadonly(true);
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$myField->setCase("UPPER");
		$myField->addValidation("required");
		
		$decodeParameters = array(
			'TYPE' => 'i5_object',
			'OBJTYPE' => '*LIB',
			'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
/*		
		$myLookUp = new wi400LookUp("LU_OBJECT");
		$myLookUp->addParameter("OBJTYPE", "*LIB");
		$myLookUp->addField("DTALIB");
		$myField->setLookUp($myLookUp);
*/		
		$myLookUp = new wi400LookUp("LU_FILE_LIB");
		$myField->setLookUp($myLookUp);
		
		$actionDetail->addField($myField);
/*		
		if($actionContext->getForm()=="DETAIL") {
			$list = new wi400Os400Object("*DTAARA", $row['DTALIB'], $row['DTANAM']);
			$list->getList();
				
			$des_dta = "";
			while ($obj_read = $list->getEntry()) {
//				echo "DATI:<pre>"; print_r($obj_read); echo "</pre>";
				$des_dta = $obj_read['DESCRIP'];
			}
				
			$fieldDetail = new wi400Text("DES_DATA_AREA");
			$fieldDetail->setLabel("Descrizione Data Area");
			$fieldDetail->setValue($des_dta);
			$actionDetail->addField($fieldDetail);
		}
*/		
		// DTADS
		$myField = new wi400InputText('DTADS');
		$myField->setLabel('Nome DS campi Data Area');
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$myField->setCase("UPPER");
		$myField->addValidation("required");
		
		$decodeParameters = array(
			'TYPE' => 'i5_object',
			'OBJTYPE' => '*FILE',
			'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
/*		
		$myLookUp = new wi400LookUp("LU_OBJECT");
		$myLookUp->addParameter("OBJTYPE", "*FILE");
		$myLookUp->addField("DTADS");
		$myField->setLookUp($myLookUp);
*/		
		$myLookUp = new wi400LookUp("LU_FILE_LIST");
		$myField->setLookUp($myLookUp);
		
		$actionDetail->addField($myField);
		
		// DTADSL
		$myField = new wi400InputText('DTADSL');
		$myField->setLabel('Libreria DS campi Data Area');
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$myField->setCase("UPPER");
		$myField->addValidation("required");
		
		$decodeParameters = array(
			'TYPE' => 'i5_object',
			'OBJTYPE' => '*LIB',
			'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
/*		
		$myLookUp = new wi400LookUp("LU_OBJECT");
		$myLookUp->addParameter("OBJTYPE", "*LIB");
		$myLookUp->addField("DTADSL");
		$myField->setLookUp($myLookUp);
*/		
		$myLookUp = new wi400LookUp("LU_FILE_LIB");
		$myField->setLookUp($myLookUp);
		
		$actionDetail->addField($myField);
/*		
		if($actionContext->getForm()=="DETAIL") {
			$list = new wi400Os400Object("*FILE", $row['DTADSL'], $row['DTADS']);
			$list->getList();
		
			$des_dta = "";
			while ($obj_read = $list->getEntry()) {
//				echo "DATI:<pre>"; print_r($obj_read); echo "</pre>";
				$des_dta = $obj_read['DESCRIP'];
			}
		
			$fieldDetail = new wi400Text("DES_DATA_AREA");
			$fieldDetail->setLabel("Descrizione DS campi Data Area");
			$fieldDetail->setValue($des_dta);
			$actionDetail->addField($fieldDetail);
		}
*/		
		// Salva
		$myButton = new wi400InputButton('SAVE_BUTTON');
		$myButton->setLabel("Salva");
		$myButton->setAction($azione);
		if($actionContext->getForm()=="DETAIL") {
			$myButton->setForm("UPDATE");
			$myButton->setConfirmMessage("Salvare?");
		}
		else if($actionContext->getForm()=="NEW_DETAIL") {
			$myButton->setForm("INSERT");
		}
		$myButton->setValidation(true);
		$actionDetail->addButton($myButton);
		
		if($actionContext->getForm()=="DETAIL") {
			// Elimina
			$myButton = new wi400InputButton('CANCEL_BUTTON');
			$myButton->setLabel("Elimina");
			$myButton->setAction($azione);
			$myButton->setForm("DELETE");
			$myButton->setConfirmMessage("Eliminare?");
			$actionDetail->addButton($myButton);
		}
		
		$actionDetail->dispose();
	}