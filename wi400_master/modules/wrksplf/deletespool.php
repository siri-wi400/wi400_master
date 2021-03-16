<?php
foreach($wi400List->getSelectionArray() as $key => $value)
{
$keyArray = explode("|",$key);

$jobname=   $keyArray[0];    //job
$jobuser=   $keyArray[1];    //user
$jobnumber= $keyArray[2];    //nbr
$splfname=  $keyArray[3];    //splf
$splfnbr=   $keyArray[4];    //splfnbr
$spluserdata = $keyArray[5];

// Cancellazione file di spool
$do =executeCommand("DLTSPLF", array("FILE"=>$splfname, "JOB"=>$jobnumber."/".$jobuser."/".$jobname,
 "SPLNBR"=>$splfnbr), array(),$connzend);
}
subfileDelete("WRKSPLF");
subfileDelete("WRKSPLFA");
?>