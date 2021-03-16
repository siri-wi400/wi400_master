<?php

	require_once 'manager_rpg_settings_common.php';

	$azione = $actionContext->getAction();
	
	$off = 1;
	if(!in_array($actionContext->getForm(), array("SAVE"))) {
		$off = 2;
		$history->addCurrent();
	}
	
	$steps = $history->getSteps();
//	echo "STEPS:<pre>"; print_r($steps); echo "</pre>";
	
	$array_steps = get_history_steps($off, $steps);
	
	$first_action = $array_steps['FIRST_ACTION'];
	$first_form = $array_steps['FIRST_FORM'];
	
	$last_action = $array_steps['LAST_ACTION'];
	$last_form = $array_steps['LAST_FORM'];
//	echo "LAST_ACTION: $last_action - LAST FORM: $last_form<br>";
	
//	echo "REQUEST:<pre>"; print_r($_REQUEST); echo "</pre>";
//	echo "DETAIL:<pre>"; print_r(wi400Detail::getDetailValues($azione."_SRC")); echo "</pre>";

	$data_area = wi400Detail::getDetailValue($azione."_SRC", "DATA_AREA");
	
	if($actionContext->getForm()!="DEFAULT") {
		$sql = "select * from ZDTATABE a where DTANAM='$data_area'";
//		echo "SQL: $sql<br>";
		
		$result = $db->singleQuery($sql);
		
		if($row = $db->fetch_array($result)) {
			$dta_lib = $row['DTALIB'];
			$tabella = $row['DTADS'];
			$libreria = $row['DTADSL'];
		}
		
//		$campiFile = getDs($tabella, Null, True);
		$campi_tab = $db->columns($tabella, "", False, "", $libreria);
	}

	if($actionContext->getForm()=="DEFAULT") {
		$label = $actionContext->getLabel();
		$actionContext->setLabel("Parametri");
	}
	else if($actionContext->getForm()=="DETAIL") {
		
	}
	else if($actionContext->getForm()=="SAVE") {
		$string = "";
		foreach($campi_tab as $cmp => $vals) {
			$label = $vals['HEADING'];
//			$label = $campi[$cmp]['REMARKS'];
		
			$len = $vals['LENGTH_PRECISION'];
			
			$valore = $_POST[$cmp];
		
			$type = "STRING";
			$dec = "";
			switch($vals['DATA_TYPE_STRING']) {
				case "DECIMAL":
				case "NUMERIC":
				case "INTEGER":
				case "FLOAT":
					$dec = $vals['NUM_SCALE'];
					break;
				case "DATE";
					$type = "DATE";
					$valore = dateViewToModel($valore);
				break;
				case "TIMESTMP":
					$type = "COMPLETE_TIMESTAMP";
					break;
			}
		
			$align = "left";
			if($dec!="") {
				$type = "INTEGER";
				if($dec!=0) {
//					$type = "DOUBLE_".$dec;
					$type = array("DOUBLE", $dec);
//					$valore = str_replace(",", "", $valore);
				}
		
				$align = "right";
			}
			
//			echo "CAMPO: $cmp - LEN: $len - TYPE: $type - VALORE: $valore<br>";
		
			$string .= sprintf("%-".$len."s", $valore);
		}
//		echo "STRING: $string<br>";
		
		$string_prova = str_replace(' ','-',$string);
//		echo "STRING: $string_prova<br>";
//die("HERE");

		$dta = $data_area;
		if($dta_lib!="")
			$dta = $dta_lib."/".$data_area;
		
		data_area_write($dta, $string);
		
		$messageContext->addMessage("SUCCESS", "Aggiornamento effettuato con successo");
		
		$actionContext->gotoAction($azione, "DETAIL");
	}