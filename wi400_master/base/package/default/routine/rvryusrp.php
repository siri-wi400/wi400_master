<?php
  
    // Descrittore routine ZCHGUSRP
		$export_description = array (
			array ("Name" => "UTENTE", "IO" => I5_IN, "Type" => I5_TYPE_CHAR, "Length" => "10"), 
			array ("Name" => "PASSWORD", "IO" => I5_IN, "Type" => I5_TYPE_CHAR, "Length" => "10"), 
			array ("Name" => "FLAG", "IO" => I5_INOUT, "Type" => I5_TYPE_CHAR, "Length" => "1"), 
			array ("Name" => "DATSCA", "IO" => I5_INOUT, "Type" => I5_TYPE_CHAR, "Length" => "8"),
			array ("Name" => "IP", "IO" => I5_INOUT, "Type" => I5_TYPE_CHAR, "Length" => "15" )
		);
?>
