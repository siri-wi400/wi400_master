<?php
	if(in_array($form, array('FORM', 'VIS_MASCHERA', 'DETTAGLIO_CAMPI', 'ABBINA_FORM'))) {
		$detail = new wi400Detail($azione."_RIEP_LIST");
		$detail->setColsNum(2);
				
		// Libreria
		$myField = new wi400Text('LIBRERIA', 'Libreria', $keyFile['OT5LIB']);
		$detail->addField($myField);
		
		// File
		$myField = new wi400Text('FILE', 'File', $keyFile['OT5FIL']);
		$detail->addField($myField);
		
		//Chiave
		$myField = new wi400Text('CHIAVE', 'Chiave', $keyFile['OT5KEY']);
		$detail->addField($myField);
		
		if(in_array($form, array('', 'DETTAGLIO_CAMPI'))) {
			$myField = new wi400Text('FORM', 'Form', $keyForm['OT5FMT']);
			$detail->addField($myField);
		}
	}

	if($form == 'DEFAULT') {
		$detail = new wi400Detail($azione."_PARAM", false);
		$detail->setSaveDetail(true);
		
		// Libreria
		$myField = new wi400InputText('LIBRERIA');
		$myField->setLabel("Libreria");
		$myField->setCase("UPPER");
		//$myField->addValidation("required");
		$detail->addField($myField);
		
		// File
		$myField = new wi400InputText('FILE');
		$myField->setLabel("File");
		$myField->setCase("UPPER");
		//$myField->addValidation("required");
		$detail->addField($myField);
		
		//Bottone seleziona
		$button = new wi400InputButton('SELEZIONA_BUTTON');
		$button->setLabel("Seleziona");
		$button->setAction($azione);
		$button->setForm('DETAIL');
		$detail->addButton($button);
		
		$detail->dispose();
		
		
	}else if($form == 'DETAIL') {
		$detail = new wi400Detail($azione."_RIEP_PARAM");
		$detail->setColsNum(2);
		
		// Libreria
		$myField = new wi400Text('LIBRERIA', 'Libreria', $param['LIBRERIA']);
		$detail->addField($myField);
		
		// File
		$myField = new wi400Text('FILE', 'File', $param['FILE']);
		$detail->addField($myField);
		
		$detail->dispose();
		
		echo "<br>";
		
		$miaLista = new wi400List($azione."_LIST", !$isFromHistory);
		
		$miaLista->setFrom($tabLibrerie);
		$miaLista->setWhere($where);
		
		$dettaglio = new wi400Column("DETTAGLIO", "Dettaglio", "", "CENTER");
		$dettaglio->setDecorator("ICONS");
		$dettaglio->setDefaultValue("SEARCH");
		$dettaglio->setSortable(false);
		$dettaglio->setExportable(false);
		$dettaglio->setActionListId($azione."_DETTAGLIO");
		
		$miaLista->setCols(array(
			$dettaglio,
			new wi400Column("OT5LIB", "Libreria"),
			new wi400Column("OT5FIL", "File"),
			new wi400Column("OT5KEY", "Chiave"),
			new wi400Column("OT5TIM", "Riferimento"),
			new wi400Column("OT5ID", "Id"),
		));
		
		$miaLista->addKey('OT5KEY');
		$miaLista->addKey('OT5LIB');
		$miaLista->addKey('OT5FIL');
		
		//filtro libreria
		$myFilter = new wi400Filter("OT5LIB", "Libreria");
		$myFilter->setFast(true);
		$myFilter->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$miaLista->addFilter($myFilter);

		//filtro file
		$myFilter = new wi400Filter("OT5FIL", "File");
		$myFilter->setFast(true);
		$myFilter->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$miaLista->addFilter($myFilter);
		
		// Dettaglio form
		$action = new wi400ListAction();
		$action->setLabel("Dettaglio");
		$action->setId($azione."_DETTAGLIO");
		$action->setAction($azione);
		$action->setForm("FORM");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		$miaLista->dispose();
		
	}else if($form == 'FORM') {
		
		
		$detail->dispose();
		
		echo "<br>";
		
		$miaLista = new wi400List($azione."_LIST_FORM", !$isFromHistory);
		$miaLista->setSelection('MULTIPLE');
		
		$miaLista->setFrom($tabForm);
		$miaLista->setWhere("OT5KEY='{$keyFile['OT5KEY']}'");
		
		$vis_col = new wi400Column("VISUALIZZA", "Visualizza<br>maschera", "", "CENTER");
		$vis_col->setDecorator("ICONS");
		$vis_col->setDefaultValue("SEARCH");
		$vis_col->setSortable(false);
		$vis_col->setExportable(false);
		$vis_col->setActionListId($azione."_VIS");
		
		$det_col = new wi400Column("DETTAGLIO", "Dettaglio<br>campi", "", "CENTER");
		$det_col->setDecorator("ICONS");
		$det_col->setDefaultValue("MODIFICA");
		$det_col->setSortable(false);
		$det_col->setExportable(false);
		$det_col->setActionListId($azione."_DET");
		
		$miaLista->setCols(array(
			$vis_col,
				$det_col,
			new wi400Column("OT5FMT", "Form"),
			new wi400Column("OT5FMC", "File"),
			new wi400Column("OT5TYP", "Tipo"),
			new wi400Column("OT5SUB", "Subfile"),
			new wi400Column("OT5IDE", "Id"),
		));
		
		$miaLista->addKey('OT5FMT');
		
		$myFilter = new wi400Filter("OT5FMT", "Form");
		$myFilter->setFast(true);
		$myFilter->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$miaLista->addFilter($myFilter);
		
		// Dettaglio form
		$action = new wi400ListAction();
		$action->setLabel("Visualizza maschera");
		$action->setId($azione."_VIS");
		$action->setAction($azione);
		$action->setForm("VIS_MASCHERA");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		// Dettaglio campi
		$action = new wi400ListAction();
		$action->setLabel("Dettaglio campi");
		$action->setId($azione."_DET");
		$action->setAction($azione);
		$action->setForm("DETTAGLIO_CAMPI");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		// Abbina
		$action = new wi400ListAction();
		$action->setLabel("Abbina");
		$action->setAction($azione);
		$action->setForm("ABBINA_FORM");
		$action->setSelection("MULTIPLE");
		$miaLista->addAction($action);
		
		$miaLista->dispose();
	}else if($form == 'RECORD') {
		$detail = new wi400Detail($azione."_RIEP_LIST");
		$detail->setColsNum(2);
		
		// Libreria
		$myField = new wi400Text('LIBRERIA', 'Libreria', $keyFile['OT5LIB']);
		$detail->addField($myField);
		
		// File
		$myField = new wi400Text('FILE', 'File', $keyFile['OT5FIL']);
		$detail->addField($myField);
		
		//Chiave
		$myField = new wi400Text('CHIAVE', 'Chiave', $keyFile['OT5KEY']);
		$detail->addField($myField);
		
		if(in_array($form, array('', 'DETTAGLIO_CAMPI'))) {
			$myField = new wi400Text('FORM', 'Form', $keyForm['OT5FMT']);
			$detail->addField($myField);
		}
		$myField = new wi400InputText('OT5COL');
		$myField->setLabel('Numero colonne');
		$myField->setSize(1);
		$myField->setMaxLength(1);
		$myField->addValidation('integer');
		$myField->setValue($row ? $row['OT5COL'] : 2);
		$detail->addField($myField);
		
		//descrizioni tab
		$myField = new wi400InputText('OT5TAB');
		$myField->setLabel('Label tab');
		$myField->setShowMultiple(true);
		$myField->setSortMultiple(true);
		$myField->setValue($row ? $row['OT5TAB'] : array());
		$detail->addField($myField);
		
		//Gestione bottoni con descrizione
		$myField = new wi400InputSwitch('OT5TXT');
		$myField->setLabel('Etichette bottoni');
		$myField->setChecked(($row && $row['OT5TXT'] == 'S') ? true : false);
		$detail->addField($myField);
		
		//Gestione grandezza finestra
		$myField = new wi400InputText('OT5SIZ');
		$myField->setLabel('Grandezza finestra');
		$myField->setValue($row ? $row['OT5SIZ'] : '');
		$detail->addField($myField);
		
		//Rendering Engine
		$myField = new wi400InputText('OT5RDE');
		$myField->setLabel('Rendering Engine');
		$myField->setValue($row ? $row['OT5RDE'] : '');
		$detail->addField($myField);
		
		$myButton = new wi400InputButton('SALVA');
		$myButton->setLabel('Salva');
		$myButton->setAction($azione);
		$myButton->setForm('SAVE_DATI_DETAIL');
		//$myButton->setTarget('WINDOW', 700, 500);
		$detail->addButton($myButton);
		
		$detail->dispose();
		
		
	}else if($form == 'VIS_MASCHERA') {
		echo '<link rel="stylesheet" type="text/css" href="modules/giwi400/css/giwi400.css"  media="screen">';
		
		$myField = new wi400InputText('OT5COL');
		$myField->setLabel('Numero colonne');
		$myField->setSize(1);
		$myField->setMaxLength(1);
		$myField->addValidation('integer');
		$myField->setValue($row ? $row['OT5COL'] : 2);
		$detail->addField($myField);
		
		//descrizioni tab
		$myField = new wi400InputText('OT5TAB');
		$myField->setLabel('Label tab');
		$myField->setShowMultiple(true);
		$myField->setSortMultiple(true);
		$myField->setValue($row ? $row['OT5TAB'] : array());
		$detail->addField($myField);
		
		//Gestione bottoni con descrizione
		$myField = new wi400InputSwitch('OT5TXT');
		$myField->setLabel('Etichette bottoni');
		$myField->setChecked(($row && $row['OT5TXT'] == 'S') ? true : false);
		$detail->addField($myField);
		
		//Gestione grandezza finestra
		$myField = new wi400InputText('OT5SIZ');
		$myField->setLabel('Grandezza finestra');
		//$myField->setValue($row ? $row['OT5SIZ'] : '');
		$detail->addField($myField);
		
		$myButton = new wi400InputButton('SALVA');
		$myButton->setLabel('Salva');
		$myButton->setAction($azione);
		$myButton->setForm('SAVE_DATI_DETAIL');
		//$myButton->setTarget('WINDOW', 700, 500);
		$detail->addButton($myButton);
		
		$detail->dispose();
		
		echo "<br>";
		
		$giwi400->display();
		
		echo "<br><br>";
		
		$myButton = new wi400InputButton('SHOW_MANAGER');
		$myButton->setLabel('Show Manager');
		$myButton->setAction('DATI_GIWI400');
		$myButton->setForm('SHOW_DATI_FIELD&IS_BUTTON=si&IS_MANAGER=si&I_GIWI_FIL='.$keyFile['OT5FIL'].'&I_GIWI_FLI='.$keyFile['OT5LIB']);
		$myButton->setTarget('WINDOW', 700, 500);
		$myButton->dispose();
		
		echo "&nbsp;";
		
		$myButton = new wi400InputButton('BUTTON_CONDIZIONI');
		$myButton->setLabel('Condizioni');
		$myButton->setAction('DATI_GIWI400');
		$myButton->setForm('CONDIZIONI');
		$myButton->setTarget('WINDOW', '', '', true, "closeWindow();");
		$myButton->dispose(); 
	}else if($form == 'ABBINA_FORM') {
		echo '<link rel="stylesheet" type="text/css" href="modules/giwi400/css/giwi400.css"  media="screen">';
		
		$detail->dispose();
		
		echo "<br>";
		
		$detailAbbina->dispose();
		
	}else if($form == 'DETTAGLIO_CAMPI') {
		
		$detail->dispose();
		
		echo "<br>";
		
		$miaLista = new wi400List($azione."_LIST_CAMPI", !$isFromHistory);
		
		$from = $tabCampi." a";
		$from .= " left join ZOT5FLDR b on a.ot5key=b.ot5key and a.ot5fmt=b.ot5fmt and a.ot5fld=b.ot5fld
				left join $tabClientAttributi c on a.ot5key=c.ot5key and a.ot5fmt=c.ot5fmt and a.ot5fld=c.ot5fld";
		
		$miaLista->setField("a.*, b.OT5FL1, c.OT5TXT as LABEL, c.OT5SEQ as SEQUENZA");
		$miaLista->setFrom($from);
		$miaLista->setWhere("a.OT5KEY='{$keyFile['OT5KEY']}' AND a.OT5FMT='{$keyForm['OT5FMT']}' AND A.OT5TYD<>'P' AND SUBSTR(A.OT5FLD, 1, 1) <>'*'");
		
		$miaLista->setSelection("MULTIPLE");
		
		$miaLista->setIncludeFile('giwi400', 'console_giwi400_commons.php');
		
		$col_param = new wi400Column("ATTRIBUTI", "Attributi", "", "CENTER");
		$col_param->setDecorator("ICONS");
		$col_param->setDefaultValue("MODIFICA");
		$col_param->setSortable(false);
		$col_param->setExportable(false);
		$col_param->setActionListId($azione."_PARAM");
		
		$colonne = getColumnListFromTable($tabCampi);
//		echo "COLONNE:<pre>"; print_r(array_keys($colonne)); echo "</pre>";
		
		array_unshift($colonne, $col_param);
//		echo "COLONNE:<pre>"; print_r(array_keys($colonne)); echo "</pre>";

		$col_legame = new wi400Column("OT5FL1", "Campo<br>Legato");
		$col_legame->setActionListId($azione."_LEGAME");
		$legame_cond = array();
//		$legame_cond[] = array('EVAL:$row["OT5FL1"]<>""', "wi400_font_red");
		$legame_cond[] = array('EVAL:$row["OT5FL1"]<>""', "wi400_grid_yellow");
		$legame_cond[] = array('EVAL:1==1', "");
		$col_legame->setStyle($legame_cond);

		$col_obj = $colonne['OT5FLD'];		
		$legame_cond = array();
		$legame_cond[] = array('EVAL:$row["OT5FL1"]<>""', "wi400_grid_yellow");
		$legame_cond[] = array('EVAL:check_has_legame("'.$file_src.'", "'.$form_src.'", $row["OT5FLD"], $row["OT5FL1"])=="L"', "wi400_grid_green");
		$legame_cond[] = array('EVAL:$row["OT5USE"]=="§"', "wi400_grid_blue");
//		$legame_cond[] = array('EVAL:prepare_string($row["OT5USE"])=="§"', "wi400_grid_blue");
		$legame_cond[] = array('EVAL:1==1', "");
		$col_obj->setStyle($legame_cond);
		
		$col_label = new wi400Column("LABEL", "TESTO PERSONALIZZATO");
		
		$inputField = new wi400InputText("SEQUENZA_I");
		$inputField->setSize(5);
		$inputField->setMask("1234567890,");
		$inputField->setDecimals(2);
		$inputField->setCheckUpdate(True);
		
		$col_seq = new wi400Column("SEQUENZA", "SEQUENZA", "DOUBLE_2", "right");
		$col_seq->setInput($inputField);

		//Aggiungo la colonna OT5FL1 dopo OT5FLD
		$pos = array_search("OT5FLD", array_keys($colonne), true);
//		echo "POS: $pos<br>";

		$cols = array_slice($colonne, 0, $pos+1);
//		echo "COLS:<pre>"; print_r(array_keys($cols)); echo "</pre>";

		$cols["OT5FL1"] = $col_legame;
		
		$cols = array_merge($cols, array_slice($colonne, $pos+1));
//		echo "COLS:<pre>"; print_r(array_keys($cols)); echo "</pre>";
		
		//Aggiungo la colonna LABEL dopo OT5TXT
		$colonne = $cols;
		
		$pos = array_search("OT5TXT", array_keys($colonne), true);
//		echo "POS: $pos<br>";
		
		$cols = array_slice($colonne, 0, $pos+1);
//		echo "COLS:<pre>"; print_r(array_keys($cols)); echo "</pre>";
		
		$cols["LABEL"] = $col_label;
		
		$cols = array_merge($cols, array_slice($colonne, $pos+1));
//		echo "COLS:<pre>"; print_r(array_keys($cols)); echo "</pre>";
		
//		echo "COLS:<pre>"; print_r(array_keys($cols)); echo "</pre>";

//		$cols['OT5USE']->setFormat("PREPARE_STRING");

//		$cols['OT5SEQ']->setDescription("Sequenza");
		$cols['OT5SEQ']->setShow(false);
		
		$cols['SEQUENZA'] = $col_seq;
		
//		$miaLista->setCols($colonne);
		$miaLista->setCols($cols);
		
		$miaLista->addKey('OT5FLD');
		$miaLista->addKey('OT5KEY');
		$miaLista->addKey('OT5FMT');
		
		$myFilter = new wi400Filter("OT5FLD", "Nome Campo");
		$myFilter->setKey("A.OT5FLD");
//		$myFilter->setFast(true);
		$myFilter->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$miaLista->addFilter($myFilter);
		
		$myFilter = new wi400Filter("OT5FL1", "Campo Legato");
//		$myFilter->setFast(true);
		$myFilter->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$miaLista->addFilter($myFilter);
		
		//$tipo_array = get_val_array("OT5TYP");
		
		$myFilter = new wi400Filter("OT5TYP","TIPO","SELECT","");
		$filterValues = array();
		foreach($tipo_array as $val) {
			$filterValues["OT5TYP='$val'"] = $val;
		}
		$myFilter->setSource($filterValues);
		$miaLista->addFilter($myFilter);
		
		//$use_array = get_val_array("OT5USE");
		
		$myFilter = new wi400Filter("OT5USE","UTILIZZO","SELECT","");
		$filterValues = array();
		foreach($use_array as $val) {
//			if(prepare_string($val)=="§")
//				$val = prepare_string($val);
			
			$filterValues["OT5USE='$val'"] = $val;
		}
		$myFilter->setSource($filterValues);
		$miaLista->addFilter($myFilter);
		
		//$tipo_disp_array = get_val_array("OT5TYD");
		
		$myFilter = new wi400Filter("OT5TYD","TIPO DISPLAY","SELECT","");
		$filterValues = array();
		foreach($tipo_disp_array as $val) {
			$des = $val;
			if($val=="")
				$des = "*BLANK";
			
			$filterValues["OT5TYD='$val'"] = $des;
		}
		$myFilter->setSource($filterValues);
		$miaLista->addFilter($myFilter);
		
		// Modifica parametri
		$action = new wi400ListAction();
		$action->setLabel("Parametri");
		$action->setId($azione."_PARAM");
		$action->setAction($azione);
		$action->setForm("CLIENT_ATTRIBUTI");
		$action->setTarget('WINDOW');
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		// Nuovo Campo
		$action = new wi400ListAction();
		$action->setLabel("Aggiungi Campo");
		$action->setId($azione."_ADDNEW");
		$action->setAction($azione);
		$action->setForm("ADDNEW");
		$action->setTarget('WINDOW');
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		// Legame
		$action = new wi400ListAction();
		$action->setLabel("Campo Legato");
		$action->setId($azione."_LEGAME");
		$action->setAction($azione);
		$action->setForm("LEGAME");
		$action->setTarget('WINDOW');
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		$action = new wi400ListAction();
		$action->setLabel("Salva sequenza");
		$action->setAction($azione);
		$action->setForm("UPDATE_SEQUENZA");
		$action->setSelection("MULTIPLE");
		$miaLista->addAction($action);
		
		$miaLista->dispose();
	}else if($form == 'CLIENT_ATTRIBUTI') {
		$detail = new wi400Detail($azione."_CLIENT_ATTRIBUTI");
		
		$detail->setSource($row);
		
		$check_hla = false;
		if($row) {
			if($row['OT5HLA']=="S")
				$check_hla = true;
		}
//		echo "<font color='blue'>HIDE LABEL</font> - CHECK: "; var_dump($check_hla); echo "<br>";
		
		function getTool($type) {
			global $azione;
			
			$customTool = new wi400CustomTool($azione, "PARAMETRI_CLIENT_ATTRIBUTI");
			$customTool->setIco("themes/common/images/table-select-row.png");
			$customTool->setTarget("WINDOW");
			$customTool->addParameter("OT5FLD1", $type);
			//$customTool->addJsParameter($id);
			
			return $customTool;
		}
		
		//Decoding
		$myField = new wi400InputText('OT5DEC');
		$myField->setLabel('Decoding');
		$myField->setMaxLength(50);
		
		$customTool = getTool('DECODING');
		$myField->addCustomTool($customTool);
		
		$detail->addField($myField);
		
		//Lookup
		$myField = new wi400InputText('OT5LOK');
		$myField->setLabel('Lookup');
		$myField->setMaxLength(50);
		
		$customTool2 = getTool('LOOKUP');
		$myField->addCustomTool($customTool2);
		
		$detail->addField($myField);
		
		//Abilitazione
		$myField = new wi400InputText('OT5ABI');
		$myField->setLabel('Funzione di abilitazione');
		$myField->setMaxLength(50);
		$customTool2 = getTool('OT5ABI');
		$myField->addCustomTool($customTool2);		
		$detail->addField($myField);
		
		$myField = new wi400InputText('OT5OTH');
		$myField->setLabel('Altre funzioni');
		$myField->setMaxLength(50);
		$customTool2 = getTool('OT5OTH');
		$myField->addCustomTool($customTool2);
		// Attacco un lookup e un decoding
		$decodeParameters = array(
		    'TYPE' => 'common',
		    'TABLE_NAME' => "ZOT5PMFU",
		    'COLUMN' => 'OT5DES',
		    'KEY_FIELD_NAME' => 'OT5FUN',
		    'FILTER_SQL' => "OT5UAF <> ' '",
		    'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
		// Lookup
		$myLookUp = new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE", "ZOT5PMFU");
		$myLookUp->addParameter("CAMPO","OT5FUN");
		$myLookUp->addParameter("DESCRIZIONE","OT5DES");
		$myLookUp->addParameter("LU_WHERE","OT5UAF <> ' '");
		$myField->setLookUp($myLookUp);
		//
		$detail->addField($myField);
		
		//Style
		$myField = new wi400InputText('OT5STY');
		$myField->setLabel('Style');
		$myField->setMaxLength(50);
		$detail->addField($myField);
		
		//Visualizzazione
		$select = new wi400InputSelect('OT5VIS');
		$select->setLabel('Visualizza');
		$select->addOption('Si', 'S');
		$select->addOption('No', 'N');
		$select->addOption('Protetto', 'P');
		$detail->addField($select);
		
		//Numero tab
		$myField = new wi400InputText('OT5TAB');
		$myField->setLabel('Numero tab');
		$myField->setSize(2);
		$myField->setMaxLength(2);
		$myField->addValidation('integer');
		$detail->addField($myField);
		
		$myField = new wi400InputSwitch("OT5HLA");
		$myField->setLabel("Nascondi Label del campo");
		$myField->setOnLabel(_t('LABEL_YES'));
		$myField->setOffLabel(_t('LABEL_NO'));
		$myField->setChecked($check_hla);
		$detail->addField($myField);
		
		$myField = new wi400InputText('OT5FOP');
		$myField->setLabel('Funzione di Output PHP');
		$myField->setMaxLength(50);
		$customTool2 = getTool('OT5FOP');
		// Attacco un lookup e un decoding
		$decodeParameters = array(
		    'TYPE' => 'common',
		    'TABLE_NAME' => "ZOT5PMFU",
		    'COLUMN' => 'OT5DES',
		    'KEY_FIELD_NAME' => 'OT5FUN',
		    'FILTER_SQL' => "OT5UPO <> ' '",
		    'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
		// Lookup
		$myLookUp = new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE", "ZOT5PMFU");
		$myLookUp->addParameter("CAMPO","OT5FUN");
		$myLookUp->addParameter("DESCRIZIONE","OT5DES");
		$myLookUp->addParameter("LU_WHERE","OT5UPO <> ' '");
		$myField->setLookUp($myLookUp);
		//
		$myField->addCustomTool($customTool2);
		
		$detail->addField($myField);
		
		$myField = new wi400InputText('OT5FOR');
		$myField->setLabel('Funzione di Output RPG');
		$myField->setMaxLength(50);
		$customTool2 = getTool('OT5FOR');
		// Attacco un lookup e un decoding
		$decodeParameters = array(
		    'TYPE' => 'common',
		    'TABLE_NAME' => "ZOT5PMFU",
		    'COLUMN' => 'OT5DES',
		    'KEY_FIELD_NAME' => 'OT5FUN',
		    'FILTER_SQL' => "OT5RPO <> ' '",
		    'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
		// Lookup
		$myLookUp = new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE", "ZOT5PMFU");
		$myLookUp->addParameter("CAMPO","OT5FUN");
		$myLookUp->addParameter("DESCRIZIONE","OT5DES");
		$myLookUp->addParameter("LU_WHERE","OT5RPO <> ' '");
		$myField->setLookUp($myLookUp);
		//
		
		$myField->addCustomTool($customTool2);
		
		$detail->addField($myField);
		
		$myField = new wi400InputText('OT5TXT');
		$myField->setLabel('Testo Personalizzato Label');
		$myField->setMaxLength(50);
		$detail->addField($myField);
		
		$myField = new wi400InputText('OT5SEQ');
		$myField->setLabel('Sequenza visualizzazione Campo');
		$myField->setMaxLength(10);
		$myField->setValue(doubleModelToView($row['OT5SEQ'],2));
		$myField->setDecimals(2);
		$detail->addField($myField);
		
		//Bottone seleziona
		$button = new wi400InputButton('SELEZIONA_BUTTON');
		$button->setLabel("Salva");
		$button->setAction($azione);
		$button->setForm('SAVE_CLIENT_ATTRIBUTI');
		$detail->addButton($button);
		//Bottone Cancella, solo se Campo §
		if ($theuse=="§") {
			$button = new wi400InputButton('CANCELLA');
			$button->setLabel("Elimina");
			$button->setAction($azione);
			$button->setForm('ELIMINA_FIELD');
			$detail->addButton($button);
		}
		
		$detail->dispose();
	}else if($form == 'PARAMETRI_CLIENT_ATTRIBUTI') {
		
		$miaLista = new wi400List($azione."_LIST_PARAMETRI", !$isFromHistory);
		
		$miaLista->setSubfile($subfile);
		$miaLista->setAutoUpdateList(True);
		$miaLista->setCallBackFunction("updateRow", "updateRowParametri");
		
		$miaLista->setIncludeFile('giwi400', 'console_giwi400_commons.php');
		
		$col_chiave = new wi400Column("OT5PRM", "Chiave");
		$input = new wi400InputText('CHIAVE');
		$myLookup = new wi400LookUp('LU_GENERICO');
		$myLookup->addParameter('UNION_ALL', $param_union);
		$myLookup->addParameter('CAMPO', 'GRUPPO');
		$myLookup->addParameter('DESCRIZIONE', 'GRUPPO');
		$input->setLookUp($myLookup);
		$col_chiave->setInput($input);
		
		$col_valore = new wi400Column("OT5VAL", "Valore");
		$input = new wi400InputText('VALORE');
		$col_valore->setInput($input);
		
		$col_stato = new wi400Column("OT5STA", "Stato");
		$input = new wi400InputCheckbox('STATO');
		$input->setUncheckedValue('0');
		$input->setValue('1');
		$col_stato->setInput($input);
		
		$miaLista->setCols(array(
			$col_chiave,
			$col_valore,
			$col_stato
		));
		
		$miaLista->addKey('NREL');
		
		$miaLista->dispose();
		
		echo "<br>";
		
		//Bottone salva
		$button = new wi400InputButton('SALVA_BUTTON');
		$button->setLabel("Salva");
		$button->setAction($azione);
		$button->setForm('SAVE_PARAMETRI_CLIENT');
		$button->addParameter('OT5FLD1', $_REQUEST['OT5FLD1']);
		$button->dispose();
		
	}else if($form == 'SHOW_DATI_FIELD') {
		
	    $detail = new wi400Detail($azione."_SHOW_DATI_FIELD");
	    $myField = new wi400Text('CAMPO', 'Campo', $_REQUEST['CAMPO']);
	    $detail->addField($myField);
	    $myField = new wi400Text('FILE', 'File', $_REQUEST['I_GIWI_FIL']);
	    $detail->addField($myField);
	    $myField = new wi400Text('LIBRERIA', 'Libreria', $_REQUEST['I_GIWI_FLI']);
	    $detail->addField($myField);
	    $myField = new wi400Text('FORMATO', 'Formato', $_REQUEST['I_GIWI_FRM']);
	    $detail->addField($myField);
	    // Verifico se presente un legame
	    $key = $_REQUEST['I_GIWI_FIL']."_".$_REQUEST['I_GIWI_FLI'];
	    $campo = $_REQUEST['CAMPO'];
	    $formato = $_REQUEST['I_GIWI_FRM'];
	    $sql = "SELECT * FROM ZOT5FLDR WHERE OT5KEY='$key' AND OT5FMT='$formato' AND OT5FLD='$campo'";
	    $result = $db->singleQuery($sql);
	    $row = $db->fetch_array($result);
	    if ($row) {
	        $myField = new wi400Text('LEGATO', 'Campo Legato', $row['OT5FL1']);
	        $detail->addField($myField);
	        $myButton = new wi400InputButton('PULISCI_RELOAD');
	        $myButton->setLabel("Cancella Legame");
	        $myButton->setAction($azione);
	        $myButton->setForm("ELIMINA_LEGAME");
	        $myButton->setConfirmMessage("Sicuro di voler cancellare il legame?");
	        $detail->addButton($myButton);
	    }

		if(!isset($_REQUEST['IS_BUTTON'])) {
			$button = new wi400InputButton('CLIENT_ATTRIBUTI');
			$button->setLabel("Attributi Client");
			$button->setAction($azione);
			$button->setTarget("WINDOW");
			$button->setForm('CLIENT_ATTRIBUTI_WINDOWS');
			$button->addParameter('I_GIWI_FIL', $_REQUEST['I_GIWI_FIL']);
			$button->addParameter('I_GIWI_FLI', $_REQUEST['I_GIWI_FLI']);
			$button->addParameter('CAMPO', $_REQUEST['CAMPO']);
			$button->addParameter('I_GIWI_FRM', $_REQUEST['I_GIWI_FRM']);
			$detail->addButton($button);
		}
		// Bottone Pulizia Cache e Reload Maschera sottostante
		$myButton = new wi400InputButton('PULISCI_RELOAD');
		$myButton->setLabel("Clear&Reload");
		$myButton->setAction("CONSOLE_GIWI400");
		$myButton->setForm("CLEAR_RELOAD");
		$detail->addButton($myButton);
		
		$detail->dispose();
		echo getHTMLObject($field, "Array", "1", false);
	}else if($form == 'ELIMINA_LEGAME') {
	    $key = $_REQUEST['FILE']."_".$_REQUEST['LIBRERIA'];
	    $campo = $_REQUEST['CAMPO'];
	    $formato = $_REQUEST['FORMATO'];
	    
	    $sql = "DELETE FROM ZOT5FLDR WHERE OT5KEY='$key' AND OT5FMT='$formato' AND OT5FLD='$campo'";
	    $result = $db->query($sql);
	    $messageContext->addMessage("INFO", "Legame Cancellato");
	    // Bottone Pulizia Cache e Reload Maschera sottostante
	    $myButton = new wi400InputButton('PULISCI_RELOAD');
	    $myButton->setLabel("Clear&Reload");
	    $myButton->setAction("CONSOLE_GIWI400");
	    $myButton->setForm("CLEAR_RELOAD");
	    $myButton->dispose();
	}else if($form == 'CONDIZIONI') {
		$detail = new wi400Detail($azione.'_CONDITION', true);
		$detail->setColsNum(4);
		
		//showArray($condizioni);
		
		$detail->setSource($condizioni);
		
		foreach($condizioni as $key => $val) {
			$myField = new wi400InputSwitch($key);
			$myField->setLabel($key);
			$myField->setOnLabel('TRUE');
			$myField->setOffLabel('FALSE');
			$myField->setChecked($val == 'true' ? true : false);
			//$myField->setValue($val);
// 			$myField->setOnChange("console.log(this)");
			$detail->addField($myField);
		}
		
		$detail->dispose();
		
?>
		<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery('input.checkSwitch').parent().click(function() {
					var that =  jQuery(this);
					var input = that.find('input');
					var id = input.attr('id');
					//console.log(id);
					var value = that.hasClass('off');
					console.log(input);
					console.log(value);
					
					jQuery.ajax({  
						type: "GET",
						url: _APP_BASE + APP_SCRIPT + "?t="+CURRENT_ACTION+"&f=AJAX_SAVE_CONDIZIONI&DECORATION=clean&ID="+id+"&VALUE="+value
					}).done(function ( response ) {  
						
					}).fail(function ( data ) {  
						
					});
				});
			});
		</script>
<?php 
	}
	else if($actionContext->getForm()=="LEGAME") {
		$detail = new wi400Detail($azione."_LEGAME");
		
		$myField = new wi400InputText('CAMPO');
		$myField->setLabel('Campo');
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$myField->setCase("UPPER");
		$myField->setValue($field_src);
		$myField->setReadonly(true);
		$detail->addField($myField);
		
		$myField = new wi400InputText('LEGAME');
		$myField->setLabel('Campo Legato');
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$myField->setCase("UPPER");
		$myField->setValue($legame);
		
		$where_legame = "OT5KEY='$file_src' and OT5FMT='$form_src'";
		$where_legame .= " and OT5FLD<>'$field_src'";
		$where_legame .= " and OT5FLD not like '*%'";
		$where_legame .= " and OT5FLD not in (select OT5FL1 from ZOT5FLDR where OT5KEY='$file_src' and OT5FMT='$form_src' and OT5FLD<>'$field_src')";
		
		$decodeParameters = array(
			'TYPE' => 'common',
			'TABLE_NAME' => $tabCampi,
			'COLUMN' => 'OT5FLD',
			'KEY_FIELD_NAME' => 'OT5FLD',
			'FILTER_SQL' => $where_legame,
			'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE", $tabCampi);
		$myLookUp->addParameter("CAMPO", "OT5FLD");
		$myLookUp->addParameter("DESCRIZIONE", "OT5FLD");
		$myLookUp->addParameter("LU_WHERE",$where_legame);
		$myLookUp->addParameter("TITLE", "Ricerca Campo Legato");
		$myField->setLookUp($myLookUp);
		
		$detail->addField($myField);
		
		// Salva
		$button = new wi400InputButton('SAVE_BUTTON');
		$button->setLabel("Salva");
		$button->setAction($azione);
		if($has_legame===true)
			$button->setForm('UPDATE_LAGAME');
		else 
			$button->setForm('INSERT_LAGAME');
		$detail->addButton($button);
		
		if($has_legame===true) {
			// Elimina
			$button = new wi400InputButton('DELETE_BUTTON');
			$button->setLabel("Elimina");
			$button->setAction($azione);
			$button->setForm('DELETE_LAGAME');
			$detail->addButton($button);
		}
		
		$detail->dispose();
	}
	else if($actionContext->getForm()=="ADDNEW") {
		$detail = new wi400Detail($azione."_ADDNEW");
		
		$myField = new wi400InputText('CAMPO');
		$myField->setLabel('Campo');
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$myField->setCase("UPPER");
		$myField->setValue("");
		$myField->addValidation("required");
		$detail->addField($myField);
		
		$myField = new wi400InputText('PHPOUT');
		$myField->setLabel('Funzione PHP Rendering Campo');
		$myField->setSize(50);
		$myField->setMaxLength(50);
		$myField->setValue("");
		$myField->addValidation("required");
		$detail->addField($myField);
		
		// Salva
		$button = new wi400InputButton('SAVE_BUTTON');
		$button->setLabel("Salva");
		$button->setAction($azione);
		$button->setValidation(True);
		$button->setForm('INSERT_ADDNEW');
		$detail->addButton($button);
		
		$detail->dispose();
		
	}