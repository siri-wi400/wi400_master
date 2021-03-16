<?php

class econ_negozi extends wi400Decoding {

	public function decode(){
			
		global $db;

		$decodeParameters = $this->getDecodeParameters();
		$where = "";
		if (isset($decodeParameters['GET_SESSION'])){
					//$where = " AND COAZ6A ='".$decodeParameters['DATA_VALIDITA']."'";
		    	$where = " AND COAZ6A ='".$_SESSION[$decodeParameters['GET_SESSION']]."'";
		}
  		    	
		$sql = "SELECT * FROM PITAB6AF WHERE NOME6A='NEGOZI' AND CODI6A='".$this->getFieldValue()."' AND ANNU6A<>'A' AND NRIG6A=0 ".$where;
		$decodeColumn = "GENE6A";
		$result = $db->query($sql);
		if ($result) {
			$row = $db->fetch_array($result);
		}
		// Ritorno valori per il batch
		if (!$row) {	
			//$messageContext->addMessage("ERROR", _t("VALORE_DI").$fieldLabel._t("NON_VALIDO"), $fieldId);
			$this->setFieldMessage(_t("VALORE_DI").$this->getFieldLabel()._t("NON_VALIDO"));
			return false;			
		}else{
			//$viewContext->__set($fieldId."_DESCRIPTION",$datiCausale['DESCRIZIONE']);
			return substr($row[$decodeColumn], 0 , 30);
	    }
			
	}

}
?>