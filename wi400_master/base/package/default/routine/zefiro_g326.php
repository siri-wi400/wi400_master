<?php
  
    $dsin = create_descriptor('G326DSIN', $this->connzend);
    $dsou = create_descriptor('G326DSOU', $this->connzend);
    $dsdb = create_descriptor('ANCLI7CP', $this->connzend);    
    $dsd2 = create_descriptor('ANCLD0DP', $this->connzend);

    $export_description = array(
    			array("DSName"=>"G326DSIN", "DSParm"=>$dsin, "count"=>1, "Type"=>I5_TYPE_STRUCT),					
    			array("DSName"=>"G326DSOU", "DSParm"=>$dsou, "count"=>1, "Type"=>I5_TYPE_STRUCT),					
			    array("DSName"=>"ANCLI7CP", "DSParm"=>$dsdb, "count"=>1, "Type"=>I5_TYPE_STRUCT),
			    array("DSName"=>"ANCLD0DP", "DSParm"=>$dsd2, "count"=>1, "Type"=>I5_TYPE_STRUCT)				
	     	);

?>