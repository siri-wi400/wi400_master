<?php

	$date = date("Ymd");
	$hour = date("His");
	$idUser = $_SESSION['user'];

	$field_names_array = array(
		"SELECT_FLD" => "SELECT",
		"FROM_FLD" => "FROM", 
		"WHERE_FLD" => "WHERE",
		"GROUP_FLD" => "GROUP_BY",
		"ORDER_FLD" => "ORDER_BY",
		"FONT_CAT" => "FONT_CATALOGO"
	);
	
	$not_sel_query = array(
		"INSERT",
		"UPDATE",
		"DELETE",
		"DROP"
	);
	
	$query_admin_array = array(
		"QUERY_ADMIN",
		"QUERY_USER",
		"QUERY_FILTRO",
		"QUERY_MARKER"
	);