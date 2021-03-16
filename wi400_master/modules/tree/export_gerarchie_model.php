<?php

	require_once 'export_gerarchie_common.php';
	
	$azione = $actionContext->getAction();
	
	$off = 1;
	if(!in_array($actionContext->getForm(), array("EXPORT"))) {
		$off = 2;
		$history->addCurrent();
	}
	
	$steps = $history->getSteps();
//	echo "STEPS:<pre>"; print_r($steps); echo "</pre>";
	
	$array_steps = get_history_steps($off, $steps);
	
	$last_action = $array_steps['LAST_ACTION'];
	$last_form = $array_steps['LAST_FORM'];
//	echo "LAST_ACTION: $last_action - LAST FORM: $last_form<br>";

	$pdv_src = array();
	if(wi400Detail::getDetailValue($azione.'_SRC',"PDV_SRC")!="")
		$pdv_src = wi400Detail::getDetailValue($azione.'_SRC',"PDV_SRC");
//	echo "PDV:<pre>"; print_r($pdv_src); echo "</pre>";

	$ger_src = array();
	if(wi400Detail::getDetailValue($azione.'_SRC',"GERARCHIA_SRC")!="")
		$ger_src = wi400Detail::getDetailValue($azione.'_SRC',"GERARCHIA_SRC");
//	echo "GERARCHIA:<pre>"; print_r($ger_src); echo "</pre>";

	if($actionContext->getForm()=="DEFAULT") {
		$label = $actionContext->getLabel();
		$actionContext->setLabel("Parametri");
	}
	else if(in_array($actionContext->getForm(), array("LIST", "EXPORT"))) {
		$select = "select MAACDE, MAAGER, MAAPAP";
		$from = " from FMAAGERE";
		
		$where = " where MAASTA='1'";
		
		if(!empty($ger_src))
			$where .= " and MAAGER in ('".implode("', '", $ger_src)."')";
		
		if(!empty($pdv_src))
			$where .= " and MAACDE in ('".implode("', '", $pdv_src)."')";
		
		$where .= " and MAACDE in (select MAFCDE from FMAFENTI b, LATERAL (
				SELECT rrn(i) AS NREL
				FROM LMAFENTI i
				WHERE b.MAFCDE = i.MAFCDE and digits(MAFAVA)!!digits(MAFMVA)!!digits(MAFGVA) <= ".date("Ymd")."
				ORDER BY digits(i.MAFAVA)!!digits(i.MAFMVA)!!digits(i.MAFGVA) desc
				FETCH FIRST ROW ONLY ) AS y
			where rrn(b)=y.NREL and MAFTPE='01' and substr(MAFDUM, 4, 1)='S' and MAFSTA='1')";
		
		$group_by = " group by MAACDE, MAAGER, MAAPAP";
		
		$order_by = " order by MAACDE, MAAGER";
/*		
		$select .= ", MAFDSE";
		$from .= ", FMAFENTI b, LATERAL (
			SELECT rrn(i) AS NREL
			FROM LMAFENTI i
			WHERE b.MAFCDE = i.MAFCDE and digits(MAFAVA)!!digits(MAFMVA)!!digits(MAFGVA) <= ".date("Ymd")."
			ORDER BY digits(i.MAFAVA)!!digits(i.MAFMVA)!!digits(i.MAFGVA) desc
			FETCH FIRST ROW ONLY ) AS y";
		$where .= " and rrn(b)=y.NREL and MAFTPE='01' and substr(MAFDUM, 4, 1)='S' and MAFSTA='1'";
		$group_by .= ", MAFDSE";
*/		
		$sql = $select.$from.$where.$group_by.$order_by;
//		echo "SQL: $sql<br>";
		
		$res = $db->query($sql, false, 0);
		
		$pdv_array = array();
		$ger_array = array();
		while($row = $db->fetch_array($res)) {
/*			
			$ente_array = get_campo_ente($row['MAACDE'], date("Ymd"));
//			echo "ENTE: ".$row['MAACDE']." - TIPO: ".$ente_array['MAFTPE']." - APERTO: ".substr($ente_array['MAFDUM'], 3, 1)."<br>";
				
			if($ente_array['MAFTPE']!="01")
				continue;
				
			// Aperto
			if(substr($ente_array['MAFDUM'], 3, 1)!="S")
				continue;
				
//			echo "<font color='red'>PDV</font><br>";
*/			
			$ger = trim($row['MAAGER']);
			
			if(!isset($pdv_array[$row['MAACDE']]) || !array_key_exists($ger, $pdv_array[$row['MAACDE']]))		
				$pdv_array[$row['MAACDE']][$ger] = $row['MAAPAP'];
			
			if(!in_array($ger, $ger_array))
				$ger_array[] = $ger;
		}
		
		sort($ger_array);
		
//		echo "<font color='blue'>PDV ARRAY:</font><pre>"; print_r($pdv_array); echo "</pre>";
//		echo "<font color='blue'>GER ARRAY:</font><pre>"; print_r($ger_array); echo "</pre>";

		$idList = "EXPORT_GERARCHIE_".$actionContext->getForm()."_LIST";

