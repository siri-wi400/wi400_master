<?php
    global $api_err;
    // Descrittore routine 
    $export_description = array(
	    array("DSName"=>"DATI", "DSParm"=>$tracciato, "count"=>1),
	    array("Name"=>"SIZEDATA", "IO"=>I5_INOUT, "Type"=>I5_TYPE_INT, "Length"=>"10.0"),
		array("Name"=>"FORMAT", "IO"=>I5_IN, "Type"=>I5_TYPE_CHAR, "Length"=>"8"),
		array("Name"=>"RESET", "IO"=>I5_IN, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
		array("DSName"=>"API_ERR", "DSParm"=>$api_err, "count"=>1)
	);
?>