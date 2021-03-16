<?php

	// Dettaglio parametro
	if ($actionContext->getForm () == "DEFAULT") {
		// Estrazione Dati
		$searchAction = new wi400Detail ( $azione . "_PARAMETRI", false );
		$searchAction->setTitle($title);
		$searchAction->setSaveDetail ( true );
		
		// Intestazione //
		$myField = new wi400Text("TEXT");
		$myField->setLabel("Funzione :");
		$myField->setValue("Alert su articoli a prezzo variabile");
		$searchAction->addField($myField);
		
		// Ente //
		$myField = new wi400InputText('ENTE');
		$myField->setLabel("Ente");
		$myField->setShowMultiple(True);
		$myField->setMaxLength(4);
		$myField->setSize(4);
		$myField->setInfo("Seleziona l'ente");
		if($isPdv===true) {
			$myField->setReadonly(true);
		}
		$decodeParameters = array(
				'TYPE' => 'ente',
				'CLASSE_ENTE' => '01',
				'AJAX' => true,
				'COMPLETE' => true,
				'COMPLETE_MIN' => 2,
				'COLUMN' => 'MAFDSE',
				'COMPLETE_MAX_RESULT' => 15
		);
		$myField->setDecode($decodeParameters);
		$myLookUp = new wi400LookUp("LU_ENTI");
		$myLookUp->addParameter("CLASSE", "01");
		$myLookUp->addField("ENTE");
		$myField->setLookUp($myLookUp);
		$searchAction->addField($myField);
		// Numero Ordine //
		$myField = new wi400InputText('ORDINE');
		$myField->setLabel("Numero Ordine");
		$myField->setShowMultiple(True);
		$myField->setMaxLength(7);
		$myField->setSize(7);
		$myField->setInfo("Seleziona l'ordine");
		$searchAction->addField($myField);
		// Fornitore
		$myField = new wi400InputText('FORNITORE');
		$myField->setLabel(_t("FORNITORE"));
		$myField->setMaxLength(6);
		$myField->setSize(6);
		$myField->setShowMultiple(true);
		$myField->setValue($fornitore_array);
		$myField->setCase("UPPER");
		$myField->setInfo(_t("INSERIRE_FORNITORE"));
		$myField->setUserApplicationValue("FORNITORE");
		
		$decodeParameters = array(
				'TYPE' => 'interlocutore',
				'TIPO_RAPPORTO' => '01',
				'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_INTER");
		$myLookUp->addParameter("TIPO_RAPPORTO", "01");
		$myLookUp->addParameter("FILTER_SQL","MEBCDF IN (SELECT ANACDF FROM FANACONS)");
		$myLookUp->addField("FORNITORE");
		$myField->setLookUp($myLookUp);
		
		$searchAction->addField($myField);
		// Articolo //
		$myField = new wi400InputText('ARTI');
		$myField->setLabel("Codice articolo");
		$myField->setShowMultiple(True);
		$myField->setUserApplicationValue("ITEM");
		$myField->setMaxLength(7);
		$myField->setSize(7);
		$myField->setCase("UPPER");
		$myField->setInfo("Inserire il codice dell'articolo da ricercare");
		$decodeParameters = array(
				'TYPE' => 'articolo',
				'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
		// Lookup
		$myLookUp =new wi400LookUp("LU_ARTICOLI");
		$myLookUp->addField("ARTI");
		$myField->setLookUp($myLookUp);
		
		$searchAction->addField($myField);
		
		// Data carico dal //
		$myField = new wi400InputText('DCARICOD');
		$myField->setLabel("Data carico dal");
		$myField->addValidation("date");
//		$myField->addValidation("required");
		$myField->setValue("01/".date('m/Y'));
		$myField->setInfo("Seleziona la data di carico di partenza");
		$searchAction->addField($myField);
		// Data carico al//
		$myField = new wi400InputText('DCARICOA');
		$myField->setLabel("Data carico al");
		$myField->addValidation("date");
//		$myField->addValidation("required");
		$myField->setValue(dateModelToView($_SESSION['data_validita']));
		$myField->setInfo("Seleziona la data di carico di arrivo");
		$searchAction->addField($myField);
		
		// Dat registrazione //
		$myField = new wi400InputText('DREGD');
		$myField->setLabel("Data registrazione dal");
		$myField->addValidation("date");
//		$myField->addValidation("required");
		$myField->setValue("01/".date('m/Y'));
		$myField->setInfo("Seleziona la data di registrazione di partenza");
		$searchAction->addField($myField);	
		// Percentuale di delta //
		$myField = new wi400InputText('DREGA');
		$myField->setLabel("Data registrazione al");
		$myField->addValidation("date");
//		$myField->addValidation("required");
		$myField->setValue(dateModelToView($_SESSION['data_validita']));
		$myField->setInfo("Seleziona la data di registrazione di arrivo");
		$searchAction->addField($myField);
		
		// Percentuale di delta //
		$myField = new wi400InputText('PERDELTA');
		$myField->setLabel("Percentuale Delta");
		$myField->setAlign('right');
		$myField->addValidation('double');
		$myField->setDecimals(2);
		$myField->setMaxLength(6);
		$myField->setSize(6);
		$myField->setMask("1234567890,");
		$myField->setInfo("Percentuale di delta");
		$searchAction->addField($myField);
		
		// Seleziona
		$formatw = "LIST";
		$myButton = new wi400InputButton('SEARCH_BUTTON');
		$myButton->setLabel("Elabora");
		$myButton->setAction($azione);
		$myButton->setForm($formatw);
		$myButton->setValidation(true);
		$searchAction->addButton($myButton);
		$searchAction->dispose ();	
	}
	// Raggruppamento iniziale Data movimento - Tipo Movimento
	elseif($actionContext->getForm()=="LIST"){
		$myDetail = new wi400Detail($azione."_RIEPILOGO", true);
		$myDetail->setColsNum(2);
		// Intestazione //
		// Ente //
		if(!empty($ente)) {
			$des_dep_array = array();
			foreach($ente as $mag) {
				$des_mag = get_campo_ente($mag, date("Ymd"), "MAFDSE");
				$des_mag_array[] = $mag." - ".$des_mag;
			}
		$myField = new wi400Text("PDV");
		$myField->setLabel("PDV");
		$myField->setValue(implode ("</br>", $des_mag_array));
		$myDetail->addField($myField);
		}
		// Fornitore //
		if(!empty($ente)) {
			$des_dep_array = array();
			foreach($fornitore as $mag) {
				$des_mag = get_campo_fornitore($mag, date("Ymd"), "MEBRAG");
				$des_mag_array[] = $mag." - ".$des_mag;
			}
			$myField = new wi400Text("PDV");
			$myField->setLabel("PDV");
			$myField->setValue(implode ("</br>", $des_mag_array));
			$myDetail->addField($myField);
		}

		// Articolo //
		if(!empty($articolo)) {
			$des_art_array = array();
			foreach($articolo as $art) {
				$des_art = get_campo_articolo($art, date("Ymd"), "MDADSA");
				$des_art_array[] = $art." - ".$des_art;
			}
		$myField = new wi400Text("ARTI");
		$myField->setLabel("Articolo");
		$myField->setValue(implode ("</br>", $des_art_array));
		$myDetail->addField($myField);
		}
		
		$myDetail->dispose();
		echo "<br/>";
	
// Stampo il dettaglio parametri	
	$miaLista = new wi400List($azione."_LIST",true);
		$miaLista->setFrom("SIRILABFR/FLOGFORZ");
		$idList = $azione."_LIST";
		$where = implode(" and ", $where_array);
		//echo $where."<br/>";
		$miaLista->setWhere($where);
		$miaLista->setOrder("LOGDMO, LOGCDE");
		$miaLista->setField("LOGCDE,LOGCDF,LOGPST,LOGNOR,LOGCDA,LOGQTA,LOGCOS,LOGCOA,LOGVAR,LOGDMO,LOGHMO,LOGWHO,LOGDCA");
		
		$col_des_ente = new wi400Column("DES_ENTE","Descrizione locale");
		$col_des_ente->setDefaultValue('EVAL:get_campo_ente($row["LOGCDE"],'.date("Ymd").',"MAFDSE")');
		$col_des_ente->setSortable(False);
		
		$col_des_forni = new wi400Column("DES_FORNI","Descrizione Fornitore");
		$col_des_forni->setDefaultValue('EVAL:get_campo_fornitore($row["LOGCDF"],'.date("Ymd").',"MEBRAG")');
		$col_des_forni->setSortable(False);
		
		$col_des_art = new wi400Column("DES_ART","Descrizione Articolo");
		$col_des_art->setDefaultValue('EVAL:get_campo_articolo($row["LOGCDA"],'.date("Ymd").',"MDADSA")');
		$col_des_art->setSortable(False);
		
		$col_des_pst = new wi400Column("DES_POS","Descrizione Post");
		$decodeParameters = array(
				'TYPE' => 'post',
				//			'ENTE_SQL' => $filter_post,
				'ENTE_SQL' => 'EVAL:$row["LOGPST"]',
				'AJAX' => true
		);
		
		$col_des_pst->setDecode($decodeParameters);

		$miaLista->setCols(array(

				new wi400Column("LOGCDE", "Ente","STRING","left"),
				$col_des_ente,
				new wi400Column("LOGCDF", "Fornitore","STRING","left"),
				$col_des_forni,
				new wi400Column("LOGPST", "Post","STRING","left"),
				$col_des_pst,
				new wi400Column("LOGNOR", "Numero ordine","STRING","left"),
				new wi400Column("LOGCDA", "Articolo","string","left"),
				$col_des_art,
				new wi400Column("LOGQTA", "Quanti&agrave</br>inserita","DOUBLE_2","right"),
				new wi400Column("LOGCOS", "Costo unitario</br>inserito","DOUBLE_7","right"),
				new wi400Column("LOGCOA", "Costo unitario</br>anagrafico","DOUBLE_7","right"),
				new wi400Column("LOGVAR", "Variazione costo</br>unitario/anagrafico</br>% valore assoluto","DOUBLE_3","right"),
				new wi400Column("LOGDCA", "Data carico","DATE","center"),
				new wi400Column("LOGDMO", "Data conferma carico","STRING_6_DATE","center"),
				new wi400Column("LOGHMO", "Ora conferma carico","TIME_INTEGER","center"),
				new wi400Column("LOGWHO", "Utente","STRING","center"),
		));
				
		$miaLista->dispose();		
}