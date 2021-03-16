<?php
    global $api_err;
    // Descrittore routine BCH10
    $ds_key = array(
    		array("Name"=>"KEYCODE_1", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0"),
    		array("Name"=>"KEYCODE_2", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0")
    );		
    $export_description = array(
		array("Name"=>"USERSPACE", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"20"),
		array("Name"=>"FORMAT", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"8"),
	    array("Name"=>"JOBQUAL", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"26"),
	    array("Name"=>"STATUS", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
    	// Extension 1	
	    array("DSName"=>"API_ERR", "DSParm"=>$api_err, "count"=>1, "Len"=>"recerr"),
    	// Extension 2
    	array("Name"=>"JOBTYPE", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
    	array("Name"=>"KEYNUMBER", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0"),
    	array("DSName"=>"KEY_FIELD", "DSParm"=>$ds_key, "count"=>"1")
	);
/*
1 Qualified user space name Input Char(20) 
2 Format name Input Char(8) 
3 Qualified job name Input Char(26) 
4 Status Input Char(10)

Status
*ACTIVE Active jobs. This includes group jobs, system request jobs, and disconnected jobs. 
*JOBQ Jobs currently on job queues. 
*OUTQ Jobs that have completed running but still have output on an output queue. 
*ALL All jobs, regardless of status. 
*/ 