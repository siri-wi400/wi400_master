<?php

	function wi400_decorator_ICON_MAPPING($value, $parameters = array()) {
		if($value == '') return '';
	
		$type = "DESTINAZIONE";
		if(isset($parameters["ROW"])) {
			$type .= "-".$parameters["ROW"];
		}
		
		list($id, $tipo, $curr_azione) = explode("|", $value);
		
		$url_tipo = "";
		if($tipo == 'DETAIL') {
			$url_tipo = "MAP_DETAIL=".$id;
		}else {
			$url_tipo = "MAP_LIST=".$id;
		}
	
		$onClick = "doSubmit(\"ABILITAZIONI_CAMPI_DETAIL\", \"MAP_DETAIL&FROM_DEVELOPER=1&$url_tipo&WIDAZI=$curr_azione\");";
	
		$icon = "<img id='$type' onClick='$onClick' src='themes/common/images/pencil.png' class='wi400-pointer' border='0'>";
	
		return $icon;
	}
	
	function checkIsMapping($id, $tipo, $azione) {
		global $db;
		
		static $stmt_check_mapping;
		
		if(!$stmt_check_mapping) {
			$query = "SELECT WIDKEY FROM ZWIDETPA WHERE WIDAZI=? AND WIDID=? AND WIDDOL=?";
			$stmt_check_mapping = $db->singlePrepare($query);
		}
		
		$tipo = $tipo == 'DETAIL' ? 'D' : 'L';
		
		//$query = "SELECT WIDKEY FROM ZWIDETPA WHERE WIDAZI='$azione' AND WIDID='$id' AND WIDDOL='$tipo'";
		//$rs = $db->singleQuery($query);
		$rs = $db->execute($stmt_check_mapping, array($azione, $id, $tipo));
		$row = $db->fetch_array($stmt_check_mapping);
		
		if($row) {
			return 'Mappato';
		}else {
			return 'Da mappare';
		}
	}