<?php

class econ_azienda extends wi400Decoding {

	public function decode(){
			
		global $db;

		$decodeParameters = $this->getDecodeParameters();
		$sql = "SELECT * FROM TAB11AKF WHERE CDAZAK='".$this->getFieldValue()."'";
	    $decodeColumn = "DAZIAK";
		$result = $db->query($sql);
		if ($result) {
			$row = $db->fetch_array($result);
		}
		if (isset($decodeParameters['SAVE_SESSION'])) {
			$_SESSION[$decodeParameters['SAVE_SESSION']]= $this->getFieldValue();
		}
		// Ritorno valori per il batch
		if (!$row) {	
			//$messageContext->addMessage("ERROR", _t("VALORE_DI").$fieldLabel._t("NON_VALIDO"), $fieldId);
			$this->setFieldMessage(_t("VALORE_DI").$this->getFieldLabel()._t("NON_VALIDO"));
			return false;			
		}else{
			//$viewContext->__set($fieldId."_DESCRIPTION",$datiCausale['DESCRIZIONE']);
			return $row[$decodeColumn];
	    }
			
	}

}

?>