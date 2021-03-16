<?php
$myAction = new wi400Action();
ini_set("memory_limit","1000M");
foreach($wi400List->getSelectionArray() as $key => $value)
{
	$keyArray = explode("|",$key);
	$actionContext->setLabel(_t('SPOOL_DETAIL_VIEW'). " ".$keyArray[0]."/".$keyArray[1]."/".$keyArray[2]. " " .$keyArray[3]);
	$history->add($actionContext, "TSPOOLVIEW");
}
?>