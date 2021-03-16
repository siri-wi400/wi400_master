<?php

//	$data_val = date("Ymd");
	$data_val = $_SESSION['data_validita'];
//	echo "DATA VAL: $data_val<br>";

	$subfile = new wi400Subfile($db, "LU_OPERATORI_LIST", $settings['db_temp'], 20);
	$subfile->setConfigFileName("LU_OPERATORI_LIST");
	$subfile->setModulo("lookup");
	
	$sql = "SELECT T703CD 
		FROM FTAB703";

	if (isset($_REQUEST["FILTER_SQL"]) AND $_REQUEST["FILTER_SQL"] != ""){
		$sql .= " WHERE ".$_REQUEST["FILTER_SQL"];
	}
	
//	echo "SQL: $sql<br>";

	$subfile->addParameter("DATA_VAL", $data_val, true);
	
	$subfile->setSql($sql);