<?php
	$k= getListKeyArray("WRKSPLFA");
	$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $_REQUEST['IDLIST']);

	foreach($wi400List->getSelectionArray() as $key => $value){
			$keyArray = explode("|",$key);
			if (isset($value['SPOOLUSRDATA'])){
			    $ret = executeCommand("chgsplfa",array("file" => $k['SPOOLNAME'],
			    "job"=>$k['SPOOLNUMBER']."/".$k['SPOOLUSER']."/".$k['SPOOLJOB'], "splnbr"=>$k['SPOOLNBR'],
			    "usrdta"=>$value['SPOOLUSRDATA']),
			    array());
			    subfileDelete("WRKSPLFA");
			}
	}
?>