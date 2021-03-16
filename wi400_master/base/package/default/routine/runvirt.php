<?php
    // Descrittore routine ZVRTREAD
    
    $export_description = array(
    		array("Name"=>"ID", "IO"=>I5_IN, "Type"=>I5_TYPE_CHAR, "Length"=>"40"),
 			array("Name"=>"OPER", "IO"=>I5_IN, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
		    array("Name"=>"INPUTS", "IO"=>I5_IN, "Type"=>I5_TYPE_CHAR, "Length"=>"1000"),
			array("Name"=>"DATA", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10000"),
    		array("Name"=>"OUTPUS", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1000"),
    		array("Name"=>"FLAGRIT", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1")
    );
?>
