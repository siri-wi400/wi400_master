<?php
// SOLO DECODIFICHE GENERICHE.
// Se legate ad una package vanno inserire in base/package/<nome package>

function wi400_filter_dataFrom($field, $date){
	$date = dateViewToModel($date);
	return $field." >= ".$date;
	
}

function wi400_filter_dataTo($field, $date){
	$date = dateViewToModel($date);
	return $field." <= ".$date;
}

function wi400_filter_timestampFrom($field, $date){
	$date = dateToTimestamp($date);
	return $field." >= '".$date."'";

}

function wi400_filter_timestampTo($field, $date){
	$date = dateToTimestamp($date);
	$date = str_replace("00.00.00.000000","23.59.59.000000", $date);
	return $field." <= '".$date."'";
}

function wi400_filter_date_timestamp($field, $date, $option){
	$date = dateToTimestamp($date);
//	$date = str_replace("00.00.00.000000","23.59.59.000000", $date);
	$date = "'".$date."'";
	return $field.$option.$date;

}

function wi400_filter_date_dbdate($field, $date, $option){
	$date = dateViewToModel($date);
	$date = dateToDBdate($date);
	$date = "'".$date."'";
	return $field.$option.$date;
}

/*
 * monitor_bolle_view.php 
 */
function wi400_filter_sub_articoli($field, $value, $option) {

	return $field." in (select rretkp from FRRESINE where rrecda in ('".implode("', '", $value)."') and STATO='1')";
}

/*
 * siri_abilitazioni/manager_abil_view.php			CODABIL
 * siri_abilitazioni/manager_group_abil_view.php	CODGROUP
 */
/*
function wi400_filter_ass_group_abil($field, $value, $option) {
	if($field=="CODABIL")
		$search = "CODGROUP";
	else if($field=="CODGROUP")
		$search = "CODABIL";

	return $field." in (select distinct $field from FASSGABIL where $search in ('".implode("', '", $value)."') and STATO='1')";
}
*/