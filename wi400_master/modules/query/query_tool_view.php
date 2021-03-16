<?php 

	$spacer = new wi400Spacer();
	
	if(in_array($actionContext->getForm(),array("DEFAULT","EXECUTE"))) {
		if($blocked===true && $query_libera===true) {
			$readonly = true;
		}
//		echo "VIEW - READONLY: $readonly<br>";
		
		$searchAction = new wi400Detail($idDetail, true);
		$searchAction->setTitle(_t('QRY_PARM'));
		$searchAction->isEditable(true);
		$searchAction->setSaveDetail(true);
		if($readonly===true)
			$searchAction->setLoadOnlyDetail(true);

//		$searchAction->setSource($array_campi);

		if(isset($des_query) && !empty($des_query)) {
			$fieldDetail = new wi400Text("DES_QUERY");
//			$fieldDetail = new wi400InputText("DES_QUERY");
			$fieldDetail->setLabel("Query");
			$fieldDetail->setValue($des_query);
//			$fieldDetail->setReadonly(true);
			$searchAction->addField($fieldDetail);
		}
		
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
		$searchAction->addField($myField);
		
		$myField = new wi400InputSwitch("FONT_CATALOGO");
		$myField->setLabel("Font Catalogo");
		$myField->setOnLabel(_t('LABEL_YES'));
		$myField->setOffLabel(_t('LABEL_NO'));
		$myField->setChecked($check_font_catalogo);
		$myField->setValue(1);
		if($hide_query===true || $readonly===true)
			$myField->setType("HIDDEN");
		$searchAction->addField($myField);
		
		// Totali colonne
		$myField = new wi400InputText('TOTALI_STR');
		$myField->setLabel('Totali');
		$myField->setSize(50);
//		$myField->setMaxLength(50);
//		$myField->setCase("UPPER");
		$myField->setInfo("Inserire i valori in formato <CAMPO1>;<CAMPO2>");
		$myField->setValue($totali_str);
		$myField->setReadonly($readonly);
		if($hide_query===true)
			$myField->setType("HIDDEN");
		$searchAction->addField($myField);
		
		$myButton = new wi400InputButton('EXECUTE_BUTTON');
		$myButton->setLabel(_t('EXECUTE'));
		$myButton->setAction($azione);
		$myButton->setForm("EXECUTE");
		$myButton->setValidation(true);
		$searchAction->addButton($myButton);
		
		if($readonly===false) {
			$myButton = new wi400InputButton('MARKERS');
			$myButton->setLabel("Aggiungi Markers");
			$myButton->setAction($azione);
			$myButton->setForm("MARKERS_DEL");
			$myButton->setTarget("WINDOW");
			$searchAction->addButton($myButton);
		}
		
		if($hide_query===false && $readonly===false) {
			$myButton = new wi400InputButton('CLEAN_SQL');
			$myButton->setLabel("Pulisci Query");
			$myButton->setAction($azione);
			$myButton->setForm("CLEAN");
			$searchAction->addButton($myButton);
		}
		
		if($query_libera===false && $blocked===false) {
			$myButton = new wi400InputButton('CHANGE_SQL');
			$myButton->setLabel("Query libera");
			$myButton->setAction($azione);
			$myButton->setForm("DEFAULT_LIBERO");
			$searchAction->addButton($myButton);
		}
		else if($query_libera===true && $loadonly===false) {
			$myButton = new wi400InputButton('CHANGE_SQL');
			$myButton->setAction($azione);
			$myButton->setLabel("Query indirizzata");
			$myButton->setForm("RETURN_DEFAULT");
			$searchAction->addButton($myButton);
		}
		
		$searchAction->dispose();
	}
	
	if(in_array($actionContext->getForm(), array("DEFAULT","EXECUTE"))) {
		if(!empty($markers)) {
			$spacer->dispose();
				
			$searchAction = new wi400Detail($idDetailMarkers, true);
			$searchAction->setTitle("Markers");
			$searchAction->isEditable(true);
//			$searchAction->setSaveDetail(true);

			if(isset($settings['show_field_required']) && $settings['show_field_required']===true)
				$searchAction->setShowFieldRequired(true);
	
			foreach($markers as $mark) {
//				echo "MARKER: $mark<br>";
	
				$val = "";
				if(isset($marker_values[$mark]))
					$val = $marker_values[$mark];
	
				$myField = marker_field($mark, $val);
	
				$searchAction->addField($myField);
			}
			
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
			$miaLista = new wi400List($azione."_LIST", true);
			$miaLista->setExportDetails(array($idDetail));
			
			// Recupero un identificativo univoco in base alla query
			$idu = hash ("crc32", $sql_query);
			
			$miaLista->setConfigFileName($idu."_QUERY_LIST");
			
			if($query_libera===false) {
/*				
				$miaLista = new wi400List($azione."_LIST", true);
				$miaLista->setExportDetails(array($idDetail));
				
				$miaLista->setConfigFileName($from.$azione."_LIBERO_LIST");	
*/
				if(!empty($marker_values)) {
					foreach($array_campi as $campo => $val) {
						$field = strtolower($campo);
				
						$$field = replace_markers($$field, $marker_values);
//						echo "$campo: ".$$field."<br>";
					}
				}
				
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
		$myButton->setScript('closeLookUp()');
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