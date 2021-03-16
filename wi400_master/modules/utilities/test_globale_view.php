
	
	
<?php
	
	if($actionContext->getForm()=="DEFAULT") {
		$detail = new wi400Detail($azione."_TAB_DETAIL", true);
		
		$detail->addTab("test_7", "Drag&Drop");
		$detail->addTab("test_1", "wi400InputText");
		$detail->addTab("test_2", "Lista/filtro/drag&drop");
		$detail->addTab("test_4", "decode/complete in lista");
		$detail->addTab("test_3", "iframe filtro");
		$detail->addTab("test_5", "Chiusura detail");
		$detail->addTab("test_6", "Tab dentro tab");
		$detail->addTab("test_8", "test_8");
		$detail->addTab("test_9", "test_9");
		
		$detail->addJstoTab("test_2", "activeIframe('primo')");
		//$detail->addJstoTab("test_2", "alert('ciao');");
		//$detail->addJstoTab("test_4", "activeIframe('terzo')");
		
		$scheda = "test_1";
		
		//SCHEDA 1111111111111111111111111111111111111111111111111111111
		//AZIONI
		$myField = new wi400InputText('codazi_test');
		$myField->setLabel("Azioni");
		$myField->setCase("UPPER");
		$myField->setMaxLength(40);
		$myField->setInfo(_t("ACTION_CODE_INFO"));
		
		$decodeParameters = array(
				'TYPE'=> 'common',
				'COLUMN' => 'DESCRIZIONE',
				'TABLE_NAME' => 'FAZISIRI',
				'KEY_FIELD_NAME' => 'AZIONE',
				'ALLOW_NEW' => True,
				'AJAX' => true,
				'COMPLETE' => true,
				'COMPLETE_MIN' => 2,
				'COMPLETE_MAX_RESULT' => 15
		);
		$myField->setDecode($decodeParameters);
		$myLookUp = new wi400LookUp("LU_AZIONI");
		$myField->setLookUp($myLookUp);
		
		$detail->addField($myField, $scheda);
		
		//CODICE ARTICOLO
		$myField = new wi400InputText('codart99');
		$myField->setLabel("Codice articolo");
		$myField->addValidation('required');
		$myField->setMaxLength(7);
		$myField->setUserApplicationValue("ITEM");
		$myField->setSize(7);
		$myField->setInfo("Codice dell'articolo da inserire/manutenere");
		$decodeParameters = array(
				'TYPE' => 'articolo',
				'AJAX' => true,
				'COMPLETE' => true,
				'COMPLETE_MIN' => 2,
				'COLUMN' => 'MDADSA',
				'COMPLETE_MAX_RESULT' => 15
		);
		$myField->setDecode($decodeParameters);
		// Lookup
		$myLookUp = new wi400LookUp("LU_ARTICOLI");
		$myLookUp->addField("codart99");
		$myField->setLookUp($myLookUp);
		
		$detail->addField($myField, $scheda);
		
		//CODICE ARTICOLO SENZA LOOKUP
		$myField = new wi400InputText('codart2');
		$myField->setLabel("Codice articolo");
		$myField->addValidation('required');
		$myField->setMaxLength(7);
		$myField->setUserApplicationValue("ITEM");
		$myField->setSize(7);
		$myField->setInfo("Codice dell'articolo da inserire/manutenere");
		$decodeParameters = array(
				'TYPE' => 'articolo',
				'AJAX' => true,
				'COMPLETE' => false,
				'COMPLETE_MIN' => 2,
				'COLUMN' => 'MDADSA',
				'COMPLETE_MAX_RESULT' => 15
		);
		$myField->setDecode($decodeParameters);
		
		$detail->addField($myField, $scheda);
		
		//CODICE ARTICOLO MULTI
		$myField = new wi400InputText('codart_multi');
		$myField->setLabel("Codice articolo multi");
		$myField->addValidation('required');
		$myField->setMaxLength(7);
		$myField->setShowMultiple(true);
		$myField->setUserApplicationValue("ITEM");
		$myField->setSize(7);
		$myField->setInfo("Codice dell'articolo da inserire/manutenere");
		$decodeParameters = array(
				'TYPE' => 'articolo',
				'AJAX' => true,
				'COMPLETE' => true,
				'COMPLETE_MIN' => 2,
				'COLUMN' => 'MDADSA',
				'COMPLETE_MAX_RESULT' => 15
		);
		$myField->setDecode($decodeParameters);
		// Lookup
		$myLookUp = new wi400LookUp("LU_ARTICOLI");
		$myLookUp->addField("codart_multi");
		$myField->setLookUp($myLookUp);
		
		$detail->addField($myField, $scheda);
		
		//CODICE ARTICOLO ORDINABILE
		$myField = new wi400InputText('codart_ordinabile');
		$myField->setLabel("Codice articolo multi sort");
		//$myField->addValidation('required');
		$myField->setMaxLength(7);
		$myField->setShowMultiple(true);
		$myField->setSortMultiple(true);
		$myField->setUserApplicationValue("ITEM");
		$myField->setSize(7);
		$myField->setInfo("Codice dell'articolo da inserire/manutenere");
		$decodeParameters = array(
				'TYPE' => 'articolo',
				'AJAX' => true,
				'COMPLETE' => true,
				'COMPLETE_MIN' => 2,
				'COLUMN' => 'MDADSA',
				'COMPLETE_MAX_RESULT' => 15
		);
		$myField->setDecode($decodeParameters);
		// Lookup
		$myLookUp = new wi400LookUp("LU_ARTICOLI");
		$myLookUp->addField("codart_ordinabile");
		$myField->setLookUp($myLookUp);
		
		$detail->addField($myField, $scheda);
		
		$scheda = "test_2";
		$iframe = new wi400Iframe("primo", "TLISTINI", "");
		$iframe->setDecoration("lookup");
		$iframe->setStyle("height: 100%;");
		$iframe->setAutoLoad(false);
		$myField = new wi400InputText('TIPI_RAPPORTO');
		$myField->setCustomHTML($iframe->getHtml());
		$myField->setHeight(500);
		
		$detail->addField($myField, $scheda);
		
		$scheda = "test_3";
		
		$miaLista = new wi400List($azione."_LIST", true);
		
		$where = "USER_NAME=ZSUTE";
		
		$miaLista->setFrom("ZSLOG, $users_table");
		$miaLista->setWhere($where);
		$miaLista->setOrder("ZSUTE, ZTIME DESC");
		$miaLista->setSelection('SINGLE');
		
		$esito_col = new wi400Column("ZSESI","Esito log");
		$esito_cond = array();
		$esito_cond[] = array('EVAL:$row["ZSESI"]=="OK"', 'wi400_grid_green');
		$esito_cond[] = array('EVAL:$row["ZSESI"]=="KO"', 'wi400_grid_red');
		$esito_col->setStyle($esito_cond);
		
		$miaLista->setCols(array(
				new wi400Column("ZSUTE","Codice utente"),
				new wi400Column("FIRST_NAME","Nome utente"),
				new wi400Column("LAST_NAME","Cognome utente"),
				$esito_col,
				new wi400Column("ZSIP","Indirizzo IP"),
				new wi400Column("ZTIME","Data di log","COMPLETE_TIMESTAMP")
		));
		
		// Aggiunta chiavi di lista
		$miaLista->addKey("ZSUTE");
		$miaLista->addKey("FIRST_NAME");
		$miaLista->addKey("LAST_NAME");
		$miaLista->addKey("ZSESI");
		$miaLista->addKey("ZSIP");
		$miaLista->addKey("ZTIME");
		
		$miaLista->setBreakKey("ZSUTE");
		
		// Aggiunta filtri rapidi
		// Utente
		$mioFiltro = new wi400Filter("ZSUTE","Codice utente","STRING");
		$mioFiltro->setFast(true);
		$miaLista->addFilter($mioFiltro);
		
		// Aggiunta Filtri avanzati
		$mioFiltro = new wi400Filter("ZSESI","Esito log","SELECT","");
		$filterValues = array();
		$filterValues["ZSESI='OK'"] = "OK";
		$filterValues["ZSESI='KO'"] = "KO";
		$mioFiltro->setSource($filterValues);
		$miaLista->addFilter($mioFiltro);
		
		$mioFiltro = new wi400Filter("ZSIP", "Codice ip", "LOOKUP");
		$myField = new wi400InputText('FILTRO_CODICE_IP');
		$myField->setLabel("Settore");
		$decodeParameters = array(
			'TYPE'=> 'common',
			'COLUMN' => '',
			'TABLE_NAME' => 'ZSLOG',
			'KEY_FIELD_NAME' => 'TRIM(ZSIP) AS CIAO',
			'GROUP_BY' => "ZSIP",
			'AJAX' => true,
			'COMPLETE' => true,
			'COMPLETE_MIN' => 2,
			'COMPLETE_MAX_RESULT' => 15
		);
		//$myField->setDecode($decodeParameters);
		$myLookUp =new wi400LookUp("LU_GENERICO");
		$query = base64_encode("SELECT TRIM(ZSIP) AS CIAO FROM ZSLOG GROUP BY ZSIP");
		$myLookUp->addParameter("DIRECT_SQL", $query);
		$myLookUp->addParameter("CAMPO", "CIAO");
		$myField->setLookUp($myLookUp);
		$mioFiltro->setFieldObj($myField);
		$miaLista->addFilter($mioFiltro);
		
		// Percentuale accessi utenti
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("PERCENTUALE_ACCESSI");
		$action->setLabel("Percentuale accessi utenti");
		$action->setSelection("NONE");
		$miaLista->addAction($action);
		
		// Andamento accessi utente
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("ANDAMENTO_ACCESSI");
		$action->setLabel("Andamento accessi utenti");
		$action->setSelection("NONE");
		$miaLista->addAction($action);
		
		// GeoIP
		$action = new wi400ListAction();
		$action->setAction("GEOIP");
		$action->setLabel("GeoIP");
		$action->setTarget("WINDOW",1000,500);
		$action->setGateway("ACCESS_LOG");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		$miaLista->setShowHeadFilter(True);
		//$miaLista->dispose();
		
		$detail->addField($miaLista, $scheda);
		
		
		$scheda = "test_4";
		
		$nameDetail = "TINDIRIZZI_INTERLOCUTORI_SRC";
		$myField = getTemplateField('interlocutori_lookup', 'INTERLOCUTORE');
		$myField->setValue("000001");
		wi400Detail::setDetailField($nameDetail, $myField);
		
		$fieldObj = new wi400InputText("DATA_RIFERIMENTO");
		$fieldObj->setValue(date('Ymd'));
		wi400Detail::setDetailField($nameDetail, $fieldObj);
		
		$iframe = new wi400Iframe("terzo", "TINDIRIZZI_INTERLOCUTORI", "DETAIL", "TINTERLOCUTORI");
		$iframe->setDecoration("lookup");
		$iframe->setStyle("height: 100%;");
		//$iframe->setAutoLoad(false);
		$myField = new wi400InputText('INDIRIZZI_INTERLOCUTORI');
		$myField->setCustomHTML($iframe->getHtml());
		$myField->setHeight(500);
		
		$detail->addField($myField, $scheda);
		
		$scheda = "test_5";
		$detail_1 = new wi400Detail("prova_close");
		$detail_1->setStatus("CLOSE");
		$detail_1->setTitle("Titolo");
		
		$myField = new wi400Text("qualcosa", "Prova", "valore");
		$detail_1->addField($myField);
		
		$detail->addField($detail_1, $scheda);
		
		$scheda = "test_6";
		$iframe = new wi400Iframe("quarto", "TARTICOLI", "DEFAULT");
		$iframe->setDecoration("lookup");
		$iframe->setStyle("height: 100%;");
		$myField = new wi400InputText('GESTIONE_ARTICOLI');
		$myField->setCustomHTML($iframe->getHtml());
		$myField->setHeight(500);
		
		$detail->addField($myField, $scheda);
		
		$scheda = "test_7";
		
		$appDrag = new wi400DragAndDrop($azione."_DRAG");
		$appDrag->setWidth("49%");
		$appDrag->setHeight("100");
		$appDrag->setCheckUpdate(true);
		
		$appList1 = new wi400DragList("TO_GROUPS", _t("SELEZIONATI"));
		$appList1->setRows($to_groups);
		$appList1->setColor("#ffebe8");
		$appDrag->addList($appList1);
		
		$appList2 = new wi400DragList("FROM_GROUPS", _t('NON_SELEZIONATI'));
		$appList2->setRows($from_groups);
		$appList2->setColor("#FFFFCC");
		$appDrag->addList($appList2);
		
		$appDrag->save($azione."_DRAG", $appDrag);
		
		$myButton = new wi400InputButton("DRAG_BUTTON_SAVE");
		$myButton->setLabel("Salva");
		$myButton->setAction($azione);
		$myButton->setForm("SALVA_DRAG_DROP");
		//$myButton->setTarget("WINDOW");
		$detail->addButton($myButton, $scheda);
		
		$myButton = new wi400InputButton("DRAG_BUTTON_SAVE");
		$myButton->setLabel("Ripristina");
		$myButton->setAction($azione);
		$myButton->setForm("RIPRISTINA");
		$detail->addButton($myButton, $scheda);
		
		$detail->addField($appDrag, $scheda);
		
		$detail->dispose();
		
		echo "<br/>";
		
		$secondo_detail = new wi400Detail($azione."_SECONDO_DETAIL");
		$secondo_detail->setTitle("Secondo detail");
		//AZIONI2
		$myField = new wi400InputText('codazi123');
		$myField->setLabel("Azioni");
		$myField->setCase("UPPER");
		$myField->setMaxLength(40);
		$myField->setInfo(_t("ACTION_CODE_INFO"));
		
		$decodeParameters = array(
				'TYPE'=> 'common',
				'COLUMN' => 'DESCRIZIONE',
				'TABLE_NAME' => 'FAZISIRI',
				'KEY_FIELD_NAME' => 'AZIONE',
				'ALLOW_NEW' => True,
				'AJAX' => true,
				'COMPLETE' => true,
				'COMPLETE_MIN' => 2,
				'COMPLETE_MAX_RESULT' => 15
		);
		$myField->setDecode($decodeParameters);
		$myLookUp = new wi400LookUp("LU_AZIONI");
		$myField->setLookUp($myLookUp);
		
		$secondo_detail->addField($myField);
		
		$myField = new wi400InputText("format_number");
		$myField->setLabel("Prova format");
		//$myField->setMask("0123456789,");
		
		//$myField->setSize(7);
		//$myField->addValidation("numeric");
		$secondo_detail->addField($myField);
		
		$secondo_detail->dispose();
		
		echo "<br/>";
		
		$terzo_detail = new wi400Detail($azione."_TERZO_DETAIL");
		$terzo_detail->setTitle("Terzo detail");
		//AZIONI3
		$myField = new wi400InputText('codazi112');
		$myField->setLabel("Azioni");
		$myField->setCase("UPPER");
		$myField->setMaxLength(40);
		$myField->setInfo(_t("ACTION_CODE_INFO"));
		
		$decodeParameters = array(
				'TYPE'=> 'common',
				'COLUMN' => 'DESCRIZIONE',
				'TABLE_NAME' => 'FAZISIRI',
				'KEY_FIELD_NAME' => 'AZIONE',
				'ALLOW_NEW' => True,
				'AJAX' => true,
				'COMPLETE' => true,
				'COMPLETE_MIN' => 2,
				'COMPLETE_MAX_RESULT' => 15
		);
		$myField->setDecode($decodeParameters);
		$myLookUp = new wi400LookUp("LU_AZIONI");
		$myField->setLookUp($myLookUp);
		
		$terzo_detail->addField($myField);
		
		$terzo_detail->dispose();
		
		
	}else if($actionContext->getForm() == "DETAIL") {
	
	}else if($form == "INFO") {
		$detail = new wi400Detail($azione."_INFO");
		
		foreach($row as $key => $valore) {
			$myField = new wi400Text($key, $key, $valore);
			$detail->addField($myField);
		}
		
		$_SESSION['TO_GROUP'] = $_REQUEST[$azione."_DRAG_TO_GROUPS"];
		$_SESSION['FROM_GROUP'] = $_REQUEST[$azione."_DRAG_FROM_GROUPS"];
		
		$myButton = new wi400InputButton("ELIMINA");
		$myButton->setLabel("Elimina");
		$myButton->setAction($azione);
		$myButton->setForm("ELIMINA");
		$myButton->addParameter("ID_ORDINE", $row['ID_ORDINE']);
		/*$myButton->addParameter($azione."_DRAG_TO_GROUPS", $_REQUEST[$azione."_DRAG_TO_GROUPS"]);
		$myButton->addParameter($azione."_DRAG_FROM_GROUPS", $_REQUEST[$azione."_DRAG_FROM_GROUPS"]);*/
		$detail->addButton($myButton);

		$detail->dispose();
	}else if($form == "SALVA_DRAG_DROP") {
		echo "TO: ".$_REQUEST['TEST_GLOBALE_DRAG_TO_GROUPS']."<br/>";
		echo "FROM: ".$_REQUEST['TEST_GLOBALE_DRAG_FROM_GROUPS']."<br/>";
	}
	
	
	
	