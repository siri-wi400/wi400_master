<?php
    global $api_err;
    // Descrittore routine BCH10
    $export_description = array(
		array("Name"=>"HANDLER", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0"),
	    array("Name"=>"USERSPACE", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"20"),
	    array("Name"=>"FORMAT", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"8"),
	    array("Name"=>"BUFFER", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0"),
		array("Name"=>"END", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),	    
	    array("DSName"=>"API_ERR", "DSParm"=>$api_err, "count"=>1)	
	);
/**
1 Spooled file handle Input Binary(4) 
2 Qualified user space name Input Char(20) 
3 Format name Input Char(8) 
4 Ordinal number of buffer to be read Input Binary(4) 
5 End of open spooled file Input Char(10) 
6 Error code I/O Char(*) 

*/
