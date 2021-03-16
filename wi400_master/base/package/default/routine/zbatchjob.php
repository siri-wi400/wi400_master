<?php
   
    $struct = create_descriptor('KPJBA', $this->connzend);
    // Descrittore routine ZBATCHJOB
    $export_description = array(
	array("Name"=>"PGM", "IO"=>I5_IN, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	array("Name"=>"USER", "IO"=>I5_IN, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),    
	array("DSName"=>"KPJBA", "DSParm"=>$struct, "count"=>1)	
	);
?>