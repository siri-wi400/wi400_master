<?php
  
    // Descrittore routine ZGEWIINIT
		$export_description = array (
			array ("Name" => "ID_UNIVOCO", "IO" => I5_INOUT, "Type" => I5_TYPE_CHAR, "Length" => "20"), 
			array ("Name" => "ID_FILE", "IO" => I5_INOUT, "Type" => I5_TYPE_CHAR, "Length" => "200"),
			array ("Name" => "UTENTE", "IO" => I5_IN, "Type" => I5_TYPE_CHAR, "Length" => "10"), 
			array ("Name" => "ERRORE", "IO" => I5_INOUT, "Type" => I5_TYPE_CHAR, "Length" => "1"), 
			array ("Name" => "DESERR", "IO" => I5_INOUT, "Type" => I5_TYPE_CHAR, "Length" => "30")
		);
?>
