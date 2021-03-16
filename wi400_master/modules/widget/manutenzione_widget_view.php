<?php

	if($form == "DEFAULT") {	
		$miaLista = new wi400List($azione."_LIST", true);
		$miaLista->setSelection("SINGLE");
		
		$miaLista->setIncludeFile("widget", "manutenzione_widget_commons.php");
		
		$miaLista->setFrom("ZWIDGUSR");
		if(!$_SESSION['user_admin']) {
			$miaLista->setWhere("WIDUSR='{$_SESSION['user']}'");
		}
		$miaLista->setOrder("WIDUSR, WIDRIG");
		
		/*$dettaglio_col = new wi400Column("DETTAGLIO", "Dettaglio", "", "CENTER");
		$dettaglio_col->setDecorator("ICONS");
		$dettaglio_col->setDefaultValue("SEARCH");
		$dettaglio_col->setActionListId($azione."_DETTAGLIO");
		$dettaglio_col->setSortable(false);
		$dettaglio_col->setExportable(false);*/
		
		$modifica_col = new wi400Column("MODIFICA", "Modifica", "", "CENTER");
		$modifica_col->setDecorator("NOTE_ICONS");
		$modifica_col->setDefaultValue("1");
		$modifica_col->setActionListId("MODIFICA");
		$modifica_col->setSortable(false);
		$modifica_col->setExportable(false);
		
		$stato_col = new wi400Column("WIDSTA", "Stato", "", "CENTER");
		$stato_col->setDecorator("YES_NO_ICO");
		
		$in_menu_col = new wi400Column("WIDDOC", "In menù", "", "CENTER");
		$in_menu_col->setDecorator("YES_NO_ICO");
		
		$check_param_col = new wi400Column("CHECK_PARAM", "Parametri", "", "CENTER");
		$check_param_col->setDecorator("YES_NO_ICO");
		$check_param_col->setDefaultValue('EVAL:check_param($row)');
		
		$cols = array();
		$cols[] = $modifica_col;
		
		if($_SESSION['user_admin']) {
			$cols[] = new wi400Column("WIDUSR", "Utente");
		}
		
		$cols2 = array(
			new wi400Column("WIDAZI", "Azione"),
			new wi400Column("WIDPRG", "Progressivo", "", "right"),
			new wi400Column("WIDCOL", "Larghezza<br/>in colonne", "", "right"),
			new wi400Column("WIDRIG", "Posizione", "", "right"),
			$in_menu_col,
			$check_param_col,
			$stato_col
		);
		
		$miaLista->setCols(array_merge($cols, $cols2));
		
		// Aggiunta chiavi di lista
		$miaLista->addKey("WIDAZI");
		$miaLista->addKey("WIDPRG");
		$miaLista->addKey("WIDDOC");
		$miaLista->addKey("WIDUSR");
		$miaLista->addKey("WIDCOL");
		
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("ADD_WIDGET");
		$action->setLabel("Aggiungi");
		$action->setTarget("WINDOW");
		$action->setSelection("NONE");
		$miaLista->addAction($action);
		
		$action = new wi400ListAction();
		$action->setId("MODIFICA");
		$action->setAction($azione);
		$action->setForm("MODIFICA");
		$action->setLabel("Modifica");
		$action->setTarget("WINDOW");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("ELIMINA");
		$action->setLabel("Elimina");
		$action->setSelection("MULTIPLE");
		$miaLista->addAction($action);
		
		$miaLista->dispose();
	}else if($form == "ADD_WIDGET") {
		$miaLista = new wi400List($azione."_ALL_WIDGET", true);
		$miaLista->setSelection("MULTIPLE");
		
		$miaLista->setField("AZIONE, DESCRIZIONE");
		$miaLista->setFrom("FAZISIRI");
		$miaLista->setWhere("HAS_WIDGET='1'");
		$miaLista->setOrder("AZIONE");
		
		$miaLista->setCols(array(
				new wi400Column("AZIONE", "Azione"),
				new wi400Column("DESCRIZIONE", "Descrizione")
		));
		
		// Aggiunta chiavi di lista
		$miaLista->addKey("AZIONE");
		
		$miaLista->dispose();
		
		echo "<br/>";
		
		$button = new wi400InputButton("CONFERMA");
		$button->setAction($azione);
		$button->setForm("AGGIUNGI_WIDGET");
		$button->setLabel("Aggiungi");
		
		if($_SESSION['user_admin']) {
			$detail = new wi400Detail($azione."_ADD_WIDGET", true);
			
			$myField = new wi400InputText('codusr');
			$myField->setLabel("Utente");
			$myField->addValidation('required');
			$myField->setMaxLength(20);
			$myField->setCase("UPPER");
			$myField->setInfo(_t('USER_CODE_INFO'));
			$myField->setValue($_SESSION['user']);
			
			if (isset($settings['only_as_user']) && $settings['only_as_user']==True) {
				$decodeParameters = array(
						'TYPE' => 'i5_object',
						'OBJTYPE' => '*USRPRF'
				);
				$myField->setDecode($decodeParameters);
			} else {
				$decodeParameters = array(
						'TYPE'=> 'common',
						'COLUMN' => 'LAST_NAME',
						'TABLE_NAME' => 'SIR_USERS',
						'KEY_FIELD_NAME' => 'USER_NAME',
						'AJAX' => true,
						'COMPLETE' => true,
						'COMPLETE_MIN' => 2,
						'COMPLETE_MAX_RESULT' => 15
				);
				$myField->setDecode($decodeParameters);
			}
			$myLookUp =new wi400LookUp("LU_GENERICO");
			$myLookUp->addParameter("FILE", $users_table);
			$myLookUp->addParameter("CAMPO","USER_NAME");
			$myLookUp->addParameter("DESCRIZIONE","EMAIL");
			$myLookUp->addParameter("LU_SELECT", "FIRST_NAME|LAST_NAME");
			$myLookUp->addParameter("LU_AS_TITLES", "Nome|Cognome");
			$myField->setLookUp($myLookUp);
			$myField->setAutoFocus(True);
			$detail->addField($myField);
			
			$detail->addButton($button);
			
			$detail->dispose();
			
			echo "<br/>";
			
			//DETAIL per il aggiungere il gruppo
			$detail_gruppo = new wi400Detail($azione."_ADD_WIDGET_GRUPPO", true);

			//Aggiungi per gruppo
			$myField = new wi400InputText('gruppo');
			$myField->setLabel("Gruppo");
			$myField->addValidation('required');
			$myField->setCase("UPPER");
			
			$custom = new wi400CustomTool($azione, "TOOL_GRUPPI");
			$custom->setIco("themes/vega/images/lookup.png");
			//$custom->addJsParameter("codart");
			$custom->setToolTip("Lista gruppi");
			//$custom->setValidation(true);
			$custom->setReturnParameter(true);
			$myField->addCustomTool($custom);
			
			$detail_gruppo->addField($myField);
				
			$button = new wi400InputButton("CONFERMA_GRUPPO");
			$button->setAction($azione);
			$button->setForm("AGGIUNGI_WIDGET&GRUPPO=SI");
			$button->setLabel("Aggiungi");
			$detail_gruppo->addButton($button);
			
			$detail_gruppo->dispose();
		}else {
			$button->dispose();
		}
	}else if($form == "MODIFICA") {
		$in_menu = new wi400InputSwitch("IN_MENU");
		$in_menu->setLabel("In menù");
		$in_menu->setOffLabel("No");
		$in_menu->setOnLabel("Si");
		$in_menu->setValue("1");
		$in_menu->setChecked($key[2] == "1" ? true : false);
		$detail->addField($in_menu);
		
		$myField = new wi400InputText("NUM_COLONNE");
		$myField->setLabel("Larghezza in colonne");
		$myField->setMask("1234");
		$myField->setSize(1);
		$myField->setMaxLength(1);
		$myField->setValue($key['WIDCOL']);
		$myField->setInfo("Inserire un valore tra 1 e 4");
		$myField->addValidation("required");
		$detail->addField($myField);
		
		$button = new wi400InputButton("CONFERMA");
		$button->setAction($azione);
		$button->setForm("SAVE_PARAM");
		$button->setLabel("Salva");
		$button->setValidation(true);
		$detail->addButton($button);
		
		$detail->dispose();
	}else if($form == "TOOL_GRUPPI") {
		$miaLista = new wi400List($azione."_GRUPPI", true);
		$miaLista->setSubfile($subfile);
		$miaLista->setPassValue($_REQUEST['CAMPO']);
		
		$miaLista->setCols(array(
			new wi400Column("GRUPPO", "Gruppo")
		));
		
		// Aggiunta chiavi di lista
		$miaLista->addKey("GRUPPO");
		
		$mioFiltro = new wi400Filter("GRUPPO", "Gruppo", "STRING");
		$mioFiltro->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$mioFiltro->setFast(true);
		$miaLista->addFilter($mioFiltro);
		
		$miaLista->dispose();
	}else if ($actionContext->getForm() == "PASS_VALUE") {
		$key = getListKeyArray($azione."_GRUPPI");
		
		//showArray($key);
?>
		<script type="text/javascript">
			passValue('<?=$key['GRUPPO']?>', '<?=$_REQUEST['CAMPO']?>');

			closeLookUp();
		</script>
<?
	}