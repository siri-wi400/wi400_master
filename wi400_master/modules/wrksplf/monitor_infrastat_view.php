<?php

	$spacer = new wi400Spacer();
	
	if($actionContext->getForm()=="DEFAULT") {
		$searchAction = new wi400Detail($azione."_SRC", true);
		$searchAction->setTitle($label);
		$searchAction->isEditable(true);
//		$searchAction->setSaveDetail(true);
/*
		// Data inizio
		$myField = new wi400InputText('DATA_RIF_INI');
		$myField->setLabel('Data di riferimento iniziale');
		$myField->addValidation('date');
		$myField->addValidation('required');
		if(!isset($data_rif_ini))
			$myField->setValue(dateModelToView(date("Ymd", mktime(0, 0, 0, date('m')-1, 1, date("Y")))));
		else
			$myField->setValue($data_rif_ini);
		$searchAction->addField($myField);
		
		// Data fine
		$myField = new wi400InputText('DATA_RIF_FIN');
		$myField->setLabel('Data di riferimento finale');
		$myField->addValidation('date');
		$myField->addValidation('required');
		if(!isset($data_rif_fin))
			$myField->setValue(dateModelToView(date("Ymd", mktime(0, 0, 0, date('m'), 0, date("Y")))));
		else
			$myField->setValue($data_rif_fin);
		$searchAction->addField($myField);
*/
		// Anno inizio
		$myField = new wi400InputText('ANNO_SRC_INI');
		$myField->setLabel(_t("ANNO_INIZIO"));
		$myField->addValidation("required");
		$myField->setMaxLength(4);
		$myField->setSize(4);
		$myField->setMask("1234567890");
		$myField->setValue($anno_rif_ini);
		$myField->setInfo(_t("Inserire ANNO INIZIO e MESE INIZIO assieme"));
		$searchAction->addField($myField);
		
		// Mese inizio
		$mySelect = new wi400InputSelect("MESE_SRC_INI");
		$mySelect->setLabel(_t("MESE_INIZIO"));
		$mySelect->addValidation("required");
		$mySelect->setFirstLabel(_t('SELEZIONA')."...");
		for($m=1; $m<=12; $m++) {
			$mySelect->addOption(ucfirst(nome_mese($m)), $m);
		}
		$mySelect->setValue($mese_rif_ini);
		$myField->setInfo(_t("Inserire ANNO INIZIO e MESE INIZIO assieme"));
		$searchAction->addField($mySelect);
		
		// Anno fine
		$myField = new wi400InputText('ANNO_SRC_FIN');
		$myField->setLabel(_t("ANNO_FINE"));
		$myField->addValidation("required");
		$myField->setMaxLength(4);
		$myField->setSize(4);
		$myField->setMask("1234567890");
		$myField->setValue($anno_rif_fin);
		$myField->setInfo(_t("Inserire ANNO FINE e MESE FINE assieme"));
		$searchAction->addField($myField);
		
		// Mese fine
		$mySelect = new wi400InputSelect("MESE_SRC_FIN");
		$mySelect->setLabel(_t("MESE_FINE"));
		$mySelect->addValidation("required");
		$mySelect->setFirstLabel(_t('SELEZIONA')."...");
		for($m=1; $m<=12; $m++) {
			$mySelect->addOption(ucfirst(nome_mese($m)), $m);
		}
		$mySelect->setValue($mese_rif_fin);
		$myField->setInfo(_t("Inserire ANNO FINE e MESE FINE assieme"));
		$searchAction->addField($mySelect);
		
		$myButton = new wi400InputButton('SEARCH_BUTTON');
		$myButton->setLabel("Seleziona");
		$myButton->setAction($azione);
		$myButton->setForm("LIST");
		$myButton->setValidation(true);
		$searchAction->addButton($myButton);
		
		$searchAction->dispose();
	}
	else if($actionContext->getForm()=="LIST") {
		$ListDetail = new wi400Detail($azione."_".$actionContext->getForm()."_DET");
		$ListDetail->setColsNum(2);
/*		
		$fieldDetail = new wi400Text("PERIODO");
		$fieldDetail->setLabel("Periodo di riferimento");
		$fieldDetail->setValue("Dal ".$data_rif_ini." al ".$data_rif_fin);
		$ListDetail->addField($fieldDetail);
*/
		$fieldDetail = new wi400Text("PERIODO_RIF");
		$fieldDetail->setLabel(_t("PERIODO_RIFERIMENTO"));
		$fieldDetail->setValue(_t("DA_A", array(nome_mese($mese_rif_ini)." ".$anno_rif_ini, nome_mese($mese_rif_fin)." ".$anno_rif_fin)));
		$ListDetail->addField($fieldDetail);
		
		$ListDetail->dispose();
		
		$spacer->dispose();
		
		$miaLista = new wi400List($azione."_LIST", !$isFromHistory);
/*		
		$miaLista->setField($select);
		$miaLista->setFrom($from);
		$miaLista->setWhere($where);
*/
		$miaLista->setSubfile($subfile);
		$miaLista->setFrom($subfile->getTable());
//		$miaLista->setOrder("DATA_BOL desc");
		
//		echo "SQL: ".$miaLista->getSql()."<br>";
/*		
		$col_des_loc = new wi400Column("DES_LOC", "Descrizione Locale");
		$col_des_loc->setDefaultValue('EVAL:get_campo_ente($row["FABCD1"],'.dateViewToModel($data_rif).',"MAFDSE")');
		$col_des_loc->setSortable(false);
		
		$col_des_art = new wi400Column("DES_ART", "Descrizione Articolo");
		$col_des_art->setDefaultValue('EVAL:get_campo_articolo($row["FABCDA"],'.dateViewToModel($data_rif).',"MDADSA")');
		$col_des_art->setSortable(false);
*/		
		$miaLista->setCols(array(
			new wi400Column("FABCD1", "Locale"),
			new wi400Column("MAFDSE", "Descrizione Locale"),
//			$col_des_loc,
			new wi400Column("FABNBL", "Bolla"),
			new wi400Column("DATA_BOL", "Data Bolla", "DATE"),
			new wi400Column("FATTURA", "Fattura"),
			new wi400Column("DATA_FAT", "Data Fattura", "DATE"),
			new wi400Column("FABCDA", "Articolo"),
			new wi400Column("MDADSA", "Descrizione Articolo"),
//			$col_des_art,
			new wi400Column("MDACON", "Confezione"),
			new wi400Column("MDATPG", "Tipo<br>grammatura"),
			new wi400Column("MDAGRA", "Grammatura", "INTEGER", "right"),
			new wi400Column("MDAPEZ", "Pezzi<br>cartone", "INTEGER", "right"),
			new wi400Column("MHLPES", "Peso medio<br>cartone", "DOUBLE_4", "right"),
			new wi400Column("PESO_MEDIO", "Peso medio", "DOUBLE_2", "right"),
			new wi400Column("FABIVA", "IVA"),
			new wi400Column("VALORE", "Valore", "DOUBLE_3", "right"),
			new wi400Column("FABPPR", "Quantità", "DOUBLE_2", "right"),
		));
		
		listDispose($miaLista);
	}