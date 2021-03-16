<?php
  
    // Descrittore routine ZP2OPRT Stampa diretta di un PDF su una OUTQ
    $export_description = array(
	array("Name"=>"PDF", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"200"),
	array("Name"=>"OUTQ", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	array("Name"=>"LIBL", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
    array("Name"=>"DUPLEX", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
    array("Name"=>"FLAG", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1")
	);

?>
