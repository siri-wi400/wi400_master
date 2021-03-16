<?php

	$subfile = new wi400Subfile($db, "LU_TIPO_EVENTI_LIST", $settings['db_temp'], 20);
	$subfile->setConfigFileName("LU_TIPO_EVENTI_LIST");
	$subfile->setModulo("lookup");
	
	$select = "select *";
	$from = " from FANAGRTM";
	$where = "";
	if (isset($_REQUEST["LU_WHERE"]) AND $_REQUEST["LU_WHERE"] != ""){
		$where = " where ".$_REQUEST["LU_WHERE"];
	}
	$order = " order by ANIDGP, ANIDRT";
	
	$sql = $select.$from.$where.$order;
	
//	echo "SQL: $sql<br>";
	
	$subfile->setSql($sql);
