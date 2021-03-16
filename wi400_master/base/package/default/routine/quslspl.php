<?php
    global $api_err;
    // Descrittore routine BCH10
    $export_description = array(
		array("Name"=>"USERSPACE", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"20"),
		array("Name"=>"FORMAT", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"8"),
	    array("Name"=>"UTENTE", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	    array("Name"=>"OUTQPA", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"20"),
	    array("Name"=>"FORMTYPE", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	    array("Name"=>"USRDTA", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	    array("DSName"=>"API_ERR", "DSParm"=>$api_err, "count"=>1)	    
	);
?>