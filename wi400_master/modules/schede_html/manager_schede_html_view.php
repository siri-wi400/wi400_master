<?php
	// ********************************
	// Lista temi delle schede
	// ********************************	
	
	echo '<link rel="stylesheet" type="text/css" href="themes/common/css/button.css"  media="screen">';
	
 	if($actionContext->getForm() == "DEFAULT") {
		$detail = new wi400Detail($azione."_ARGOMENTO", false);
/*		
		$myLookUp = new wi400LookUp("LU_GENERICO");
		$query = base64_encode("SELECT FLD_ARGO, FLD_DESC FROM ZFLDARGD WHERE FLD_TYPE='****' GROUP BY FLD_ARGO, FLD_DESC order by FLD_ARGO");
		$myLookUp->addParameter("DIRECT_SQL", $query);
		$myLookUp->addParameter("CAMPO", "FLD_ARGO");
		$myLookUp->addParameter("DESCRIZIONE", "FLD_DESC");
*/		
		$myLookUp = new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE", "ZFLDARGD");
		$myLookUp->addParameter("CAMPO","FLD_ARGO");
		$myLookUp->addParameter("DESCRIZIONE","FLD_DESC");
		$myLookUp->addParameter("LU_FIELDS", "FLD_ARGO, FLD_DESC");
		$myLookUp->addParameter("LU_WHERE", "FLD_TYPE='****'");
		$myLookUp->addParameter("LU_GROUP", "FLD_ARGO, FLD_DESC");
		$myLookUp->addParameter("LU_ORDER", "FLD_ARGO");
		
		$decodeParameters = array(
				'TYPE'=> 'common',
				'COLUMN' => 'FLD_DESC',
				'TABLE_NAME' => 'ZFLDARGD',
				'KEY_FIELD_NAME' => 'FLD_ARGO',
				'WHERE_COND' => "FLD_TYPE<>'****'",
				'GROUP_BY' => 'FLD_ARGO, FLD_DESC',
				'AJAX' => true,
				'COMPLETE' => true,
				'COMPLETE_MIN' => 2,
				'COMPLETE_MAX_RESULT' => 15
		);
		
		$myField = new wi400InputText('OBJ_ARGO');
		$myField->setLabel('Argomento');
		$myField->setLookUp($myLookUp);
		$myField->setDecode($decodeParameters);
		$myField->addValidation("required");
		$myField->setCase("UPPER");
		$detail->addField($myField);
		
		$button = new wi400InputButton("SELEZIONA");
		$button->setLabel("Seleziona");
		$button->setAction($azione);
		$button->setForm("DETAIL");
		$button->setValidation(true);
		$detail->addButton($button);
		
		$detail->dispose();
	}else if($actionContext->getForm() == "DETAIL") {
		$detail = new wi400Detail("RIEPILOGO");
		
		$myField = new wi400Text("ARGOMENTO_DESC", "Argomento", $argomento);
		$detail->addField($myField);
		
		$detail->dispose();
		
		echo "<br/>";
		
		$toThemesList = new wi400List("TO_THEMES_LIST",true);
		
		$toThemesList->setSelection("SINGLE");
		$toThemesList->setShowMenu(true);
		
		$toThemesList->setFrom("ZFLDHTML");
		$toThemesList->setWhere("OBJ_ARGO='$argomento'");
		$toThemesList->setOrder("THM_ORDER");
		
		$folderDescription = new wi400Column("FLD_DESC","Descrizione","","","",true);
		$folderDescription->setDetailAction($azione."&DECORATION=clean", "ANTEPRIMA");
		$folderDescription->setDetailSize(-400, -100);
		
		$modifica_col = new wi400Column("MODIFICA", "Modifica", "", "CENTER");
		$modifica_col->setDecorator("NOTE_ICONS");
		$modifica_col->setDefaultValue("1");
		$modifica_col->setActionListId("MOD_REC");
		$modifica_col->setSortable(false);
		$modifica_col->setExportable(false);
		
		$toThemesList->setCols(array(
			$modifica_col,
			new wi400Column("OBJ_TYPE","Contesto","","","",true),
			new wi400Column("FLD_TYPE","Tipo Scheda","","","",true),
			$folderDescription,
			new wi400Column("THM_ORDER","Priorità","","","",true),
		));
		
		$toFilter = new wi400Filter("FLD_DESC","Descrizione");
		$toFilter->setFast(true);
		$toFilter->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_BOTH);
		$toThemesList->addFilter($toFilter);
		
		$tool = new wi400ListAction();
		$tool->setScript("openWindow(_APP_BASE + APP_SCRIPT + '?DECORATION=lookUp&t=SORT_LIST&IDLIST=TO_THEMES_LIST', 'Riordina', 600, 400)");
		$tool->setIco($temaDir."images/grid/reorder.gif");
		$tool->setLabel("Riordina");
		$toThemesList->addTool($tool);
	
		// Azione di aggiunta tema
		$toAction = new wi400ListAction();
		$toAction->setLabel("Nuovo tipo scheda");
		$toAction->setAction($azione);
		$toAction->setForm("NEW");
		$toAction->setSelection("NONE");
		$toThemesList->addAction($toAction);
	
		// Azione di modifica tema
		$toAction = new wi400ListAction();
		$toAction->setId("MOD_REC");
		$toAction->setLabel("Modifica tipo scheda");
		$toAction->setAction($azione);
		$toAction->setForm("UPDATE");
		$toAction->setSelection("SINGLE");
		$toThemesList->addAction($toAction);
		
		// Azione di aggiunta tema
		$toAction = new wi400ListAction();
		$toAction->setLabel("Cancella tipo scheda");
		$toAction->setConfirmMessage("Cancellare il tipo scheda selezionato?");
		$toAction->setAction($azione);
		$toAction->setForm("DELETE");
		$toAction->setSelection("SINGLE");
		$toThemesList->addAction($toAction);
		
		$toThemesList->addKey("FLD_TYPE");
		
		$toThemesList->dispose();
		
		echo "<br/>";
		
		$button = new wi400InputButton("NUOVA_SCHEDA");
		$button->setLabel("Nuovo tipo scheda");
		$button->setAction($azione);
		$button->setForm("UPDATE");
		$button->setButtonClass("ccq-button-active");
		$button->setButtonStyle(getCssButton("auto", "#6899bb", "#2c658b", "white", "black"));
		//$button->setButtonStyle(getCssButton("#F7F7F7", "#C8C8C8", "#5b5a5a", "#A8A8A8"));
		$button->dispose();
	}else if(in_array($actionContext->getForm(), array("NEW", "UPDATE"))) {
		$folderDetail = new wi400Detail("THEME_DETAIL");
		$folderDetail->setSource($toThemeObj);
		
		$myField = new wi400Text("ARGOMENTO_DESC", "Argomento", $argomento);
		$folderDetail->addField($myField);
		
		if ($fldType != "") {
			// UPDATE
			$folderDetail->addParameter("FLD_TYPE", $fldType);
		}
		
		$folderType = new wi400InputSelect("OBJ_TYPE");
		$folderType->setLabel("Contesto scheda");
		$folderType->setOptions($objectTypes);
		$folderDetail->addField($folderType);
		
		$folderDesc = new wi400InputText("FLD_DESC");
		$folderDesc->setLabel("Descrizione");
		$folderDesc->addValidation("required");
		$folderDesc->setSize(30);
		$folderDetail->addField($folderDesc);
		
		$folderDyn = new wi400InputCheckbox("FLD_DYN");
		$folderDyn->setLabel("Scheda dinamica");
		if(isset($toThemeObj["FLD_DYN"]) && $toThemeObj["FLD_DYN"]==="1"){
			$folderDyn->setChecked(true);
		}
		$folderDetail->addField($folderDyn);
		
		$myEditor = new wi400InputEditor('FLD_TEMPLATE');
		$myEditor->setHeight(400);
		$folderDetail->addField($myEditor);
		
		// Azioni di salvataggio
		
		$myButton = new wi400InputButton("ADD_BUTTON");
		$myButton->setAction($azione);
		$myButton->setForm("SAVE");
		$myButton->setValidation(true);
		$myButton->setLabel("Salva");
		$folderDetail->addButton($myButton);
		
		$myButton = new wi400InputButton("APPLICA_BUTTON");
		$myButton->setLabel("Applica");
		$myButton->setScript("applicaHTML('FLD_TEMPLATE')");
		$folderDetail->addButton($myButton);
			
		$myButton = new wi400InputButton("CANCEL_BUTTON");
		$myButton->setAction($azione);
		$myButton->setLabel("Annulla");
		$folderDetail->addButton($myButton);
		
		$folderDetail->dispose();
		
?>
		<script>
			function applicaHTML() {
				jQuery.ajax({  
					type: "POST",
					url: _APP_BASE + APP_SCRIPT + "?t=<?= $azione?>&f=AJAX_APPLICA_HTML&DECORATION=clean",
					data: {
						"HTML": btoa(CKEDITOR.instances.FLD_TEMPLATE.getData())
					},
				}).done(function ( response ) {
					var dati = JSON.parse(response);  
					
					var objMess = jQuery("#messageArea");
					objMess.html('<br/><div class="messageLabel_'+dati.rs+'">'+dati.testo+'</div><br/>')
						.attr("class", "messageArea_"+dati.rs)
						.slideDown('slow')
						.css("display", "block");

					MESSAGE_AREA_OPEN = false;

					setTimeout(function() {
						objMess.click();
					}, 2000);
				}).fail(function ( data ) {  
					console.error("Errore ajax applicaHTML: è andato in fail.");
				});
			}
		</script>
<?php 
	}
?>