<?php
	if(!$checkGroup) {
		die("Accesso negato! Permessi negati per quest'azione.");
	}

	echo '<link rel="stylesheet" type="text/css" href="themes/common/css/button.css"  media="screen">';

	if(in_array($actionContext->getForm(), array("DETAIL_LOG", "CONTENUTO", "ALLEGATI"))) {
		$ListDetail = new wi400Detail("DETAIL_INFO", true);
		$ListDetail->setColsNum(1);
		
		$labelDetail = new wi400Text("ID_MESS", "ID", $key['TESID']);
		$ListDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("TITOLO_MESS", "Messaggio", $key['TITOLO']);
		$ListDetail->addField($labelDetail);
		
		$ListDetail->dispose();
		
		echo "<br/><br/>";
	}
	if($actionContext->getForm()=="DESTINATARI") {
		$ListDetail = new wi400Detail("DETAIL_INFO", true);
		$ListDetail->setColsNum(2);
	
		$labelDetail = new wi400Text("ID_MESS", "ID", $key['TESID']);
		$ListDetail->addField($labelDetail);
	
		$labelDetail = new wi400Text("TITOLO_MESS", "Messaggio", $key['TITOLO']);
		$ListDetail->addField($labelDetail);
	
		$myField = new wi400InputFile("IMPORT_FILE");
		$myField->setLabel("Destinatari da excel");
		$ListDetail->addField($myField);
	
		$button = new wi400InputButton("CARICA_EXCEL");
		$button->setLabel("Carica excel");
		$button->setAction($azione);
		$button->setForm("DESTINATARI_EXCEL");
		$ListDetail->addButton($button);
	
		$labelDetail = new wi400Text("SCARICA_TEMPLATE");
		$labelDetail->setLabel("Scarica template");
		$labelDetail->setValue($files_path);
		$labelDetail->setLink(create_file_download_link($files_path));
		$ListDetail->addField($labelDetail);
	
		$ListDetail->dispose();
	
		echo "<br/><br/>";
	}
	if($actionContext->getForm() == "DEFAULT") {
		$miaLista = new wi400List($azione."_HOME_LIST", true);
		$miaLista->setSelection("SINGLE");
		
		$gruppi = getGroupsMessage();
		
		$select = "TESID, CTTTXT as TITOLO, TESTOL, TESGRP, TESTOV, TESTOR, TESSCA, TESSTA, TESFMT, TESDIV, TESPRY";
		$select .= ", TESARE";
//		$select .= ", (select VALORE from ZTABTABE where TABELLA='MESSAGGIO_AREA' and TIPO<>'D' and ELEMENTO=TESARE) as DES_ARE";
		$select .= ", b.VALORE as DES_ARE";
//		$select .= ", a.TMSMOD";

		$from = "ZMSGTES a left join ZTABTABE b on b.TABELLA='MESSAGGIO_AREA' and b.TIPO<>'D' and b.ELEMENTO=TESARE, ZMSGCTT";
		
		$where = "TESID=CTTID and CTTRIG=0 and TESGRP IN ('', '".implode("', '", $gruppi)."')";
		
		$miaLista->setField($select);
		$miaLista->setFrom($from);
		$miaLista->setwhere($where);
		$miaLista->setOrder("TESID DESC");
		
		$miaLista->setIncludeFile("messaggistica", "manager_messages_function.php");
		
		//echo "Sono qui: ".$miaLista->getSql();
		
		$stato_col = new wi400Column("TESSTA", "Pubblicato", "", "CENTER");
		$stato_col->setDecorator("YES_NO_ICO");
		
		$dettaglio = new wi400Column("DETTAGLIO_LOG", "Log risposte", "", "CENTER");
		$dettaglio->setDecorator("ICONS");
		$dettaglio->setDefaultValue("SEARCH");
		$dettaglio->setSortable(false);
		$dettaglio->setExportable(false);
		$dettaglio->setActionListId($azione."_LOG");
		
		$modifica = new wi400Column("MODIFICA_TES", "Modifica", "", "CENTER");
		$modifica->setDecorator("ICONS");
		$modifica->setDefaultValue("MODIFICA");
		$modifica->setSortable(false);
		$modifica->setExportable(false);
		$modifica->setActionListId($azione."_MODIFICA");
		
		$contenuto = new wi400Column("CONTENUTO", "Contenuto", "", "CENTER");
		$contenuto->setDecorator("ICONE");
		$contenuto->setDefaultValue('EVAL:"TESTO;{$row[\'TESID\']};{$row[\'TESFMT\']}"');
		$contenuto->setSortable(false);
		$contenuto->setExportable(false);
		$contenuto->setActionListId($azione."_CONTENUTO");
		
		$allegati = new wi400Column("ALLEGATI", "Allegati", "", "CENTER");
		$allegati->setDecorator("ICONE");
		$allegati->setDefaultValue('EVAL:"PDF;{$row[\'TESID\']};{$row[\'TESFMT\']}"');
		$allegati->setSortable(false);
		$allegati->setExportable(false);
		$allegati->setActionListId($azione."_ALLEGATI");
		
		$destinatari = new wi400Column("DESTINATARI", "Destinatari", "", "CENTER");
		$destinatari->setDecorator("ICONE");
		$destinatari->setDefaultValue('EVAL:"SEND_MAIL;{$row[\'TESID\']};{$row[\'TESFMT\']}"');
		$destinatari->setSortable(false);
		$destinatari->setExportable(false);
		$destinatari->setActionListId($azione."_DESTINATARI");
		
		// @todo Parametri Aggiuntivi
		$params_col = new wi400Column("PARAMETRI", "Parametri<br>Aggiuntivi", "", "CENTER");
		$params_col->setDecorator("ICONE");
		$params_col->setDefaultValue('EVAL:"COPY;{$row[\'TESID\']};{$row[\'TESFMT\']}"');
		$params_col->setSortable(false);
		$params_col->setExportable(false);
		$params_col->setActionListId($azione."_PARAMS_SEL");
		
		$miaLista->setCols(array(
			$dettaglio,
			$modifica,
				$params_col,
			new wi400Column("TESID", "Id", "", "center"),
			new wi400Column("TITOLO", "Titolo"),
//				new wi400Column("TESARE", "Area Messaggio"),
				new wi400Column("DES_ARE", "Area Messaggio"),
			new wi400Column("TESPRY", "Priorit&agrave;", "", "right"),
			new wi400Column("TESTOV", "Visualizzato", "", "right"),
			new wi400Column("TESTOL", "Letto", "", "right"),
			new wi400Column("TESTOR", "Risposto", "", "right"),
			new wi400Column("TESSCA", "Scadenza", "TIMESTAMP_INZ_BLANK"),
			$contenuto,
			$allegati,
			$destinatari,
			$stato_col,
//				new wi400Column("TMSMOD", "Data Mod.", "TIMESTAMP_INZ_BLANK")
		));
		
		$miaLista->addKey("TESID");
		$miaLista->addKey("TITOLO");
		$miaLista->addKey("TESFMT");
		$miaLista->addKey("TESDIV");
		
		// Dettaglio articolo
		$action = new wi400ListAction();
		$action->setId($azione."_LOG");
		$action->setAction($azione);
		$action->setForm("DETAIL_LOG");
		$action->setLabel("Dettaglio");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		// Modifica messaggio
		$action = new wi400ListAction();
		$action->setId($azione."_MODIFICA");
		$action->setAction($azione);
		$action->setForm("MOD_MESSAGE");
		$action->setLabel("Modifica");
		$action->setTarget("WINDOW");
		$miaLista->addAction($action);
		
		// @todo Parametri Aggiuntivi
		$action = new wi400ListAction();
		$action->setId($azione."_PARAMS_SEL");
		$action->setAction($azione);
		$action->setForm("PARAMS_SEL");
		$action->setLabel("Parametri Aggiuntivi");
		$action->setTarget("WINDOW");
		$miaLista->addAction($action);
		
		// Elimina messaggio
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("ELIMINA_MESSAGE");
		$action->setLabel("Elimina");
		$action->setConfirmMessage("Sei sicuro di voler eliminare questo messaggio?");
		$miaLista->addAction($action);
		
		// CONTENUTO
		$action = new wi400ListAction();
		$action->setId($azione."_CONTENUTO");
		$action->setAction($azione);
		$action->setForm("CONTENUTO");
		$action->setLabel("Contenuto");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		// ALLEGATI
		$action = new wi400ListAction();
		$action->setId($azione."_ALLEGATI");
		$action->setAction($azione);
		$action->setForm("ALLEGATI");
		$action->setLabel("Allegati");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		// DESTINATARI
		$action = new wi400ListAction();
		$action->setId($azione."_DESTINATARI");
		$action->setAction($azione);
		$action->setForm("DESTINATARI");
		$action->setLabel("Destinatari");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		// Pubblica
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("PUBBLICA_MESS");
		$action->setLabel("Pubblica");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		listDispose($miaLista);
		echo "<br/>";
		
		$button = new wi400InputButton("NEW_MESSAGE");
		$button->setLabel("Nuovo messaggio");
		$button->setAction($azione);
		$button->setForm("NEW_MESSAGE");
		$button->setTarget("WINDOW");
		$button->setButtonClass("ccq-button-active");
		$button->setButtonStyle(getCssButton("#6899bb", "#2c658b", "white", "black"));
		//$button->setButtonStyle(getCssButton("#F7F7F7", "#C8C8C8", "#5b5a5a", "#A8A8A8"));
		$button->dispose();
	}else if($actionContext->getForm() == "DETAIL_LOG") {
		$miaLista = new wi400List($azione."_LIST_LOG", true);
		$miaLista->setSelection("SINGLE");
		
		$miaLista->setField("LOGID, LOGUSR, LOGRIS, LOGRPT, LOGNOT, LOGLET, LOGRPY");
		$miaLista->setFrom("ZMSGLOG");
		$miaLista->setwhere("LOGID='".$key['TESID']."'");
		//$miaLista->setGroup("SOCCOD, SOCDES, CLICOD, CLIDES");
		
		//echo "Sono qui: ".$miaLista->getSql();
		
		$miaLista->setIncludeFile("messaggistica", "manager_messages_function.php");
		
		$desc_col = new wi400Column("DESC", "Descrizione");
		$desc_col->setDefaultValue('EVAL:getDescrizione("*USER", $row["LOGUSR"])');
		$desc_col->setSortable(false);
		
		$miaLista->setCols(array(
				new wi400Column("LOGID", "Id"),
				new wi400Column("LOGUSR", "Utente"),
				$desc_col,
				new wi400Column("LOGRIS", "Risposta?"),
				new wi400Column("LOGRPT", "Testo risposta"),
				new wi400Column("LOGNOT", "Visualizzato", "TIMESTAMP_INZ_BLANK"),
				new wi400Column("LOGLET", "Letto", "TIMESTAMP_INZ_BLANK"),
				new wi400Column("LOGRPY", "Risposto il", "TIMESTAMP_INZ_BLANK")
		));
		
		//$miaLista->addKey("TESID");
		
		// Dettaglio articolo
		$action = new wi400ListAction();
		$action->setId($azione."_LOG");
		$action->setAction($azione);
		$action->setForm("DETAIL_LOG");
		$action->setLabel("Dettaglio");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		listDispose($miaLista);
	}
	else if(in_array($actionContext->getForm(), array("NEW_MESSAGE", "MOD_MESSAGE"))) {
		$tespub = date('d/m/Y');
		$form = "";
		if($actionContext->getForm() == "MOD_MESSAGE") {
			$sql = "SELECT a.*, b.CTTTXT as TITOLO
					FROM ZMSGTES a, ZMSGCTT b
					WHERE TESID=CTTID and CTTRIG=0 and TESID='{$key['TESID']}'";
			
			$rs = $db->query($sql);
			if($rs) {
				$row = $db->fetch_array($rs);
			}
		}else {
			$row = getDs("ZMSGTES");
			$row['TESPRY'] = "";
			$row['TITOLO'] = "";
			$row['TESGRP'] = "";
		}
		
		//showArray($row);
		
		list($gg, $mm, $aaaa) = explode("/", $tespub);
		$tessca = date('d/m/Y',strtotime($gg."-".$mm."-".$aaaa."+10 year"));
		
		//echo $tespub."<br/>";
		//echo $tessca."<br/>";
		if(isset($_REQUEST['CLEAR_DETAIL_MESSAGE'])) {
			$ListDetail = new wi400Detail("DETAIL_NEW_MESSAGE", false);
		}else {
			$ListDetail = new wi400Detail("DETAIL_NEW_MESSAGE", true);
		}
		$ListDetail->setColsNum(1);
		
		
		$fieldHidden = new wi400InputHidden("TESID");
		$fieldHidden->setValue($key['TESID']);
		$fieldHidden->dispose();
		
		$gruppi = getGroupsMessage();
		
		if(count($gruppi) == 1) {
			$inputDetail = new wi400Text("TESGRP", "Gruppo", $gruppi[0]);
			$ListDetail->addField($inputDetail);
		}else {
			$inputDetail = new wi400InputSelect("TESGRP");
			$inputDetail->setLabel("Gruppo");
			$inputDetail->setFirstLabel("-- scegli un gruppo --");
			//$inputDetail->addOption("-- scegli un gruppo --");
			foreach($gruppi as $valore) {
				$inputDetail->addOption($valore);
			}
			$inputDetail->setValue($row['TESGRP']);
			$inputDetail->addValidation("required");
			$ListDetail->addField($inputDetail);
		}
		
		$inputDetail = new wi400InputText("TITOLO");
		$inputDetail->setLabel("Titolo");
		$inputDetail->setSize(50);
		$inputDetail->setValue($row['TITOLO']);
		$inputDetail->addValidation("required");
		$ListDetail->addField($inputDetail);
		
		if($row['TITOLO']) {
			$inputDetail = new wi400InputHidden("OLD_TITOLO");
			$inputDetail->setValue($row['TITOLO']);
			$ListDetail->addField($inputDetail);
		}
		
		$inputDetail = new wi400InputSelect("TESFMT");
		$inputDetail->setLabel("Formato");
		$inputDetail->addOption("TXT");
		$inputDetail->addOption("HTML");
		$inputDetail->setValue($row['TESFMT']);
		$ListDetail->addField($inputDetail);
		
		//Tipo di messaggio
		$inputDetail = new wi400InputSelect("TESTYP");
		$inputDetail->setLabel("Tipo");
		$inputDetail->addOption("INFO");
		$inputDetail->addOption("WARNING");
		$inputDetail->addOption("ERROR");
		$inputDetail->addOption("PRODOTTO");
		$inputDetail->addOption("SEGRETERIA");
		$inputDetail->setValue($row['TESTYP']);
		$ListDetail->addField($inputDetail);
		
		//Tipo notifica
		$inputDetail = new wi400InputSelect("TESTNO");
		$inputDetail->setLabel("Tipo notifica");
		$inputDetail->addOption("*NONE");
		$inputDetail->addOption("E-MAIL");
		$inputDetail->addOption("SMS");
		$inputDetail->setValue($row['TESTNO']);
		$ListDetail->addField($inputDetail);
		
		//Messaggio news
		$inputDetail = new wi400InputSwitch("TESCLE");
		$inputDetail->setLabel("Conferma lettura");
		$inputDetail->setOnLabel("SI");
		$inputDetail->setOffLabel("NO");
		$inputDetail->setChecked($row['TESCLE'] == "S" || $row['TESCLE'] == '' ? true : false);
		$inputDetail->setValue("S");
		$ListDetail->addField($inputDetail);
		
		//Richiede risposta
		$inputDetail = new wi400InputSwitch("TESRPY");
		$inputDetail->setLabel("Richiede risposta?");
		$inputDetail->setOnLabel("SI");
		$inputDetail->setOffLabel("NO");
		$inputDetail->setChecked($row['TESRPY'] == "S" ? true : false);
		$inputDetail->setValue("S");
		$ListDetail->addField($inputDetail);
		
		$inputDetail = new wi400InputSelect("TESRPYT");
		$inputDetail->setLabel("Tipo riposta");
		$inputDetail->addOption("TXT");
		$inputDetail->addOption("SI-NO");
		$inputDetail->setValue($row['TESRPYT']);
		$ListDetail->addField($inputDetail);
		
		//Reply to e-mail
		$inputDetail = new wi400InputText("TESTO");
		$inputDetail->setLabel("Invia risposta a mail");
		$inputDetail->setSize(20);
		$inputDetail->setValue($row['TESTO']);
		$inputDetail->addValidation("email");
		//$inputDetail->addValidation("required");
		$ListDetail->addField($inputDetail);
		
		$inputDetail = new wi400InputSelect("TESVIS");
		$inputDetail->setLabel("Visualizzazione");
		$inputDetail->addOption("*HOME");
		$inputDetail->addOption("*ACTION");
		$inputDetail->setValue($row['TESVIS']);
		$inputDetail->setOnChange($onChange);
		$ListDetail->addField($inputDetail);
		
		$inputDetail = new wi400InputSwitch("TESPRV");
		$inputDetail->setLabel("Messaggio chiuso");
		$inputDetail->setOnLabel("SI");
		$inputDetail->setOffLabel("NO");
		$inputDetail->setChecked($row['TESPRV'] == "S" ? true : false);
		$inputDetail->setValue("S");
		$ListDetail->addField($inputDetail);
		
		// Gestione delle azioni
		$myField = new wi400InputText('TESAZI');
		$myField->setLabel("Azione");
		$myField->setCase("UPPER");	
		$myField->setMaxLength(40);
		$myField->setValue($row['TESAZI']);
		$myField->setInfo(_t("ACTION_CODE_INFO"));
		
		$decodeParameters = array(
			'TYPE'=> 'common',
			'COLUMN' => 'DESCRIZIONE',
			'TABLE_NAME' => 'FAZISIRI',
			'KEY_FIELD_NAME' => 'AZIONE',
			'AJAX' => true,
			'COMPLETE' => true,
			'COMPLETE_MIN' => 2,
			'COMPLETE_MAX_RESULT' => 15
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_AZIONI");
		$myField->setLookUp($myLookUp);
		
		$ListDetail->addField($myField);
		
		$inputDetail = new wi400InputSelect("TESDIV");
		$inputDetail->setLabel("Divulgazione");
		$inputDetail->addOption("*LOGIN");
		$inputDetail->addOption("*IMMED");
		$inputDetail->setValue($row['TESDIV']);
		$ListDetail->addField($inputDetail);
		
		//Data pubblicazione
		$inputDetail = new wi400InputText("TESPUB");
		$inputDetail->setLabel("Data pubblicazione");
		$inputDetail->addValidation('date');
		$inputDetail->addValidation("required");
		$inputDetail->setValue($row['TESPUB'] ? wi400_format_SHORT_TIMESTAMP($row['TESPUB']) : $tespub);
		$ListDetail->addField($inputDetail);
		
		//Data scadenza
		$inputDetail = new wi400InputText("TESSCA");
		$inputDetail->setLabel("Data scadenza");
		$inputDetail->addValidation('date');
		$inputDetail->addValidation("required");
		$inputDetail->setValue($row['TESSCA'] ? wi400_format_SHORT_TIMESTAMP($row['TESSCA']) : $tessca);
		$ListDetail->addField($inputDetail);
		
		$inputDetail = new wi400InputText("TESPRY");
		$inputDetail->setLabel("Priorit&agrave;");
		$inputDetail->setMaxLength("3");
		$inputDetail->setSize("3");
		//$inputDetail->setValue("1");
		$inputDetail->setValue($row['TESPRY'] ? $row['TESPRY'] : "1");
		$inputDetail->setMask("123456789");
		$inputDetail->addValidation("required");
		$ListDetail->addField($inputDetail);
	
		// Area messaggio
		$myField = new wi400InputText('TESARE');
		$myField->setLabel("Area Messaggio");
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$myField->setCase("UPPER");
		$myField->setValue($row['TESARE']);
		
		$decodeParameters = array(
			'TYPE' => 'common',
			'TABLE_NAME' => "ZTABTABE",
			'COLUMN' => 'VALORE',
			'KEY_FIELD_NAME' => 'ELEMENTO',
			'FILTER_SQL' => "TABELLA='MESSAGGIO_AREA' and TIPO<>'D'",
			'AJAX' => true,
			'COMPLETE' => true,
			'COMPLETE_MIN' => 2,
			'COMPLETE_MAX_RESULT' => 15
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE", "ZTABTABE");
		$myLookUp->addParameter("CAMPO", "ELEMENTO");
		$myLookUp->addParameter("DESCRIZIONE", "VALORE");
		$myLookUp->addParameter("LU_WHERE","TABELLA='MESSAGGIO_AREA' and TIPO<>'D'");
		$myField->setLookUp($myLookUp);
		
		$ListDetail->addField($myField);
		
		$inputDetail = new wi400InputSwitch("TESEVR");
		$inputDetail->setLabel("Sempre visibile (fino scadenza)");
		$inputDetail->setOnLabel("SI");
		$inputDetail->setOffLabel("NO");
		$inputDetail->setChecked($row['TESEVR'] == "S" ? true : false);
		$inputDetail->setValue("S");
		$ListDetail->addField($inputDetail);
		
		// Salva
		$myButton = new wi400InputButton('SAVE_BUTTON');
		$myButton->setLabel("Salva");
		$myButton->setAction($azione);
		if($actionContext->getForm() == "MOD_MESSAGE") {
			$myButton->setForm("UPDATE_MESSAGE");
		}else {
			$myButton->setForm("INSERT_MESSAGE");
		}
		$myButton->setConfirmMessage("Salvare?");
		$myButton->setValidation(true);
		$ListDetail->addButton($myButton);
		
		// Parametri aggiuntivi
		$myButton = new wi400InputButton('PARAMS_BUTTON');
		$myButton->setLabel("Parametri Aggiuntivi");
		$myButton->setAction($azione);
		$myButton->setForm("PARAMS_SEL");
		$myButton->setTarget("WINDOW");
		$myButton->setValidation(true);
		$ListDetail->addButton($myButton);
		
		// Annulla
		$myButton = new wi400InputButton('BACK_BUTTON');
		$myButton->setLabel("Annulla");
		$myButton->setAction("CLOSE");
		$myButton->setForm("CLOSE_LOOKUP");
		//$myButton->addParameter("CLEAN_DETAIL", $idDetail);
		$ListDetail->addButton($myButton);
		
		$ListDetail->dispose();
	}
	else if($actionContext->getForm()=="PARAMS_SEL") {
		// @todo Parametri Aggiuntivi
		$key = getListKeyArray($azione."_HOME_LIST");
		$miaLista = new wi400List($azione."_LIST_PARAMETRI", true);
		$miaLista->setSelection("SINGLE");
		
		$miaLista->setField("PRMPRM, PRMVAL");
		$miaLista->setFrom("ZMSGPRM");
		$miaLista->setwhere("PRMID='".$key['TESID']."'");
		
		$colonna1 = new wi400Column("PRMPRM", "Parametro");
		$colonna2 = new wi400Column("PRMVAL", "Valore");
		$miaLista->addCol($colonna1);
		$miaLista->addCol($colonna2);
		
		$miaLista->dispose();
		
	}
	else if($actionContext->getForm() == "INSERT_MESSAGE") {
		
	}else if($actionContext->getForm() == "CONTENUTO") {
		$ListDetail = new wi400Detail("DETAIL_CONTENUTO", true);
		$ListDetail->setColsNum(1);
		
		if($key['TESFMT'] == "TXT") {
			$fieldText = new wi400InputTextArea("AREA_CONTENUTO");
			$fieldText->setLabel("Testo");
			$fieldText->setRows(20);
			$fieldText->setSize(60);
			$fieldText->setStyle("resize: vertical;");
			$fieldText->setWrap(true);
			if($contenuto) {
				$fieldText->setValue($contenuto);
			}
			$ListDetail->addField($fieldText);
		}else {
			$editor = new wi400InputEditor("EDITOR");
			$editor->setValue($contenuto);
			$editor->addValidation("required");
			$editor->dispose();
		}
		
		if($contenuto) {
			$hiddenField = new wi400InputHidden("UPDATE");
			$hiddenField->setValue("1");
			$hiddenField->addValidation("required");
			$ListDetail->addField($hiddenField);
		}
		
		// Salva
		$myButton = new wi400InputButton('SAVE_BUTTON');
		$myButton->setLabel("Salva");
		$myButton->setAction($azione);
		$myButton->setForm("INSERT_CONTENUTO_MESSAGE");
		$myButton->setConfirmMessage("Salvare?");
		$myButton->setValidation(true);
		$ListDetail->addButton($myButton);
		
		// Annulla
		$myButton = new wi400InputButton('BACK_BUTTON');
		$myButton->setLabel("Annulla");
		$myButton->setAction($azione);
		$myButton->setForm("");
		//$myButton->addParameter("CLEAN_DETAIL", $idDetail);
		$ListDetail->addButton($myButton);
		
		$ListDetail->dispose();
	}else if($actionContext->getForm() == "ALLEGATI") {
		
		//echo $data_path."<br/>";
		//wi400_mkdir($data_path."messages");
		
		//showArray($key);
		
		$ListDetail = new wi400Detail("DETAIL_IMPORT_ALLEGATO", true);
		$ListDetail->setTitle("Importa allegati");
		$ListDetail->setColsNum(1);
		
		$fieldText = new wi400InputFile("IMPORT_FILE");
		$fieldText->setLabel("Importa file");
		$ListDetail->addField($fieldText);
		
		// Importa
		$myButton = new wi400InputButton('IMPORT_BUTTON');
		$myButton->setLabel("Importa");
		$myButton->setAction($azione);
		$myButton->setForm("IMPORT_FILE");
		$ListDetail->addButton($myButton);
		
		$ListDetail->dispose();
		echo "<br/><br/>";
		
		$miaLista = new wi400List($azione."_LIST_ALLEGATI", true);
		$miaLista->setSelection("SINGLE");
		
		$miaLista->setField("ATCID, ATCATC");
		$miaLista->setFrom("ZMSGATC");
		$miaLista->setwhere("ATCID='".$key['TESID']."'");
		
		$allegato_col = new wi400Column("ATCATC", "Nome allegato");
		$allegato_col->setActionListId("MOSTRA");
		
		$miaLista->setCols(array(
				//new wi400Column("ATCATC", "Nome allegato")
				$allegato_col
		));
		
		$miaLista->addKey("ATCATC");
		
		// Mostra allegato
		$action = new wi400ListAction("Mostra");
		$action->setId("MOSTRA");
		$action->setAction("ANNOUNCE_MESSAGE");
		$action->setForm("EXPORT_FILE");
		$action->setLabel("Mostra allegato");
		$action->setSelection("SINGLE");
		$action->setTarget("WINDOW", '800', '500');
		$miaLista->addAction($action);
		
		// Elimina allegato
		$action = new wi400ListAction("Elimina");
		//$action->setId($azione."_DETTAGLIO");
		$action->setAction($azione);
		$action->setForm("ELIMINA_ALLEGATO");
		$action->setLabel("Elimina");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		listDispose($miaLista);	
	}else if($actionContext->getForm() == "DESTINATARI") {
		$miaLista = new wi400List($azione."_LIST_DESTINATARI", true);
		$miaLista->setSelection("MULTIPLE");
		
		$miaLista->setField("DSTID, DSTTYP, DSTDST, DSTIOE, DSTOOA, DSTSEQ, DSTSTA");
//		$miaLista->setFrom("PHPLIB/ZMSGDST");
		$miaLista->setFrom("ZMSGDST");
		$miaLista->setwhere("DSTID='".$key['TESID']."'");
		
		//echo $miaLista->getSql();
		$miaLista->setIncludeFile("messaggistica", "manager_messages_function.php");
		
		$mod_col = new wi400Column("MOD_DEST", "Modifica", "", "CENTER");
		$mod_col->setDecorator("ICONS");
		$mod_col->setDefaultValue("MODIFICA");
		$mod_col->setSortable(false);
		$mod_col->setExportable(false);
		$mod_col->setActionListId($azione."_MOD_DEST");
		
		$desc_col = new wi400Column("DESC", "Descrizione");
		$desc_col->setDefaultValue('EVAL:getDescrizione($row["DSTTYP"], $row["DSTDST"])');
		$desc_col->setSortable(false);
		
		$miaLista->setCols(array(
			$mod_col,
			new wi400Column("DSTTYP", "Tipo destinatario", "", "CENTER"),
			new wi400Column("DSTDST", "Destinatario", "", "CENTER"),
			$desc_col,
			new wi400Column("DSTIOE", "Includi/Escludi", "", "CENTER"),
			new wi400Column("DSTOOA", "OR/AND", "", "CENTER"),
			new wi400Column("DSTSEQ", "Sequenza", "", "CENTER"),
//				new wi400Column("DSTSTA", "Stato")
		));
		
		$miaLista->addKey("DSTTYP");
		$miaLista->addKey("DSTDST");
		$miaLista->addKey("DSTIOE");
		$miaLista->addKey("DSTOOA");
		$miaLista->addKey("DSTSEQ");
		
		// Modifica destinatario
		$action = new wi400ListAction();
		$action->setId($azione."_MOD_DEST");
		$action->setAction($azione);
		$action->setForm("MOD_DESTINATARIO");
		$action->setLabel("Modifica destinatario");
		$action->setTarget("WINDOW");
		$miaLista->addAction($action);
		
		// Elimina destinatario
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("ELIMINA_DESTINATARIO");
		$action->setLabel("Elimina");
		$miaLista->addAction($action);
		
		// Elimina destinatario
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("SVUOTA_DESTINATARIO");
		$action->setLabel("Svuota");
		$action->setSelection("NONE");
		$miaLista->addAction($action);
		
		listDispose($miaLista);
		
		echo "<br/><br/>";
		
		$button = new wi400InputButton("BUTTON_INSERT");
		$button->setLabel("Inserisci destinatario");
		$button->setAction($azione);
		$button->setForm("NEW_DESTINATARIO");
		$button->setTarget("WINDOW");
		$button->setButtonClass("ccq-button-active");
		$button->setButtonStyle(getCssButton("#6899bb", "#2c658b", "white", "black"));
		//$button->setButtonStyle(getCssButton("#F7F7F7", "#C8C8C8", "#5b5a5a", "#A8A8A8"));
		$button->dispose();
		
		echo "&nbsp;&nbsp;&nbsp;";
		
		$button = new wi400InputButton("BUTTON_SIMULA");
		$button->setLabel("Simula divulgazione");
		$button->setAction($azione);
		$button->setForm("SIMULA_DIVULGAZIONE");
		$button->setTarget("WINDOW");
		$button->setButtonClass("ccq-button-active");
		$button->setButtonStyle(getCssButton("#6899bb", "#2c658b", "white", "black"));
		//$button->setButtonStyle(getCssButton("#F7F7F7", "#C8C8C8", "#5b5a5a", "#A8A8A8"));
		$button->dispose();
		
		echo "&nbsp;&nbsp;&nbsp;";
		
		if($key['TESDIV'] == "*LOGIN") {
			$button = new wi400InputButton("BUTTON_MULTI_INSERT");
			$button->setLabel("Inserimento massivo clienti");
			$button->setAction($azione);
			$button->setForm("MULTI_DESTINATARI");
			$button->setTarget("WINDOW");
			$button->setButtonClass("ccq-button-active");
			$button->setButtonStyle(getCssButton("#6899bb", "#2c658b", "white", "black"));
			//$button->setButtonStyle(getCssButton("#F7F7F7", "#C8C8C8", "#5b5a5a", "#A8A8A8"));
			$button->dispose();
		}
	}else if(in_array($actionContext->getForm(), array("NEW_DESTINATARIO", "MOD_DESTINATARIO"))) {
		$form = "";
		
		if($actionContext->getForm() == "MOD_DESTINATARIO") {
			$form = "MOD";
		}
		
		//showArray($dest);
		//showArray($_SESSION['WI400_GROUPS']);
		$clear = true;
		if(isset($error_vali) && $error_vali) {
			$clear = false;
		}
		
		$fieldHidden = new wi400InputHidden("TESDIVULG");
		$fieldHidden->setValue($key['TESDIV']);
		$fieldHidden->dispose();
		
		$ListDetail = new wi400Detail("DETAIL_DESTINATARI", $clear);
		$ListDetail->setColsNum(1);
		
		if($form) {
			$inputDetail = new wi400InputHidden("OLD_DST");
			$inputDetail->setValue($dest['DSTDST']);
			$ListDetail->addField($inputDetail);
		}
		
		//Tipo destinatario
		$inputSelect = new wi400InputSelect("DSTTYP");
		$inputSelect->setLabel("Tipo destinatario");
		$inputSelect->addOption("*USER");
		$inputSelect->addOption("*GRUPPO");
		$inputSelect->addOption("*INT");
		$inputSelect->addOption("*ENTE");
		if($form || isset($_REQUEST['DSTTYP'])) {
			$inputSelect->setValue($dest['DSTTYP']);
		}else {
			$inputSelect->setValue("*USER");
		}
		$inputSelect->setOnChange("doSubmit('$azione', '{$actionContext->getForm()}')");
		//$inputDetail->setValue("GRUPPO");
		$ListDetail->addField($inputSelect);
		
		
		// DESTINATARIO
		$inputDetail = new wi400InputText("DSTDST");
		$inputDetail->setLabel("Destinatario");
		$inputDetail->setMaxLength(30);
		$inputDetail->setCase("UPPER");
		$inputDetail->addValidation("required");
		if($form) {
			$inputDetail->setValue($dest['DSTDST']);
		}
		
		switch ($inputSelect->getValue()) {
			case "*USER": 	//$sql = "SELECT USER_NAME, EMAIL, FIRST_NAME, LAST_NAME FROM ".$users_table." UNION ALL VALUES('*ALL', 'Tutti gli utenti', '','Tutti gli utenti')";
							$decodeParameters = array(
									'TYPE'=> 'common',
									'COLUMN' => 'EMAIL',
									'TABLE_NAME' => 'SIR_USERS',
									'KEY_FIELD_NAME' => 'USER_NAME',
									'SPECIAL_VALUE' => array('*ALL' => "Tutti gli utenti"),
									'AJAX' => true,
									'COMPLETE' => true,
									'COMPLETE_MIN' => 2,
									'COMPLETE_MAX_RESULT' => 15
							);
							$inputDetail->setDecode($decodeParameters);
							
							$myLookUp =new wi400LookUp("LU_GENERICO");
							$myLookUp->addParameter("CAMPO","USER_NAME");
							$myLookUp->addParameter("DESCRIZIONE","EMAIL");
							$myLookUp->addParameter("FILE", $users_table);
//							$myLookUp->addParameter("SPECIAL_VALUE", array('*ALL' => "Tutti gli utenti"));
							//$myLookUp->addParameter("DIRECT_SQL", base64_encode($sql));
							$myLookUp->addParameter("LU_SELECT", "FIRST_NAME|LAST_NAME");
							$myLookUp->addParameter("LU_AS_TITLES", "Nome|Cognome");
							
							$inputDetail->setInfo("Inserire un codice Utente specifico o *ALL per tutti gli utenti");
							
							break;
			case "*INT": $decodeParameters = array(
									'TYPE' => 'interlocutore',
									'AJAX' => true,
									'COMPLETE' => true,
									'COMPLETE_MIN' => 2,
									'COLUMN' => 'MEBRAG',
									'COMPLETE_MAX_RESULT' => 15
							);
							$inputDetail->setDecode($decodeParameters);
							
							$myLookUp = new wi400LookUp("LU_INTER");
							break;
			case "*ENTE": $decodeParameters = array(
									'TYPE' => 'ente',
									'AJAX' => true,
									'COMPLETE' => true,
									'COLUMN' => "MAFDSE",
									'COMPLETE_MIN' => 2,
									'COMPLETE_CUSTOM_HIGHLIGHT' => True,
									'COMPLETE_MAX_RESULT' => 15
							);
							$inputDetail->setDecode($decodeParameters);
							
							$myLookUp =new wi400LookUp("LU_ENTI");
							break;
		}

		if($inputSelect->getValue() != "*GRUPPO") {
			$myLookUp->addField("DSTDST");
			$inputDetail->setLookUp($myLookUp);
		}
		$ListDetail->addField($inputDetail);
		
		//Incluso o escludo
		$inputDetail = new wi400InputSelect("DSTIOE");
		$inputDetail->setLabel("Includi/Escludi");
		$inputDetail->addOption("Includi");
		$inputDetail->addOption("Escludi");
		if($form) {
			$inputDetail->setValue($dest['DSTIOE'] == "I" ? "Includi" : "Escludi");
		}
		$ListDetail->addField($inputDetail);
		
		// OR / AND 
		$inputDetail = new wi400InputSelect("DSTOOA");
		$inputDetail->setLabel("AND/OR");
		$inputDetail->addOption("OR");
		$inputDetail->addOption("AND");
		if($form) {
			$inputDetail->setValue($dest['DSTOOA']);
		}
		$ListDetail->addField($inputDetail);
		
		$inputDetail = new wi400InputText("DSTSEQ");
		$inputDetail->setLabel("Sequenza");
		$inputDetail->setValue("1");
		$inputDetail->addValidation("required");
		$inputDetail->setMaxLength(3);
		$inputDetail->setSize(4);
		$inputDetail->setMask("0123456789");
		if($form) {
			$inputDetail->setValue($dest['DSTSEQ']);
		}
		$ListDetail->addField($inputDetail);
		
		// Salva
		$myButton = new wi400InputButton('SAVE_BUTTON');
		$myButton->setLabel("Salva");
		$myButton->setAction($azione);
		if($form) {
			$myButton->setForm("UPDATE_DESTINATARIO");
		}else {
			$myButton->setForm("INSERT_DESTINATARIO");
		}
		$myButton->setConfirmMessage("Salvare?");
		$myButton->setValidation(true);
		$ListDetail->addButton($myButton);
		
		// Annulla
		$myButton = new wi400InputButton('BACK_BUTTON');
		$myButton->setLabel("Annulla");
		$myButton->setAction("CLOSE");
		$myButton->setForm("CLOSE_LOOKUP");
		$ListDetail->addButton($myButton);
		
		$ListDetail->dispose();
	}else if($actionContext->getForm() == "MULTI_DESTINATARI") {
		echo "<div style='width: 100%; text-align: center;'>";
			$button = new wi400InputButton("BUTTON_INSERT_CLIENTI");
			$button->setLabel("Inserimento da lista clienti");
			$button->setAction("MESSAGES_CLI_ADD");
			$button->setForm("");
			$button->setButtonClass("ccq-button-active");
			$button->setButtonStyle(getCssButton("#6899bb", "#2c658b", "white", "black"));
			//$button->setButtonStyle(getCssButton("#F7F7F7", "#C8C8C8", "#5b5a5a", "#A8A8A8"));
			$button->dispose();
			
			echo "<br/><br/>";
			
			$button = new wi400InputButton("BUTTON_MULTI_INSERT");
			$button->setLabel("Inserimento da tabella V014");
			$button->setAction("MESSAGES_GRP_CLI_IMP");
			$button->setForm("");
			$button->setButtonClass("ccq-button-active");
			$button->setButtonStyle(getCssButton("#6899bb", "#2c658b", "white", "black"));
			//$button->setButtonStyle(getCssButton("#F7F7F7", "#C8C8C8", "#5b5a5a", "#A8A8A8"));
			$button->dispose();
		echo "</div>";
	}else if($actionContext->getForm() == "INSERT_DESTINATARIO") {
		//$actionContext->gotoAction("CLOSE", "CLOSE_WINDOW");
	}else if($actionContext->getForm() == "UPDATE_DESTINATARIO") {
		$actionContext->gotoAction("CLOSE", "CLOSE_WINDOW");
	}else if($actionContext->getForm() == "SIMULA_DIVULGAZIONE") {
		//echo $key['TESID'];
		
		if(!$checkEntiInt) {
			$announce = new wi400AnnounceMessage();
			$utenti = $announce->simulaMessageId($key['TESID']);
			
			//showArray($utenti);
			
			if($utenti['DIVULGATI']) {
				$mess = "Il messaggio sarà divulgato a <font style='font-size: 22px;'>".$utenti['DIVULGATI']."</font>";
				if($utenti['DIVULGATI'] == 1) {
					$mess .= " utente:<br/>";
				}else {
					$mess .= " utenti:<br/>";
				}
			}else {
				$mess = "<center><h3>Nessuna divulgazione</h3></center>";
			}
			
			echo $mess;
			foreach($utenti['UTENTI'] as $valore) {
				echo $valore."<br/>";
			}
		}else {
			echo "<center><h3>Simulazione non possibile! Sono presenti interlocutori o enti.</h3></center>";
		}
	}
	
	
	
	