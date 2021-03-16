<?php

//	$data_val = date("Ymd");
	$data_val = $_SESSION['data_validita'];
	if(isset($_REQUEST['LU_DATA_VAL']) && $_REQUEST['LU_DATA_VAL']!="") {
		$data_val = $_REQUEST['LU_DATA_VAL'];
	}
//	echo "DATA VAL: $data_val<br>";

	$check_al_abil = "";
	if(isset($_REQUEST['CHECK_AL_ABIL']) && $_REQUEST['CHECK_AL_ABIL']!="") {
		$check_al_abil = "S";
	}
	
	$subfile = new wi400Subfile($db, "LU_RICETTE_ABIL_LIST", $settings['db_temp'], 20);
	$subfile->setConfigFileName("LU_RICETTE_ABIL_LIST");
	$subfile->setModulo("lookup");
	
	$sql = "SELECT RICCDA, RICDSA
		FROM FRICANAR
		WHERE '$data_val' BETWEEN
			DIGITS(RICAVA)!!digits(RICMVA)!!digits(RICGVA)
			AND DIGITS(RICAFV)!!digits(RICMFV)!!digits(RICGFV)";

	if (isset($_REQUEST["FILTER_SQL"]) AND $_REQUEST["FILTER_SQL"] != ""){
		$sql .= " AND ".$_REQUEST["FILTER_SQL"];
	}
	
//	echo "SQL: $sql<br>";

	$subfile->addParameter("DATA_VAL", $data_val, true);
	$subfile->addParameter("CHECK_AL_ABIL", $check_al_abil, true);
	
	$subfile->setSql($sql);