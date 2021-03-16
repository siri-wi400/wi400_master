<?php
   
    $struct = create_descriptor('KPJBA', $this->connzend);
    // Descrittore routine CBCH10
    $export_description = array(
	array("DSName"=>"KPJBA", "DSParm"=>$struct, "count"=>1),
	array("Name"=>"AZIONE", "IO"=>I5_IN, "Type"=>I5_TYPE_CHAR, "Length"=>"4")
	);
?>