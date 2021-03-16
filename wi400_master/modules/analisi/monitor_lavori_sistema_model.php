<?php

	require_once 'monitor_lavori_sistema_commons.php';
	
	$azione = $actionContext->getAction();
	
	$steps = $history->getSteps();
//	echo "STEPS:<pre>"; print_r($steps); echo "</pre><br>";
	$last_action = "";
	if(!empty($steps)) {
		$last_step = $steps[count($steps)-1];
//		echo "LAST STEP: $last_step<br>";
	
		$last_action_obj = $history->getAction($last_step);
		if (isset($last_action_obj)) {
			$last_action = $last_action_obj->getAction();
			$last_form = $last_action_obj->getForm();
		}
//		echo "LAST_ACTION: $last_action - LAST FORM: $last_form<br>";
	}
	
	$history->addCurrent();
	
//	echo "REQUEST:<pre>"; print_r($_REQUEST); echo "</pre>";
	
	$data_ini = wi400Detail::getDetailValue($azione."_SRC","DATA_INI");
	$data_fin = wi400Detail::getDetailValue($azione."_SRC","DATA_FIN");
	$ora_ini = wi400Detail::getDetailValue($azione."_SRC","ORA_INI");
	$ora_fin = wi400Detail::getDetailValue($azione."_SRC","ORA_FIN");
	$tipo_int = wi400Detail::getDetailValue($azione."_SRC","TIPO_INT");
	
	$sql_subsys = "select MONSBS from $tabella group by MONSBS order by MONSBS";
	
	$steps = $history->getSteps();
//	echo "STEPS:<pre>"; print_r($steps); echo "</pre><br>";
	$current_step = $steps[count($steps)-1];
//	echo "CURRENT STEP: $current_step<br>";

	$tipo_dati_sel = array();
	if($current_step==$azione."_DEFAULT" && isset($_SESSION[$azione.'_TIPO_DATI']))
		$tipo_dati_sel = $_SESSION[$azione.'_TIPO_DATI'];
	else if($last_action!="" && $last_action!=$azione)
		$tipo_dati_sel = $_SESSION[$azione.'_TIPO_DATI'];
	else {
		if($current_step==$azione."_DEFAULT" && !isset($_SESSION[$azione.'_TIPO_DATI'])) {
			$tipo_dati_sel = array_keys($tipo_dati_array);
		}
		else if($steps[count($steps)-2]==$azione."_DEFAULT") {
			foreach($tipo_dati_array as $key => $val) {
				if(isset($_POST[$key])) {
					$tipo_dati_sel[] = $key;
				}
			}
		}
		
		$_SESSION[$azione.'_TIPO_DATI'] = $tipo_dati_sel;
	}
//	echo "TIPO DATI:<pre>"; print_r($tipo_dati_sel); echo "</pre>";
	
	$sel_subsys = array();
	if($current_step==$azione."_DEFAULT" && isset($_SESSION[$azione.'_SUBSYS_ARRAY']))
		$sel_subsys = $_SESSION[$azione.'_SUBSYS_ARRAY'];
	else if($last_action!="" && $last_action!=$azione)
		$sel_subsys = $_SESSION[$azione.'_SUBSYS_ARRAY'];
	else {
		$res_subsys = $db->query($sql_subsys,0,true);
		while($row_subsys = $db->fetch_array($res_subsys)) {
			$subsys = $row_subsys['MONSBS'];
			
			if($current_step==$azione."_DEFAULT" && !isset($_SESSION[$azione.'_SUBSYS_ARRAY'])) {
				$sel_subsys[] = $subsys;
			}
			else if($steps[count($steps)-2]==$azione."_DEFAULT") {
				if(isset($_POST[$subsys])){
					$sel_subsys[] = $subsys;
				}
			}
		}
		
		$_SESSION[$azione.'_SUBSYS_ARRAY'] = $sel_subsys;
	}
//	echo "SOTTOSISTEMI:<pre>"; print_r($sel_subsys); echo "</pre>";
	
	if($actionContext->getForm()=="DEFAULT") {
		$actionContext->setLabel('Parametri');
	}
	else if($actionContext->getForm()=="LIST") {
		$int_ini = time_to_timestamp($data_ini, $ora_ini.":00");
		$int_fin = time_to_timestamp($data_fin, $ora_fin.":00");
//		echo "INT INI: $int_ini - INT FIN: $int_fin<br>";
		
		$sql = "with PT as (".get_sql($int_ini, $int_fin, $sel_subsys, $tipo_int, $tipo_dati_sel).") 
			SELECT data";
		$where_sbs = array();
		foreach($sel_subsys as $val) {
			if(in_array("MEDIA", $tipo_dati_sel))
				$where_sbs[] = "avg(case when monsbs='$val' then MEDIA end) as MEDIA_".$val;
			if(in_array("PICCO", $tipo_dati_sel))
				$where_sbs[] = "max(case when monsbs='$val' then PICCO end) as PICCO_".$val;
		}
		if(!empty($where_sbs)) {
			$sql .= ", ".implode(", ", $where_sbs);
		}
		$sql .= " FROM PT
			GROUP BY data
			ORDER BY data";
//		echo "SQL: $sql<br>";
		
//		subfileDelete("MONITOR_LAVORI_SISTEMA");
		
		$subfile = new wi400Subfile($db, $azione."_LIST", $settings['db_temp'], 20);
		$subfile->setConfigFileName("MONITOR_LAVORI_SISTEMA");	
		$subfile->setModulo('analisi');
		
		$subfile->addParameter("CHIAVE", date("YMDhis"), True);
		$subfile->addParameter("SUBSYS", $sel_subsys, True, True);
		$subfile->addParameter("TIPO_DATI", $tipo_dati_sel,True,True);
		$subfile->addParameter("DATA_INI", $data_ini);
		$subfile->addParameter("DATA_FIN", $data_fin);
		$subfile->addParameter("ORA_INI", $ora_ini);
		$subfile->addParameter("ORA_FIN", $ora_fin);
		$subfile->addParameter("TIPO_INT", $tipo_int);
		
		$subfile->addExtraRow("Med.");
		
		$subfile->setSql($sql);
		
//		echo "TABELLA: ".$subfile->getTable()."<br>";
	}

?>