<?php

class wrs_group extends wi400Decoding {

	public function decode(){			
		global $db;

		$decodeParameters = $this->getDecodeParameters();	
		
		$select = "select distinct(".$decodeParameters['COLUMN'].")";
		$from = " from ".$decodeParameters['TABLE_NAME'];
		$compare = "='?'";
		if (isset($decodeParameters['SELECTION_KEY'])) {
		    $compare = $decodeParameters['SELECTION_KEY'];
		}
		$compare = str_replace('?', $this->getFieldValue(),$compare);
		$where = " where ".$decodeParameters['KEY_FIELD_NAME'].$compare;
		if (!isset($decodeParameters['RETURN_COLUMN'])) {
		     $decodeParameters['RETURN_COLUMN']=$decodeParameters['COLUMN'];
		}
		
		$filter = '';
		if (isset($decodeParameters['FILTER_SQL']) && $decodeParameters['FILTER_SQL']!='') {
		    $filter = ' and '.$decodeParameters['FILTER_SQL'];
		}

		$sql = $select.$from.$where.$filter;
		
//		echo "SQL: $sql<br>";

		$rs = $db->query($sql,false,0);
		
		if (!$rs) {
			//$messageContext->addMessage("ERROR", _t("VALORE_DI").$fieldLabel._t("NON_VALIDO"), $fieldId);
			$this->setFieldMessage(_t("VALORE_DI").$this->getFieldLabel()._t("NON_VALIDO"));
			return false;		
		}
		else {
			$valueDecoded = array();
			
			//valori di selezione della zona	
			$sql_zone = "select T05DEL from FTAB005 where T05SIG='0049' and T05STA='1' and T05COD=?";
			$stmt_zone = $db->singlePrepare($sql_zone);
			
			while($arrayResult = $db->fetch_array($rs)) {
				$zona = $arrayResult[$decodeParameters['RETURN_COLUMN']];
				
				$result_zone = $db->execute($stmt_zone, array($zona));
				$des_zona = "";
    			if($zone_array = $db->fetch_array($stmt_zone))
    				$des_zona = $zone_array['T05DEL'];
				
				$valueDecoded[] = $zona."-".$des_zona;
			}
			
			if(!empty($valueDecoded)) {
				return implode(", ", $valueDecoded);
			}
			else {
				//$messageContext->addMessage("ERROR", _t("VALORE_DI").$fieldLabel._t("NON_VALIDO"), $fieldId);
				$this->setFieldMessage(_t("VALORE_DI").$this->getFieldLabel()._t("NON_VALIDO"));
				return false;
			}
		}
	}
	
}	

?>