<?php 

	$spacer = new wi400Spacer();
	
	if(in_array($actionContext->getForm(),array("DEFAULT","EXECUTE"))) {
		$searchAction = new wi400Detail($azione."_SRC", true);
		$searchAction->setTitle(_t('QRY_PARM'));
		$searchAction->isEditable(true);
		$searchAction->setSaveDetail(true);
		if($load_only===true)
			$searchAction->setLoadOnlyDetail(true);

//		$searchAction->setSource($array_campi);
		
		$myField = new wi400InputTextArea('SELECT');
		$myField->setLabel("SELECT");
		$myField->setSize(180);
		$myField->setRows(2);
		$myField->setValue($select);
		$myField->setReadonly($readonly);
//		echo "SELECT: $select<br>";
		
		$myLookUp = new wi400LookUp("LU_FILE_TABLE");
		$myLookUp->addJsParameter("FROM");
//		$myLookUp->addParameter("LU_FROM","<@REQUEST(FROM)@>");
		$myLookUp->addParameter("SOURCE","SELECT");
		$myField->setLookUp($myLookUp);
		
		$searchAction->addField($myField);
		
		$myField = new wi400InputTextArea('FROM');
		$myField->setLabel("FROM");
		$myField->setSize(180);
		$myField->setRows(2);
		$myField->setValue($from);
		$myField->setReadonly($readonly);
		
		$myLookUp = new wi400LookUp("LU_FILE");
		$myField->setLookUp($myLookUp);
		
		$searchAction->addField($myField);
		
		$myField = new wi400InputTextArea('WHERE');
		$myField->setLabel("WHERE");
		$myField->setSize(180);
		$myField->setRows(2);
		$myField->setValue($where);
		$myField->setReadonly($readonly);
		
		$myLookUp = new wi400LookUp("LU_FILE_TABLE");
		$myLookUp->addJsParameter("FROM");
//		$myLookUp->addParameter("LU_FROM","<@REQUEST(FROM)@>");
		$myLookUp->addParameter("SOURCE","WHERE");
		$myField->setLookUp($myLookUp);
		
		$searchAction->addField($myField);
		
		$myField = new wi400InputTextArea('GROUP_BY');
		$myField->setLabel("GROUP BY");
		$myField->setSize(180);
		$myField->setRows(2);
		$myField->setValue($group_by);
		$myField->setReadonly($readonly);
		
		$myLookUp = new wi400LookUp("LU_FILE_TABLE");
		$myLookUp->addJsParameter("FROM");
//		$myLookUp->addParameter("LU_FROM","<@REQUEST(FROM)@>");
		$myLookUp->addParameter("SOURCE","GROUP_BY");
		$myField->setLookUp($myLookUp);
		
		$searchAction->addField($myField);
		
		$myField = new wi400InputTextArea('ORDER_BY');
		$myField->setLabel("ORDER BY");
		$myField->setSize(180);
		$myField->setRows(2);
		$myField->setValue($order_by);
		$myField->setReadonly($readonly);
		
		$myLookUp = new wi400LookUp("LU_FILE_TABLE");
		$myLookUp->addJsParameter("FROM");
//		$myLookUp->addParameter("LU_FROM","<@REQUEST(FROM)@>");
		$myLookUp->addParameter("SOURCE","ORDER_BY");
		$myField->setLookUp($myLookUp);
		
		$searchAction->addField($myField);
		
		$myButton = new wi400InputButton('EXECUTE_BUTTON');
		$myButton->setLabel(_t('EXECUTE'));
		$myButton->setAction($azione);
		$myButton->setForm("EXECUTE");
		$myButton->setValidation(true);
		$searchAction->addButton($myButton);
		
		if($blocked===false) {
			$myButton = new wi400InputButton('CHANGE_SQL');
			$myButton->setAction($azione);
			$myButton->setLabel("Query libera");
			$myButton->setForm("DEFAULT_LIBERO");
			$searchAction->addButton($myButton);
		}
		
		$searchAction->dispose();
	}
	else if(in_array($actionContext->getForm(),array("DEFAULT_LIBERO","EXECUTE_LIBERO"))) {
		$searchAction = new wi400Detail($azione."_LIBERO_SRC", false);
		$searchAction->setTitle(_t('QRY_PARM'));
		$searchAction->isEditable(true);
		$searchAction->setSaveDetail(true);
		
		$myField = new wi400InputTextArea('SQL_QUERY');
		$myField->setLabel("SQL QUERY");
		$myField->setSize(180);
		$myField->setRows(10);
		$myField->setValue($sql_query);
		$myField->addValidation('required');
		$searchAction->addField($myField);
		
		$myButton = new wi400InputButton('EXECUTE_BUTTON');
		$myButton->setLabel(_t('EXECUTE'));
		$myButton->setAction($azione);
		$myButton->setForm("EXECUTE_LIBERO");
		$myButton->setValidation(true);
		$searchAction->addButton($myButton);
		
		$myButton = new wi400InputButton('CHANGE_SQL');
		$myButton->setAction($azione);
		$myButton->setLabel("Query indirizzata");
		$myButton->setForm("DEFAULT");
		$searchAction->addButton($myButton);
		
		$searchAction->dispose();
	}
	
	if(in_array($actionContext->getForm(),array("EXECUTE","EXECUTE_LIBERO")) &&
		$messageContext->getSeverity()!="ERROR"
//		&& isset($_REQUEST["FROM"])
	) {
		$spacer->dispose();
		if ($riga!==False) {
			if($actionContext->getForm()=="EXECUTE") {
				$miaLista = new wi400List($azione."_LIST", true);
				$miaLista->setExportDetails(array($azione."_SRC"));
				$miaLista->setConfigFileName($from.$azione."_LIBERO_LIST");				
				$miaLista->setField($select);
				$miaLista->setFrom($from);
				if(isset($where) && trim($where)!="")
					$miaLista->setWhere($where);
				if(isset($group_by) && trim($group_by)!="")
					$miaLista->setGroup($group_by);
				if(isset($order_by) && trim($order_by)!="")
					$miaLista->setOrder($order_by);
				
				// AutoFilter settato a false per permette di filtrare campi non esistenti in tabella (ridenominati AS), creando una query con WITH
				$miaLista->setAutoFilter(false);
				$miaLista->set_execute_like_query(true);
			}
			else if($actionContext->getForm()=="EXECUTE_LIBERO") {
				$miaLista = new wi400List($azione."_LIBERO_LIST", true);
				$miaLista->setExportDetails(array($azione."_LIBERO_SRC"));
				// Recupero un identificativo univoco in base alla query
				$idu = hash ("crc32", $value); 
				$miaLista->setConfigFileName($idu."_LIBERO_LIST");
				$miaLista->setQuery($value);
				
				// AutoFilter settato a false per permette di filtrare campi non esistenti in tabella (ridenominati AS), creando una query con WITH
				$miaLista->setAutoFilter(false);
				if(strtoupper(substr(trim($value), 0, 4))=="WITH") {
					$miaLista->setAutoFilter(true);					
					$miaLista->setPagBetween(false);
				}
			}
			
			$miaLista->setSelection("SINGLE");
			
//			echo "SQL:".$miaLista->getSql()."<br>";
			
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
					case "timestamp": $tipo = "COMPLETE_TIMESTAMP"; break;
				}
				
				$miaLista->addCol(new wi400Column($key,$key,$tipo,$align));
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
						
			listDispose($miaLista);
		}
	}

?>