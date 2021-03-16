<?php

	require_once $moduli_path.'/utilities/filtri_detail_common.php';

	$azione = $actionContext->getAction();
	
	$history->addCurrent();
	
	$to_user_sel = array();
	if(wi400Detail::getDetailValue($azione."_DET",'TO_USER')!="")
		$to_user_sel = wi400Detail::getDetailValue($azione."_DET",'TO_USER');
//	echo "TO USER SEL:<pre>"; print_r($to_user_sel); echo "</pre>";

	$array_campi = array(
		"SELECT" => "SELECT",
		"FROM" => "FROM",
		"WHERE" => "WHERE",
		"GROUP_BY" => "GROUP BY",
		"ORDER_BY" => "ORDER BY",
	);

	if($actionContext->getForm()=="DEFAULT") {
		
	}
	else if($actionContext->getForm()=="SHOW") {
		$to_user_array = array();
		if(empty($to_user_sel)) {
			$sql = "select $id_user_name from $id_user_file_lib/$id_user_file";
//			echo "SQL: $sql<br>";
			$res = $db->query($sql, false, 0);
		
			while($row = $db->fetch_array($res)) {
				$to_user_array[] = $row[$id_user_name];
			}
		}
		else {
			$to_user_array = $to_user_sel;
		}
//		echo "TO USER ARRAY:<pre>"; print_r($to_user_array); echo "</pre>";

		$query_array = array();
		
		if(!empty($to_user_array)) {
			foreach($to_user_array as $to_user) {
				$str = "";
				
//				$str .= "<font color='red'>UTENTE:</font> $to_user<br>";
				
				$file_path =  $settings['data_path'].$to_user."/detail/";
				
//				$str .= "<font color='green'>QUERY INDIRIZZATE</font><br>";
		
				$from_file = $file_path."QUERY_TOOL_SRC.dtl";
//				$str .= "FROM FILE: $from_file<br>";
				
				$from_filtri_objs = array();
				if(file_exists($from_file)) {
					$handle = fopen($from_file, "r");
					$from_contents = fread($handle, filesize($from_file));
					fclose($handle);
					$from_filtri_objs = unserialize($from_contents);
				}
//				echo "FROM FILTRI:<pre>"; print_r($from_filtri_objs); echo "</pre>";
				
				if(!empty($from_filtri_objs)) {
					foreach($from_filtri_objs as $filtro => $vals) {
//						$str .= "<font color='blue'>QUERY:</font> $filtro<br>";

						if(isset($query_array[$to_user]['IND'][$filtro]))
							continue;
				
						foreach($array_campi as $campo => $val) {
							if(!isset($vals[$campo]))
								continue;
							
							$field_obj = $vals[$campo];
							
							$valore = $field_obj->getValue();
							
							if(trim($valore)!="") {
//								$str .= "$val $valore<br>";

								$query_array[$to_user]['IND'][$filtro] .= " ".$val." ".$valore;
							}
						}
					}
				}
				
//				$str .= "<font color='green'>QUERY LIBERE</font><br>";
				
				$from_file = $file_path."QUERY_TOOL_LIBERO_SRC.dtl";
//				$str .= "FROM FILE: $from_file<br>";
				
				$from_filtri_objs = array();
				if(file_exists($from_file)) {
					$handle = fopen($from_file, "r");
					$from_contents = fread($handle, filesize($from_file));
					fclose($handle);
					$from_filtri_objs = unserialize($from_contents);
				}
//				echo "FROM FILTRI:<pre>"; print_r($from_filtri_objs); echo "</pre>";
				
				if(!empty($from_filtri_objs)) {
					foreach($from_filtri_objs as $filtro => $vals) {
//						$str .= "<font color='blue'>QUERY:</font> $filtro<br>";

						if(isset($query_array[$to_user]['LIB'][$filtro]))
							continue;
				
						if(!isset($vals['SQL_QUERY']))
							continue;
				
						$field_obj = $vals['SQL_QUERY'];
				
//						$str .= $field_obj->getValue()."<br>";
				
						$query_array[$to_user]['LIB'][$filtro] = $field_obj->getValue();
					}
				}
				
//				echo $str;
			}
			
//			echo "QUERY ARRAY:<pre>"; print_r($query_array); echo "</pre>";
			
//			echo "<font color='red'>FINE</font><br>";
		}
	}