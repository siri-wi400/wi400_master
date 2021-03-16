<?php

	$spacer = new wi400Spacer();
	
	if($actionContext->getForm()!="DEFAULT") {
		$actionDetail = new wi400Detail($azione."_DET");
		$actionDetail->setColsNum(2);
		
		$myField = new wi400Text("PERIODO");
		$myField->setLabel("Periodo");
		$myField->setValue("Dal $data_ini al $data_fin");
		$actionDetail->addField($myField);
		
		$myField = new wi400Text("FASCIA_ORARIA");
		$myField->setLabel("Fascia oraria");
		$myField->setValue("Dalle $ora_ini alle $ora_fin");
		$actionDetail->addField($myField);
		
		$myField = new wi400Text("TIPO_INT");
		$myField->setLabel("Tipo intervallo");
		$myField->setValue($intervalli_array[$tipo_int]);
		$actionDetail->addField($myField);
		
		$myField = new wi400Text("TIPO_DATI");
		$myField->setLabel("Tipo dati");
		foreach($tipo_dati_sel as $tipo) {
			$des_td[] = $tipo_dati_array[$tipo];
		}
		$myField->setValue(implode("<br>",$des_td));
		$actionDetail->addField($myField);
		
		$myField = new wi400Text("SUBSYS");
		$myField->setLabel("Sottosistemi");
		$myField->setValue(implode("<br>",$sel_subsys));
		$actionDetail->addField($myField);
		
		$actionDetail->dispose();
		
		$spacer->dispose();
	}
	
	if($actionContext->getForm()=="DEFAULT") {
		$searchAction = new wi400Detail($azione."_SRC");
		$searchAction->setTitle('Monitor dei lavori di sistema');
		
		// Data iniaziale
		$myField = new wi400InputText('DATA_INI');
		$myField->addValidation('date');
		$myField->addValidation('required');
		if(!isset($data_ini) || empty($data_ini))
			$myField->setValue(dateModelToView($_SESSION['data_validita']));
		else
			$myField->setValue($data_ini);
		$myField->setLabel('Data iniziale');
		$myField->setInfo('Inserisci la data iniziale');
		$searchAction->addField($myField);
		
		// Data finale
		$myField = new wi400InputText('DATA_FIN');
		$myField->addValidation('date');
		$myField->addValidation('required');
		if(!isset($data_fin) || empty($data_fin))
			$myField->setValue(dateModelToView($_SESSION['data_validita']));
		else
			$myField->setValue($data_fin);
		$myField->setLabel('Data finale');
		$myField->setInfo('Inserisci la data finale');
		$searchAction->addField($myField);
		
		// Ora iniziale
		$myField = new wi400InputText('ORA_INI');
		$myField->setLabel("Ora inizio");
		$myField->addValidation('time');
		$myField->addValidation('required');
		$myField->setMaxLength(5);
		$myField->setSize(5);
		if(!isset($ora_ini) || empty($ora_ini))
			$myField->setValue('07:00');
		else
			$myField->setValue($ora_ini);
		$myField->setInfo("Inserire l'ora di inizio della fascia oraria da considerare");
		$searchAction->addField($myField);
		
		// Ora finale
		$myField = new wi400InputText('ORA_FIN');
		$myField->setLabel("Ora fine");
		$myField->addValidation('time');
		$myField->addValidation('required');
		$myField->setMaxLength(5);
		$myField->setSize(5);
		if(!isset($ora_fin) || empty($ora_fin))
			$myField->setValue('18:00');
		else
			$myField->setValue($ora_fin);
		$myField->setInfo("Inserire l'ora di fine della fascia orario da considerare");
		$searchAction->addField($myField);
		
		// Tipo intervallo
		$mySelect = new wi400InputSelect("TIPO_INT");
		$mySelect->setLabel("Tipo intervallo");
		$myField->addValidation('required');
		foreach($intervalli_array as $key => $val) {
			$mySelect->addOption($val, $key);
		}
		$mySelect->setValue($tipo_int);
		$mySelect->setInfo("Selezionare un tipo intervallo");
		$searchAction->addField($mySelect);
		
		// Tipo dati
		$mySelect = new wi400InputSelectCheckBox('TIPO_DATI');
		$mySelect->setLabel("Tipo dati");
		$mySelect->setMultiple(true);
//		$mySelect->setColsNumber(2);
		
		$mySelect->setOptions($tipo_dati_array);
		
		$mySelect->setValue($tipo_dati_sel);
		
		$searchAction->addField($mySelect);
		
		// Sottosistemi
		$mySelect = new wi400InputSelectCheckBox('SUBSYS');
		$mySelect->setLabel("Sottosistemi");
		$mySelect->setMultiple(true);
		$mySelect->setColsNumber(5);
		
		$res_subsys = $db->query($sql_subsys,0,true);
		while($row_subsys = $db->fetch_array($res_subsys)) {
			$subsys = $row_subsys['MONSBS'];
			
			$mySelect->addOption($subsys);
		}
		
		$mySelect->setValue($sel_subsys);
		
		$searchAction->addField($mySelect);
		
		$myButton = new wi400InputButton('SEARCH');
		$myButton->setLabel("Seleziona");
		$myButton->setAction($azione);
		$myButton->setForm("LIST");
		$myButton->setValidation(true);
		$searchAction->addButton($myButton);
		
		$searchAction->dispose();
	}
	else if($actionContext->getForm()=="LIST") {
		$miaLista = new wi400List($azione."_LIST", True);
		$miaLista->setSubfile($subfile);
		$miaLista->setFrom($subfile->getTable());
		
		$miaLista->setSelection("SINGLE");
		
		$miaLista->addCol(new wi400Column("DATA","Data",$int_date_array[$tipo_int]));
		foreach($sel_subsys as $val) {
			foreach($tipo_dati_sel as $tipo) {
				$miaLista->addCol(new wi400Column($tipo."_".$val, $tipo_dati_array[$tipo]."<br>$val", "DOUBLE_2", "right"));
			}
		}
		
		$miaLista->addKey("DATA");
		
		// Grafico
		foreach($tipo_dati_sel as $tipo) {
			$action = new wi400ListAction();
			$action->setAction("GRAPH_MONITOR_LAV_SIS");
			$action->setForm($tipo);
			$action->setLabel("Grafico ".$tipo_dati_array[$tipo]);
			$action->setSelection("NONE");
			$miaLista->addAction($action);
		}
		
		listDispose($miaLista);
	}

?>