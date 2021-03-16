<?php
  
    $dsin = create_descriptor('G323DSIN', $this->connzend);
    $dsou = create_descriptor('G323DSOU', $this->connzend);
    $dsdb = create_descriptor('ANART1AP', $this->connzend);    

    $export_description = array(
    			array("DSName"=>"G323DSIN", "DSParm"=>$dsin, "count"=>1, "Type"=>I5_TYPE_STRUCT),					
    			array("DSName"=>"G323DSOU", "DSParm"=>$dsou, "count"=>1, "Type"=>I5_TYPE_STRUCT),					
			    array("DSName"=>"ANART1AP", "DSParm"=>$dsdb, "count"=>1, "Type"=>I5_TYPE_STRUCT)				
	     	);

?>