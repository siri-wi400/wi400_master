<?php 
$tracciato = array(
	array("Name"=>"JOBNAME", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10") , // Job Name Used 
	array("Name"=>"JOBUSER", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10") , // User Name Used 
	array("Name"=>"JOBNBR", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"6") , // Job Number Used 
	array("Name"=>"INTID", "IO"=>I5_INOUT, "Type"=>I5_TYPE_BYTE, "Length"=>"16") , // Internal Job Id 
	array("Name"=>"JOBSTATUS", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10") , // Status 
	array("Name"=>"JOBTYPE", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1") , // Job Type 
	array("Name"=>"JOBSUBTYPE", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1") , // Job Subtype 
	array("Name"=>"RESERVED", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"2") , // Reserved 
	);
                       