//		wi400Session::delete(wi400Session::$_TYPE_LIST, $idList);
		deleteList($idList);
		subfileDelete($idList);
		
		$subfile = new wi400Subfile($db, $idList, $settings['db_temp'], 20);
		$subfile->setConfigFileName("EXPORT_GERARCHIE_LIST");
		$subfile->setModulo("tree");
		
		$subfile->addParameter("PDV_SRC", $pdv_src, true, true);
		$subfile->addParameter("GER_SRC", $ger_src, true, true);
		
		$subfile->addParameter("PDV_ARRAY", $pdv_array);
		$subfile->addParameter("GER_ARRAY", $ger_array);
		
		$subfile->setSql("*AUTOBODY");
		
		if($actionContext->getForm()=="EXPORT")
			wi400List::disposeSubfile(null, $subfile, "REGENERATE");
		
		$idDetail = $azione."_".$actionContext->getForm()."_DET";
		
		$ListDetail = new wi400Detail($idDetail);
		$ListDetail->setColsNum(2);
		
		$c = 0;
		
		// Pdv
		if(!empty($pdv_src)) {
			$des_pdv_array = array();
			foreach($pdv_src as $pdv) {
				$des_pdv = get_campo_ente($pdv, date("Ymd"), "MAFDSE");
		
				$des_pdv_array[] = $pdv." - ".$des_pdv;
			}
				
			$fieldDetail = new wi400Text("PDV");
			$fieldDetail->setLabel("Pdv");
			$fieldDetail->setValue(implode("<br>", $des_pdv_array));
			if($actionContext->getForm()=="LIST")
				$ListDetail->addField($fieldDetail);
			else if($actionContext->getForm()=="EXPORT")
				wi400Detail::setDetailField($idDetail, $fieldDetail);
				
			$c++;
		}
		
		// Gerarchie
		if(!empty($ger_src)) {
			$des_ger_array = array();
			foreach($ger_src as $ger) {
				$ger_tab_array = $persTable->decodifica('0200', $ger);
				$des_ger = prepare_string($ger_tab_array['DESCRIZIONE']);
		
				$des_ger_array[] = $ger." - ".$des_ger;
			}
				
			$fieldDetail = new wi400Text("GERARCHIE");
			$fieldDetail->setLabel("Gerarchie");
			$fieldDetail->setValue(implode("<br>", $des_ger_array));
			if($actionContext->getForm()=="LIST")
				$ListDetail->addField($fieldDetail);
			else if($actionContext->getForm()=="EXPORT")
				wi400Detail::setDetailField($idDetail, $fieldDetail);
				
			$c++;
		}
		
		if($c===0) {
			$fieldDetail = new wi400Text("SELEZIONI");
			$fieldDetail->setLabel("Selezioni");
			$fieldDetail->setValue("TUTTE");
			if($actionContext->getForm()=="LIST")
				$ListDetail->addField($fieldDetail);
			else if($actionContext->getForm()=="EXPORT")
				wi400Detail::setDetailField($idDetail, $fieldDetail);
		}
		
		$miaLista = new wi400List($idList, true);
			
		$miaLista->setSubfile($subfile);
		$miaLista->setFrom($subfile->getTable());
		$miaLista->setOrder("MAACDE");
			
//		echo "SQL_LIST: ".$miaLista->getSql()."<br>";
			
		$miaLista->setSelection("MULTIPLE");
			
		$cols = array(
			new wi400Column("MAACDE", "Codice PdV"),
			new wi400Column("DES_PDV", "Descrizione PdV"),
			new wi400Column("CONT", "Contingentamento"),
			new wi400Column("GER_RIF", "Gerarchia rifornibilità<br>costi prezzi"),
		);
			
		foreach($ger_array as $k => $ger) {
			$ger_tab_array = $persTable->decodifica('0200', $ger);
			$des_ger = prepare_string($ger_tab_array['DESCRIZIONE']);
			
			$col = new wi400Column("G_".$k, $ger." - ".$des_ger);
			$col->setOrientation("vertical");
			
			$cols[] = $col;
		}
		
		// Per le colonne verticali devo settare manualmente l'altezza dell'header
		$miaLista->setRowHeaderHeight(280);
			
		$miaLista->setCols($cols);
		
		if($actionContext->getForm()=="EXPORT") {
			require_once $routine_path."/classi/wi400ExportList.cls.php";
			require_once $routine_path."/classi/wi400invioEmail.cls.php";
			
			if(isset($settings['classe_export']) && $settings['classe_export']=="PhpSpreadsheet")
				require_once $moduli_path.'/list/export_list_xls_PhpSpreadsheet.php';	// EXCEL - PhpSpreadsheet
			else
				require_once $moduli_path.'/list/export_list_xls.php';					// EXCEL - PhpExcel
	
			require_once $moduli_path.'/list/export_list_csv.php';
			
			saveList($idList, $miaLista);

			$export = new wi400ExportList("ALL", $miaLista);
			
			// Recupero la query della lista (compresi i filtri utilizzati)
			$export->prepare();
			
			$export->setIdDetails(array($idDetail));
			
			$exportFormat = "excel2007";
			$exportTarget = "ALL";
			$exportFilters = true;
			
			// Lancio l'esportazione a seconda del formato (impostato nel codice html che si trova nella _view.php)
			// e ottengo il filename di ritorno in modo che sia comune con la _view.php
			switch($exportFormat) {
				case "excel5":
				case "excel2007":
					exportXLS($export, $idList, $exportFormat, $exportTarget, $exportFilters);
					break;
				case "csv":
					exportCSV($export, $idList, "ALL");
					break;
			}
			
			// Recupero dei parametri del file generato necessari per il download
			$filename = $export->getFilename();
			$filepath = $export->get_filepath();
			$TypeImage = $export->getTypeImage();
//			$temp = $export->getTemp();
//			echo "FILEPATH: $filepath\r\n";
		}
	}