<?php
    global $api_err;
    // Descrittore routine BCH10
    $export_description = array(
		array("Name"=>"HANDLER", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0"),
	    array("DSName"=>"API_ERR", "DSParm"=>$api_err, "count"=>1)	
	);
/**
1 Spooled file handle Input Binary(4) 
2 Error code I/O Char(*) 
*/
