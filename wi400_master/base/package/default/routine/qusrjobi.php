<?php
    global $api_err;
    // Descrittore routine BCH10
    $export_description = array(
	    array("DSName"=>"DATI", "DSParm"=>$tracciato, "count"=>1),	 
	    array("Name"=>"SIZE", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0"),
		array("Name"=>"FORMAT", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"8"),
	    array("Name"=>"JOBQUAL", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"26"),
	    array("Name"=>"INTID", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"16"),
	    array("DSName"=>"API_ERR", "DSParm"=>$api_err, "count"=>1)	    
	);
/*
1 Receiver variable Output Char(*) 
2 Length of receiver variable Input Binary(4) 
3 Format name Input Char(8) 
4 Qualified job name Input Char(26) 
 
5 Internal job identifier Input Char(16) 

*/ 
	