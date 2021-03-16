<?php

	$azione = $actionContext->getAction();
	
	$idList = $_GET['IDLIST'];
	$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
/*	
	if($wi400List->getQuery()!="") {
		$query = $wi400List->getQuery();
//		echo "QUERY: $query<br>";
	}
	else {
		$query = $wi400List->getSql();
//		echo "SQL: $query<br>";
	}
*/
	// ***************************************************
	// COSTRUZIONE QUERY
	// ***************************************************
	require_once $routine_path.'/classi/wi400ListSql.cls.php';
	
	$wi400ListSql = new wi400ListSql($wi400List, $wi400List->getAutoFilter());
	
//	$wi400ListSql->prepare_query_parts();
	
	$query = trim($wi400ListSql->get_query());
//	echo "QUERY: $query<br>";