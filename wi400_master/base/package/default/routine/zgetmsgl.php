<?php
  
    // Descrittore routine ZCHGUSRP
		$export_description = array (
			array ("Name" => "JOBQUAL", "IO" => I5_IN, "Type" => I5_TYPE_CHAR, "Length" => "26"), 
			array ("Name" => "FETCH_MODE", "IO" => I5_IN, "Type" => I5_TYPE_CHAR, "Length" => "6"), 
			array ("Name" => "KEY", "IO" => I5_INOUT, "Type" => I5_TYPE_INT, "Length" => "10.0"), 
			array ("Name" => "MESSAGE", "IO" => I5_INOUT, "Type" => I5_TYPE_CHAR, "Length" => "256"),
		);
?>