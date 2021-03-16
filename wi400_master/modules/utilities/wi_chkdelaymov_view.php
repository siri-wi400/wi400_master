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
	$myField->setValue("Verifica movimenti in ritardo");
	$searchAction->addField($myField);

	// Periodo di elaborazione //
/*	$myField = new wi400InputText('DATAP');
	$myField->setLabel("Data Periodo");
//	$myField->addValidation('date');
	$myField->setMaxLength(10);
	$myField->setSize(10);
	$myField->addValidation('required');
	
	$myLookUp =new wi400LookUp("LU_GENERICO");
	$myLookUp->addParameter("FILE","FSTGIPDV");
	$myLookUp->addParameter("CAMPO","DATAP");
	$lu_fields ="DIGITS(STGGVA)!!'/'!!DIGITS(STGMVA)!!'/'!!DIGITS(STGAVA) AS DATAP , DIGITS(STGALI)!!'/'!!DIGITS(STGMLI)!!'/'!!DIGITS(STGGLI) AS DATAM";
	$myLookUp->addParameter("LU_FIELDS", $lu_fields);
	$myLookUp->addParameter("LU_SELECT", "DATAP|DATAM");
	$myLookUp->addParameter("LU_AS_TITLES", "Data Periodo|Data validitÃ");
	$myLookUp->addParameter("TITLE", "Tabella elaborazione giacenze mensili PDV");
	$myField->setLookUp($myLookUp);
	$searchAction->addField($myField); */
	
	// Periodo di elaborazione
	$mySelect = new wi400InputSelect('DATAP');
	$mySelect->setLabel("Data Periodo");
	// Data Limite
		$sql = "SELECT DIGITS(STGGVA)!!'/'!!DIGITS(STGMVA)!!'/'!!DIGITS(STGAVA) AS DATAP FROM FSTGIPDV ORDER BY DIGITS(STGAVA)!!'/'!!DIGITS(STGMVA)!!'/'!!DIGITS(STGGVA) DESC";
		$result = $db->query($sql);
		while($row = $db->fetch_array($result)){
		$mySelect->addOption($row['DATAP'],$row['DATAP']);}
//	$mySelect->setFirstLabel("Tutte");
	$mySelect->setInfo("Scegliere uno stato");
//	$mySelect->setValue($datap);
	$searchAction->addField($mySelect);
		
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
	
	// Articolo //
	$myField = new wi400InputText('ARTI');
	$myField->setLabel("Codice articolo");
	$myField->setShowMultiple(True);
	$myField->setUserApplicationValue("ITEM");
	$myField->setMaxLength(7);
	$myField->setSize(7);
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
	$myButton = new wi400InputButton('SEARCH_BUTTON');
	$myButton->setLabel("Elabora");
	$myButton->setAction($azione);
	$myButton->setForm("LIST");
	$myButton->setValidation(true);
	$searchAction->addButton($myButton);
	
	$searchAction->dispose ();
	
	}

	// Lista
	elseif($actionContext->getForm()=="LIST"){
		$myDetail = new wi400Detail($azione."_RIEPILOGO", true);
		$myDetail->setColsNum(2);
		// Intestazione //
		// Ente
		if(!empty($ente)) {
			$des_dep_array = array();
			foreach($ente as $dep) {
				$des_dep = get_campo_ente($dep, date("Ymd"), "MAFDSE");
				$des_dep_array[] = $dep." - ".$des_dep;
			}
		$myField = new wi400Text("ENTE");
		$myField->setLabel("Ente");
		$myField->setValue(implode ("</br>", $des_dep_array));
		$myDetail->addField($myField);
		}
		// Data Periodo
		$myField = new wi400Text("PERIODO");
		$myField->setLabel("Data Elaborazione");
		$myField->setValue("Data Periodo : ".dateModelToView ( $datap )." - Data CutOff : ".dateModelToView(date6to8_rev ( $datal )));
		$myDetail->addField($myField);
		// Articolo
		if(!empty($ente)) {
			$des_art_array = array();
			foreach($articolo as $art) {
				$des_art = get_campo_articolo($art, date("Ymd"), "MDADSA");
				$des_art_array[] = $art." - ".$des_art;
			}
		$myField = new wi400Text("ARTICOLO");
		$myField->setLabel("Articolo");
		$myField->setValue(implode ('</br>', $des_art_array));
		$myDetail->addField($myField);
		}
		// Data Periodo
//		$myField = new wi400Text("LIMITE");
//		$myField->setLabel("Data limite");
//		$myField->setValue(dateModelToView(date6to8_rev ( $datal )));
//		$myDetail->addField($myField);
		
		$myDetail->dispose();
		echo "<br/>";
	
	// Stampo il dettaglio parametri
	$miaLista = new wi400List($azione."_LIST",true);
	$miaLista->setFrom("FCAAMOME");
	$where = implode(" and ", $where_array);
	//echo $where."<br/>";
	$miaLista->setWhere($where);
	$miaLista->setOrder("CAAENP");
	$miaLista->setField("CAAENP,CAAARP,CAAINP,CAACAU,CAAQTP,CAAV01,digits(CAAACP)!!digits(CAAMCP)!!digits(CAAGCP) as DATACOMP,CAADMO,CAAHMO");
	
	$col_des_ent = new wi400Column("DES_ENT","Descrizione</br> ente");
	$col_des_ent->setDefaultValue('EVAL:get_campo_ente($row["CAAENP"],'.date("Ymd").',"MAFDSE")');
	$col_des_ent->setSortable(False);
	
	$col_des_art = new wi400Column("DES_ART","Descrizione</br> Articolo");
	$col_des_art->setDefaultValue('EVAL:get_campo_articolo($row["CAAARP"],'.date("Ymd").',"MDADSA")');
	$col_des_art->setSortable(False);
	
	$col_des_for = new wi400Column("DES_FOR","Descrizione</br> Fornitore");
	$col_des_for->setDefaultValue('EVAL:get_campo_fornitore($row["CAAINP"],'.date("Ymd").',"MEBRAG")');
	$col_des_for->setSortable(False);
	
	$col_des_cau = new wi400Column("DES_CAU","Descrizione</br> Causale");
	$col_des_cau->setDefaultValue('EVAL:get_des_causale($row["CAACAU"])');
	$col_des_cau->setSortable(False);
	
	$col_data_comp = new wi400Column("DAT_COMP","Data</br> competenza","DATE","right");
	$col_data_comp->setDefaultValue('EVAL:$row["DATACOMP"]');
	
	$col_data_regi = new wi400Column("DAT_REGI","Data</br>registrazione","DATE","right");
	$col_data_regi->setDefaultValue('EVAL:(date6to8_rev($row["CAADMO"]))');
	
	$miaLista->setCols(array(
			new wi400Column("CAAENP", "Ente","STRING","left"),
			$col_des_ent,
			new wi400Column("CAAARP", "Articolo","STRING","left"),
			$col_des_art,
			new wi400Column("CAAINP", "Fornitore","STRING","left"),
			$col_des_for,
			new wi400Column("CAACAU", "Causale","STRING","left"),
			$col_des_cau,
			new wi400Column("CAAQTP", "Qta Pezzi","DOUBLE_2","right"),	
			new wi400Column("CAAV01", "Valore","DOUBLE_3","right"),
			$col_data_comp,
			$col_data_regi,
			new wi400Column ( "CAAHMO", "Ora", "TIME_INTEGER", "right" )
	));
	
	$miaLista->dispose();
	
	}