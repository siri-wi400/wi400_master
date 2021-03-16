<?php
	/*
	 * @TODO Abilitazione filtri limitazione colonne proiettate vanno in errore.
	 * Esempio colonne con 'AS' o con il getDefaultValue 
	 * 
	 */
	if(!in_array($actionContext->getForm(), array("DEFAULT", "MAP_DETAIL"))) {
		$detail = new wi400Detail($azione."_INFO");
		
		$myField = new wi400Text("WIDAZI_INFO", "Azione", $key_azi['WIDAZI']);
		$myField->setDescription($key_azi['DESCRIZIONE']);
		$detail->addField($myField);
	}
	if(!in_array($actionContext->getForm(), array("DEFAULT", "DETAIL", "MAP_DETAIL"))) {
		$detail->setColsNum(2);
		
		$myField = new wi400Text("WIDID_INFO", "Detail", $key_det['WIDID']);
		$myField->setDescription($key_det['WIDDED']);
		$detail->addField($myField);
		
		$myField = new wi400Text("WIDDOL_INFO", "Tipo", ($key_det['WIDDOL'] == "D" || $key_det['WIDDOL'] == "") ? "Detail": ($key_det['WIDDOL'] == "L" ? "Lista" : "Parametri"));
		$detail->addField($myField);
	}
	if(in_array($actionContext->getForm(), array("LIST_ABILITAZIONI", "LIST_PARAMETRI"))) {
		$myField = new wi400Text("WIDKEY_INFO", "Utente", $key_ute['WIDKEY']);
		$detail->addField($myField);
	}
	
	if($actionContext->getForm() == "DEFAULT") {
		$miaLista = new wi400List($azione."_AZIONI", true);
		$miaLista->setField("WIDAZI, DESCRIZIONE");
		//$miaLista->setFrom("ZWIDETPA, FAZISIRI");
		$miaLista->setFrom("ZWIDETPA a LEFT JOIN fazisiri b ON WIDAZI=AZIONE");
		//$miaLista->setWhere("WIDAZI=AZIONE");
		$miaLista->setGroup("WIDAZI, DESCRIZIONE");
		$miaLista->setOrder("WIDAZI");
		
		$azione_col = new wi400Column("WIDAZI", "Azione");
		$azione_col->setActionListId("GO_DETAIL");
		
		$miaLista->setCols(array(
			$azione_col,
			new wi400Column("DESCRIZIONE", "Descrizione")
		));
		
		$miaLista->addKey("WIDAZI");
		$miaLista->addKey("DESCRIZIONE");
		
		$action = new wi400ListAction();
		$action->setId("GO_DETAIL");
		$action->setAction($azione);
		$action->setForm("DETAIL");
		$action->setLabel("Dettaglio");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		$mioFiltro = new wi400Filter("WIDAZI", "Azione", "STRING");
		$mioFiltro->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_INPUT);
		//$mioFiltro->setId("ELEMENTO");
		$mioFiltro->setFast(true);
		$miaLista->addFilter($mioFiltro);
		
		listDispose($miaLista);
	}else if($actionContext->getForm() == "CHECK_CONF_EXIST") {
		if($check_azione) {
			$sql = "SELECT WIDAZI FROM ZWIDETPA WHERE WIDAZI='$check_azione' AND WIDDOL='P'";
			$rs = $db->query($sql);
			if(!$row = $db->fetch_array($rs)) {
				echo "<center>
							<h3>Non è presente alcuna configurazione per quest'azione!</h3>
							<button onClick='doSubmit(\"$azione\", \"CREATE_CONF\")'>Crea</button>
						</center>";
			}else {
				$actionContext->gotoAction($azione, "UTENTI", "UTENTI&AZI=".$check_azione, true);
			}
		}else {
			echo "errore passare l'azione<br/>";
		}
	
	}else if($actionContext->getForm() == "DETAIL") {
		$detail->dispose();
		
		echo "<br/>";
		
		$miaLista = new wi400List($azione."_DETAIL", true);
		$miaLista->setField("WIDID, WIDDED, WIDDOL");
		$miaLista->setFrom("ZWIDETPA");
		$miaLista->setWhere("WIDAZI='{$key_azi['WIDAZI']}'");
		$miaLista->setGroup("WIDID, WIDDED, WIDDOL");
		$miaLista->setOrder("WIDID");
		$miaLista->setSelection("MULTIPLE");
		
		$detail_col = new wi400Column("WIDID", "Detail");
		$detail_col->setActionListId("GO_UTENTI");
		
		$tipo_col = new wi400Column("WIDDOL", "Tipo");
		$tipo_col->setDefaultValue('EVAL:($row["WIDDOL"] == "D" || $row["WIDDOL"] == "") ? "Detail" : ($row["WIDDOL"] == "L" ? "Lista" : "Parametri")');
		
		$miaLista->setCols(array(
			$detail_col,
			new wi400Column("WIDDED", "Titolo"),
			$tipo_col
		));
		
		$miaLista->addKey("WIDID");
		$miaLista->addKey("WIDDED");
		$miaLista->addKey("WIDDOL");
		
		$action = new wi400ListAction();
		$action->setId("GO_UTENTI");
		$action->setAction($azione);
		$action->setForm("UTENTI");
		$action->setLabel("Utenti");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("ELIMINA_DETAIL");
		$action->setLabel("Elimina");
		$action->setSelection("MULTIPLE");
		$miaLista->addAction($action);
		
		listDispose($miaLista);
	}else if($actionContext->getForm() == "UTENTI") {
		$detail->dispose();
		
		echo "<br/>";
		
		$miaLista = new wi400List($azione."_UTENTI", true);
		//$miaLista->setQuery($query);
		$miaLista->setField("WIDKEY");
		$miaLista->setFrom("ZWIDETPA");
		$miaLista->setWhere(implode(" and ", $where));
		$miaLista->setGroup("WIDKEY");
		$miaLista->setOrder("WIDKEY");
		$miaLista->setSelection("MULTIPLE");
		
		$utenti_col = new wi400Column("WIDKEY", "Utenti");
		$utenti_col->setActionListId("GO_ABILITAZIONI");
		
		$miaLista->setCols(array(
			$utenti_col
		));
		
		$miaLista->addKey("WIDKEY");
		
		$mioFiltro = new wi400Filter("WIDKEY", "Utente");
		$mioFiltro->setFast(true);
		$miaLista->addFilter($mioFiltro);
		
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("CLEAN_DETAIL_COPIA");
		$action->setLabel("Copia");
		$action->setSelection("SINGLE");
		$action->setTarget("WINDOW");
		$miaLista->addAction($action);
		
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("ELIMINA_UTENTE");
		$action->setLabel("Elimina");
		//$action->setTarget("WINDOW", 500, 500);
		$action->setSelection("MULTIPLE");
		$miaLista->addAction($action);
		
		if($key_det['WIDDOL'] == 'P') {
			$action = new wi400ListAction();
			$action->setId("GO_ABILITAZIONI");
			$action->setAction($azione);
			$action->setForm("LIST_PARAMETRI");
			$action->setLabel("Abilitazioni");
			$action->setSelection("SINGLE");
			$miaLista->addAction($action);
		}else {
			$action = new wi400ListAction();
			$action->setId("GO_ABILITAZIONI");
			$action->setAction($azione);
			$action->setForm("LIST_ABILITAZIONI");
			$action->setLabel("Abilitazioni");
			$action->setSelection("SINGLE");
			$miaLista->addAction($action);
		}
		
		listDispose($miaLista);
		
		if($key_det['WIDDOL'] == 'P') {
			echo "<br>";
			
			$da_iframe = "";
			if(isset($_REQUEST['DECORATION']) && $_REQUEST['DECORATION'] == 'lookup') {
				$da_iframe = "&DA_IFRAME=si";
			}
			
			$myButton = new wi400InputButton('INSERT_BUTTON');
			$myButton->setLabel("Gestione parametri");
			$myButton->setAction('GESTIONE_PARAMETRI');
			$myButton->setForm("DEFAULT&DA_ABILITAZIONI=".$key_azi['WIDAZI'].$da_iframe);
			$myButton->setTarget("WINDOW", 1200, 400);
			$myButton->dispose();
		}
	}else if($actionContext->getForm() == "DETAIL_COPIA") {
		//showArray($_REQUEST);
		
		$copia_detail = new wi400Detail($azione."_COPIA", false);
		
		//UTENTE
		$myField = new wi400InputText("TIPO_UTENTE");
		$myField->setLabel("Copia per utente");
		$myField->setMaxLength(20);
		//$myField->addValidation("required");
		$myField->setCase("UPPER");
		$myField->setShowMultiple(true);
		
		$decodeParameters = array(
				'TYPE'=> 'common',
				'COLUMN' => 'LAST_NAME',
				'TABLE_NAME' => 'SIR_USERS',
				'KEY_FIELD_NAME' => 'USER_NAME',
				'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp =new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE", $users_table);
		$myLookUp->addParameter("CAMPO","USER_NAME");
		$myLookUp->addParameter("DESCRIZIONE","LAST_NAME");
		$myLookUp->addParameter("LU_SELECT", "FIRST_NAME|LAST_NAME");
		$myLookUp->addParameter("LU_AS_TITLES", "Nome|Cognome");
		$myField->setLookUp($myLookUp);
		$copia_detail->addField($myField);
		
		//GRUPPO
		$myField = new wi400InputText("TIPO_GRUPPO");
		$myField->setLabel("Copia per gruppo");
		$myField->setCase("UPPER");
		$myField->setShowMultiple(true);

		$sql_gruppi = array();
		$arr_gruppi = array_merge(explode(";",$settings['wi400_groups']), explode(";",$settings['wi400_sel_groups']));
		foreach ($arr_gruppi as $index => $gruppo) {
			$sql_gruppi[] = "select '".($index+1)."' as KEY, '$gruppo' as GRUPPO FROM SYSIBM".$settings['db_separator']."SYSDUMMY1";
		}
		
		$decodeParameters = array(
				'TYPE' => 'common',
				'TABLE_NAME' => "(".implode(' union all ', $sql_gruppi).") as x",
				'COLUMN' => 'GRUPPO',
				'KEY_FIELD_NAME' => 'GRUPPO',
				'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp =new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("TITLE", "Lista gruppi");
		$myLookUp->addParameter("DIRECT_SQL", base64_encode(implode(" union all ", $sql_gruppi)));
		$myLookUp->addParameter("CAMPO", "GRUPPO");
		$myLookUp->addParameter("DESCRIZIONE","GRUPPO");
		$myField->setLookUp($myLookUp);
		
		$copia_detail->addField($myField);
		
		$button = new wi400InputButton("BUTTON_COPIA");
		$button->setLabel("Copia");
		$button->setAction($azione);
		$button->setForm("COPIA");
		$button->setValidation(true);
		$copia_detail->addButton($button);
		
		$copia_detail->dispose();
	}else if($actionContext->getForm() == "NUOVO_PARAMETRO") {
		$myDetail = new wi400Detail($azione."_DETAIL", !$isFromHistory);
		
		$myField = new wi400InputText("NOME_PARAMETRO");
		$myField->setLabel("Nome");
		$myField->addValidation("required");
		$myField->setSize(30);
		
		$sql_param = array();
		foreach ($gestione_param as $index => $param) {
			$sql_param[] = "select '".$param['ELEMENTO']."' as CODICE, '".$param['VALORE']."' as DESCRIZIONE FROM SYSIBM".$settings['db_separator']."SYSDUMMY1";
		}
		
		$decodeParameters = array(
				'TYPE' => 'common',
				'TABLE_NAME' => "(".implode(' union all ', $sql_param).") as x",
				'KEY_FIELD_NAME' => 'CODICE',
				'COLUMN' => 'DESCRIZIONE',
				'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp =new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("TITLE", "Lista gruppi");
		$myLookUp->addParameter("DIRECT_SQL", base64_encode(implode(" union all ", $sql_param)));
		$myLookUp->addParameter("CAMPO", "CODICE");
		$myLookUp->addParameter("DESCRIZIONE", "DESCRIZIONE");
		$myField->setLookUp($myLookUp);
		$myDetail->addField($myField);
		
		$myField = new wi400InputText("VALORE_PARAMETRO");
		$myField->setLabel("Valore");
		$myField->addValidation("required");
		$myDetail->addField($myField);
		
		$myButton = new wi400InputButton("SALVA_BUTTON");
		$myButton->setLabel("Salva");
		$myButton->setAction($azione);
		$myButton->setValidation(true);
		$myButton->setForm("INSERT_NUOVO_PARAMETRO");
		$myDetail->addButton($myButton);
		
		$myDetail->dispose();
		
	}else if($actionContext->getForm() == "LIST_ABILITAZIONI") {
		$widazi = $key_azi['WIDAZI'];
		$desc_art = $key_azi['DESCRIZIONE'];

		$detail->dispose();
		
		echo "<br/>";
		
		$miaLista = new wi400List($azione."_ABIL", !$isFromHistory);
		$miaLista->setSelection("MULTIPLE");
		$miaLista->setField("WIDKEY, WIDREQ, WIDDER, WIDABI, WIDHID, WIDFIL, WIDDFT, WIDDFV, WIDTYP, WIDSEQ, WIDSTA, WIDDOL");
		$miaLista->setFrom("ZWIDETPA");
		
		$where_lista = "WIDAZI='$widazi' AND WIDID='{$key_det['WIDID']}' and WIDKEY='{$key_ute['WIDKEY']}' and WIDREQ<>''";
		$miaLista->setWhere($where_lista);
		//if($key_det['WIDDOL'] == "L") {
			$miaLista->setOrder("WIDTYP desc, WIDSEQ, WIDREQ");
		//}
		
		$miaLista->setAutoUpdateList(true);
		$miaLista->setCallBackFunction("updateRow", "functionUpdateRow");
		$miaLista->setIncludeFile("utilities", "abilitazioni_campi_detail_commons.php");
		
		
		//Filtro veloce LABEL
		$mioFiltro = new wi400Filter("WIDDER","Label", "STRING");
		$mioFiltro->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$mioFiltro->setFast(true);
		$miaLista->addFilter($mioFiltro);
		
		if($key_det['WIDDOL'] != 'P') {
		
			$inputField = new wi400InputCheckbox("WIDABI_I");
			$inputField->setValue("1");
			$inputField->setUncheckedValue("0");
			$abil_col = new wi400Column("WIDABI", "Abilitato", "", "CENTER");
			$abil_col->setDisableAutoUpdate(true);
			$abil_col->setInput($inputField);
			/*$abil_col->setSortable(false);
			$abil_col->setHeaderAction($azione);
			$abil_col->setHeaderForm("CHECK_ALL");
			$abil_col->setHeaderIco(array("uncheck.png","check.png"));*/
			//$abil_col->setHeaderCallBack("setUpdateStatus(UPDATE_STATUS_ON)");
			
			$inputField = new wi400InputCheckbox("WIDHID_I");
			$inputField->setValue("1");
			$inputField->setUncheckedValue("0");
			$hidden_col = new wi400Column("WIDHID", "Nascosto", "", "CENTER");
			$hidden_col->setDisableAutoUpdate(true);
			$hidden_col->setReadonly('EVAL:nascostoReadOnly($row)');
			/*if($key_det['WIDDOL'] == 'L') {
				$inputField->setReadOnly(true);
				$inputField->setDisabled(true);
				$hidden_col->setReadonly(true);
			}*/
			$hidden_col->setInput($inputField);
			
			$inputField = new wi400InputCheckbox("WIDFIL_I");
			$inputField->setValue("1");
			$inputField->setUncheckedValue("0");
			$filtro_col = new wi400Column("WIDFIL", "Filtro", "", "CENTER");
			$filtro_col->setDisableAutoUpdate(true);
			$filtro_col->setInput($inputField);
			
			$inputField = new wi400InputCheckbox("WIDDFT_I");
			$inputField->setValue("1");
			$inputField->setUncheckedValue("0");
			$default_abil = new wi400Column("WIDDFT", $label_WIDDFT, "", "center");
			$default_abil->setInput($inputField);
			
			
			//Default value
			$val_col = new wi400Column("WIDDFV", $label_WIDDFV);
			$val_col->setDisableAutoUpdate(true);
			$exists_cond = array();
			$exists_cond[] = array('EVAL:!$row["WIDDFT"]', "wi400_grid_hidden");
			$val_col->setStyle($exists_cond);
			
			$myField = new wi400InputText("DEFAULT_VALUE");
			$myField->setSize(60);
			$val_col->setInput($myField);
			
			$tipo_col = new wi400Column("WIDTYP", "Tipo");
			$exists_cond = array();
			$exists_cond[] = array('EVAL:in_array($row["WIDTYP"], array("BUTTON", "TOOL"))', "wi400_grid_green");
			$exists_cond[] = array('EVAL:$row["WIDTYP"]=="ACTION"', 'wi400_grid_yellow');
			$tipo_col->setStyle($exists_cond);
			
			$inputField = new wi400InputCheckbox("WIDSTA_I");
			$inputField->setValue("1");
			$inputField->setUncheckedValue("0");
			$stato_col = new wi400Column("WIDSTA", "Stato");
			$stato_col->setDisableAutoUpdate(true);
			$stato_col->setInput($inputField);
			
			$myField = new wi400InputCheckbox("CHECK_ESTENDI");
			$myField->setValue("1");
			$myField->setUncheckedValue("0");
			$estendi_col = new wi400Column("ESTENDI", "Estendi<br>a tutti", "", "CENTER");
			$estendi_col->setDisableAutoUpdate(true);
			$estendi_col->setInput($myField);
			
			$miaLista->setCols(array(
				new wi400Column("WIDDER", "Label"),
				new wi400Column("WIDREQ", "Campo"),
				$abil_col,
				$hidden_col,
				$filtro_col,
				$default_abil,
				$val_col,
				$tipo_col,
				new wi400Column("WIDSEQ", "Ordinamento", "", "center"),
				$stato_col,
				$estendi_col
			));
			
			if($key_ute['WIDKEY'] != "*ALL") {
				$miaLista->removeCol("ESTENDI");
			}
			
			$miaLista->addKey("WIDREQ");
			/*$miaLista->addKey("WIDABI");
			$miaLista->addKey("WIDHID");
			$miaLista->addKey("WIDDFT");
			$miaLista->addKey("WIDDFV");
			$miaLista->addKey("WIDSTA");*/
			
			//Sezione filtri
			$mioFiltro = new wi400Filter("WIDREQ", "Campo", "STRING");
			$mioFiltro->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
			$mioFiltro->setFast(true);
			$miaLista->addFilter($mioFiltro);
			
			$mioFiltro = new wi400Filter("WIDTYP", "Tipo", "STRING");
			$mioFiltro->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
			$mioFiltro->setFast(true);
			$miaLista->addFilter($mioFiltro);
			
			//$mioFiltro = new wi400Filter("WIDABI", "Non abilitato", "CHECK_STRING", "=0");
			
			
			$mioFiltro = new wi400Filter("WIDABI","Abilitato","SELECT");
			$filterValues = array();
			$filterValues["WIDABI='1'"] = "Abilitato";
			$filterValues["(WIDABI='0' OR WIDABI='')"] = "Non abilitato";
			$mioFiltro->setSource($filterValues);
			$miaLista->addFilter($mioFiltro);
			
			if($key_det['WIDDOL'] != 'L') {
				$mioFiltro = new wi400Filter("WIDHID", "Nascosto", "SELECT");
				$filterValues = array();
				$filterValues["WIDHID='1'"] = "Abilitato";
				$filterValues["(WIDHID='0' OR WIDHID='')"] = "Non abilitato";
				$mioFiltro->setSource($filterValues);
				$miaLista->addFilter($mioFiltro);
			}
			
			$mioFiltro = new wi400Filter("WIDDFT", $label_WIDDFT, "SELECT");
			$filterValues = array();
			$filterValues["WIDDFT='1'"] = "Abilitato";
			$filterValues["(WIDDFT='0' OR WIDDFT='')"] = "Non abilitato";
			$mioFiltro->setSource($filterValues);
			$miaLista->addFilter($mioFiltro);
			
			$mioFiltro = new wi400Filter("WIDSTA", "Stato", "SELECT");
			$filterValues = array();
			$filterValues["WIDSTA='1'"] = "Abilitato";
			$filterValues["(WIDSTA='0' OR WIDSTA='')"] = "Non abilitato";
			$mioFiltro->setSource($filterValues);
			$miaLista->addFilter($mioFiltro);
		}else {
			//showArray($_REQUEST);
			if(isset($_REQUEST['DECORATION']) && $_REQUEST['DECORATION'] == 'lookUp') {
				$myButton = new wi400InputButton('RETURN_BUTTON');
				$myButton->setLabel("Indietro");
				$myButton->setAction($azione);
				$myButton->setForm("UTENTI");
				$myButton->dispose();
			
				echo "<br>";
			}
			/*$val_col = new wi400Column("WIDDFV", "Default value");
			
			$myField = new wi400InputText("DEFAULT_VALUE");
			$myField->setSize(10);
			$val_col->setInput($myField);*/
			
			$miaLista->setCols(array(
				new wi400Column("WIDREQ", "Parametro"),
				new wi400Column("WIDDFV", "Valore"),
			));
			
			$miaLista->addKey("WIDREQ");
			$miaLista->addKey("WIDDFV");
			
			$listAction = new wi400ListAction();
			$listAction->setAction($azione);
			$listAction->setForm("ELIMINA_PARAMETRO");
			//$listAction->setTarget("WINDOW");
			$listAction->setLabel("Elimina parametro");
			$listAction->setSelection("MULTIPLE");
			$miaLista->addAction($listAction);
		}
		
		// Aggiunta azioni di lista
		$listAction = new wi400ListAction();
		$listAction->setAction($azione);
		if($key_det['WIDDOL'] == 'P') {
			$listAction->setForm("UPDATE_PARAMETER");
		}else {
			$listAction->setForm("UPDATE_VALUES");
		}
		$listAction->setLabel("Salva modifiche");
		$listAction->setSelection("MULTIPLE");
		$miaLista->addAction($listAction);
		
		if($key_det['WIDDOL'] == 'L') {
			$listAction = new wi400ListAction();
			$listAction->setAction($azione);
			$listAction->setForm("CONFIGURA_LIST");
			$listAction->setLabel("Configura Lista");
			$listAction->setSelection("NONE");
			$listAction->setTarget("WINDOW");
			$miaLista->addAction($listAction);
			
			$listAction = new wi400ListAction();
			$listAction->setAction($azione);
			$listAction->setForm("RESET_ORDINAMENTO");
			$listAction->setLabel("Reset ordinamento");
			$listAction->setSelection("NONE");
			$miaLista->addAction($listAction);
			
			// Aggiunta riordino
			$sl = new wi400SortList();
				
			$sl->addSortColumn("WIDDER");
			//$sl->addSortColumn("WIDREQ");
				
			$sl->addSortKey("WIDAZI");
			$sl->addSortKey("WIDID");
			$sl->addSortKey("WIDKEY");
			$sl->addSortKey("WIDREQ");
			//$sl->addSortKey("ASS_NREL");
				
			//$sl->setSortTable("(select * from ZWIDETPA where ".$where_lista." and widtyp='COLUMN') as x");
			$sl->setSortTable("ZWIDETPA");
			$sl->setSortWhere("WIDTYP='COLUMN'");
				
			$sl->setSorter("WIDSEQ");
				
			$miaLista->addSortList($sl);
			
			$tool = new wi400ListAction();
			//$tool->setScript("openWindow(_APP_BASE + APP_SCRIPT + '?DECORATION=lookUp&t=SORT_LIST&IDLIST={$azione}_ABIL', 'Riordina', 600, 400)");
			$tool->setScript("openWindow(_APP_BASE + APP_SCRIPT + '?DECORATION=lookUp&t=$azione&f=ORDINAMENTO_COL', 'Riordina', 600, 400)");
			$tool->setIco($temaDir."images/grid/reorder.gif");
			$tool->setLabel("Riordina");
			$miaLista->addTool($tool);
		}else {
			$miaLista->removeCol("WIDSEQ");
		}
		
		listDispose($miaLista);
	}else if($actionContext->getForm() == "LIST_PARAMETRI") {
		$widazi = $key_azi['WIDAZI'];
		$desc_art = $key_azi['DESCRIZIONE'];
		
		$detail->dispose();
		
		echo "<br/>";
		
		if(isset($_REQUEST['DECORATION']) && $_REQUEST['DECORATION'] == 'lookUp') {
			$myButton = new wi400InputButton('RETURN_BUTTON');
			$myButton->setLabel("Indietro");
			$myButton->setAction($azione);
			$myButton->setForm("UTENTI");
			$myButton->dispose();
				
			echo "<br>";
		}
		
		$miaLista = new wi400List($azione."_PARAMETRI", !$isFromHistory);
		$miaLista->setSelection("MULTIPLE");
		$miaLista->setFrom("ztabtabe left join $tabella on ".implode(" AND ", $left_join));
		$miaLista->setWhere(implode(" and ", $where));
		
		//echo $miaLista->getSql()."__<br>";
		
		/*$miaLista->setAutoUpdateList(true);
		$miaLista->setCallBackFunction("inputCell", "functionFormattazioneInput");*/
		$miaLista->setIncludeFile("utilities", "abilitazioni_campi_detail_commons.php");
		
		$col_mod = new wi400Column("MODIFICA", "Modifica", "", "CENTER");
		$col_mod->setDecorator("ICONS");
		$col_mod->setDefaultValue('MODIFICA');
		$col_mod->setActionListId("ACTION_MODIFICA");
		
		$col_valore = new wi400Column("VALORE", "Valore");
		$col_valore->setDefaultValue('EVAL:getValueParam($row)');
		//$input = new wi400InputText("INPUT_VALORE");
		//$col_valore->setInput($input);
		
		//Il valore visualizzato arriva dal default
		$col_default = new wi400Column("FLAG_DEFAULT", "Default");
		$col_default->setDecorator('YES_NO_ICO');
		$col_default->setDefaultValue('EVAL:checkValoreDefault($row)');
		
		//Il parametro ha valori multipli
		$col_multi = new wi400Column("FLAG_MULTI", "Multi");
		$col_multi->setDecorator('YES_NO_ICO');
		$col_multi->setDefaultValue('EVAL:$row["MULTI"] ? "S" : "N"');
		
		$miaLista->setCols(array(
			$col_mod,
			new wi400Column("ELEMENTO", "Parametro"),
			$col_default,
			$col_valore,
			$col_multi,
			//new wi400Column("WIDDFV", "Valore salvato"),
		));
			
		$miaLista->addKey("ELEMENTO");
		//$miaLista->addKey("VALORE");
		
		$listAction = new wi400ListAction();
		$listAction->setId('ACTION_MODIFICA');
		$listAction->setAction($azione);
		$listAction->setForm("MODIFICA_PARAMETRO");
		$listAction->setTarget("WINDOW");
		$listAction->setLabel("Modifica");
		$listAction->setSelection("SINGLE");
		$miaLista->addAction($listAction);
		
		$listAction = new wi400ListAction();
		$listAction->setAction($azione);
		$listAction->setForm("ELIMINA_CONF_PARAMETRO");
		$listAction->setLabel("Elimina configurazione");
		$listAction->setSelection("MULTIPLE");
		$miaLista->addAction($listAction);
		
		/*$a = getParamAzione('CODIFICA_CLIENTI', 'ENTE_CODIFICA');
		showArray($a);*/
			
		/*$listAction = new wi400ListAction();
		$listAction->setAction($azione);
		$listAction->setForm("ELIMINA_PARAMETRO");
		//$listAction->setTarget("WINDOW");
		$listAction->setLabel("Elimina parametro");
		$listAction->setSelection("MULTIPLE");
		$miaLista->addAction($listAction);*/
		
		$miaLista->dispose();
	}else if($actionContext->getForm() == "MODIFICA_PARAMETRO") {
		
		//showArray($row);
		
		$detail = new wi400Detail($azione."_MODIFICA_PARAM");
		
		//$myField = new wi400InputText("VALORE");
		if($row['TEMPLATE']){
			$myField = getTemplateField($row['TEMPLATE'], 'VALORE');
			$myField->removeValidation('required');
		}else {
			$myField = getFieldFromParam(false, $row);
		}
		$myField->setLabel("Valore");
		$val = getValueParam($row);
		if($row['MULTI']) {
			$myField->setShowMultiple(true);
			$myField->setSortMultiple(true);
			$val = $multi_val;
		}
		if($row['VALORE1'] == "wi400InputCheckBox" || $row['VALORE1'] == "wi400InputSwitch") {
			$myField->setChecked(!!$val);
		}
		
		$myField->setValue($val);
		$detail->addField($myField);
		
		//Bottone seleziona
		$myButton = new wi400InputButton('MOD_BUTTON');
		$myButton->setLabel("Salva");
		$myButton->setAction($azione);
		if($row['WIDDFT']) {
			$myButton->setForm("UPDATE_PARAMETRO");
		}else {
			$myButton->setForm("INSERT_PARAMETRO");
		}
		$myButton->setValidation(true);
		$detail->addButton($myButton);
		
		$detail->dispose();
		
		
	}else if($actionContext->getForm() == "CONFIGURA_LIST") {
		//echo "ciao a tutti<br/>";
		
		//$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $key_det['WIDID']);
		
		
		$sql = "SELECT * FROM ZWIDETPA WHERE WIDAZI='{$key_azi['WIDAZI']}' AND WIDID='{$key_det['WIDID']}' AND WIDKEY='{$key_ute['WIDKEY']}' and WIDREQ<>'' and widtyp='COLUMN'";
		$rs = $db->query($sql);
		
		$idList = $key_det['WIDID'];
		$wi400List = new wi400List($idList);
		
		while($row = $db->fetch_array($rs)) {
			echo $row['WIDREQ']."<BR/>";
			
			$col = new wi400Column($row['WIDREQ'], $row['WIDDER']);
			$wi400List->addCol($col);
		}
		
		saveList($idList, $wi400List);
		
		$actionContext->gotoAction("MANAGELIST", "DEFAULT&IDLIST=$idList&DECORATION=lookUp", "", true);
	}
	
	
	
	
	