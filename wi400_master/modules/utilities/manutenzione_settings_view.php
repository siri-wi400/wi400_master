<?php
	
	if($form == "DEFAULT") {
		echo '<link rel="stylesheet" type="text/css" href="themes/common/css/button.css"  media="screen">';
		echo "	<style>
				.color {
					float: left;
					border: solid 1px black;
					color: #FFF;
					text-shadow: none;
					width: 82px;
					white-space: normal;
					background-color: #143962;
					background: -webkit-linear-gradient(#7CA6D6, #143962);
					background: linear-gradient(#7CA6D6, #143962);
					background: -o-linear-gradient(#7CA6D6, #143962);
					background: -moz-linear-gradient(#7CA6D6, #143962);
				}
			</style>";
		
		$miaLista = new wi400List($azione."_PARAMETRI", true);

		$miaLista->setField("A.PARAMETRO, A.PARAM_DES, A.FORMATO, B.VALORE, case when B.VALORE is null then 'N' else 'S' end as EXIT");
		$miaLista->setFrom("$tabSettings A LEFT JOIN (SELECT * FROM $tabValori WHERE AMBIENTE='$ambiente') B ON A.PARAMETRO=B.PARAMETRO and 
																												B.PGR=(SELECT MIN(PGR) 
																														FROM $tabValori C 
																														WHERE C.PARAMETRO=A.PARAMETRO)");
		$miaLista->setWhere("A.STATO=1");
		$miaLista->setOrder("EXIT, A.PARAMETRO");
		//$miaLista->setWhere("TABELLA='SYSPARAM'");
		
		$miaLista->setIncludeFile('utilities', "manutenzione_settings_commons.php");
		
//		echo $miaLista->getSql();

		$det_col = new wi400Column("DETTAGLIO", "Dettaglio", "", "CENTER");
		$det_col->setDecorator("ICONS");
		$det_col->setDefaultValue("SEARCH");
		$det_col->setActionListId($azione."_DETTAGLIO");
		
		$val_col = new wi400Column("VAL", "Valore");
		$val_col->setDefaultValue('EVAL:getValoriInLista($row)');
		
		$exit_col = new wi400Column("EXIT", "Valorizzato", "", "CENTER");
		$exit_col->setDecorator("YES_NO_ICO");
		
		$miaLista->setCols(array(
			$det_col,
			new wi400Column("PARAMETRO", "Parametro"),
			new wi400Column("PARAM_DES", "Descrizione"),
			//new wi400Column("VALORE", "Valore"),
			$val_col,
			$exit_col
			//new wi400Column("FORMATO", "Formato"),
		));
		
		$miaLista->addKey("PARAMETRO");
		$miaLista->addKey("FORMATO");
		
		$mioFiltro = new wi400Filter("PARAMETRO","Parametro","STRING");
		$mioFiltro->setKey("a.PARAMETRO");
		$mioFiltro->setCaseSensitive("CASE_SENSITIVE_DB");
		$mioFiltro->setFast(true);
		$miaLista->addFilter($mioFiltro);
		
		// Dettaglio parametro
		$action = new wi400ListAction();
		$action->setId($azione."_DETTAGLIO");
		$action->setAction($azione);
		$action->setForm("DETAIL");
		$action->setLabel("Dettaglio");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		listDispose($miaLista);
		
		echo "<br/><br/>";
		
		$button = new wi400InputButton("IMPORT_BUTTON");
		$button->setLabel("Import valori");
		$button->setAction("GESTIONE_SETTINGS");
		$button->setForm("IMPORT_VALORI");
		$button->setButtonClass("ccq-button-active color");
		$button->setConfirmMessage("Sei sicuro di voler continuare? Verranno eliminati tutti valori salvati.");
		$button->dispose();
		
	}else if($form == "DETAIL") {
		$detail = new wi400Detail($azione."_RIEPILOGO");
		
		$myField = new wi400Text("PARAMETRO", "Parametro", $parametro);
		$detail->addField($myField);
		
		$detail->dispose();
		
		echo "<br/>";
		
		$miaLista = new wi400List($azione."_PARAMETRI_DETAIL", true);
		$miaLista->setSelection("MULTIPLE");
		
		$miaLista->setFrom($tabValori);
		$miaLista->setWhere("AMBIENTE='$ambiente' and PARAMETRO='$parametro'");
		$miaLista->setOrder("PGR");
		
		$input = getFieldFromParam($parametro);
		$col_valore = new wi400Column("VALORE", "Valore");
		$col_valore->setInput($input);
		
		$myField = new wi400InputText("PROGRESSIVO");
		$myField->setAlign("right");
		$myField->setSize(2);
		$myField->setMaxLength(2);
		$myField->setMask("0123456789");
		$col_prog = new wi400Column("PGR", "Progressivo");
		$col_prog->setInput($myField);
		
		$myField = new wi400InputText("INPUT_CHIAVE");
		$col_chiave = new wi400Column("CHIAVE", "Chiave");
		$col_chiave->setInput($myField);
		
		$miaLista->setCols(array(
			$col_prog,
			$col_chiave,
			$col_valore
		));
		
		if($formato != "array") {
			$miaLista->removeCol("PGR");
			$miaLista->removeCol("CHIAVE");
		}
		
		$miaLista->addKey("VALORE");
		$miaLista->addKey("PGR");
		$miaLista->addKey("CHIAVE");
		
		// Salva
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("SALVA_VALORE");
		$action->setLabel("Salva");
		$action->setSelection("MULTIPLE");
		$miaLista->addAction($action);
		
		// Nuovo
		if($formato == "array" || !$parm_valori) {
			$action = new wi400ListAction();
			$action->setAction($azione);
			$action->setForm("NUOVA_CONFIGURAZIONE");
			$action->setLabel("Nuovo");
			$action->setTarget("WINDOW");
			$action->setSelection("NONE");
			$miaLista->addAction($action);
		}
		
		// Elimina
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("ELIMINA_CONFIGURAZIONE");
		$action->setLabel("Elimina");
		$action->setSelection("MULTIPLE");
		$miaLista->addAction($action);
		
		listDispose($miaLista);
	}else if($form == "NUOVA_CONFIGURAZIONE") {
		$detail = new wi400Detail($azione."_NUOVA_CONFIGURAZIONE", !$isFromHistory);
		
		if($p['FORMATO'] == "array") {
			$myField = new wi400InputText("PROGRESSIVO");
			$myField->setLabel("Progressivo");
			$myField->setSize(2);
			$myField->setMaxLength(2);
			$detail->addField($myField);
			
			$myField = new wi400InputText("CHIAVE");
			$myField->setLabel("Chiave");
			$detail->addField($myField);
		}else {
			$myField = new wi400InputHidden("PROGRESSIVO");
			$myField->setValue(0);
			$detail->addField($myField);
				
			$myField = new wi400InputHidden("CHIAVE");
			$myField->setValue('');
			$detail->addField($myField);
		}
		
		$myField = getFieldFromParam($parametro);
		$detail->addField($myField);
		
		$button = new wi400InputButton("SALVA_BUTTON");
		$button->setLabel("Salva");
		$button->setAction($azione);
		$button->setValidation(true);
		$button->setForm("INSERT_CONFIGURAZIONE");
		$detail->addButton($button);
		
		$detail->dispose();
	}