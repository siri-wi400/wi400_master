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
	$myField->setValue("Verifica formula di destocking");
	$searchAction->addField($myField);

	// Periodo di elaborazione
	// Data dal //
	$mySelect = new wi400InputSelect('DATAD');
	$mySelect->setLabel("Data Periodo");
	// Data Limite
	$sql = "SELECT distinct logdta FROM FLOGDESC order by LOGDTA desc";
	$result = $db->query($sql);
	while($row = $db->fetch_array($result)){
		$mySelect->addOption(dateModelToView($row['LOGDTA'],$row['LOGDTA']));}
//		$mySelect->setFirstLabel("Tutte");
		$mySelect->setInfo("Scegliere uno stato");
//		$mySelect->setValue(dateModelToView($row['LOGDTA'],$row['LOGDTA']));
		$searchAction->addField($mySelect);
		$datad=$row['LOGDTA'];
		
		// Periodo di elaborazione
		// Data al //
		$mySelect = new wi400InputSelect('DATAL');
		$mySelect->setLabel("Data Periodo");
		// Data Limite
		$sql = "SELECT distinct logdta FROM FLOGDESC order by LOGDTA desc";
		$result = $db->query($sql);
		while($row = $db->fetch_array($result)){
			$mySelect->addOption(dateModelToView($row['LOGDTA'],$row['LOGDTA']));}
			//	$mySelect->setFirstLabel("Tutte");
			$mySelect->setInfo("Scegliere uno stato");
			//	$mySelect->setValue($datap);
			$searchAction->addField($mySelect);
	
/*	// Data dal //
	$myField = new wi400InputText('DATAD');
	$myField->setValue(dateModelToView($data));
	$myField->setLabel("Data dal");
	$myField->addValidation('date');
	$myField->addValidation('required');
	$searchAction->addField($myField);
	
	// Data al //
	$myField = new wi400InputText('DATAL');
	$myField->setValue(dateModelToView($data));
	$myField->setLabel("Data al");
	$myField->addValidation('date');
	$myField->addValidation('required');
	$searchAction->addField($myField);	*/
	
	// Magazzino //
	$myField = new wi400InputText('MAGAZZINO');
	$myField->setLabel("PDV");
	$myField->setShowMultiple(True);
	$myField->setMaxLength(4);
	$myField->setSize(4);
	$myField->setInfo("Seleziona il deposito");
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
	$myLookUp->addField("MAGAZZINO");
	$myField->setLookUp($myLookUp);
	$searchAction->addField($myField);
	
	// POST //
	$myField = new wi400InputText('POST');
	$myField->setLabel("POST");
	$myField->setShowMultiple(True);
	$myField->setCase("UPPER");
	$myField->setMaxLength(4);
	$myField->setSize(4);
	$myField->setInfo("Inserire il codice del POST da ricercare");
		$decodeParameters = array(
			'TYPE' => 'post',
			'AJAX' => true
		);
		
		$myField->setDecode($decodeParameters);
	// Lookup
	$myLookUp =new wi400LookUp("LU_POST");
//	$myLookUp->addParameter("ENTE_SQL", implode ("', '", $magazzino));
	$myLookUp->addJsParameter("MAGAZZINO");
	$myLookUp->addField("POST");
	$myField->setLookUp($myLookUp);
	$searchAction->addField($myField);
	
	// Cumulativo //
	$myField = new wi400InputText('CUMU');
	$myField->setLabel("Cumulativo");
	$myField->setShowMultiple(True);
	$myField->setUserApplicationValue("CUM");
	$myField->setMaxLength(7);
	$myField->setSize(7);
	$myField->setCase("UPPER");
	$myField->setInfo("Inserire il codice del cumulativo da ricercare");
		$decodeParameters = array(
			'TYPE' => 'articolo',
//			'DATA_VAL' => $datad,
//			'LOCALE' => $locale,
			'AJAX' => true
		);
	$myField->setDecode($decodeParameters);
	// Lookup
	$myLookUp =new wi400LookUp("LU_ART_CUMULATIVI");
	$myLookUp->addField("CUMU");
	$myLookUp->addParameter("LU_DATA_VAL", $datad);
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
	// Periodo //
	$myField = new wi400Text("PERI");
	$myField->setLabel("Periodo");
	$datad=dateModelToView($datad);
	$datal=dateModelToView($datal);
	$myField->setValue("Dal : $datad al : $datal");
	$myDetail->addField($myField);	
	// PDV //
	if(!empty($magazzino)) {
		$des_dep_array = array();
		foreach($magazzino as $mag) {
			$des_mag = get_campo_ente($mag, date("Ymd"), "MAFDSE");
			$des_mag_array[] = $mag." - ".$des_mag;
		}
	$myField = new wi400Text("PDV");
	$myField->setLabel("PDV");
	$myField->setValue(implode ("</br>", $des_mag_array));
	$myDetail->addField($myField);
	}
	// POST //
	if(!empty($post)) {
		$des_pos_array = array();
		foreach($post as $pos) {
//			$des_pos = get_campo_ente($pos, date("Ymd"), "MAFDSE");
			$des_pos = array(
					'TYPE' => 'post',
					//			'ENTE_SQL' => $filter_post,
					'ENTE_SQL' => $pos,
					'AJAX' => true
			);
			$des_pos_array[] = $pos." - ".$des_pos;
		}
	$myField = new wi400Text("POST");
	$myField->setLabel("POST");
	$myField->setValue(implode ("</br>", $des_pos_array));
	$myDetail->addField($myField);
	}
	// Cumulativo //
	if(!empty($cumu)) {
		$des_cum_array = array();
		foreach($cumu as $cum) {
			$des_cum = get_campo_articolo($cum, date("Ymd"), "MDADSA");
			$des_cum_array[] = $cum." - ".$des_cum;
		}
	$myField = new wi400Text("CUMU");
	$myField->setLabel("Cumulativo");
	$myField->setValue(implode ("</br>", $des_cum_array));
	$myDetail->addField($myField);
	}
	// Articolo //
	if(!empty($arti)) {
		$des_art_array = array();
		foreach($arti as $art) {
			$des_art = get_campo_articolo($art, date("Ymd"), "MDADSA");
			$des_art_array[] = $art." - ".$des_art;
		}
	$myField = new wi400Text("ARTI");
	$myField->setLabel("Articolo");
	$myField->setValue(implode ("</br>", $des_art_array));
	$myDetail->addField($myField);
	}
	
	$myButton = new wi400InputButton('SEARCH_BUTTON');
	$myButton->setLabel("Esporta");
	$myButton->setAction($azione);
