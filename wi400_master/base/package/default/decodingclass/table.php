<?php

class table extends wi400Decoding {

	public function decode(){
			
		global $persTable;

		$decodeParameters = $this->getDecodeParameters();
		
		$datiCausale = $persTable->decodifica($decodeParameters['TABLE'], $this->getFieldValue());
		
	    $decodeColumn = "DESCRIZIONE";
		if (isset($decodeParameters["COLUMN"])){
			$decodeColumn = $decodeParameters["DESCRIZIONE"];
		}	
			
		// Ritorno valori per il batch
		if ($datiCausale['FOUND']!=True) {	
			//$messageContext->addMessage("ERROR", _t("VALORE_DI").$fieldLabel._t("NON_VALIDO"), $fieldId);
			$this->setFieldMessage(_t("VALORE_DI").$this->getFieldLabel()._t("NON_VALIDO"));
			return false;			
		}
		else {
			if (isset($decodeParameters['STATO']) && $decodeParameters['STATO'] !=$datiCausale['STATO']) {
				$this->setFieldMessage(_t("VALORE_DI").$this->getFieldLabel()._t("NON_VALIDO"));
				return false;
			}
			
			if(isset($decodeParameters['NOT_IN_ARRAY']) && in_array($this->getFieldValue(), $decodeParameters['NOT_IN_ARRAY'])) {
				$this->setFieldMessage(_t("VALORE_DI").$this->getFieldLabel()._t("NON_VALIDO"));
				return false;
			}
			
			if(isset($decodeParameters['CHECK_IN_ARRAY']) && !empty($decodeParameters['CHECK_IN_ARRAY'])) {
				foreach($decodeParameters['CHECK_IN_ARRAY'] as $key => $val_array) {
//					$this->setFieldMessage("CHECK_IN_ARRAY: $key");
//					$this->setFieldMessage("CHECK_IN_ARRAY: ".implode(" - ", $val_array));
//					return false;
					
					$tab_array = $datiCausale['TABELLA'];
					
					if(isset($tab_array[$key])) {
//						$this->setFieldMessage("T127LM: ".$tab_array[$key]);
//						return false;
						
						if(!in_array($tab_array[$key], $val_array)) {
							$this->setFieldMessage(_t("VALORE_DI").$this->getFieldLabel()._t("NON_VALIDO"));
							return false;
						}
					}
				}
			}
			
			return $datiCausale[$decodeColumn];
	    }
			
	}
	public function complete(){
			
		global $persTable, $db;
	
		$decodeParameters = $this->getDecodeParameters();
	/*
	 * MI servono queste informazioni
	 * 			'COLUMN' => 'LAST_NAME',
				'TABLE_NAME' => 'SIR_USERS',
				'KEY_FIELD_NAME' => 'USER_NAME',
				
				    [FILE] => FTAB005
    [SIGLA] => T05SIG
    [CODICE] => T05COD
    [DESCRIZIONE] => T05DEL
    [STATO] => T05STA
	 */
		$datiTabella = $persTable->getTabella($decodeParameters['TABLE']);
		//print_r($datiTabella);
		$select = "select ";
		$what = "A.*";
		$from="";
		$decodeParameters['COLUMN']=$datiTabella['DESCRIZIONE'];
		$decodeParameters['KEY_FIELD_NAME']=$datiTabella['CODICE'];
		if (isset($decodeParameters['BYDESC']) && $decodeParameters['BYDESC']=="YES") {
			$decodeParameters['KEY_COLUMN']=$datiTabella['DESCRIZIONE'];
		}
		if (isset($datiTabella['ISTABGEN']) && $datiTabella['ISTABGEN']==True)
		{
			$what.=", SUBSTR(TABREC, ".$datiTabella['STAPOS'] ." , 1) AS STAGEN ,
		    		    SUBSTR(TABREC, ".$datiTabella['DESPOS'] ." , ".$datiTabella['DESLEN']. ") AS DESGEN";
			$decodeParameters['COLUMN']="DESGEN";
			if (isset($decodeParameters['BYDESC']) && $decodeParameters['BYDESC']=="YES") {
				$decodeParameters['KEY_COLUMN']="SUBSTR(TABREC, ".$datiTabella['DESPOS'] ." , ".$datiTabella['DESLEN']. ")";
			}
		}
		$select .= $what." from ".$datiTabella['FILE']. " AS A ";
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
		$and = "";
		$where = " where ".$datiTabella['SIGLA']."="."'".$decodeParameters['TABLE']."'"; //.$decodeParameters['KEY_FIELD_NAME'].$compare;
		$and = " AND ";
		if (!isset($decodeParameters['RETURN_COLUMN'])) {
			$decodeParameters['RETURN_COLUMN']=$decodeParameters['COLUMN'];
		}
		$filter = '';
		if (isset($decodeParameters['FILTER_SQL']) && $decodeParameters['FILTER_SQL']!='') {
			$filter = $and.' '.$decodeParameters['FILTER_SQL'];
			$and = " AND ";
		}
		$decodeColumn = "";
		if (isset($decodeParameters["COLUMN"])){
			$decodeColumn = $decodeParameters["COLUMN"];
		}
		$keyColumn = $decodeParameters["KEY_FIELD_NAME"];
		if (isset($decodeParameters["KEY_COLUMN"])){
			$keyColumn = $decodeParameters["KEY_COLUMN"];
		}
		$group_by = "";
		if (isset($decodeParameters["GROUP_BY"]) && $decodeParameters["GROUP_BY"]!=""){
			$group_by = " group by ".$decodeParameters["GROUP_BY"];
		}
		$filter2 = " $and $keyColumn LIKE '".str_replace("##FIELD##", $this->getFieldValue(), $queryMask)."'";
		$where .=$filter.$filter2;
		//$sql = $select.$from.$where.$filter.$group_by;
		$sql = $select.$from.$where.$group_by;
		//		echo "SQL: $sql<br>";
		if (isset($decodeParameters["DIRECT_SQL"]) && $decodeParameters["DIRECT_SQL"]!=""){
			$sql = $decodeParameters["DIRECT_SQL"];
		}
		//$stmtDecode = $db->singlePrepare($sql);
		$rs = $db->singleQuery($sql);
		$dati = array();
		////
		////echo $sql;
		$result = $db->query($sql, false, $this->getMaxResult());
		$x=0;
		if ($result) {
			while($row = $db->fetch_array($result)) {
				$dati[]= array('value' =>$row[$decodeParameters['KEY_FIELD_NAME']], "desc" => $row[$decodeColumn] , "id"=>$x);
				$x++;
				if ($x>=$this->getMaxResult()) break;
			}
		}
		return $dati;
		}

}

?>