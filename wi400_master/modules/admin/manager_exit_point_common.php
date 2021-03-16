<?php

 	function functionUpdateRow(wi400List $wi400List, $request) {
 		global $wi400_trigger;
 		
 		if($request['COLONNA'] == "EXSTA") {
 			$key = $request['LIST_KEY'];
 			$keyArray = explode("|",$key);
 			
			$row = $wi400List->getCurrentRow();
		
			$rs = $wi400_trigger->updateStatoTestata($row['EXSTA'], $keyArray[0], $keyArray[1]);
 		}
	
		return $wi400List;
	}
	
	function functionUpdateRow_det(wi400List $wi400List, $request) {
		global $wi400_trigger;
			
		if($request['COLONNA'] == "EASTA") {
			$id = $request['EXITP_ID'];
			$evento = $request['EXITP_EVENTO'];
			
			$key = $request['LIST_KEY'];
			$keyArray = explode("|",$key);
	
			$row = $wi400List->getCurrentRow();
	
			$rs = $wi400_trigger->updateDettaglioValue(array("EASTA" => $row['EASTA']), $id, $evento, $keyArray[0]);
		}
	
		return $wi400List;
	}
	
	