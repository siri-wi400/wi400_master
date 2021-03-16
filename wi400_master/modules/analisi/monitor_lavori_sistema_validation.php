<?php

	require_once 'monitor_lavori_sistema_commons.php';

	if($actionContext->getForm()=="DEFAULT") {
		if (isset($_POST["DATA_INI"]) &&  isset($_POST["DATA_FIN"])){
			$check = check_periodo($_POST["DATA_INI"], $_POST["DATA_FIN"]);
			
			if($check===false)
				$messageContext->addMessage("ERROR", "La data di INIZIO deve essere precedente a quella di FINE.", "DATA_INI",true);
		}
		
		if (isset($_POST["ORA_INI"]) &&  isset($_POST["ORA_FIN"])){
			$ora_ini = wi400_format_TIME_INTEGER($_POST['ORA_INI'].":00");
			$ora_fin = wi400_format_TIME_INTEGER($_POST['ORA_FIN'].":00");
			
			if($ora_fin<$ora_ini)
				$messageContext->addMessage("ERROR", "L'ora di INIZIO deve essere precedente a quella di FINE.", "ORA_INI",true);
		}
		
		$sql_subsys = "select MONSBS from $tabella group by MONSBS order by MONSBS";
		
		$res_subsys = $db->query($sql_subsys,0,true);
		$sel_subsys = array();
		while($row_subsys = $db->fetch_array($res_subsys)) {
			$subsys = $row_subsys['MONSBS'];
			if(isset($_POST[$subsys])){
				$sel_subsys[] = $subsys;
			}
		}
		if(empty($sel_subsys)) {
			$_SESSION["MONITOR_LAVORI_SISTEMA_SUBSYS_ARRAY"] = array();
			$messageContext->addMessage("ERROR", "Selezionare almeno un sottosistema", "SUBSYS",true);
		}
		else {
			$_SESSION["MONITOR_LAVORI_SISTEMA_SUBSYS_ARRAY"] = $sel_subsys;
		}
		
		$tipo_dati_sel = array();
		foreach($tipo_dati_array as $key => $val) {
			if(isset($_POST[$key]))
				$tipo_dati_sel[] = $key;
		}
		if(empty($tipo_dati_sel)) {
			$_SESSION["MONITOR_LAVORI_SISTEMA_TIPO_DATI"] = array();
			$messageContext->addMessage("ERROR", "Selezionare almeno un Tipo dati", "TIPO_DATI",true);
		}
		else {
			$_SESSION["MONITOR_LAVORI_SISTEMA_TIPO_DATI"] = $tipo_dati_sel;
		}
	}

?>