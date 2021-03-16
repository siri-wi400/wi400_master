<?php
  
    $struct = create_descriptor('IDSSPLFA', $this->connzend);
    // Descrittore routine ZSPLFA
    // JOB nella forma di :JOBNAME (10) + JOBUSER (10) + JOBNBR (6)
    $export_description = array(
					array("Name"=>"SPOOLNAME", "IO"=>I5_IN, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
					array("Name"=>"JOB", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"26"),
					array("Name"=>"NBR", "IO"=>I5_INOUT, "Type"=>I5_TYPE_PACKED, "Length"=>"6.0"),
					array("DSName"=>"SPLFA", "DSParm"=>$struct, "count"=>1),					
	     	);

?>
