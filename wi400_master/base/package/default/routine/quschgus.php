<?php
    global $api_err;
    // Descrittore routine BCH10
    if (!(isset($count)) || $count == 0) {
         $count = 1;
    }    
    $export_description = array(
		array("Name"=>"USERSPACE", "IO"=>I5_IN, "Type"=>I5_TYPE_CHAR, "Length"=>"20"),
		array("Name"=>"OFFSET", "IO"=>I5_IN, "Type"=>I5_TYPE_INT, "Length"=>"10.0"),
		array("Name"=>"SIZE", "IO"=>I5_IN, "Type"=>I5_TYPE_INT, "Length"=>"10.0"),
	    array("DSName"=>"DATI", "DSParm"=>$tracciato, "Count"=>$count),	    
	    array("Name"=>"FORCE", "IO"=>I5_IN, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),	
	    array("DSName"=>"API_ERR", "DSParm"=>$api_err, "Count"=>1)	
	  );
 