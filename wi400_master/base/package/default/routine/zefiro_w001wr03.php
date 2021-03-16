<?php
  
    $dsin = create_descriptor('W400DSIN', $this->connzend);
    $dsou = create_descriptor('W400DSOU', $this->connzend);
    //$dswr = create_descriptor('W001WR03', $this->connzend, 'ZEFIFILDEM');

    $export_description = array(
    			array("DSName"=>"W400DSIN", "DSParm"=>$dsin, "count"=>1, "Type"=>I5_TYPE_STRUCT),					
    			array("DSName"=>"W400DSOU", "DSParm"=>$dsou, "count"=>1, "Type"=>I5_TYPE_STRUCT),
    			array("DSName"=>"W001WR03", "DSParm"=>$tracciato, "count"=>1, "Type"=>I5_TYPE_STRUCT)								
	     	);

?>