<?php 
$ds_key_1 = array(
		array("Name"=>"R_LENGTH", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0", "SetLen"=>"datalen_1"),
		array("Name"=>"R_KEY", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0"),
		array("Name"=>"R_TYPE", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
		array("Name"=>"R_RESERVED", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"3"),
		array("Name"=>"R_LDATA", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0"),
		array("Name"=>"R_DATA", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"4"),
);
$ds_key_2 = array(
		array("Name"=>"R_LENGTH", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0", "SetLen"=>"datalen_1"),
		array("Name"=>"R_KEY", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0"),
		array("Name"=>"R_TYPE", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
		array("Name"=>"R_RESERVED", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"3"),
		array("Name"=>"R_LDATA", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0"),
		array("Name"=>"R_DATA", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"20"),
);
$tracciato = array(
	array("Name"=>"JOBNAME", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10") , // Job Name Used 
	array("Name"=>"JOBUSER", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10") , // User Name Used 
	array("Name"=>"JOBNBR", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"6") , // Job Number Used 
	array("Name"=>"INTID", "IO"=>I5_INOUT, "Type"=>I5_TYPE_BYTE, "Length"=>"16") , // Internal Job Id 
	array("Name"=>"JOBSTATUS", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10") , // Status 
	array("Name"=>"JOBTYPE", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1") , // Job Type 
	array("Name"=>"JOBSUBTYPE", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1") , // Job Subtype 
	array("Name"=>"RESERVED", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"2") , // Reserved 
	array("Name"=>"JOBISTATUS", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1") , // Job Information Status
	array("Name"=>"RESERVED2", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"3") , // Reserved 2
	array("Name"=>"FIELD_RET", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0"), // Field Returned
	array("DSName"=>"RETDATI_1", "DSParm"=>$ds_key_1, "count"=>"1", "Len"=>"datalen_1"),
	array("DSName"=>"RETDATI_2", "DSParm"=>$ds_key_2, "count"=>"1", "Len"=>"datalen_2")
);

