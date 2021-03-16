<?php
  
    $dsin = create_descriptor('W400DSIN', $this->connzend);
    $dsou = create_descriptor('W400DSOU', $this->connzend);
    $dsgen = create_descriptor('ZGENDSOU', $this->connzend);
    $dsdft = create_descriptor('ZDFTDSOU', $this->connzend);

    $export_description = array(
    			array("DSName"=>"W400DSIN", "DSParm"=>$dsin, "count"=>1, "Type"=>I5_TYPE_STRUCT),					
    			array("DSName"=>"W400DSOU", "DSParm"=>$dsou, "count"=>1, "Type"=>I5_TYPE_STRUCT),
    			array("DSName"=>"ZGENDSOU", "DSParm"=>$dsgen, "count"=>1, "Type"=>I5_TYPE_STRUCT),
    			array("DSName"=>"ZDFTDSOU", "DSParm"=>$dsdft, "count"=>1, "Type"=>I5_TYPE_STRUCT)							
	     	);

?>