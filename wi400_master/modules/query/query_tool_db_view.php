<?php 

	$spacer = new wi400Spacer();
	$database = $settings['database'];
	if(in_array($actionContext->getForm(),array("DEFAULT","EXECUTE"))) {
		if($blocked===true && $query_libera===true) {
			$readonly = true;
		}
//		echo "VIEW - READONLY: $readonly<br>";

//		echo "VIEW - ID QUERY: $id_query<br>";
		
		$searchAction = new wi400Detail($idDetail, true);
		$searchAction->setTitle(_t('QRY_PARM'));
		$searchAction->isEditable(true);
		
		// Query
		$myField = new wi400InputText('ID_QUERY');
		$myField->setLabel("Query");
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$myField->setCase("UPPER");
//		$myField->addValidation('required');
		$myField->setValue($id_query);
//		$myField->setDispose(true);
		$myField->setType("HIDDEN");
/*		
		if($id_query=="") {
			$myField->setType("HIDDEN");
		}
		else {
			$myField->setReadonly(true);
		}
*/
		$myField->setOnChange("doSubmit('".$azione."', 'DEFAULT')");
		
		$searchAction->addField($myField);
		
		// Descrizione query
		$myField = new wi400InputText('DES_QUERY');
		$myField->setLabel("Descrizione Query");
		$myField->setSize(100);
		$myField->setMaxLength(100);
//		$myField->setCase("UPPER");
//		$myField->addValidation('required');
		$myField->setValue($des_query);
//		$myField->setReadonly($readonly);
		$myField->setReadonly(true);
		if($id_query=="")
			$myField->setType("HIDDEN");
		$searchAction->addField($myField);
		
		// Area query
		$myField = new wi400InputText('AREA');
		$myField->setLabel("Area");
		$myField->setSize(20);
		$myField->setMaxLength(20);
//		$myField->setCase("UPPER");
//		$myField->addValidation('required');
		$myField->setValue($area_query);
//		$myField->setReadonly($readonly);
		$myField->setReadonly(true);
		if($id_query=="")
			$myField->setType("HIDDEN");
		$searchAction->addField($myField);
		
		// Funzione query
		$myField = new wi400InputText('FUNZIONE');
		$myField->setLabel("Funzione");
		$myField->setSize(20);
		$myField->setMaxLength(20);
//		$myField->setCase("UPPER");
//		$myField->addValidation('required');
		$myField->setValue($funz_query);
//		$myField->setReadonly($readonly);
		$myField->setReadonly(true);
		if($id_query=="")
			$myField->setType("HIDDEN");
		$searchAction->addField($myField);
		
		// Note query
//		$myField = new wi400InputText('NOTE');
		$myField = new wi400InputTextArea('NOTE');
		$myField->setLabel("Note");
		$myField->setSize(100);
//		$myField->setMaxLength(200);
		$myField->setRows(2);
//		$myField->setCase("UPPER");
//		$myField->addValidation('required');
		$myField->setValue($note_query);
//		$myField->setReadonly($readonly);
		$myField->setReadonly(true);
		if($id_query=="")
			$myField->setType("HIDDEN");
		$searchAction->addField($myField);
		
		if($query_libera===false) {
			$myField = new wi400InputTextArea('SELECT');
			$myField->setLabel("SELECT");
			$myField->setSize(130);
			$myField->setRows(2);
			$myField->setValue($select_2);
			$myField->setReadonly($readonly);
//			if($hide_query===true || $query_libera===true)
			if($hide_query===true)
				$myField->setType("HIDDEN");
//			echo "SELECT: $select<br>";
		
			$myLookUp = new wi400LookUp("LU_FILE_TABLE");
			$myLookUp->addJsParameter("FROM");
//			$myLookUp->addParameter("LU_FROM","<@REQUEST(FROM)@>");
			$myLookUp->addParameter("SOURCE","SELECT");
			$myField->setLookUp($myLookUp);
		
			$searchAction->addField($myField);
			
			$myField = new wi400InputTextArea('FROM');
			$myField->setLabel("FROM");
			$myField->setSize(130);
			$myField->setRows(2);
			$myField->setValue($from_2);
			$myField->setReadonly($readonly);
//			if($hide_query===true || $query_libera===true)
			if($hide_query===true)
				$myField->setType("HIDDEN");
			
			$myLookUp = new wi400LookUp("LU_FILE");
			$myField->setLookUp($myLookUp);
			
			$searchAction->addField($myField);
			
			$myField = new wi400InputTextArea('WHERE');
			$myField->setLabel("WHERE");
			$myField->setSize(130);
			$myField->setRows(2);
			$myField->setValue($where_2);
			$myField->setReadonly($readonly);
//			if($hide_query===true || $query_libera===true)
			if($hide_query===true)
				$myField->setType("HIDDEN");

			$myLookUp = new wi400LookUp("LU_FILE_TABLE");
			$myLookUp->addJsParameter("FROM");
//			$myLookUp->addParameter("LU_FROM","<@REQUEST(FROM)@>");
			$myLookUp->addParameter("SOURCE","WHERE");
			$myField->setLookUp($myLookUp);
		
			$searchAction->addField($myField);
			
//			echo "WHERE: $where<br>";
			
			$myField = new wi400InputTextArea('GROUP_BY');
			$myField->setLabel("GROUP BY");
			$myField->setSize(130);
			$myField->setRows(2);
			$myField->setValue($group_by_2);
			$myField->setReadonly($readonly);
//			if($hide_query===true || $query_libera===true)
			if($hide_query===true)
				$myField->setType("HIDDEN");
	
			$myLookUp = new wi400LookUp("LU_FILE_TABLE");
			$myLookUp->addJsParameter("FROM");
//			$myLookUp->addParameter("LU_FROM","<@REQUEST(FROM)@>");
			$myLookUp->addParameter("SOURCE","GROUP_BY");
			$myField->setLookUp($myLookUp);
			
			$searchAction->addField($myField);
		
			$myField = new wi400InputTextArea('ORDER_BY');
			$myField->setLabel("ORDER BY");
			$myField->setSize(130);
			$myField->setRows(2);
			$myField->setValue($order_by_2);
			$myField->setReadonly($readonly);
//			if($hide_query===true || $query_libera===true)
			if($hide_query===true)
				$myField->setType("HIDDEN");
			
			$myLookUp = new wi400LookUp("LU_FILE_TABLE");
			$myLookUp->addJsParameter("FROM");
//			$myLookUp->addParameter("LU_FROM","<@REQUEST(FROM)@>");
			$myLookUp->addParameter("SOURCE","ORDER_BY");
			$myField->setLookUp($myLookUp);
		
			$searchAction->addField($myField);
		}
		
		if($query_libera===true) {
			$myField = new wi400InputTextArea('SQL_QUERY');
			$myField->setLabel("SQL QUERY");
			$myField->setSize(130);
			$myField->setRows(10);
			$myField->setValue($sql_query_2);
//			$myField->addValidation('required');
			$myField->setReadonly($readonly);
//			if($hide_query===true || $query_libera===false)
			if($hide_query===true)
				$myField->setType("HIDDEN");
			$searchAction->addField($myField);
		}
		
		$myField = new wi400InputSwitch("DES_TITOLI");
		$myField->setLabel("Descrizioni Titoli");
		$myField->setOnLabel(_t('LABEL_YES'));
		$myField->setOffLabel(_t('LABEL_NO'));
		$myField->setChecked($check_des_titoli);
		$myField->setValue(1);
		if($hide_query===true || $readonly===true)
			$myField->setType("HIDDEN");
		else if($detonly===true)
			$myField->setDisabled(true);
		$searchAction->addField($myField);
		
		$myField = new wi400InputSwitch("FONT_CATALOGO");
		$myField->setLabel("Font Catalogo");
		$myField->setOnLabel(_t('LABEL_YES'));
		$myField->setOffLabel(_t('LABEL_NO'));
		$myField->setChecked($check_font_catalogo);
		$myField->setValue(1);
		if($hide_query===true || $readonly===true)
			$myField->setType("HIDDEN");
		else if($detonly===true)
			$myField->setDisabled(true);
		$searchAction->addField($myField);
		
		// Totali colonne
		$myField = new wi400InputText('TOTALI_STR');
		$myField->setLabel('Totali');
		$myField->setSize(50);
//		$myField->setMaxLength(50);
		$myField->setCase("UPPER");
		$myField->setInfo("Inserire i valori in formato <CAMPO1>;<CAMPO2>");
		$myField->setValue($totali_str);
		$myField->setReadonly($readonly);
		if($hide_query===true)
			$myField->setType("HIDDEN");
		$searchAction->addField($myField);
		
		if($detonly===false || $exe_button===true) {
			$myButton = new wi400InputButton('EXECUTE_BUTTON');
			$myButton->setLabel(_t('EXECUTE'));
			$myButton->setAction($azione);
			$myButton->setForm("EXECUTE");
			$myButton->setGateway($actionContext->getGateway());
			$myButton->setValidation(true);
//			$myButton->setButtonStyle("background-color:#b4dcb4;color:#FF0000;);");
			$searchAction->addButton($myButton);
		}
		
		if($detonly===false) {
			$myButton = new wi400InputButton('SELECT_BUTTON');
			$myButton->setLabel("Seleziona Query");
			$myButton->setAction($azione);
			$myButton->setForm("QUERY_SEL");
			$myButton->setTarget("WINDOW");
//			$myButton->setButtonStyle("background-color:#7FFFD4;);");
			$searchAction->addButton($myButton);
			
//			if($query_admin_level=="QUERY_ADMIN") {
				$myButton = new wi400InputButton('SELECT_BUTTON_TREE');
				$myButton->setLabel("Albero Query");
				$myButton->setAction($azione);
				$myButton->setForm("TREE_QUERY_SEL");
				$myButton->setTarget("WINDOW");
//				$myButton->setButtonStyle("background-color:#7FFFD4;);");
				$searchAction->addButton($myButton);
//			}
		}

		if($detonly===false) {
			if($readonly===false) {
				$myButton = new wi400InputButton('MARKERS');
				$myButton->setLabel("Aggiungi Markers");
				$myButton->setAction($azione);
				$myButton->setForm("MARKERS_DEL");
//				$myButton->setTarget("WINDOW");
				$myButton->setTarget("WINDOW", -500, 500);
//				$myButton->setButtonStyle("background-color:#DABAD0;);");
				$searchAction->addButton($myButton);
			}
			
			if($query_libera===false && $blocked===false) {
				$myButton = new wi400InputButton('CHANGE_SQL');
				$myButton->setLabel("Query libera");
				$myButton->setAction($azione);
				$myButton->setForm("DEFAULT_LIBERO");
//				$myButton->setButtonStyle("background-color:#DABAD0;);");
				$myButton->setConfirmMessage("Passare alla query libera?");
				$searchAction->addButton($myButton);
			}
			else if($query_libera===true && $loadonly===false) {
				$myButton = new wi400InputButton('CHANGE_SQL');
				$myButton->setAction($azione);
				$myButton->setLabel("Query indirizzata");
				$myButton->setForm("RETURN_DEFAULT");
//				$myButton->setButtonStyle("background-color:#DABAD0;);");
				$myButton->setConfirmMessage("Passare alla query indirizzata?");
				$searchAction->addButton($myButton);
			}
			
			if($readonly===false) {
/*
				$myButton = new wi400InputButton('SAVE');
				$myButton->setLabel("Salva Query");
				$myButton->setAction($azione);
				$myButton->setForm("SAVE");
				if($id_query!="")
					$myButton->setConfirmMessage("Salvare la query MODIFICATA? La query verrà SOVRASCRITTA!");
				else
					$myButton->setConfirmMessage("Salvare la query?");
				$searchAction->addButton($myButton);
*/
				$myButton = new wi400InputButton('SAVE');
				$myButton->setLabel("Salva Query");
				$myButton->setAction($azione);
				$myButton->setForm("SAVE_SEL");
				$myButton->setTarget("WINDOW");
//				$myButton->setButtonStyle("background-color:#FFFF66;);");
				$searchAction->addButton($myButton);
/*
				if($id_query!="") {
					$myButton = new wi400InputButton('ELIMINA');
					$myButton->setLabel("Elimina Query");
					$myButton->setAction($azione);
					$myButton->setForm("DELETE");
					$myButton->setConfirmMessage("Eliminare la query?");
					$searchAction->addButton($myButton);
				}
*/
			}
			
			if($hide_query===false && $readonly===false) {
				$myButton = new wi400InputButton('CLEAN_SQL');
				$myButton->setLabel("Pulisci Query");
				$myButton->setAction($azione);
				$myButton->setForm("CLEAN");
//				$myButton->setButtonStyle("background-color:#7FFFD4;);");
//				$myButton->setButtonStyle("background-color:#ffa500;);");
				$myButton->setConfirmMessage("Pulire la query?");
				$searchAction->addButton($myButton);
			}
		}
		
//		$searchAction->setShowTopButtons(true);
		
		$searchAction->dispose();
	}
	
	if(in_array($actionContext->getForm(), array("DEFAULT","EXECUTE"))) {
		if(!empty($markers)) {
			$spacer->dispose();
				
//			$searchAction = new wi400Detail($idDetailMarkers, true);
			$searchAction = new wi400Detail($idDetailMarkers, false);
			$searchAction->setTitle("Markers");
			$searchAction->isEditable(true);
//			$searchAction->setSaveDetail(true);

			if(isset($settings['show_field_required']) && $settings['show_field_required']===true)
				$searchAction->setShowFieldRequired(true);
	
			$marker_fields = array();
			foreach($markers as $mark) {
//				echo "MARKER: $mark<br>";

				$field_id = get_marker_field_id($mark);
//				echo "FIELD ID: $field_id<br>";

				if(in_array($field_id, $marker_fields)) {
					continue;
				}
				
				$marker_fields[] = $field_id;
	
				$val = "";
				if(isset($marker_values[$mark]))
					$val = $marker_values[$mark];
				
				if(wi400Detail::getDetailField($azione."_MARKERS_DEF", $field_id)) {
//					echo "MARKERS_DEF: $field_id<br>";
					$myField = wi400Detail::getDetailField($azione."_MARKERS_DEF", $field_id);
//					$myField->setValue($val);
				}
				else {
					$myField = marker_field($mark, $val);
				}
	
				$searchAction->addField($myField);
			}
			
//			wi400Detail::cleanSession($azione."_MARKERS_DEF");
			
			if(count($markers)==$hidden_markers)
				$searchAction->setHidden(true);
	
			$searchAction->dispose();
		}
	}
	
	if($actionContext->getForm()=="EXECUTE" && $messageContext->getSeverity()!="ERROR"
//		&& isset($_REQUEST["FROM"])
	) {
		$spacer->dispose();
		if ($riga!==False) {
			$idList = $azione."_LIST";
			if($actionContext->getGateway()=="QUERY_TOOL_DB_MULTI")
				$idList .= "_".$id_query;
//			echo "ID LIST: $idList<br>";
			
			$miaLista = new wi400List($idList, true);
			$miaLista->setExportDetails(array($idDetail));
			
			// Recupero un identificativo univoco in base alla query
			$idu = hash ("crc32", $sql_query);
			
			$miaLista->setConfigFileName($idu."_QUERY_LIST");
			
			if($query_libera===false) {
/*				
				$miaLista = new wi400List($azione."_LIST", true);
				$miaLista->setExportDetails(array($idDetail));
				
				$miaLista->setConfigFileName($from.$azione."_LIBERO_LIST");	
*//*
				if(!empty($marker_values)) {
					foreach($array_campi as $key => $val) {
//						echo "KEY: $key<br>";
					
						if(!in_array($key, array("SELECT_FLD", "FROM_FLD", "WHERE_FLD", "GROUP_FLD", "ORDER_FLD")))
							continue;
					
						$campo = $key;
						if(array_key_exists($key, $field_names_array))
							$campo = $field_names_array[$key];
//						echo "CAMPO: $key - CHIAVE: $campo<br>";
					
						$field = strtolower($campo);
						
//						echo "$campo: ".$$field."<br>";
						
						$$field = replace_markers($$field, $marker_values);
//						echo "$campo REPLACED: ".$$field."<br>";
					}
				}
*/				
				$miaLista->setField($select);
				$miaLista->setFrom($from);
				if(isset($where) && trim($where)!="")
					$miaLista->setWhere($where);
				if(isset($group_by) && trim($group_by)!="")
					$miaLista->setGroup($group_by);
				if(isset($order_by) && trim($order_by)!="")
					$miaLista->setOrder($order_by);
				
//				echo "SQL:".$miaLista->getSql()."<br>";
				
				// AutoFilter settato a false per permette di filtrare campi non esistenti in tabella (ridenominati AS), creando una query con WITH
				$miaLista->setAutoFilter(false);
				// execute_like_query settato a true per permettere in caso di $autofilter = false; di eseguire la query senza trasformarla in una query con with
				$miaLista->set_execute_like_query(true);
			}
			else if($query_libera===true) {
/*				
				$miaLista = new wi400List($azione."_LIBERO_LIST", true);
				$miaLista->setExportDetails(array($idDetail));
				
				// Recupero un identificativo univoco in base alla query
				$idu = hash ("crc32", $value); 
				$miaLista->setConfigFileName($idu."_LIBERO_LIST");
*/				
				$miaLista->setQuery($value);
				
//				echo "SQL_LIBERO:".$miaLista->getQuery()."<br>";
				
				// AutoFilter settato a false per permette di filtrare campi non esistenti in tabella (ridenominati AS), creando una query con WITH
				$miaLista->setAutoFilter(false);
				if(strtoupper(substr(trim($value), 0, 4))=="WITH") {
					$miaLista->setAutoFilter(true);					
					$miaLista->setPagBetween(false);
				}
			}
			
			$miaLista->setSelection("SINGLE");
			
			if($check_des_titoli===true) {
				require_once $routine_path."/generali/db_support.php";
				$dati = $db->getCurrentOpenFile();
				$arrayCampi = getAllFieldUsed($dati);
//				echo "ARRAY CAMPI:<pre>"; print_r($arrayCampi); echo "</pre>";	
			}
			
			foreach($riga as $key => $val) {
				$field_data = $db->getField($res, $key);
//				echo "FORMAT $key:<pre>"; print_r($field_data); echo "</pre><br>";
				
				$tipo = "";
				$align = "";
				switch($field_data['TIPO']) {
					case "string": $tipo = ""; break;
					case "real": 
						if($field_data['SCALE']==0)
							$tipo = "INTEGER";
						else
							$tipo = "DOUBLE_".$field_data['SCALE']; 
						$align = "right";
						break;
					case "timestamp": 
						$tipo = "COMPLETE_TIMESTAMP"; 
						break;
				}
				
				$desc = $key;
				if($check_des_titoli===true) {
					$desc = getCustomFieldDesc($key, $arrayCampi, True, True);
				}
				
				$col = new wi400Column($key, $desc, $tipo, $align);
				
				if($check_font_catalogo===true) {
					$style = "wi400_font_Courier";
					$col->setStyle($style);
				}
				
				$miaLista->addCol($col);
			}
			
			$miaLista->setCanExport("RESUBMIT");
			
			// Aggiunta dinamica filtri su tutti i campi
			if(strtoupper(substr(trim($value), 0, 4))!="WITH") { 
				foreach($miaLista->getCols() as $key => $val) {
					$listFlt = new wi400Filter($key);
					$listFlt->setDescription($val->getDescription());
					if ($val->getAlign()=='right') 
						$listFlt->setType("NUMERIC");
					else 
						$listFlt->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
					$miaLista->addFilter($listFlt);
				}
			}
/*			
			$listFlt = new wi400Filter("USER_WHERE", "Filtro<br>personalizzato", "USERE_WHERE");
			$miaLista->addFilter($listFlt);			
*/
			$miaLista->setFilterUserWhere(true);
//			$miaLista->setForceUserWhere(true);

			if(isset($totali_str) && !empty($totali_str)) {
				$totali_array = explode(";", $totali_str);
//				echo "TOTALI:<pre>"; print_r($totali_array); echo "</pre>";
				
				foreach($totali_array as $tot) {
					$tot = trim($tot);
					
					if($tot!="")
						$miaLista->addTotal($tot);
				}
			}
			
//			if($hide_query===false)
			if($readonly===true)
				$miaLista->setExportFilterSel(false);
			else
				$miaLista->setExportFilterSelChecked(false);
						
			listDispose($miaLista);
		}
	}
	else if($actionContext->getForm()=="MARKERS") {
		$detailAction = detail_markers($azione."_MARKERS_DET", $query_libera);
		
		$myButton = new wi400InputButton("ADD_BUTTON");
		$myButton->setAction($azione);
		$myButton->setForm("ADD_MARKER");
		$myButton->setLabel("Aggiungi");
		$myButton->setValidation(true);
		$buttonsBar[] = $myButton;
		
		$myButton = new wi400InputButton("CLOSE_BUTTON");
//		$myButton->setScript('closeLookUp()');
		$myButton->setAction("CLOSE");
		$myButton->setForm("CLOSE_LOOKUP");
		$myButton->setLabel("Chiudi");
		$buttonsBar[] = $myButton;
	}
	else if($actionContext->getForm()=="ADD_MARKER") {
?>
	<script>
		passValue("<?=$marker?>", "<?=$in_campo?>", true);
		closeLookUp();
	</script>
<?
	}
	else if($actionContext->getForm()=="SAVE_SEL") {
		$searchAction = new wi400Detail($azione."_SAVE_DET", false);
		$searchAction->setTitle(_t("PARAMETRI"));
		$searchAction->isEditable(true);
		
		if($id_query!="") {
			// Query
			$myField = new wi400InputText('ID_QUERY');
			$myField->setLabel("ID Query");
			$myField->setSize(10);
			$myField->setMaxLength(10);
			$myField->setCase("UPPER");
			$myField->setValue($id_query);
			$myField->setReadonly(true);
			$searchAction->addField($myField);
		}
		
		// Descrizione query
		$myField = new wi400InputText('DES_QUERY');
		$myField->setLabel("Descrizione Query");
		$myField->setSize(100);
		$myField->setMaxLength(100);
//		$myField->setCase("UPPER");
		$myField->addValidation('required');
		$myField->setValue($des_query);
		$searchAction->addField($myField);
		
		// Area query
		$myField = new wi400InputText('AREA');
		$myField->setLabel("Area");
		$myField->setSize(20);
		$myField->setMaxLength(20);
//		$myField->setCase("UPPER");
//		$myField->addValidation('required');
		$myField->setValue($area_query);
		$searchAction->addField($myField);
		
		// Funzione query
		$myField = new wi400InputText('FUNZIONE');
		$myField->setLabel("Funzione");
		$myField->setSize(20);
		$myField->setMaxLength(20);
//		$myField->setCase("UPPER");
//		$myField->addValidation('required');
		$myField->setValue($funz_query);
		$searchAction->addField($myField);
		
		// Note query
//		$myField = new wi400InputText('NOTE');
		$myField = new wi400InputTextArea('NOTE');
		$myField->setLabel("Note");
		$myField->setSize(100);
//		$myField->setMaxLength(200);
		$myField->setRows(2);
//		$myField->setCase("UPPER");
//		$myField->addValidation('required');
		$myField->setValue($note_query);
		$searchAction->addField($myField);
		
		if($id_query!="") {
			$myField = new wi400InputSwitch("OVERWRITE");
			$myField->setLabel("Sovrascrivere");
			$myField->setOnLabel(_t('LABEL_YES'));
			$myField->setOffLabel(_t('LABEL_NO'));
			$myField->setChecked($check_overwrite);
			$myField->setValue(1);
			$searchAction->addField($myField);
		}
		
		$myButton = new wi400InputButton('SAVE');
		$myButton->setLabel("Salva Query");
		$myButton->setAction($azione);
		$myButton->setForm("SAVE");
		$myButton->setConfirmMessage("Salvare la query?");
		$searchAction->addButton($myButton);
		
		$searchAction->dispose();
	}
	else if($actionContext->getForm()=="QUERY_SEL") {
		$miaLista = new wi400List($azione."_".$actionContext->getForm()."_LIST", !$isFromHistory);
	
		$miaLista->setFrom($from);
		$miaLista->setWhere($where);
//		$miaLista->setOrder("ID_QUERY");
		$miaLista->setOrder("DES_QUERY");
	
//		echo "SQL: ".$miaLista->getSql()."<br>";
	
		$miaLista->setSelection("MULTIPLE");
		
		$col_id = new wi400Column("ID_QUERY", "ID<br>Query", "INTEGER", "right");
		$col_id->setShow(false);
	
		$miaLista->setCols(array(
			$col_id,
			new wi400Column("DES_QUERY", "Descrizione<br>Query"),
			new wi400Column("AREA", "Area"),
			new wi400Column("FUNZIONE", "Funzione"),
			new wi400Column("NOTE", "Note"),
		));
	
		$miaLista->addKey("ID_QUERY");
//		$miaLista->addKey("DES_QUERY");
//		$miaLista->addKey("NOTE");
		
//		$miaLista->setPassKey(true);
//		$miaLista->setPassDesc("DES_QUERY");

		$miaLista->setPassValue("ID_QUERY");
		
		$myFilter = new wi400Filter("DES_QUERY", "Descrizione Query");
		$myFilter->setFast(true);
		$myFilter->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$miaLista->addFilter($myFilter);
	
		$myFilter = new wi400Filter("ID_QUERY", "ID Query");
//		$myFilter->setFast(true);
		$myFilter->setCase('UPPER');
		$miaLista->addFilter($myFilter);
		
		$myFilter = new wi400Filter("AREA", "Area");
//		$myFilter->setFast(true);
		$myFilter->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$miaLista->addFilter($myFilter);
		
		$myFilter = new wi400Filter("FUNZIONE", "Funzione");
//		$myFilter->setFast(true);
		$myFilter->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$miaLista->addFilter($myFilter);
	
		listDispose($miaLista);
/*		
		$myButton = new wi400InputButton('SAVE');
		$myButton->setLabel("Seleziona");
		$myButton->setAction($azione);
		$myButton->setForm("CLOSE_WINDOW");
		$myButton->setGateway("QUERY_TOOL_DB_SEL");
		$buttonsBar[] = $myButton;
		
		$myButton = new wi400InputButton("CLOSE_BUTTON");
		$myButton->setAction("CLOSE");
		$myButton->setForm("CLOSE_LOOKUP");
		$myButton->setLabel("Chiudi");
		$buttonsBar[] = $myButton;
*/
	}
	else if($actionContext->getForm() == "TREE_QUERY_SEL") {
?>
		<style>
			ul.ztree {height:360px;overflow-y:scroll;overflow-x:auto;}
		</style>
		<link rel="stylesheet" href="routine/zTree/css/zTreeStyle/zTreeStyle.css" type="text/css">
		<script type="text/javascript" src="routine/zTree/js/jquery.ztree.all.min.js"></script>
		<script LANGUAGE="JavaScript">
			var zTreeObj, cont = 1000;
			// zTree configuration information, refer to API documentation (setting details)
			var setting = {
				/*async: {
					enable: true,
					//url:"index.php?t="+CURRENT_ACTION+"&f=AJAX_TREE&DECORATION=clean"
				},*/
				edit: {
					enable: false,
					showRemoveBtn: showRemoveBtn,
					showRenameBtn: showRemoveBtn
				},
				data: {
					keep: {
						parent:true,
						leaf:true
					},
					simpleData: {
						enable: true
					}
				},
				callback: {
					onClick: function(e, id, nodo, flag) {
						//console.log(id, nodo, flag);
						if(nodo.isParent) {
							zTreeObj.cancelSelectedNode();
							zTreeObj.expandNode(nodo, !nodo.open);
						}else {
							if(!zTreeObj.setting.edit.enable) {
								passValue(nodo.id_query_fold, 'ID_QUERY');
								closeLookUp();
							}
						}
					},
					beforeRemove: function(id, nodo) {
						//console.log(nodo);
						if(typeof(nodo.children) == "undefined" || nodo.children.length == 0) {
							return true;
						}else {
							alert("Svuotare la cartella per poterla eliminare!");
							return false;
						}
					}
				}
			};
	
			function showRemoveBtn(treeId, treeNode) {
				return treeNode.isParent;
			}
	
			function add(e) {
				zTreeObj.addNodes(null, {'id': cont, 'pId': 0, name:"nuova cartella", isParent: true, id_query_fold: cont});
				cont++;
			}
	
			function getDati(nodi) {
				var arr = [];
				for(var i=0; i<nodi.length; i++) {
					n = nodi[i];
					var obj = {id: n.id, pId: n.pId, isParent: n.isParent, name: n.name, id_query_fold: n.id_query_fold};
					if(typeof(n.children) != "undefined" && n.children.length) {
						obj.children = getDati(n.children);
					}
					arr.push(obj);
				}
			
				return arr;
			}

			function modifica(button) {
				jQuery(button).css("display", "none");
				jQuery("#salva_albero").css("display", "");
				jQuery("#add_cartella").css("display", "");
				zTreeObj.setting.edit.enable = true;
			}
	
			function save() {
				var nodi = zTreeObj.getNodes();
				var data = getDati(nodi);
				//console.log(data);
				
				jQuery.ajax({  
					type: "POST",
					data: {
		            	"data": data
		            },
					url: _APP_BASE + APP_SCRIPT + "?t="+CURRENT_ACTION+"&f=SAVE_TREE&DECORATION=clean"
				}).done(function ( response ) {  
					//console.log(response);
					
					jQuery("#modifica_albero").css("display", "");
					jQuery("#salva_albero").css("display", "none");
					jQuery("#add_cartella").css("display", "none");
					zTreeObj.setting.edit.enable = false;

					alert("Salvataggio eseguito con successo!");
				}).fail(function ( data ) {  
					alert("Errore salataggio albero");
				});
			}
			// zTree data attributes, refer to the API documentation (treeNode data details)
	
			jQuery(document).ready(function(){
				jQuery.ajax({  
					type: "POST",
					url: _APP_BASE + APP_SCRIPT + "?t="+CURRENT_ACTION+"&f=AJAX_TREE&DECORATION=clean"
				}).done(function ( response ) {  
					res = JSON.parse(response);
					//console.log(res[0]);
					cont = res[1];
	
					zTreeObj = jQuery.fn.zTree.init(jQuery("#treeDemo"), setting, res[0]);
				}).fail(function ( data ) {  
					alert("Errore caricamento albero");
				});
			});
		</script>
	  
		<div>
			<ul id="treeDemo" class="ztree"></ul>
		</div>
		
		<button id="modifica_albero" onClick="modifica(this)" >Modifica</button>
		<button id="salva_albero" onClick="save()" style="display: none;">Salva</button>
		<button id="add_cartella" onClick="add()" style="display: none;">Nuova cartella</button>
<?php 
	}
	else if ($actionContext->getForm() == "PASS_VALUE") {
		$key = getListKeyArray($azione."_QUERY_SEL_LIST");
//		echo "KEY:<pre>"; print_r($key); echo "</pre>";
?>
		<script type="text/javascript">
			passValue('<?=$key['ID_QUERY']?>', 'ID_QUERY');

			closeLookUp();
		</script>
<?php
	}