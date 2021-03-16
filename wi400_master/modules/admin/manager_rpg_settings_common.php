<?php

	function get_data_area_values($data_area, $dta_lib="") {
		global $db;
	
		static $stmt_dta;
	
		if(!isset($stmt_dta)) {
			$sql_dta = "select * from ZDTATABE a where DTANAM=?";
			if($dta_lib!="")
				$sql_dta .= " and DTALIB=?";
//			echo "SQL_DTA: $sql_dta<br>";
	
			$stmt_dta = $db->singlePrepare($sql_dta, 0, true);
		}
	
		$campi = array($data_area);
		if($dta_lib!="")
			$campi[] = $dta_lib;
		
//		echo "DATA_AREA: $data_area - LIB: $dta_lib<br>";
	
		$res = $db->execute($stmt_dta, $campi);
	
		if($row = $db->fetch_array($stmt_dta)) {
			$dta_lib = $row['DTALIB'];
			$tabella = $row['DTADS'];
			$libreria = $row['DTADSL'];
	
//			$campiFile = getDs($tabella, Null, True);
			$campi_tab = $db->columns($tabella, "", False, "", $libreria);
			
			$c = 1;
			$dta_array = array();
			foreach($campi_tab as $cmp => $vals) {
//				echo "CMP: $cmp<br>";
				
				$label = "";
//				$label .= trim($vals['HEADING']);
//				$label .= " - ";
				$label .= trim($vals['REMARKS']);
				$label .= "<br>(".trim($vals['HEADING']).")";
//				echo "LABEL: $label<br>";
			
				$len = $vals['LENGTH_PRECISION'];
					
				$valore = data_area_read($tabella, $c, $len);
				
				$dta_array[$cmp] = $valore;
				
//				echo "CAMPO: $cmp - LABEL: $label - INI: $c - LEN: $len - VALORE: $valore<br>";
				
				$c += $len;
			}
	
			return $dta_array;
		}
	
		return false;
	}