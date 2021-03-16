<?php

if (isset($tracciato['DSParm'])) {
	$tracciato = $tracciato['DSParm'];
}
$export_description = array(
		array("Name"=>"CODA", "IO"=>I5_IN, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
		array("Name"=>"LIBRERIA", "IO"=>I5_IN, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
		array("Name"=>"LEN", "IO"=>I5_INOUT, "Type"=>I5_TYPE_PACKED, "Length"=>"5.0"),
		array("DSName"=>"DATI", "DSParm"=>$tracciato, "count"=>1),
		array("Name"=>"WAIT", "IO"=>I5_IN, "Type"=>I5_TYPE_PACKED, "Length"=>"5.0"),
		array("Name"=>"KEYORDER", "IO"=>I5_IN, "Type"=>I5_TYPE_CHAR, "Length"=>"2"),
		array("Name"=>"KEYLEN", "IO"=>I5_IN, "Type"=>I5_TYPE_PACKED, "Length"=>"3.0"),
		array("Name"=>"KEY", "IO"=>I5_IN, "Type"=>I5_TYPE_CHAR, "Length"=>"40"),
		array("Name"=>"SENDERLEN", "IO"=>I5_IN, "Type"=>I5_TYPE_PACKED, "Length"=>"3.0"),
		array("Name"=>"SENDERINFO", "IO"=>I5_IN, "Type"=>I5_TYPE_CHAR, "Length"=>"40"),
);
?>