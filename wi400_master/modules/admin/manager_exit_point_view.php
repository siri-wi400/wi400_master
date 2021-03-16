<?php

	if($form == "DEFAULT") {
		$miaLista = new wi400List($azione."_TESTATA", true);
		
		$miaLista->setFrom("ZEXIPOIN");
		$miaLista->setOrder("EXID, EXEVENT");
		$miaLista->setAutoUpdateList(true);
		$miaLista->setCallBackFunction("updateRow", "functionUpdateRow");
		
		$miaLista->setIncludeFile("admin", "manager_exit_point_common.php");
		
		//Dettaglio
		$dettaglio = new wi400Column("DETTAGLIO_LOG", "Dettaglio", "", "CENTER");
		$dettaglio->setDecorator("ICONS");
		$dettaglio->setDefaultValue("SEARCH");
		$dettaglio->setSortable(false);
		$dettaglio->setExportable(false);
		$dettaglio->setActionListId($azione."_DET");
		
		//Stato
		$input = new wi400InputCheckbox("CHECK_STATO");
		$input->setUncheckedValue("0");
		$input->setValue("1");
		
		$col_sta = new wi400Column("EXSTA", "Stato", "", "center");
		$col_sta->setInput($input);
		
		$miaLista->setCols(array(
			$dettaglio,
			new wi400Column("EXID", "Id"),
			new wi400Column("EXEVENT", "Evento"),
			new wi400Column("EXTYPE", "Tipo", "", "right"),
			new wi400Column("EXDESC", "Descrizione"),
			new wi400Column("EXPARAM", "Parametri"),
			$col_sta
		));
		
		$miaLista->addKey("EXID");
		$miaLista->addKey("EXEVENT");
		
		$filter = new wi400Filter("EXTYPE", "Tipo");
		$filter->setFast(true);
		$miaLista->addFilter($filter);
		
		// Dettaglio
		$action = new wi400ListAction();
		$action->setId($azione."_DET");
		$action->setAction($azione);
		$action->setForm("RIGHE");
		$action->setLabel("Dettaglio");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		$miaLista->dispose();
	}else if($form == "RIGHE") {
		echo '<link rel="stylesheet" type="text/css" href="themes/common/css/button.css"  media="screen">';
		
		$detail = new wi400Detail($azione."_INFO");
		$detail->setColsNum(2);
		
		$myField = new wi400Text("ID", "Id", $keyTestata['EXID']);
		$detail->addField($myField);
		
		$myField = new wi400Text("EVENTO", "Evento", $keyTestata['EXEVENT']);
		$detail->addField($myField);
		
		$detail->dispose();
		
		echo "<br>";
		
		$miaLista = new wi400List($azione."_RIGHE", true);
		
		$miaLista->setFrom("ZEXIAZIO");
		$miaLista->setWhere(implode(" and ", $where));
		$miaLista->setOrder("EAPRG");
		
		$miaLista->setAutoUpdateList(true);
		$miaLista->setCallBackFunction("updateRow", "functionUpdateRow_det");
		
		$miaLista->setIncludeFile("admin", "manager_exit_point_common.php");
		
		$modifica = new wi400Column("MODIFICA", "Modifica", "", "CENTER");
		$modifica->setDecorator("ICONS");
		$modifica->setDefaultValue("MODIFICA");
		$modifica->setSortable(false);
		$modifica->setExportable(false);
		$modifica->setActionListId($azione."_MODIFICA");
		
		//Stato
		$col_sta = new wi400Column('EASTA', 'Stato', '', 'center');		
//		$col_sta->setDecorator("YES_NO_ICO");
		
		$input = new wi400InputCheckbox("CHECK_STATO");
		$input->setUncheckedValue("0");
		$input->setValue("1");
		$col_sta->setInput($input);
		
		$miaLista->setCols(array(
			$modifica,
			new wi400Column('EACOND', 'Condizioni'),
			new wi400Column('EASAZI', 'Sorgente azione'),
			new wi400Column('EASFRM', 'Sorgente form'),
			new wi400Column('EASGTW', 'Sorgente gateway'),
			new wi400Column('EAPRG', 'Progressivo'),
			new wi400Column('EATAZI', 'Azione'),
			new wi400Column('EATFRM', 'Form'),
			new wi400Column('EATGTW', 'Gateway'),
			new wi400Column('EATCLI', 'Cliente'),
			new wi400Column('EASLCA', 'Last Call'),
			new wi400Column('EASLRU', 'Last Run'),
			$col_sta
		));
		
		$miaLista->addKey("EAPRG");
		
		$hiddenField = new wi400InputHidden("EXITP_ID");
		$hiddenField->setValue($keyTestata['EXID']);
		$hiddenField->dispose();
		
		$hiddenField = new wi400InputHidden("EXITP_EVENTO");
		$hiddenField->setValue($keyTestata['EXEVENT']);
		$hiddenField->dispose();
		
		// Mofifica
		$action = new wi400ListAction();
		$action->setLabel("Modifica");
		$action->setId($azione."_MODIFICA");
		$action->setAction($azione);
		$action->setForm("MODIFICA_RIGA");
		$action->setTarget("WINDOW");
		$miaLista->addAction($action);
		
		//Elimina
		$action = new wi400ListAction();
		$action->setLabel("Elimina");
		$action->setAction($azione);
		$action->setForm("ELIMINA_RIGA");
		$miaLista->addAction($action);
		
		//Dettaglio LOG
		$action = new wi400ListAction();
		$action->setLabel("Dettaglio Log");
		$action->setAction($azione);
		$action->setForm("DETTAGLIO_LOG");
		$miaLista->addAction($action);
		
		$miaLista->dispose();
		
		echo "<br/>";
		
		$button = new wi400InputButton("NEW_MESSAGE");
		$button->setLabel("Nuovo");
		$button->setAction($azione);
		$button->setForm("NUOVA_RIGA");
		$button->setTarget("WINDOW");
		$button->setButtonClass("ccq-button-active");
		$button->setButtonStyle(wi400GetCssButton('85px', "#6899bb", "#2c658b", "white", "black"));
		//$button->setButtonStyle(getCssButton("#F7F7F7", "#C8C8C8", "#5b5a5a", "#A8A8A8"));
		$button->dispose();
	}else if(in_array($form, array("NUOVA_RIGA", "MODIFICA_RIGA"))) {
		$detail = new wi400Detail($azione."_CAMPI_RIGA");
		$detail->setSource($dati);
		
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
		
		//Condizioni
		$myField = new wi400InputTextArea("EACOND");
		$myField->setLabel("Condizioni");
		$myField->setMaxLength(200);
		$myField->setSize(70);
		$myField->setRows(3);
		$myField->setInfo("Condizioni: #=Parametro, §=riga triggerata");
		$detail->addField($myField);
		
		//Sorgente azione
		$myField = new wi400InputText("EASAZI");
		$myField->setLabel("Sorgente azione");

		$myField->setDecode($decodeParameters);
		$myLookUp = new wi400LookUp("LU_AZIONI");
		$myField->setLookUp($myLookUp);
		$detail->addField($myField);
		
		//Sorgente form
		$myField = new wi400InputText("EASFRM");
		$myField->setLabel("Sorgente form");
		$detail->addField($myField);
		
		//Sorgente gateway
		$myField = new wi400InputText("EASGTW");
		$myField->setLabel("Sorgente gateway");
		$detail->addField($myField);
		
		//Azione
		$myField = new wi400InputText("EATAZI");
		$myField->setLabel("Azione");
		$myField->setCase("UPPER");
		$myField->setMaxLength(40);
		$myField->addValidation("required");

		$myField->setDecode($decodeParameters);
		$myLookUp = new wi400LookUp("LU_AZIONI");
		$myField->setLookUp($myLookUp);
		
		$detail->addField($myField);
		
		//Form
		$myField = new wi400InputText("EATFRM");
		$myField->setLabel("Form");
		$detail->addField($myField);
		
		//Gateway
		$myField = new wi400InputText("EATGTW");
		$myField->setLabel("Gateway");
		$detail->addField($myField);
		
		//Cliente
		$myField = new wi400InputText("EATCLI");
		$myField->setLabel("Cliente");
		$detail->addField($myField);
		
		// Elaboraizone Parallelo
		$myField = new wi400InputSwitch("EASBCH");
		$myField->setLabel("Elaborazione parallela");
		$myField->setOnLabel("Attivo");
		$myField->setOffLabel("Non attivo");
		$myField->setChecked($dati['EASBCH'] == "1" ? true : false);
		$myField->setValue("1");
		$detail->addField($myField);
		
		// Wait
		$myField = new wi400InputSwitch("EASWAI");
		$myField->setLabel("Wait elaborazione");
		$myField->setOnLabel("Attivo");
		$myField->setOffLabel("Non attivo");
		$myField->setChecked($dati['EASWAI'] == "1" ? true : false);
		$myField->setValue("1");
		$detail->addField($myField);
		
		// Log 
		$myField = new wi400InputSwitch("EASLOG");
		$myField->setLabel("Attiva Log");
		$myField->setOnLabel("Attivo");
		$myField->setOffLabel("Non attivo");
		$myField->setChecked($dati['EASLOG'] == "1" ? true : false);
		$myField->setValue("1");
		$detail->addField($myField);
		
		// Asincrono
		$myField = new wi400InputSwitch("EASYNC");
		$myField->setLabel("Asincrono");
		$myField->setOnLabel("Si");
		$myField->setOffLabel("No");
		$myField->setChecked($dati['EASYNC'] == "1" ? true : false);
		$myField->setValue("1");
		$detail->addField($myField);

		// Innesco
		$myField = new wi400InputText("EASQUE");
		$myField->setLabel("Coda Processi");
		$detail->addField($myField);
		
		//Stato
		$myField = new wi400InputSwitch("EASTA");
		$myField->setLabel("Stato");
		$myField->setOnLabel("Attivo");
		$myField->setOffLabel("Non attivo");
		$myField->setChecked($dati['EASTA'] == "1" ? true : false);
		$myField->setValue("1");
		$detail->addField($myField);
		
		$button = new wi400InputButton("SALVA");
		$button->setLabel("Salva");
		$button->setAction($azione);
		$button->setForm("SALVA_RIGA");
		$button->setValidation(true);
		$detail->addButton($button);
		
		$detail->dispose();
	}else if($form == "DETTAGLIO_LOG") {
		$detail = new wi400Detail($azione."_INFO");
		$detail->setColsNum(2);
		$keyTestata = getListKeyArray($azione."_TESTATA");
		$keyRighe = getListKeyArray($azione."_RIGHE");
		$prg = $keyRighe['EAPRG'];
		$id = $keyTestata['EXID'];
		$event = $keyTestata['EXEVENT'];
		
		$myField = new wi400Text("ID", "Id", $keyTestata['EXID']);
		$detail->addField($myField);
		$myField = new wi400Text("EVENTO", "Evento", $keyTestata['EXEVENT']);
		$detail->addField($myField);
		$myField = new wi400Text("PROGRESSIVO", "Progressivo", $prg);
		$detail->addField($myField);
		$detail->dispose();
		
		echo "<br>";
		$miaLista = new wi400List($azione."_LOG", true);

		$miaLista->setFrom("ZEXILOGA");
		$where ="EAID='$id' AND EAEVENT='$event' AND EAPRG=$prg";
		$miaLista->setWhere($where);
		$miaLista->setOrder("EATIMC DESC");
		
		$miaLista->setCols(array(
				new wi400Column('EAESI', 'Esito'),
				new wi400Column('EAINTID', 'ID Interno'),
				new wi400Column('EASTR', 'Stringa Parametri'),
				new wi400Column('EASTRR', 'Stringa Ritorno'),
				new wi400Column('EAPRG', 'Progressivo'),
				new wi400Column('EATIMC', 'Timestamp Inserimento'),
				new wi400Column('EATIMA', 'Timestamp Aggiornamento')
		));
		$miaLista->addKey("EAINTID");
		//Elimina LOG LOG
		$action = new wi400ListAction();
		$action->setLabel("Elimina Log");
		$action->setSelection("NONE");
		$action->setAction($azione);
		$action->setForm("ELIMINA_LOG");
		$miaLista->addAction($action);
		// Risotometti EVENTO
		$action = new wi400ListAction();
		$action->setLabel("Risottometti");
		$action->setSelection("SINGLE");
		$action->setAction($azione);
		$action->setForm("RISOTTOMETTI");
		$miaLista->addAction($action);
		
		$miaLista->dispose();
		
	}