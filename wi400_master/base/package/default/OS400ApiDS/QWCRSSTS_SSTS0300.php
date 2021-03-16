<?php 
$tracciato = array(
	array("Name"=>"BYTEAVL", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Bytes available 
	array("Name"=>"BYTERET", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Bytes returned
	array("Name"=>"CURDATE", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"8") , // Current date and time
	array("Name"=>"SYSNAME", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"8") , // System name 
	array("Name"=>"USESGON", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Users currently signed on
	array("Name"=>"USESGOFF", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Users temporarily signed off (disconnected)
	array("Name"=>"USESSR", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Users suspended by system request
	array("Name"=>"USESGJ", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Users suspended by group jobs
	array("Name"=>"USESGOFFP", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Users signed off with printer output waiting to print
	array("Name"=>"BJWAITM", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Batch jobs waiting for messages				
	array("Name"=>"BJRUN", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Batch jobs running
	array("Name"=>"BJHELD", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Batch jobs held while running
	array("Name"=>"BJEND", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Batch jobs ending
	array("Name"=>"BJWAITR", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Batch jobs waiting to run or already scheduled
	array("Name"=>"BJHELDQ", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Batch jobs held on a job queue
	array("Name"=>"BJHELDJQ", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Batch jobs on a held job queue
	array("Name"=>"BJUNASS", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Batch jobs on an unassigned job queue
	array("Name"=>"BJENDPO", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0") , // Batch jobs ended with printer output waiting to print
);
