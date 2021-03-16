<?php
  
    // Descrittore routine ZCHKJOB
    $export_description = array(
	array("Name"=>"JOB", "IO"=>I5_IN, "Type"=>I5_TYPE_CHAR, "Length"=>"26"),
	array("Name"=>"STATUS", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	array("Name"=>"ACTSTATUS", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10"));
?>
