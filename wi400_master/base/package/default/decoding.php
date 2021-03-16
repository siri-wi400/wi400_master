<?php

function wi400_format_STATO_ARTICOLO($value){
	global $db;
	
	static $stmt;
	$stato = "";
	
	if (! isset ( $stmt )) {
		$sql = "SELECT DSSTARY6 FROM TBSARY6P WHERE CDSTARY6=?";
		$stmt = $db->prepareStatement ( $sql, 1 );
	}
	$result = $db->execute ( $stmt, array ( $value ) );
	$row = $db->fetch_array ( $stmt );
	if ($row){
		if (trim($value) != ""){
			$stato = $value." - ";
		}
		$stato .= $row["DSSTARY6"];
	}
	return $stato;

}
?>
