<?php

class common extends wi400Decoding {

	public function decode(){			
		global $db, $settings;

		$decodeParameters = $this->getDecodeParameters();	

		if(isset($decodeParameters['SPECIAL_VALUE']) && in_array($this->getFieldValue(), array_keys($decodeParameters['SPECIAL_VALUE']))) {
			return $decodeParameters['SPECIAL_VALUE'][$this->getFieldValue()];
		}
		$select = "select ".$decodeParameters['COLUMN'];
		if(isset($decodeParameters['LU_SELECT']))
			$select = "select ".str_replace("|", ", ", $decodeParameters['LU_SELECT']);
		if(isset($decodeParameters['LU_FIELDS']))
			$select = "select ".$decodeParameters['LU_FIELDS'];
		
		if(isset($decodeParameters['UNION_ALL']) && $decodeParameters['UNION_ALL']) {
			$union = $decodeParameters['UNION_ALL'];
			
			$arr_sql_union = array();
			foreach ($union as $index => $gruppo) {
				$arr_sql_union[] = "select '".$gruppo."' as GRUPPO, '$index' as KEY FROM SYSIBM".$settings['db_separator']."SYSDUMMY1";
			}
			
			$decodeParameters['TABLE_NAME'] = "(".implode(" union all ", $arr_sql_union).") as x";
		}
		
		$from = " from ".$decodeParameters['TABLE_NAME'];
		$compare = "='?'";
		if (isset($decodeParameters['LIKE_KEY'])) {
			$compare = " LIKE '%?%'";
		}
		if (isset($decodeParameters['SELECTION_KEY'])) {
		    $compare = $decodeParameters['SELECTION_KEY'];
		
		}
		
		$value = $this->getFieldValue();
		$value = sanitize_sql_string($value);
		
		$compare = str_replace('?', $value,$compare);
		
		$where = " where ".$decodeParameters['KEY_FIELD_NAME'].$compare;
		if (!isset($decodeParameters['RETURN_COLUMN'])) {
		     $decodeParameters['RETURN_COLUMN']=$decodeParameters['COLUMN'];
		}
		
		$filter = '';
		if (isset($decodeParameters['FILTER_SQL']) && $decodeParameters['FILTER_SQL']!='') {
		    $filter = ' and '.$decodeParameters['FILTER_SQL'];
		}
		
		$where_cond = '';
		if(isset($decodeParameters['WHERE_COND']) && $decodeParameters['WHERE_COND']!='') {
			$where_cond = $decodeParameters['WHERE_COND'];
			
//			echo "REQUEST:<pre>"; print_r($_REQUEST); echo "</pre>";
			
			// Sostituazione parametri JSON
			$where_cond = substituteRequestParmLookup($where_cond, $_REQUEST);

			$where_cond = ' and '.$where_cond;
		}
		
		$group_by = "";
		if (isset($decodeParameters["GROUP_BY"]) && $decodeParameters["GROUP_BY"]!=""){
			$group_by = " group by ".$decodeParameters["GROUP_BY"];
		}

		$sql = $select.$from.$where.$filter.$where_cond.$group_by;
//		echo "SQL: $sql<br>";
		if (isset($decodeParameters["DIRECT_SQL"]) && $decodeParameters["DIRECT_SQL"]!=""){
			$sql = $decodeParameters["DIRECT_SQL"];
		}
		//$stmtDecode = $db->singlePrepare($sql);
		$rs = $db->singleQuery($sql);
		
		//$rs = $db->execute($stmtDecode, array($this->getFieldValue()));
		if (!$rs) {
			//$messageContext->addMessage("ERROR", _t("VALORE_DI").$fieldLabel._t("NON_VALIDO"), $fieldId);
			$this->setFieldMessage(_t("VALORE_DI").$this->getFieldLabel()._t("NON_VALIDO"));
			return false;		
		}
		else {
			//$arrayResult = $db->fetch_array($stmtDecode);
			$arrayResult = $db->fetch_array($rs);
			if (!$arrayResult && $this->getAllowedNew()==True) {
				return "*NEW";
			}
			if (!$arrayResult){
				$this->setFieldMessage(_t("VALORE_DI").$this->getFieldLabel()._t("NON_VALIDO"));
				return false;
			}
			else {
				$valueDecoded = $arrayResult[$decodeParameters['RETURN_COLUMN']];
				return $valueDecoded;
			}
		}
	}
	public function complete(){
		global $connzend, $decodeMemory, $settings, $db;
	
			global $connzend, $decodeMemory, $settings, $db;

			$decodeParameters = $this->getDecodeParameters();
			
			if(isset($decodeParameters['UNION_ALL']) && $decodeParameters['UNION_ALL']) {
				$union = $decodeParameters['UNION_ALL'];
					
				$arr_sql_union = array();
				foreach ($union as $index => $gruppo) {
					$arr_sql_union[] = "select '".$gruppo."' as GRUPPO, '$index' as KEY FROM SYSIBM".$settings['db_separator']."SYSDUMMY1";
				}
					
				$decodeParameters['TABLE_NAME'] = "(".implode(" union all ", $arr_sql_union).") as x";
			}
			
			$asTable = " XYZ ";
			$asSelect = " XYZ.* ";
			if (isset($decodeParameters["GROUP_BY"]) && $decodeParameters["GROUP_BY"]!=""){
				$asSelect ="";
			}
			//$select = "select ".$decodeParameters['KEY_FIELD_NAME']." , ". $decodeColumn;
			if (strlen($decodeParameters['TABLE_NAME'])> 10 or !(isset($decodeParameters["LU_SELECT"]) && $decodeParameters["LU_SELECT"]!="")) {
				$asTable = "";
				$asSelect ="";
			}
			$from = " from ".$decodeParameters['TABLE_NAME']. $asTable;
			$compare = "='?'";
			if (isset($decodeParameters['LIKE_KEY'])) {
				$compare = " LIKE '%?%'";
			}
			if (isset($decodeParameters['SELECTION_KEY'])) {
				$compare = $decodeParameters['SELECTION_KEY'];
			
			}
			$queryMask = "##FIELD##%";
			if (isset($decodeParameters["QUERY_MASK"])){
				$queryMask = $decodeParameters["QUERY_MASK"];
			}
			//$compare = str_replace('?', $this->getFieldValue(),$compare);
			$where = " where "; //.$decodeParameters['KEY_FIELD_NAME'].$compare;
			if (!isset($decodeParameters['RETURN_COLUMN'])) {
				$decodeParameters['RETURN_COLUMN']=$decodeParameters['COLUMN'];
			}
			$filter = '';
			$and = "";
			if (isset($decodeParameters['FILTER_SQL']) && $decodeParameters['FILTER_SQL']!='') {
				$filter = ' '.$decodeParameters['FILTER_SQL'];
				$and = " AND ";
			}
			$where_cond = '';
			if(isset($decodeParameters['WHERE_COND']) && $decodeParameters['WHERE_COND']!='') {
				$where_cond = $decodeParameters['WHERE_COND'];
					
				//			echo "REQUEST:<pre>"; print_r($_REQUEST); echo "</pre>";
					
				// Sostituazione parametri JSON
				$where_cond = substituteRequestParmLookup($where_cond, $_REQUEST);
			
				$where_cond = ' and '.$where_cond;
			}
			$decodeColumn = "";
			if (isset($decodeParameters["COLUMN"])){
				$decodeColumn = $decodeParameters["COLUMN"];
				// Patch per campi proiettati
				$find = strpos(strtoupper($decodeColumn), " AS ");
				if ($find!==False) {
					$decodeColumn = substr($decodeColumn, $find+4);
				}
			}
			
			$secondColumn = $decodeParameters["COLUMN"];
			if ($secondColumn =="") 
				$secondColumn = $asSelect;
			
			$select = "select ".$decodeParameters['KEY_FIELD_NAME']." , ". $secondColumn;
/*			
			if (isset($decodeParameters["LU_SELECT"]) && $decodeParameters["LU_SELECT"]!=""){
				$select .=" , $asSelect";
			}
*/			
			if (isset($decodeParameters["LU_SELECT"]) && $decodeParameters["LU_SELECT"]!=""){
				$select = "select ".str_replace("|", ", ", $decodeParameters['LU_SELECT']);
				if(trim($asSelect)!="")
					$select .=" , $asSelect";
			}
/*			
			if(isset($decodeParameters['LU_FIELDS']))
				$select = "select ".$decodeParameters['LU_FIELDS'];
*/			
			//
			$keyColumn = $decodeParameters["KEY_FIELD_NAME"];
			if (isset($decodeParameters["KEY_COLUMN"])){
				$keyColumn = $decodeParameters["KEY_COLUMN"];
			}
			$group_by = "";
			if (isset($decodeParameters["GROUP_BY"]) && $decodeParameters["GROUP_BY"]!=""){
				$group_by = " group by ".$decodeParameters["GROUP_BY"];
			}
			$miacolonna=$keyColumn;
			if (isset($decodeParameters["CASE"]) && $decodeParameters["CASE"]==False) {
				$miacolonna = " UPPER($miacolonna) ";
			}
			$filter2 = " $and $miacolonna LIKE '".str_replace("##FIELD##", sanitize_sql_string($this->getFieldValue()), $queryMask)."'";
			$where .=$filter.$filter2.$where_cond;
			//$sql = $select.$from.$where.$filter.$group_by;
			$sql = $select.$from.$where.$group_by;
			//		echo "SQL: $sql<br>";
			if (isset($decodeParameters["DIRECT_SQL"]) && $decodeParameters["DIRECT_SQL"]!=""){
				$sql = $decodeParameters["DIRECT_SQL"];
			}
			//$stmtDecode = $db->singlePrepare($sql);
			//$rs = $db->singleQuery($sql);
			$dati = array();
			////
			$result = $db->query($sql, false, $this->getMaxResult());
			$x=0;
			if ($result) {
				while($row = $db->fetch_array($result)) {
					$extra = " ";
					if (isset($decodeParameters["LU_SELECT"]) && $decodeParameters["LU_SELECT"]!=""){
						$campi_extra = explode("|", $decodeParameters['LU_SELECT']);
						foreach ($campi_extra as $cc => $vv) {
							$extra .= ucfirst(strtolower($vv)). "-".$row[$vv]." ";
						}
					}
					$desc = $row[$decodeColumn].$extra;
					if (isset($decodeParameters['NO_DESC'])) {
						$desc ="";
					}
					$dati[]= array('value' =>$row[$decodeParameters['KEY_FIELD_NAME']], "desc" => $desc , "id"=>$x);
					$x++;
					if ($x>=$this->getMaxResult()) break;
				}
			}
			return $dati;
		}
	
	
}	

?>