<?php 

	$spacer = new wi400Spacer();
	
	if($actionContext->getForm()=="DEFAULT") {
		$searchAction = new wi400Detail($idDetail, true);
		$searchAction->setTitle($label);
		$searchAction->isEditable(true);
		$searchAction->setSaveDetail(true);
		
		// Query
		$myField = new wi400InputText('ID_QUERY');
		$myField->setLabel("Query");
		$myField->setShowMultiple(true);
		$myField->setSortMultiple(true);
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$myField->setCase("UPPER");
		$myField->addValidation('required');
		$myField->setValue($id_query_array);
		
		$decodeParameters = array(
			'TYPE' => 'common',
			'TABLE_NAME' => "TABQUERY",
			'COLUMN' => 'DES_QUERY',
			'KEY_FIELD_NAME' => 'ID_QUERY',
			'AJAX' => true,
			'COMPLETE' => true,
			'COMPLETE_MIN' => 2,
			'COMPLETE_MAX_RESULT' => 15
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE", "TABQUERY");
		$myLookUp->addParameter("CAMPO","ID_QUERY");
		$myLookUp->addParameter("DESCRIZIONE", "DES_QUERY");
		$myLookUp->addParameter("LU_WHERE", $where);
		$myField->setLookUp($myLookUp);
		
		$myLookUp = new wi400LookUp("LU_QUERY");
//		$myLookUp->addParameter("FILE", "TABQUERY");
		$myLookUp->addParameter("CAMPO", "ID_QUERY");
		$myLookUp->addParameter("DESCRIZIONE", "DES_QUERY");
		$myLookUp->addParameter("LU_WHERE", $where);
		$myField->setLookUp($myLookUp);
		
		$searchAction->addField($myField);
		
		$myButton = new wi400InputButton('SELECT_BUTTON');
		$myButton->setLabel("Seleziona");
		$myButton->setAction($azione);
		$myButton->setForm("SELECT_FRAME");
		$myButton->setValidation(true);
		$searchAction->addButton($myButton);
/*		
		$myButton = new wi400InputButton('EXECUTE_BUTTON');
		$myButton->setLabel("Esegui");
		$myButton->setAction($azione);
		$myButton->setForm("EXECUTE_FRAME");
		$myButton->setValidation(true);
		$searchAction->addButton($myButton);
*/		
		$searchAction->dispose();
	}
	else if(in_array($actionContext->getForm(), array("SELECT_FRAME", "EXECUTE_FRAME"))) {
		$azioniDetail = new wi400Detail($azione."_DETAIL");
		
		$sql_des = "select ID_QUERY, DES_QUERY from TABQUERY where ID_QUERY=? and STATO='1'";
		$stmt_des = $db->singlePrepare($sql_des);
		
		foreach($id_query_array as $id_key => $id_query) {
			$des_query = "";
			
			$res_des = $db->execute($stmt_des, array($id_query));
			if($row_des = $db->fetch_array($stmt_des))
				$des_query = $row_des['DES_QUERY'];
			
			$title_tab = "Query ".($id_key+1);
			$title_tab .= ": $id_query";
			$title_tab .= " - ".$des_query;
			
			$azioniDetail->addTab("opp_".$id_key, $title_tab);
			
			if($actionContext->getForm()=="SELECT_FRAME")
				$to_form = "DEFAULT";
			else if($actionContext->getForm()=="EXECUTE_FRAME")
				$to_form = "EXECUTE";
			
			$iframe = new wi400Iframe("IFRAME_DETAIL_".$id_key, "QUERY_TOOL_DB", $to_form."&ID_QUERY=$id_query", "QUERY_TOOL_DB_MULTI");
//			$iframe->setDecoration("lookup");
			$iframe->setStyle("height: 500px;");
			$iframe->setAutoResize(false);
			
			$myField = new wi400InputText('CAMPO_QUERY_'.$id_key);
			$myField->setCustomHTML($iframe->getHtml());
//			$myField->setHeight(500);
			
			$azioniDetail->addField($myField, "opp_".$id_key);
		}
		
		$azioniDetail->dispose();
	}