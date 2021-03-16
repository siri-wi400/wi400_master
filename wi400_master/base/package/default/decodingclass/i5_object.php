<?php

class i5_object extends wi400Decoding {

	public function decode(){

        global $connzend, $routine_path;
        
		$decodeParameters = $this->getDecodeParameters();
		$tipoOggetto = $decodeParameters["OBJTYPE"];
	    // Libreria Facoltativa
	    $libreria="";
		if (isset($decodeParameters["LIBRERIA"])) {
				$libreria = $decodeParameters["LIBRERIA"];    	
	    }
	    if ($libreria =="") {
	    	$libreria = '*ALL';
	    }
	    $decodeColumn = "DESCRIP";
		if (isset($decodeParameters["COLUMN"])){
				$decodeColumn = $decodeParameters["COLUMN"];
		}	
	    // Nome Oggetto
	    $nome=$this->getFieldValue();
		require_once $routine_path."/os400/wi400Os400Object.cls.php";
		$list = new wi400Os400Object($tipoOggetto, $libreria, $nome);
		$list->getList();	    
        $obj_read = $list->getEntry();
        
		if (!$obj_read) {
				$this->setFieldMessage(_t("VALORE_DI").$this->getFieldLabel()._t("NON_VALIDO"));
				return false;		
		}else{
				//$valueDecoded = $arrayResult[$decodeColumn];
				$descrizione = $obj_read[$decodeColumn];
				if ($descrizione=='') {
					$descrizione = $tipoOggetto;
				}
				return $descrizione;
		}
	}
}	


?>