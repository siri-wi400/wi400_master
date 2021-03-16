<?php
    global $api_err;
    // Descrittore routine BCH10
    $export_description = array(
		array("Name"=>"USERSPACE", "IO"=>I5_IN, "Type"=>I5_TYPE_CHAR, "Length"=>"20"),
		array("Name"=>"EXTENDATR", "IO"=>I5_IN, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	    array("Name"=>"INITSIZE", "IO"=>I5_IN, "Type"=>I5_TYPE_INT, "Length"=>"10.0"),
	    array("Name"=>"INITVALUE", "IO"=>I5_IN, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	    array("Name"=>"PUBAUT", "IO"=>I5_IN, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	    array("Name"=>"DESC", "IO"=>I5_IN, "Type"=>I5_TYPE_CHAR, "Length"=>"50"),
	    array("Name"=>"REPLACE", "IO"=>I5_IN, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	    array("DSName"=>"API_ERR", "DSParm"=>$api_err, "count"=>1)
	);
?>