//	$myButton->setAction("EXPORTLIST");
	$myButton->setForm("EXPORT_BATCH");
//	$myButton->addParameter("EXP_LIST", "WI_CHKSTOCK_LIST");
//	$myButton->addParameter("TARGET", "PAGE");
//	$myButton->addParameter("ZIP", "ZIP");
//	$myButton->addParameter("FORMAT", "excel5");
	$myButton->setTarget("WINDOW");
	$myDetail->addButton($myButton);
	
	$myDetail->dispose();
	echo "<br/>";
	
// Stampo il dettaglio parametri	
	$miaLista = new wi400List($azione."_LIST",true);
		$miaLista->setFrom("FLOGDESC");
		$idList = $azione."_LIST";
		$where = implode(" and ", $where_array);
		//echo $where."<br/>";
		$miaLista->setWhere($where);
		$miaLista->setOrder("LOGDTA, LOGCDE");
		$miaLista->setField("LOGDTA,LOGCDE,LOGPST,LOGCUM,LOGCDA,LOGFTC,LOGINI,LOGCAR,LOGSCA,LOGFIN,LOGQTS,LOGDLT,LOGINC,LOGNOT");
		
		$col_des_pdv = new wi400Column("DES_PDV","Descrizione locale");
		$col_des_pdv->setDefaultValue('EVAL:get_campo_ente($row["LOGCDE"],'.date("Ymd").',"MAFDSE")');
		$col_des_pdv->setSortable(False);
		
		$col_des_art = new wi400Column("DES_ART","Descrizione Articolo");
		$col_des_art->setDefaultValue('EVAL:get_campo_articolo($row["LOGCDA"],'.date("Ymd").',"MDADSA")');
		$col_des_art->setSortable(False);
		
		$col_des_cum = new wi400Column("DES_CUM","Descrizione Cumulativo");
		$col_des_cum->setDefaultValue('EVAL:get_campo_articolo($row["LOGCUM"],'.date("Ymd").',"MDADSA")');
		$col_des_cum->setSortable(False);
		
		$col_des_pst = new wi400Column("DES_POS","Descrizione Post");
		$decodeParameters = array(
				'TYPE' => 'post',
				//			'ENTE_SQL' => $filter_post,
				'ENTE_SQL' => 'EVAL:$row["LOGPST"]',
				'AJAX' => true
		);
		
		$col_des_pst->setDecode($decodeParameters);

		$miaLista->setCols(array(

				new wi400Column("LOGDTA", "Periodo","DATE"),
				new wi400Column("LOGCDE", "Locale","STRING","left"),
				$col_des_pdv,
				new wi400Column("LOGPST", "Post","STRING","left"),
				$col_des_pst,
				new wi400Column("LOGCUM", "Cumulativo","STRING","left"),
				$col_des_cum,
				new wi400Column("LOGCDA", "Articolo","string","left"),
				$col_des_art,
				new wi400Column("LOGFTC", "Fattore</br>di conversione","DOUBLE_4","right"),
				new wi400Column("LOGINI", "Inventario</br>iniziale","DOUBLE_2","right"),
				new wi400Column("LOGCAR", "Entrate","DOUBLE_2","right"),
				new wi400Column("LOGSCA", "Uscite","DOUBLE_2","right"),
				new wi400Column("LOGFIN", "Inventario</br>finale","DOUBLE_2","right"),
				new wi400Column("LOGQTS", "Delta","DOUBLE_2","right"),
				new wi400Column("LOGDLT", "Delta</br>utilizzato","DOUBLE_7","right"),
				new wi400Column("LOGINC", "Percentuale</br>di incidenza","DOUBLE_5","right"),
				new wi400Column("LOGNOT", "Note","STRING","left")

		));
				
		$miaLista->dispose();
		
//	saveList($idList, $miaLista);
		
}