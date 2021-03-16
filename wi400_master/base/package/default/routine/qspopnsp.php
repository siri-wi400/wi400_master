<?php
    global $api_err;
    // Descrittore routine BCH10
    $export_description = array(
		array("Name"=>"HANDLER", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0"),
	    array("Name"=>"JOBQUAL", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"26"),
	    array("Name"=>"INTID", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"16"),
	    array("Name"=>"INTJOB", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"16"),
	    array("Name"=>"SPOOLNAME", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	    array("Name"=>"SPOOLNBR", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0"),
		array("Name"=>"BUFFER", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0"),	    
	    array("DSName"=>"API_ERR", "DSParm"=>$api_err, "count"=>1)	
	);
/**
1 Spooled file handle returned Output Binary(4) 
2 Qualified job name Input Char(26) 
3 Internal job identifier Input Char(16) 
4 Internal spooled file identifier Input Char(16) 
5 Spooled file name Input Char(10) 
6 Spooled file number Input Binary(4) 
7 Number of buffers to get Input Binary(4) 
8 Error code I/O Char(*)
*/
