<?php
  
    // Descrittore routine ZCHGUSRP
    $export_description = array(
	array("Name"=>"USER", "IO"=>I5_IN, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	array("Name"=>"PASSWORD", "IO"=>I5_IN, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	array("Name"=>"NEWPASS", "IO"=>I5_IN, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	array("Name"=>"FLAG", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"MSG1", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"100"),
	array("Name"=>"MSG2", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"300")		
	);
?>
